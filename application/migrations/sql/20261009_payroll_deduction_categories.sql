-- Run after 20261008_payroll_paid_leave.sql.
-- Keep statutory payroll deductions distinguishable from other deductions.
ALTER TABLE `payroll_deductions`
    ADD COLUMN `category` VARCHAR(30) NOT NULL DEFAULT 'other' AFTER `payroll_item_id`,
    ADD INDEX `idx_payroll_deductions_category` (`category`);
