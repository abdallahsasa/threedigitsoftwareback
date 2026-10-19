<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingReceipt extends Model
{
    use HasFactory;

    protected $table = 'accounting_receipts';

    protected $guarded = [];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:4',
        'frozen_receipt_snapshot' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(AccountingPayment::class, 'payment_id');
    }

    public static function generateReceiptNumber(): string
    {
        $settings = AccountingCompanySetting::getSettings();
        $prefix = $settings->receipt_prefix ?: 'TDS-REC-';
        $year = date('Y');
        $base = "{$prefix}{$year}-";

        $last = self::where('receipt_number', 'LIKE', "{$base}%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last->receipt_number);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return sprintf('%s%04d', $base, $nextSeq);
    }
}
