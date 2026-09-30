-- Run after 20261001_create_teachers.sql and 20261001_teacher_salary_history.sql.
CREATE TABLE `payroll_periods` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `pay_date` DATE NOT NULL,
    `frequency` VARCHAR(20) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `created_by` VARCHAR(100) NULL,
    `finalized_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `finalized_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payroll_period_range` (`period_start`, `period_end`),
    KEY `idx_payroll_period_status_date` (`status`, `period_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payroll_period_id` BIGINT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `employee_no_snapshot` VARCHAR(100) NOT NULL,
    `teacher_name_snapshot` VARCHAR(255) NOT NULL,
    `position_snapshot` VARCHAR(100) NULL,
    `monthly_salary_snapshot` DECIMAL(12,2) NOT NULL,
    `basic_pay` DECIMAL(12,2) NOT NULL,
    `total_deductions` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `net_pay` DECIMAL(12,2) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payroll_item_teacher_period` (`payroll_period_id`, `teacher_id`),
    KEY `idx_payroll_item_teacher` (`teacher_id`, `payroll_period_id`),
    CONSTRAINT `fk_payroll_item_period`
        FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `fk_payroll_item_teacher`
        FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_deductions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payroll_item_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(150) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `notes` VARCHAR(500) NULL,
    `created_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payroll_deduction_item` (`payroll_item_id`),
    CONSTRAINT `fk_payroll_deduction_item`
        FOREIGN KEY (`payroll_item_id`) REFERENCES `payroll_items` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
