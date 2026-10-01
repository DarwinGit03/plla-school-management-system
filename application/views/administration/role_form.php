<?php if (!empty($error_message)): ?><div class="alert alert-danger"><?= html_escape($error_message); ?></div><?php endif; ?>
<?php $role = $access['role']; ?>
<form method="post" action="<?= site_url('admin/roles/save'); ?>">
    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
    <input type="hidden" name="role_id" value="<?= (int) $role->id; ?>">
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Role details</h2></div>
        <div class="card-body"><div class="row g-3"><div class="col-md-6"><label class="form-label" for="roleName">Role name</label><input class="form-control" id="roleName" name="role_name" maxlength="50" value="<?= html_escape($role->role_name); ?>" required <?= !empty($role->is_system) ? 'readonly' : ''; ?>></div><div class="col-md-6"><label class="form-label" for="roleStatus">Status</label><select class="form-select" id="roleStatus" name="is_active" <?= !empty($role->is_system) ? 'disabled' : ''; ?>><option value="1" <?= $role->is_active ? 'selected' : ''; ?>>Active</option><option value="0" <?= !$role->is_active ? 'selected' : ''; ?>>Inactive</option></select><?php if (!empty($role->is_system)): ?><input type="hidden" name="is_active" value="1"><?php endif; ?></div><div class="col-12"><label class="form-label" for="roleDescription">Description</label><input class="form-control" id="roleDescription" name="description" maxlength="255" value="<?= html_escape($role->description); ?>"></div></div></div>
    </section>
    <?php $modules = []; foreach ($access['permissions'] as $permission) $modules[$permission->module][] = $permission; ?>
    <?php foreach ($modules as $module => $permissions): ?>
        <section class="card border-0 shadow-sm mb-3"><div class="card-header bg-white"><h3 class="h6 fw-bold mb-0"><?= html_escape($module); ?></h3></div><div class="list-group list-group-flush">
            <?php foreach ($permissions as $permission): ?><label class="list-group-item d-flex gap-3 align-items-start"><input class="form-check-input mt-1" type="checkbox" name="permission_ids[]" value="<?= (int) $permission->id; ?>" <?= !empty($permission->granted) ? 'checked' : ''; ?> <?= !empty($role->is_system) ? 'disabled' : ''; ?>><span><strong><?= html_escape($permission->name); ?></strong><span class="d-block small text-secondary"><?= html_escape($permission->description); ?></span></span></label><?php endforeach; ?>
        </div></section>
    <?php endforeach; ?>
    <?php if (!empty($role->is_system)): foreach ($access['permissions'] as $permission): ?><input type="hidden" name="permission_ids[]" value="<?= (int) $permission->id; ?>"><?php endforeach; endif; ?>
    <div class="d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="<?= site_url('admin/roles'); ?>">Cancel</a><button class="btn btn-primary" type="submit">Save role</button></div>
</form>
