-- Keep inactive teacher assignment rows as history; the generated unique active_subject_id
-- index continues to enforce at most one current teacher for each subject offering.
ALTER TABLE `employee_subject_assignments`
    ADD KEY `idx_employee_subject_employee` (`employee_id`),
    DROP INDEX `uq_employee_subject_grade_year`;
