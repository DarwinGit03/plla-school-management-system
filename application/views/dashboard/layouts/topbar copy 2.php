<nav class="navbar navbar-expand-lg bg-white shadow-sm px-4">

    <div class="container-fluid">

        <button
            id="sidebarToggle"
            class="btn btn-outline-secondary me-3">

            <i class="fas fa-bars"></i>

        </button>

        <form class="d-none d-md-flex w-50">

            <input
                class="form-control"
                type="search"
                placeholder="Search students, faculty, subjects...">

        </form>

        <div class="ms-auto d-flex align-items-center">

            <button
                class="btn btn-light position-relative me-2">

                <i class="fas fa-bell"></i>

                <span
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                    3

                </span>

            </button>

            <button
                class="btn btn-light position-relative me-3">

                <i class="fas fa-envelope"></i>

                <span
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">

                    5

                </span>

            </button>

            <div class="dropdown">

                <a
                    href="#"
                    class="d-flex align-items-center text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown">

                    <img
                        src="<?= base_url('assets/images/avatar.png'); ?>"
                        width="42"
                        height="42"
                        class="rounded-circle border">

                    <div class="ms-2 text-start">

                        <strong>

                            <?= $current_user->first_name ?? 'Administrator'; ?>

                        </strong>

                        <br>

                        <small class="text-muted">

                            Administrator

                        </small>

                    </div>

                </a>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>

                        <a class="dropdown-item" href="#">

                            My Profile

                        </a>

                    </li>

                    <li>

                        <a class="dropdown-item" href="#">

                            Account Settings

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

    </div>

</nav>  