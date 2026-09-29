<?php $this->load->view('layouts/header'); ?>

<main class="auth-page">
    <div class="auth-card">
        <aside class="auth-brand-panel">
            <div class="auth-brand-lockup">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PRECIOUS LITTLE LIGHTS ACADEM <small>School Management</small></span>
            </div>
            <div class="auth-brand-copy">
                <!-- <span class="auth-eyebrow">PRECIOUS LITTLE LIGHTS ACADEMY</span> -->
                <span class="auth-eyebrow">Proverbs 22:6</span>
                <h2>Train up a child in the way he should go: and when he is old, he will not depart from it.</h2>
                <p>Sign in to continue to your school management workspace.</p>
            </div>
            <div class="auth-brand-footer">A welcoming place to learn and grow.</div>
        </aside>

        <section class="auth-form-panel">
            <div class="auth-mobile-brand">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <div class="auth-heading">
                <span class="auth-step">WELCOME BACK</span>
                <h1>Sign in to your account</h1>
                <p>Enter your school account details to continue.</p>
            </div>

            <form id="loginForm" class="auth-form">
                <div class="auth-field">
                    <label for="email">Email address</label>
                    <input type="email" name="email" id="email" class="form-control" autocomplete="username" placeholder="you@school.edu.ph" required autofocus>
                </div>
                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-password-control">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="current-password" placeholder="Enter your password" required>
                        <button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                            <svg class="auth-eye-show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.7"/></svg>
                            <svg class="auth-eye-hide" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6.1 0 9.5 6 9.5 6a16 16 0 0 1-3 3.6M6.2 6.8C3.8 8.4 2.5 12 2.5 12s3.4 6 9.5 6c1.3 0 2.5-.3 3.5-.7"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                </div>
                <div class="auth-form-meta">
                    <span class="auth-secure-note"><span aria-hidden="true">●</span> Secure school access</span>
                    <a href="<?= base_url('forgot-password'); ?>">Forgot password?</a>
                </div>
                <button id="btnLogin" type="submit" class="btn auth-submit">Sign in <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-help">Need help accessing your account? Contact your school administrator.</p>
        </section>
    </div>
    <footer class="auth-page-footer">© <?= date('Y'); ?> Precious Little Lights Academy</footer>
</main>

<?php $this->load->view('layouts/footer'); ?>
