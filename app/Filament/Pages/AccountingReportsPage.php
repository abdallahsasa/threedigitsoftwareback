<?php

namespace App\Filament\Pages;

use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingPartner;
use App\Models\AccountingProject;
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

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'التقارير المالية';

    protected static ?string $title = 'التقارير المالية والقوائم المحاسبية';

    protected static ?int $navigationSort = 8;

    public string $activeTab = 'cash_statement'; // cash_statement, partner_statement, client_collections, monthly_expenses, project_costs
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
        $sheet->setRightToLeft(true);

        $reportTitle = match ($this->activeTab) {
            'cash_statement' => 'كشف حركة الصندوق والنقدية',
            'partner_statement' => 'كشف حساب الشريك',
            'client_collections' => 'تقرير تحصيلات العملاء والمشاريع',
            'monthly_expenses' => 'تقرير المصاريف التشغيلية الشهرية',
            'project_costs' => 'تقرير تكاليف وأرباح المشاريع',
            default => 'تقرير مالي',
        };

        $sheet->setCellValue('A1', 'Three Digit Software - ' . $reportTitle);
        $sheet->setCellValue('A2', 'تاريخ استخراج التقرير: ' . now()->format('Y-m-d H:i'));

        $row = 4;

        if ($this->activeTab === 'cash_statement') {
            $sheet->setCellValue('A' . $row, 'التاريخ');
            $sheet->setCellValue('B' . $row, 'رقم الحركة');
            $sheet->setCellValue('C' . $row, 'نوع الحركة');
            $sheet->setCellValue('D' . $row, 'وارد (مقبوضات)');
            $sheet->setCellValue('E' . $row, 'صادر (مدفوعات)');
            $sheet->setCellValue('F' . $row, 'البيان');

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
            $sheet->setCellValue('A3', 'الشريك: ' . ($partner?->name_ar ?: ''));
            $sheet->setCellValue('A' . $row, 'التاريخ');
            $sheet->setCellValue('B' . $row, 'رقم الحركة');
            $sheet->setCellValue('C' . $row, 'مدين (سداد مسترد)');
            $sheet->setCellValue('D' . $row, 'دائن (تمويل / مصروف شخصي)');
            $sheet->setCellValue('E' . $row, 'البيان');

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
        } else {
            // General export
            $trxs = AccountingTransaction::with(['partner', 'client', 'project'])->get();
            $sheet->setCellValue('A' . $row, 'رقم الحركة');
            $sheet->setCellValue('B' . $row, 'التاريخ');
            $sheet->setCellValue('C' . $row, 'نوع الحركة');
            $sheet->setCellValue('D' . $row, 'المبلغ (USD)');
            $sheet->setCellValue('E' . $row, 'الطرف المعني');
            $sheet->setCellValue('F' . $row, 'مصدر الأموال');
            $sheet->setCellValue('G' . $row, 'البيان');

            foreach ($trxs as $trx) {
                $row++;
                $sheet->setCellValue('A' . $row, $trx->transaction_number);
                $sheet->setCellValue('B' . $row, $trx->transaction_date->format('Y-m-d'));
                $sheet->setCellValue('C' . $row, $trx->transaction_type);
                $sheet->setCellValue('D' . $row, (float)$trx->amount);
                $sheet->setCellValue('E' . $row, $trx->partner?->name_ar ?: ($trx->client?->name ?: '-'));
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

        // 4. Projects profitability
        $projects = AccountingProject::with(['client'])->get();

        // 5. Monthly Expenses breakdown
        $monthlyExpenses = AccountingTransaction::whereIn('transaction_type', ['company_expense', 'partner_personal_expense'])
            ->where('status', 'completed')
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, expense_category, sum(amount) as total')
            ->groupBy('month', 'expense_category')
            ->orderBy('month', 'desc')
            ->get();

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
            'projects' => $projects,
            'monthlyExpenses' => $monthlyExpenses,
        ];
    }
}
