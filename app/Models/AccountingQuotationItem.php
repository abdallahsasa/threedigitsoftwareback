<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingQuotationItem extends Model
{
    use HasFactory;

    protected $table = 'accounting_quotation_items';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'line_total' => 'decimal:4',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(AccountingQuotation::class, 'quotation_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(AccountingService::class, 'service_id');
    }
}
