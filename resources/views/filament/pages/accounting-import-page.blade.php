<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        <!-- 1. Header & Source Selector -->
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        استيراد ومطابقة بيانات ملف Excel
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        تقوم الأداة بقراءة أوراق العمل (Income, Expenses, Projects, Sheet1)، مع منع تكرار الحركات عبر البصمة الرقمية (Fingerprint Hash)، وتوجيه الحركات غير المحسومة إلى طابور المراجعة دون حذف.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <x-filament::button wire:click="executeImport" color="primary" icon="heroicon-m-check-badge">
                        بدء اعتماد الاستيراد
                    </x-filament::button>
                </div>
            </div>

            <!-- Upload new or use system file -->
            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl border {{ $selectedSource === 'system_file' ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-950/20' : 'border-gray-200 dark:border-gray-800' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">ملف النظام المرفق</span>
                            <p class="text-xs text-gray-500 font-mono mt-0.5">Three Digit Software - Accounting Dashboard.xlsx</p>
                        </div>
                        <button wire:click="loadSystemFilePreview" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            معاينة الملف المرفق
                        </button>
                    </div>
                </div>

                <div class="p-4 rounded-xl border {{ $selectedSource === 'upload' ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-950/20' : 'border-gray-200 dark:border-gray-800' }}">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">رفع ملف إكسل بديل (.xlsx)</label>
                    <div class="flex items-center gap-2">
                        <input type="file" wire:model="uploadedFile" accept=".xlsx,.xls" class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                        <button wire:click="handleFileUpload" wire:loading.attr="disabled" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition">
                            فحص ومعاينة
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Import Result Alert (if run) -->
        @if($importResult)
        <div class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-emerald-500 text-white">
                    <x-heroicon-m-check-circle class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="font-bold text-emerald-900 dark:text-emerald-300 text-base">تقرير عملية الاستيراد رقم: {{ $importResult['batch_number'] }}</h3>
                    <div class="flex flex-wrap items-center gap-4 mt-1 text-xs text-emerald-800 dark:text-emerald-400">
                        <span>• حركات معتمدة: <strong>{{ $importResult['imported_rows'] }}</strong></span>
                        <span>• حركات محولة للمراجعة: <strong>{{ $importResult['flagged_rows'] }}</strong></span>
                        <span>• حركات مكررة تم تجنبها بأمان: <strong>{{ $importResult['duplicate_rows'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 3. Preview Stats & Integrity Indicators -->
        @if($previewData && isset($previewData['stats']))
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                <span class="text-xs text-gray-500">إجمالي الصفوف المفحوصة</span>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $previewData['stats']['total_rows'] }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                <span class="text-xs text-gray-500">إيرادات Income (16 صف)</span>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($previewData['stats']['income_total_amount'], 2) }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                <span class="text-xs text-gray-500">مصاريف Expenses (78 صف)</span>
                <div class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1">${{ number_format($previewData['stats']['expenses_total_amount'], 2) }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                <span class="text-xs text-gray-500">المشاريع المفعلة</span>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $previewData['stats']['projects_count'] }} مشاريع</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                <span class="text-xs text-gray-500">حركات تتطلب المراجعة</span>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $previewData['stats']['pending_review_count'] }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center">
                <span class="text-xs text-gray-500">حركات مكررة بالبصمة</span>
                <div class="text-xl font-bold {{ $previewData['stats']['duplicate_count'] > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400' }} mt-1">
                    {{ $previewData['stats']['duplicate_count'] }}
                </div>
            </div>
        </div>

        <!-- 4. Column Mapping & Handling Rules Summary -->
        <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 space-y-2">
            <h4 class="font-bold text-gray-900 dark:text-white text-sm">قواعد المطابقة الآلية المطبقة أثناء الاستيراد:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong>1. صفوف التأسيس والرأسمال:</strong> صفا amer و abdullah بمبلغ صفر وملاحظة 1,277 يعاملان كمعلومات ديون شركاء تاريخية، ولا يُنشأ بهما فواتير غير مدفوعة. مبلغ 700$ التأسيسي يدرج كرأس مال صُرف لاحقاً.
                </div>
                <div class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong>2. سداد الألف لعامر:</strong> حركة 18 آب 2026 (Rent 1,000$ company "for amer") أعيد تصنيفها كسداد دين لعامر، دون خصمها مجدداً من رصيده المؤكد الحالي (1,277$).
                </div>
                <div class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong>3. حصص الشركاء (4,798$):</strong> حصص الإكسل في Salaries وضعت بطابور المراجعة لعدم الخلط بين التوزيعات وأجور التنفيذ.
                </div>
                <div class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong>4. منع التكرار (Deduplication):</strong> تحسب بصمة SHA-256 لكل صف؛ إعادة رفع الملف أو تكراره لن تضيف صفوفاً مكررة إلى الحسابات.
                </div>
            </div>
        </div>

        <!-- 5. Sample Preview Tables -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-900 dark:text-white text-base">معاينة تفصيلية لبيانات ورقة المصاريف (Expenses Sample)</h3>
            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3">الصف</th>
                            <th class="p-3">التاريخ</th>
                            <th class="p-3">التصنيف</th>
                            <th class="p-3">الجهة / الخدمة</th>
                            <th class="p-3">المبلغ</th>
                            <th class="p-3">الدافع</th>
                            <th class="p-3">التصنيف المحاسبي المقترح</th>
                            <th class="p-3">حالة المراجعة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach(array_slice($previewData['expenses']['rows'], 0, 15) as $r)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3 font-mono text-gray-400">R{{ $r['row_index'] }}</td>
                            <td class="p-3">{{ $r['date'] ?: 'بدون تاريخ' }}</td>
                            <td class="p-3">{{ $r['category'] }}</td>
                            <td class="p-3 font-medium text-gray-900 dark:text-white">{{ $r['vendor'] }}</td>
                            <td class="p-3 font-bold">${{ number_format($r['amount'], 2) }}</td>
                            <td class="p-3">{{ $r['paid_by'] }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ $r['proposed_type'] }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($r['review_status'] === 'pending_review')
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-medium">معلق للمراجعة</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">معتمد</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- 6. Previous Import Batches History -->
        @if(count($batches) > 0)
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 font-bold text-gray-900 dark:text-white text-sm">
                سجل دفعات الاستيراد السابقة (Import Batches Log)
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3">رقم الدفعة</th>
                            <th class="p-3">اسم الملف</th>
                            <th class="p-3">تاريخ الاستيراد</th>
                            <th class="p-3">الحركات المعتمدة</th>
                            <th class="p-3">حركات المراجعة</th>
                            <th class="p-3">المكررات المتجنبة</th>
                            <th class="p-3">المستخدم</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @foreach($batches as $b)
                        <tr>
                            <td class="p-3 font-mono font-bold text-amber-600 dark:text-amber-400">{{ $b->batch_number }}</td>
                            <td class="p-3">{{ $b->filename }}</td>
                            <td class="p-3">{{ $b->created_at->format('Y-m-d H:i') }}</td>
                            <td class="p-3 font-bold text-emerald-600">{{ $b->imported_rows }}</td>
                            <td class="p-3 font-bold text-amber-600">{{ $b->flagged_rows }}</td>
                            <td class="p-3 font-bold text-purple-600">{{ $b->summary['duplicate_rows'] ?? 0 }}</td>
                            <td class="p-3">{{ $b->importer?->name ?: 'المدير' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
