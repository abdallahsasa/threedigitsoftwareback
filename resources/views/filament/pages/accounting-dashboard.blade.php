<x-filament-panels::page>
    <div class="space-y-6" dir="ltr">
        <!-- 1. Header & Cutover Status Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                            <x-heroicon-m-calendar class="w-3.5 h-3.5" />
                            Cutover Transition Date: {{ $reconciliationDate }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                            <x-heroicon-m-check-badge class="w-3.5 h-3.5" />
                            Balances Verified by Management
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-2.5">
                        Internal Accounting & Partner Capital System
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-3xl leading-relaxed">
                        Strict segregation of company treasury funds from partner personal funding debts and drawings. 
                        Confirmed Debt to Abdullah: <strong class="text-amber-600 dark:text-amber-400">${{ number_format($abdullahDebt, 2) }}</strong> | 
                        Confirmed Debt to Amer: <strong class="text-amber-600 dark:text-amber-400">${{ number_format($amerDebt, 2) }}</strong> (net after $1,000 cash recovery) | 
                        Reported Vault Cash: <strong class="text-emerald-600 dark:text-emerald-400">${{ number_format($reportedCash, 2) }}</strong> (approximate count).
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <x-filament::button tag="a" :href="route('filament.admin.resources.accounting-transactions.create')" color="primary" icon="heroicon-m-plus">
                        New Transaction
                    </x-filament::button>
                    <x-filament::button tag="a" :href="route('filament.admin.pages.accounting-import-page')" color="gray" icon="heroicon-m-arrow-up-tray">
                        Import Excel
                    </x-filament::button>
                    <x-filament::button tag="a" :href="route('filament.admin.pages.accounting-reports-page')" color="gray" icon="heroicon-m-document-chart-bar">
                        Statements
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- 2. Primary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Company Cash In Hand -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company Treasury (Cash)</span>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        ${{ number_format($cashBalance, 2) }}
                    </div>
                    <div class="flex items-center gap-1.5 mt-2.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Cutover Baseline: ${{ number_format($reportedCash, 2) }} (Physical audit pending)</span>
                    </div>
                </div>
            </div>

            <!-- Total Partner Debts -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Partner Debt</span>
                    <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-user-group class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                        ${{ number_format($totalPartnerDebt, 2) }}
                    </div>
                    <div class="mt-2.5 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                        <span>Abdullah: <strong class="text-gray-700 dark:text-gray-300">${{ number_format($abdullahDebt, 2) }}</strong></span>
                        <span>Amer: <strong class="text-gray-700 dark:text-gray-300">${{ number_format($amerDebt, 2) }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Total Client Revenue -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Operating Revenue</span>
                    <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        ${{ number_format($totalRevenue, 2) }}
                    </div>
                    <div class="mt-2.5 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                        <span>Collected from projects</span>
                        <span class="text-blue-600 dark:text-blue-400">Est. Rec: ${{ number_format($potentialReceivables, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Expenses & Direct Costs -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Expenses & Direct Costs</span>
                    <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <x-heroicon-o-receipt-percent class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">
                        ${{ number_format($totalOperatingExpenses + $totalDirectCosts, 2) }}
                    </div>
                    <div class="mt-2.5 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                        <span>Operating: ${{ number_format($totalOperatingExpenses, 2) }}</span>
                        <span>Direct: ${{ number_format($totalDirectCosts, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Secondary Metrics: Partner Accounts & Cutover Audit -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Card 1: Partner Accounts Breakdown -->
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                            <x-heroicon-m-user-circle class="w-5 h-5 text-amber-500" />
                            Confirmed Partner Accounts
                        </h3>
                        <a href="{{ route('filament.admin.resources.accounting-partners.index') }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                            View Ledger &rarr;
                        </a>
                    </div>
                    <div class="space-y-4 mt-4">
                        <!-- Abdullah -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900 dark:text-white">Abdullah Al Sasa</span>
                                <span class="font-extrabold text-amber-600 dark:text-amber-400">${{ number_format($abdullahDebt, 2) }}</span>
                            </div>
                            <div class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                                <span>Payable by Company</span>
                                <span>Drawings: ${{ number_format($abdullahDrawings, 2) }}</span>
                            </div>
                        </div>

                        <!-- Amer -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900 dark:text-white">Amer</span>
                                <span class="font-extrabold text-amber-600 dark:text-amber-400">${{ number_format($amerDebt, 2) }}</span>
                            </div>
                            <div class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                                <span>Payable by Company (Net)</span>
                                <span>Drawings: ${{ number_format($amerDrawings, 2) }}</span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-xs text-amber-900 dark:text-amber-200 border border-amber-200/50 dark:border-amber-900/40">
                            <strong>Audit Clarification:</strong> Amer's $1,000 cash recovery on Aug 18, 2026 was deducted from his balance. Personal settlement of $200–$210 between Abdullah and Amer is already factored into both initial balances.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Operating Profit & Reliability Status -->
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                            <x-heroicon-m-calculator class="w-5 h-5 text-blue-500" />
                            Operating Profit & Status
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $pendingReviewCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                            {{ $pendingReviewCount > 0 ? 'Audit Items Pending' : 'Fully Balanced' }}
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">Net Operating Profit:</div>
                        <div class="text-3xl font-extrabold mt-1 {{ $operatingProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            ${{ number_format($operatingProfit, 2) }}
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Calculation: Revenue (${{ number_format($totalRevenue, 2) }}) minus Direct Costs (${{ number_format($totalDirectCosts, 2) }}) and Operating Expenses (${{ number_format($totalOperatingExpenses, 2) }}).
                        </div>

                        <div class="mt-4 p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-300 space-y-1.5">
                            <div>• Initial $700 founding capital recorded as equity.</div>
                            <div>• Partner shares ($4,798) in review as drawings vs direct labor.</div>
                            <div>• Historical Excel discrepancy (−$254.51) is an unverified difference, not real loss.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Cutover & Review Queue Status -->
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                            <x-heroicon-m-clipboard-document-check class="w-5 h-5 text-indigo-500" />
                            Audit & Review Queue
                        </h3>
                        <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            View All ({{ $pendingReviewCount }}) &rarr;
                        </a>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-rose-500" />
                                <span class="text-xs font-semibold text-rose-800 dark:text-rose-300">Items Requiring Decision</span>
                            </div>
                            <span class="text-sm font-extrabold text-rose-600 dark:text-rose-400">{{ $pendingReviewCount }}</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-300 space-y-1.5">
                            <div class="font-bold text-gray-900 dark:text-white">Key Items in Review Queue:</div>
                            <div>1. Classify partner shares ($4,798 as drawings or execution labor).</div>
                            <div>2. Office rent discrepancy ($1,000 in Excel vs $2,000 verbal).</div>
                            <div>3. Confirm Abdullah Sheet1 duplicated payments ($1,075 × 2).</div>
                            <div>4. Othman payments ($16.21 and $4.00).</div>
                            <div>5. FRS receivables & Abdullah perfumes ($85).</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-semibold text-xs transition border border-indigo-200 dark:border-indigo-800">
                        Open Decision & Audit Screen
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. Quick Review Queue Table -->
        @if(count($pendingReviews) > 0)
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">Pending Review Queue Highlights</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Transactions staged from Excel awaiting management classification</p>
                </div>
                <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                    View Complete Audit Table &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Sheet / Row</th>
                            <th class="p-3.5">Description / Vendor</th>
                            <th class="p-3.5">Amount</th>
                            <th class="p-3.5">Payer in File</th>
                            <th class="p-3.5">Reason for Review</th>
                            <th class="p-3.5">Proposed Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @foreach($pendingReviews as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="p-3.5 font-mono text-gray-500">
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ $item->sheet_name }} : R{{ $item->row_index }}
                                </span>
                            </td>
                            <td class="p-3.5 font-semibold text-gray-900 dark:text-white">
                                {{ $item->raw_data['Vendor / Service'] ?? $item->raw_data['Project / Service'] ?? $item->raw_data['Client Name'] ?? '-' }}
                            </td>
                            <td class="p-3.5 font-bold text-gray-900 dark:text-white">
                                ${{ number_format((float)$item->amount, 2) }}
                            </td>
                            <td class="p-3.5">
                                {{ $item->raw_data['Paid By'] ?? $item->raw_data['founders '] ?? '-' }}
                            </td>
                            <td class="p-3.5 text-rose-600 dark:text-rose-400 max-w-xs">
                                {{ $item->review_reason }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    {{ $item->proposed_type }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- 5. Recent Accounting Transactions -->
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">Recent Posted Transactions & Journal Entries</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Double-entry accounting journal entries posted with balanced debits and credits</p>
                </div>
                <a href="{{ route('filament.admin.resources.accounting-transactions.index') }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                    View All Transactions &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Reference #</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Transaction Type</th>
                            <th class="p-3.5">Amount (USD)</th>
                            <th class="p-3.5">Party / Entity</th>
                            <th class="p-3.5">Money Source</th>
                            <th class="p-3.5">Notes</th>
                            <th class="p-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="p-3.5 font-mono text-gray-500 font-medium">{{ $trx->transaction_number }}</td>
                            <td class="p-3.5">{{ $trx->transaction_date->format('Y-m-d') }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full font-semibold text-xs
                                    @if($trx->transaction_type === 'client_collection') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @elseif($trx->transaction_type === 'partner_funding') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                    @elseif($trx->transaction_type === 'partner_personal_expense') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                                    @elseif($trx->transaction_type === 'partner_debt_repayment') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300
                                    @elseif($trx->transaction_type === 'partner_distribution') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 @endif">
                                    {{ match($trx->transaction_type) {
                                        'client_collection' => 'Client Collection',
                                        'partner_funding' => 'Partner Funding',
                                        'partner_personal_expense' => 'Personal Out-of-Pocket Expense',
                                        'company_expense' => 'Company Expense',
                                        'partner_debt_repayment' => 'Partner Debt Repayment',
                                        'partner_distribution' => 'Partner Drawing / Distribution',
                                        'capital_injection' => 'Founding Capital Injection',
                                        'partner_project_labor' => 'Partner Project Execution Labor',
                                        default => ucwords(str_replace('_', ' ', $trx->transaction_type))
                                    } }}
                                </span>
                            </td>
                            <td class="p-3.5 font-bold text-gray-900 dark:text-white">
                                ${{ number_format((float)$trx->amount, 2) }}
                            </td>
                            <td class="p-3.5 font-medium">
                                {{ $trx->partner?->name_en ?: ($trx->partner?->name_ar ?: ($trx->client?->name ?: '-')) }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ match($trx->money_source) {
                                        'company_funds' => 'Company Treasury',
                                        'personal_abdullah' => 'Abdullah Personal',
                                        'personal_amer' => 'Amer Personal',
                                        'othman' => 'Othman',
                                        default => ucwords(str_replace('_', ' ', $trx->money_source))
                                    } }}
                                </span>
                            </td>
                            <td class="p-3.5 max-w-xs truncate text-gray-600 dark:text-gray-400">
                                {{ $trx->notes ?: '-' }}
                            </td>
                            <td class="p-3.5">
                                @if($trx->status === 'reversed')
                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 text-xs font-semibold">Reversed</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-semibold">Posted</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-gray-500">
                                No transactions recorded yet. Click "New Transaction" or "Import Excel" to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
