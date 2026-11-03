-- Short-lived, rate-limited codes for guardian email sign-in.
-- Guardian emails in student_guardians are the allow-list for parent access.
CREATE TABLE `parent_portal_login_codes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `code_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `sent_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_parent_portal_login_email` (`email`),
    KEY `idx_parent_portal_login_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
