<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> | PLLA Parent Portal</title>
    <link rel="icon" href="<?= base_url('assets/images/logo.png'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#f3f6fc;color:#172b4d}.parent-nav{background:#122b4d}.parent-brand img{width:40px;height:40px;object-fit:cover;border-radius:12px;background:#fff}
        .portal-wrap{max-width:1180px}.soft-card{border:0;border-radius:18px;box-shadow:0 8px 26px #1d355710}.student-mark{width:48px;height:48px;border-radius:15px;background:#e8f0ff;color:#1d55be;display:grid;place-items:center;font-weight:700}
    </style>
</head>
<body>
<nav class="navbar navbar-dark parent-nav">
    <div class="container portal-wrap">
        <a class="navbar-brand parent-brand d-flex align-items-center gap-2" href="<?= site_url('parent-portal'); ?>">
            <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA">
            <span><strong>PLLA</strong><small class="d-block text-white-50">Parent Portal</small></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <a class="text-white text-decoration-none" href="<?= site_url('parent-portal'); ?>">My children</a>
            <a class="text-white text-decoration-none" href="<?= site_url('parent-portal/payments'); ?>">Payments</a>
            <form method="post" action="<?= site_url('parent-portal/logout'); ?>" class="m-0">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <button class="btn btn-sm btn-outline-light" type="submit">Sign out</button>
            </form>
        </div>
    </div>
</nav>
<main class="container portal-wrap py-4 py-md-5">
    <div class="mb-4"><div class="small text-primary fw-semibold">PARENT PORTAL</div><h1 class="h3 fw-bold mb-1"><?= html_escape($title); ?></h1><p class="text-secondary mb-0">Welcome, <?= html_escape($parent_name); ?>.</p></div>
    <?php $this->load->view($content); ?>
</main>
<footer class="border-top bg-white py-3"><div class="container portal-wrap small text-secondary">&copy; <?= date('Y'); ?> PLLA School Management System</div></footer>
</body>
</html>
