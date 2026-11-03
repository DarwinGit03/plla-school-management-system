-- Daily subject attendance, academic review workflow, report cards, and promotion history.
-- Existing grades are retained and begin in pending review through the new column default.

ALTER TABLE `student_subject_grades`
    ADD COLUMN `review_status` ENUM('pending_review', 'approved', 'returned') NOT NULL DEFAULT 'pending_review' AFTER `final_grade`,
    ADD COLUMN `submitted_at` DATETIME NULL AFTER `review_status`,
    ADD COLUMN `reviewed_by` BIGINT NULL AFTER `submitted_at`,
    ADD COLUMN `reviewed_at` DATETIME NULL AFTER `reviewed_by`,
    ADD COLUMN `review_note` VARCHAR(500) NULL AFTER `reviewed_at`,
    ADD KEY `idx_student_subject_grades_review` (`review_status`, `updated_at`);

CREATE TABLE `student_attendance_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` BIGINT UNSIGNED NOT NULL,
    -- academic_sections.id is a signed INT in this schema (see the enrollment section_id migration).
    `section_id` INT NOT NULL,
    `attendance_date` DATE NOT NULL,
    `created_by` BIGINT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attendance_assignment_section_date` (`assignment_id`, `section_id`, `attendance_date`),
    KEY `idx_attendance_session_date_section` (`attendance_date`, `section_id`),
    CONSTRAINT `fk_attendance_session_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `employee_subject_assignments` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_attendance_session_section` FOREIGN KEY (`section_id`) REFERENCES `academic_sections` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_attendance` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` BIGINT UNSIGNED NOT NULL,
    `enrollment_id` INT UNSIGNED NOT NULL,
    `attendance_status` ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    `updated_by` BIGINT NOT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attendance_session_enrollment` (`session_id`, `enrollment_id`),
    KEY `idx_attendance_enrollment` (`enrollment_id`),
    CONSTRAINT `fk_attendance_record_session` FOREIGN KEY (`session_id`) REFERENCES `student_attendance_sessions` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_attendance_record_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `student_enrollments` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `academic_correction_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_type` ENUM('attendance', 'grade') NOT NULL,
    `record_id` BIGINT UNSIGNED NOT NULL,
    `proposed_values` TEXT NOT NULL,
    `reason` VARCHAR(500) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `requested_by` BIGINT NOT NULL,
    `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_by` BIGINT NULL,
    `reviewed_at` DATETIME NULL,
    `review_note` VARCHAR(500) NULL,
    `pending_record_type` VARCHAR(20) GENERATED ALWAYS AS (CASE WHEN `status` = 'pending' THEN `record_type` ELSE NULL END) STORED,
    `pending_record_id` BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN `status` = 'pending' THEN `record_id` ELSE NULL END) STORED,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_academic_correction_one_pending` (`pending_record_type`, `pending_record_id`),
    KEY `idx_academic_corrections_status` (`status`, `requested_at`),
    KEY `idx_academic_corrections_record` (`record_type`, `record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_promotion_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `enrollment_id` INT UNSIGNED NOT NULL,
    `school_year` VARCHAR(20) NOT NULL,
    `decision` ENUM('promoted', 'retained', 'transferred') NOT NULL,
    `next_grade_code` VARCHAR(30) NULL,
    `decision_date` DATE NOT NULL,
    `notes` VARCHAR(500) NULL,
    `decided_by` BIGINT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_student_promotion_year_decision` (`school_year`, `decision`),
    CONSTRAINT `fk_student_promotion_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `student_enrollments` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`permission_key`, `module`, `name`, `description`) VALUES
('academic.attendance.review', 'Academic', 'Review attendance corrections', 'Review and approve or reject teacher requests to correct submitted attendance.'),
('academic.grades.review', 'Academic', 'Review and approve grades', 'Review teacher-submitted grades and grade correction requests.'),
('academic.report_cards.view', 'Academic', 'View report cards', 'Review and print student report cards with approved grades and attendance.'),
('academic.promotion.manage', 'Academic', 'Manage promotion history', 'Record and review year-end student promotion decisions.')
ON DUPLICATE KEY UPDATE `module` = VALUES(`module`), `name` = VALUES(`name`), `description` = VALUES(`description`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT `roles`.`id`, `permissions`.`id`
FROM `roles`
CROSS JOIN `permissions`
WHERE LOWER(`roles`.`role_name`) IN ('admin', 'administrator', 'principal')
  AND `permissions`.`permission_key` IN ('academic.attendance.review', 'academic.grades.review', 'academic.report_cards.view', 'academic.promotion.manage');
