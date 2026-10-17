<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingImportBatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AccountingImportItem::class, 'batch_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public static function generateBatchNumber(): string
    {
        $date = date('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('BATCH-%s-%03d', $date, $count);
    }
}
