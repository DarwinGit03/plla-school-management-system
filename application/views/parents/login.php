<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> | PLLA</title>
    <link rel="icon" href="<?= base_url('assets/images/logo.png'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{min-height:100vh;background:linear-gradient(135deg,#eaf1ff,#f7f9fc);display:grid;place-items:center;color:#172b4d}
        .parent-login{width:min(460px,calc(100% - 28px));border:0;border-radius:22px;box-shadow:0 20px 60px #12315b20;overflow:hidden}
        .brand{background:#122b4d;color:#fff;padding:28px 32px;display:flex;align-items:center;gap:14px}
        .brand img{width:54px;height:54px;object-fit:cover;border-radius:14px;background:#fff}
        .form-control{min-height:48px}.btn-primary{background:#2463eb;border-color:#2463eb;min-height:48px;font-weight:600}
    </style>
</head>
<body>
    <main class="card parent-login">
        <header class="brand">
            <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
            <div><strong>PLLA Parent Portal</strong><div class="small text-white-50">Precious Little Lights Academy</div></div>
        </header>
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 fw-bold mb-2"><?= $pending_email ? 'Check your email' : 'Sign in to your parent account'; ?></h1>
            <p class="text-secondary mb-4"><?= $pending_email ? 'Enter the six-digit code sent to ' . html_escape($pending_email) . '. It expires in five minutes.' : 'Use the email address recorded with your child’s guardian information. We’ll email you a one-time code.'; ?></p>
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= html_escape($message_type); ?>" role="status"><?= html_escape($message); ?></div>
            <?php endif; ?>
            <?php if ($pending_email): ?>
                <form method="post" action="<?= site_url('parent-portal/verify-code'); ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <label class="form-label fw-semibold" for="code">Email verification code</label>
                    <input class="form-control mb-3" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
                    <button class="btn btn-primary w-100" type="submit">Verify and continue</button>
                </form>
                <form method="post" action="<?= site_url('parent-portal/request-code'); ?>" class="mt-3">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="email" value="<?= html_escape($pending_email); ?>">
                    <button class="btn btn-link p-0 text-decoration-none" type="submit">Send another code</button>
                    <span class="text-secondary small"> · </span>
                    <a class="small text-decoration-none" href="<?= site_url('parent-portal/login'); ?>?change=1">Use a different email</a>
                </form>
            <?php else: ?>
                <form method="post" action="<?= site_url('parent-portal/request-code'); ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <label class="form-label fw-semibold" for="email">Parent or guardian email</label>
                    <input class="form-control mb-3" type="email" id="email" name="email" autocomplete="email" required autofocus>
                    <button class="btn btn-primary w-100" type="submit">Email me a sign-in code</button>
                </form>
            <?php endif; ?>
            <p class="small text-secondary mt-4 mb-0">The code is valid for five minutes. If your email is not linked to your child’s record, please contact the school office.</p>
            
        </div>
    </main>
</body>
</html>
