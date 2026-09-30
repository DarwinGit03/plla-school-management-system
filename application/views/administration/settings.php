<div class="row g-3 mb-4">
    <?php foreach ([['User accounts', $overview['users']], ['Active accounts', $overview['active_users']], ['Roles', $overview['roles']], ['Audit events', $overview['audit_events']]] as $metric): ?>
        <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary"><?= html_escape($metric[0]); ?></div><div class="h3 fw-bold mb-0"><?= number_format((int) $metric[1]); ?></div></div></div></div>
    <?php endforeach; ?>
</div>
<section class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Configuration</h2><div class="small text-secondary">Open a settings area to update its existing records.</div></div>
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= site_url('finance'); ?>"><span><strong>School years and fees</strong><span class="d-block small text-secondary">Manage school years, fee schedules, and payment configurations.</span></span><i class="fas fa-chevron-right text-secondary"></i></a>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= site_url('payroll/settings'); ?>"><span><strong>Payroll deductions</strong><span class="d-block small text-secondary">Manage effective-dated employee contribution rates.</span></span><i class="fas fa-chevron-right text-secondary"></i></a>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= site_url('admin/roles'); ?>"><span><strong>User roles</strong><span class="d-block small text-secondary">Review the role records assigned to accounts.</span></span><i class="fas fa-chevron-right text-secondary"></i></a>
    </div>
</section>
