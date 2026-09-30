CREATE TABLE `payroll_tax_deductions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payroll_item_id` BIGINT UNSIGNED NOT NULL,
    `sss_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `philhealth_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `pagibig_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `withholding_tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_by` VARCHAR(100) NULL,
    `updated_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payroll_tax_deductions_item` (`payroll_item_id`),
    CONSTRAINT `fk_payroll_tax_deductions_item`
        FOREIGN KEY (`payroll_item_id`) REFERENCES `payroll_items` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
