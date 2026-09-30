-- Run after 20261004_teacher_weekly_pay.sql.
-- Existing salary rates remain valid; new salary changes should record a reason.
ALTER TABLE `teacher_salary_history`
    ADD COLUMN `change_reason` VARCHAR(255) NULL AFTER `effective_from`;
