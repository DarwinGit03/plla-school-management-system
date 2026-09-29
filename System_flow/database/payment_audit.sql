-- Add a durable audit trail for voids and non-financial payment corrections.
-- Run once against an existing school_management_system database.

CREATE TABLE IF NOT EXISTS student_fee_payment_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payment_id INT UNSIGNED NOT NULL,
    action ENUM('void', 'correction') NOT NULL,
    field_name VARCHAR(40) NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    reason VARCHAR(500) NOT NULL,
    performed_by INT UNSIGNED NULL,
    performed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payment_audit_payment (payment_id, id),
    CONSTRAINT fk_payment_audit_payment
        FOREIGN KEY (payment_id)
        REFERENCES student_fee_payments(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
