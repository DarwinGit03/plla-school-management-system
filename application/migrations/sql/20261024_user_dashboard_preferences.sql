-- Persist per-user dashboard selections across logouts and computer restarts.
CREATE TABLE `user_preferences` (
    `user_id` BIGINT NOT NULL,
    `preference_key` VARCHAR(100) NOT NULL,
    `preference_value` VARCHAR(255) NOT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `preference_key`),
    CONSTRAINT `fk_user_preferences_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
