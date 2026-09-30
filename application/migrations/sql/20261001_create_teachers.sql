-- Run once in school_management_system to enable the faculty directory.
CREATE TABLE `teachers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT NULL,
    `employee_no` VARCHAR(100) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `middle_name` VARCHAR(100) NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `suffix` VARCHAR(30) NULL,
    `work_email` VARCHAR(150) NULL,
    `phone` VARCHAR(40) NULL,
    `position_title` VARCHAR(100) NULL,
    `employment_type` VARCHAR(30) NOT NULL DEFAULT 'full_time',
    `hire_date` DATE NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `created_by` VARCHAR(100) NULL,
    `updated_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_teachers_employee_no` (`employee_no`),
    UNIQUE KEY `uq_teachers_user_id` (`user_id`),
    KEY `idx_teachers_status_name` (`status`, `last_name`, `first_name`),
    CONSTRAINT `fk_teachers_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
