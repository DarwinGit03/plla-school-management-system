<!-- ==========================================================
     MOBILE SIDEBAR
========================================================== -->

<div
    class="offcanvas offcanvas-start bg-dark text-white"
    tabindex="-1"
    id="sidebarMobile"
    aria-labelledby="sidebarMobileLabel"
    style="width: 270px;">

    <div class="offcanvas-header border-bottom border-secondary">

        <h5
            class="offcanvas-title fw-bold"
            id="sidebarMobileLabel">

            PLLA

        </h5>

        <button
            type="button"
            class="btn-close btn-close-white"
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


<!-- ==========================================================
     DESKTOP SIDEBAR
========================================================== -->

<aside
    id="sidebarDesktop"
    class="d-none d-lg-flex flex-column bg-dark text-white"
    style="
        width:260px;
        height:100vh;
        position:fixed;
        top:0;
        left:0;
        z-index:1030;
    ">

    <!-- Logo -->

    <div class="p-4 border-bottom border-secondary">

        <div class="d-flex align-items-center">

            <img
                src="<?= base_url('assets/images/logo.png'); ?>"
                width="45"
                height="45"
                class="rounded me-3"
                alt="PLLA">

            <div>

                <h5 class="mb-0 fw-bold">

                    PLLA

                </h5>

                <small class="text-secondary">

                    School Management

                </small>

            </div>

        </div>

    </div>


    <!-- Navigation -->

    <div
        class="flex-grow-1 overflow-auto py-3">

        <?php

        $this->load->view(
            'dashboard/layouts/sidebar_menu'
        );

        ?>

    </div>


    <!-- Footer -->

    <div class="p-3 border-top border-secondary">

        <small class="text-secondary">

            Version 1.0.0

        </small>

    </div>

</aside>