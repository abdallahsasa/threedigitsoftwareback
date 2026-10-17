<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Partners
        Schema::create('accounting_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('slug')->unique(); // 'abdullah', 'amer'
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('initial_debt_balance', 14, 4)->default(1277.0000);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Chart of Accounts
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->string('sub_type')->nullable(); // 'cash', 'bank', 'receivable', 'prepaid_rent', 'fixed_asset', 'partner_debt', 'capital', 'drawings', 'direct_cost', 'operating_expense'
            $table->foreignId('partner_id')->nullable()->constrained('accounting_partners')->nullOnDelete();
            $table->decimal('current_balance', 14, 4)->default(0.0000);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Clients
        Schema::create('accounting_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('potential_receivable_amount', 14, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Projects
        Schema::create('accounting_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('accounting_clients')->nullOnDelete();
            $table->decimal('total_price', 14, 4)->default(0.0000);
            $table->decimal('direct_costs', 14, 4)->default(0.0000);
            $table->enum('status', ['in_progress', 'done', 'cancelled'])->default('in_progress');
            $table->decimal('time_spent_hours', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description');
            $table->enum('status', ['posted', 'reversed'])->default('posted');
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reversed_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Journal Entry Lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounting_accounts')->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('accounting_partners')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('accounting_clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('accounting_projects')->nullOnDelete();
            $table->decimal('debit', 14, 4)->default(0.0000);
            $table->decimal('credit', 14, 4)->default(0.0000);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 7. Accounting Transactions (Business Layer)
        Schema::create('accounting_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->date('transaction_date');
            $table->string('transaction_type'); // 'client_collection', 'partner_funding', 'partner_personal_expense', 'company_expense', 'partner_debt_repayment', 'partner_distribution', 'capital_injection', 'partner_project_labor', 'internal_transfer', 'reconciliation_adjustment'
            $table->decimal('amount', 14, 4);
            $table->foreignId('account_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('accounting_partners')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('accounting_clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('accounting_projects')->nullOnDelete();
            $table->string('expense_category')->nullable();
            $table->string('payer_type')->default('company'); // 'company', 'partner_abdullah', 'partner_amer', 'othman', 'other'
            $table->string('payer_name')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('money_source')->default('company_funds'); // 'company_funds', 'personal_abdullah', 'personal_amer', 'othman', 'undetermined'
            $table->string('status')->default('completed'); // 'completed', 'pending_review', 'flagged', 'reversed'
            $table->string('review_status')->default('approved'); // 'approved', 'pending_review', 'needs_clarification', 'rejected'
            $table->text('review_reason')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_sheet')->nullable();
            $table->integer('source_row')->nullable();
            $table->string('fingerprint_hash')->nullable()->index();
            $table->json('attachments')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Import Batches
        Schema::create('accounting_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->string('file_hash')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('flagged_rows')->default(0);
            $table->integer('rejected_rows')->default(0);
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('completed');
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        // 9. Import Items (Staging & Review Audit)
        Schema::create('accounting_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('accounting_import_batches')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->integer('row_index');
            $table->json('raw_data');
            $table->string('fingerprint_hash')->index();
            $table->date('date')->nullable();
            $table->decimal('amount', 14, 4)->nullable();
            $table->string('proposed_type')->nullable();
            $table->foreignId('proposed_partner_id')->nullable()->constrained('accounting_partners')->nullOnDelete();
            $table->foreignId('proposed_client_id')->nullable()->constrained('accounting_clients')->nullOnDelete();
            $table->foreignId('proposed_project_id')->nullable()->constrained('accounting_projects')->nullOnDelete();
            $table->string('review_status')->default('pending_review'); // 'pending_review', 'approved', 'rejected', 'ignored'
            $table->text('review_reason')->nullable();
            $table->text('suggested_action')->nullable();
            $table->string('final_decision')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('accounting_transactions')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 10. Reconciliations & Cutover Transition
        Schema::create('accounting_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->date('reconciliation_date');
            $table->string('title');
            $table->decimal('reported_cash_balance', 14, 4)->default(250.0000);
            $table->decimal('calculated_historical_cash', 14, 4)->nullable();
            $table->decimal('cash_discrepancy', 14, 4)->nullable();
            $table->string('cash_audit_status')->default('estimated_needs_physical_audit');
            $table->decimal('confirmed_abdullah_debt', 14, 4)->default(1277.0000);
            $table->decimal('confirmed_amer_debt', 14, 4)->default(1277.0000);
            $table->decimal('total_confirmed_partner_debt', 14, 4)->default(2554.0000);
            $table->boolean('amer_repayment_deducted')->default(true);
            $table->boolean('abdullah_amer_settlement_handled')->default(true);
            $table->decimal('founding_capital_amount', 14, 4)->default(700.0000);
            $table->string('status')->default('active'); // 'draft', 'active', 'archived'
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 11. Accounting Audit Logs
        Schema::create('accounting_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // 'create', 'update', 'reverse', 'reconcile', 'import', 'approve_review'
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // 12. Add accounting role/permission fields to users if not present
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('admin');
                $table->json('permissions')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['role', 'permissions']);
            });
        }
        Schema::dropIfExists('accounting_audit_logs');
        Schema::dropIfExists('accounting_reconciliations');
        Schema::dropIfExists('accounting_import_items');
        Schema::dropIfExists('accounting_import_batches');
        Schema::dropIfExists('accounting_transactions');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounting_projects');
        Schema::dropIfExists('accounting_clients');
        Schema::dropIfExists('accounting_accounts');
        Schema::dropIfExists('accounting_partners');
    }
};
