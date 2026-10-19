<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AccountingQuotation extends Model
{
    use HasFactory;

    protected $table = 'accounting_quotations';

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccountingProject::class, 'project_id');
    }

    public function convertedProject(): BelongsTo
    {
        return $this->belongsTo(AccountingProject::class, 'converted_to_project_id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(AccountingInvoice::class, 'converted_to_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccountingQuotationItem::class, 'quotation_id')->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentShares(): HasMany
    {
        return $this->hasMany(AccountingDocumentShare::class, 'document_id')
            ->where('document_type', 'quotation');
    }

    public static function generateQuotationNumber(): string
    {
        $settings = AccountingCompanySetting::getSettings();
        $prefix = $settings->quotation_prefix ?: 'TDS-QT-';
        $year = date('Y');
        $base = "{$prefix}{$year}-";

        $last = self::where('quotation_number', 'LIKE', "{$base}%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last->quotation_number);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return sprintf('%s%04d', $base, $nextSeq);
    }

    public function recalculateTotals(): void
    {
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
        $this->tax_amount = round($taxAmount, 4);
        $this->total_amount = round(max(0.0, $subtotal - $discountAmount + $taxAmount), 4);
        $this->save();
    }

    /**
     * Convert accepted quotation to a new project
     */
    public function convertToProject(): AccountingProject
    {
        if ($this->converted_to_project_id && $this->convertedProject) {
            return $this->convertedProject;
        }

        return DB::transaction(function () {
            $project = AccountingProject::create([
                'name' => $this->title,
                'client_id' => $this->client_id,
                'total_price' => $this->total_amount,
                'direct_costs' => 0.0000,
                'status' => 'in_progress',
                'description' => $this->customer_notes,
                'scope' => $this->scope,
                'deliverables' => $this->deliverables,
                'start_date' => now()->toDateString(),
                'currency' => $this->currency,
                'notes' => "أنشئ تلقائياً من عرض السعر رقم: {$this->quotation_number}",
            ]);

            $this->converted_to_project_id = $project->id;
            $this->status = 'accepted';
            $this->save();

            return $project;
        });
    }

    /**
     * Convert accepted quotation to an invoice draft
     */
    public function convertToInvoice(): AccountingInvoice
    {
        if ($this->converted_to_invoice_id && $this->convertedInvoice) {
            return $this->convertedInvoice;
        }

        return DB::transaction(function () {
            $invoice = AccountingInvoice::create([
                'invoice_number' => AccountingInvoice::generateInvoiceNumber(),
                'client_id' => $this->client_id,
                'project_id' => $this->project_id ?: $this->converted_to_project_id,
                'quotation_id' => $this->id,
                'title' => $this->title,
                'reference' => $this->quotation_number,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'currency' => $this->currency,
                'document_language' => $this->document_language,
                'document_status' => 'draft',
                'payment_status' => 'unpaid',
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discount_amount,
                'taxable_amount' => max(0, $this->subtotal - $this->discount_amount),
                'tax_rate' => $this->tax_rate,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
                'paid_amount' => 0.0000,
                'balance_due' => $this->total_amount,
                'payment_terms' => $this->terms_and_conditions ?: 'Payment due within 14 days',
                'customer_notes' => $this->customer_notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $qItem) {
                $invoice->items()->create([
                    'service_id' => $qItem->service_id,
                    'service_name' => $qItem->service_name,
                    'description' => $qItem->description,
                    'quantity' => $qItem->quantity,
                    'unit' => $qItem->unit,
                    'unit_price' => $qItem->unit_price,
                    'discount_type' => $qItem->discount_type,
                    'discount_value' => $qItem->discount_value,
                    'discount_amount' => $qItem->discount_amount,
                    'tax_rate' => $qItem->tax_rate,
                    'tax_amount' => $qItem->tax_amount,
                    'line_total' => $qItem->line_total,
                    'sort_order' => $qItem->sort_order,
                ]);
            }

            $this->converted_to_invoice_id = $invoice->id;
            $this->status = 'accepted';
            $this->save();

            return $invoice;
        });
    }
}
