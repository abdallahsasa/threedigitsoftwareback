<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingReconciliation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'reconciliation_date' => 'date',
        'reported_cash_balance' => 'decimal:4',
        'calculated_historical_cash' => 'decimal:4',
        'cash_discrepancy' => 'decimal:4',
        'confirmed_abdullah_debt' => 'decimal:4',
        'confirmed_amer_debt' => 'decimal:4',
        'total_confirmed_partner_debt' => 'decimal:4',
        'amer_repayment_deducted' => 'boolean',
        'abdullah_amer_settlement_handled' => 'boolean',
        'founding_capital_amount' => 'decimal:4',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
