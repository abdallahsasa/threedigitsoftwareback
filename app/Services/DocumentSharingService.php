<?php

namespace App\Services;

use App\Models\AccountingAuditLog;
use App\Models\AccountingCompanySetting;
use App\Models\AccountingDocumentShare;
use App\Models\AccountingInvoice;
use App\Models\AccountingQuotation;
use App\Models\AccountingReceipt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class DocumentSharingService
{
    public function __construct(
        protected AccountingPdfService $pdfService
    ) {}

    /**
     * Generate or retrieve an active secure share link for a document
     */
    public function getOrCreateShare(string $docType, int $docId, int $clientId, string $channel = 'direct_link', ?string $recipient = null): AccountingDocumentShare
    {
        $existing = AccountingDocumentShare::where('document_type', $docType)
            ->where('document_id', $docId)
            ->where('client_id', $clientId)
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        return AccountingDocumentShare::createSecureShare($docType, $docId, $clientId, $channel, $recipient, 30);
    }

    /**
     * Prepare WhatsApp text message with secure link
     */
    public function prepareWhatsAppText(AccountingDocumentShare $share): string
    {
        $doc = $share->getDocument();
        $company = AccountingCompanySetting::getSettings();
        $client = $share->client;

        $docTitle = match ($share->document_type) {
            'invoice' => "فاتورة مبيعات رقم #{$doc->invoice_number} بمبلغ " . number_format($doc->total_amount, 2) . " " . ($doc->currency ?? 'USD'),
            'quotation' => "عرض سعر رسمي رقم #{$doc->quotation_number} بمبلغ " . number_format($doc->total_amount, 2) . " " . ($doc->currency ?? 'USD'),
            'receipt' => "سند قبض رسمي رقم #{$doc->receipt_number} بمبلغ " . number_format($doc->amount, 2) . " " . ($doc->currency ?? 'USD'),
            'statement' => "كشف حساب مالي رسمي للعميل {$client->display_name}",
            default => "مستند مالي",
        };

        return "مرحباً {$client->display_name}،\n\nتحية طيبة من شركة {$company->brand_name}.\n\nيسرنا تزويدكم برابط استعراض وتحميل {$docTitle}:\n\n🔗 {$share->document_url}\n\nشكراً لتعاملكم معنا.\nفريق {$company->brand_name}";
    }

    /**
     * Get WhatsApp Web Click-to-Chat URL
     */
    public function getWhatsAppUrl(AccountingDocumentShare $share): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string)$share->client->whatsapp ?: (string)$share->client->phone);
        $message = urlencode($this->prepareWhatsAppText($share));

        if (!empty($phone)) {
            return "https://api.whatsapp.com/send?phone={$phone}&text={$message}";
        }

        return "https://api.whatsapp.com/send?text={$message}";
    }

    /**
     * Preview Email parameters before dispatching
     */
    public function previewEmail(AccountingDocumentShare $share): array
    {
        $doc = $share->getDocument();
        $company = AccountingCompanySetting::getSettings();
        $client = $share->client;

        $subject = match ($share->document_type) {
            'invoice' => "فاتورة مبيعات رقم {$doc->invoice_number} من {$company->brand_name}",
            'quotation' => "عرض سعر رسمي رقم {$doc->quotation_number} من {$company->brand_name}",
            'receipt' => "سند قبض رسمي رقم {$doc->receipt_number} من {$company->brand_name}",
            'statement' => "كشف حساب رسمي من {$company->brand_name}",
            default => "مستند مالي من {$company->brand_name}",
        };

        $body = "السادة / {$client->display_name} المحترمين،\n\n"
            . "مرفق طيه نسخة رسمية بصيغة PDF من المستند المالي الصادر إليكم من {$company->brand_name}.\n\n"
            . "كما يمكنكم استعراض وتحميل المستند مباشرة عبر الرابط المؤمن التالي:\n"
            . "{$share->document_url}\n\n"
            . "مع فائق التقدير والاحترام،\n"
            . "قسم الحسابات والمالية — {$company->brand_name}\n"
            . "{$company->email} | {$company->phone}";

        $attachmentName = match ($share->document_type) {
            'invoice' => "Invoice_{$doc->invoice_number}.pdf",
            'quotation' => "Quotation_{$doc->quotation_number}.pdf",
            'receipt' => "Receipt_{$doc->receipt_number}.pdf",
            'statement' => "Statement_{$client->id}.pdf",
            default => "Document.pdf",
        };

        return [
            'recipient_email' => $share->recipient_contact ?: $client->email,
            'subject' => $subject,
            'body' => $body,
            'attachment_name' => $attachmentName,
        ];
    }

    /**
     * Send email with attached PDF, avoiding duplicate sends and recording attempt status
     */
    public function sendEmail(AccountingDocumentShare $share, array $emailData): void
    {
        $recipient = $emailData['recipient_email'] ?? $share->client->email;

        if (empty($recipient)) {
            throw ValidationException::withMessages([
                'recipient_email' => 'البريد الإلكتروني للعميل غير محدد. يرجى إدخال عنوان بريد إلكتروني صالح.',
            ]);
        }

        // Prevent accidental duplicate sends within 1 minute
        if ($share->email_status === 'delivered' && $share->updated_at->diffInMinutes(now()) < 1) {
            throw ValidationException::withMessages([
                'duplicate_send' => 'تم إرسال هذا المستند بالبريد للتو بنجاح. لمنع التكرار، انتظر دقيقة قبل إعادة الإرسال.',
            ]);
        }

        $doc = $share->getDocument();
        $pdfOutput = match ($share->document_type) {
            'invoice' => $this->pdfService->getInvoicePdfOutput($doc),
            default => '',
        };

        $subject = $emailData['subject'];
        $body = $emailData['body'];
        $attachmentName = $emailData['attachment_name'];

        try {
            $share->email_status = 'queued';
            $share->recipient_contact = $recipient;
            $share->save();

            Mail::raw($body, function ($message) use ($recipient, $subject, $pdfOutput, $attachmentName) {
                $message->to($recipient)
                    ->subject($subject);

                if (!empty($pdfOutput)) {
                    $message->attachData($pdfOutput, $attachmentName, [
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            $share->email_status = 'delivered';
            $share->email_error = null;
            $share->save();

            // Update document sending status
            if ($share->document_type === 'invoice' && $doc instanceof AccountingInvoice) {
                $doc->sending_status = 'provider_accepted';
                $doc->save();
            }

            AccountingAuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'send_email',
                'entity_type' => $share->document_type,
                'entity_id' => $share->document_id,
                'description' => "تم إرسال المستند بنجاح بالبريد الإلكتروني إلى {$recipient}",
            ]);
        } catch (\Throwable $e) {
            $share->email_status = 'failed';
            $share->email_error = $e->getMessage();
            $share->save();

            if ($share->document_type === 'invoice' && $doc instanceof AccountingInvoice) {
                $doc->sending_status = 'failed';
                $doc->save();
            }

            throw $e;
        }
    }
}
