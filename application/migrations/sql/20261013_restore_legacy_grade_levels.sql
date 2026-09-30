-- Optional rollback for 20261013_remove_unused_grade_levels.sql.
CREATE TABLE `grade_levels` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `grade_name` VARCHAR(100) NOT NULL,
    `grade_code` VARCHAR(100) NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `grade_levels` (`id`, `grade_name`, `grade_code`, `status`, `created_at`) VALUES
    (1, 'Nursery', 'N', 'active', '2026-09-08 11:12:36'),
    (2, 'Kindergarten', 'K', 'active', '2026-09-08 11:12:36'),
    (3, 'Grade 1', '1', 'active', '2026-09-08 11:12:36'),
    (4, 'Grade 2', '2', 'active', '2026-09-08 11:12:36'),
    (5, 'Grade 3', '3', 'active', '2026-09-08 11:12:36'),
    (6, 'Grade 4', '4', 'active', '2026-09-08 11:12:36'),
    (7, 'Grade 5', '5', 'active', '2026-09-08 11:12:36'),
    (8, 'Grade 6', '6', 'active', '2026-09-08 11:12:36');
