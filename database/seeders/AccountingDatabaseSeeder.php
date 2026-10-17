<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use App\Models\AccountingPartner;
use App\Models\AccountingReconciliation;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Partners
            $abdullah = AccountingPartner::firstOrCreate(
                ['slug' => 'abdullah'],
                [
                    'name_ar' => 'عبدالله الساسا',
                    'name_en' => 'Abdullah Al Sasa',
                    'initial_debt_balance' => 1277.0000,
                    'is_active' => true,
                    'notes' => 'شريك مؤسس. رصيد الدين المؤكد حتى تاريخ الانتقال 13 أكتوبر 2026 هو 1,277 دولار (شاملاً التسوية الشخصية مع عامر).',
                ]
            );

            $amer = AccountingPartner::firstOrCreate(
                ['slug' => 'amer'],
                [
                    'name_ar' => 'عامر',
                    'name_en' => 'Amer',
                    'initial_debt_balance' => 1277.0000,
                    'is_active' => true,
                    'notes' => 'شريك مؤسس. رصيد الدين المؤكد حتى تاريخ الانتقال 13 أكتوبر 2026 هو 1,277 دولار (صافي بعد استرداد 1,000 دولار من الشركة، وشاملاً التسوية الشخصية).',
                ]
            );

            // 2. Chart of Accounts
            $accounts = [
                // Assets (1000 - 1999)
                ['code' => '1010', 'name_ar' => 'صندوق الشركة (نقدية)', 'name_en' => 'Cash in Hand (Company Fund)', 'type' => 'asset', 'sub_type' => 'cash', 'partner_id' => null, 'description' => 'أموال الشركة النقدية المتاحة'],
                ['code' => '1020', 'name_ar' => 'الحساب البنكي / محافظ إلكترونية', 'name_en' => 'Bank / Digital Wallets', 'type' => 'asset', 'sub_type' => 'bank', 'partner_id' => null, 'description' => 'الحسابات المصرفية ومحافظ التحويل'],
                ['code' => '1100', 'name_ar' => 'ذمم العملاء المدينة', 'name_en' => 'Accounts Receivable', 'type' => 'asset', 'sub_type' => 'receivable', 'partner_id' => null, 'description' => 'مستحقات على العملاء مقابل فواتير صادرة'],
                ['code' => '1200', 'name_ar' => 'إيجار مدفوع مقدماً', 'name_en' => 'Prepaid Rent', 'type' => 'asset', 'sub_type' => 'prepaid_rent', 'partner_id' => null, 'description' => 'دفعات إيجار المكتب المدفوعة مقدماً لتوزيعها على الفترات'],
                ['code' => '1500', 'name_ar' => 'الأصول الثابتة والمعدات', 'name_en' => 'Office Equipment & Assets', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'partner_id' => null, 'description' => 'الأجهزة والإنفرتر والبطاريات وأثاث المكتب'],
                ['code' => '1590', 'name_ar' => 'مجمع إهلاك الأصول', 'name_en' => 'Accumulated Depreciation', 'type' => 'asset', 'sub_type' => 'contra_asset', 'partner_id' => null, 'description' => 'مجمع إهلاك المعدات والأجهزة'],

                // Liabilities (2000 - 2999)
                ['code' => '2101', 'name_ar' => 'دين الشركة للشريك عبدالله', 'name_en' => 'Partner Debt - Abdullah Al Sasa', 'type' => 'liability', 'sub_type' => 'partner_debt', 'partner_id' => $abdullah->id, 'description' => 'مستحقات التمويل والمصاريف الشخصية للشريك عبدالله'],
                ['code' => '2102', 'name_ar' => 'دين الشركة للشريك عامر', 'name_en' => 'Partner Debt - Amer', 'type' => 'liability', 'sub_type' => 'partner_debt', 'partner_id' => $amer->id, 'description' => 'مستحقات التمويل والمصاريف الشخصية للشريك عامر'],
                ['code' => '2103', 'name_ar' => 'ذمم دائنة معلقة - عثمان', 'name_en' => 'Other Payables - Othman', 'type' => 'liability', 'sub_type' => 'other_payable', 'partner_id' => null, 'description' => 'مبالغ مدفوعة من عثمان قيد تحديد طبيعتها'],
                ['code' => '2200', 'name_ar' => 'ذمم الموردين الدائنة', 'name_en' => 'Accounts Payable', 'type' => 'liability', 'sub_type' => 'accounts_payable', 'partner_id' => null, 'description' => 'فواتير الموردين والخدمات المستحقة الدفع'],

                // Equity (3000 - 3999)
                ['code' => '3010', 'name_ar' => 'رأس المال التأسيسي', 'name_en' => 'Founding Capital', 'type' => 'equity', 'sub_type' => 'capital', 'partner_id' => null, 'description' => 'رأس مال التأسيس للشركة (مثل 700 دولار التأسيسية التي صُرفت)'],
                ['code' => '3101', 'name_ar' => 'سحوبات وتوزيعات الشريك عبدالله', 'name_en' => 'Partner Drawings - Abdullah', 'type' => 'equity', 'sub_type' => 'drawings', 'partner_id' => $abdullah->id, 'description' => 'سحوبات وتوزيعات الأرباح الخاصة بعبدالله'],
                ['code' => '3102', 'name_ar' => 'سحوبات وتوزيعات الشريك عامر', 'name_en' => 'Partner Drawings - Amer', 'type' => 'equity', 'sub_type' => 'drawings', 'partner_id' => $amer->id, 'description' => 'سحوبات وتوزيعات الأرباح الخاصة بعامر'],
                ['code' => '3200', 'name_ar' => 'تسوية انتقال تاريخية معلقة للمطابقة', 'name_en' => 'Historical Cutover Reconciliation Reserve', 'type' => 'equity', 'sub_type' => 'reconciliation_reserve', 'partner_id' => null, 'description' => 'فروق المطابقة التاريخية بين النقد التقريبي والأرصدة المؤكدة للشركاء حتى 13 أكتوبر 2026'],
                ['code' => '3300', 'name_ar' => 'الأرباح / الخسائر المدورة', 'name_en' => 'Retained Earnings', 'type' => 'equity', 'sub_type' => 'retained_earnings', 'partner_id' => null, 'description' => 'صافي الأرباح المتراكمة'],

                // Revenues (4000 - 4999)
                ['code' => '4010', 'name_ar' => 'إيرادات المشاريع والخدمات', 'name_en' => 'Project & Service Revenue', 'type' => 'revenue', 'sub_type' => 'operating_revenue', 'partner_id' => null, 'description' => 'إيرادات تطوير البرمجيات والمواقع والتسويق الرقمي'],

                // Expenses (5000 - 6999)
                ['code' => '5010', 'name_ar' => 'تكاليف المشاريع المباشرة وأجور التنفيذ', 'name_en' => 'Direct Project Costs & Execution Labor', 'type' => 'expense', 'sub_type' => 'direct_cost', 'partner_id' => null, 'description' => 'تكاليف تنفيذ المشاريع المباشرة وأجور الشركاء أو المستقلين'],
                ['code' => '6010', 'name_ar' => 'مصروف الإيجار', 'name_en' => 'Rent Expense', 'type' => 'expense', 'sub_type' => 'operating_expense', 'partner_id' => null, 'description' => 'إيجار مقر ومكاتب الشركة'],
                ['code' => '6020', 'name_ar' => 'مصاريف المكتب والضيافة والتجهيزات', 'name_en' => 'Office, Hospitality & Supplies', 'type' => 'expense', 'sub_type' => 'operating_expense', 'partner_id' => null, 'description' => 'مصاريف النظافة، القهوة، أدوات المكتب، والضيافة'],
                ['code' => '6030', 'name_ar' => 'برمجيات واشتراكات وأدوات رقمية', 'name_en' => 'Software & Digital Tools', 'type' => 'expense', 'sub_type' => 'operating_expense', 'partner_id' => null, 'description' => 'اشتراكات CapCut، السيرفرات، والاستضافات'],
                ['code' => '6040', 'name_ar' => 'التسويق والإعلانات والعمولات', 'name_en' => 'Marketing, Ads & Commissions', 'type' => 'expense', 'sub_type' => 'operating_expense', 'partner_id' => null, 'description' => 'إعلانات السوشيال ميديا وعمولات جلب المشاريع'],
                ['code' => '6050', 'name_ar' => 'مصروف إهلاك الأصول', 'name_en' => 'Depreciation Expense', 'type' => 'expense', 'sub_type' => 'operating_expense', 'partner_id' => null, 'description' => 'قسط إهلاك الأجهزة والمعدات'],
                ['code' => '6090', 'name_ar' => 'مصاريف تشغيلية أخرى', 'name_en' => 'Other Operating Expenses', 'type' => 'expense', 'sub_type' => 'operating_expense', 'partner_id' => null, 'description' => 'مصاريف متنوعة غير مصنفة'],
            ];

            foreach ($accounts as $acc) {
                AccountingAccount::updateOrCreate(['code' => $acc['code']], $acc);
            }

            // 3. Cutover Transition Record (13 October 2026)
            $adminUser = User::first();
            $transitionDate = '2026-10-13';

            $reconciliation = AccountingReconciliation::firstOrCreate(
                ['reconciliation_date' => $transitionDate, 'title' => 'تسوية الانتقال ونقطة البداية المؤكدة'],
                [
                    'reported_cash_balance' => 250.0000,
                    'calculated_historical_cash' => null,
                    'cash_discrepancy' => null,
                    'cash_audit_status' => 'estimated_needs_physical_audit',
                    'confirmed_abdullah_debt' => 1277.0000,
                    'confirmed_amer_debt' => 1277.0000,
                    'total_confirmed_partner_debt' => 2554.0000,
                    'amer_repayment_deducted' => true,
                    'abdullah_amer_settlement_handled' => true,
                    'founding_capital_amount' => 700.0000,
                    'status' => 'active',
                    'notes' => 'تسوية انتقال معتمدة وفق الحقائق المؤكدة من صاحب الشركة بتاريخ 13 أكتوبر 2026. رصيد دين عبدالله المؤكد: 1,277$، رصيد دين عامر المؤكد: 1,277$ (صافي بعد رد 1,000$ لعامر). النقد المبلّغ عنه نحو 250$ تقريبي يحتاج جرداً، ولا يسجل كإيراد أو رأس مال جديد.',
                    'created_by' => $adminUser?->id,
                ]
            );

            // 4. Create Cutover Opening Journal Entry
            $existingCutoverEntry = JournalEntry::where('reference_type', 'cutover_transition')->first();
            if (!$existingCutoverEntry) {
                $cashAcc = AccountingAccount::where('code', '1010')->first();
                $reconcilReserveAcc = AccountingAccount::where('code', '3200')->first();
                $abdullahDebtAcc = AccountingAccount::where('code', '2101')->first();
                $amerDebtAcc = AccountingAccount::where('code', '2102')->first();

                $entry = JournalEntry::create([
                    'entry_number' => 'JE-2026-0001',
                    'entry_date' => $transitionDate,
                    'reference_type' => 'cutover_transition',
                    'reference_id' => $reconciliation->id,
                    'description' => 'قيد افتتاح تسوية الانتقال المؤكدة بتاريخ 13 أكتوبر 2026 (ديون الشركاء 2,554$ والنقد التقريبي 250$)',
                    'status' => 'posted',
                    'created_by' => $adminUser?->id,
                ]);

                // Debits: Cash $250.00 + Reconciliation Reserve $2,304.00 = $2,554.00
                $entry->lines()->create([
                    'account_id' => $cashAcc->id,
                    'debit' => 250.0000,
                    'credit' => 0.0000,
                    'description' => 'نقد الصندوق المبلّغ عنه تقريبياً بتاريخ الانتقال 13 أكتوبر 2026 (يحتاج جرد فعلي)',
                ]);

                $entry->lines()->create([
                    'account_id' => $reconcilReserveAcc->id,
                    'debit' => 2304.0000,
                    'credit' => 0.0000,
                    'description' => 'احتياطي تسوية انتقال تاريخية معلقة للمطابقة (فارق أرصدة التأسيس والتاريخ حتى الجرد)',
                ]);

                // Credits: Abdullah Debt $1,277.00 + Amer Debt $1,277.00 = $2,554.00
                $entry->lines()->create([
                    'account_id' => $abdullahDebtAcc->id,
                    'partner_id' => $abdullah->id,
                    'debit' => 0.0000,
                    'credit' => 1277.0000,
                    'description' => 'رصيد دين الشريك عبدالله المؤكد لدى الشركة بتاريخ الانتقال',
                ]);

                $entry->lines()->create([
                    'account_id' => $amerDebtAcc->id,
                    'partner_id' => $amer->id,
                    'debit' => 0.0000,
                    'credit' => 1277.0000,
                    'description' => 'رصيد دين الشريك عامر المؤكد لدى الشركة بتاريخ الانتقال (صافي بعد استرداد 1,000$)',
                ]);
            }
        });
    }
}
