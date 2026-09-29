-- Store actual payments separately from the immutable enrollment fee assessment.
-- Apply after enrollment_fee_records.sql has created student_enrollment_fees.

CREATE TABLE student_fee_payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    enrollment_fee_id INT UNSIGNED NOT NULL,
    receipt_number VARCHAR(32) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    reference_number VARCHAR(100) NULL,
    paid_at DATETIME NOT NULL,
    status ENUM('posted', 'voided') NOT NULL DEFAULT 'posted',
    recorded_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_fee_payments_receipt (receipt_number),
    KEY idx_student_fee_payments_fee_status (enrollment_fee_id, status),
    KEY idx_student_fee_payments_paid_at (paid_at),
    CONSTRAINT fk_student_fee_payments_enrollment_fee
        FOREIGN KEY (enrollment_fee_id)
        REFERENCES student_enrollment_fees(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Keep an immutable audit trail for payment voids and metadata corrections.
CREATE TABLE student_fee_payment_audit (
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
