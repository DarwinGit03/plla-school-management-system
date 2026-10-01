-- Employee and parent portals are opt-in for administrative roles.
-- Admins and principals keep their dashboard and management permissions;
-- personal portal access can still be granted as a user-level override.
DELETE role_permissions
FROM role_permissions
JOIN roles ON roles.id = role_permissions.role_id
JOIN permissions ON permissions.id = role_permissions.permission_id
WHERE LOWER(roles.role_name) IN ('admin', 'administrator', 'principal')
  AND permissions.permission_key IN ('payroll.self.view', 'parent.payments.view');
