-- Run after 20261002_teacher_salary_basis.sql.
-- Store attendance and monthly absence deductions with each payroll snapshot.
ALTER TABLE `payroll_items`
    ADD COLUMN `absence_days` DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER `payable_days`,
    ADD COLUMN `absence_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `absence_days`,
    ADD COLUMN `attendance_confirmed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `absence_deduction`;
