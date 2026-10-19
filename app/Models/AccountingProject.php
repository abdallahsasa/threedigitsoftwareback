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
        'service_categories' => 'array',
        'attachments' => 'array',
        'start_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(AccountingQuotation::class, 'project_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(AccountingInvoice::class, 'project_id');
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
     * Total collected from client for this project (cash collections only)
     */
    public function getTotalCollectedAttribute(): float
    {
        return (float) $this->transactions()
            ->where('transaction_type', 'client_collection')
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Total amount of issued invoices for this project
     */
    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->invoices()
            ->where('document_status', 'issued')
            ->sum('total_amount');
    }

    /**
     * Calculated net project margin = total_price - direct_costs
     * (Contract value is not cash or guaranteed revenue, status 'cancelled' does not produce receivables)
     */
    public function getProjectMarginAttribute(): float
    {
        if ($this->status === 'cancelled') {
            return 0.0;
        }
        return round((float)$this->total_price - (float)$this->direct_costs, 4);
    }
}
