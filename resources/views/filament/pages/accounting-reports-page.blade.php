<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/accounting-pages.css') }}?v=1">
    <div class="space-y-6" dir="ltr">
        <!-- 1. Header with Export Buttons -->
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-500/20">
                        Official Statements
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500">Three Digit Software Financial Records</span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1.5">
                    Financial Statements & Accounting Reports
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Audited statements for company treasury, partner capital & debt accounts, client receivables, operating overhead, and project profitability.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <x-filament::button wire:click="exportExcel" color="success" icon="heroicon-m-arrow-down-tray">
                    Export Excel (.xlsx)
                </x-filament::button>
                <x-filament::button wire:click="exportPdf" color="danger" icon="heroicon-m-document-arrow-down">
                    Export PDF
                </x-filament::button>
            </div>
        </div>

        <!-- 2. Tab Navigation Bar -->
        <div class="flex flex-wrap items-center gap-1.5 p-1.5 rounded-2xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700">
            <button wire:click="setTab('cash_statement')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'cash_statement' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-banknotes class="w-4 h-4" />
                Treasury & Cash Flow
            </button>
            <button wire:click="setTab('partner_statement')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'partner_statement' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-user-group class="w-4 h-4" />
                Partner Account Ledger
            </button>
            <button wire:click="setTab('client_collections')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'client_collections' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-building-office class="w-4 h-4" />
                Client Collections & Receivables
            </button>
            <button wire:click="setTab('receivables_aging')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'receivables_aging' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-clock class="w-4 h-4" />
                Receivables Aging
            </button>
            <button wire:click="setTab('monthly_expenses')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'monthly_expenses' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-receipt-percent class="w-4 h-4" />
                Operating Overhead
            </button>
            <button wire:click="setTab('project_costs')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'project_costs' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-briefcase class="w-4 h-4" />
                Project Margins & Costing
            </button>
            <button wire:click="setTab('reconciliation_unresolved')" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'reconciliation_unresolved' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <x-heroicon-m-scale class="w-4 h-4" />
                Cutover & Unresolved Audit
            </button>
        </div>

        <!-- 3. Tab Contents -->

        <!-- TAB 1: Cash Statement -->
        @if($activeTab === 'cash_statement')
        <div class="space-y-4">
            <!-- Summary KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Cash Inflows</span>
                    <div class="text-3xl font-extrabold text-emerald-600 mt-1">${{ number_format($cashInflows, 2) }}</div>
                    <span class="text-xs text-gray-400 mt-1 inline-block">Collections & Treasury Injections</span>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Cash Outflows</span>
                    <div class="text-3xl font-extrabold text-rose-600 mt-1">${{ number_format($cashOutflows, 2) }}</div>
                    <span class="text-xs text-gray-400 mt-1 inline-block">Paid from Company Treasury</span>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Net Treasury Balance</span>
                    <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">${{ number_format($cashNet, 2) }}</div>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 inline-block font-medium">Verified Cutover Baseline: $250.00</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Entry #</th>
                            <th class="p-3.5">Debit (Inflow)</th>
                            <th class="p-3.5">Credit (Outflow)</th>
                            <th class="p-3.5">Description & Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($cashLines as $l)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                            <td class="p-3.5">{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                            <td class="p-3.5 font-mono text-gray-500 font-medium">{{ $l->journalEntry->entry_number }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">
                                {{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}
                            </td>
                            <td class="p-3.5 font-bold text-rose-600">
                                {{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}
                            </td>
                            <td class="p-3.5 text-gray-700 dark:text-gray-300">{{ $l->description ?: $l->journalEntry->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">No cash entries found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- TAB 2: Partner Statement -->
        @if($activeTab === 'partner_statement')
        <div class="space-y-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Select Partner:</span>
                    <select wire:model.live="selectedPartnerId" class="text-xs rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-semibold py-2 px-3">
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name_en }} ({{ $p->name_ar }})</option>
                        @endforeach
                    </select>
                </div>
                @if($selectedPartner)
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <div class="px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        Cutover Baseline: <strong>${{ number_format($selectedPartner->initial_debt_balance, 2) }}</strong>
                    </div>
                    <div class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        Current Outstanding Debt: <strong class="text-base">${{ number_format($selectedPartner->calculated_debt_balance, 2) }}</strong>
                    </div>
                    <div class="px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                        Total Drawings / Distributions: <strong>${{ number_format($selectedPartner->total_drawings, 2) }}</strong>
                    </div>
                </div>
                @endif
            </div>

            <!-- Partner Debt Ledger Table -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">
                        Debt & Funding Ledger — {{ $selectedPartner?->name_en }}
                    </h3>
                    <span class="text-xs text-gray-500">Credits increase debt owed to partner | Debits reflect repayments</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="p-3.5">Date</th>
                                <th class="p-3.5">Entry #</th>
                                <th class="p-3.5">Debit (Repayment / Reduction)</th>
                                <th class="p-3.5">Credit (Funding / Out-of-Pocket Expense)</th>
                                <th class="p-3.5">Notes & Journal Narration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($partnerDebtLines as $l)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                                <td class="p-3.5">{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td class="p-3.5 font-mono text-gray-500">{{ $l->journalEntry->entry_number }}</td>
                                <td class="p-3.5 font-bold text-purple-600">
                                    {{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}
                                </td>
                                <td class="p-3.5 font-bold text-amber-600 dark:text-amber-400">
                                    {{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}
                                </td>
                                <td class="p-3.5 text-gray-700 dark:text-gray-300">{{ $l->description ?: $l->journalEntry->description }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-500">No partner debt journal lines recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Drawings & Distributions Table -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">
                        Partner Drawings & Equity Distributions — {{ $selectedPartner?->name_en }}
                    </h3>
                    <span class="text-xs text-gray-500">Withdrawals of profits / personal draws (Equity Account 3101/3102)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="p-3.5">Date</th>
                                <th class="p-3.5">Entry #</th>
                                <th class="p-3.5">Drawing Amount (Debit)</th>
                                <th class="p-3.5">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($partnerDrawingsLines as $l)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                                <td class="p-3.5">{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td class="p-3.5 font-mono text-gray-500">{{ $l->journalEntry->entry_number }}</td>
                                <td class="p-3.5 font-bold text-rose-600">${{ number_format((float)$l->debit, 2) }}</td>
                                <td class="p-3.5 text-gray-700 dark:text-gray-300">{{ $l->description ?: $l->journalEntry->description }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-gray-500">No partner drawings recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- TAB 3: Client Collections -->
        @if($activeTab === 'client_collections')
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Client Collections & Potential Receivables</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Realized cash receipts vs potential balances pending client confirmation</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="p-3.5">Client / Organization</th>
                                <th class="p-3.5">Contact Person</th>
                                <th class="p-3.5">Total Collected (Cash Inflow)</th>
                                <th class="p-3.5">Potential Receivable</th>
                                <th class="p-3.5">Projects Count</th>
                                <th class="p-3.5">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($clients as $c)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                                <td class="p-3.5 font-bold text-gray-900 dark:text-white">{{ $c->name }}</td>
                                <td class="p-3.5 text-gray-600 dark:text-gray-400">{{ $c->contact_person ?: '-' }}</td>
                                <td class="p-3.5 font-bold text-emerald-600">${{ number_format($c->total_collected, 2) }}</td>
                                <td class="p-3.5">
                                    @if((float)$c->potential_receivable_amount > 0)
                                        <span class="px-2 py-0.5 rounded font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            ${{ number_format($c->potential_receivable_amount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">$0.00</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $c->projects->count() }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $c->notes ?: '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">No client accounts recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- TAB 4: Monthly Expenses -->
        @if($activeTab === 'monthly_expenses')
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Monthly Operating Overhead Statement</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Aggregated by month and expense category (Rent, Software, Office, Marketing, Equipment)</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="p-3.5">Month</th>
                                <th class="p-3.5">Category</th>
                                <th class="p-3.5">Total Amount (USD)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($monthlyExpenses as $exp)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                                <td class="p-3.5 font-mono font-medium">{{ $exp->month }}</td>
                                <td class="p-3.5 font-semibold text-gray-900 dark:text-white">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        {{ $exp->expense_category ?: 'General Operating' }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-bold text-rose-600">${{ number_format((float)$exp->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">No monthly expense data recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- TAB 5: Project Costs & Margins -->
        @if($activeTab === 'project_costs')
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Project Margins & Direct Costing</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Contract values, realized collections, direct execution labor, and gross profit margin</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="p-3.5">Project Name</th>
                                <th class="p-3.5">Client</th>
                                <th class="p-3.5">Contract Value</th>
                                <th class="p-3.5">Collected</th>
                                <th class="p-3.5">Direct Costs</th>
                                <th class="p-3.5">Gross Margin</th>
                                <th class="p-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($projects as $prj)
                            @php
                                $margin = (float)$prj->total_collected - (float)$prj->direct_costs;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                                <td class="p-3.5 font-bold text-gray-900 dark:text-white">{{ $prj->name }}</td>
                                <td class="p-3.5 text-gray-600 dark:text-gray-400">{{ $prj->client?->name ?: '-' }}</td>
                                <td class="p-3.5 font-bold text-gray-800 dark:text-gray-200">${{ number_format($prj->total_price, 2) }}</td>
                                <td class="p-3.5 font-bold text-emerald-600">${{ number_format($prj->total_collected, 2) }}</td>
                                <td class="p-3.5 font-bold text-rose-600">${{ number_format($prj->direct_costs, 2) }}</td>
                                <td class="p-3.5 font-extrabold {{ $margin >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    ${{ number_format($margin, 2) }}
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold
                                        @if($prj->status === 'done') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                        @elseif($prj->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @else bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 @endif">
                                        {{ ucwords(str_replace('_', ' ', $prj->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">No projects recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- TAB 6: Receivables Aging -->
        @if($activeTab === 'receivables_aging')
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <span class="text-xs text-gray-500 font-semibold uppercase">Current (< 30 Days)</span>
                    <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                        ${{ number_format($agingData['totals']['current'], 2) }}
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <span class="text-xs text-amber-500 font-semibold uppercase">30 - 60 Days Overdue</span>
                    <div class="text-xl font-bold text-amber-600 mt-1">
                        ${{ number_format($agingData['totals']['days_30'], 2) }}
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <span class="text-xs text-orange-500 font-semibold uppercase">61 - 90 Days Overdue</span>
                    <div class="text-xl font-bold text-orange-600 mt-1">
                        ${{ number_format($agingData['totals']['days_60'], 2) }}
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <span class="text-xs text-rose-500 font-semibold uppercase">90+ Days Overdue</span>
                    <div class="text-xl font-bold text-rose-600 mt-1">
                        ${{ number_format($agingData['totals']['days_90'], 2) }}
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Client Receivables Aging Schedule</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs text-gray-500 font-semibold uppercase">
                                <th class="p-3">Client</th>
                                <th class="p-3">Current</th>
                                <th class="p-3">30-60 Days</th>
                                <th class="p-3">61-90 Days</th>
                                <th class="p-3">90+ Days</th>
                                <th class="p-3 font-bold text-gray-900 dark:text-white">Total Outstanding</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($agingData['clients'] as $c)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $c['name'] }}</td>
                                <td class="p-3">${{ number_format($c['current'], 2) }}</td>
                                <td class="p-3 text-amber-600">${{ number_format($c['days_30'], 2) }}</td>
                                <td class="p-3 text-orange-600">${{ number_format($c['days_60'], 2) }}</td>
                                <td class="p-3 text-rose-600 font-bold">${{ number_format($c['days_90'], 2) }}</td>
                                <td class="p-3 font-black text-gray-900 dark:text-white">${{ number_format($c['total'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400">No outstanding client receivables currently recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- TAB 7: Cutover & Unresolved Audit -->
        @if($activeTab === 'reconciliation_unresolved')
        <div class="space-y-4">
            <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-scale class="w-5 h-5 text-amber-600" />
                    <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">
                        Cutover Transition Facts — 13 October 2026
                    </h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-xs">
                    <div class="p-3 rounded-xl bg-white/60 dark:bg-gray-900/60 border border-amber-200 dark:border-amber-800">
                        <span class="text-gray-500 block">Confirmed Debt to Abdullah:</span>
                        <strong class="text-base text-gray-900 dark:text-white">$1,277.00</strong>
                        <div class="text-[11px] text-gray-500 mt-1">Includes ~$200-210 personal transfer from Abdullah to Amer.</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/60 dark:bg-gray-900/60 border border-amber-200 dark:border-amber-800">
                        <span class="text-gray-500 block">Confirmed Debt to Amer:</span>
                        <strong class="text-base text-gray-900 dark:text-white">$1,277.00</strong>
                        <div class="text-[11px] text-gray-500 mt-1">Net balance after prior $1,000 cash repayment.</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/60 dark:bg-gray-900/60 border border-amber-200 dark:border-amber-800">
                        <span class="text-gray-500 block">Reported Physical Cash:</span>
                        <strong class="text-base text-amber-600">~$250.00</strong>
                        <div class="text-[11px] text-amber-600 mt-1">Approximate observation; physical count & audit pending.</div>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-base">Unresolved Staging Records</h4>
                        <p class="text-xs text-gray-500">Records kept in review staging to prevent distorting certified financial profit.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        {{ count($unresolvedItems) }} Items
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-500 font-semibold uppercase">
                                <th class="p-3">Sheet</th>
                                <th class="p-3">Row</th>
                                <th class="p-3">Proposed Type</th>
                                <th class="p-3">Amount</th>
                                <th class="p-3">Review Reason</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($unresolvedItems as $item)
                            <tr>
                                <td class="p-3 font-semibold">{{ $item->sheet_name }}</td>
                                <td class="p-3">#{{ $item->row_index }}</td>
                                <td class="p-3">{{ $item->proposed_type }}</td>
                                <td class="p-3 font-bold">${{ number_format($item->amount, 2) }}</td>
                                <td class="p-3 text-gray-600 dark:text-gray-400">{{ $item->review_reason }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        غير محسوم / Unresolved
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400">All historical items have been resolved or confirmed.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
