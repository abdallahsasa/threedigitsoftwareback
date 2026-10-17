<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccountingPartner extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'initial_debt_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function debtAccount(): HasOne
    {
        return $this->hasOne(AccountingAccount::class, 'partner_id')
            ->where('sub_type', 'partner_debt');
    }

    public function drawingsAccount(): HasOne
    {
        return $this->hasOne(AccountingAccount::class, 'partner_id')
            ->where('sub_type', 'drawings');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountingTransaction::class, 'partner_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'partner_id');
    }

    /**
     * Calculate current net debt owed to this partner by the company
     */
    public function getCalculatedDebtBalanceAttribute(): float
    {
        $account = $this->debtAccount;
        if (!$account) {
            return (float) $this->initial_debt_balance;
        }

        // For liability account: Credit increases liability (company debt to partner), Debit decreases liability (repayment)
        $credits = (float) JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit');

        $debits = (float) JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');

        return round($credits - $debits, 4);
    }

    /**
     * Calculate total drawings/distributions taken by this partner
     */
    public function getTotalDrawingsAttribute(): float
    {
        $account = $this->drawingsAccount;
        if (!$account) {
            return 0.0;
        }

        // Drawings is equity contra account: debited when drawing
        return (float) JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');
    }
}
