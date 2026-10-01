-- Rename teacher-specific profile and salary tables to employee terminology.
-- Existing payroll rows and employee profiles are preserved.
RENAME TABLE
    `teachers` TO `employees`,
    `teacher_salary_history` TO `employee_salary_history`;

ALTER TABLE `employee_salary_history`
    DROP FOREIGN KEY `fk_teacher_salary_teacher`,
    CHANGE COLUMN `teacher_id` `employee_id` INT UNSIGNED NOT NULL,
    ADD CONSTRAINT `fk_employee_salary_employee`
        FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
        ON UPDATE CASCADE;

ALTER TABLE `payroll_items`
    DROP FOREIGN KEY `fk_payroll_item_teacher`,
    CHANGE COLUMN `teacher_id` `employee_id` INT UNSIGNED NOT NULL,
    CHANGE COLUMN `teacher_name_snapshot` `employee_name_snapshot` VARCHAR(255) NOT NULL,
    ADD CONSTRAINT `fk_payroll_item_employee`
        FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
        ON UPDATE CASCADE;

ALTER TABLE `employee_salary_history`
    DROP INDEX `uq_teacher_salary_effective_from`,
    DROP INDEX `idx_teacher_salary_effective_range`,
    ADD UNIQUE KEY `uq_employee_salary_effective_from` (`employee_id`, `effective_from`),
    ADD KEY `idx_employee_salary_effective_range` (`employee_id`, `effective_to`);

ALTER TABLE `payroll_items`
    DROP INDEX `uq_payroll_item_teacher_period`,
    DROP INDEX `idx_payroll_item_teacher`,
    ADD UNIQUE KEY `uq_payroll_item_employee_period` (`payroll_period_id`, `employee_id`),
    ADD KEY `idx_payroll_item_employee` (`employee_id`, `payroll_period_id`);

ALTER TABLE `employees`
    DROP INDEX `uq_teachers_employee_no`,
    DROP INDEX `uq_teachers_user_id`,
    DROP INDEX `idx_teachers_status_name`,
    ADD UNIQUE KEY `uq_employees_employee_no` (`employee_no`),
    ADD UNIQUE KEY `uq_employees_user_id` (`user_id`),
    ADD KEY `idx_employees_status_name` (`status`, `last_name`, `first_name`);
