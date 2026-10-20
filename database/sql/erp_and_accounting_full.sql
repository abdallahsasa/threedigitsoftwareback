-- ================================================================
-- THREE DIGIT SOFTWARE - ERP & ACCOUNTING SYSTEM COMPLETE SQL
-- Generated for production deployment / phpMyAdmin / MySQL CLI
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- 1. Ensure users table has role & permissions
SET @col_role = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role');
SET @sql_role = IF(@col_role = 0, 'ALTER TABLE `users` ADD COLUMN `role` VARCHAR(255) NOT NULL DEFAULT \'admin\', ADD COLUMN `permissions` JSON NULL', 'SELECT 1');
PREPARE stmt_role FROM @sql_role;
EXECUTE stmt_role;
DEALLOCATE PREPARE stmt_role;

-- 2. Tables Schema
CREATE TABLE IF NOT EXISTS `accounting_partners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_debt_balance` decimal(14,4) NOT NULL DEFAULT '1277.0000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_partners_slug_unique` (`slug`),
  KEY `accounting_partners_user_id_foreign` (`user_id`),
  CONSTRAINT `accounting_partners_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('asset','liability','equity','revenue','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_id` bigint unsigned DEFAULT NULL,
  `current_balance` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_accounts_code_unique` (`code`),
  KEY `accounting_accounts_partner_id_foreign` (`partner_id`),
  CONSTRAINT `accounting_accounts_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `accounting_partners` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'company',
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `normalized_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_sector` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `potential_receivable_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `internal_notes` text COLLATE utf8mb4_unicode_ci,
  `public_notes` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_clients_normalized_name_index` (`normalized_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_sector` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_categories` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `scope` text COLLATE utf8mb4_unicode_ci,
  `deliverables` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `total_price` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `direct_costs` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `status` enum('in_progress','done','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
  `time_spent_hours` decimal(8,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_projects_client_id_foreign` (`client_id`),
  CONSTRAINT `accounting_projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `journal_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entry_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry_date` date NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('posted','reversed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'posted',
  `is_reversal` tinyint(1) NOT NULL DEFAULT '0',
  `reversed_entry_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_entries_entry_number_unique` (`entry_number`),
  KEY `journal_entries_reversed_entry_id_foreign` (`reversed_entry_id`),
  KEY `journal_entries_created_by_foreign` (`created_by`),
  CONSTRAINT `journal_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entries_reversed_entry_id_foreign` FOREIGN KEY (`reversed_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `journal_entry_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `partner_id` bigint unsigned DEFAULT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `debit` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `credit` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_lines_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `journal_entry_lines_account_id_foreign` (`account_id`),
  KEY `journal_entry_lines_partner_id_foreign` (`partner_id`),
  KEY `journal_entry_lines_client_id_foreign` (`client_id`),
  KEY `journal_entry_lines_project_id_foreign` (`project_id`),
  CONSTRAINT `journal_entry_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_entry_lines_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entry_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_entry_lines_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `accounting_partners` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entry_lines_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `accounting_projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `partner_id` bigint unsigned DEFAULT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `expense_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'company',
  `payer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `money_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'company_funds',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `review_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `review_reason` text COLLATE utf8mb4_unicode_ci,
  `reviewer_id` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `source_sheet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_row` int DEFAULT NULL,
  `fingerprint_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_transactions_transaction_number_unique` (`transaction_number`),
  KEY `accounting_transactions_account_id_foreign` (`account_id`),
  KEY `accounting_transactions_partner_id_foreign` (`partner_id`),
  KEY `accounting_transactions_client_id_foreign` (`client_id`),
  KEY `accounting_transactions_project_id_foreign` (`project_id`),
  KEY `accounting_transactions_reviewer_id_foreign` (`reviewer_id`),
  KEY `accounting_transactions_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `accounting_transactions_created_by_foreign` (`created_by`),
  KEY `accounting_transactions_fingerprint_hash_index` (`fingerprint_hash`),
  CONSTRAINT `accounting_transactions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_transactions_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_transactions_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_transactions_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `accounting_partners` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_transactions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `accounting_projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_transactions_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_import_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_rows` int NOT NULL DEFAULT '0',
  `imported_rows` int NOT NULL DEFAULT '0',
  `flagged_rows` int NOT NULL DEFAULT '0',
  `rejected_rows` int NOT NULL DEFAULT '0',
  `imported_by` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `summary` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_import_batches_batch_number_unique` (`batch_number`),
  KEY `accounting_import_batches_imported_by_foreign` (`imported_by`),
  CONSTRAINT `accounting_import_batches_imported_by_foreign` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_import_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint unsigned NOT NULL,
  `sheet_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `row_index` int NOT NULL,
  `raw_data` json NOT NULL,
  `fingerprint_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date DEFAULT NULL,
  `amount` decimal(14,4) DEFAULT NULL,
  `proposed_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proposed_partner_id` bigint unsigned DEFAULT NULL,
  `proposed_client_id` bigint unsigned DEFAULT NULL,
  `proposed_project_id` bigint unsigned DEFAULT NULL,
  `review_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_review',
  `review_reason` text COLLATE utf8mb4_unicode_ci,
  `suggested_action` text COLLATE utf8mb4_unicode_ci,
  `final_decision` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decision_notes` text COLLATE utf8mb4_unicode_ci,
  `transaction_id` bigint unsigned DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_import_items_batch_id_foreign` (`batch_id`),
  KEY `accounting_import_items_proposed_partner_id_foreign` (`proposed_partner_id`),
  KEY `accounting_import_items_proposed_client_id_foreign` (`proposed_client_id`),
  KEY `accounting_import_items_proposed_project_id_foreign` (`proposed_project_id`),
  KEY `accounting_import_items_transaction_id_foreign` (`transaction_id`),
  KEY `accounting_import_items_reviewed_by_foreign` (`reviewed_by`),
  KEY `accounting_import_items_fingerprint_hash_index` (`fingerprint_hash`),
  CONSTRAINT `accounting_import_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `accounting_import_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_import_items_proposed_client_id_foreign` FOREIGN KEY (`proposed_client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_import_items_proposed_partner_id_foreign` FOREIGN KEY (`proposed_partner_id`) REFERENCES `accounting_partners` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_import_items_proposed_project_id_foreign` FOREIGN KEY (`proposed_project_id`) REFERENCES `accounting_projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_import_items_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_import_items_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `accounting_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_reconciliations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reconciliation_date` date NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reported_cash_balance` decimal(14,4) NOT NULL DEFAULT '250.0000',
  `calculated_historical_cash` decimal(14,4) DEFAULT NULL,
  `cash_discrepancy` decimal(14,4) DEFAULT NULL,
  `cash_audit_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estimated_needs_physical_audit',
  `confirmed_abdullah_debt` decimal(14,4) NOT NULL DEFAULT '1277.0000',
  `confirmed_amer_debt` decimal(14,4) NOT NULL DEFAULT '1277.0000',
  `total_confirmed_partner_debt` decimal(14,4) NOT NULL DEFAULT '2554.0000',
  `amer_repayment_deducted` tinyint(1) NOT NULL DEFAULT '1',
  `abdullah_amer_settlement_handled` tinyint(1) NOT NULL DEFAULT '1',
  `founding_capital_amount` decimal(14,4) NOT NULL DEFAULT '700.0000',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_reconciliations_created_by_foreign` (`created_by`),
  CONSTRAINT `accounting_reconciliations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `accounting_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_company_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Three Digit Software',
  `legal_company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commercial_registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registered_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_swift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_methods_text` text COLLATE utf8mb4_unicode_ci,
  `default_payment_terms` text COLLATE utf8mb4_unicode_ci,
  `document_footer` text COLLATE utf8mb4_unicode_ci,
  `default_document_language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ar',
  `invoice_prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TDS-INV-',
  `quotation_prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TDS-QT-',
  `receipt_prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TDS-REC-',
  `credit_note_prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TDS-CN-',
  `default_tax_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `default_tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'service',
  `default_price` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` enum('draft','sent','accepted','rejected','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `document_language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ar',
  `subtotal` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `discount_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `total_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `scope` text COLLATE utf8mb4_unicode_ci,
  `deliverables` text COLLATE utf8mb4_unicode_ci,
  `payment_schedule` text COLLATE utf8mb4_unicode_ci,
  `terms_and_conditions` text COLLATE utf8mb4_unicode_ci,
  `customer_notes` text COLLATE utf8mb4_unicode_ci,
  `private_notes` text COLLATE utf8mb4_unicode_ci,
  `converted_to_project_id` bigint unsigned DEFAULT NULL,
  `converted_to_invoice_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_quotations_quotation_number_unique` (`quotation_number`),
  KEY `accounting_quotations_client_id_foreign` (`client_id`),
  KEY `accounting_quotations_project_id_foreign` (`project_id`),
  KEY `accounting_quotations_converted_to_project_id_foreign` (`converted_to_project_id`),
  KEY `accounting_quotations_created_by_foreign` (`created_by`),
  CONSTRAINT `accounting_quotations_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_quotations_converted_to_project_id_foreign` FOREIGN KEY (`converted_to_project_id`) REFERENCES `accounting_projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_quotations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_quotations_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `accounting_projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_quotation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unit',
  `unit_price` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `discount_type` enum('none','percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `line_total` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_quotation_items_quotation_id_foreign` (`quotation_id`),
  KEY `accounting_quotation_items_service_id_foreign` (`service_id`),
  CONSTRAINT `accounting_quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `accounting_quotations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_quotation_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `accounting_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `quotation_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `exchange_rate` decimal(14,4) NOT NULL DEFAULT '1.0000',
  `document_language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ar',
  `document_status` enum('draft','issued','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `payment_status` enum('unpaid','partially_paid','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `sending_status` enum('not_sent','pending','provider_accepted','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_sent',
  `is_advance` tinyint(1) NOT NULL DEFAULT '0',
  `subtotal` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `discount_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `taxable_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `total_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `paid_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `balance_due` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `payment_terms` text COLLATE utf8mb4_unicode_ci,
  `payment_instructions` text COLLATE utf8mb4_unicode_ci,
  `customer_notes` text COLLATE utf8mb4_unicode_ci,
  `private_notes` text COLLATE utf8mb4_unicode_ci,
  `frozen_company_snapshot` json DEFAULT NULL,
  `frozen_client_snapshot` json DEFAULT NULL,
  `frozen_items_snapshot` json DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `issued_by` bigint unsigned DEFAULT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_invoices_invoice_number_unique` (`invoice_number`),
  KEY `accounting_invoices_client_id_foreign` (`client_id`),
  KEY `accounting_invoices_project_id_foreign` (`project_id`),
  KEY `accounting_invoices_quotation_id_foreign` (`quotation_id`),
  KEY `accounting_invoices_issued_by_foreign` (`issued_by`),
  KEY `accounting_invoices_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `accounting_invoices_created_by_foreign` (`created_by`),
  CONSTRAINT `accounting_invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_invoices_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_invoices_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_invoices_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `accounting_projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_invoices_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `accounting_quotations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unit',
  `unit_price` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `discount_type` enum('none','percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `line_total` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `accounting_invoice_items_service_id_foreign` (`service_id`),
  CONSTRAINT `accounting_invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `accounting_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_invoice_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `accounting_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `exchange_rate` decimal(14,4) NOT NULL DEFAULT '1.0000',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `receiving_account_id` bigint unsigned NOT NULL,
  `handled_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `type` enum('invoice_payment','customer_advance','unapplied_payment','refund') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'invoice_payment',
  `allocated_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `unallocated_amount` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_payments_payment_number_unique` (`payment_number`),
  KEY `accounting_payments_client_id_foreign` (`client_id`),
  KEY `accounting_payments_receiving_account_id_foreign` (`receiving_account_id`),
  KEY `accounting_payments_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `accounting_payments_created_by_foreign` (`created_by`),
  CONSTRAINT `accounting_payments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_payments_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_payments_receiving_account_id_foreign` FOREIGN KEY (`receiving_account_id`) REFERENCES `accounting_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_payment_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_payment_allocations_payment_id_foreign` (`payment_id`),
  KEY `accounting_payment_allocations_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `accounting_payment_allocations_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `accounting_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_payment_allocations_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `accounting_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` bigint unsigned NOT NULL,
  `receipt_date` date NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `frozen_receipt_snapshot` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_receipts_receipt_number_unique` (`receipt_number`),
  KEY `accounting_receipts_payment_id_foreign` (`payment_id`),
  CONSTRAINT `accounting_receipts_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `accounting_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_credit_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `credit_note_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','issued') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_credit_notes_credit_note_number_unique` (`credit_note_number`),
  KEY `accounting_credit_notes_invoice_id_foreign` (`invoice_id`),
  KEY `accounting_credit_notes_client_id_foreign` (`client_id`),
  KEY `accounting_credit_notes_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `accounting_credit_notes_created_by_foreign` (`created_by`),
  CONSTRAINT `accounting_credit_notes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_credit_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_credit_notes_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `accounting_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_credit_notes_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_document_shares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` enum('quotation','invoice','receipt','statement') COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `view_count` int NOT NULL DEFAULT '0',
  `last_viewed_at` timestamp NULL DEFAULT NULL,
  `last_viewed_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_channel` enum('whatsapp','email','direct_link') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct_link',
  `recipient_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_status` enum('not_sent','queued','provider_accepted','delivered','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_sent',
  `email_error` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_document_shares_token_unique` (`token`),
  KEY `accounting_document_shares_client_id_foreign` (`client_id`),
  KEY `accounting_document_shares_created_by_foreign` (`created_by`),
  CONSTRAINT `accounting_document_shares_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `accounting_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_document_shares_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed Initial Configuration, Partners, Services & Chart of Accounts
INSERT IGNORE INTO `accounting_company_settings` (`id`, `brand_name`, `default_document_language`, `invoice_prefix`, `quotation_prefix`, `receipt_prefix`, `credit_note_prefix`, `default_tax_enabled`, `default_tax_rate`, `created_at`, `updated_at`) VALUES (1, 'Three Digit Software', 'ar', 'TDS-INV-', 'TDS-QT-', 'TDS-REC-', 'TDS-CN-', 0, 0.00, NOW(), NOW());

INSERT IGNORE INTO `accounting_partners` (`id`, `name_ar`, `name_en`, `slug`, `initial_debt_balance`, `is_active`, `created_at`, `updated_at`) VALUES (1, 'عبدالله الساسا', 'Abdullah Al Sasa', 'abdullah', 1277.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_partners` (`id`, `name_ar`, `name_en`, `slug`, `initial_debt_balance`, `is_active`, `created_at`, `updated_at`) VALUES (2, 'عامر', 'Amer', 'amer', 1277.000000, 1, NOW(), NOW());

INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (1, 'تطوير المواقع وتطبيقات الويب', 'Website & Web Application Development', 'website_development', 'تصميم وبرمجة المواقع التعريفية ومنصات الويب بأحدث التقنيات', 'Modern responsive website and web portal design & engineering', 'project', 1200.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (2, 'تطوير البرمجيات والأنظمة المخصصة', 'Custom Software & ERP Development', 'software_development', 'بناء أنظمة إدارة داخلية ولوحات تحكم مخصصة للشركات', 'Tailored business automation and internal CRM/ERP software', 'project', 2500.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (3, 'حلول المتاجر والتجارة الإلكترونية', 'E-Commerce Platforms & Solutions', 'e_commerce', 'إنشاء متاجر إلكترونية متكاملة مع بوابات الدفع والشحن', 'End-to-end e-commerce store with checkout & inventory integrations', 'project', 1800.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (4, 'إدارة حملات وسائل التواصل الاجتماعي', 'Social Media Management & Campaigns', 'social_media', 'إدارة المحتوى، كتابة المنشورات، وإطلاق الحملات الإعلانية الممولة', 'Social media content creation, publishing, and paid advertising', 'month', 450.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (5, 'تصميم الهوية البصرية وتجربة المستخدم', 'Branding & UI/UX Design', 'design', 'تصميم شعارات، هويات بصرية كاملة، وواجهات مستخدم تفاعلية', 'Logo, full visual brand identity, and UI/UX design mockups', 'service', 600.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (6, 'التصوير والإنتاج الإعلامي', 'Photography & Media Production', 'photography', 'جلسات تصوير احترافية للمنتجات ومقاطع فيديو إعلانية للمشاريع', 'Commercial product photography, videography, and reels editing', 'session', 350.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (7, 'الاستضافة السحابية وإدارة السيرفرات', 'Cloud Hosting & Server Infrastructure', 'hosting', 'خوادم سريعة ومحمية مع نسخ احتياطي دوري ودعم فني', 'Secure cloud hosting, daily backups, and performance monitoring', 'year', 200.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_services` (`id`, `name_ar`, `name_en`, `category`, `description_ar`, `description_en`, `unit`, `default_price`, `is_active`, `created_at`, `updated_at`) VALUES (8, 'الصيانة والدعم الفني المستمر', 'Maintenance & Ongoing Support', 'maintenance', 'تحديثات أمنية دورية، حل المشكلات التقنية، وتطوير المزايا', 'Ongoing technical support, bug fixing, and software updates', 'month', 150.000000, 1, NOW(), NOW());

INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (1, '1010', 'صندوق الشركة (نقدية)', 'Cash in Hand (Company Fund)', 'asset', 'cash', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (2, '1020', 'الحساب البنكي / محافظ إلكترونية', 'Bank / Digital Wallets', 'asset', 'bank', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (3, '1100', 'ذمم العملاء المدينة', 'Accounts Receivable', 'asset', 'receivable', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (4, '1200', 'إيجار مدفوع مقدماً', 'Prepaid Rent', 'asset', 'prepaid_rent', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (5, '1500', 'الأصول الثابتة والمعدات', 'Office Equipment & Assets', 'asset', 'fixed_asset', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (6, '1590', 'مجمع إهلاك الأصول', 'Accumulated Depreciation', 'asset', 'contra_asset', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (7, '2101', 'دين الشركة للشريك عبدالله', 'Partner Debt - Abdullah Al Sasa', 'liability', 'partner_debt', 1, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (8, '2102', 'دين الشركة للشريك عامر', 'Partner Debt - Amer', 'liability', 'partner_debt', 2, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (9, '2103', 'ذمم دائنة معلقة - عثمان', 'Other Payables - Othman', 'liability', 'other_payable', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (10, '2200', 'ذمم الموردين الدائنة', 'Accounts Payable', 'liability', 'accounts_payable', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (11, '3010', 'رأس المال التأسيسي', 'Founding Capital', 'equity', 'capital', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (12, '3101', 'سحوبات وتوزيعات الشريك عبدالله', 'Partner Drawings - Abdullah', 'equity', 'drawings', 1, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (13, '3102', 'سحوبات وتوزيعات الشريك عامر', 'Partner Drawings - Amer', 'equity', 'drawings', 2, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (14, '3200', 'تسوية انتقال تاريخية معلقة للمطابقة', 'Historical Cutover Reconciliation Reserve', 'equity', 'reconciliation_reserve', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (15, '3300', 'الأرباح / الخسائر المدورة', 'Retained Earnings', 'equity', 'retained_earnings', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (16, '4010', 'إيرادات المشاريع والخدمات', 'Project & Service Revenue', 'revenue', 'operating_revenue', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (17, '5010', 'تكاليف المشاريع المباشرة وأجور التنفيذ', 'Direct Project Costs & Execution Labor', 'expense', 'direct_cost', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (18, '6010', 'مصروف الإيجار', 'Rent Expense', 'expense', 'operating_expense', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (19, '6020', 'مصاريف المكتب والضيافة والتجهيزات', 'Office, Hospitality & Supplies', 'expense', 'operating_expense', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (20, '6030', 'برمجيات واشتراكات وأدوات رقمية', 'Software & Digital Tools', 'expense', 'operating_expense', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (21, '6040', 'التسويق والإعلانات والعمولات', 'Marketing, Ads & Commissions', 'expense', 'operating_expense', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (22, '6050', 'مصروف إهلاك الأصول', 'Depreciation Expense', 'expense', 'operating_expense', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (23, '6090', 'مصاريف تشغيلية أخرى', 'Other Operating Expenses', 'expense', 'operating_expense', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (24, '2250', 'ضريبة القيمة المضافة / المبيعات المستحقة', 'Sales Tax / VAT Payable', 'liability', 'sales_tax_payable', NULL, 0.000000, 1, NOW(), NOW());
INSERT IGNORE INTO `accounting_accounts` (`id`, `code`, `name_ar`, `name_en`, `type`, `sub_type`, `partner_id`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES (25, '2300', 'إيرادات مؤجلة / دفعات عملاء مقدمة', 'Deferred Revenue / Customer Advances', 'liability', 'deferred_revenue', NULL, 0.000000, 1, NOW(), NOW());

INSERT IGNORE INTO `accounting_reconciliations` (`id`, `reconciliation_date`, `title`, `reported_cash_balance`, `cash_audit_status`, `confirmed_abdullah_debt`, `confirmed_amer_debt`, `total_confirmed_partner_debt`, `amer_repayment_deducted`, `abdullah_amer_settlement_handled`, `founding_capital_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES (1, '2026-10-13', 'Baseline Cutover Transition - 13 Oct 2026', 250.0000, 'estimated_needs_physical_audit', 1277.0000, 1277.0000, 2554.0000, 1, 1, 700.0000, 'active', 'Historical baseline transition balances confirmed by partners.', NOW(), NOW());

-- 4. Register Migrations in Laravel migration log
INSERT IGNORE INTO `migrations` (`migration`, `batch`) SELECT '2026_10_17_000001_create_accounting_core_tables', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations` WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_17_000001_create_accounting_core_tables');
INSERT IGNORE INTO `migrations` (`migration`, `batch`) SELECT '2026_10_19_000001_create_erp_and_invoicing_tables', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations` WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_19_000001_create_erp_and_invoicing_tables');

SET FOREIGN_KEY_CHECKS = 1;
