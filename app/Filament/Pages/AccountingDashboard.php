<?php

namespace App\Filament\Pages;

use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingImportItem;
use App\Models\AccountingInvoice;
use App\Models\AccountingPartner;
use App\Models\AccountingPayment;
use App\Models\AccountingProject;
use App\Models\AccountingReconciliation;
use App\Models\AccountingTransaction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AccountingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static string $view = 'filament.pages.accounting-dashboard';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Financial Dashboard';

    protected static ?string $title = 'Financial & Accounting Dashboard - Three Digit Software';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public function getViewData(): array
    {
        // 1. Contract Values (Agreed contract values across all active projects)
        $totalContractValue = (float) AccountingProject::where('status', '!=', 'cancelled')->sum('total_price');

        // 2. Issued Invoice Amounts
        $totalIssuedInvoices = (float) AccountingInvoice::where('document_status', 'issued')->sum('total_amount');

        // 3. Earned Revenue (Ledger Revenue 4010)
        $revenueAccount = AccountingAccount::where('code', '4010')->first();
        $totalEarnedRevenue = $revenueAccount ? $revenueAccount->balance : 0.0;

        // 4. Customer Collections (Actual realized cash collections)
        $totalCustomerCollections = (float) AccountingPayment::sum('amount')
            + (float) AccountingTransaction::where('transaction_type', 'client_collection')
                ->where('status', 'completed')
                ->sum('amount');

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

        // 7. Company Account Balances
        $cashAccount = AccountingAccount::where('code', '1010')->first();
        $bankAccount = AccountingAccount::where('code', '1020')->first();
        $cashBalance = $cashAccount ? $cashAccount->balance : 0.0;
        $bankBalance = $bankAccount ? $bankAccount->balance : 0.0;
        $totalCompanyFunds = $cashBalance + $bankBalance;

        // 8. Reported Physical Cash & Count Date (Approximate dated observation)
        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->first();
        $reportedCash = $reconciliation ? (float)$reconciliation->reported_cash_balance : 250.0;
        $cashAuditStatus = $reconciliation ? $reconciliation->cash_audit_status : 'estimated_needs_physical_audit';
        $reconciliationDate = '13 October 2026';

        // 9. Partner Loan Balances (Confirmed net company debt to partners)
        $abdullah = AccountingPartner::where('slug', 'abdullah')->first();
        $amer = AccountingPartner::where('slug', 'amer')->first();
        $abdullahDebt = $abdullah ? $abdullah->calculated_debt_balance : 1277.0;
        $amerDebt = $amer ? $amer->calculated_debt_balance : 1277.0;
        $totalPartnerDebt = $abdullahDebt + $amerDebt;

        // 10. Partner Withdrawals and Distributions
        $abdullahDrawings = $abdullah ? $abdullah->total_drawings : 0.0;
        $amerDrawings = $amer ? $amer->total_drawings : 0.0;
        $totalDrawings = $abdullahDrawings + $amerDrawings;

        // 11. Confirmed Customer Receivables (From Ledger Account 1100 + Unpaid Issued Invoices)
        $receivableAccount = AccountingAccount::where('code', '1100')->first();
        $confirmedReceivables = $receivableAccount ? $receivableAccount->balance : 0.0;
        $potentialReceivables = (float) AccountingClient::sum('potential_receivable_amount');

        // 12. Unresolved Records (Pending review staging items & flagged transactions)
        $pendingImportCount = AccountingImportItem::where('review_status', 'pending_review')->count();
        $pendingTrxCount = AccountingTransaction::where('review_status', 'pending_review')->count();
        $totalUnresolvedCount = $pendingImportCount + $pendingTrxCount;

        // 13. Operating Profit & Data Completeness
        // Operating Profit = Earned Revenue - (Direct Costs + Operating Expenses)
        $operatingProfit = $totalEarnedRevenue - ($totalDirectCosts + $totalOperatingExpenses);

        // Recent Activity
        $recentInvoices = AccountingInvoice::with('client')
            ->latest('id')
            ->take(5)
            ->get();

        $recentTransactions = AccountingTransaction::with(['partner', 'client', 'project'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(8)
            ->get();

        return [
            'totalContractValue' => $totalContractValue,
            'totalIssuedInvoices' => $totalIssuedInvoices,
            'totalEarnedRevenue' => $totalEarnedRevenue,
            'totalCustomerCollections' => $totalCustomerCollections,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'totalDirectCosts' => $totalDirectCosts,
            'cashBalance' => $cashBalance,
            'bankBalance' => $bankBalance,
            'totalCompanyFunds' => $totalCompanyFunds,
            'reportedCash' => $reportedCash,
            'cashAuditStatus' => $cashAuditStatus,
            'reconciliationDate' => $reconciliationDate,
            'abdullahDebt' => $abdullahDebt,
            'amerDebt' => $amerDebt,
            'totalPartnerDebt' => $totalPartnerDebt,
            'totalDrawings' => $totalDrawings,
            'abdullahDrawings' => $abdullahDrawings,
            'amerDrawings' => $amerDrawings,
            'confirmedReceivables' => $confirmedReceivables,
            'potentialReceivables' => $potentialReceivables,
            'totalUnresolvedCount' => $totalUnresolvedCount,
            'operatingProfit' => $operatingProfit,
            'recentInvoices' => $recentInvoices,
            'recentTransactions' => $recentTransactions,
        ];
    }
}
