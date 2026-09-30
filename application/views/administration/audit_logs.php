<section class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form method="get" action="<?= site_url('admin/audit-logs'); ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-8"><label for="auditSearch" class="form-label mb-1">Search audit activity</label><input id="auditSearch" name="q" class="form-control" value="<?= html_escape($search); ?>" placeholder="Module, action, description, employee no., or IP"></div>
            <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Search</button></div>
            <div class="col-auto"><a class="btn btn-outline-secondary" href="<?= site_url('admin/audit-logs'); ?>">Clear</a></div>
            <div class="col text-md-end small text-secondary"><?= number_format($total_logs); ?> events</div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">Date and time</th><th>User</th><th>Module / action</th><th>Description</th><th>Record</th><th>IP address</th></tr></thead>
            <tbody>
            <?php if ($logs): foreach ($logs as $log): ?>
                <?php $actor = trim(($log->first_name ?? '') . ' ' . ($log->last_name ?? '')); ?>
                <tr><td class="ps-3 text-nowrap"><?= html_escape($log->created_at); ?></td><td><?= html_escape($actor ?: ($log->employee_no ?: 'System')); ?><div class="small text-secondary"><?= html_escape($log->employee_no ?: ''); ?></div></td><td><div class="fw-semibold"><?= html_escape($log->action); ?></div><div class="small text-secondary"><?= html_escape($log->module); ?></div></td><td><?= html_escape($log->description ?: '—'); ?></td><td><?= $log->record_id !== null ? (int) $log->record_id : '—'; ?></td><td><?= html_escape($log->ip_address ?: '—'); ?></td></tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center text-secondary py-5">No audit events match this search.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php $logParams = $search !== '' ? ['q' => $search] : []; ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center"><small class="text-secondary">Page <?= (int) $current_page; ?> of <?= (int) $total_pages; ?></small><div class="btn-group btn-group-sm"><?php $logParams['page'] = max(1, $current_page - 1); ?><a class="btn btn-outline-secondary <?= $current_page <= 1 ? 'disabled' : ''; ?>" href="<?= html_escape(site_url('admin/audit-logs') . '?' . http_build_query($logParams)); ?>">Previous</a><?php $logParams['page'] = min($total_pages, $current_page + 1); ?><a class="btn btn-outline-secondary <?= $current_page >= $total_pages ? 'disabled' : ''; ?>" href="<?= html_escape(site_url('admin/audit-logs') . '?' . http_build_query($logParams)); ?>">Next</a></div></div>
</section>
