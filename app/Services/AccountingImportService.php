<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingAuditLog;
use App\Models\AccountingClient;
use App\Models\AccountingImportBatch;
use App\Models\AccountingImportItem;
use App\Models\AccountingPartner;
use App\Models\AccountingProject;
use App\Models\AccountingTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AccountingImportService
{
    /**
     * Load spreadsheet memory-efficiently
     */
    protected function loadSpreadsheet(string $filePath): Spreadsheet
    {
        ini_set('memory_limit', '512M');
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        return $reader->load($filePath);
    }

    /**
     * Preview the Excel file before committing import
     */
    public function preview(string $filePath): array
    {
        $spreadsheet = $this->loadSpreadsheet($filePath);
        $fileHash = hash_file('sha256', $filePath);

        $previewData = [
            'file_hash' => $fileHash,
            'sheets_found' => $spreadsheet->getSheetNames(),
            'income' => $this->parseIncomeSheet($spreadsheet, true),
            'expenses' => $this->parseExpensesSheet($spreadsheet, true),
            'projects' => $this->parseProjectsSheet($spreadsheet, true),
            'sheet1' => $this->parseSheet1($spreadsheet, true),
        ];

        $totalRows = count($previewData['income']['rows'])
            + count($previewData['expenses']['rows'])
            + count($previewData['projects']['rows'])
            + count($previewData['sheet1']['rows']);

        $pendingReviewCount = 0;
        $duplicateCount = 0;

        foreach (['income', 'expenses', 'projects', 'sheet1'] as $key) {
            foreach ($previewData[$key]['rows'] as $r) {
                if ($r['review_status'] === 'pending_review') {
                    $pendingReviewCount++;
                }
                if ($r['is_duplicate']) {
                    $duplicateCount++;
                }
            }
        }

        $previewData['stats'] = [
            'total_rows' => $totalRows,
            'income_rows' => count($previewData['income']['rows']),
            'income_total_amount' => $previewData['income']['total_amount'],
            'expenses_rows' => count($previewData['expenses']['rows']),
            'expenses_total_amount' => $previewData['expenses']['total_amount'],
            'projects_count' => count($previewData['projects']['rows']),
            'sheet1_rows' => count($previewData['sheet1']['rows']),
            'pending_review_count' => $pendingReviewCount,
            'duplicate_count' => $duplicateCount,
        ];

        return $previewData;
    }

    /**
     * Execute full import and store items with deduplication
     */
    public function import(string $filePath, ?int $userId = null, array $options = []): array
    {
        return DB::transaction(function () use ($filePath, $userId, $options) {
            $spreadsheet = $this->loadSpreadsheet($filePath);
            $fileHash = hash_file('sha256', $filePath);
            $filename = basename($filePath);

            $batch = AccountingImportBatch::create([
                'batch_number' => AccountingImportBatch::generateBatchNumber(),
                'filename' => $filename,
                'file_path' => $filePath,
                'file_hash' => $fileHash,
                'imported_by' => $userId,
                'status' => 'processing',
            ]);

            $importedCount = 0;
            $flaggedCount = 0;
            $rejectedCount = 0;
            $duplicateCount = 0;

            // 1. Projects first so foreign keys can link
            $projectsData = $this->parseProjectsSheet($spreadsheet, false);
            foreach ($projectsData['rows'] as $projRow) {
                $isDup = AccountingImportItem::where('fingerprint_hash', $projRow['fingerprint_hash'])->exists();
                if ($isDup && !($options['force_duplicates'] ?? false)) {
                    $duplicateCount++;
                    continue;
                }

                $client = null;
                if (!empty($projRow['client_name'])) {
                    $normalized = AccountingClient::normalizeName($projRow['client_name']);
                    $client = AccountingClient::firstOrCreate(
                        ['normalized_name' => $normalized],
                        ['name' => trim($projRow['client_name'])]
                    );
                }

                $project = AccountingProject::firstOrCreate(
                    ['name' => trim($projRow['name'])],
                    [
                        'client_id' => $client?->id,
                        'total_price' => $projRow['total_price'],
                        'direct_costs' => $projRow['direct_costs'],
                        'status' => $projRow['status_code'],
                        'notes' => 'مستورد من ورقة Projects في الإكسل',
                    ]
                );

                AccountingImportItem::create([
                    'batch_id' => $batch->id,
                    'sheet_name' => 'Projects',
                    'row_index' => $projRow['row_index'],
                    'raw_data' => $projRow['raw_data'],
                    'fingerprint_hash' => $projRow['fingerprint_hash'],
                    'amount' => $projRow['total_price'],
                    'proposed_type' => 'project_record',
                    'proposed_client_id' => $client?->id,
                    'proposed_project_id' => $project->id,
                    'review_status' => 'approved',
                    'review_reason' => 'مشروع مسجل في الإكسل: ' . $projRow['name'],
                    'final_decision' => 'imported',
                ]);
                $importedCount++;
            }

            // 2. Income rows
            $incomeData = $this->parseIncomeSheet($spreadsheet, false);
            foreach ($incomeData['rows'] as $incRow) {
                $isDup = AccountingImportItem::where('fingerprint_hash', $incRow['fingerprint_hash'])->exists();
                if ($isDup && !($options['force_duplicates'] ?? false)) {
                    $duplicateCount++;
                    continue;
                }

                // Match or create client
                $client = null;
                if (!empty($incRow['client_name']) && $incRow['client_name'] !== 'capital') {
                    $normalized = AccountingClient::normalizeName($incRow['client_name']);
                    $client = AccountingClient::firstOrCreate(
                        ['normalized_name' => $normalized],
                        ['name' => trim($incRow['client_name'])]
                    );
                    if ($incRow['potential_receivable'] > 0) {
                        $client->update(['potential_receivable_amount' => $incRow['potential_receivable']]);
                    }
                }

                // Match project if any
                $project = null;
                if (!empty($incRow['project_service'])) {
                    $project = AccountingProject::where('name', 'like', '%' . trim($incRow['project_service']) . '%')
                        ->orWhere('name', 'like', '%' . trim($incRow['client_name'] ?? '') . '%')
                        ->first();
                }

                $partner = null;
                if ($incRow['partner_slug']) {
                    $partner = AccountingPartner::where('slug', $incRow['partner_slug'])->first();
                }

                $item = AccountingImportItem::create([
                    'batch_id' => $batch->id,
                    'sheet_name' => 'Income',
                    'row_index' => $incRow['row_index'],
                    'raw_data' => $incRow['raw_data'],
                    'fingerprint_hash' => $incRow['fingerprint_hash'],
                    'date' => $incRow['date'],
                    'amount' => $incRow['amount'],
                    'proposed_type' => $incRow['proposed_type'],
                    'proposed_partner_id' => $partner?->id,
                    'proposed_client_id' => $client?->id,
                    'proposed_project_id' => $project?->id,
                    'review_status' => $incRow['review_status'],
                    'review_reason' => $incRow['review_reason'],
                    'suggested_action' => $incRow['suggested_action'],
                ]);

                if ($incRow['review_status'] === 'pending_review') {
                    $flaggedCount++;
                } else {
                    $importedCount++;
                }
            }

            // 3. Expenses rows
            $expensesData = $this->parseExpensesSheet($spreadsheet, false);
            foreach ($expensesData['rows'] as $expRow) {
                $isDup = AccountingImportItem::where('fingerprint_hash', $expRow['fingerprint_hash'])->exists();
                if ($isDup && !($options['force_duplicates'] ?? false)) {
                    $duplicateCount++;
                    continue;
                }

                $partner = null;
                if ($expRow['partner_slug']) {
                    $partner = AccountingPartner::where('slug', $expRow['partner_slug'])->first();
                }

                $item = AccountingImportItem::create([
                    'batch_id' => $batch->id,
                    'sheet_name' => 'Expenses',
                    'row_index' => $expRow['row_index'],
                    'raw_data' => $expRow['raw_data'],
                    'fingerprint_hash' => $expRow['fingerprint_hash'],
                    'date' => $expRow['date'],
                    'amount' => $expRow['amount'],
                    'proposed_type' => $expRow['proposed_type'],
                    'proposed_partner_id' => $partner?->id,
                    'review_status' => $expRow['review_status'],
                    'review_reason' => $expRow['review_reason'],
                    'suggested_action' => $expRow['suggested_action'],
                ]);

                if ($expRow['review_status'] === 'pending_review') {
                    $flaggedCount++;
                } else {
                    $importedCount++;
                }
            }

            // 4. Sheet1 rows (1,075 each for Abdullah)
            $sheet1Data = $this->parseSheet1($spreadsheet, false);
            foreach ($sheet1Data['rows'] as $s1Row) {
                $isDup = AccountingImportItem::where('fingerprint_hash', $s1Row['fingerprint_hash'])->exists();
                if ($isDup && !($options['force_duplicates'] ?? false)) {
                    $duplicateCount++;
                    continue;
                }

                $abdullah = AccountingPartner::where('slug', 'abdullah')->first();

                AccountingImportItem::create([
                    'batch_id' => $batch->id,
                    'sheet_name' => 'Sheet1',
                    'row_index' => $s1Row['row_index'],
                    'raw_data' => $s1Row['raw_data'],
                    'fingerprint_hash' => $s1Row['fingerprint_hash'],
                    'amount' => $s1Row['amount'],
                    'proposed_type' => 'partner_debt_memo',
                    'proposed_partner_id' => $abdullah?->id,
                    'review_status' => 'pending_review',
                    'review_reason' => 'سجل مصدر معلق من Sheet1 بقيمة 1,075 دولار باسم عبدالله؛ محفوظ دون ترحيل كدين إضافي حتى اكتمال التفسير.',
                    'suggested_action' => 'الاحتفاظ به في سجلات المراجعة للتحقق من علاقته بحسابات التأسيس السابقة.',
                ]);
                $flaggedCount++;
            }

            $totalRows = $importedCount + $flaggedCount + $duplicateCount;

            $batch->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
                'imported_rows' => $importedCount,
                'flagged_rows' => $flaggedCount,
                'rejected_rows' => $rejectedCount,
                'summary' => [
                    'imported_rows' => $importedCount,
                    'flagged_rows' => $flaggedCount,
                    'rejected_rows' => $rejectedCount,
                    'duplicate_rows' => $duplicateCount,
                    'income_sum' => $incomeData['total_amount'],
                    'expenses_sum' => $expensesData['total_amount'],
                ],
            ]);

            AccountingAuditLog::create([
                'user_id' => $userId,
                'action' => 'import_excel',
                'entity_type' => AccountingImportBatch::class,
                'entity_id' => $batch->id,
                'description' => "استيراد ملف إكسل {$filename}: {$importedCount} حركة معتمدة، {$flaggedCount} حركة للمراجعة، {$duplicateCount} حركة مكررة تم تجنبها.",
                'new_values' => $batch->summary,
            ]);

            return [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'imported_rows' => $importedCount,
                'flagged_rows' => $flaggedCount,
                'rejected_rows' => $rejectedCount,
                'duplicate_rows' => $duplicateCount,
                'summary' => $batch->summary,
            ];
        });
    }

    /**
     * Parse Income sheet rows and build analysis
     */
    protected function parseIncomeSheet($spreadsheet, bool $forPreview): array
    {
        $sheet = $spreadsheet->getSheetByName('Income');
        if (!$sheet) {
            return ['rows' => [], 'total_amount' => 0];
        }

        $rows = [];
        $totalAmount = 0;
        $highestRow = $sheet->getHighestRow();

        for ($r = 2; $r <= $highestRow; $r++) {
            $client = trim((string)$sheet->getCell('B' . $r)->getValue());
            $amountVal = $sheet->getCell('E' . $r)->getCalculatedValue();

            if (empty($client) && (empty($amountVal) || (float)$amountVal == 0)) {
                continue;
            }

            $dateCell = $sheet->getCell('A' . $r);
            $date = $this->parseDateCell($dateCell);
            $service = trim((string)$sheet->getCell('C' . $r)->getValue());
            $invNo = trim((string)$sheet->getCell('D' . $r)->getValue());
            $amount = round((float)$amountVal, 4);
            $status = trim((string)$sheet->getCell('F' . $r)->getValue());
            $payDate = $this->parseDateCell($sheet->getCell('G' . $r));
            $payMethod = trim((string)$sheet->getCell('H' . $r)->getValue());
            $notes = trim((string)$sheet->getCell('I' . $r)->getValue());

            $totalAmount += $amount;

            $fingerprint = AccountingImportItem::computeFingerprint('Income', $r, $date, $amount, $client, $notes);
            $isDuplicate = AccountingImportItem::where('fingerprint_hash', $fingerprint)->exists();

            $proposedType = 'client_collection';
            $partnerSlug = null;
            $reviewStatus = 'approved';
            $reviewReason = null;
            $suggestedAction = null;
            $potentialReceivable = 0;

            // Specific rules from owner & excel structure
            if (strtolower($client) === 'capital') {
                if (strtolower($service) === 'amer') {
                    $proposedType = 'partner_debt_memo';
                    $partnerSlug = 'amer';
                    $reviewStatus = 'pending_review';
                    $reviewReason = 'صف تمويل/دين شريك عامر بمبلغ 0 وملاحظة 1,277؛ ليس فاتورة غير مدفوعة.';
                    $suggestedAction = 'مطابقة مع رصيد دين عامر المؤكد 1,277 دولار.';
                } elseif (strtolower($service) === 'abdullah') {
                    $proposedType = 'partner_debt_memo';
                    $partnerSlug = 'abdullah';
                    $reviewStatus = 'pending_review';
                    $reviewReason = 'صف تمويل/دين شريك عبدالله بمبلغ 0 وملاحظة 1,277؛ ليس فاتورة غير مدفوعة.';
                    $suggestedAction = 'مطابقة مع رصيد دين عبدالله المؤكد 1,277 دولار.';
                } elseif (strtolower($service) === 'capital') {
                    $proposedType = 'capital_injection';
                    $reviewStatus = 'pending_review';
                    $reviewReason = 'مبلغ 700 دولار رأس مال تأسيسي صُرف لاحقاً وليس إيراد عميل، وليس نقداً متبقياً.';
                    $suggestedAction = 'تسجيل كرأس مال تأسيسي ضمن حقوق الملكية مع مطابقة المصاريف المقابلة.';
                }
            } elseif (str_contains(strtolower($client), 'hkcartrimming')) {
                $proposedType = 'client_collection';
                $reviewStatus = 'approved';
                $suggestedAction = 'تحصيل إيراد مشروع hkcartrimming بقيمة 2,000 دولار كاش.';
            } else {
                // Client payments
                $proposedType = 'client_collection';
                if (str_contains($notes, 'left') || str_contains($notes, 'pefumes') || str_contains($notes, 'عطور')) {
                    $reviewStatus = 'pending_review';
                    $reviewReason = 'ملاحظة تشير إلى رصيد متبقٍ أو تسوية عينية: ' . $notes;

                    // Parse potential receivables from notes
                    if (str_contains($notes, '350')) {
                        $potentialReceivable = 350.0;
                    } elseif (str_contains($notes, '315')) {
                        $potentialReceivable = 315.0;
                    } elseif (str_contains($notes, '150')) {
                        $potentialReceivable = 150.0;
                    } elseif (str_contains($notes, '425')) {
                        $potentialReceivable = 425.0;
                    }

                    if (str_contains($notes, '85') && (str_contains($notes, 'pefumes') || str_contains($notes, 'عطور'))) {
                        $reviewReason .= ' | يوجد 85 دولار عطور لعبدالله تحتاج مراجعة كمعاملة غير نقدية / سحب شريك.';
                    }
                }
            }

            $rows[] = [
                'row_index' => $r,
                'date' => $date,
                'client_name' => $client,
                'project_service' => $service,
                'invoice_no' => $invNo,
                'amount' => $amount,
                'status' => $status,
                'payment_date' => $payDate,
                'payment_method' => $payMethod,
                'notes' => $notes,
                'fingerprint_hash' => $fingerprint,
                'is_duplicate' => $isDuplicate,
                'proposed_type' => $proposedType,
                'partner_slug' => $partnerSlug,
                'potential_receivable' => $potentialReceivable,
                'review_status' => $reviewStatus,
                'review_reason' => $reviewReason,
                'suggested_action' => $suggestedAction,
                'raw_data' => [
                    'Date' => $date,
                    'Client Name' => $client,
                    'Project / Service' => $service,
                    'Invoice No.' => $invNo,
                    'Amount' => $amount,
                    'Status' => $status,
                    'Payment Date' => $payDate,
                    'Payment Method' => $payMethod,
                    'Notes' => $notes,
                ],
            ];
        }

        return ['rows' => $rows, 'total_amount' => round($totalAmount, 4)];
    }

    /**
     * Parse Expenses sheet rows and build analysis
     */
    protected function parseExpensesSheet($spreadsheet, bool $forPreview): array
    {
        $sheet = $spreadsheet->getSheetByName('Expenses');
        if (!$sheet) {
            return ['rows' => [], 'total_amount' => 0];
        }

        $rows = [];
        $totalAmount = 0;
        $highestRow = $sheet->getHighestRow();

        for ($r = 2; $r <= $highestRow; $r++) {
            $cat = trim((string)$sheet->getCell('B' . $r)->getValue());
            $vendor = trim((string)$sheet->getCell('C' . $r)->getValue());
            $amountVal = $sheet->getCell('D' . $r)->getCalculatedValue();

            if (empty($cat) && empty($vendor) && (empty($amountVal) || (float)$amountVal == 0)) {
                continue;
            }

            $dateCell = $sheet->getCell('A' . $r);
            $date = $this->parseDateCell($dateCell);
            $amount = round((float)$amountVal, 4);
            $recurring = trim((string)$sheet->getCell('E' . $r)->getValue());
            $paidBy = trim((string)$sheet->getCell('F' . $r)->getValue());
            $payMethod = trim((string)$sheet->getCell('G' . $r)->getValue());
            $projRelated = trim((string)$sheet->getCell('H' . $r)->getValue());
            $notes = trim((string)$sheet->getCell('I' . $r)->getValue());

            $totalAmount += $amount;

            $fingerprint = AccountingImportItem::computeFingerprint('Expenses', $r, $date, $amount, $paidBy, $notes ?: $vendor);
            $isDuplicate = AccountingImportItem::where('fingerprint_hash', $fingerprint)->exists();

            $proposedType = 'company_expense';
            $partnerSlug = null;
            $reviewStatus = 'approved';
            $reviewReason = null;
            $suggestedAction = null;

            $paidByLower = strtolower($paidBy);

            // Paid by partner personal funds?
            if (str_contains($paidByLower, 'abdullah')) {
                $partnerSlug = 'abdullah';
                $proposedType = 'partner_personal_expense';
            } elseif (str_contains($paidByLower, 'amer')) {
                $partnerSlug = 'amer';
                $proposedType = 'partner_personal_expense';
            } elseif (str_contains($paidByLower, 'othman')) {
                $proposedType = 'company_expense';
                $reviewStatus = 'pending_review';
                $reviewReason = 'اسم الدافع عثمان؛ شخص مستقل يحتاج تحديد مصدر الدفع وعلاقته بالشركة.';
                $suggestedAction = 'تحديد هل المبلغ سُدد لعثمان أو قيد دين شخصي له.';
            } else {
                $proposedType = 'company_expense';
            }

            // Case: Row 42 (2026-08-18 Rent 1000 company "for amer") -> Debt repayment to Amer!
            if ($r === 42 || ($amount == 1000.0 && str_contains(strtolower($notes), 'for amer') && str_contains(strtolower($vendor), 'rent'))) {
                $proposedType = 'partner_debt_repayment';
                $partnerSlug = 'amer';
                $reviewStatus = 'approved';
                $reviewReason = 'سداد دين لعامر بقيمة 1,000 دولار وفق إفادة صاحب الشركة؛ لا تخصم ثانية من رصيده المؤكد الحالي (1,277 دولار).';
                $suggestedAction = 'تصنيف كسداد دين شريك تاريخي مسترد.';
            }
            // Case: Original Rent 2026-06-25 Amer 1000
            elseif ($amount == 1000.0 && str_contains(strtolower($vendor), 'rent') && str_contains($paidByLower, 'amer')) {
                $reviewStatus = 'pending_review';
                $reviewReason = 'إيجار المكتب الأصلي في الإكسل 1,000 بينما ورد شفهياً 2,000؛ تم الاحتفاظ بقيمة المصدر 1,000 مع تعليم الفارق للمراجعة.';
                $suggestedAction = 'تأكيد العقد الفعلي وهل يوجد دفعة ثانية غير مسجلة.';
            }
            // Case: Salaries / Partner shares ($4,798 total)
            elseif ($cat === 'Salaries') {
                if (str_contains(strtolower($vendor), 'frs social media')) {
                    $proposedType = 'partner_personal_expense';
                    $partnerSlug = 'abdullah';
                    $reviewStatus = 'pending_review';
                    $reviewReason = 'مبلغ 96 دولار لخدمة FRS social media منفصل عن حصص الشركاء؛ تكلفة خدمة مبدئية.';
                    $suggestedAction = 'تسجيل كتكلفة تسويق/مشروع ومراجعة مصدر الدفع.';
                } else {
                    $proposedType = 'partner_distribution';
                    $reviewStatus = 'pending_review';
                    $reviewReason = 'حصة شريك مسجلة تحت Salaries؛ وضعت في المراجعة لتحديد هل هي سحوبات/توزيعات أم أجور تنفيذ (لا تعتبر راتباً تلقائياً).';
                    $suggestedAction = 'تحديد نوع الحركة: سحب شريك (Drawings) أو أجر تنفيذ مشروع (Direct Labor).';

                    if (str_contains(strtolower($notes . ' ' . $vendor), 'amer')) {
                        $partnerSlug = 'amer';
                    } elseif (str_contains(strtolower($notes . ' ' . $vendor), 'abdullah')) {
                        $partnerSlug = 'abdullah';
                    }
                }
            }
            // Case: "start taking from company" note on 2026-08-19
            elseif (str_contains(strtolower($projRelated . ' ' . $notes), 'start taking from company')) {
                $proposedType = 'company_expense';
                $reviewStatus = 'pending_review';
                $reviewReason = 'ملاحظة start taking from company ترجح مصدر أموال الشركة بدلاً من جيب عبدالله؛ مقترحة للمراجعة.';
                $suggestedAction = 'اعتماد الصرف من صندوق الشركة بدلاً من زيادة دين الشريك.';
            }

            $rows[] = [
                'row_index' => $r,
                'date' => $date,
                'category' => $cat,
                'vendor' => $vendor,
                'amount' => $amount,
                'recurring' => $recurring,
                'paid_by' => $paidBy,
                'payment_method' => $payMethod,
                'project_related' => $projRelated,
                'notes' => $notes,
                'fingerprint_hash' => $fingerprint,
                'is_duplicate' => $isDuplicate,
                'proposed_type' => $proposedType,
                'partner_slug' => $partnerSlug,
                'review_status' => $reviewStatus,
                'review_reason' => $reviewReason,
                'suggested_action' => $suggestedAction,
                'raw_data' => [
                    'Date' => $date,
                    'Category' => $cat,
                    'Vendor / Service' => $vendor,
                    'Amount' => $amount,
                    'Recurring?' => $recurring,
                    'Paid By' => $paidBy,
                    'Payment Method' => $payMethod,
                    'Project Related?' => $projRelated,
                    'Notes' => $notes,
                ],
            ];
        }

        return ['rows' => $rows, 'total_amount' => round($totalAmount, 4)];
    }

    /**
     * Parse Projects sheet rows
     */
    protected function parseProjectsSheet($spreadsheet, bool $forPreview): array
    {
        $sheet = $spreadsheet->getSheetByName('Projects');
        if (!$sheet) {
            return ['rows' => []];
        }

        $rows = [];
        $highestRow = $sheet->getHighestRow();

        for ($r = 2; $r <= $highestRow; $r++) {
            $name = trim((string)$sheet->getCell('A' . $r)->getValue());
            if (empty($name)) {
                continue;
            }

            $client = trim((string)$sheet->getCell('B' . $r)->getValue());
            $price = round((float)$sheet->getCell('C' . $r)->getCalculatedValue(), 4);
            $costs = round((float)$sheet->getCell('D' . $r)->getCalculatedValue(), 4);
            $timeHours = $sheet->getCell('E' . $r)->getCalculatedValue();
            $statusRaw = trim((string)$sheet->getCell('H' . $r)->getValue());

            $statusCode = match (strtolower($statusRaw)) {
                'done' => 'done',
                'cancelled' => 'cancelled',
                default => 'in_progress',
            };

            $fingerprint = AccountingImportItem::computeFingerprint('Projects', $r, null, $price, $client, $name);
            $isDuplicate = AccountingImportItem::where('fingerprint_hash', $fingerprint)->exists();

            $rows[] = [
                'row_index' => $r,
                'name' => $name,
                'client_name' => $client,
                'total_price' => $price,
                'direct_costs' => $costs,
                'time_hours' => $timeHours ? (float)$timeHours : null,
                'status' => $statusRaw ?: 'In Progress',
                'status_code' => $statusCode,
                'fingerprint_hash' => $fingerprint,
                'is_duplicate' => $isDuplicate,
                'review_status' => 'approved',
                'raw_data' => [
                    'Project Name' => $name,
                    'Client' => $client,
                    'Total Price' => $price,
                    'Direct Costs' => $costs,
                    'Status' => $statusRaw,
                ],
            ];
        }

        return ['rows' => $rows];
    }

    /**
     * Parse Sheet1 rows
     */
    protected function parseSheet1($spreadsheet, bool $forPreview): array
    {
        $sheet = $spreadsheet->getSheetByName('Sheet1');
        if (!$sheet) {
            return ['rows' => []];
        }

        $rows = [];
        $highestRow = $sheet->getHighestRow();

        for ($r = 2; $r <= $highestRow; $r++) {
            $amountVal = $sheet->getCell('A' . $r)->getCalculatedValue();
            $founder = trim((string)$sheet->getCell('B' . $r)->getValue());

            if (empty($amountVal) || (float)$amountVal == 0) {
                continue;
            }

            $amount = round((float)$amountVal, 4);
            $fingerprint = AccountingImportItem::computeFingerprint('Sheet1', $r, null, $amount, $founder, 'Sheet1 row');
            $isDuplicate = AccountingImportItem::where('fingerprint_hash', $fingerprint)->exists();

            $rows[] = [
                'row_index' => $r,
                'amount' => $amount,
                'founder' => $founder,
                'fingerprint_hash' => $fingerprint,
                'is_duplicate' => $isDuplicate,
                'review_status' => 'pending_review',
                'review_reason' => 'صفان في Sheet1 كل منهما 1,075 دولار باسم عبدالله؛ احتفظ بهما كسجلات مصدر معلقة دون حذف أو تكرار ديون.',
                'raw_data' => [
                    'ammount' => $amount,
                    'founders' => $founder,
                ],
            ];
        }

        return ['rows' => $rows];
    }

    /**
     * Helper to parse date cell safely
     */
    protected function parseDateCell($cell): ?string
    {
        if (!$cell) {
            return null;
        }

        $val = $cell->getValue();
        if (empty($val)) {
            return null;
        }

        if (Date::isDateTime($cell)) {
            try {
                $dt = Date::excelToDateTimeObject($val);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Try string parsing
        $str = trim((string)$cell->getFormattedValue() ?: (string)$val);
        $time = strtotime($str);
        if ($time !== false && $time > 0) {
            return date('Y-m-d', $time);
        }

        return null;
    }
}
