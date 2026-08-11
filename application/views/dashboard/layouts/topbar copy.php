<nav class="topbar">

    <div class="topbar-left">

        <button
            id="sidebarToggle"
            class="btn-toggle"
        >
            <i class="fas fa-bars"></i>
        </button>

        <h4 class="page-title">

            Administrator Dashboard

        </h4>

    </div>

    <div class="topbar-center">

        <div class="search-box">

            <i class="fas fa-search"></i>

            <input
                type="text"
                placeholder="Search..."
            >

        </div>

    </div>

    <div class="topbar-right">

        <button class="icon-btn">

            <i class="far fa-bell"></i>

            <span class="badge">3</span>

        </button>

        <button class="icon-btn">

            <i class="far fa-envelope"></i>

            <span class="badge">5</span>

        </button>

        <div class="profile-menu">

            <img
                src="<?= base_url('assets/images/default-avatar.png'); ?>"
                class="avatar"
            >

            <div class="profile-info">

                <span class="name">

                    <?= $this->session->userdata('email'); ?>

                </span>

                <small>

                    Administrator

                </small>

            </div>

            <i class="fas fa-chevron-down"></i>

        </div>

    </div>

</nav>