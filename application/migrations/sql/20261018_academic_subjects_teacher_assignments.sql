-- Subject catalog and school-year / grade-level teacher assignments.
-- Grade options continue to come from configured fee grades; grade_levels is not used.
CREATE TABLE `academic_subjects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subject_code` VARCHAR(30) NULL,
    `subject_name` VARCHAR(120) NOT NULL,
    `description` VARCHAR(500) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` VARCHAR(100) NULL,
    `updated_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_academic_subject_code` (`subject_code`),
    UNIQUE KEY `uq_academic_subject_name` (`subject_name`),
    KEY `idx_academic_subject_active_name` (`is_active`, `subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_subject_assignments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `school_year` VARCHAR(20) NOT NULL,
    `grade_code` VARCHAR(30) NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `assigned_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_employee_subject_grade_year` (`employee_id`, `subject_id`, `school_year`, `grade_code`),
    KEY `idx_employee_subject_year_grade` (`school_year`, `grade_code`, `status`),
    KEY `idx_employee_subject_subject_year` (`subject_id`, `school_year`),
    CONSTRAINT `fk_employee_subject_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_employee_subject_subject` FOREIGN KEY (`subject_id`) REFERENCES `academic_subjects` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`permission_key`, `module`, `name`, `description`)
VALUES ('academic.subjects.manage', 'Academic', 'Manage subjects and teacher assignments', 'Create subjects and assign employees by school year and grade.')
ON DUPLICATE KEY UPDATE
    `module` = VALUES(`module`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT `roles`.`id`, `permissions`.`id`
FROM `roles`
JOIN `permissions` ON `permissions`.`permission_key` = 'academic.subjects.manage'
WHERE LOWER(`roles`.`role_name`) IN ('admin', 'administrator', 'principal');
