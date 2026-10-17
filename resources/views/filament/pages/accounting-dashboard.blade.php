<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        <!-- 1. Header & Cutover Status Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-l from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 shadow-sm relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                            نقطة انتقال الحسابات: 13 أكتوبر 2026
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                            الأرصدة مؤكدة من الإدارة
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                        نظام المحاسبة الداخلية وإدارة أموال الشركاء
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-3xl">
                        تم فصل أموال الشركة في الصندوق عن ديون التمويل الشخصي وسحوبات الشركاء. رصيد دين عبدالله المؤكد: <strong class="text-amber-600 dark:text-amber-400">1,277$</strong>، رصيد دين عامر المؤكد: <strong class="text-amber-600 dark:text-amber-400">1,277$</strong> (صافي بعد استرداد 1,000$). النقد التقريبي المبلّغ عنه: <strong class="text-emerald-600 dark:text-emerald-400">250$</strong>.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <x-filament::button tag="a" :href="route('filament.admin.resources.accounting-transactions.create')" color="primary" icon="heroicon-m-plus">
                        حركة مالية جديدة
                    </x-filament::button>
                    <x-filament::button tag="a" :href="route('filament.admin.pages.accounting-import-page')" color="gray" icon="heroicon-m-arrow-up-tray">
                        استيراد Excel
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- 2. Primary KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Company Cash In Hand -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">نقدية الصندوق (المتاح)</span>
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        ${{ number_format($cashBalance, 2) }}
                    </div>
                    <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>مبلغ مبلّغ عنه: ${{ number_format($reportedCash, 2) }} (جرد تقريبي 13 تشرين الأول)</span>
                    </div>
                </div>
            </div>

            <!-- Total Partner Debts -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي ديون الشركاء</span>
                    <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-user-group class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                        ${{ number_format($totalPartnerDebt, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                        <span>عبدالله: ${{ number_format($abdullahDebt, 2) }}</span>
                        <span>عامر: ${{ number_format($amerDebt, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Total Client Revenue -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">تحصيلات وإيرادات المشاريع</span>
                    <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-arrow-trending-up class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        ${{ number_format($totalRevenue, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>مستحقات محتملة للعملاء: ${{ number_format($potentialReceivables, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Operating Expenses & Direct Costs -->
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">المصاريف وتكاليف المشاريع</span>
                    <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <x-heroicon-o-receipt-percent class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">
                        ${{ number_format($totalOperatingExpenses + $totalDirectCosts, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                        <span>تشغيلية: ${{ number_format($totalOperatingExpenses, 2) }}</span>
                        <span>مشاريع: ${{ number_format($totalDirectCosts, 2) }}</span>
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
                            كشف حسابات الشريكين المؤكدة
                        </h3>
                        <a href="{{ route('filament.admin.resources.accounting-partners.index') }}" class="text-xs text-amber-600 dark:text-amber-400 hover:underline">
                            عرض التفاصيل
                        </a>
                    </div>
                    <div class="space-y-4 mt-4">
                        <!-- Abdullah -->
                        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900 dark:text-white">عبدالله الساسا</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">${{ number_format($abdullahDebt, 2) }}</span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 flex justify-between">
                                <span>دين مستحق على الشركة</span>
                                <span>سحوبات / توزيعات: ${{ number_format($abdullahDrawings, 2) }}</span>
                            </div>
                        </div>

                        <!-- Amer -->
                        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900 dark:text-white">عامر</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">${{ number_format($amerDebt, 2) }}</span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 flex justify-between">
                                <span>دين مستحق على الشركة (صافي)</span>
                                <span>سحوبات / توزيعات: ${{ number_format($amerDrawings, 2) }}</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-xs text-amber-800 dark:text-amber-300">
                            <strong>ملاحظة محاسبية:</strong> تم استرداد 1,000$ لعامر بتاريخ 18 آب 2026 وأُدرجت في رصيده الصافي؛ كما تم تضمين التسوية الشخصية (200-210$) في الرصيدين، فلا يُعاد خصمها.
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
                            الربح التشغيلي وحالة الاكتمال
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $pendingReviewCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $pendingReviewCount > 0 ? 'بيانات قيد المراجعة' : 'مكتمل ومطابق' }}
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">صافي الربح التشغيلي المحسوب:</div>
                        <div class="text-3xl font-extrabold mt-1 {{ $operatingProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            ${{ number_format($operatingProfit, 2) }}
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            أساس الحساب: الإيرادات المحققة (${{ number_format($totalRevenue, 2) }}) ناقص التكاليف المباشرة (${{ number_format($totalDirectCosts, 2) }}) والمصاريف التشغيلية (${{ number_format($totalOperatingExpenses, 2) }}).
                        </div>

                        <div class="mt-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <div>• رأس المال التأسيسي 700$ مسجل كحقوق ملكية وصُرف لاحقاً.</div>
                            <div>• حصص الشركاء (4,798$) معلقة بالمراجعة كـسحوبات أو أجور.</div>
                            <div>• فارق الإكسل القديم (−254.51$) لا يُعتبر خسارة حقيقية.</div>
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
                            طابور المراجعة والمطابقة
                        </h3>
                        <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            عرض الكل ({{ $pendingReviewCount }})
                        </a>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-rose-500" />
                                <span class="text-xs font-semibold text-rose-800 dark:text-rose-300">حركات تحتاج قراراً إدارياً</span>
                            </div>
                            <span class="text-sm font-bold text-rose-600 dark:text-rose-400">{{ $pendingReviewCount }}</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 space-y-1.5">
                            <div class="font-semibold text-gray-800 dark:text-gray-200">أهم الملفات في المراجعة:</div>
                            <div>1. تحديد طبيعة حصص الشركاء (4,798$ سحوبات أم أجور تنفيذ).</div>
                            <div>2. إيجار المكتب (1,000$ في الإكسل مقابل 2,000$ شفهياً).</div>
                            <div>3. توثيق دفعتي Sheet1 لعبدالله (1,075$ × 2).</div>
                            <div>4. دفعات عثمان المستقلة (16.21$ و 4.00$).</div>
                            <div>5. تسوية مستحقات FRS وعطور عبدالله (85$).</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-medium text-xs transition border border-indigo-200 dark:border-indigo-800">
                        فتح شاشة البت في الحركات المعلقة
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. Quick Review Queue Table -->
        @if(count($pendingReviews) > 0)
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">حركات معلقة تتطلب اعتماد القرار</h3>
                    <p class="text-xs text-gray-500 mt-0.5">الحركات المستوردة من الإكسل والموضوعة في المراجعة لعدم التسرع في تصنيفها</p>
                </div>
                <a href="{{ route('filament.admin.resources.accounting-reviews.index') }}" class="text-xs font-medium text-amber-600 dark:text-amber-400 hover:underline">
                    فتح شاشة المطابقة الكاملة &larr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">الورقة / الصف</th>
                            <th class="p-3.5">البيان / الخدمة</th>
                            <th class="p-3.5">المبلغ</th>
                            <th class="p-3.5">الدافع في الملف</th>
                            <th class="p-3.5">سبب التعليق للمراجعة</th>
                            <th class="p-3.5">التصنيف المقترح</th>
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
                            <td class="p-3.5 font-medium text-gray-900 dark:text-white">
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
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">أحدث القيود والحركات المحاسبية المسجلة</h3>
                    <p class="text-xs text-gray-500 mt-0.5">الحركات المنفذة فعلياً ذات القيود المحاسبية المتوازنة خلف الكواليس</p>
                </div>
                <a href="{{ route('filament.admin.resources.accounting-transactions.index') }}" class="text-xs font-medium text-amber-600 dark:text-amber-400 hover:underline">
                    عرض جميع الحركات &larr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">رقم الحركة</th>
                            <th class="p-3.5">التاريخ</th>
                            <th class="p-3.5">نوع الحركة</th>
                            <th class="p-3.5">المبلغ (USD)</th>
                            <th class="p-3.5">الشريك / العميل</th>
                            <th class="p-3.5">مصدر الأموال</th>
                            <th class="p-3.5">البيان والملاحظات</th>
                            <th class="p-3.5">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                            <td class="p-3.5 font-mono text-gray-500">{{ $trx->transaction_number }}</td>
                            <td class="p-3.5">{{ $trx->transaction_date->format('Y-m-d') }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full font-semibold text-xs
                                    @if($trx->transaction_type === 'client_collection') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @elseif($trx->transaction_type === 'partner_funding') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                    @elseif($trx->transaction_type === 'partner_personal_expense') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                                    @elseif($trx->transaction_type === 'partner_debt_repayment') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300
                                    @elseif($trx->transaction_type === 'partner_distribution') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 @endif">
                                    {{ match($trx->transaction_type) {
                                        'client_collection' => 'تحصيل من عميل',
                                        'partner_funding' => 'تمويل شريك للصندوق',
                                        'partner_personal_expense' => 'مصروف شخصي للشريك',
                                        'company_expense' => 'مصروف من أموال الشركة',
                                        'partner_debt_repayment' => 'سداد دين لشريك',
                                        'partner_distribution' => 'سحب شريك / توزيع أرباح',
                                        'capital_injection' => 'رأس مال تأسيسي',
                                        'partner_project_labor' => 'أجر تنفيذ مشروع للشريك',
                                        default => $trx->transaction_type
                                    } }}
                                </span>
                            </td>
                            <td class="p-3.5 font-bold text-gray-900 dark:text-white">
                                ${{ number_format((float)$trx->amount, 2) }}
                            </td>
                            <td class="p-3.5">
                                {{ $trx->partner?->name_ar ?: ($trx->client?->name ?: '-') }}
                            </td>
                            <td class="p-3.5">
                                {{ match($trx->money_source) {
                                    'company_funds' => 'صندوق الشركة',
                                    'personal_abdullah' => 'مال عبدالله الشخصي',
                                    'personal_amer' => 'مال عامر الشخصي',
                                    'othman' => 'عثمان',
                                    default => $trx->money_source
                                } }}
                            </td>
                            <td class="p-3.5 max-w-xs truncate text-gray-600 dark:text-gray-400">
                                {{ $trx->notes ?: '-' }}
                            </td>
                            <td class="p-3.5">
                                @if($trx->status === 'reversed')
                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 text-xs">معكوس</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs">معتمد ومرحل</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-gray-500">
                                لا توجد حركات مسجلة حتى الآن. يمكنك إضافة حركة جديدة أو استيراد البيانات من الإكسل.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
