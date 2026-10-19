<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'accounting_payment_allocations';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(AccountingPayment::class, 'payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(AccountingInvoice::class, 'invoice_id');
    }
}
