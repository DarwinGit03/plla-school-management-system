-- Removes confirmed-unused legacy objects after checking application references
-- and confirming the affected tables are empty in the local database.
-- Keep `roles`: active authentication queries use its role_name.
ALTER TABLE `users` DROP COLUMN `login_attempts`;

DROP TABLE `activity_logs`, `api_tokens`, `permissions`, `role_permissions`;
