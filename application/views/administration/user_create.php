<?php if (!empty($error_message)): ?><div class="alert alert-danger" role="alert"><?= html_escape($error_message); ?></div><?php endif; ?>

<form method="post" action="<?= site_url('admin/users/save'); ?>" class="card border-0 shadow-sm" id="createUserForm">
    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
    <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Account details</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6"><label for="roleId" class="form-label">Role <span class="text-danger">*</span></label><select id="roleId" name="role_id" class="form-select" required><option value="">Choose a role</option><?php foreach ($roles as $role): ?><option value="<?= (int) $role->id; ?>" <?= (string) set_value('role_id') === (string) $role->id ? 'selected' : ''; ?>><?= html_escape($role->role_name); ?></option><?php endforeach; ?></select><div class="form-text">The account inherits this role's access. You can manage individual access after creation.</div></div>
            <div class="col-12 col-md-6"><label for="username" class="form-label">Username <span class="text-danger">*</span></label><input id="username" name="username" class="form-control" maxlength="100" value="<?= html_escape(set_value('username')); ?>" autocomplete="username" required></div>

            <div class="col-12 d-none" id="employeeProfileField"><label for="employeeId" class="form-label">Employee profile <span class="text-danger">*</span></label><select id="employeeId" name="employee_id" class="form-select" disabled><option value="">Choose an employee</option><?php foreach ($employees as $employee): ?><option value="<?= (int) $employee->id; ?>" <?= (string) set_value('employee_id') === (string) $employee->id ? 'selected' : ''; ?>><?= html_escape($employee->employee_no . ' · ' . trim($employee->first_name . ' ' . $employee->last_name) . ' · ' . $employee->work_email . ($employee->position_title ? ' · ' . $employee->position_title : '')); ?></option><?php endforeach; ?></select><div class="form-text">Name, work email, and employee number are taken from this profile. Only active employees with no account are listed.</div><?php if (empty($employees)): ?><div class="form-text text-warning">There are no eligible profiles. Add an active employee with a work email in Faculty first.</div><?php endif; ?></div>

            <div class="col-12 col-md-4 identity-field"><label for="firstName" class="form-label">First name <span class="text-danger">*</span></label><input id="firstName" name="first_name" class="form-control" maxlength="100" value="<?= html_escape(set_value('first_name')); ?>"></div>
            <div class="col-12 col-md-4 identity-field"><label for="middleName" class="form-label">Middle name</label><input id="middleName" name="middle_name" class="form-control" maxlength="100" value="<?= html_escape(set_value('middle_name')); ?>"></div>
            <div class="col-12 col-md-4 identity-field"><label for="lastName" class="form-label">Last name <span class="text-danger">*</span></label><input id="lastName" name="last_name" class="form-control" maxlength="100" value="<?= html_escape(set_value('last_name')); ?>"></div>
            <div class="col-12 col-md-4 identity-field"><label for="email" class="form-label">Email <span class="text-danger">*</span></label><input type="email" id="email" name="email" class="form-control" maxlength="150" value="<?= html_escape(set_value('email')); ?>" autocomplete="email"></div>
            <div class="col-12 col-md-4 identity-field"><label for="employeeNo" class="form-label">Employee / reference number <span class="text-danger">*</span></label><input id="employeeNo" name="employee_no" class="form-control" maxlength="100" value="<?= html_escape(set_value('employee_no')); ?>"><div class="form-text">Use a unique reference for non-employee accounts.</div></div>

            <div class="col-12 col-md-6"><label for="initialPassword" class="form-label">Initial password <span class="text-danger">*</span></label><input type="password" id="initialPassword" name="password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required><div class="form-text">12–72 characters, with uppercase, lowercase, a number, and a special character.</div></div>
            <div class="col-12 col-md-6"><label for="confirmPassword" class="form-label">Confirm password <span class="text-danger">*</span></label><input type="password" id="confirmPassword" name="password_confirm" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required></div>
        </div>
        <div class="alert alert-info mt-4 mb-0" id="employeeAccountHelp">For a Teacher login, choose the existing employee profile. The account will be linked directly to that profile, including its salary history.</div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="<?= site_url('admin/users'); ?>">Cancel</a><button class="btn btn-primary" type="submit"><i class="fas fa-user-plus me-1"></i>Create account</button></div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var role = document.getElementById('roleId');
    var profile = document.getElementById('employeeProfileField');
    var employee = document.getElementById('employeeId');
    var help = document.getElementById('employeeAccountHelp');
    var identity = document.querySelectorAll('.identity-field');
    var password = document.getElementById('initialPassword');
    var confirmation = document.getElementById('confirmPassword');
    var teacherRoleId = <?= (int) $teacher_role_id; ?>;
    function updateFields() {
        var isTeacher = teacherRoleId > 0 && role.value === String(teacherRoleId);
        profile.classList.toggle('d-none', !isTeacher);
        employee.disabled = !isTeacher;
        employee.required = isTeacher;
        help.classList.toggle('d-none', !isTeacher);
        identity.forEach(function (group) {
            group.classList.toggle('d-none', isTeacher);
            var input = group.querySelector('input');
            if (input) input.required = !isTeacher && input.id !== 'middleName';
        });
    }
    role.addEventListener('change', updateFields);
    updateFields();

    function validatePasswords() {
        var value = password.value;
        var meetsPolicy = value.length >= 12 && value.length <= 72
            && /[A-Z]/.test(value) && /[a-z]/.test(value)
            && /[0-9]/.test(value) && /[^A-Za-z0-9\s]/.test(value);
        password.setCustomValidity(value && !meetsPolicy
            ? 'Use 12–72 characters with uppercase, lowercase, a number, and a special character.'
            : '');
        confirmation.setCustomValidity(confirmation.value && confirmation.value !== value
            ? 'Passwords do not match.'
            : '');
    }
    password.addEventListener('input', validatePasswords);
    confirmation.addEventListener('input', validatePasswords);
});
</script>
