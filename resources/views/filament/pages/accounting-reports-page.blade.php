<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        <!-- 1. Header with Export Buttons -->
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    التقارير والقوائم المالية الرسمية
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    كشوفات تفصيلية للصندوق، حسابات الشركاء، مستحقات العملاء، المصاريف الشهرية، وأرباح المشاريع.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-filament::button wire:click="exportExcel" color="success" icon="heroicon-m-arrow-down-tray">
                    تصدير Excel (.xlsx)
                </x-filament::button>
                <x-filament::button wire:click="exportPdf" color="danger" icon="heroicon-m-document-arrow-down">
                    تصدير PDF
                </x-filament::button>
            </div>
        </div>

        <!-- 2. Tab Navigation Bar -->
        <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700">
            <button wire:click="setTab('cash_statement')" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'cash_statement' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                كشف حركة الصندوق
            </button>
            <button wire:click="setTab('partner_statement')" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'partner_statement' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                كشف حساب شريك
            </button>
            <button wire:click="setTab('client_collections')" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'client_collections' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                تحصيلات العملاء والمشاريع
            </button>
            <button wire:click="setTab('monthly_expenses')" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'monthly_expenses' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                المصاريف الشهرية
            </button>
            <button wire:click="setTab('project_costs')" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $activeTab === 'project_costs' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                تكاليف وأرباح المشاريع
            </button>
        </div>

        <!-- 3. Tab Contents -->

        <!-- TAB 1: Cash Statement -->
        @if($activeTab === 'cash_statement')
        <div class="space-y-4">
            <!-- Summary KPI -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                    <span class="text-xs text-gray-500">إجمالي المقبوضات (وارد)</span>
                    <div class="text-2xl font-bold text-emerald-600 mt-1">${{ number_format($cashInflows, 2) }}</div>
                </div>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                    <span class="text-xs text-gray-500">إجمالي المدفوعات (صادر)</span>
                    <div class="text-2xl font-bold text-rose-600 mt-1">${{ number_format($cashOutflows, 2) }}</div>
                </div>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                    <span class="text-xs text-gray-500">صافي رصيد الصندوق الحالي</span>
                    <div class="text-2xl font-extrabold text-amber-600 mt-1">${{ number_format($cashNet, 2) }}</div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">التاريخ</th>
                            <th class="p-3.5">رقم القيد</th>
                            <th class="p-3.5">وارد (مدين)</th>
                            <th class="p-3.5">صادر (دائن)</th>
                            <th class="p-3.5">البيان والتفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($cashLines as $l)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3.5">{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                            <td class="p-3.5 font-mono text-gray-500">{{ $l->journalEntry->entry_number }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">
                                {{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}
                            </td>
                            <td class="p-3.5 font-bold text-rose-600">
                                {{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}
                            </td>
                            <td class="p-3.5">{{ $l->description ?: $l->journalEntry->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">لا توجد حركات نقدية مسجلة</td>
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
            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">اختر الشريك:</span>
                    <select wire:model.live="selectedPartnerId" class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-semibold">
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name_ar }} ({{ $p->name_en }})</option>
                        @endforeach
                    </select>
                </div>
                @if($selectedPartner)
                <div class="flex items-center gap-4 text-xs">
                    <span>رصيد الدين المؤكد عند الانتقال: <strong>${{ number_format($selectedPartner->initial_debt_balance, 2) }}</strong></span>
                    <span>صافي الدين المستحق حالياً: <strong class="text-amber-600 dark:text-amber-400">${{ number_format($selectedPartner->calculated_debt_balance, 2) }}</strong></span>
                    <span>إجمالي السحوبات والتوزيعات: <strong class="text-purple-600">${{ number_format($selectedPartner->total_drawings, 2) }}</strong></span>
                </div>
                @endif
            </div>

            <!-- Partner Debt Ledger Table -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-3.5 bg-gray-50 dark:bg-gray-800 font-bold text-xs text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-800">
                    كشف حركات دين الشريك (تمويل، مصاريف شخصية، سداد)
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="p-3">التاريخ</th>
                                <th class="p-3">رقم القيد</th>
                                <th class="p-3">مدين (سداد مسترد للشريك)</th>
                                <th class="p-3">دائن (تمويل / مصروف شخصي)</th>
                                <th class="p-3">البيان</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($partnerDebtLines as $l)
                            <tr>
                                <td class="p-3">{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td class="p-3 font-mono text-gray-500">{{ $l->journalEntry->entry_number }}</td>
                                <td class="p-3 font-bold text-emerald-600">
                                    {{ (float)$l->debit > 0 ? '$' . number_format((float)$l->debit, 2) : '-' }}
                                </td>
                                <td class="p-3 font-bold text-amber-600">
                                    {{ (float)$l->credit > 0 ? '$' . number_format((float)$l->credit, 2) : '-' }}
                                </td>
                                <td class="p-3">{{ $l->description }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-5 text-center text-gray-500">لا توجد حركات مسجلة على حساب الدين بعد نقطة الانتقال</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Partner Drawings Ledger Table -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="p-3.5 bg-purple-50 dark:bg-purple-950/20 font-bold text-xs text-purple-900 dark:text-purple-300 border-b border-purple-200 dark:border-purple-900/30">
                    كشف سحوبات وتوزيعات الشريك (منفصلة تماماً عن دين التمويل)
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="p-3">التاريخ</th>
                                <th class="p-3">رقم القيد</th>
                                <th class="p-3">مبلغ السحب / التوزيع</th>
                                <th class="p-3">البيان</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($partnerDrawingsLines as $l)
                            <tr>
                                <td class="p-3">{{ $l->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td class="p-3 font-mono text-gray-500">{{ $l->journalEntry->entry_number }}</td>
                                <td class="p-3 font-bold text-purple-600">
                                    ${{ number_format((float)$l->debit, 2) }}
                                </td>
                                <td class="p-3">{{ $l->description }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-5 text-center text-gray-500">لا توجد سحوبات مسجلة للشريك</td>
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
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">العميل</th>
                            <th class="p-3.5">المشاريع</th>
                            <th class="p-3.5">المقبوض فعلياً</th>
                            <th class="p-3.5">مستحقات محتملة (تحتاج تأكيد)</th>
                            <th class="p-3.5">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($clients as $c)
                        <tr>
                            <td class="p-3.5 font-bold text-gray-900 dark:text-white">{{ $c->name }}</td>
                            <td class="p-3.5">{{ $c->projects->pluck('name')->implode(', ') ?: '-' }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">
                                ${{ number_format((float) \App\Models\AccountingTransaction::where('client_id', $c->id)->where('transaction_type', 'client_collection')->sum('amount'), 2) }}
                            </td>
                            <td class="p-3.5">
                                @if((float)$c->potential_receivable_amount > 0)
                                    <span class="px-2 py-0.5 rounded font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        ${{ number_format((float)$c->potential_receivable_amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-gray-500">{{ $c->notes ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- TAB 4: Monthly Expenses -->
        @if($activeTab === 'monthly_expenses')
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">الشهر</th>
                            <th class="p-3.5">التصنيف</th>
                            <th class="p-3.5">إجمالي المبلغ (USD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($monthlyExpenses as $me)
                        <tr>
                            <td class="p-3.5 font-mono font-bold">{{ $me->month }}</td>
                            <td class="p-3.5 font-medium">{{ $me->expense_category ?: 'أخرى' }}</td>
                            <td class="p-3.5 font-bold text-rose-600">${{ number_format((float)$me->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-5 text-center text-gray-500">لا توجد مصاريف مسجلة بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- TAB 5: Project Costs & Profitability -->
        @if($activeTab === 'project_costs')
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">المشروع</th>
                            <th class="p-3.5">العميل</th>
                            <th class="p-3.5">قيمة العقد</th>
                            <th class="p-3.5">المقبوض فعلياً</th>
                            <th class="p-3.5">المتبقي على العقد</th>
                            <th class="p-3.5">التكاليف المباشرة</th>
                            <th class="p-3.5">هامش الربح المتوقع</th>
                            <th class="p-3.5">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($projects as $p)
                        <tr>
                            <td class="p-3.5 font-bold text-gray-900 dark:text-white">{{ $p->name }}</td>
                            <td class="p-3.5">{{ $p->client?->name ?: '-' }}</td>
                            <td class="p-3.5 font-bold">${{ number_format((float)$p->total_price, 2) }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">${{ number_format($p->total_collected, 2) }}</td>
                            <td class="p-3.5">${{ number_format($p->remaining_balance, 2) }}</td>
                            <td class="p-3.5 text-rose-600">${{ number_format((float)$p->direct_costs, 2) }}</td>
                            <td class="p-3.5 font-bold text-blue-600">${{ number_format($p->net_profit, 2) }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $p->status === 'done' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'cancelled' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
