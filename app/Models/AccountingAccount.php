<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'current_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(AccountingPartner::class, 'partner_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /**
     * Get computed balance based on account normal balance rules:
     * - Assets & Expenses: normal debit (Debits - Credits)
     * - Liabilities, Equity, Revenues: normal credit (Credits - Debits)
     */
    public function getBalanceAttribute(): float
    {
        $debits = (float) $this->journalLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');

        $credits = (float) $this->journalLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit');

        if (in_array($this->type, ['asset', 'expense'])) {
            return round($debits - $credits, 4);
        }

        // Drawings is contra equity, normal debit
        if ($this->sub_type === 'drawings') {
            return round($debits - $credits, 4);
        }

        return round($credits - $debits, 4);
    }
}
