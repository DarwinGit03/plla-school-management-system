<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url('admin/users'); ?>" class="row g-3 align-items-end">
            <div class="col-12 col-lg-5">
                <label for="userSearch" class="form-label">Find a user</label>
                <input id="userSearch" name="q" class="form-control" value="<?= html_escape($search); ?>" placeholder="Name, username, email, or employee no.">
            </div>
            <div class="col-6 col-lg-3">
                <label for="userRole" class="form-label">Role</label>
                <select id="userRole" name="role" class="form-select">
                    <option value="">All roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role->id; ?>" <?= (string) $role_filter === (string) $role->id ? 'selected' : ''; ?>><?= html_escape($role->role_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label for="userStatus" class="form-label">Status</label>
                <select id="userStatus" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users'); ?>">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-warning">Role IDs 4 and 5 are named Parent and Finance in the database, but some application access checks use the opposite mapping. Review the role page before changing an account's role.</div>

<section class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 fw-bold mb-0">User accounts</h2>
        <span class="small text-secondary"><?= number_format($total_users); ?> matching accounts</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">User</th><th>Username</th><th>Employee no.</th><th>Role</th><th>Status</th><th>Last login</th></tr></thead>
            <tbody>
            <?php if ($users): foreach ($users as $user): ?>
                <tr>
                    <td class="ps-3"><div class="fw-semibold"><?= html_escape(trim($user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name)); ?></div><div class="small text-secondary"><?= html_escape($user->email); ?></div></td>
                    <td><?= html_escape($user->username); ?></td>
                    <td><?= html_escape($user->employee_no ?: '—'); ?></td>
                    <td><?= html_escape($user->role_name ?: 'Unassigned'); ?></td>
                    <td><span class="badge text-bg-<?= $user->is_active ? 'success' : 'secondary'; ?>"><?= $user->is_active ? 'Active' : 'Inactive'; ?></span></td>
                    <td><?= $user->last_login ? html_escape($user->last_login) : 'Never'; ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center text-secondary py-5">No user accounts match these filters.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php $baseParams = array_filter(['q' => $search, 'role' => $role_filter, 'status' => $status_filter], function ($value) { return $value !== ''; }); ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-secondary">Page <?= (int) $current_page; ?> of <?= (int) $total_pages; ?></small>
        <div class="btn-group btn-group-sm">
            <?php $baseParams['page'] = max(1, $current_page - 1); ?><a class="btn btn-outline-secondary <?= $current_page <= 1 ? 'disabled' : ''; ?>" href="<?= html_escape(site_url('admin/users') . '?' . http_build_query($baseParams)); ?>">Previous</a>
            <?php $baseParams['page'] = min($total_pages, $current_page + 1); ?><a class="btn btn-outline-secondary <?= $current_page >= $total_pages ? 'disabled' : ''; ?>" href="<?= html_escape(site_url('admin/users') . '?' . http_build_query($baseParams)); ?>">Next</a>
        </div>
    </div>
</section>
