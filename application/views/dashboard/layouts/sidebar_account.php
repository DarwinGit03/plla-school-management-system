<?php
$sidebarUser = $current_user ?? null;
$sidebarName = trim(($sidebarUser->first_name ?? '') . ' ' . ($sidebarUser->last_name ?? ''));
if ($sidebarName === '') {
    $sidebarName = trim((string) ($sidebarUser->username ?? $sidebarUser->email ?? 'User'));
}
$sidebarRole = trim((string) ($sidebarUser->role_name ?? ''));
if ($sidebarRole === '') {
    $sidebarRole = 'Role ' . (int) ($sidebarUser->role_id ?? $this->session->userdata('role_id'));
}
?>

<div class="sidebar-account">
    <div class="sidebar-user-card">
        <img src="<?= base_url('assets/images/avatar-default.png'); ?>" alt="" class="sidebar-user-avatar">
        <div class="sidebar-user-info">
            <strong><?= html_escape($sidebarName); ?></strong>
            <span><?= html_escape($sidebarRole); ?></span>
        </div>
    </div>
    <a class="btn sidebar-logout-btn" href="<?= site_url('auth/logout'); ?>">
        <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
        <span>Sign out</span>
    </a>
    <div class="sidebar-version">PLLA School Management <span>v1.0.0</span></div>
</div>
