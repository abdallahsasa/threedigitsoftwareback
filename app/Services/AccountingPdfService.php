<?php

namespace App\Services;

use App\Models\AccountingClient;
use App\Models\AccountingCompanySetting;
use App\Models\AccountingInvoice;
use App\Models\AccountingPayment;
use App\Models\AccountingQuotation;
use App\Models\AccountingReceipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AccountingPdfService
{
    /**
     * Generate Invoice PDF
     */
    public function generateInvoicePdf(AccountingInvoice $invoice, bool $download = false)
    {
        $companySettings = AccountingCompanySetting::getSettings();
        $lang = $invoice->document_language ?? 'ar';

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'companySettings' => $companySettings,
        ])->setPaper('a4', 'portrait');

        $filename = "Invoice_{$invoice->invoice_number}.pdf";

        return $download ? $pdf->download($filename) : $pdf->stream($filename);
    }

    /**
     * Get Invoice PDF raw output string
     */
    public function getInvoicePdfOutput(AccountingInvoice $invoice): string
    {
        $companySettings = AccountingCompanySetting::getSettings();
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'companySettings' => $companySettings,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Generate Quotation PDF
     */
    public function generateQuotationPdf(AccountingQuotation $quotation, bool $download = false)
    {
        $companySettings = AccountingCompanySetting::getSettings();

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'companySettings' => $companySettings,
        ])->setPaper('a4', 'portrait');

        $filename = "Quotation_{$quotation->quotation_number}.pdf";

        return $download ? $pdf->download($filename) : $pdf->stream($filename);
    }

    /**
     * Generate Payment Receipt PDF
     */
    public function generateReceiptPdf(AccountingReceipt $receipt, bool $download = false)
    {
        $companySettings = AccountingCompanySetting::getSettings();

        $pdf = Pdf::loadView('pdf.receipt', [
            'receipt' => $receipt,
            'companySettings' => $companySettings,
        ])->setPaper('a4', 'portrait');

        $filename = "Receipt_{$receipt->receipt_number}.pdf";

        return $download ? $pdf->download($filename) : $pdf->stream($filename);
    }

    /**
     * Generate Client Account Statement PDF
     */
    public function generateStatementPdf(AccountingClient $client, ?string $fromDate = null, ?string $toDate = null, bool $download = false)
    {
        $companySettings = AccountingCompanySetting::getSettings();

        // 1. Gather all issued invoices
        $invoicesQuery = $client->invoices()->where('document_status', 'issued');
        if ($fromDate) {
            $invoicesQuery->whereDate('issue_date', '>=', $fromDate);
        }
        if ($toDate) {
            $invoicesQuery->whereDate('issue_date', '<=', $toDate);
        }
        $invoices = $invoicesQuery->get();

        // 2. Gather payments
        $paymentsQuery = $client->payments();
        if ($fromDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $toDate);
        }
        $payments = $paymentsQuery->get();

        // 3. Gather credit notes
        $creditNotesQuery = $client->creditNotes()->where('status', 'issued');
        if ($fromDate) {
            $creditNotesQuery->whereDate('issue_date', '>=', $fromDate);
        }
        if ($toDate) {
            $creditNotesQuery->whereDate('issue_date', '<=', $toDate);
        }
        $creditNotes = $creditNotesQuery->get();

        // Build chronological ledger of client statements
        $rows = [];
        foreach ($invoices as $inv) {
            $rows[] = [
                'date' => $inv->issue_date->format('Y-m-d'),
                'sort_date' => $inv->issue_date->timestamp,
                'type' => 'فاتورة مبيعات',
                'badge_class' => 'badge-info',
                'reference' => $inv->invoice_number,
                'notes' => $inv->title,
                'debit' => (float)$inv->total_amount,
                'credit' => 0.0,
            ];
        }

        foreach ($payments as $pay) {
            $rows[] = [
                'date' => $pay->payment_date->format('Y-m-d'),
                'sort_date' => $pay->payment_date->timestamp,
                'type' => 'سند قبض',
                'badge_class' => 'badge-success',
                'reference' => $pay->payment_number,
                'notes' => $pay->notes ?: $pay->payment_method,
                'debit' => 0.0,
                'credit' => (float)$pay->amount,
            ];
        }

        foreach ($creditNotes as $cn) {
            $rows[] = [
                'date' => $cn->issue_date->format('Y-m-d'),
                'sort_date' => $cn->issue_date->timestamp,
                'type' => 'إشعار دائن',
                'badge_class' => 'badge-warning',
                'reference' => $cn->credit_note_number,
                'notes' => $cn->reason,
                'debit' => 0.0,
                'credit' => (float)$cn->amount,
            ];
        }

        // Sort by date ascending
        usort($rows, fn($a, $b) => $a['sort_date'] <=> $b['sort_date']);

        $runningBalance = 0.0;
        foreach ($rows as &$r) {
            $runningBalance += ($r['debit'] - $r['credit']);
            $r['balance'] = round($runningBalance, 2);
        }

        $totalInvoiced = (float)$invoices->sum('total_amount');
        $totalPaid = (float)$payments->sum('amount');
        $totalCredits = (float)$creditNotes->sum('amount');
        $outstandingBalance = max(0.0, round($totalInvoiced - $totalPaid - $totalCredits, 2));

        $pdf = Pdf::loadView('pdf.statement', [
            'client' => $client,
            'companySettings' => $companySettings,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'statementRows' => $rows,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalCredits' => $totalCredits,
            'outstandingBalance' => $outstandingBalance,
        ])->setPaper('a4', 'portrait');

        $filename = "Statement_{$client->id}_{$client->name}.pdf";

        return $download ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
