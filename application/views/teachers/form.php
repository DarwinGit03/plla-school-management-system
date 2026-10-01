<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$teacherValue = function ($field, $default = '') use ($teacher) {
    $fallback = $teacher && isset($teacher->$field) ? $teacher->$field : $default;
    return set_value($field, $fallback);
};
?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?= html_escape($error_message); ?></div>
<?php endif; ?>
<?php if (validation_errors()): ?>
    <div class="alert alert-danger" role="alert"><?= validation_errors('<div>', '</div>'); ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('teachers/save'); ?>" class="card border-0 shadow-sm">
    <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
    <input type="hidden" name="id" value="<?= $teacher ? (int) $teacher->id : 0; ?>">
    <div class="card-body p-3 p-lg-4">
        <div class="mb-3">
            <h2 class="h6 fw-bold mb-1">Teacher information</h2>
            <p class="small text-secondary mb-0">Enter the teacher's employment details.</p>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="employee_no" class="form-label">Employee number <span class="text-danger">*</span></label>
                <input id="employee_no" name="employee_no" class="form-control" maxlength="100" value="<?= html_escape($teacherValue('employee_no')); ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                <input id="first_name" name="first_name" class="form-control" maxlength="100" value="<?= html_escape($teacherValue('first_name')); ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="middle_name" class="form-label">Middle name</label>
                <input id="middle_name" name="middle_name" class="form-control" maxlength="100" value="<?= html_escape($teacherValue('middle_name')); ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                <input id="last_name" name="last_name" class="form-control" maxlength="100" value="<?= html_escape($teacherValue('last_name')); ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label for="position_title" class="form-label">Position</label>
                <input id="position_title" name="position_title" class="form-control" maxlength="100" value="<?= html_escape($teacherValue('position_title')); ?>" placeholder="Teacher I">
            </div>
            <div class="col-12 col-md-4">
                <label for="work_email" class="form-label">Work email</label>
                <input type="email" id="work_email" name="work_email" class="form-control" maxlength="150" value="<?= html_escape($teacherValue('work_email')); ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control" maxlength="40" value="<?= html_escape($teacherValue('phone')); ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="employment_type" class="form-label">Employment type <span class="text-danger">*</span></label>
                <?php $employmentType = $teacherValue('employment_type', 'full_time'); ?>
                <select id="employment_type" name="employment_type" class="form-select" required>
                    <option value="full_time" <?= $employmentType === 'full_time' ? 'selected' : ''; ?>>Full time</option>
                    <option value="part_time" <?= $employmentType === 'part_time' ? 'selected' : ''; ?>>Part time</option>
                    <option value="contractual" <?= $employmentType === 'contractual' ? 'selected' : ''; ?>>Contractual</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="hire_date" class="form-label">Hire date</label>
                <input type="date" id="hire_date" name="hire_date" class="form-control" value="<?= html_escape($teacherValue('hire_date')); ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="linked_user_id" class="form-label">Teacher login account</label>
                <?php $linkedUserId = set_value('linked_user_id', $teacher && !empty($teacher->user_id) ? $teacher->user_id : ($preselected_user_id ?? '')); ?>
                <select id="linked_user_id" name="linked_user_id" class="form-select">
                    <option value="">Not linked</option>
                    <?php foreach ($user_accounts as $account): ?>
                        <option value="<?= (int) $account->id; ?>" data-employee-no="<?= html_escape($account->employee_no); ?>" data-first-name="<?= html_escape($account->first_name); ?>" data-last-name="<?= html_escape($account->last_name); ?>" data-email="<?= html_escape($account->email); ?>" <?= (string) $linkedUserId === (string) $account->id ? 'selected' : ''; ?>>
                            <?= html_escape($account->employee_no . ' — ' . $account->first_name . ' ' . $account->last_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Link an existing Teacher role login to let the teacher view their own pay history.</div>
            </div>
            <?php if ($teacher): ?>
                <div class="col-12 col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <?php $status = $teacherValue('status', 'active'); ?>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!$teacher): ?>
            <hr class="my-4">
            <div class="mb-3">
                <h2 class="h6 fw-bold mb-1">Starting salary</h2>
                <p class="small text-secondary mb-0">Choose the salary rate and how often it is paid. Weekly and 15th/month-end schedules are supported.</p>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="salary_type" class="form-label">Salary basis <span class="text-danger">*</span></label>
                    <?php $salaryType = set_value('salary_type', 'monthly'); ?>
                    <select id="salary_type" name="salary_type" class="form-select" required>
                        <option value="monthly" <?= $salaryType === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="daily" <?= $salaryType === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="pay_frequency" class="form-label">Pay schedule <span class="text-danger">*</span></label>
                    <?php $payFrequency = set_value('pay_frequency', 'semi_monthly'); ?>
                    <select id="pay_frequency" name="pay_frequency" class="form-select" required>
                        <option value="weekly" <?= $payFrequency === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="semi_monthly" <?= $payFrequency === 'semi_monthly' ? 'selected' : ''; ?>>15th and month-end</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="salary_amount" class="form-label">Rate amount (PHP) <span class="text-danger">*</span></label>
                    <input type="number" id="salary_amount" name="salary_amount" class="form-control" min="0.01" step="0.01" value="<?= html_escape(set_value('salary_amount')); ?>" placeholder="0.00" required>
                    <div class="form-text">Enter a monthly amount for Monthly, or a per-workday amount for Daily.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label for="salary_effective_from" class="form-label">Effective from <span class="text-danger">*</span></label>
                    <input type="date" id="salary_effective_from" name="salary_effective_from" class="form-control" value="<?= html_escape(set_value('salary_effective_from', date('Y-m-d'))); ?>" required>
                </div>
                <div class="col-12 col-md-8">
                    <label for="salary_change_reason" class="form-label">Reason for this salary rate <span class="text-danger">*</span></label>
                    <input id="salary_change_reason" name="salary_change_reason" class="form-control" maxlength="255" value="<?= html_escape(set_value('salary_change_reason', 'Initial salary')); ?>" placeholder="Initial salary, annual review, promotion" required>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="<?= site_url('teachers'); ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1" aria-hidden="true"></i>Save teacher</button>
    </div>
</form>
<script>
    (function () {
        const accountSelect = document.getElementById('linked_user_id');
        if (!accountSelect) return;
        const fields = {
            employee_no: 'employee-no',
            first_name: 'first-name',
            last_name: 'last-name',
            work_email: 'email'
        };
        function fillFromAccount(onlyEmpty) {
            const option = accountSelect.options[accountSelect.selectedIndex];
            if (!option || !option.value) return;
            Object.keys(fields).forEach(function (fieldName) {
                const field = document.getElementById(fieldName);
                const value = option.dataset[fields[fieldName]] || '';
                if (field && value && (!onlyEmpty || !field.value)) field.value = value;
            });
        }
        fillFromAccount(true);
        accountSelect.addEventListener('change', function () { fillFromAccount(true); });
    })();
</script>
