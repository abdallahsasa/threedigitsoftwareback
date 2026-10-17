<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingProject extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total_price' => 'decimal:4',
        'direct_costs' => 'decimal:4',
        'time_spent_hours' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountingTransaction::class, 'project_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'project_id');
    }

    /**
     * Total collected from client for this project
     */
    public function getTotalCollectedAttribute(): float
    {
        return (float) $this->transactions()
            ->where('transaction_type', 'client_collection')
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Calculated net profit = total_price - direct_costs
     */
    public function getNetProfitAttribute(): float
    {
        return round((float)$this->total_price - (float)$this->direct_costs, 4);
    }

    /**
     * Remaining uncollected balance on contract
     */
    public function getRemainingBalanceAttribute(): float
    {
        return round(max(0, (float)$this->total_price - $this->total_collected), 4);
    }
}
