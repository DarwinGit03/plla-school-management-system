<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-3">

    <!-- Mobile Toggle -->

    <!-- <button
        class="btn btn-outline-primary d-lg-none me-3"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMobile">

        <i class="fas fa-bars"></i>

    </button> -->
    <button
        type="button"
        class="btn btn-outline-primary d-lg-none"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarMobile"
        aria-controls="sidebarMobile">

        <i class="fas fa-bars"></i>

    </button>

    <!-- Title -->

    <div>

        <!-- <h5 class="mb-0 fw-bold">

            <?= $page_title ?? 'Dashboard'; ?>

        </h5>

        <small class="text-muted">

            <?= $page_subtitle ?? ''; ?>

        </small> -->

        <?php if (!empty($breadcrumb)): ?>

            <nav aria-label="breadcrumb">

                <ol class="breadcrumb mb-0">

                    <li class="breadcrumb-item">

                        <a href="<?= site_url('dashboard'); ?>">

                            Home

                        </a>

                    </li>

                    <?php foreach ($breadcrumb as $item): ?>

                        <li class="breadcrumb-item active">

                            <?= $item; ?>

                        </li>

                    <?php endforeach; ?>

                </ol>

            </nav>

        <?php endif; ?>

    </div>

    <!-- Right Side -->

    <div class="ms-auto d-flex align-items-center">

        <!-- Search -->

        <!-- <div class="d-none d-lg-block me-3">

            <input
                class="form-control"
                type="search"
                placeholder="Search...">

        </div> -->

        <!-- Notification -->

        <!-- <button
            class="btn btn-light position-relative me-3">

            <i class="fas fa-bell"></i>

            <span
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                3

            </span>

        </button> -->

        <!-- User -->

        <div class="dropdown">

            <a
                href="#"
                class="d-flex align-items-center text-decoration-none dropdown-toggle"
                data-bs-toggle="dropdown">

                <img
                    src="<?= base_url('assets/images/avatar-default.png'); ?>"
                    width="38"
                    height="38"
                    class="rounded-circle border me-2">

                <span class="fw-semibold">

                    <?= $current_user->first_name ?? 'Admin'; ?>
                    <i><?= $current_user->employee_no ?? '#'; ?></i>

                </span>

            </a>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>

                    <a class="dropdown-item" href="#">

                        Profile

                    </a>

                </li>

                <li>

                    <a class="dropdown-item" href="#">

                        Settings

                    </a>

                </li>

                <li><hr class="dropdown-divider"></li>

                <li>

                    <a
                        class="dropdown-item text-danger"
                        href="<?= site_url('auth/logout'); ?>">

                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>