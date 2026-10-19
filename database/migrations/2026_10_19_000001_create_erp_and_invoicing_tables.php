<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Company Settings
        Schema::create('accounting_company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->default('Three Digit Software');
            $table->string('legal_company_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('commercial_registration_number')->nullable();
            $table->string('registered_address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_swift')->nullable();
            $table->text('payment_methods_text')->nullable();
            $table->text('default_payment_terms')->nullable();
            $table->text('document_footer')->nullable();
            $table->string('default_document_language')->default('ar');
            $table->string('invoice_prefix')->default('TDS-INV-');
            $table->string('quotation_prefix')->default('TDS-QT-');
            $table->string('receipt_prefix')->default('TDS-REC-');
            $table->string('credit_note_prefix')->default('TDS-CN-');
            $table->boolean('default_tax_enabled')->default(false);
            $table->decimal('default_tax_rate', 5, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Enhance Clients
        Schema::table('accounting_clients', function (Blueprint $table) {
            $table->string('type')->default('company')->after('name'); // 'individual', 'company'
            $table->string('brand_name')->nullable()->after('type');
            $table->string('legal_name')->nullable()->after('brand_name');
            $table->string('business_sector')->nullable()->after('company_name');
            $table->string('whatsapp')->nullable()->after('phone');
            $table->string('billing_address')->nullable()->after('email');
            $table->string('city')->nullable()->after('billing_address');
            $table->string('country')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('country');
            $table->string('tax_number')->nullable()->after('postal_code');
            $table->string('registration_number')->nullable()->after('tax_number');
            $table->text('internal_notes')->nullable()->after('potential_receivable_amount');
            $table->text('public_notes')->nullable()->after('internal_notes');
        });

        // 3. Services Catalog
        Schema::create('accounting_services', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('category'); // 'website_development', 'software_development', 'e_commerce', 'social_media', 'design', 'photography', 'hosting', 'maintenance'
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('unit')->default('service'); // 'hour', 'project', 'month', 'service', 'item'
            $table->decimal('default_price', 14, 4)->default(0.0000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Enhance Projects
        Schema::table('accounting_projects', function (Blueprint $table) {
            $table->string('business_sector')->nullable()->after('name');
            $table->json('service_categories')->nullable()->after('business_sector');
            $table->text('description')->nullable()->after('service_categories');
            $table->text('scope')->nullable()->after('description');
            $table->text('deliverables')->nullable()->after('scope');
            $table->date('start_date')->nullable()->after('deliverables');
            $table->date('delivery_date')->nullable()->after('start_date');
            $table->string('currency')->default('USD')->after('total_price');
            $table->json('attachments')->nullable()->after('notes');
        });

        // 5. Quotations
        Schema::create('accounting_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('client_id')->constrained('accounting_clients')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('accounting_projects')->nullOnDelete();
            $table->string('title');
            $table->date('issue_date');
            $table->date('valid_until');
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->string('currency')->default('USD');
            $table->string('document_language')->default('ar');
            $table->decimal('subtotal', 14, 4)->default(0.0000);
            $table->decimal('discount_amount', 14, 4)->default(0.0000);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 4)->default(0.0000);
            $table->decimal('total_amount', 14, 4)->default(0.0000);
            $table->text('scope')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('payment_schedule')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('private_notes')->nullable();
            $table->foreignId('converted_to_project_id')->nullable()->constrained('accounting_projects')->nullOnDelete();
            $table->unsignedBigInteger('converted_to_invoice_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Quotation Items
        Schema::create('accounting_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('accounting_quotations')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('accounting_services')->nullOnDelete();
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit')->default('unit');
            $table->decimal('unit_price', 14, 4)->default(0.0000);
            $table->enum('discount_type', ['none', 'percentage', 'fixed'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 4)->default(0.0000);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 4)->default(0.0000);
            $table->decimal('line_total', 14, 4)->default(0.0000);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 7. Invoices
        Schema::create('accounting_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained('accounting_clients')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('accounting_projects')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('accounting_quotations')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('reference')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('currency')->default('USD');
            $table->decimal('exchange_rate', 14, 4)->default(1.0000);
            $table->string('document_language')->default('ar');
            $table->enum('document_status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->enum('sending_status', ['not_sent', 'pending', 'provider_accepted', 'failed'])->default('not_sent');
            $table->boolean('is_advance')->default(false);
            $table->decimal('subtotal', 14, 4)->default(0.0000);
            $table->decimal('discount_amount', 14, 4)->default(0.0000);
            $table->decimal('taxable_amount', 14, 4)->default(0.0000);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 4)->default(0.0000);
            $table->decimal('total_amount', 14, 4)->default(0.0000);
            $table->decimal('paid_amount', 14, 4)->default(0.0000);
            $table->decimal('balance_due', 14, 4)->default(0.0000);
            $table->text('payment_terms')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('private_notes')->nullable();
            $table->json('frozen_company_snapshot')->nullable();
            $table->json('frozen_client_snapshot')->nullable();
            $table->json('frozen_items_snapshot')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Invoice Items
        Schema::create('accounting_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('accounting_invoices')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('accounting_services')->nullOnDelete();
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit')->default('unit');
            $table->decimal('unit_price', 14, 4)->default(0.0000);
            $table->enum('discount_type', ['none', 'percentage', 'fixed'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 4)->default(0.0000);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 4)->default(0.0000);
            $table->decimal('line_total', 14, 4)->default(0.0000);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 9. Payments
        Schema::create('accounting_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('client_id')->constrained('accounting_clients')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 4);
            $table->string('currency')->default('USD');
            $table->decimal('exchange_rate', 14, 4)->default(1.0000);
            $table->string('payment_method')->default('cash'); // 'cash', 'bank_transfer', 'crypto', 'other'
            $table->foreignId('receiving_account_id')->constrained('accounting_accounts');
            $table->string('handled_by')->nullable();
            $table->string('reference')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->enum('type', ['invoice_payment', 'customer_advance', 'unapplied_payment', 'refund'])->default('invoice_payment');
            $table->decimal('allocated_amount', 14, 4)->default(0.0000);
            $table->decimal('unallocated_amount', 14, 4)->default(0.0000);
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Payment Allocations
        Schema::create('accounting_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('accounting_payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('accounting_invoices')->cascadeOnDelete();
            $table->decimal('amount', 14, 4);
            $table->timestamps();
        });

        // 11. Payment Receipts
        Schema::create('accounting_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('payment_id')->constrained('accounting_payments')->cascadeOnDelete();
            $table->date('receipt_date');
            $table->decimal('amount', 14, 4);
            $table->string('currency')->default('USD');
            $table->json('frozen_receipt_snapshot')->nullable();
            $table->timestamps();
        });

        // 12. Credit Notes
        Schema::create('accounting_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->unique();
            $table->foreignId('invoice_id')->constrained('accounting_invoices')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('accounting_clients')->cascadeOnDelete();
            $table->date('issue_date');
            $table->decimal('amount', 14, 4);
            $table->text('reason');
            $table->enum('status', ['draft', 'issued'])->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 13. Document Shares & Secure Links
        Schema::create('accounting_document_shares', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->enum('document_type', ['quotation', 'invoice', 'receipt', 'statement']);
            $table->unsignedBigInteger('document_id');
            $table->foreignId('client_id')->constrained('accounting_clients')->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->string('last_viewed_ip')->nullable();
            $table->enum('share_channel', ['whatsapp', 'email', 'direct_link'])->default('direct_link');
            $table->string('recipient_contact')->nullable();
            $table->enum('email_status', ['not_sent', 'queued', 'provider_accepted', 'delivered', 'failed'])->default('not_sent');
            $table->text('email_error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_document_shares');
        Schema::dropIfExists('accounting_credit_notes');
        Schema::dropIfExists('accounting_receipts');
        Schema::dropIfExists('accounting_payment_allocations');
        Schema::dropIfExists('accounting_payments');
        Schema::dropIfExists('accounting_invoice_items');
        Schema::dropIfExists('accounting_invoices');
        Schema::dropIfExists('accounting_quotation_items');
        Schema::dropIfExists('accounting_quotations');

        if (Schema::hasTable('accounting_projects')) {
            Schema::table('accounting_projects', function (Blueprint $table) {
                $table->dropColumn([
                    'business_sector', 'service_categories', 'description',
                    'scope', 'deliverables', 'start_date', 'delivery_date',
                    'currency', 'attachments'
                ]);
            });
        }

        Schema::dropIfExists('accounting_services');

        if (Schema::hasTable('accounting_clients')) {
            Schema::table('accounting_clients', function (Blueprint $table) {
                $table->dropColumn([
                    'type', 'brand_name', 'legal_name', 'business_sector',
                    'whatsapp', 'billing_address', 'city', 'country',
                    'postal_code', 'tax_number', 'registration_number',
                    'internal_notes', 'public_notes'
                ]);
            });
        }

        Schema::dropIfExists('accounting_company_settings');
    }
};
