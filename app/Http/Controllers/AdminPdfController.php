<?php

namespace App\Http\Controllers;

use App\Models\AccountingClient;
use App\Models\AccountingInvoice;
use App\Models\AccountingQuotation;
use App\Models\AccountingReceipt;
use App\Services\AccountingPdfService;
use Illuminate\Http\Request;

class AdminPdfController extends Controller
{
    public function invoice(Request $request, int $id, AccountingPdfService $pdfService)
    {
        $invoice = AccountingInvoice::findOrFail($id);
        $download = $request->has('download');
        return $pdfService->generateInvoicePdf($invoice, $download);
    }

    public function quotation(Request $request, int $id, AccountingPdfService $pdfService)
    {
        $quotation = AccountingQuotation::findOrFail($id);
        $download = $request->has('download');
        return $pdfService->generateQuotationPdf($quotation, $download);
    }

    public function receipt(Request $request, int $id, AccountingPdfService $pdfService)
    {
        $receipt = AccountingReceipt::findOrFail($id);
        $download = $request->has('download');
        return $pdfService->generateReceiptPdf($receipt, $download);
    }

    public function statement(Request $request, int $clientId, AccountingPdfService $pdfService)
    {
        $client = AccountingClient::findOrFail($clientId);
        $download = $request->has('download');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        return $pdfService->generateStatementPdf($client, $fromDate, $toDate, $download);
    }
}
