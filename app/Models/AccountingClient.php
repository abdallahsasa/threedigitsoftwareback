<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingClient extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'potential_receivable_amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (AccountingClient $client) {
            if (empty($client->normalized_name)) {
                $base = $client->name ?: $client->company_name ?: $client->brand_name ?: 'client';
                $client->normalized_name = static::normalizeName($base);
            }
        });
    }

    public function projects(): HasMany
    {
        return $this->hasMany(AccountingProject::class, 'client_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(AccountingQuotation::class, 'client_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(AccountingInvoice::class, 'client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccountingPayment::class, 'client_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(AccountingCreditNote::class, 'client_id');
    }

    public function documentShares(): HasMany
    {
        return $this->hasMany(AccountingDocumentShare::class, 'client_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountingTransaction::class, 'client_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'client_id');
    }

    public static function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->company_name) && !empty($this->name) && $this->company_name !== $this->name) {
            return "{$this->company_name} ({$this->name})";
        }
        return $this->company_name ?: $this->name;
    }

    /**
     * Confirmed balance based on posted/issued documents and payments
     */
    public function getConfirmedOutstandingBalanceAttribute(): float
    {
        $issuedInvoicesTotal = (float) $this->invoices()
            ->where('document_status', 'issued')
            ->sum('total_amount');

        $paidTotal = (float) $this->payments()
            ->sum('amount');

        $creditNotesTotal = (float) $this->creditNotes()
            ->where('status', 'issued')
            ->sum('amount');

        return max(0.0, round($issuedInvoicesTotal - $paidTotal - $creditNotesTotal, 4));
    }
}
