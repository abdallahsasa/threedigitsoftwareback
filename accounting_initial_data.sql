-- 1. Partners Initial Data
INSERT INTO `accounting_partners` (`id`, `name_ar`, `name_en`, `slug`, `initial_debt_balance`, `is_active`, `notes`, `created_at`, `updated_at`) VALUES 
(1, 'عبدالله الساسا', 'Abdullah Al Sasa', 'abdullah', 1277.0000, 1, 'شريك مؤسس. رصيد الدين المؤكد حتى تاريخ الانتقال 13 أكتوبر 2026 هو 1,277 دولار.', NOW(), NOW()),
(2, 'عامر', 'Amer', 'amer', 1277.0000, 1, 'شريك مؤسس. رصيد الدين المؤكد حتى تاريخ الانتقال 13 أكتوبر 2026 هو 1,277 دولار (صافي بعد استرداد 1,000 دولار).', NOW(), NOW()) ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 2. Chart of Accounts
INSERT INTO `accounting_accounts` (`code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `description`, `created_at`, `updated_at`) VALUES 
('1010', 'صندوق الشركة (نقدية)', 'Cash in Hand (Company Fund)', 'asset', 'cash', NULL, 0.0000, 1, 'أموال الشركة النقدية المتاحة', NOW(), NOW()),
('1020', 'الحساب البنكي / محافظ إلكترونية', 'Bank / Digital Wallets', 'asset', 'bank', NULL, 0.0000, 1, 'الحسابات المصرفية ومحافظ التحويل', NOW(), NOW()),
('1100', 'ذمم العملاء المدينة', 'Accounts Receivable', 'asset', 'receivable', NULL, 0.0000, 1, 'مستحقات على العملاء مقابل فواتير صادرة', NOW(), NOW()),
('1200', 'إيجار مدفوع مقدماً', 'Prepaid Rent', 'asset', 'prepaid_rent', NULL, 0.0000, 1, 'دفعات إيجار المكتب المدفوعة مقدماً لتوزيعها على الفترات', NOW(), NOW()),
('1500', 'الأصول الثابتة والمعدات', 'Office Equipment & Assets', 'asset', 'fixed_asset', NULL, 0.0000, 1, 'الأجهزة والإنفرتر والبطاريات وأثاث المكتب', NOW(), NOW()),
('1590', 'مجمع إهلاك الأصول', 'Accumulated Depreciation', 'asset', 'contra_asset', NULL, 0.0000, 1, 'مجمع إهلاك المعدات والأجهزة', NOW(), NOW()),
('2101', 'دين الشركة للشريك عبدالله', 'Partner Debt - Abdullah Al Sasa', 'liability', 'partner_debt', 1, 0.0000, 1, 'مستحقات التمويل والمصاريف الشخصية للشريك عبدالله', NOW(), NOW()),
('2102', 'دين الشركة للشريك عامر', 'Partner Debt - Amer', 'liability', 'partner_debt', 2, 0.0000, 1, 'مستحقات التمويل والمصاريف الشخصية للشريك عامر', NOW(), NOW()),
('2103', 'ذمم دائنة معلقة - عثمان', 'Other Payables - Othman', 'liability', 'other_payable', NULL, 0.0000, 1, 'مبالغ مدفوعة من عثمان قيد تحديد طبيعتها', NOW(), NOW()),
('2200', 'ذمم الموردين الدائنة', 'Accounts Payable', 'liability', 'accounts_payable', NULL, 0.0000, 1, 'فواتير الموردين والخدمات المستحقة الدفع', NOW(), NOW()),
('3010', 'رأس المال التأسيسي', 'Founding Capital', 'equity', 'capital', NULL, 0.0000, 1, 'رأس مال التأسيس للشركة (مثل 700 دولار التأسيسية التي صُرفت)', NOW(), NOW()),
('3101', 'سحوبات وتوزيعات الشريك عبدالله', 'Partner Drawings - Abdullah', 'equity', 'drawings', 1, 0.0000, 1, 'سحوبات وتوزيعات الأرباح الخاصة بعبدالله', NOW(), NOW()),
('3102', 'سحوبات وتوزيعات الشريك عامر', 'Partner Drawings - Amer', 'equity', 'drawings', 2, 0.0000, 1, 'سحوبات وتوزيعات الأرباح الخاصة بعامر', NOW(), NOW()),
('3200', 'تسوية انتقال تاريخية معلقة للمطابقة', 'Historical Cutover Reconciliation Reserve', 'equity', 'reconciliation_reserve', NULL, 0.0000, 1, 'فروق المطابقة التاريخية بين النقد التقريبي والأرصدة المؤكدة للشركاء حتى 13 أكتوبر 2026', NOW(), NOW()),
('3300', 'الأرباح / الخسائر المدورة', 'Retained Earnings', 'equity', 'retained_earnings', NULL, 0.0000, 1, 'صافي الأرباح المتراكمة', NOW(), NOW()),
('4010', 'إيرادات المشاريع والخدمات', 'Project & Service Revenue', 'revenue', 'operating_revenue', NULL, 0.0000, 1, 'إيرادات تطوير البرمجيات والمواقع والتسويق الرقمي', NOW(), NOW()),
('5010', 'تكاليف المشاريع المباشرة وأجور التنفيذ', 'Direct Project Costs & Execution Labor', 'expense', 'direct_cost', NULL, 0.0000, 1, 'تكاليف تنفيذ المشاريع المباشرة وأجور الشركاء أو المستقلين', NOW(), NOW()),
('6010', 'مصروف الإيجار', 'Rent Expense', 'expense', 'operating_expense', NULL, 0.0000, 1, 'إيجار مقر ومكاتب الشركة', NOW(), NOW()),
('6020', 'مصاريف المكتب والضيافة والتجهيزات', 'Office, Hospitality & Supplies', 'expense', 'operating_expense', NULL, 0.0000, 1, 'مصاريف النظافة، القهوة، أدوات المكتب، والضيافة', NOW(), NOW()),
('6030', 'برمجيات واشتراكات وأدوات رقمية', 'Software & Digital Tools', 'expense', 'operating_expense', NULL, 0.0000, 1, 'اشتراكات CapCut، السيرفرات، والاستضافات', NOW(), NOW()),
('6040', 'التسويق والإعلانات والعمولات', 'Marketing, Ads & Commissions', 'expense', 'operating_expense', NULL, 0.0000, 1, 'إعلانات السوشيال ميديا وعمولات جلب المشاريع', NOW(), NOW()),
('6050', 'مصروف إهلاك الأصول', 'Depreciation Expense', 'expense', 'operating_expense', NULL, 0.0000, 1, 'قسط إهلاك الأجهزة والمعدات', NOW(), NOW()),
('6090', 'مصاريف تشغيلية أخرى', 'Other Operating Expenses', 'expense', 'operating_expense', NULL, 0.0000, 1, 'مصاريف متنوعة غير مصنفة', NOW(), NOW()) ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 3. Cutover Transition Record (13 Oct 2026)
INSERT INTO `accounting_reconciliations` (`id`, `reconciliation_date`, `title`, `reported_cash_balance`, `cash_audit_status`, `confirmed_abdullah_debt`, `confirmed_amer_debt`, `total_confirmed_partner_debt`, `amer_repayment_deducted`, `abdullah_amer_settlement_handled`, `founding_capital_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES (1, '2026-10-13', 'تسوية الانتقال ونقطة البداية المؤكدة', 250.0000, 'estimated_needs_physical_audit', 1277.0000, 1277.0000, 2554.0000, 1, 1, 700.0000, 'active', 'تسوية انتقال معتمدة وفق الحقائق المؤكدة من صاحب الشركة بتاريخ 13 أكتوبر 2026.', NOW(), NOW()) ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 4. Cutover Opening Journal Entry & Lines
INSERT INTO `journal_entries` (`id`, `entry_number`, `entry_date`, `reference_type`, `reference_id`, `description`, `status`, `created_at`, `updated_at`) VALUES (1, 'JE-2026-0001', '2026-10-13', 'cutover_transition', 1, 'قيد افتتاح تسوية الانتقال المؤكدة بتاريخ 13 أكتوبر 2026 (ديون الشركاء 2,554$ والنقد التقريبي 250$)', 'posted', NOW(), NOW()) ON DUPLICATE KEY UPDATE `updated_at` = NOW();
INSERT INTO `journal_entry_lines` (`journal_entry_id`, `account_id`, `partner_id`, `debit`, `credit`, `description`, `created_at`, `updated_at`) VALUES
(1, (SELECT `id` FROM `accounting_accounts` WHERE `code` = '1010' LIMIT 1), NULL, 250.0000, 0.0000, 'نقد الصندوق المبلّغ عنه تقريبياً بتاريخ الانتقال 13 أكتوبر 2026 (يحتاج جرد فعلي)', NOW(), NOW()),
(1, (SELECT `id` FROM `accounting_accounts` WHERE `code` = '3200' LIMIT 1), NULL, 2304.0000, 0.0000, 'احتياطي تسوية انتقال تاريخية معلقة للمطابقة (فارق أرصدة التأسيس والتاريخ حتى الجرد)', NOW(), NOW()),
(1, (SELECT `id` FROM `accounting_accounts` WHERE `code` = '2101' LIMIT 1), 1, 0.0000, 1277.0000, 'رصيد دين الشريك عبدالله المؤكد لدى الشركة بتاريخ الانتقال', NOW(), NOW()),
(1, (SELECT `id` FROM `accounting_accounts` WHERE `code` = '2102' LIMIT 1), 2, 0.0000, 1277.0000, 'رصيد دين الشريك عامر المؤكد لدى الشركة بتاريخ الانتقال (صافي بعد استرداد 1,000$)', NOW(), NOW());