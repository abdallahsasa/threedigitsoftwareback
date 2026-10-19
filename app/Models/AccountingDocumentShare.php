<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccountingDocumentShare extends Model
{
    use HasFactory;

    protected $table = 'accounting_document_shares';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(AccountingClient::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function createSecureShare(
        string $documentType,
        int $documentId,
        int $clientId,
        string $channel = 'direct_link',
        ?string $recipientContact = null,
        ?int $expiryDays = 30
    ): self {
        return self::create([
            'token' => Str::random(64),
            'document_type' => $documentType,
            'document_id' => $documentId,
            'client_id' => $clientId,
            'share_channel' => $channel,
            'recipient_contact' => $recipientContact,
            'expires_at' => $expiryDays ? now()->addDays($expiryDays) : null,
            'created_by' => auth()->id(),
        ]);
    }

    public function isValid(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function recordView(?string $ipAddress = null): void
    {
        $this->increment('view_count');
        $this->last_viewed_at = now();
        $this->last_viewed_ip = $ipAddress;
        $this->save();
    }

    public function getDocumentUrlAttribute(): string
    {
        return url("/portal/docs/{$this->token}");
    }

    public function getDocument()
    {
        return match ($this->document_type) {
            'invoice' => AccountingInvoice::find($this->document_id),
            'quotation' => AccountingQuotation::find($this->document_id),
            'receipt' => AccountingReceipt::find($this->document_id),
            'statement' => AccountingClient::find($this->document_id),
            default => null,
        };
    }
}
