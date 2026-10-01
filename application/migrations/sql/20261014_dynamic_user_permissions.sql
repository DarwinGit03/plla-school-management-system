CREATE TABLE `permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `permission_key` VARCHAR(100) NOT NULL,
    `module` VARCHAR(60) NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_permissions_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_permission_overrides` (
    `user_id` BIGINT NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `effect` ENUM('allow', 'deny') NOT NULL,
    `reason` VARCHAR(255) NULL,
    `updated_by` VARCHAR(100) NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `permission_id`),
    CONSTRAINT `fk_user_permission_overrides_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_user_permission_overrides_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `permissions` (`permission_key`, `module`, `name`, `description`) VALUES
('dashboard.view', 'Dashboard', 'View dashboard', 'Open the main dashboard.'),
('students.view', 'Students', 'View students', 'Browse student records.'),
('students.manage', 'Students', 'Manage students', 'Register and update student profiles.'),
('students.enrollment.manage', 'Students', 'Manage enrollment', 'Create or update student enrollment records.'),
('students.section.assign', 'Students', 'Assign sections', 'Assign enrolled students to academic sections.'),
('academic.sections.manage', 'Academic', 'Manage sections', 'Create, update, and deactivate academic sections.'),
('faculty.view', 'Faculty', 'View faculty', 'Browse faculty records.'),
('faculty.manage', 'Faculty', 'Manage faculty', 'Register and update faculty profiles.'),
('faculty.salary.manage', 'Faculty', 'Manage salaries', 'Set salary rates and review salary history.'),
('payroll.manage', 'Payroll', 'Manage payroll', 'Prepare, update, finalize, and pay payroll runs.'),
('payroll.settings.manage', 'Payroll', 'Manage payroll settings', 'Update statutory deduction rate settings.'),
('payroll.self.view', 'Payroll', 'View own payslips', 'View personal payroll history and payslips.'),
('finance.fees.manage', 'Finance', 'Manage fees', 'Manage school years and fee configurations.'),
('finance.payments.manage', 'Finance', 'Manage payments', 'Record, correct, and void student payments.'),
('parent.payments.view', 'Parent portal', 'View linked student payments', 'View payment history for linked students.'),
('administration.users.manage', 'Administration', 'Manage users', 'Review users and change role or user access.'),
('administration.roles.manage', 'Administration', 'Manage roles', 'Create roles and assign role permissions.'),
('administration.audit.view', 'Administration', 'View audit logs', 'Review recorded system activity.'),
('administration.settings.manage', 'Administration', 'Manage settings', 'Open organization-wide configuration settings.');

-- The database role labels are the source of truth for the initial grants.
UPDATE `roles` SET `is_active` = 1;
ALTER TABLE `roles` ADD COLUMN `is_system` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`;
UPDATE `roles` SET `is_system` = 1 WHERE LOWER(`role_name`) IN ('admin', 'administrator');

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT roles.id, permissions.id
FROM roles
CROSS JOIN permissions
WHERE LOWER(roles.role_name) IN ('admin', 'administrator', 'principal');

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.permission_key IN ('dashboard.view', 'payroll.self.view')
WHERE LOWER(roles.role_name) = 'teacher';

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.permission_key IN ('dashboard.view', 'parent.payments.view')
WHERE LOWER(roles.role_name) = 'parent';

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.permission_key IN (
    'dashboard.view', 'faculty.view', 'payroll.manage',
    'finance.fees.manage', 'finance.payments.manage'
)
WHERE LOWER(roles.role_name) IN ('finance', 'accountant');
