-- One-time migration for storing fee assessments against an enrollment.
-- Run after confirming student_enrollments does not already have this column.

ALTER TABLE student_enrollments
    ADD COLUMN fee_configuration_id INT UNSIGNED NULL AFTER payment_mode,
    ADD KEY idx_enrollment_fee_configuration (fee_configuration_id),
    ADD CONSTRAINT fk_enrollment_fee_configuration
        FOREIGN KEY (fee_configuration_id)
        REFERENCES fee_configurations(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;

CREATE TABLE student_enrollment_fees (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    enrollment_id INT UNSIGNED NOT NULL,
    fee_category ENUM('tuition', 'worktext', 'extra_curricular', 'uniform') NOT NULL,
    source_fee_id INT UNSIGNED NULL,
    payment_sequence INT UNSIGNED NULL,
    payment_label VARCHAR(150) NOT NULL,
    payment_date DATE NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_student_enrollment_fees_enrollment (enrollment_id),
    CONSTRAINT fk_student_enrollment_fees_enrollment
        FOREIGN KEY (enrollment_id)
        REFERENCES student_enrollments(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
