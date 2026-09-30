<?php
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
?>

<?php if (!empty($success_message)): ?><div class="alert alert-success"><?= html_escape($success_message); ?></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger"><?= html_escape($error_message); ?></div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="small text-secondary">Current rates effective <?= html_escape($current_settings->effective_from); ?></div>
        <h2 class="h5 fw-bold mb-0">Payroll deduction rates</h2>
    </div>
    <a href="<?= site_url('payroll'); ?>" class="btn btn-outline-secondary">Back to payroll</a>
</div>

<div class="alert alert-info">Set the employee contribution rates once. Payroll calculates each employee's deductions from monthly salary and saves the amounts with each payroll run.</div>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h3 class="h6 fw-bold mb-0">Update rates</h3>
        <div class="small text-secondary">Changes apply from the effective date and keep earlier payroll history unchanged.</div>
    </div>
    <form method="post" action="<?= site_url('payroll/settings/save'); ?>">
        <input type="hidden" name="<?= html_escape($csrfName); ?>" value="<?= html_escape($csrfHash); ?>">
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="effective_from">Effective from</label>
                    <input class="form-control" type="date" id="effective_from" name="effective_from" min="<?= html_escape(date('Y-m-d')); ?>" required>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Deduction type</th><th style="width: 240px">Employee share rate</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><strong>SSS</strong> <span class="text-secondary">(Social Security System)</span><div class="small text-secondary">Percentage of monthly salary credit (MSC).</div></td>
                            <td><div class="input-group"><input class="form-control" type="number" min="0" max="100" step="0.001" id="sss_employee_rate" name="sss_employee_rate" value="<?= html_escape($current_settings->sss_employee_rate); ?>" required><span class="input-group-text">%</span></div></td>
                        </tr>
                        <tr>
                            <td><strong>PhilHealth</strong> <span class="text-secondary">(Health Insurance)</span><div class="small text-secondary">Employee share of the total premium.</div></td>
                            <td><div class="input-group"><input class="form-control" type="number" min="0" max="100" step="0.001" id="philhealth_employee_rate" name="philhealth_employee_rate" value="<?= html_escape($current_settings->philhealth_total_rate * $current_settings->philhealth_employee_share / 100); ?>" required><span class="input-group-text">%</span></div></td>
                        </tr>
                        <tr>
                            <td><strong>Pag-IBIG</strong> <span class="text-secondary">(HDMF)</span><div class="small text-secondary">Uses 1% when monthly salary is PHP 1,500 or less.</div></td>
                            <td><div class="input-group"><input class="form-control" type="number" min="0" max="100" step="0.001" id="pagibig_high_rate" name="pagibig_high_rate" value="<?= html_escape($current_settings->pagibig_high_rate); ?>" required><span class="input-group-text">%</span></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-text mt-3">Withholding tax remains calculated separately using the system's BIR tax schedule.</div>
        </div>
        <div class="card-footer bg-white text-end"><button class="btn btn-primary" type="submit">Save rates</button></div>
    </form>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h3 class="h6 fw-bold mb-0">Rate history</h3></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Effective from</th><th>SSS employee share</th><th>PhilHealth employee share</th><th>Pag-IBIG employee share</th></tr></thead>
            <tbody>
            <?php foreach ($settings_history as $policy): ?>
                <tr>
                    <td><?= html_escape($policy->effective_from); ?><?= $policy->id == $current_settings->id ? ' · Current' : ''; ?></td>
                    <td><?= number_format((float) $policy->sss_employee_rate, 3); ?>% of MSC</td>
                    <td><?= number_format((float) ($policy->philhealth_total_rate * $policy->philhealth_employee_share / 100), 3); ?>%</td>
                    <td><?= number_format((float) $policy->pagibig_high_rate, 3); ?>% (1% at/below PHP 1,500)</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
