<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingPayment extends Model
{
    use HasFactory;

    protected $table = 'accounting_payments';

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:4',
        'exchange_rate' => 'decimal:4',
        'allocated_amount' => 'decimal:4',
        'unallocated_amount' => 'decimal:4',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function receivingAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'receiving_account_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(AccountingPaymentAllocation::class, 'payment_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(AccountingReceipt::class, 'payment_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generatePaymentNumber(): string
    {
        $year = date('Y');
        $base = "TDS-PAY-{$year}-";

        $last = self::where('payment_number', 'LIKE', "{$base}%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last->payment_number);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return sprintf('%s%04d', $base, $nextSeq);
    }

    /**
     * Process payment, apply invoice allocations, post to double-entry ledger, and generate receipt
     */
    public function postPayment(array $invoiceAllocations = []): AccountingReceipt
    {
        if ($this->journal_entry_id) {
            // Already posted
            return $this->receipt ?: $this->generateReceipt();
        }

        return DB::transaction(function () use ($invoiceAllocations) {
            $totalAmount = (float)$this->amount;
            $allocatedTotal = 0.0;

            // 1. Process explicit allocations
            foreach ($invoiceAllocations as $alloc) {
                $invoiceId = $alloc['invoice_id'];
                $allocAmount = round((float)$alloc['amount'], 4);

                if ($allocAmount <= 0) {
                    continue;
                }

                $invoice = AccountingInvoice::where('id', $invoiceId)->lockForUpdate()->firstOrFail();

                // Prevent over-allocation beyond invoice balance
                if ($allocAmount > (float)$invoice->balance_due + 0.0001) {
                    throw ValidationException::withMessages([
                        'allocations' => "مبلغ التخصيص ({$allocAmount}) أكبر من الرصيد المتبقي على الفاتورة {$invoice->invoice_number} ({$invoice->balance_due}).",
                    ]);
                }

                AccountingPaymentAllocation::create([
                    'payment_id' => $this->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $allocAmount,
                ]);

                // Update invoice
                $invoice->paid_amount = round((float)$invoice->paid_amount + $allocAmount, 4);
                $invoice->updatePaymentStatus();

                $allocatedTotal += $allocAmount;
            }

            // Explicitly track allocated vs unallocated/advance
            $unallocated = round(max(0.0, $totalAmount - $allocatedTotal), 4);
            $this->allocated_amount = $allocatedTotal;
            $this->unallocated_amount = $unallocated;

            // 2. Double-Entry Posting
            $cashAcc = AccountingAccount::findOrFail($this->receiving_account_id);
            $receivableAcc = AccountingAccount::where('code', '1100')->firstOrFail();
            $advanceAcc = AccountingAccount::where('code', '2300')->firstOrFail(); // Deferred revenue / customer advance

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $this->payment_date->toDateString(),
                'reference_type' => 'client_payment',
                'reference_id' => $this->id,
                'description' => "سند قبض رقم {$this->payment_number} من العميل {$this->client->display_name}",
                'status' => 'posted',
                'created_by' => auth()->id() ?? $this->created_by,
            ]);

            // Debit receiving cash/bank account
            $entry->lines()->create([
                'account_id' => $cashAcc->id,
                'client_id' => $this->client_id,
                'debit' => $totalAmount,
                'credit' => 0.0000,
                'description' => "إيداع مقبوضات بحساب {$cashAcc->name_ar}",
            ]);

            // Credit Accounts Receivable for allocated portion
            if ($allocatedTotal > 0) {
                $entry->lines()->create([
                    'account_id' => $receivableAcc->id,
                    'client_id' => $this->client_id,
                    'debit' => 0.0000,
                    'credit' => $allocatedTotal,
                    'description' => "سداد فواتير مستحقة من العميل {$this->client->display_name}",
                ]);
            }

            // Credit Deferred Revenue / Customer Advances for unallocated portion
            if ($unallocated > 0) {
                $entry->lines()->create([
                    'account_id' => $advanceAcc->id,
                    'client_id' => $this->client_id,
                    'debit' => 0.0000,
                    'credit' => $unallocated,
                    'description' => "دفعة مقدمة غير مخصصة / رصيد دائن للعميل {$this->client->display_name}",
                ]);
            }

            $this->journal_entry_id = $entry->id;
            $this->save();

            // 3. Generate Official Payment Receipt
            return $this->generateReceipt();
        });
    }

    /**
     * Generate frozen snapshot payment receipt
     */
    public function generateReceipt(): AccountingReceipt
    {
        $existing = AccountingReceipt::where('payment_id', $this->id)->first();
        if ($existing) {
            return $existing;
        }

        $receiptNumber = AccountingReceipt::generateReceiptNumber();
        $settings = AccountingCompanySetting::getSettings();

        $allocationsData = [];
        foreach ($this->allocations as $alloc) {
            $allocationsData[] = [
                'invoice_number' => $alloc->invoice->invoice_number,
                'amount' => (float)$alloc->amount,
            ];
        }

        return AccountingReceipt::create([
            'receipt_number' => $receiptNumber,
            'payment_id' => $this->id,
            'receipt_date' => $this->payment_date,
            'amount' => $this->amount,
            'currency' => $this->currency ?: 'USD',
            'frozen_receipt_snapshot' => [
                'receipt_number' => $receiptNumber,
                'payment_number' => $this->payment_number,
                'company_name' => $settings->legal_company_name ?: $settings->brand_name,
                'tax_number' => $settings->tax_number,
                'client_name' => $this->client->display_name,
                'client_tax_number' => $this->client->tax_number,
                'payment_method' => $this->payment_method,
                'receiving_account' => $this->receivingAccount->name_ar,
                'handled_by' => $this->handled_by,
                'reference' => $this->reference,
                'notes' => $this->notes,
                'amount' => (float)$this->amount,
                'currency' => $this->currency ?: 'USD',
                'allocated_amount' => (float)$this->allocated_amount,
                'unallocated_amount' => (float)$this->unallocated_amount,
                'allocations' => $allocationsData,
            ],
        ]);
    }
}
