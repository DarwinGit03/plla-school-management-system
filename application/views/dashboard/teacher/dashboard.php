<?php if (!$teacher): ?>
    <section class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold">Your employee profile is not linked yet</h2>
            <p class="text-secondary mb-0">Please contact the school administrator to connect this account to your employee profile. Your salary records will appear here once linked.</p>
        </div>
    </section>
<?php else: ?>
    <?php $teacherName = trim(implode(' ', array_filter([$teacher->first_name, $teacher->middle_name, $teacher->last_name]))); ?>
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                <div>
                    <p class="text-secondary small mb-1">Welcome</p>
                    <h2 class="h4 fw-bold mb-1"><?= html_escape($teacherName); ?></h2>
                    <div class="text-secondary">Employee no. <?= html_escape($teacher->employee_no); ?><?php if (!empty($teacher->position_title)): ?> &middot; <?= html_escape($teacher->position_title); ?><?php endif; ?></div>
                </div>
                <a class="btn btn-primary" href="<?= site_url('my-payroll'); ?>"><i class="fas fa-file-invoice-dollar me-2" aria-hidden="true"></i>View salary and payslips</a>
            </div>
        </div>
    </section>

<?php endif; ?>

<div class="mt-4">
    <?php $this->load->view('dashboard/widgets/events_preview'); ?>
</div>
