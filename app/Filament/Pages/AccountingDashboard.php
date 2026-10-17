<?php

namespace App\Filament\Pages;

use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingImportItem;
use App\Models\AccountingPartner;
use App\Models\AccountingProject;
use App\Models\AccountingReconciliation;
use App\Models\AccountingTransaction;
use App\Models\JournalEntryLine;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AccountingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static string $view = 'filament.pages.accounting-dashboard';

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'لوحة المحاسبة العامة';

    protected static ?string $title = 'لوحة التحكم والتحليل المالي - Three Digit Software';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public function getViewData(): array
    {
        // 1. Company Fund Balances
        $cashAccount = AccountingAccount::where('code', '1010')->first();
        $bankAccount = AccountingAccount::where('code', '1020')->first();
        $cashBalance = $cashAccount ? $cashAccount->balance : 0.0;
        $bankBalance = $bankAccount ? $bankAccount->balance : 0.0;

        // 2. Transition Point & Reported Cash
        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->first();
        $reportedCash = $reconciliation ? (float)$reconciliation->reported_cash_balance : 250.0;
        $cashAuditStatus = $reconciliation ? $reconciliation->cash_audit_status : 'estimated_needs_physical_audit';

        // 3. Partner Debts (Confirmed)
        $abdullah = AccountingPartner::where('slug', 'abdullah')->first();
        $amer = AccountingPartner::where('slug', 'amer')->first();
        $abdullahDebt = $abdullah ? $abdullah->calculated_debt_balance : 1277.0;
        $amerDebt = $amer ? $amer->calculated_debt_balance : 1277.0;
        $totalPartnerDebt = $abdullahDebt + $amerDebt;

        // 4. Client Collections
        $revenueAccount = AccountingAccount::where('code', '4010')->first();
        $totalRevenue = $revenueAccount ? $revenueAccount->balance : 0.0;

        // 5. Operating Expenses
        $rentExpense = AccountingAccount::where('code', '6010')->first()?->balance ?? 0.0;
        $officeExpense = AccountingAccount::where('code', '6020')->first()?->balance ?? 0.0;
        $softwareExpense = AccountingAccount::where('code', '6030')->first()?->balance ?? 0.0;
        $marketingExpense = AccountingAccount::where('code', '6040')->first()?->balance ?? 0.0;
        $otherExpense = AccountingAccount::where('code', '6090')->first()?->balance ?? 0.0;
        $totalOperatingExpenses = $rentExpense + $officeExpense + $softwareExpense + $marketingExpense + $otherExpense;

        // 6. Direct Project Costs
        $directCostAccount = AccountingAccount::where('code', '5010')->first();
        $totalDirectCosts = $directCostAccount ? $directCostAccount->balance : 0.0;

        // 7. Partner Drawings / Distributions
        $abdullahDrawings = $abdullah ? $abdullah->total_drawings : 0.0;
        $amerDrawings = $amer ? $amer->total_drawings : 0.0;
        $totalDrawings = $abdullahDrawings + $amerDrawings;

        // 8. Potential Client Receivables (notes from Excel & clients)
        $potentialReceivables = (float) AccountingClient::sum('potential_receivable_amount');

        // 9. Items Needing Review
        $pendingReviewCount = AccountingImportItem::where('review_status', 'pending_review')->count();
        $flaggedTrxCount = AccountingTransaction::where('review_status', 'pending_review')->count();

        // 10. Operating Profit
        // Operating Profit = Revenue - (Direct Costs + Operating Expenses)
        $operatingProfit = $totalRevenue - ($totalDirectCosts + $totalOperatingExpenses);

        // 11. Recent Transactions
        $recentTransactions = AccountingTransaction::with(['partner', 'client', 'project'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(10)
            ->get();

        // 12. Pending Review Highlights
        $pendingReviews = AccountingImportItem::where('review_status', 'pending_review')
            ->take(6)
            ->get();

        return [
            'cashBalance' => $cashBalance,
            'bankBalance' => $bankBalance,
            'reportedCash' => $reportedCash,
            'cashAuditStatus' => $cashAuditStatus,
            'reconciliationDate' => '13 أكتوبر 2026',
            'abdullahDebt' => $abdullahDebt,
            'amerDebt' => $amerDebt,
            'totalPartnerDebt' => $totalPartnerDebt,
            'totalRevenue' => $totalRevenue,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'totalDirectCosts' => $totalDirectCosts,
            'totalDrawings' => $totalDrawings,
            'abdullahDrawings' => $abdullahDrawings,
            'amerDrawings' => $amerDrawings,
            'potentialReceivables' => $potentialReceivables,
            'pendingReviewCount' => $pendingReviewCount + $flaggedTrxCount,
            'operatingProfit' => $operatingProfit,
            'recentTransactions' => $recentTransactions,
            'pendingReviews' => $pendingReviews,
            'foundingCapitalSpent' => 700.0,
            'historicalReserve' => 2304.0,
        ];
    }
}
