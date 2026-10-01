<?php if (!empty($success_message)): ?><div class="alert alert-success alert-dismissible fade show" role="status"><?= html_escape($success_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= html_escape($error_message); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<?php if (!$teacher): ?>
    <div class="alert alert-info" role="status">
        Your login is not linked to a teacher profile yet. Please contact the school administrator to link your account.
    </div>
<?php else: ?>
    <section class="mb-4" id="account-settings">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">My account</h2>
                <p class="small text-secondary mb-0">Manage your personal information and login details.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                    <i class="fas fa-user-pen me-1" aria-hidden="true"></i>Edit personal details
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fas fa-key me-1" aria-hidden="true"></i>Change password
                </button>
            </div>
        </div>
        <section class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                <div>
                    <div class="fw-semibold"><?= html_escape($account->username); ?></div>
                    <div class="small text-secondary"><?= html_escape($account->email); ?></div>
                </div>
                <div class="small text-secondary">Your employee contact email is kept in sync with your account email.</div>
            </div>
        </section>
    </section>

    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="<?= site_url('my-payroll/profile'); ?>">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editAccountTitle">Personal and login details</h2>
                            <p class="small text-secondary mb-0">Update your details. Your current password is required to save changes.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <div class="row g-3">
                            <div class="col-12 col-md-6"><label for="profileFirstName" class="form-label">First name</label><input id="profileFirstName" name="first_name" class="form-control" maxlength="100" value="<?= html_escape($account->first_name); ?>" required></div>
                            <div class="col-12 col-md-6"><label for="profileMiddleName" class="form-label">Middle name</label><input id="profileMiddleName" name="middle_name" class="form-control" maxlength="100" value="<?= html_escape($account->middle_name ?? ''); ?>"></div>
                            <div class="col-12 col-md-6"><label for="profileLastName" class="form-label">Last name</label><input id="profileLastName" name="last_name" class="form-control" maxlength="100" value="<?= html_escape($account->last_name); ?>" required></div>
                            <div class="col-12 col-md-6"><label for="profilePhone" class="form-label">Phone</label><input id="profilePhone" name="phone" type="tel" class="form-control" maxlength="40" value="<?= html_escape($teacher->phone ?? ''); ?>"></div>
                            <div class="col-12 col-md-6"><label for="profileUsername" class="form-label">Username</label><input id="profileUsername" name="username" class="form-control" maxlength="100" value="<?= html_escape($account->username); ?>" required></div>
                            <div class="col-12 col-md-6"><label for="profileEmail" class="form-label">Email</label><input id="profileEmail" name="email" type="email" class="form-control" maxlength="150" value="<?= html_escape($account->email); ?>" required><div class="form-text">This also updates the email on your employee profile.</div></div>
                            <div class="col-12"><label for="profileCurrentPassword" class="form-label">Current password <span class="text-danger">*</span></label><input id="profileCurrentPassword" name="current_password" type="password" class="form-control" autocomplete="current-password" required><div class="form-text">Confirm your identity before saving account changes.</div></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save account details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="<?= site_url('my-payroll/password'); ?>">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="changePasswordTitle">Change password</h2>
                            <p class="small text-secondary mb-0">Choose a strong password to protect your account.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                        <div class="mb-3"><label for="passwordCurrent" class="form-label">Current password</label><input id="passwordCurrent" name="current_password" type="password" class="form-control" autocomplete="current-password" required></div>
                        <div class="mb-3"><label for="passwordNew" class="form-label">New password</label><input id="passwordNew" name="new_password" type="password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required><div class="form-text">At least 12 characters, with uppercase and lowercase letters, a number, and a special character.</div></div>
                        <div><label for="passwordConfirm" class="form-label">Confirm new password</label><input id="passwordConfirm" name="password_confirm" type="password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-key me-1" aria-hidden="true"></i>Update password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.hash === '#edit-account') {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editAccountModal')).show();
            }
            if (window.location.hash === '#change-password') {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('changePasswordModal')).show();
            }
        });
    </script>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">My salary rate history</h2><div class="small text-secondary mt-1">Past and current salary rates recorded for your employee profile.</div></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Effective from</th><th>Effective to</th><th>Salary basis</th><th>Pay schedule</th><th>Reason</th><th class="text-end pe-3">Rate</th></tr></thead>
                <tbody>
                    <?php if (!$salary_history): ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">No salary rate history is available.</td></tr>
                    <?php else: foreach ($salary_history as $rate): ?>
                        <?php $frequency = $rate->pay_frequency ?? 'semi_monthly'; $amount = $rate->salary_type === 'daily' ? $rate->daily_salary : $rate->monthly_salary; ?>
                        <tr>
                            <td class="ps-3"><?= html_escape($rate->effective_from); ?></td>
                            <td><?= empty($rate->effective_to) ? '<span class="badge text-bg-success">Current</span>' : html_escape($rate->effective_to); ?></td>
                            <td><?= $rate->salary_type === 'daily' ? 'Daily' : 'Monthly'; ?></td>
                            <td><?= $frequency === 'weekly' ? 'Weekly' : '15th and month-end'; ?></td>
                            <td><?= html_escape($rate->change_reason ?? '—'); ?></td>
                            <td class="text-end pe-3">PHP <?= number_format((float) $amount, 2); ?><?= $rate->salary_type === 'daily' ? ' / day' : ' / month'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">My payroll history</h2><div class="small text-secondary mt-1">Payslips become available after payroll is finalized.</div></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Pay period</th><th>Pay date</th><th>Schedule</th><th class="text-end">Gross pay</th><th class="text-end">Deductions</th><th class="text-end">Net pay</th><th>Status</th><th class="text-end pe-3">Payslip</th></tr></thead>
                <tbody>
                    <?php if (!$payroll_items): ?>
                        <tr><td colspan="8" class="text-center text-secondary py-4">No payroll records are available yet.</td></tr>
                    <?php else: foreach ($payroll_items as $item): $paymentLabel = $item->payment_status === 'paid' ? 'Paid' : 'Finalized - awaiting payment'; $paymentBadge = $item->payment_status === 'paid' ? 'success' : 'warning'; ?>
                        <tr id="payroll-item-<?= (int) $item->id; ?>">
                            <td class="ps-3 fw-semibold"><?= html_escape($item->period_start . ' to ' . $item->period_end); ?><div class="small text-secondary"><?= html_escape($item->payable_days ?? '—'); ?> attended · <?= html_escape($item->paid_leave_days); ?> paid leave · <?= html_escape($item->absence_days); ?> unpaid absent</div></td>
                            <td><?= html_escape($item->pay_date); ?></td>
                            <td><?= $item->frequency === 'weekly' ? 'Weekly' : ($item->frequency === 'semi_monthly' ? '15th and month-end' : 'Legacy monthly'); ?></td>
                            <td class="text-end">PHP <?= number_format((float) $item->basic_pay, 2); ?></td>
                            <td class="text-end">PHP <?= number_format((float) $item->total_deductions, 2); ?></td>
                            <td class="text-end fw-semibold">PHP <?= number_format((float) $item->net_pay, 2); ?></td>
                            <td><span class="badge text-bg-<?= $paymentBadge; ?>"><?= html_escape($paymentLabel); ?></span></td>
                            <td class="text-end pe-3">
                                <?php if ($item->period_status === 'finalized'): ?>
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('my-payroll/payslip/' . (int) $item->id . '?view=1'); ?>" target="_blank" rel="noopener">View PDF</a>
                                        <a class="btn btn-sm btn-primary" href="<?= site_url('my-payroll/payslip/' . (int) $item->id); ?>">Download PDF</a>
                                        <form method="post" action="<?= site_url('my-payroll/payslip/' . (int) $item->id . '/email'); ?>">
                                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-envelope me-1" aria-hidden="true"></i>Email to me</button>
                                        </form>
                                    </div>
                                <?php else: ?><span class="small text-secondary">Not finalized</span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
