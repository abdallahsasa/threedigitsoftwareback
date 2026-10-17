<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingImportItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'raw_data' => 'array',
        'date' => 'date',
        'amount' => 'decimal:4',
        'reviewed_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AccountingImportBatch::class, 'batch_id');
    }

    public function proposedPartner(): BelongsTo
    {
        return $this->belongsTo(AccountingPartner::class, 'proposed_partner_id');
    }

    public function proposedClient(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'proposed_client_id');
    }

    public function proposedProject(): BelongsTo
    {
        return $this->belongsTo(AccountingProject::class, 'proposed_project_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(AccountingTransaction::class, 'transaction_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function computeFingerprint(string $sheet, int $row, ?string $date, ?float $amount, ?string $payer, ?string $notes): string
    {
        return hash('sha256', implode('|', [
            trim($sheet),
            (int) $row,
            trim((string)$date),
            number_format((float)$amount, 4, '.', ''),
            strtolower(trim((string)$payer)),
            strtolower(trim((string)$notes)),
        ]));
    }
}
