<?php

namespace App\Http\Controllers;

use App\Models\AccountingCompanySetting;
use App\Models\AccountingDocumentShare;
use App\Models\AccountingInvoice;
use App\Models\AccountingQuotation;
use App\Models\AccountingReceipt;
use App\Services\AccountingPdfService;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    public function viewDocument(Request $request, string $token)
    {
        $share = AccountingDocumentShare::where('token', $token)->first();

        if (!$share || !$share->isValid()) {
            return response()->view('portal.invalid_link', [
                'message' => 'هذا الرابط غير صالح أو منتهي الصلاحية. يرجى التواصل مع فريق ثري ديجيت للحصول على رابط جديد. / This document link is invalid, expired, or has been revoked.',
            ], 404);
        }

        // Record real view with IP
        $share->recordView($request->ip());

        $companySettings = AccountingCompanySetting::getSettings();
        $doc = $share->getDocument();

        if (!$doc) {
            abort(404, 'المستند المطلوب لم يعد متوفراً.');
        }

        return view('portal.document', [
            'share' => $share,
            'doc' => $doc,
            'docType' => $share->document_type,
            'companySettings' => $companySettings,
        ]);
    }

    public function downloadPdf(Request $request, string $token, AccountingPdfService $pdfService)
    {
        $share = AccountingDocumentShare::where('token', $token)->first();

        if (!$share || !$share->isValid()) {
            abort(403, 'الرابط غير صالح.');
        }

        $share->recordView($request->ip());
        $doc = $share->getDocument();

        return match ($share->document_type) {
            'invoice' => $pdfService->generateInvoicePdf($doc, true),
            'quotation' => $pdfService->generateQuotationPdf($doc, true),
            'receipt' => $pdfService->generateReceiptPdf($doc, true),
            'statement' => $pdfService->generateStatementPdf($share->client, null, null, true),
            default => abort(404),
        };
    }
}
