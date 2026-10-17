<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingImportBatch;
use App\Models\AccountingImportItem;
use App\Models\AccountingPartner;
use App\Models\AccountingProject;
use App\Models\AccountingReconciliation;
use App\Models\AccountingTransaction;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use App\Services\AccountingImportService;
use Database\Seeders\AccountingDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountingDatabaseSeeder::class);
    }

    /**
     * Test 1: دفع عبدالله مصروفاً شخصياً للشركة بقيمة 100:
     * دينه يزيد 100، وصندوق الشركة لا يتغير.
     */
    public function test_1_partner_personal_expense_increases_debt_and_leaves_cash_unchanged(): void
    {
        $abdullah = AccountingPartner::where('slug', 'abdullah')->first();
        $cashAcc = AccountingAccount::where('code', '1010')->first();

        $initialDebt = $abdullah->calculated_debt_balance;
        $initialCash = $cashAcc->balance;

        $trx = AccountingTransaction::create([
            'transaction_number' => AccountingTransaction::generateTransactionNumber(),
            'transaction_date' => '2026-10-18',
            'transaction_type' => 'partner_personal_expense',
            'amount' => 100.0000,
            'partner_id' => $abdullah->id,
            'expense_category' => 'Office',
            'payer_type' => 'partner_abdullah',
            'money_source' => 'personal_abdullah',
            'notes' => 'شراء مستلزمات مكتبية من المال الشخصي لعبدالله',
            'status' => 'completed',
            'review_status' => 'approved',
        ]);

        $trx->postToLedger();

        $this->assertEquals($initialDebt + 100.0, $abdullah->fresh()->calculated_debt_balance);
        $this->assertEquals($initialCash, $cashAcc->fresh()->balance);
    }

    /**
     * Test 2: سداد الشركة لعامر 100:
     * صندوقها ينقص 100، ودينه ينقص 100، ومصروف التشغيل لا يتغير.
     */
    public function test_2_company_repays_amer_decreases_cash_and_debt_without_operating_expense(): void
    {
        $amer = AccountingPartner::where('slug', 'amer')->first();
        $cashAcc = AccountingAccount::where('code', '1010')->first();
        $officeExpenseAcc = AccountingAccount::where('code', '6020')->first();

        $initialDebt = $amer->calculated_debt_balance;
        $initialCash = $cashAcc->balance;
        $initialExpense = $officeExpenseAcc->balance;

        $trx = AccountingTransaction::create([
            'transaction_number' => AccountingTransaction::generateTransactionNumber(),
            'transaction_date' => '2026-10-18',
            'transaction_type' => 'partner_debt_repayment',
            'amount' => 100.0000,
            'partner_id' => $amer->id,
            'payer_type' => 'company',
            'money_source' => 'company_funds',
            'notes' => 'سداد دفعة من دين عامر',
            'status' => 'completed',
            'review_status' => 'approved',
        ]);

        $trx->postToLedger();

        $this->assertEquals($initialDebt - 100.0, $amer->fresh()->calculated_debt_balance);
        $this->assertEquals($initialCash - 100.0, $cashAcc->fresh()->balance);
        $this->assertEquals($initialExpense, $officeExpenseAcc->fresh()->balance);
    }

    /**
     * Test 3: تمويل شريك للصندوق بقيمة 200:
     * الصندوق والدين يزيدان 200، والإيرادات لا تتغير.
     */
    public function test_3_partner_funding_increases_cash_and_debt_without_affecting_revenue(): void
    {
        $abdullah = AccountingPartner::where('slug', 'abdullah')->first();
        $cashAcc = AccountingAccount::where('code', '1010')->first();
        $revenueAcc = AccountingAccount::where('code', '4010')->first();

        $initialDebt = $abdullah->calculated_debt_balance;
        $initialCash = $cashAcc->balance;
        $initialRevenue = $revenueAcc->balance;

        $trx = AccountingTransaction::create([
            'transaction_number' => AccountingTransaction::generateTransactionNumber(),
            'transaction_date' => '2026-10-18',
            'transaction_type' => 'partner_funding',
            'amount' => 200.0000,
            'partner_id' => $abdullah->id,
            'payer_type' => 'partner_abdullah',
            'money_source' => 'personal_abdullah',
            'notes' => 'تمويل إضافي قابل للاسترداد لصندوق الشركة',
            'status' => 'completed',
            'review_status' => 'approved',
        ]);

        $trx->postToLedger();

        $this->assertEquals($initialDebt + 200.0, $abdullah->fresh()->calculated_debt_balance);
        $this->assertEquals($initialCash + 200.0, $cashAcc->fresh()->balance);
        $this->assertEquals($initialRevenue, $revenueAcc->fresh()->balance);
    }

    /**
     * Test 4: توزيع للشريك بقيمة 100:
     * الصندوق ينقص 100، ويظهر التوزيع منفصلاً، دون سداد دين التمويل تلقائياً.
     */
    public function test_4_partner_distribution_decreases_cash_recorded_in_drawings_without_reducing_debt(): void
    {
        $amer = AccountingPartner::where('slug', 'amer')->first();
        $cashAcc = AccountingAccount::where('code', '1010')->first();

        $initialDebt = $amer->calculated_debt_balance;
        $initialCash = $cashAcc->balance;
        $initialDrawings = $amer->total_drawings;

        $trx = AccountingTransaction::create([
            'transaction_number' => AccountingTransaction::generateTransactionNumber(),
            'transaction_date' => '2026-10-18',
            'transaction_type' => 'partner_distribution',
            'amount' => 100.0000,
            'partner_id' => $amer->id,
            'payer_type' => 'company',
            'money_source' => 'company_funds',
            'notes' => 'توزيع أرباح للشريك عامر',
            'status' => 'completed',
            'review_status' => 'approved',
        ]);

        $trx->postToLedger();

        $this->assertEquals($initialDebt, $amer->fresh()->calculated_debt_balance); // Debt untouched!
        $this->assertEquals($initialCash - 100.0, $cashAcc->fresh()->balance);
        $this->assertEquals($initialDrawings + 100.0, $amer->fresh()->total_drawings);
    }

    /**
     * Test 5: إصدار فاتورة ثم تحصيلها لا يضاعف الإيراد.
     */
    public function test_5_invoicing_then_collection_does_not_double_revenue(): void
    {
        $revenueAcc = AccountingAccount::where('code', '4010')->first();
        $receivableAcc = AccountingAccount::where('code', '1100')->first();
        $cashAcc = AccountingAccount::where('code', '1010')->first();

        $client = AccountingClient::firstOrCreate(
            ['normalized_name' => 'test client'],
            ['name' => 'Test Client']
        );

        $initialRevenue = $revenueAcc->balance;
        $initialCash = $cashAcc->balance;

        // Step 1: Issue Invoice $500 -> Debit Receivable 500, Credit Revenue 500
        $invoiceEntry = JournalEntry::create([
            'entry_number' => JournalEntry::generateEntryNumber(),
            'entry_date' => '2026-10-18',
            'reference_type' => 'client_invoice',
            'description' => 'فاتورة مشروع رقم INV-100',
            'status' => 'posted',
        ]);
        $invoiceEntry->lines()->create([
            'account_id' => $receivableAcc->id,
            'client_id' => $client->id,
            'debit' => 500.0000,
            'credit' => 0,
            'description' => 'استحقاق فاتورة',
        ]);
        $invoiceEntry->lines()->create([
            'account_id' => $revenueAcc->id,
            'client_id' => $client->id,
            'debit' => 0,
            'credit' => 500.0000,
            'description' => 'إيراد خدمات',
        ]);

        // Revenue is recognized once
        $this->assertEquals($initialRevenue + 500.0, $revenueAcc->fresh()->balance);
        $this->assertEquals(500.0, $receivableAcc->fresh()->balance);

        // Step 2: Collection against receivable -> Debit Cash 500, Credit Receivable 500 (NOT Revenue again!)
        $collectEntry = JournalEntry::create([
            'entry_number' => JournalEntry::generateEntryNumber(),
            'entry_date' => '2026-10-19',
            'reference_type' => 'client_collection_settlement',
            'description' => 'تحصيل قيمة الفاتورة INV-100',
            'status' => 'posted',
        ]);
        $collectEntry->lines()->create([
            'account_id' => $cashAcc->id,
            'client_id' => $client->id,
            'debit' => 500.0000,
            'credit' => 0,
            'description' => 'قبض كاش',
        ]);
        $collectEntry->lines()->create([
            'account_id' => $receivableAcc->id,
            'client_id' => $client->id,
            'debit' => 0,
            'credit' => 500.0000,
            'description' => 'تسديد الذمة المدينة',
        ]);

        // Revenue remains 500 (NOT doubled to 1,000!)
        $this->assertEquals($initialRevenue + 500.0, $revenueAcc->fresh()->balance);
        $this->assertEquals($initialCash + 500.0, $cashAcc->fresh()->balance);
        $this->assertEquals(0.0, $receivableAcc->fresh()->balance);
    }

    /**
     * Test 6: إعادة استيراد الملف نفسه لا تكرر الحركات.
     */
    public function test_6_reimporting_same_file_does_not_duplicate_transactions(): void
    {
        $service = new AccountingImportService();
        $user = User::first();
        $filePath = base_path('database/data/Three_Digit_Software_Accounting_Dashboard.xlsx');

        // First import
        $res1 = $service->import($filePath, $user?->id);
        $this->assertEquals(0, $res1['duplicate_rows']);
        $this->assertEquals(81, $res1['imported_rows']);

        // Second import of the same file
        $res2 = $service->import($filePath, $user?->id);
        $this->assertEquals(104, $res2['duplicate_rows']);
        $this->assertEquals(0, $res2['imported_rows']);
    }

    /**
     * Test 7: بعد إعداد نقطة الانتقال، يظهر دين كل شريك 1,277،
     * ولا يُخصم سداد الألف التاريخي مرة ثانية.
     */
    public function test_7_cutover_partner_debits_are_1277_each_and_thousand_refund_not_deducted_again(): void
    {
        $abdullah = AccountingPartner::where('slug', 'abdullah')->first();
        $amer = AccountingPartner::where('slug', 'amer')->first();

        $this->assertEquals(1277.0000, $abdullah->calculated_debt_balance);
        $this->assertEquals(1277.0000, $amer->calculated_debt_balance);
        $this->assertEquals(2554.0000, $abdullah->calculated_debt_balance + $amer->calculated_debt_balance);

        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->first();
        $this->assertNotNull($reconciliation);
        $this->assertTrue((bool)$reconciliation->amer_repayment_deducted);
        $this->assertTrue((bool)$reconciliation->abdullah_amer_settlement_handled);
    }

    /**
     * Test 8: رصيد النقد التقريبي 250 لا يظهر كإيراد أو رأس مال جديد.
     */
    public function test_8_approximate_cash_250_is_not_revenue_or_capital(): void
    {
        $revenueAcc = AccountingAccount::where('code', '4010')->first();
        $capitalAcc = AccountingAccount::where('code', '3010')->first();

        // Revenue and Capital balances from cutover entry must be 0!
        $this->assertEquals(0.0, $revenueAcc->balance);
        $this->assertEquals(0.0, $capitalAcc->balance);

        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->first();
        $this->assertEquals('estimated_needs_physical_audit', $reconciliation->cash_audit_status);
        $this->assertEquals(250.0, (float)$reconciliation->reported_cash_balance);
    }

    /**
     * Test 9: الحركات غير المحسومة تبقى ظاهرة للمراجعة، ولا تنتج ربحاً نهائياً مضللاً.
     */
    public function test_9_unresolved_items_remain_in_review_queue(): void
    {
        $service = new AccountingImportService();
        $user = User::first();
        $filePath = base_path('database/data/Three_Digit_Software_Accounting_Dashboard.xlsx');

        if (AccountingImportItem::count() === 0) {
            $service->import($filePath, $user?->id);
        }

        $pendingItems = AccountingImportItem::where('review_status', 'pending_review')->get();

        $this->assertGreaterThan(0, $pendingItems->count());

        // Check specific items required to be in review:
        // 1. Salaries / partner shares
        $salaryReviews = $pendingItems->filter(fn($i) => ($i->raw_data['Category'] ?? '') === 'Salaries');
        $this->assertNotEmpty($salaryReviews);

        // 2. Sheet1 rows
        $sheet1Reviews = $pendingItems->filter(fn($i) => $i->sheet_name === 'Sheet1');
        $this->assertCount(2, $sheet1Reviews);

        // 3. Othman items
        $othmanReviews = $pendingItems->filter(fn($i) => str_contains(strtolower($i->raw_data['Paid By'] ?? ''), 'othman'));
        $this->assertNotEmpty($othmanReviews);
    }
}
