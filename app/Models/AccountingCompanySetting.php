<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingCompanySetting extends Model
{
    protected $table = 'accounting_company_settings';

    protected $fillable = [
        'brand_name',
        'legal_company_name',
        'tax_number',
        'commercial_registration_number',
        'registered_address',
        'city',
        'country',
        'phone',
        'whatsapp',
        'email',
        'website',
        'logo_path',
        'bank_name',
        'bank_account_number',
        'bank_iban',
        'bank_swift',
        'payment_methods_text',
        'default_payment_terms',
        'document_footer',
        'default_document_language',
        'invoice_prefix',
        'quotation_prefix',
        'receipt_prefix',
        'credit_note_prefix',
        'default_tax_enabled',
        'default_tax_rate',
    ];

    protected $casts = [
        'default_tax_enabled' => 'boolean',
        'default_tax_rate' => 'decimal:2',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'brand_name' => 'Three Digit Software',
                'legal_company_name' => null, // Required to be configured explicitly before invoice issuance
                'tax_number' => null, // Required to be configured explicitly before invoice issuance
                'commercial_registration_number' => null,
                'registered_address' => null,
                'city' => null,
                'country' => null,
                'phone' => '+963 999 000 000',
                'whatsapp' => '+963 999 000 000',
                'email' => 'info@threedigit.com',
                'website' => 'https://threedigit.com',
                'logo_path' => 'images/logo.svg',
                'payment_methods_text' => "Bank Transfer / Cash / Crypto\nUSDT (TRC20): Available on request\nBank details provided upon invoice confirmation",
                'default_payment_terms' => 'Payment due within 14 days of invoice date. 50% advance for project commencement unless otherwise specified.',
                'document_footer' => 'Thank you for choosing Three Digit Software. For inquiries, contact info@threedigit.com',
                'default_document_language' => 'ar',
                'invoice_prefix' => 'TDS-INV-',
                'quotation_prefix' => 'TDS-QT-',
                'receipt_prefix' => 'TDS-REC-',
                'credit_note_prefix' => 'TDS-CN-',
                'default_tax_enabled' => false,
                'default_tax_rate' => 0.00,
            ]
        );
    }

    /**
     * Checks if the company settings fulfill requirements to issue legal invoices
     */
    public function canIssueInvoices(): bool
    {
        return !empty(trim((string)$this->legal_company_name)) && !empty(trim((string)$this->tax_number));
    }
}
