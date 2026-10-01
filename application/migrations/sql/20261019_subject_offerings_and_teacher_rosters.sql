-- Store school year and grade with each subject offering and allow one active teacher per offering.
-- Existing single-scope assignments are copied to the subject before adding the scoped constraint.
-- Resolve any existing duplicate active teachers for the same subject/year/grade before migration.
ALTER TABLE `academic_subjects`
    ADD COLUMN `school_year` VARCHAR(20) NULL AFTER `id`,
    ADD COLUMN `grade_code` VARCHAR(30) NULL AFTER `school_year`;

UPDATE `academic_subjects` AS subjects
JOIN (
    SELECT `subject_id`, MIN(`school_year`) AS `school_year`, MIN(`grade_code`) AS `grade_code`
    FROM `employee_subject_assignments`
    GROUP BY `subject_id`
    HAVING COUNT(DISTINCT CONCAT(`school_year`, '|', `grade_code`)) = 1
) AS legacy_scope ON legacy_scope.`subject_id` = subjects.`id`
SET subjects.`school_year` = legacy_scope.`school_year`,
    subjects.`grade_code` = legacy_scope.`grade_code`;

ALTER TABLE `academic_subjects`
    DROP INDEX `uq_academic_subject_code`,
    DROP INDEX `uq_academic_subject_name`,
    ADD UNIQUE KEY `uq_academic_subject_code_year_grade` (`school_year`, `grade_code`, `subject_code`),
    ADD UNIQUE KEY `uq_academic_subject_name_year_grade` (`school_year`, `grade_code`, `subject_name`),
    ADD KEY `idx_academic_subject_year_grade_active` (`school_year`, `grade_code`, `is_active`);

ALTER TABLE `employee_subject_assignments`
    ADD COLUMN `active_subject_id` INT UNSIGNED
        GENERATED ALWAYS AS (CASE WHEN `status` = 'active' THEN `subject_id` ELSE NULL END) STORED,
    ADD UNIQUE KEY `uq_employee_subject_one_active_offering`
        (`active_subject_id`, `school_year`, `grade_code`);

INSERT INTO `permissions` (`permission_key`, `module`, `name`, `description`)
VALUES ('academic.subjects.view_own', 'Academic', 'View assigned class lists', 'View student rosters for the signed-in teacher’s own subject assignments.')
ON DUPLICATE KEY UPDATE
    `module` = VALUES(`module`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT `roles`.`id`, `permissions`.`id`
FROM `roles`
JOIN `permissions` ON `permissions`.`permission_key` = 'academic.subjects.view_own'
WHERE LOWER(`roles`.`role_name`) = 'teacher';
