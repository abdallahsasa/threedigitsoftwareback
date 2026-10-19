<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingInvoice extends Model
{
    use HasFactory;

    protected $table = 'accounting_invoices';

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'exchange_rate' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'taxable_amount' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'balance_due' => 'decimal:4',
        'is_advance' => 'boolean',
        'frozen_company_snapshot' => 'array',
        'frozen_client_snapshot' => 'array',
        'frozen_items_snapshot' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccountingProject::class, 'project_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(AccountingQuotation::class, 'quotation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccountingInvoiceItem::class, 'invoice_id')->orderBy('sort_order');
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(AccountingPaymentAllocation::class, 'invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(AccountingCreditNote::class, 'invoice_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentShares(): HasMany
    {
        return $this->hasMany(AccountingDocumentShare::class, 'document_id')
            ->where('document_type', 'invoice');
    }

    /**
     * Overdue indicator accessor
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->document_status !== 'issued' || (float)$this->balance_due <= 0.0) {
            return false;
        }
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Protect invoice numbering against concurrent creation with atomic sequence locking
     */
    public static function generateInvoiceNumber(): string
    {
        $settings = AccountingCompanySetting::getSettings();
        $prefix = $settings->invoice_prefix ?: 'TDS-INV-';
        $year = date('Y');
        $base = "{$prefix}{$year}-";

        $last = self::where('invoice_number', 'LIKE', "{$base}%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last->invoice_number);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return sprintf('%s%04d', $base, $nextSeq);
    }

    /**
     * Recalculates subtotal, discounts, taxable base, taxes, and balance
     */
    public function recalculateTotals(): void
    {
        if ($this->document_status === 'issued') {
            // Once issued, totals are immutable from draft recalculation
            return;
        }

        $subtotal = 0.0;
        $discountAmount = 0.0;
        $taxAmount = 0.0;

        foreach ($this->items as $item) {
            $lineSubtotal = (float)$item->quantity * (float)$item->unit_price;
            $lineDiscount = 0.0;

            if ($item->discount_type === 'percentage') {
                $lineDiscount = $lineSubtotal * ((float)$item->discount_value / 100);
            } elseif ($item->discount_type === 'fixed') {
                $lineDiscount = (float)$item->discount_value;
            }

            $afterDiscount = max(0.0, $lineSubtotal - $lineDiscount);
            $lineTax = $afterDiscount * ((float)$item->tax_rate / 100);
            $lineTotal = round($afterDiscount + $lineTax, 4);

            $item->discount_amount = $lineDiscount;
            $item->tax_amount = $lineTax;
            $item->line_total = $lineTotal;
            $item->save();

            $subtotal += $lineSubtotal;
            $discountAmount += $lineDiscount;
            $taxAmount += $lineTax;
        }

        $this->subtotal = round($subtotal, 4);
        $this->discount_amount = round($discountAmount, 4);
        $this->taxable_amount = round(max(0.0, $subtotal - $discountAmount), 4);
        $this->tax_amount = round($taxAmount, 4);
        $this->total_amount = round(max(0.0, $subtotal - $discountAmount + $taxAmount), 4);
        $this->balance_due = round(max(0.0, $this->total_amount - (float)$this->paid_amount), 4);
        $this->save();
    }

    /**
     * Issue invoice:
     * - Validates company legal name and tax number are configured.
     * - Freezes company, client, and line item snapshots.
     * - Atomically creates balanced double-entry accounting journal entry.
     * - Idempotent: safe against retries.
     */
    public function issueInvoice(?User $user = null): void
    {
        if ($this->document_status === 'issued') {
            return; // Idempotent
        }

        $settings = AccountingCompanySetting::getSettings();

        // Strict Requirement: Legal company name & Tax number are REQUIRED before issuance
        if (empty(trim((string)$settings->legal_company_name)) || empty(trim((string)$settings->tax_number))) {
            throw ValidationException::withMessages([
                'company_settings' => 'يجب إدخال الاسم القانوني الكامل للشركة والرقم الضريبي في إعدادات الشركة قبل إصدار الفاتورة. / Full registered legal company name and company Tax Number are required in Company Settings before issuing an invoice.',
            ]);
        }

        DB::transaction(function () use ($settings, $user) {
            // Lock invoice record
            $locked = self::where('id', $this->id)->lockForUpdate()->first();
            if ($locked->document_status === 'issued') {
                return;
            }

            // Ensure invoice number is sequential and finalized
            if (empty($locked->invoice_number) || str_contains($locked->invoice_number, 'DRAFT')) {
                $locked->invoice_number = self::generateInvoiceNumber();
            }

            // 1. Freeze Company Snapshot
            $locked->frozen_company_snapshot = [
                'brand_name' => $settings->brand_name,
                'legal_company_name' => $settings->legal_company_name,
                'tax_number' => $settings->tax_number,
                'commercial_registration_number' => $settings->commercial_registration_number,
                'registered_address' => $settings->registered_address,
                'city' => $settings->city,
                'country' => $settings->country,
                'phone' => $settings->phone,
                'whatsapp' => $settings->whatsapp,
                'email' => $settings->email,
                'website' => $settings->website,
                'logo_path' => $settings->logo_path,
                'bank_name' => $settings->bank_name,
                'bank_account_number' => $settings->bank_account_number,
                'bank_iban' => $settings->bank_iban,
                'bank_swift' => $settings->bank_swift,
                'payment_methods_text' => $settings->payment_methods_text,
                'default_payment_terms' => $settings->default_payment_terms,
                'document_footer' => $settings->document_footer,
            ];

            // 2. Freeze Client Snapshot
            $client = $locked->client;
            $locked->frozen_client_snapshot = [
                'client_id' => $client->id,
                'name' => $client->name,
                'company_name' => $client->company_name,
                'legal_name' => $client->legal_name,
                'brand_name' => $client->brand_name,
                'tax_number' => $client->tax_number,
                'registration_number' => $client->registration_number,
                'billing_address' => $client->billing_address,
                'city' => $client->city,
                'country' => $client->country,
                'postal_code' => $client->postal_code,
                'phone' => $client->phone,
                'email' => $client->email,
            ];

            // 3. Freeze Line Items Snapshot
            $itemsData = [];
            foreach ($locked->items as $item) {
                $itemsData[] = [
                    'service_name' => $item->service_name,
                    'description' => $item->description,
                    'quantity' => (float)$item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => (float)$item->unit_price,
                    'discount_type' => $item->discount_type,
                    'discount_value' => (float)$item->discount_value,
                    'discount_amount' => (float)$item->discount_amount,
                    'tax_rate' => (float)$item->tax_rate,
                    'tax_amount' => (float)$item->tax_amount,
                    'line_total' => (float)$item->line_total,
                ];
            }
            $locked->frozen_items_snapshot = $itemsData;

            // 4. Double-Entry Accounting Posting
            $receivableAcc = AccountingAccount::where('code', '1100')->firstOrFail();
            $targetCreditAcc = $locked->is_advance
                ? AccountingAccount::where('code', '2300')->firstOrFail() // Deferred Revenue
                : AccountingAccount::where('code', '4010')->firstOrFail(); // Project Revenue

            $taxAcc = (float)$locked->tax_amount > 0
                ? AccountingAccount::where('code', '2250')->first()
                : null;

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $locked->issue_date->toDateString(),
                'reference_type' => 'client_invoice',
                'reference_id' => $locked->id,
                'description' => "إصدار فاتورة مبيعات رقم {$locked->invoice_number} للعميل {$client->display_name}",
                'status' => 'posted',
                'created_by' => $user?->id ?? auth()->id(),
            ]);

            // Line 1: Debit Accounts Receivable (1100) for full invoice total
            $entry->lines()->create([
                'account_id' => $receivableAcc->id,
                'client_id' => $client->id,
                'project_id' => $locked->project_id,
                'debit' => $locked->total_amount,
                'credit' => 0.0000,
                'description' => "استحقاق ذمة مدينة عن فاتورة {$locked->invoice_number}",
            ]);

            // Line 2: Credit Revenue (4010) or Deferred Revenue (2300) for taxable base/net sales
            $revenueAmount = (float)$locked->tax_amount > 0
                ? (float)$locked->taxable_amount
                : (float)$locked->total_amount;

            $entry->lines()->create([
                'account_id' => $targetCreditAcc->id,
                'client_id' => $client->id,
                'project_id' => $locked->project_id,
                'debit' => 0.0000,
                'credit' => $revenueAmount,
                'description' => $locked->is_advance
                    ? "إيراد مؤجل / دفعة مقدمة عن فاتورة {$locked->invoice_number}"
                    : "إيراد خدمات مستحق عن فاتورة {$locked->invoice_number}",
            ]);

            // Line 3: Credit Sales Tax / VAT (2250) if tax exists
            if ((float)$locked->tax_amount > 0 && $taxAcc) {
                $entry->lines()->create([
                    'account_id' => $taxAcc->id,
                    'client_id' => $client->id,
                    'project_id' => $locked->project_id,
                    'debit' => 0.0000,
                    'credit' => $locked->tax_amount,
                    'description' => "ضريبة مبيعات مستحقة عن فاتورة {$locked->invoice_number}",
                ]);
            }

            // 5. Finalize Invoice State
            $locked->document_status = 'issued';
            $locked->issued_at = now();
            $locked->issued_by = $user?->id ?? auth()->id();
            $locked->journal_entry_id = $entry->id;
            $locked->balance_due = round(max(0.0, (float)$locked->total_amount - (float)$locked->paid_amount), 4);
            $locked->save();

            // Log Audit
            AccountingAuditLog::create([
                'user_id' => $user?->id ?? auth()->id(),
                'action' => 'issue_invoice',
                'entity_type' => 'invoice',
                'entity_id' => $locked->id,
                'description' => "تم إصدار الفاتورة رقم {$locked->invoice_number} وترحيل القيد المحاسبي رقم {$entry->entry_number}",
                'new_values' => [
                    'invoice_number' => $locked->invoice_number,
                    'total_amount' => $locked->total_amount,
                    'journal_entry_id' => $entry->id,
                ],
            ]);
        });
    }

    /**
     * Update payment status based on paid amount vs total
     */
    public function updatePaymentStatus(): void
    {
        $paid = (float)$this->paid_amount;
        $total = (float)$this->total_amount;

        if ($paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paid >= $total - 0.0001) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partially_paid';
        }

        $this->balance_due = round(max(0.0, $total - $paid), 4);
        $this->save();
    }
}
