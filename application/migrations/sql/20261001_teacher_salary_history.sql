-- Run after 20261001_create_teachers.sql.
CREATE TABLE `teacher_salary_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `teacher_id` INT UNSIGNED NOT NULL,
    `monthly_salary` DECIMAL(12,2) NOT NULL,
    `effective_from` DATE NOT NULL,
    `effective_to` DATE NULL,
    `created_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_teacher_salary_effective_from` (`teacher_id`, `effective_from`),
    KEY `idx_teacher_salary_effective_range` (`teacher_id`, `effective_to`),
    CONSTRAINT `fk_teacher_salary_teacher`
        FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `chk_teacher_salary_nonnegative`
        CHECK (`monthly_salary` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
