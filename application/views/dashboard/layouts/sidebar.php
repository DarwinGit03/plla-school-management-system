<!-- ==========================================================
     MOBILE SIDEBAR
========================================================== -->

<div
    class="offcanvas offcanvas-start app-sidebar app-sidebar-mobile text-white"
    tabindex="-1"
    id="sidebarMobile"
    aria-labelledby="sidebarMobileLabel"
    style="width: 290px;">

    <div class="offcanvas-header app-sidebar-brand">
        <a class="sidebar-brand-link" href="<?= site_url('dashboard'); ?>">
            <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
            <span class="sidebar-brand-name">PLLA<small>School Management</small></span>
        </a>

        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            aria-label="Close">
        </button>

    </div>


    <div class="offcanvas-body app-sidebar-body p-0">
        <div class="sidebar-navigation">
            <?php $this->load->view('dashboard/layouts/sidebar_menu', ['menu_suffix' => 'Mobile']); ?>
        </div>
        <?php $this->load->view('dashboard/layouts/sidebar_account'); ?>
    </div>

</div>


<!-- ==========================================================
     DESKTOP SIDEBAR
========================================================== -->

<aside
    id="sidebarDesktop"
    class="d-none d-lg-flex flex-column app-sidebar text-white">
    <div class="app-sidebar-brand">
        <a class="sidebar-brand-link" href="<?= site_url('dashboard'); ?>">
            <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
            <span class="sidebar-brand-name">PLLA<small>School Management</small></span>
        </a>
    </div>
    <div class="sidebar-navigation">
        <?php $this->load->view('dashboard/layouts/sidebar_menu', ['menu_suffix' => 'Desktop']); ?>
    </div>
    <?php $this->load->view('dashboard/layouts/sidebar_account'); ?>
</aside>
