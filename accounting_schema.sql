create table `accounting_partners` (`id` bigint unsigned not null auto_increment primary key, `name_ar` varchar(255) not null, `name_en` varchar(255) not null, `slug` varchar(255) not null, `user_id` bigint unsigned null, `phone` varchar(255) null, `email` varchar(255) null, `initial_debt_balance` decimal(14, 4) not null default '1277', `is_active` tinyint(1) not null default '1', `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_partners` add constraint `accounting_partners_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `accounting_partners` add unique `accounting_partners_slug_unique`(`slug`);

create table `accounting_accounts` (`id` bigint unsigned not null auto_increment primary key, `code` varchar(255) not null, `name_ar` varchar(255) not null, `name_en` varchar(255) not null, `type` enum('asset', 'liability', 'equity', 'revenue', 'expense') not null, `sub_type` varchar(255) null, `partner_id` bigint unsigned null, `current_balance` decimal(14, 4) not null default '0', `is_active` tinyint(1) not null default '1', `description` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_accounts` add constraint `accounting_accounts_partner_id_foreign` foreign key (`partner_id`) references `accounting_partners` (`id`) on delete set null;

alter table `accounting_accounts` add unique `accounting_accounts_code_unique`(`code`);

create table `accounting_clients` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `normalized_name` varchar(255) not null, `company_name` varchar(255) null, `contact_person` varchar(255) null, `phone` varchar(255) null, `email` varchar(255) null, `potential_receivable_amount` decimal(14, 4) not null default '0', `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_clients` add index `accounting_clients_normalized_name_index`(`normalized_name`);

create table `accounting_projects` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `code` varchar(255) null, `client_id` bigint unsigned null, `total_price` decimal(14, 4) not null default '0', `direct_costs` decimal(14, 4) not null default '0', `status` enum('in_progress', 'done', 'cancelled') not null default 'in_progress', `time_spent_hours` decimal(8, 2) null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_projects` add constraint `accounting_projects_client_id_foreign` foreign key (`client_id`) references `accounting_clients` (`id`) on delete set null;

create table `journal_entries` (`id` bigint unsigned not null auto_increment primary key, `entry_number` varchar(255) not null, `entry_date` date not null, `reference_type` varchar(255) null, `reference_id` bigint unsigned null, `description` text not null, `status` enum('posted', 'reversed') not null default 'posted', `is_reversal` tinyint(1) not null default '0', `reversed_entry_id` bigint unsigned null, `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `journal_entries` add constraint `journal_entries_reversed_entry_id_foreign` foreign key (`reversed_entry_id`) references `journal_entries` (`id`) on delete set null;

alter table `journal_entries` add constraint `journal_entries_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;

alter table `journal_entries` add unique `journal_entries_entry_number_unique`(`entry_number`);

create table `journal_entry_lines` (`id` bigint unsigned not null auto_increment primary key, `journal_entry_id` bigint unsigned not null, `account_id` bigint unsigned not null, `partner_id` bigint unsigned null, `client_id` bigint unsigned null, `project_id` bigint unsigned null, `debit` decimal(14, 4) not null default '0', `credit` decimal(14, 4) not null default '0', `description` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `journal_entry_lines` add constraint `journal_entry_lines_journal_entry_id_foreign` foreign key (`journal_entry_id`) references `journal_entries` (`id`) on delete cascade;

alter table `journal_entry_lines` add constraint `journal_entry_lines_account_id_foreign` foreign key (`account_id`) references `accounting_accounts` (`id`) on delete cascade;

alter table `journal_entry_lines` add constraint `journal_entry_lines_partner_id_foreign` foreign key (`partner_id`) references `accounting_partners` (`id`) on delete set null;

alter table `journal_entry_lines` add constraint `journal_entry_lines_client_id_foreign` foreign key (`client_id`) references `accounting_clients` (`id`) on delete set null;

alter table `journal_entry_lines` add constraint `journal_entry_lines_project_id_foreign` foreign key (`project_id`) references `accounting_projects` (`id`) on delete set null;

create table `accounting_transactions` (`id` bigint unsigned not null auto_increment primary key, `transaction_number` varchar(255) not null, `transaction_date` date not null, `transaction_type` varchar(255) not null, `amount` decimal(14, 4) not null, `account_id` bigint unsigned null, `partner_id` bigint unsigned null, `client_id` bigint unsigned null, `project_id` bigint unsigned null, `expense_category` varchar(255) null, `payer_type` varchar(255) not null default 'company', `payer_name` varchar(255) null, `payment_method` varchar(255) null, `money_source` varchar(255) not null default 'company_funds', `status` varchar(255) not null default 'completed', `review_status` varchar(255) not null default 'approved', `review_reason` text null, `reviewer_id` bigint unsigned null, `reviewed_at` timestamp null, `notes` text null, `source_sheet` varchar(255) null, `source_row` int null, `fingerprint_hash` varchar(255) null, `attachments` json null, `journal_entry_id` bigint unsigned null, `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_transactions` add constraint `accounting_transactions_account_id_foreign` foreign key (`account_id`) references `accounting_accounts` (`id`) on delete set null;

alter table `accounting_transactions` add constraint `accounting_transactions_partner_id_foreign` foreign key (`partner_id`) references `accounting_partners` (`id`) on delete set null;

alter table `accounting_transactions` add constraint `accounting_transactions_client_id_foreign` foreign key (`client_id`) references `accounting_clients` (`id`) on delete set null;

alter table `accounting_transactions` add constraint `accounting_transactions_project_id_foreign` foreign key (`project_id`) references `accounting_projects` (`id`) on delete set null;

alter table `accounting_transactions` add constraint `accounting_transactions_reviewer_id_foreign` foreign key (`reviewer_id`) references `users` (`id`) on delete set null;

alter table `accounting_transactions` add constraint `accounting_transactions_journal_entry_id_foreign` foreign key (`journal_entry_id`) references `journal_entries` (`id`) on delete set null;

alter table `accounting_transactions` add constraint `accounting_transactions_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;

alter table `accounting_transactions` add unique `accounting_transactions_transaction_number_unique`(`transaction_number`);

alter table `accounting_transactions` add index `accounting_transactions_fingerprint_hash_index`(`fingerprint_hash`);

create table `accounting_import_batches` (`id` bigint unsigned not null auto_increment primary key, `batch_number` varchar(255) not null, `filename` varchar(255) not null, `file_path` varchar(255) null, `file_hash` varchar(255) null, `total_rows` int not null default '0', `imported_rows` int not null default '0', `flagged_rows` int not null default '0', `rejected_rows` int not null default '0', `imported_by` bigint unsigned null, `status` varchar(255) not null default 'completed', `summary` json null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_import_batches` add constraint `accounting_import_batches_imported_by_foreign` foreign key (`imported_by`) references `users` (`id`) on delete set null;

alter table `accounting_import_batches` add unique `accounting_import_batches_batch_number_unique`(`batch_number`);

create table `accounting_import_items` (`id` bigint unsigned not null auto_increment primary key, `batch_id` bigint unsigned not null, `sheet_name` varchar(255) not null, `row_index` int not null, `raw_data` json not null, `fingerprint_hash` varchar(255) not null, `date` date null, `amount` decimal(14, 4) null, `proposed_type` varchar(255) null, `proposed_partner_id` bigint unsigned null, `proposed_client_id` bigint unsigned null, `proposed_project_id` bigint unsigned null, `review_status` varchar(255) not null default 'pending_review', `review_reason` text null, `suggested_action` text null, `final_decision` varchar(255) null, `decision_notes` text null, `transaction_id` bigint unsigned null, `reviewed_by` bigint unsigned null, `reviewed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_import_items` add constraint `accounting_import_items_batch_id_foreign` foreign key (`batch_id`) references `accounting_import_batches` (`id`) on delete cascade;

alter table `accounting_import_items` add constraint `accounting_import_items_proposed_partner_id_foreign` foreign key (`proposed_partner_id`) references `accounting_partners` (`id`) on delete set null;

alter table `accounting_import_items` add constraint `accounting_import_items_proposed_client_id_foreign` foreign key (`proposed_client_id`) references `accounting_clients` (`id`) on delete set null;

alter table `accounting_import_items` add constraint `accounting_import_items_proposed_project_id_foreign` foreign key (`proposed_project_id`) references `accounting_projects` (`id`) on delete set null;

alter table `accounting_import_items` add constraint `accounting_import_items_transaction_id_foreign` foreign key (`transaction_id`) references `accounting_transactions` (`id`) on delete set null;

alter table `accounting_import_items` add constraint `accounting_import_items_reviewed_by_foreign` foreign key (`reviewed_by`) references `users` (`id`) on delete set null;

alter table `accounting_import_items` add index `accounting_import_items_fingerprint_hash_index`(`fingerprint_hash`);

create table `accounting_reconciliations` (`id` bigint unsigned not null auto_increment primary key, `reconciliation_date` date not null, `title` varchar(255) not null, `reported_cash_balance` decimal(14, 4) not null default '250', `calculated_historical_cash` decimal(14, 4) null, `cash_discrepancy` decimal(14, 4) null, `cash_audit_status` varchar(255) not null default 'estimated_needs_physical_audit', `confirmed_abdullah_debt` decimal(14, 4) not null default '1277', `confirmed_amer_debt` decimal(14, 4) not null default '1277', `total_confirmed_partner_debt` decimal(14, 4) not null default '2554', `amer_repayment_deducted` tinyint(1) not null default '1', `abdullah_amer_settlement_handled` tinyint(1) not null default '1', `founding_capital_amount` decimal(14, 4) not null default '700', `status` varchar(255) not null default 'active', `notes` text null, `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_reconciliations` add constraint `accounting_reconciliations_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;

create table `accounting_audit_logs` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `action` varchar(255) not null, `entity_type` varchar(255) not null, `entity_id` bigint unsigned null, `description` text not null, `old_values` json null, `new_values` json null, `ip_address` varchar(255) null, `user_agent` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `accounting_audit_logs` add constraint `accounting_audit_logs_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

alter table `users` add `role` varchar(255) not null default 'admin';

alter table `users` add `permissions` json null;