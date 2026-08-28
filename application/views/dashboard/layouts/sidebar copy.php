<!-- ===============================
     Mobile Sidebar
================================ -->

<div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="sidebarMobile"
    aria-labelledby="sidebarMobileLabel">

    <div class="offcanvas-header border-bottom">

        <h5
            class="fw-bold mb-0"
            id="sidebarMobileLabel">

            PLLA

        </h5>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="Close">
        </button>

    </div>

    <div class="offcanvas-body p-0">

        <?php
            $this->load->view(
                'dashboard/layouts/sidebar_menu'
            );
        ?>

    </div>

</div>


<!-- ===============================
     Desktop Sidebar
================================ -->

<aside
    id="sidebarDesktop"
    class="d-none d-lg-flex flex-column">

    <div class="p-4 border-bottom">

        <div class="d-flex align-items-center">

            <img
                src="<?= base_url('assets/images/logo.png'); ?>"
                width="45"
                class="me-3"
                alt="Logo">

            <div>

                <h5 class="mb-0 text-white fw-bold">

                    PLLA

                </h5>

                <small class="text-light">

                    School Management

                </small>

            </div>

        </div>

    </div>


    <div class="flex-grow-1 overflow-auto py-3">

        <?php
            $this->load->view(
                'dashboard/layouts/sidebar_menu'
            );
        ?>

    </div>


    <div class="border-top p-3">

        <small class="text-secondary">

            Version 1.0.0

        </small>

    </div>

</aside>