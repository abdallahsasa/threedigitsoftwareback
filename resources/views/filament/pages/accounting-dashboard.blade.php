<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/accounting-pages.css') }}?v=2">
    <div class="space-y-6" dir="ltr">
        <!-- 1. Transition Baseline Status Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-amber-500/5 to-transparent border border-emerald-500/20 shadow-sm relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                            <x-heroicon-m-calendar class="w-3.5 h-3.5" />
                            Confirmed Transition Date: {{ $reconciliationDate }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                            <x-heroicon-m-check-badge class="w-3.5 h-3.5" />
                            Management Confirmed Debt: $2,554 ($1,277 each)
                        </span>
                        @if($totalUnresolvedCount > 0)
                            <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-700 dark:text-red-300 border border-red-500/30 hover:underline">
                                <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5" />
                                {{ $totalUnresolvedCount }} Unresolved Items Pending Review
                            </a>
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-2.5">
                        Three Digit Software — ERP & Financial Management
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-3xl leading-relaxed">
                        Segregated double-entry accounting engine. Net historical debt to Abdullah: <strong class="text-amber-600 dark:text-amber-400">${{ number_format($abdullahDebt, 2) }}</strong> | 
                        Net historical debt to Amer: <strong class="text-amber-600 dark:text-amber-400">${{ number_format($amerDebt, 2) }}</strong> (net after $1,000 repayment) | 
                        Reported Vault Cash: <strong class="text-emerald-600 dark:text-emerald-400">${{ number_format($reportedCash, 2) }}</strong> (<span class="text-amber-500">غير محسوم / Unresolved Observation</span>).
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <x-filament::button tag="a" :href="route('filament.admin.resources.accounting-invoices.create')" color="primary" icon="heroicon-m-plus">
                        New Invoice
                    </x-filament::button>
                    <x-filament::button tag="a" :href="route('filament.admin.resources.accounting-quotations.create')" color="gray" icon="heroicon-m-document-currency-dollar">
                        New Quotation
                    </x-filament::button>
                    <x-filament::button tag="a" :href="route('filament.admin.pages.accounting-reports-page')" color="gray" icon="heroicon-m-document-chart-bar">
                        Reports & Exports
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- 2. Thirteen Core Business Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Contract Values -->
            <a href="{{ route('filament.admin.resources.accounting-projects.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Contract Values</span>
                    <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 group-hover:scale-105 transition">
                        <x-heroicon-o-briefcase class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalContractValue, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>All active project scopes</span>
                        <span class="text-purple-600 font-semibold">View Projects →</span>
                    </div>
                </div>
            </a>

            <!-- 2. Issued Invoice Amounts -->
            <a href="{{ route('filament.admin.resources.accounting-invoices.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Issued Invoices</span>
                    <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:scale-105 transition">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalIssuedInvoices, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Official posted billing</span>
                        <span class="text-blue-600 font-semibold">Invoices List →</span>
                    </div>
                </div>
            </a>

            <!-- 3. Earned Revenue -->
            <a href="{{ route('filament.admin.pages.accounting-reports-page') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Earned Revenue (4010)</span>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                        ${{ number_format($totalEarnedRevenue, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Recognized operating revenue</span>
                        <span class="text-emerald-600 font-semibold">Ledger →</span>
                    </div>
                </div>
            </a>

            <!-- 4. Customer Collections -->
            <a href="{{ route('filament.admin.resources.accounting-payments.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer Collections</span>
                    <div class="p-2.5 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 group-hover:scale-105 transition">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalCustomerCollections, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Actual cash collected</span>
                        <span class="text-teal-600 font-semibold">Receipts →</span>
                    </div>
                </div>
            </a>

            <!-- 5. Operating Expenses -->
            <a href="{{ route('filament.admin.resources.accounting-transactions.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Operating Expenses (6000s)</span>
                    <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 group-hover:scale-105 transition">
                        <x-heroicon-o-building-office class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalOperatingExpenses, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Rent, office, tools, ads</span>
                        <span class="text-rose-600 font-semibold">Expenses →</span>
                    </div>
                </div>
            </a>

            <!-- 6. Direct Project Costs -->
            <a href="{{ route('filament.admin.resources.accounting-transactions.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Direct Project Costs (5010)</span>
                    <div class="p-2.5 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 group-hover:scale-105 transition">
                        <x-heroicon-o-wrench-screwdriver class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalDirectCosts, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Direct delivery & labor</span>
                        <span class="text-orange-600 font-semibold">Costs →</span>
                    </div>
                </div>
            </a>

            <!-- 7. Company Account Balances -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company Accounts</span>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-wallet class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalCompanyFunds, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Cash: ${{ number_format($cashBalance, 2) }}</span>
                        <span>Bank: ${{ number_format($bankBalance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- 8. Reported Physical Cash & Date -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reported Physical Cash</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300">
                        غير محسوم / Unresolved
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400">
                        ~${{ number_format($reportedCash, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Observed on {{ $reconciliationDate }} (Physical audit pending)
                    </div>
                </div>
            </div>

            <!-- 9. Partner Loan Balances -->
            <a href="{{ route('filament.admin.resources.accounting-partners.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Partner Loans (Debt)</span>
                    <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:scale-105 transition">
                        <x-heroicon-o-user-group class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400">
                        ${{ number_format($totalPartnerDebt, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Abdullah: ${{ number_format($abdullahDebt, 2) }}</span>
                        <span>Amer: ${{ number_format($amerDebt, 2) }}</span>
                    </div>
                </div>
            </a>

            <!-- 10. Partner Withdrawals and Distributions -->
            <a href="{{ route('filament.admin.resources.accounting-partners.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Partner Drawings (3100s)</span>
                    <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 group-hover:scale-105 transition">
                        <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($totalDrawings, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Separate from debts</span>
                        <span class="text-indigo-600 font-semibold">Drawings →</span>
                    </div>
                </div>
            </a>

            <!-- 11. Confirmed Customer Receivables -->
            <a href="{{ route('filament.admin.resources.accounting-clients.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer Receivables</span>
                    <div class="p-2.5 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 group-hover:scale-105 transition">
                        <x-heroicon-o-users class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        ${{ number_format($confirmedReceivables, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Ledger Confirmed (1100)</span>
                        <span class="text-cyan-600 font-semibold">Clients →</span>
                    </div>
                </div>
            </a>

            <!-- 12. Unresolved Records -->
            <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition block group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Unresolved Items</span>
                    <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:scale-105 transition">
                        <x-heroicon-o-question-mark-circle class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black {{ $totalUnresolvedCount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                        {{ $totalUnresolvedCount }} records
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                        <span>Excluded from final P&L</span>
                        <span class="text-amber-600 font-semibold">Review Staging →</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3. Operating Profit & Accounting Disclosures -->
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-5">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">13. Operating Results</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        Operating Margin: ${{ number_format($operatingProfit, 2) }} USD
                    </h3>
                </div>
                <div class="text-xs bg-gray-50 dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 max-w-xl">
                    <strong class="text-gray-900 dark:text-white">Accounting Basis & Integrity Disclosure:</strong>
                    <div class="text-gray-600 dark:text-gray-400 mt-0.5">
                        Calculated strictly from posted double-entry journal entries (Revenue $4,010 minus Direct Costs $5,010 and Operating Expenses $6,000s). 
                        Historical unverified spreadsheet items are retained in review staging and <em>do not silently fabricate operating profit</em>.
                    </div>
                </div>
            </div>

            <!-- Recent Invoices & Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- Recent Invoices -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Recent Issued Invoices</h4>
                        <a href="{{ route('filament.admin.resources.accounting-invoices.index') }}" class="text-xs text-primary-600 font-semibold hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800 border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                        @forelse($recentInvoices as $inv)
                            <div class="p-3 bg-white dark:bg-gray-900 flex items-center justify-between text-xs">
                                <div>
                                    <strong class="text-gray-900 dark:text-white font-mono">{{ $inv->invoice_number }}</strong>
                                    <span class="text-gray-500"> — {{ $inv->client->display_name }}</span>
                                    <div class="text-[11px] text-gray-400">{{ $inv->issue_date->format('Y-m-d') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900 dark:text-white">${{ number_format($inv->total_amount, 2) }}</div>
                                    <span class="text-[10px] uppercase font-bold {{ $inv->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $inv->payment_status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-gray-400">No invoices issued yet.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Recent Ledger Entries & Movements</h4>
                        <a href="{{ route('filament.admin.resources.accounting-transactions.index') }}" class="text-xs text-primary-600 font-semibold hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800 border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                        @forelse($recentTransactions as $trx)
                            <div class="p-3 bg-white dark:bg-gray-900 flex items-center justify-between text-xs">
                                <div>
                                    <strong class="text-gray-900 dark:text-white">{{ $trx->transaction_type_label }}</strong>
                                    <div class="text-[11px] text-gray-400">{{ $trx->notes ?: ($trx->payer_name ?: $trx->transaction_number) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900 dark:text-white">${{ number_format($trx->amount, 2) }}</div>
                                    <span class="text-[10px] text-gray-400">{{ $trx->transaction_date->format('Y-m-d') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-gray-400">No recent transactions recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
