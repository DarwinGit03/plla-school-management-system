-- One organization-wide, effective-dated payroll contribution policy.
-- Employee rates/caps reflect current published schedules at the time this migration is added;
-- administrators can add a new effective-dated policy when official schedules change.
CREATE TABLE `payroll_deduction_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `effective_from` DATE NOT NULL,
    `sss_employee_rate` DECIMAL(6,3) NOT NULL DEFAULT 5.000,
    `sss_min_base` DECIMAL(12,2) NOT NULL DEFAULT 5000.00,
    `sss_max_base` DECIMAL(12,2) NOT NULL DEFAULT 35000.00,
    `sss_credit_step` DECIMAL(12,2) NOT NULL DEFAULT 500.00,
    `sss_mpf_threshold` DECIMAL(12,2) NOT NULL DEFAULT 20000.00,
    `sss_mpf_employee_rate` DECIMAL(6,3) NOT NULL DEFAULT 5.000,
    `philhealth_total_rate` DECIMAL(6,3) NOT NULL DEFAULT 5.000,
    `philhealth_employee_share` DECIMAL(6,3) NOT NULL DEFAULT 50.000,
    `philhealth_min_base` DECIMAL(12,2) NOT NULL DEFAULT 10000.00,
    `philhealth_max_base` DECIMAL(12,2) NOT NULL DEFAULT 100000.00,
    `pagibig_low_threshold` DECIMAL(12,2) NOT NULL DEFAULT 1500.00,
    `pagibig_low_rate` DECIMAL(6,3) NOT NULL DEFAULT 1.000,
    `pagibig_high_rate` DECIMAL(6,3) NOT NULL DEFAULT 2.000,
    `pagibig_max_base` DECIMAL(12,2) NOT NULL DEFAULT 10000.00,
    `created_by` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payroll_deduction_settings_effective_from` (`effective_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payroll_deduction_settings` (
    `effective_from`, `created_at`
) VALUES (
    '2025-01-01', CURRENT_TIMESTAMP
);

-- Preserve tax/contribution values that were entered manually on earlier drafts/runs.
INSERT INTO `payroll_tax_deductions` (
    `payroll_item_id`, `sss_amount`, `philhealth_amount`, `pagibig_amount`,
    `withholding_tax_amount`, `created_by`, `updated_by`, `created_at`, `updated_at`
)
SELECT
    `payroll_item_id`,
    SUM(CASE WHEN `category` = 'sss' THEN `amount` ELSE 0 END),
    SUM(CASE WHEN `category` = 'philhealth' THEN `amount` ELSE 0 END),
    SUM(CASE WHEN `category` = 'pagibig' THEN `amount` ELSE 0 END),
    SUM(CASE WHEN `category` = 'withholding_tax' THEN `amount` ELSE 0 END),
    'migration', 'migration', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM `payroll_deductions`
WHERE `category` IN ('sss', 'philhealth', 'pagibig', 'withholding_tax')
GROUP BY `payroll_item_id`
ON DUPLICATE KEY UPDATE
    `sss_amount` = `sss_amount` + VALUES(`sss_amount`),
    `philhealth_amount` = `philhealth_amount` + VALUES(`philhealth_amount`),
    `pagibig_amount` = `pagibig_amount` + VALUES(`pagibig_amount`),
    `withholding_tax_amount` = `withholding_tax_amount` + VALUES(`withholding_tax_amount`),
    `updated_by` = 'migration',
    `updated_at` = CURRENT_TIMESTAMP;

DELETE FROM `payroll_deductions`
WHERE `category` IN ('sss', 'philhealth', 'pagibig', 'withholding_tax');
