CREATE TABLE `school_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(160) NOT NULL,
    `description` VARCHAR(2000) NULL,
    `location` VARCHAR(180) NULL,
    `starts_at` DATETIME NOT NULL,
    `ends_at` DATETIME NOT NULL,
    `status` ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    `created_by` VARCHAR(100) NULL,
    `updated_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_school_events_status_start` (`status`, `starts_at`),
    KEY `idx_school_events_status_end` (`status`, `ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`permission_key`, `module`, `name`, `description`)
VALUES ('dashboard.events.manage', 'Dashboard', 'Manage school events', 'Create, update, and cancel school-wide events.')
ON DUPLICATE KEY UPDATE
    `module` = VALUES(`module`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT `roles`.`id`, `permissions`.`id`
FROM `roles`
JOIN `permissions` ON `permissions`.`permission_key` = 'dashboard.events.manage'
WHERE LOWER(`roles`.`role_name`) IN ('admin', 'administrator', 'principal');
