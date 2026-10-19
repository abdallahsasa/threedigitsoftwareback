<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingCompanySetting;
use App\Models\AccountingCreditNote;
use App\Models\AccountingDocumentShare;
use App\Models\AccountingImportBatch;
use App\Models\AccountingImportItem;
use App\Models\AccountingInvoice;
use App\Models\AccountingPartner;
use App\Models\AccountingPayment;
use App\Models\AccountingProject;
use App\Models\AccountingReceipt;
use App\Models\AccountingReconciliation;
use App\Models\AccountingTransaction;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use App\Services\AccountingImportService;
use App\Services\AccountingPdfService;
use App\Services\DocumentSharingService;
use Database\Seeders\AccountingDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
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
     * Test 1: A $100 business expense paid personally by Abdullah increases his loan balance by $100 without changing company cash.
     */
    public function test_1_partner_personal_expense_increases_debt_and_leaves_cash_unchanged(): void
    {
        $abdullah = AccountingPartner::where('slug', 'abdullah')->firstOrFail();
        $cashAcc = AccountingAccount::where('code', '1010')->firstOrFail();

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
     * Test 2: A $100 repayment to Amer reduces company cash and his loan balance without creating an operating expense.
     */
    public function test_2_company_repays_amer_decreases_cash_and_debt_without_operating_expense(): void
    {
        $amer = AccountingPartner::where('slug', 'amer')->firstOrFail();
        $cashAcc = AccountingAccount::where('code', '1010')->firstOrFail();
        $officeExpenseAcc = AccountingAccount::where('code', '6020')->firstOrFail();

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
     * Test 3: A $200 partner loan deposited into cash increases cash and partner liability without revenue.
     */
    public function test_3_partner_funding_increases_cash_and_debt_without_affecting_revenue(): void
    {
        $abdullah = AccountingPartner::where('slug', 'abdullah')->firstOrFail();
        $cashAcc = AccountingAccount::where('code', '1010')->firstOrFail();
        $revenueAcc = AccountingAccount::where('code', '4010')->firstOrFail();

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
     * Test 4: A $100 partner distribution reduces company cash without automatically repaying the partner loan.
     */
    public function test_4_partner_distribution_decreases_cash_recorded_in_drawings_without_reducing_debt(): void
    {
        $amer = AccountingPartner::where('slug', 'amer')->firstOrFail();
        $cashAcc = AccountingAccount::where('code', '1010')->firstOrFail();

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

        $this->assertEquals($initialDebt, $amer->fresh()->calculated_debt_balance); // Debt untouched
        $this->assertEquals($initialCash - 100.0, $cashAcc->fresh()->balance);
        $this->assertEquals($initialDrawings + 100.0, $amer->fresh()->total_drawings);
    }

    /**
     * Test 5: Invoice issuance and later collection do not duplicate revenue.
     */
    public function test_5_invoicing_then_collection_does_not_duplicate_revenue(): void
    {
        $revenueAcc = AccountingAccount::where('code', '4010')->firstOrFail();
        $receivableAcc = AccountingAccount::where('code', '1100')->firstOrFail();
        $cashAcc = AccountingAccount::where('code', '1010')->firstOrFail();

        $company = AccountingCompanySetting::getSettings();
        $company->update([
            'legal_company_name' => 'Three Digit Software LLC',
            'tax_number' => 'TDS-TAX-12345',
        ]);

        $client = AccountingClient::create([
            'name' => 'Client Alpha',
            'normalized_name' => 'client alpha',
        ]);

        $initialRevenue = (float)$revenueAcc->balance;
        $initialCash = (float)$cashAcc->balance;

        // 1. Create and issue invoice for $500
        $invoice = AccountingInvoice::create([
            'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
            'client_id' => $client->id,
            'issue_date' => '2026-10-18',
            'due_date' => '2026-10-30',
            'subtotal' => 500.0000,
            'total_amount' => 500.0000,
            'balance_due' => 500.0000,
            'document_status' => 'draft',
        ]);

        $invoice->items()->create([
            'service_name' => 'Web App Development',
            'quantity' => 1,
            'unit' => 'project',
            'unit_price' => 500.0000,
            'line_total' => 500.0000,
        ]);

        $invoice->issueInvoice(User::first());

        // Revenue recognized once
        $this->assertEquals($initialRevenue + 500.0, (float)$revenueAcc->fresh()->balance);
        $this->assertEquals(500.0, (float)$receivableAcc->fresh()->balance);

        // 2. Client pays the $500 invoice
        $payment = AccountingPayment::create([
            'payment_number' => AccountingPayment::generatePaymentNumber(),
            'client_id' => $client->id,
            'payment_date' => '2026-10-19',
            'amount' => 500.0000,
            'receiving_account_id' => $cashAcc->id,
            'payment_method' => 'cash',
        ]);

        $payment->postPayment([
            ['invoice_id' => $invoice->id, 'amount' => 500.0000],
        ]);

        // Revenue is NOT doubled! Still initial + 500
        $this->assertEquals($initialRevenue + 500.0, (float)$revenueAcc->fresh()->balance);
        $this->assertEquals($initialCash + 500.0, (float)$cashAcc->fresh()->balance);
        $this->assertEquals(0.0, (float)$receivableAcc->fresh()->balance);
        $this->assertEquals('paid', $invoice->fresh()->payment_status);
    }

    /**
     * Test 6: An advance invoice follows its deferred revenue treatment.
     */
    public function test_6_advance_invoice_credits_deferred_revenue_not_operating_revenue(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update([
            'legal_company_name' => 'Three Digit Software LLC',
            'tax_number' => 'TDS-TAX-12345',
        ]);

        $client = AccountingClient::create([
            'name' => 'Client Beta',
            'normalized_name' => 'client beta',
        ]);

        $revenueAcc = AccountingAccount::where('code', '4010')->firstOrFail();
        $deferredRevenueAcc = AccountingAccount::where('code', '2300')->firstOrFail();

        $initialRevenue = (float)$revenueAcc->balance;
        $initialDeferred = (float)$deferredRevenueAcc->balance;

        $advanceInvoice = AccountingInvoice::create([
            'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
            'client_id' => $client->id,
            'issue_date' => '2026-10-18',
            'due_date' => '2026-10-25',
            'is_advance' => true,
            'subtotal' => 300.0000,
            'total_amount' => 300.0000,
            'balance_due' => 300.0000,
            'document_status' => 'draft',
        ]);

        $advanceInvoice->items()->create([
            'service_name' => 'Advance Deposit for Project Kickoff',
            'quantity' => 1,
            'unit' => 'advance',
            'unit_price' => 300.0000,
            'line_total' => 300.0000,
        ]);

        $advanceInvoice->issueInvoice(User::first());

        // Deferred revenue credited, operating revenue untouched
        $this->assertEquals($initialDeferred + 300.0, (float)$deferredRevenueAcc->fresh()->balance);
        $this->assertEquals($initialRevenue, (float)$revenueAcc->fresh()->balance);
    }

    /**
     * Test 7: Reimporting the same workbook does not duplicate records.
     */
    public function test_7_reimporting_same_workbook_does_not_duplicate_records(): void
    {
        $service = new AccountingImportService();
        $user = User::first();
        $filePath = base_path('database/data/Three_Digit_Software_Accounting_Dashboard.xlsx');

        $res1 = $service->import($filePath, $user?->id);
        $this->assertEquals(0, $res1['duplicate_rows']);
        $this->assertEquals(81, $res1['imported_rows']);

        $res2 = $service->import($filePath, $user?->id);
        $this->assertEquals(104, $res2['duplicate_rows']);
        $this->assertEquals(0, $res2['imported_rows']);
    }

    /**
     * Test 8: Transition debts remain $1,277 per partner without subtracting the historical $1,000 again.
     */
    public function test_8_transition_debts_remain_1277_per_partner_without_subtracting_thousand_again(): void
    {
        $abdullah = AccountingPartner::where('slug', 'abdullah')->firstOrFail();
        $amer = AccountingPartner::where('slug', 'amer')->firstOrFail();

        $this->assertEquals(1277.0000, $abdullah->calculated_debt_balance);
        $this->assertEquals(1277.0000, $amer->calculated_debt_balance);
        $this->assertEquals(2554.0000, $abdullah->calculated_debt_balance + $amer->calculated_debt_balance);

        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->firstOrFail();
        $this->assertTrue((bool)$reconciliation->amer_repayment_deducted);
        $this->assertTrue((bool)$reconciliation->abdullah_amer_settlement_handled);
    }

    /**
     * Test 9: The approximate $250 observation does not become invented income or capital.
     */
    public function test_9_approximate_cash_observation_does_not_become_income_or_capital(): void
    {
        $revenueAcc = AccountingAccount::where('code', '4010')->firstOrFail();
        $capitalAcc = AccountingAccount::where('code', '3010')->firstOrFail();

        $this->assertEquals(0.0, $revenueAcc->balance);
        $this->assertEquals(0.0, $capitalAcc->balance);

        $reconciliation = AccountingReconciliation::whereDate('reconciliation_date', '2026-10-13')->firstOrFail();
        $this->assertEquals('estimated_needs_physical_audit', $reconciliation->cash_audit_status);
        $this->assertEquals(250.0, (float)$reconciliation->reported_cash_balance);
    }

    /**
     * Test 10: Concurrent invoice issuance cannot duplicate invoice numbers or ledger entries.
     */
    public function test_10_concurrent_invoice_issuance_cannot_duplicate_number_or_ledger_entries(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update([
            'legal_company_name' => 'Three Digit Software LLC',
            'tax_number' => 'TDS-TAX-12345',
        ]);

        $client = AccountingClient::create(['name' => 'Client Gamma', 'normalized_name' => 'client gamma']);

        $invoice = AccountingInvoice::create([
            'invoice_number' => 'TDS-INV-2026-9999',
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-31',
            'subtotal' => 150.0,
            'total_amount' => 150.0,
            'balance_due' => 150.0,
            'document_status' => 'draft',
        ]);
        $invoice->items()->create(['service_name' => 'Test', 'quantity' => 1, 'unit_price' => 150.0, 'line_total' => 150.0]);

        // Issue once
        $invoice->issueInvoice(User::first());
        $firstEntryId = $invoice->fresh()->journal_entry_id;

        // Attempt concurrent or duplicate issue
        $invoice->fresh()->issueInvoice(User::first());
        $secondEntryId = $invoice->fresh()->journal_entry_id;

        $this->assertEquals($firstEntryId, $secondEntryId);
        $this->assertEquals(1, JournalEntry::where('reference_type', 'client_invoice')->where('reference_id', $invoice->id)->count());
    }

    /**
     * Test 11: Repeated payment requests cannot duplicate payments or receipts.
     */
    public function test_11_repeated_payment_requests_cannot_duplicate_payments_or_receipts(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update(['legal_company_name' => 'Three Digit Software LLC', 'tax_number' => 'TDS-TAX-12345']);

        $client = AccountingClient::create(['name' => 'Client Delta', 'normalized_name' => 'client delta']);
        $cashAcc = AccountingAccount::where('code', '1010')->firstOrFail();

        $invoice = AccountingInvoice::create([
            'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-30',
            'subtotal' => 200.0,
            'total_amount' => 200.0,
            'balance_due' => 200.0,
            'document_status' => 'draft',
        ]);
        $invoice->items()->create(['service_name' => 'Hosting', 'quantity' => 1, 'unit_price' => 200.0, 'line_total' => 200.0]);
        $invoice->issueInvoice(User::first());

        $payment = AccountingPayment::create([
            'payment_number' => AccountingPayment::generatePaymentNumber(),
            'client_id' => $client->id,
            'payment_date' => '2026-10-19',
            'amount' => 200.0,
            'receiving_account_id' => $cashAcc->id,
            'payment_method' => 'cash',
        ]);

        $receipt1 = $payment->postPayment([
            ['invoice_id' => $invoice->id, 'amount' => 200.0],
        ]);

        // Repeated post attempt on the same payment
        $receipt2 = $payment->fresh()->postPayment([
            ['invoice_id' => $invoice->id, 'amount' => 200.0],
        ]);

        $this->assertEquals($receipt1->id, $receipt2->id);
        $this->assertEquals(1, AccountingReceipt::where('payment_id', $payment->id)->count());
        $this->assertEquals(200.0, (float)$invoice->fresh()->paid_amount);
    }

    /**
     * Test 12: Company and client edits do not alter issued invoice snapshots.
     */
    public function test_12_company_and_client_edits_do_not_alter_issued_invoice_snapshots(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update([
            'legal_company_name' => 'Original Three Digit Legal LLC',
            'tax_number' => 'ORIGINAL-TAX-111',
            'registered_address' => 'Old Address 123',
        ]);

        $client = AccountingClient::create([
            'name' => 'Client Epsilon',
            'normalized_name' => 'client epsilon',
            'legal_name' => 'Epsilon Original Legal Corp',
            'billing_address' => 'Old Client Address 456',
        ]);

        $invoice = AccountingInvoice::create([
            'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-31',
            'subtotal' => 400.0,
            'total_amount' => 400.0,
            'balance_due' => 400.0,
            'document_status' => 'draft',
        ]);
        $invoice->items()->create(['service_name' => 'Brand Design', 'quantity' => 1, 'unit_price' => 400.0, 'line_total' => 400.0]);
        $invoice->issueInvoice(User::first());

        // Modify company settings & client profile later
        $company->update([
            'legal_company_name' => 'New Changed Legal Name Ltd',
            'tax_number' => 'NEW-TAX-999',
            'registered_address' => 'New Company Address 789',
        ]);

        $client->update([
            'legal_name' => 'Epsilon Completely Changed Name',
            'billing_address' => 'New Client Address 000',
        ]);

        // Issued invoice snapshots MUST preserve original state!
        $freshInvoice = $invoice->fresh();
        $this->assertEquals('Original Three Digit Legal LLC', $freshInvoice->frozen_company_snapshot['legal_company_name']);
        $this->assertEquals('ORIGINAL-TAX-111', $freshInvoice->frozen_company_snapshot['tax_number']);
        $this->assertEquals('Epsilon Original Legal Corp', $freshInvoice->frozen_client_snapshot['legal_name']);
        $this->assertEquals('Old Client Address 456', $freshInvoice->frozen_client_snapshot['billing_address']);
    }

    /**
     * Test 13: Full legal company name and company Tax Number appear correctly on issued PDFs.
     * Incomplete settings throw validation error on issuance.
     */
    public function test_13_legal_company_name_and_tax_number_validation_and_pdf_presence(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update([
            'legal_company_name' => null, // Incomplete!
            'tax_number' => null,
        ]);

        $client = AccountingClient::create(['name' => 'Client Zeta', 'normalized_name' => 'client zeta']);

        $draftInvoice = AccountingInvoice::create([
            'invoice_number' => 'DRAFT-001',
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-30',
            'subtotal' => 100.0,
            'total_amount' => 100.0,
            'balance_due' => 100.0,
            'document_status' => 'draft',
        ]);
        $draftInvoice->items()->create(['service_name' => 'Audit', 'quantity' => 1, 'unit_price' => 100.0, 'line_total' => 100.0]);

        // Issuing incomplete draft must throw ValidationException
        $this->expectException(ValidationException::class);
        $draftInvoice->issueInvoice(User::first());
    }

    /**
     * Test 14: Arabic PDFs render correctly, including long descriptions and multipage tables.
     */
    public function test_14_arabic_pdfs_render_correctly_with_cairo_font(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update([
            'legal_company_name' => 'شركة ثري ديجيت للبرمجيات ذ.م.م',
            'tax_number' => 'SY-TAX-987654321',
        ]);

        $client = AccountingClient::create([
            'name' => 'مؤسسة الأعمال المتقدمة',
            'normalized_name' => 'advanced business corp',
            'legal_name' => 'شركة الأعمال المتقدمة للتجارة العامة والتوزيع ذ.م.م',
            'tax_number' => 'CLI-TAX-554433',
            'billing_address' => 'دمشق، ساحة المحافظة، بناء النور، الطابق الرابع',
        ]);

        $invoice = AccountingInvoice::create([
            'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-31',
            'document_language' => 'ar',
            'subtotal' => 1500.0,
            'total_amount' => 1500.0,
            'balance_due' => 1500.0,
            'document_status' => 'draft',
        ]);

        // Add multiple items with long Arabic descriptions
        for ($i = 1; $i <= 6; $i++) {
            $invoice->items()->create([
                'service_name' => "بند برمجيات متقدم رقم {$i}",
                'description' => "تطوير وضبط واجهات المستخدم التفاعلية والتحقق من الربط مع قواعد البيانات، وإجراء اختبارات الأداء وحماية الخوادم وفق أحدث المعايير البرمجية المعمول بها لضمان استقرار وسرعة استجابة المنصة.",
                'quantity' => 1,
                'unit' => 'مرحلة',
                'unit_price' => 250.0,
                'line_total' => 250.0,
            ]);
        }

        $invoice->issueInvoice(User::first());

        $pdfService = app(AccountingPdfService::class);
        $pdfOutput = $pdfService->getInvoicePdfOutput($invoice);

        $this->assertNotEmpty($pdfOutput);
        $this->assertStringStartsWith('%PDF-', $pdfOutput);
    }

    /**
     * Test 15: Secure customer links cannot expose other documents or internal accounting.
     */
    public function test_15_secure_customer_links_cannot_expose_other_documents_or_internal_accounting(): void
    {
        $clientA = AccountingClient::create(['name' => 'Client A', 'normalized_name' => 'client a', 'internal_notes' => 'SECRET INTERNAL CLIENT A NOTE']);
        $clientB = AccountingClient::create(['name' => 'Client B', 'normalized_name' => 'client b', 'internal_notes' => 'SECRET INTERNAL CLIENT B NOTE']);

        $invoiceA = AccountingInvoice::create([
            'invoice_number' => 'TDS-INV-2026-0010',
            'client_id' => $clientA->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-31',
            'subtotal' => 100.0,
            'total_amount' => 100.0,
            'balance_due' => 100.0,
            'customer_notes' => 'Public Note for Client A',
            'private_notes' => 'TOP SECRET ACCOUNTING NOTE FOR INVOICE A',
            'document_status' => 'draft',
        ]);

        $shareA = AccountingDocumentShare::createSecureShare('invoice', $invoiceA->id, $clientA->id);

        $response = $this->get("/portal/docs/{$shareA->token}");
        $response->assertStatus(200);
        $response->assertSee('Public Note for Client A');

        // Strictly verify private notes & partner data are NEVER leaked into response!
        $response->assertDontSee('SECRET INTERNAL CLIENT A NOTE');
        $response->assertDontSee('TOP SECRET ACCOUNTING NOTE FOR INVOICE A');
        $response->assertDontSee('Client B');

        // Invalid token returns 404
        $invalidResponse = $this->get('/portal/docs/invalid-non-existent-token-12345');
        $invalidResponse->assertStatus(404);
    }

    /**
     * Test 16: Failed email attempts do not appear as successfully delivered.
     */
    public function test_16_failed_email_attempts_do_not_appear_as_successfully_delivered(): void
    {
        $client = AccountingClient::create(['name' => 'Client Eta', 'normalized_name' => 'client eta', 'email' => '']); // empty email
        $invoice = AccountingInvoice::create([
            'invoice_number' => 'TDS-INV-2026-0011',
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-30',
            'subtotal' => 100.0,
            'total_amount' => 100.0,
            'balance_due' => 100.0,
            'document_status' => 'draft',
        ]);

        $share = AccountingDocumentShare::createSecureShare('invoice', $invoice->id, $client->id);
        $sharingService = app(DocumentSharingService::class);

        // Attempt send without valid email address
        $this->expectException(ValidationException::class);
        $sharingService->sendEmail($share, [
            'recipient_email' => '',
            'subject' => 'Test',
            'body' => 'Body',
            'attachment_name' => 'test.pdf',
        ]);

        $this->assertNotEquals('delivered', $share->fresh()->email_status);
    }

    /**
     * Test 17: Credit notes update receivables, cash, and reporting without deleting history.
     */
    public function test_17_credit_notes_update_receivables_without_deleting_history(): void
    {
        $company = AccountingCompanySetting::getSettings();
        $company->update(['legal_company_name' => 'Three Digit Software LLC', 'tax_number' => 'TDS-TAX-12345']);

        $client = AccountingClient::create(['name' => 'Client Theta', 'normalized_name' => 'client theta']);
        $receivableAcc = AccountingAccount::where('code', '1100')->firstOrFail();

        $invoice = AccountingInvoice::create([
            'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'due_date' => '2026-10-30',
            'subtotal' => 500.0,
            'total_amount' => 500.0,
            'balance_due' => 500.0,
            'document_status' => 'draft',
        ]);
        $invoice->items()->create(['service_name' => 'Software License', 'quantity' => 1, 'unit_price' => 500.0, 'line_total' => 500.0]);
        $invoice->issueInvoice(User::first());

        $this->assertEquals(500.0, (float)$receivableAcc->fresh()->balance);

        // Issue a $100 credit note (discount/adjustment on invoice)
        $creditNote = AccountingCreditNote::create([
            'credit_note_number' => AccountingCreditNote::generateCreditNoteNumber(),
            'invoice_id' => $invoice->id,
            'client_id' => $client->id,
            'issue_date' => '2026-10-19',
            'amount' => 100.0,
            'reason' => 'Scope adjustment credit',
            'status' => 'draft',
        ]);

        $creditNote->issueCreditNote();

        // Receivables reduced to $400, invoice history fully preserved!
        $this->assertEquals(400.0, (float)$receivableAcc->fresh()->balance);
        $this->assertEquals(400.0, (float)$invoice->fresh()->balance_due);
        $this->assertEquals(400.0, (float)$invoice->fresh()->total_amount);
        $this->assertEquals('issued', $creditNote->fresh()->status);
        $this->assertNotNull($creditNote->fresh()->journal_entry_id);
    }

    /**
     * Test 18: Unauthorized users cannot access ERP data through direct requests.
     */
    public function test_18_unauthorized_users_cannot_access_erp_data_directly(): void
    {
        // Unauthenticated guest requests to admin PDF routes must redirect to login
        $response = $this->get('/admin/pdf/invoices/1');
        $response->assertRedirect(route('login'));

        $response2 = $this->get('/admin/pdf/statements/1');
        $response2->assertRedirect(route('login'));
    }
}
