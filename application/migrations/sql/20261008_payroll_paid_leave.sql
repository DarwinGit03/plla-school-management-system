-- Run after 20261007_payroll_attendance_absences.sql.
-- Record approved paid leave separately from actual attendance and unpaid absence.
ALTER TABLE `payroll_items`
    ADD COLUMN `paid_leave_days` DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER `absence_days`;
