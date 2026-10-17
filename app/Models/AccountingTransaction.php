<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class AccountingTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:4',
        'attachments' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(AccountingPartner::class, 'partner_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccountingProject::class, 'project_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateTransactionNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('transaction_date', $year)->count() + 1;
        return sprintf('TRX-%s-%04d', $year, $count);
    }

    /**
     * Post this business transaction to the double-entry general ledger
     */
    public function postToLedger(?int $userId = null): JournalEntry
    {
        return DB::transaction(function () use ($userId) {
            // If already posted, return existing journal entry
            if ($this->journal_entry_id && $this->journalEntry) {
                return $this->journalEntry;
            }

            $journalEntry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $this->transaction_date,
                'reference_type' => 'accounting_transaction',
                'reference_id' => $this->id,
                'description' => $this->notes ?: ($this->transaction_type . ' - ' . $this->transaction_number),
                'status' => 'posted',
                'created_by' => $userId ?: $this->created_by,
            ]);

            $cashAccount = $this->account ?: AccountingAccount::where('code', '1010')->first();
            $amount = (float) $this->amount;

            switch ($this->transaction_type) {
                case 'client_collection':
                    // Debit Cash, Credit Revenue (or Accounts Receivable)
                    $revenueAccount = AccountingAccount::where('code', '4010')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'client_id' => $this->client_id,
                        'project_id' => $this->project_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'تحصيل من عميل: ' . ($this->client?->name ?: $this->payer_name),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $revenueAccount->id,
                        'client_id' => $this->client_id,
                        'project_id' => $this->project_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'إيراد مشروع / خدمة: ' . ($this->project?->name ?: 'خدمات عامة'),
                    ]);
                    break;

                case 'partner_funding':
                    // Debit Cash, Credit Partner Debt
                    $partnerDebtAccount = $this->partner?->debtAccount ?: AccountingAccount::where('code', '2101')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'تمويل صندوق الشركة من الشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $partnerDebtAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'زيادة دين الشركة للشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    break;

                case 'partner_personal_expense':
                    // Debit Expense (or Asset), Credit Partner Debt. Cash is NOT touched!
                    $expenseAccount = $this->resolveExpenseAccount();
                    $partnerDebtAccount = $this->partner?->debtAccount;
                    if (!$partnerDebtAccount && $this->partner_id) {
                        $partnerDebtAccount = AccountingAccount::where('partner_id', $this->partner_id)
                            ->where('sub_type', 'partner_debt')->first();
                    }
                    if (!$partnerDebtAccount) {
                        $partnerDebtAccount = AccountingAccount::where('code', '2101')->first();
                    }

                    $journalEntry->lines()->create([
                        'account_id' => $expenseAccount->id,
                        'partner_id' => $this->partner_id,
                        'project_id' => $this->project_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'مصروف تشغيل دفعه الشريك من ماله الخاص: ' . ($this->notes ?: $this->expense_category),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $partnerDebtAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'استحقاق دين للشريك مقابل مصروف مدفوع شخصياً: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    break;

                case 'company_expense':
                    // Debit Expense, Credit Cash
                    $expenseAccount = $this->resolveExpenseAccount();
                    $journalEntry->lines()->create([
                        'account_id' => $expenseAccount->id,
                        'project_id' => $this->project_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'مصروف تشغيل مدفوع من أموال الشركة: ' . ($this->notes ?: $this->expense_category),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'صرف من صندوق الشركة للمصروف: ' . ($this->notes ?: $this->expense_category),
                    ]);
                    break;

                case 'partner_debt_repayment':
                    // Debit Partner Debt, Credit Cash. Operating expense is NOT touched!
                    $partnerDebtAccount = $this->partner?->debtAccount ?: AccountingAccount::where('code', '2102')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $partnerDebtAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'سداد جزء من دين الشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'صرف من صندوق الشركة لسداد دين الشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    break;

                case 'partner_distribution':
                    // Debit Partner Drawings (Equity contra), Credit Cash. Partner Debt is NOT touched!
                    $drawingsAccount = $this->partner?->drawingsAccount ?: AccountingAccount::where('code', '3101')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $drawingsAccount->id,
                        'partner_id' => $this->partner_id,
                        'project_id' => $this->project_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'سحب شريك / توزيع أرباح: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'صرف من صندوق الشركة لتوزيع/سحب الشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    break;

                case 'capital_injection':
                    // Debit Cash, Credit Capital
                    $capitalAccount = AccountingAccount::where('code', '3010')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'رأس مال تأسيسي أودع في الصندوق',
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $capitalAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'رأس المال التأسيسي للشركة',
                    ]);
                    break;

                case 'partner_project_labor':
                    // Debit Direct Project Cost, Credit Partner Debt (or cash)
                    $laborAccount = AccountingAccount::where('code', '5010')->first();
                    $partnerDebtAccount = $this->partner?->debtAccount ?: AccountingAccount::where('code', '2101')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $laborAccount->id,
                        'partner_id' => $this->partner_id,
                        'project_id' => $this->project_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'أجر تنفيذ مشروع للشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $partnerDebtAccount->id,
                        'partner_id' => $this->partner_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'استحقاق أجر تنفيذ للشريك: ' . ($this->partner?->name_ar ?: 'شريك'),
                    ]);
                    break;

                default:
                    // Generic balanced entry for adjustment
                    $adjAccount = AccountingAccount::where('code', '6090')->first();
                    $journalEntry->lines()->create([
                        'account_id' => $adjAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => $this->notes ?: 'حركة تسوية محاسبية',
                    ]);
                    $journalEntry->lines()->create([
                        'account_id' => $cashAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => $this->notes ?: 'تسوية صندوق',
                    ]);
                    break;
            }

            $this->update(['journal_entry_id' => $journalEntry->id]);

            AccountingAuditLog::create([
                'user_id' => $userId ?: $this->created_by,
                'action' => 'post_transaction',
                'entity_type' => self::class,
                'entity_id' => $this->id,
                'description' => "ترحيل الحركة {$this->transaction_number} بقيد متوازن رقم {$journalEntry->entry_number}",
                'new_values' => $this->toArray(),
            ]);

            return $journalEntry;
        });
    }

    /**
     * Resolve appropriate expense account based on category
     */
    public function resolveExpenseAccount(): AccountingAccount
    {
        $cat = strtolower(trim($this->expense_category ?: ''));
        if (str_contains($cat, 'rent') || str_contains(strtolower($this->notes ?: ''), 'rent')) {
            return AccountingAccount::where('code', '6010')->first()
                ?: AccountingAccount::where('type', 'expense')->first();
        }
        if (str_contains($cat, 'software') || str_contains($cat, 'capcut') || str_contains($cat, 'tool')) {
            return AccountingAccount::where('code', '6030')->first()
                ?: AccountingAccount::where('type', 'expense')->first();
        }
        if (str_contains($cat, 'salaries') || str_contains($cat, 'direct') || str_contains($cat, 'labor')) {
            return AccountingAccount::where('code', '5010')->first()
                ?: AccountingAccount::where('type', 'expense')->first();
        }
        if (str_contains($cat, 'ad') || str_contains($cat, 'marketing') || str_contains($cat, 'coms')) {
            return AccountingAccount::where('code', '6040')->first()
                ?: AccountingAccount::where('type', 'expense')->first();
        }
        return AccountingAccount::where('code', '6020')->first()
            ?: AccountingAccount::where('type', 'expense')->first();
    }

    /**
     * Reverse this transaction via an exact opposite reversing journal entry
     */
    public function reverse(string $reason, ?int $userId = null): JournalEntry
    {
        return DB::transaction(function () use ($reason, $userId) {
            if (!$this->journalEntry) {
                throw new \Exception('لا يمكن عكس حركة ليس لها قيد محاسبي');
            }

            $originalEntry = $this->journalEntry;
            $reversalEntry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => now()->toDateString(),
                'reference_type' => 'accounting_transaction_reversal',
                'reference_id' => $this->id,
                'description' => "قيد عكسي للحركة {$this->transaction_number} - سبب العكس: {$reason}",
                'status' => 'posted',
                'is_reversal' => true,
                'reversed_entry_id' => $originalEntry->id,
                'created_by' => $userId,
            ]);

            foreach ($originalEntry->lines as $line) {
                $reversalEntry->lines()->create([
                    'account_id' => $line->account_id,
                    'partner_id' => $line->partner_id,
                    'client_id' => $line->client_id,
                    'project_id' => $line->project_id,
                    'debit' => $line->credit, // swapped
                    'credit' => $line->debit, // swapped
                    'description' => "عكس: " . $line->description,
                ]);
            }

            $originalEntry->update(['status' => 'reversed']);
            $this->update([
                'status' => 'reversed',
                'review_status' => 'rejected',
                'review_reason' => $reason,
            ]);

            AccountingAuditLog::create([
                'user_id' => $userId,
                'action' => 'reverse_transaction',
                'entity_type' => self::class,
                'entity_id' => $this->id,
                'description' => "عكس الحركة {$this->transaction_number} بالقيد العكسي {$reversalEntry->entry_number}",
                'new_values' => ['reason' => $reason, 'reversal_entry_id' => $reversalEntry->id],
            ]);

            return $reversalEntry;
        });
    }
}
