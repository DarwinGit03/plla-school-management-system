-- Run once in the school_management_system database.
ALTER TABLE `academic_sections`
    ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN `created_by` VARCHAR(50) NULL AFTER `created_at`,
    ADD COLUMN `updated_at` DATETIME NULL AFTER `created_by`,
    ADD COLUMN `updated_by` VARCHAR(50) NULL AFTER `updated_at`;
