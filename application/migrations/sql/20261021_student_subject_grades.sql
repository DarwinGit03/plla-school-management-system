-- Store teacher-entered assessment grades per active subject offering and enrollment.
CREATE TABLE `student_subject_grades` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` BIGINT UNSIGNED NOT NULL,
    `enrollment_id` INT UNSIGNED NOT NULL,
    `prelim_grade` DECIMAL(5,2) NULL,
    `midterm_grade` DECIMAL(5,2) NULL,
    `finals_grade` DECIMAL(5,2) NULL,
    `final_grade` DECIMAL(5,2) NULL,
    `updated_by` BIGINT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_student_subject_grade_enrollment` (`assignment_id`, `enrollment_id`),
    KEY `idx_student_subject_grades_enrollment` (`enrollment_id`),
    CONSTRAINT `fk_student_subject_grades_assignment`
        FOREIGN KEY (`assignment_id`) REFERENCES `employee_subject_assignments` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_student_subject_grades_enrollment`
        FOREIGN KEY (`enrollment_id`) REFERENCES `student_enrollments` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
