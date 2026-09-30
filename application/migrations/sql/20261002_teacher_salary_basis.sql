-- Run after the teacher and payroll migrations.
-- Existing salary rows remain monthly by default.
ALTER TABLE `teacher_salary_history`
    ADD COLUMN `salary_type` VARCHAR(10) NOT NULL DEFAULT 'monthly' AFTER `teacher_id`,
    ADD COLUMN `daily_salary` DECIMAL(12,2) NULL AFTER `monthly_salary`,
    MODIFY COLUMN `monthly_salary` DECIMAL(12,2) NULL,
    ADD CONSTRAINT `chk_teacher_salary_basis`
        CHECK (
            (`salary_type` = 'monthly' AND `monthly_salary` > 0 AND `daily_salary` IS NULL)
            OR (`salary_type` = 'daily' AND `daily_salary` > 0 AND `monthly_salary` IS NULL)
        );

ALTER TABLE `payroll_items`
    MODIFY COLUMN `monthly_salary_snapshot` DECIMAL(12,2) NULL,
    ADD COLUMN `salary_type_snapshot` VARCHAR(10) NOT NULL DEFAULT 'monthly' AFTER `teacher_name_snapshot`,
    ADD COLUMN `daily_salary_snapshot` DECIMAL(12,2) NULL AFTER `monthly_salary_snapshot`,
    ADD COLUMN `payable_days` DECIMAL(6,2) NULL AFTER `daily_salary_snapshot`;
