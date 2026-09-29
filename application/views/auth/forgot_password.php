<?php $this->load->view('layouts/header'); ?>

<main class="auth-page">
    <div class="auth-card auth-card-compact">
        <aside class="auth-brand-panel">
            <div class="auth-brand-lockup">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <div class="auth-brand-copy">
                <span class="auth-eyebrow">ACCOUNT SECURITY</span>
                <h2>Get back to what matters.</h2>
                <p>We’ll send a one-time verification code to the email address linked to your account.</p>
            </div>
            <div class="auth-brand-footer">Your account stays protected at every step.</div>
        </aside>

        <section class="auth-form-panel">
            <div class="auth-mobile-brand">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <a class="auth-back-link" href="<?= base_url('login'); ?>"><span aria-hidden="true">←</span> Back to sign in</a>
            <div class="auth-heading">
                <span class="auth-step">PASSWORD RECOVERY</span>
                <h1>Forgot your password?</h1>
                <p>Enter your account email and we’ll send you a one-time code.</p>
            </div>

            <form id="forgotForm" class="auth-form">
                <div class="auth-field">
                    <label for="recovery-email">Email address</label>
                    <input type="email" name="email" id="recovery-email" class="form-control" autocomplete="email" placeholder="you@school.edu.ph" required autofocus>
                </div>
                <button type="submit" class="btn auth-submit">Send verification code <span aria-hidden="true">→</span></button>
            </form>
            <div class="auth-info-note"><span aria-hidden="true">i</span><p>The verification code expires after 5 minutes. Check your inbox and spam folder.</p></div>
        </section>
    </div>
    <footer class="auth-page-footer">© <?= date('Y'); ?> Precious Little Lights Academy</footer>
</main>

<?php $this->load->view('layouts/footer'); ?>
