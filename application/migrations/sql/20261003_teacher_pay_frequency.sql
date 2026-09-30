-- Run after 20261002_teacher_salary_basis.sql.
-- Existing teachers keep the current default twice-monthly schedule.
ALTER TABLE `teacher_salary_history`
    ADD COLUMN `pay_frequency` VARCHAR(20) NOT NULL DEFAULT 'semi_monthly' AFTER `salary_type`,
    ADD CONSTRAINT `chk_teacher_pay_frequency`
        CHECK (`pay_frequency` IN ('monthly', 'semi_monthly'));

-- Monthly and 15th/month-end schedules may cover overlapping calendar dates.
ALTER TABLE `payroll_periods`
    DROP INDEX `uq_payroll_period_range`,
    ADD UNIQUE KEY `uq_payroll_period_range_frequency` (`period_start`, `period_end`, `frequency`);
