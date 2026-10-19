<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AccountingCreditNote extends Model
{
    use HasFactory;

    protected $table = 'accounting_credit_notes';

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'amount' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(AccountingInvoice::class, 'invoice_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCreditNoteNumber(): string
    {
        $settings = AccountingCompanySetting::getSettings();
        $prefix = $settings->credit_note_prefix ?: 'TDS-CN-';
        $year = date('Y');
        $base = "{$prefix}{$year}-";

        $last = self::where('credit_note_number', 'LIKE', "{$base}%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last->credit_note_number);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return sprintf('%s%04d', $base, $nextSeq);
    }

    /**
     * Issue credit note, post double-entry adjustment, and reduce invoice balance
     */
    public function issueCreditNote(): void
    {
        if ($this->status === 'issued') {
            return;
        }

        DB::transaction(function () {
            $invoice = AccountingInvoice::where('id', $this->invoice_id)->lockForUpdate()->firstOrFail();
            $receivableAcc = AccountingAccount::where('code', '1100')->firstOrFail();
            $revenueAcc = AccountingAccount::where('code', '4010')->firstOrFail();

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $this->issue_date->toDateString(),
                'reference_type' => 'credit_note',
                'reference_id' => $this->id,
                'description' => "إشعار دائن رقم {$this->credit_note_number} لتسوية الفاتورة {$invoice->invoice_number} - سبب: {$this->reason}",
                'status' => 'posted',
                'created_by' => auth()->id() ?? $this->created_by,
            ]);

            // Debit Revenue (reversal/allowance)
            $entry->lines()->create([
                'account_id' => $revenueAcc->id,
                'client_id' => $this->client_id,
                'project_id' => $invoice->project_id,
                'debit' => $this->amount,
                'credit' => 0.0000,
                'description' => "تخفيض إيراد بموجب إشعار دائن {$this->credit_note_number}",
            ]);

            // Credit Accounts Receivable
            $entry->lines()->create([
                'account_id' => $receivableAcc->id,
                'client_id' => $this->client_id,
                'project_id' => $invoice->project_id,
                'debit' => 0.0000,
                'credit' => $this->amount,
                'description' => "تخفيض ذمة العميل عن الفاتورة {$invoice->invoice_number}",
            ]);

            // Update invoice balance
            $invoice->total_amount = round(max(0.0, (float)$invoice->total_amount - (float)$this->amount), 4);
            $invoice->updatePaymentStatus();

            $this->status = 'issued';
            $this->journal_entry_id = $entry->id;
            $this->save();

            AccountingAuditLog::create([
                'user_id' => auth()->id() ?? $this->created_by,
                'action' => 'issue_credit_note',
                'entity_type' => 'credit_note',
                'entity_id' => $this->id,
                'description' => "تم اعتماد وإصدار الإشعار الدائن رقم {$this->credit_note_number} بمبلغ {$this->amount}$",
                'new_values' => [
                    'credit_note_number' => $this->credit_note_number,
                    'amount' => $this->amount,
                    'invoice_id' => $this->invoice_id,
                ],
            ]);
        });
    }
}
