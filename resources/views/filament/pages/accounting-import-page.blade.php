<x-filament-panels::page>
    <div class="space-y-6" dir="ltr">
        <!-- 1. Header & Source Selector -->
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-500/20">
                            Smart Importer
                        </span>
                        <span class="text-xs text-gray-400">•</span>
                        <span class="text-xs text-gray-500">Deduplication & Review Staging</span>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1.5">
                        Excel Ledger Synchronization & Import
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-3xl">
                        Parse Income, Expenses, Projects, and Sheet1 tabs with automated SHA-256 fingerprint deduplication. 
                        Ambiguous items are staged in the Review Queue without altering established ledger cutover balances.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <x-filament::button wire:click="executeImport" color="primary" icon="heroicon-m-check-badge">
                        Execute Smart Import
                    </x-filament::button>
                </div>
            </div>

            <!-- Upload new or use system file -->
            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl border {{ $selectedSource === 'system_file' ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-950/20' : 'border-gray-200 dark:border-gray-800' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">Bundled Workspace File</span>
                            <p class="text-xs text-gray-500 font-mono mt-0.5">Three Digit Software - Accounting Dashboard.xlsx</p>
                        </div>
                        <button wire:click="loadSystemFilePreview" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 transition">
                            Preview Bundled File
                        </button>
                    </div>
                </div>

                <div class="p-4 rounded-xl border {{ $selectedSource === 'upload' ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-950/20' : 'border-gray-200 dark:border-gray-800' }}">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Upload Custom Excel File (.xlsx)</label>
                    <div class="flex items-center gap-2">
                        <input type="file" wire:model="uploadedFile" accept=".xlsx,.xls" class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                        <button wire:click="handleFileUpload" wire:loading.attr="disabled" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition shrink-0">
                            Parse & Preview
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
                    <h3 class="font-bold text-emerald-900 dark:text-emerald-300 text-base">Import Batch Execution: {{ $importResult['batch_number'] }}</h3>
                    <div class="flex flex-wrap items-center gap-4 mt-1 text-xs text-emerald-800 dark:text-emerald-400">
                        <span>• Imported Records: <strong>{{ $importResult['imported_rows'] }}</strong></span>
                        <span>• Staged for Review: <strong>{{ $importResult['flagged_rows'] }}</strong></span>
                        <span>• Prevented Duplicates: <strong>{{ $importResult['duplicate_rows'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 3. Preview Stats & Integrity Indicators -->
        @if($previewData && isset($previewData['stats']))
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Rows</span>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $previewData['stats']['total_rows'] }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Income Rows</span>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($previewData['stats']['income_total_amount'], 2) }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Expenses Rows</span>
                <div class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1">${{ number_format($previewData['stats']['expenses_total_amount'], 2) }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active Projects</span>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $previewData['stats']['projects_count'] }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Needs Review</span>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $previewData['stats']['pending_review_count'] }}</div>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Duplicate Hashes</span>
                <div class="text-xl font-bold {{ $previewData['stats']['duplicate_count'] > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400' }} mt-1">
                    {{ $previewData['stats']['duplicate_count'] }}
                </div>
            </div>
        </div>

        <!-- 4. Column Mapping & Handling Rules Summary -->
        <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-300 space-y-2">
            <h4 class="font-bold text-gray-900 dark:text-white text-sm">Automated Data Normalization Rules:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="p-3.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong class="text-gray-900 dark:text-white">1. Founding Capital & Debt Baseline:</strong> Rows with amer and abdullah with zero amount and notes indicating 1,277 are treated as historical references and do not create duplicate unpaid debt entries. The $700 founding capital is mapped to founding equity.
                </div>
                <div class="p-3.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong class="text-gray-900 dark:text-white">2. Amer $1,000 Debt Repayment:</strong> The August 18, 2026 transaction ("Rent $1,000 company for amer") is mapped as partner debt repayment and is already factored into Amer's net confirmed balance ($1,277).
                </div>
                <div class="p-3.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong class="text-gray-900 dark:text-white">3. Partner Shares ($4,798):</strong> Excel entries listed under Salaries for partners are safely routed to the Review Queue to enable management classification as drawings vs execution labor.
                </div>
                <div class="p-3.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <strong class="text-gray-900 dark:text-white">4. Idempotent Fingerprinting:</strong> Every row is hashed using SHA-256. Re-uploading or re-importing the workbook safely skips existing records without ledger corruption.
                </div>
            </div>
        </div>

        <!-- 5. Sample Preview Tables -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-900 dark:text-white text-base">Parsed Expenses Sample Preview</h3>
            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Row</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Category</th>
                            <th class="p-3.5">Vendor / Service</th>
                            <th class="p-3.5">Amount</th>
                            <th class="p-3.5">Payer in File</th>
                            <th class="p-3.5">Proposed Accounting Type</th>
                            <th class="p-3.5">Review Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach(array_slice($previewData['expenses']['rows'], 0, 15) as $r)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                            <td class="p-3.5 font-mono text-gray-400">R{{ $r['row_index'] }}</td>
                            <td class="p-3.5">{{ $r['date'] ?: 'No Date' }}</td>
                            <td class="p-3.5">{{ $r['category'] }}</td>
                            <td class="p-3.5 font-semibold text-gray-900 dark:text-white">{{ $r['vendor'] }}</td>
                            <td class="p-3.5 font-bold">${{ number_format($r['amount'], 2) }}</td>
                            <td class="p-3.5">{{ $r['paid_by'] }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ $r['proposed_type'] }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                @if($r['review_status'] === 'pending_review')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-semibold">Pending Review</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-semibold">Approved</span>
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
                Historical Import Batches Log
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Batch #</th>
                            <th class="p-3.5">Filename</th>
                            <th class="p-3.5">Imported At</th>
                            <th class="p-3.5">Approved Rows</th>
                            <th class="p-3.5">Review Rows</th>
                            <th class="p-3.5">Duplicates Prevented</th>
                            <th class="p-3.5">Executed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @foreach($batches as $b)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                            <td class="p-3.5 font-mono font-bold text-amber-600 dark:text-amber-400">{{ $b->batch_number }}</td>
                            <td class="p-3.5">{{ $b->filename }}</td>
                            <td class="p-3.5">{{ $b->created_at->format('Y-m-d H:i') }}</td>
                            <td class="p-3.5 font-bold text-emerald-600">{{ $b->imported_rows }}</td>
                            <td class="p-3.5 font-bold text-amber-600">{{ $b->flagged_rows }}</td>
                            <td class="p-3.5 font-bold text-purple-600">{{ $b->summary['duplicate_rows'] ?? 0 }}</td>
                            <td class="p-3.5">{{ $b->importer?->name ?: 'Administrator' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
