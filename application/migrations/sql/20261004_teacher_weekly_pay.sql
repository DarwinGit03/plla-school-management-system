-- Run after 20261003_teacher_pay_frequency.sql.
-- Keep existing salary profiles on the standard twice-monthly schedule.
ALTER TABLE `teacher_salary_history`
    DROP CONSTRAINT `chk_teacher_pay_frequency`;

UPDATE `teacher_salary_history`
SET `pay_frequency` = 'semi_monthly'
WHERE `pay_frequency` = 'monthly';

ALTER TABLE `teacher_salary_history`
    ADD CONSTRAINT `chk_teacher_pay_frequency`
        CHECK (`pay_frequency` IN ('weekly', 'semi_monthly'));
