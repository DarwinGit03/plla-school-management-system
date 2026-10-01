<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
<div class="alert alert-info">Assign permissions to a role once. Users inherit those permissions and can receive individual allow or deny overrides from their user access page.</div>
<section class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><div><h2 class="h6 fw-bold mb-1">Roles and access</h2><div class="small text-secondary"><?= count($permissions); ?> available permissions</div></div><a class="btn btn-primary btn-sm" href="<?= site_url('admin/roles/create'); ?>"><i class="fas fa-plus me-1"></i>Create role</a></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-3">Role</th><th>Description</th><th>Users</th><th>Permissions</th><th>Status</th><th class="text-end pe-3">Action</th></tr></thead>
        <tbody><?php foreach ($roles as $role): ?>
            <tr><td class="ps-3"><div class="fw-semibold"><?= html_escape($role->role_name); ?><?php if (!empty($role->is_system)): ?> <span class="badge text-bg-light">System</span><?php endif; ?></div><div class="small text-secondary">Role #<?= (int) $role->id; ?></div></td><td><?= html_escape($role->description ?: '—'); ?></td><td><?= number_format((int) $role->user_count); ?></td><td><?= number_format((int) ($role->permission_count ?? 0)); ?></td><td><span class="badge text-bg-<?= $role->is_active ? 'success' : 'secondary'; ?>"><?= $role->is_active ? 'Active' : 'Inactive'; ?></span></td><td class="text-end pe-3"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/roles/' . (int) $role->id . '/edit'); ?>">Manage access</a></td></tr>
        <?php endforeach; ?></tbody>
    </table></div>
</section>
