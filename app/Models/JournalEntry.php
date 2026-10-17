<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JournalEntry extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'entry_date' => 'date',
        'is_reversal' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_entry_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(AccountingTransaction::class, 'journal_entry_id');
    }

    public function getTotalDebitAttribute(): float
    {
        return round((float) $this->lines->sum('debit'), 4);
    }

    public function getTotalCreditAttribute(): float
    {
        return round((float) $this->lines->sum('credit'), 4);
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.0001;
    }

    public static function generateEntryNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('entry_date', $year)->count() + 1;
        return sprintf('JE-%s-%04d', $year, $count);
    }
}
