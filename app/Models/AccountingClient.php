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

    public function projects(): HasMany
    {
        return $this->hasMany(AccountingProject::class, 'client_id');
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
}
