-- Run after 20261001_teacher_payroll.sql.
-- Payroll finalization and actual disbursement are tracked separately.
ALTER TABLE `payroll_items`
    ADD COLUMN `payment_status` VARCHAR(20) NOT NULL DEFAULT 'unpaid' AFTER `net_pay`,
    ADD COLUMN `payment_method` VARCHAR(30) NULL AFTER `payment_status`,
    ADD COLUMN `payment_reference` VARCHAR(100) NULL AFTER `payment_method`,
    ADD COLUMN `paid_at` DATETIME NULL AFTER `payment_reference`,
    ADD COLUMN `paid_by` VARCHAR(100) NULL AFTER `paid_at`,
    ADD INDEX `idx_payroll_items_payment_status` (`payment_status`);
