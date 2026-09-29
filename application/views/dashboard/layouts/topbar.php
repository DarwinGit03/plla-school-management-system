<header class="navbar app-topbar px-3 px-lg-4">
    <button
        type="button"
        class="btn btn-light app-menu-toggle d-lg-none me-3"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMobile"
        aria-controls="sidebarMobile"
        aria-label="Open navigation menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

    <div class="app-topbar-heading">
        <?php if (!empty($breadcrumb)): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard'); ?>">Home</a></li>
                    <?php foreach ($breadcrumb as $index => $item): ?>
                        <li class="breadcrumb-item <?= $index === count($breadcrumb) - 1 ? 'active' : ''; ?>" <?= $index === count($breadcrumb) - 1 ? 'aria-current="page"' : ''; ?>>
                            <?= html_escape($item); ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php else: ?>
            <div class="app-topbar-title"><?= html_escape($page_title ?? 'Dashboard'); ?></div>
        <?php endif; ?>
    </div>

</header>
