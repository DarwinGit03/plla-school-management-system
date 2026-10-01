<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$teacherName = trim(implode(' ', array_filter([$teacher->first_name, $teacher->middle_name, $teacher->last_name])));
$currentRate = !empty($salary_history) ? $salary_history[0] : null;
$currentAmount = $currentRate ? (float) ($currentRate->salary_type === 'daily' ? $currentRate->daily_salary : $currentRate->monthly_salary) : null;
$currentUnit = $currentRate ? ($currentRate->salary_type === 'daily' ? 'day' : 'month') : '';
?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= html_escape($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?= html_escape($error_message); ?></div>
<?php endif; ?>
<?php if (validation_errors()): ?>
    <div class="alert alert-danger" role="alert"><?= validation_errors('<div>', '</div>'); ?></div>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <div class="small text-secondary">Teacher</div>
            <h2 class="h5 fw-bold mb-1"><?= html_escape($teacherName); ?></h2>
            <div class="small text-secondary">Employee no. <?= html_escape($teacher->employee_no); ?></div>
        </div>
        <a href="<?= site_url('teachers'); ?>" class="btn btn-outline-secondary align-self-start"><i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Faculty directory</a>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4" id="salary-change">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-bold mb-1"><?= $currentRate ? 'Promote or raise salary' : 'Set starting salary'; ?></h2>
        <p class="small text-secondary mb-0"><?= $currentRate ? 'Review the proposed rate against the current rate. Saving adds a dated history entry and closes the previous rate.' : 'Set the initial salary rate. Future changes will be kept in the salary history.'; ?></p>
    </div>
    <form method="post" action="<?= site_url('teachers/salary/save'); ?>">
        <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
        <input type="hidden" name="employee_id" value="<?= (int) $teacher->id; ?>">
        <div class="card-body">
            <p class="small text-secondary">Each change is kept in salary history. The pay schedule determines which payroll periods include this teacher.</p>
            <div class="row g-3">
                <div class="col-12">
                    <div class="alert alert-light border mb-0" id="salaryRatePreview" data-current-amount="<?= $currentAmount === null ? '' : number_format($currentAmount, 2, '.', ''); ?>" data-current-unit="<?= html_escape($currentUnit); ?>">
                        <div class="row align-items-center g-2">
                            <div class="col-12 col-md-4">
                                <div class="small text-secondary">Latest saved rate</div>
                                <div class="fw-semibold" id="currentRateText"><?= $currentRate ? 'PHP ' . number_format($currentAmount, 2) . ' / ' . $currentUnit : 'No rate set'; ?></div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-secondary">Proposed rate</div>
                                <div class="fw-semibold" id="proposedRateText">Enter a rate to preview</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-secondary">Change</div>
                                <div class="fw-semibold" id="salaryChangeText">-</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label for="salary_type" class="form-label">Salary basis <span class="text-danger">*</span></label>
                    <select id="salary_type" name="salary_type" class="form-select" required>
                        <option value="monthly" <?= set_value('salary_type', 'monthly') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="daily" <?= set_value('salary_type') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="pay_frequency" class="form-label">Pay schedule <span class="text-danger">*</span></label>
                    <select id="pay_frequency" name="pay_frequency" class="form-select" required>
                        <option value="weekly" <?= set_value('pay_frequency', 'semi_monthly') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="semi_monthly" <?= set_value('pay_frequency', 'semi_monthly') === 'semi_monthly' ? 'selected' : ''; ?>>15th and month-end</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="salary_amount" class="form-label">Salary amount (PHP) <span class="text-danger">*</span></label>
                    <input type="number" id="salary_amount" name="salary_amount" class="form-control" min="0.01" step="0.01" value="<?= html_escape(set_value('salary_amount')); ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label for="effective_from" class="form-label">Effective from <span class="text-danger">*</span></label>
                    <input type="date" id="effective_from" name="effective_from" class="form-control" value="<?= html_escape(set_value('effective_from')); ?>" required>
                </div>
                <div class="col-12 col-md-8">
                    <label for="change_reason" class="form-label">Reason for change <span class="text-danger">*</span></label>
                    <input id="change_reason" name="change_reason" class="form-control" maxlength="255" value="<?= html_escape(set_value('change_reason')); ?>" placeholder="Annual review, promotion, contract change" required>
                </div>
                <div class="col-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1" aria-hidden="true"></i><?= $currentRate ? 'Save salary change' : 'Save starting salary'; ?></button>
                </div>
            </div>
        </div>
    </form>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Salary history</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">Salary basis</th><th>Pay schedule</th><th>Effective from</th><th>Effective to</th><th>Reason</th><th class="text-end pe-3">Rate (PHP)</th></tr></thead>
            <tbody>
                <?php if (empty($salary_history)): ?>
                    <tr><td colspan="6" class="py-4 text-center text-secondary">No salary rate has been recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($salary_history as $salary): ?>
                        <tr>
                            <td class="ps-3"><?= html_escape(ucfirst($salary->salary_type)); ?></td>
                            <td><?= $salary->pay_frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></td>
                            <td><?= html_escape($salary->effective_from); ?></td>
                            <td><?= $salary->effective_to ? html_escape($salary->effective_to) : '<span class="badge text-bg-success">Current</span>'; ?></td>
                            <td><?= html_escape($salary->change_reason ?? '-'); ?></td>
                            <td class="text-end pe-3 fw-semibold">PHP <?= number_format((float) ($salary->salary_type === 'daily' ? $salary->daily_salary : $salary->monthly_salary), 2); ?> / <?= $salary->salary_type === 'daily' ? 'day' : 'month'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
(function () {
    const preview = document.getElementById('salaryRatePreview');
    const amountInput = document.getElementById('salary_amount');
    const typeInput = document.getElementById('salary_type');
    const proposedText = document.getElementById('proposedRateText');
    const changeText = document.getElementById('salaryChangeText');
    if (!preview || !amountInput || !typeInput) return;

    const currentAmount = Number(preview.dataset.currentAmount || 0);
    const currentUnit = preview.dataset.currentUnit || '';
    const money = value => 'PHP ' + value.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const updatePreview = () => {
        const amount = Number(amountInput.value);
        const unit = typeInput.value === 'daily' ? 'day' : 'month';
        if (!amount || amount <= 0) {
            proposedText.textContent = 'Enter a rate to preview';
            changeText.textContent = '-';
            changeText.classList.remove('text-success', 'text-danger');
            return;
        }
        proposedText.textContent = money(amount) + ' / ' + unit;
        if (!currentAmount) {
            changeText.textContent = 'Starting rate';
        } else if (currentUnit !== unit) {
            changeText.textContent = 'Rate basis changes';
        } else {
            const difference = amount - currentAmount;
            const percent = currentAmount > 0 ? (difference / currentAmount) * 100 : 0;
            const sign = difference > 0 ? '+' : '';
            changeText.textContent = sign + money(difference) + ' / ' + unit + ' (' + sign + percent.toFixed(1) + '%)';
            changeText.classList.toggle('text-success', difference > 0);
            changeText.classList.toggle('text-danger', difference < 0);
        }
    };
    amountInput.addEventListener('input', updatePreview);
    typeInput.addEventListener('change', updatePreview);
    updatePreview();
})();
</script>

