<?php

namespace App\Filament\Pages;

use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingImportItem;
use App\Models\AccountingInvoice;
use App\Models\AccountingPartner;
use App\Models\AccountingProject;
use App\Models\AccountingReconciliation;
use App\Models\AccountingTransaction;
use App\Models\JournalEntryLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingReportsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.accounting-reports-page';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Financial Statements';

    protected static ?string $title = 'Financial Statements & Reports';

    protected static ?int $navigationSort = 8;

    public string $activeTab = 'cash_statement'; // cash_statement, partner_statement, client_collections, receivables_aging, monthly_expenses, project_costs, reconciliation_unresolved
    public ?int $selectedPartnerId = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public function mount(): void
    {
        $firstPartner = AccountingPartner::first();
        $this->selectedPartnerId = $firstPartner?->id;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Export current active report to Excel (.xlsx)
     */
    public function exportExcel(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(false);

        $reportTitle = match ($this->activeTab) {
            'cash_statement' => 'Cash Flow Statement',
            'partner_statement' => 'Partner Account Ledger',
            'client_collections' => 'Client Collections & Receivables',
            'receivables_aging' => 'Receivables Aging Report',
            'monthly_expenses' => 'Operating Expenses Statement',
            'project_costs' => 'Project Profitability & Cost Analysis',
            'reconciliation_unresolved' => 'Reconciliation & Unresolved Audit',
            default => 'Financial Statement',
        };

        $sheet->setCellValue('A1', 'Three Digit Software - ' . $reportTitle);
        $sheet->setCellValue('A2', 'Generated At: ' . now()->format('Y-m-d H:i'));

        $row = 4;

        if ($this->activeTab === 'cash_statement') {
            $sheet->setCellValue('A' . $row, 'Date');
            $sheet->setCellValue('B' . $row, 'Entry #');
            $sheet->setCellValue('C' . $row, 'Reference Type');
            $sheet->setCellValue('D' . $row, 'Debit (Inflow)');
            $sheet->setCellValue('E' . $row, 'Credit (Outflow)');
            $sheet->setCellValue('F' . $row, 'Description');

            $lines = JournalEntryLine::whereHas('account', fn($q) => $q->where('code', '1010'))
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->with(['journalEntry'])
                ->get();

            foreach ($lines as $line) {
                $row++;
                $sheet->setCellValue('A' . $row, $line->journalEntry->entry_date->format('Y-m-d'));
                $sheet->setCellValue('B' . $row, $line->journalEntry->entry_number);
                $sheet->setCellValue('C' . $row, $line->journalEntry->reference_type);
                $sheet->setCellValue('D' . $row, (float)$line->debit);
                $sheet->setCellValue('E' . $row, (float)$line->credit);
                $sheet->setCellValue('F' . $row, $line->description);
            }
        } elseif ($this->activeTab === 'partner_statement') {
            $partner = AccountingPartner::find($this->selectedPartnerId);
            $sheet->setCellValue('A3', 'Partner: ' . ($partner?->name_en ?: $partner?->name_ar ?: ''));
            $sheet->setCellValue('A' . $row, 'Date');
            $sheet->setCellValue('B' . $row, 'Entry #');
            $sheet->setCellValue('C' . $row, 'Debit (Repayment / Reduction)');
            $sheet->setCellValue('D' . $row, 'Credit (Funding / Out-of-Pocket Expense)');
            $sheet->setCellValue('E' . $row, 'Description');

            if ($partner) {
                $lines = JournalEntryLine::where('partner_id', $partner->id)
                    ->whereHas('account', fn($q) => $q->where('sub_type', 'partner_debt'))
                    ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                    ->with('journalEntry')
                    ->get();

                foreach ($lines as $line) {
                    $row++;
                    $sheet->setCellValue('A' . $row, $line->journalEntry->entry_date->format('Y-m-d'));
                    $sheet->setCellValue('B' . $row, $line->journalEntry->entry_number);
                    $sheet->setCellValue('C' . $row, (float)$line->debit);
                    $sheet->setCellValue('D' . $row, (float)$line->credit);
                    $sheet->setCellValue('E' . $row, $line->description);
                }
            }
        } elseif ($this->activeTab === 'receivables_aging') {
            $sheet->setCellValue('A' . $row, 'Client');
            $sheet->setCellValue('B' . $row, 'Current (<30d)');
            $sheet->setCellValue('C' . $row, '30 - 60 Days');
            $sheet->setCellValue('D' . $row, '61 - 90 Days');
            $sheet->setCellValue('E' . $row, 'Over 90 Days');
            $sheet->setCellValue('F' . $row, 'Total Outstanding');

            $agingData = $this->getReceivablesAging();
            foreach ($agingData['clients'] as $c) {
                $row++;
                $sheet->setCellValue('A' . $row, $c['name']);
                $sheet->setCellValue('B' . $row, $c['current']);
                $sheet->setCellValue('C' . $row, $c['days_30']);
                $sheet->setCellValue('D' . $row, $c['days_60']);
                $sheet->setCellValue('E' . $row, $c['days_90']);
                $sheet->setCellValue('F' . $row, $c['total']);
            }
        } else {
            $trxs = AccountingTransaction::with(['partner', 'client', 'project'])->get();
            $sheet->setCellValue('A' . $row, 'Transaction #');
            $sheet->setCellValue('B' . $row, 'Date');
            $sheet->setCellValue('C' . $row, 'Type');
            $sheet->setCellValue('D' . $row, 'Amount (USD)');
            $sheet->setCellValue('E' . $row, 'Party / Entity');
            $sheet->setCellValue('F' . $row, 'Money Source');
            $sheet->setCellValue('G' . $row, 'Description');

            foreach ($trxs as $trx) {
                $row++;
                $sheet->setCellValue('A' . $row, $trx->transaction_number);
                $sheet->setCellValue('B' . $row, $trx->transaction_date->format('Y-m-d'));
                $sheet->setCellValue('C' . $row, $trx->transaction_type);
                $sheet->setCellValue('D' . $row, (float)$trx->amount);
                $sheet->setCellValue('E' . $row, $trx->partner?->name_en ?: ($trx->client?->name ?: '-'));
                $sheet->setCellValue('F' . $row, $trx->money_source);
                $sheet->setCellValue('G' . $row, $trx->notes);
            }
        }

        $filename = "three_digits_report_{$this->activeTab}_" . date('Ymd_His') . ".xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Compute Receivables Aging Breakdown
     */
    public function getReceivablesAging(): array
    {
        $invoices = AccountingInvoice::where('document_status', 'issued')
            ->where('balance_due', '>', 0)
            ->with('client')
            ->get();

        $clients = [];
        $totals = ['current' => 0.0, 'days_30' => 0.0, 'days_60' => 0.0, 'days_90' => 0.0, 'total' => 0.0];

        foreach ($invoices as $inv) {
            $clientId = $inv->client_id;
            $clientName = $inv->client->display_name;

            if (!isset($clients[$clientId])) {
                $clients[$clientId] = [
                    'name' => $clientName,
                    'current' => 0.0,
                    'days_30' => 0.0,
                    'days_60' => 0.0,
                    'days_90' => 0.0,
                    'total' => 0.0,
                ];
            }

            $due = $inv->due_date;
            $balance = (float)$inv->balance_due;
            $clients[$clientId]['total'] += $balance;
            $totals['total'] += $balance;

            if (!$due || $due->isFuture()) {
                $clients[$clientId]['current'] += $balance;
                $totals['current'] += $balance;
            } else {
                $days = $due->diffInDays(now());
                if ($days <= 30) {
                    $clients[$clientId]['days_30'] += $balance;
                    $totals['days_30'] += $balance;
                } elseif ($days <= 60) {
                    $clients[$clientId]['days_60'] += $balance;
                    $totals['days_60'] += $balance;
                } else {
                    $clients[$clientId]['days_90'] += $balance;
                    $totals['days_90'] += $balance;
                }
            }
        }

        return [
            'clients' => array_values($clients),
            'totals' => $totals,
        ];
    }

    /**
     * Export current report to PDF
     */
    public function exportPdf(): StreamedResponse
    {
        $data = $this->getViewData();
        $pdf = Pdf::loadView('filament.pages.accounting-report-pdf', $data);
        $filename = "three_digits_report_{$this->activeTab}_" . date('Ymd_His') . ".pdf";

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function getViewData(): array
    {
        $partners = AccountingPartner::all();
        $selectedPartner = AccountingPartner::find($this->selectedPartnerId) ?: $partners->first();

        // 1. Cash Inflows & Outflows
        $cashInflows = (float) JournalEntryLine::whereHas('account', fn($q) => $q->where('code', '1010'))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');

        $cashOutflows = (float) JournalEntryLine::whereHas('account', fn($q) => $q->where('code', '1010'))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit');

        $cashNet = $cashInflows - $cashOutflows;

        $cashLines = JournalEntryLine::whereHas('account', fn($q) => $q->where('code', '1010'))
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->with(['journalEntry'])
            ->latest('id')
            ->take(50)
            ->get();

        // 2. Partner statement lines
        $partnerDebtLines = [];
        $partnerDrawingsLines = [];
        if ($selectedPartner) {
            $partnerDebtLines = JournalEntryLine::where('partner_id', $selectedPartner->id)
                ->whereHas('account', fn($q) => $q->where('sub_type', 'partner_debt'))
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->with('journalEntry')
                ->latest('id')
                ->get();

            $partnerDrawingsLines = JournalEntryLine::where('partner_id', $selectedPartner->id)
                ->whereHas('account', fn($q) => $q->where('sub_type', 'drawings'))
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->with('journalEntry')
                ->latest('id')
                ->get();
        }

        // 3. Client Collections
        $clients = AccountingClient::with(['projects'])->get();

        // 4. Receivables Aging
        $agingData = $this->getReceivablesAging();

        // 5. Projects profitability
        $projects = AccountingProject::with(['client'])->get();

        // 6. Monthly Expenses breakdown
        $monthlyExpenses = AccountingTransaction::whereIn('transaction_type', ['company_expense', 'partner_personal_expense'])
            ->where('status', 'completed')
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, expense_category, sum(amount) as total')
            ->groupBy('month', 'expense_category')
            ->orderBy('month', 'desc')
            ->get();

        // 7. Unresolved & Reconciliation Audit
        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->first();
        $unresolvedItems = AccountingImportItem::where('review_status', 'pending_review')->get();

        return [
            'activeTab' => $this->activeTab,
            'partners' => $partners,
            'selectedPartner' => $selectedPartner,
            'cashInflows' => $cashInflows,
            'cashOutflows' => $cashOutflows,
            'cashNet' => $cashNet,
            'cashLines' => $cashLines,
            'partnerDebtLines' => $partnerDebtLines,
            'partnerDrawingsLines' => $partnerDrawingsLines,
            'clients' => $clients,
            'agingData' => $agingData,
            'projects' => $projects,
            'monthlyExpenses' => $monthlyExpenses,
            'reconciliation' => $reconciliation,
            'unresolvedItems' => $unresolvedItems,
        ];
    }
}
