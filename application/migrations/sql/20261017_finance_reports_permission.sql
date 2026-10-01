INSERT INTO `permissions` (`permission_key`, `module`, `name`, `description`)
SELECT 'finance.reports.view', 'Finance', 'View finance reports', 'View collection summaries and outstanding fee balances.'
WHERE NOT EXISTS (
    SELECT 1 FROM `permissions` WHERE `permission_key` = 'finance.reports.view'
);

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT `roles`.`id`, `permissions`.`id`
FROM `roles`
CROSS JOIN `permissions`
WHERE LOWER(`roles`.`role_name`) IN ('admin', 'administrator', 'principal', 'finance', 'accountant')
  AND `permissions`.`permission_key` = 'finance.reports.view'
  AND NOT EXISTS (
      SELECT 1 FROM `role_permissions`
      WHERE `role_permissions`.`role_id` = `roles`.`id`
        AND `role_permissions`.`permission_id` = `permissions`.`id`
  );
