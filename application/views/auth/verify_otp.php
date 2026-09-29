<?php $this->load->view('layouts/header'); ?>

<main class="auth-page">
    <div class="auth-card auth-card-compact">
        <aside class="auth-brand-panel">
            <div class="auth-brand-lockup">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <div class="auth-brand-copy">
                <span class="auth-eyebrow">A QUICK SECURITY CHECK</span>
                <h2>One code. One more step.</h2>
                <p>Use the six-digit code we sent to your email to confirm it’s really you.</p>
            </div>
            <div class="auth-brand-footer">Never share your verification code with anyone.</div>
        </aside>

        <section class="auth-form-panel">
            <div class="auth-mobile-brand">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <a class="auth-back-link" href="<?= base_url('forgot-password'); ?>"><span aria-hidden="true">←</span> Start over</a>
            <div class="auth-heading">
                <span class="auth-step">VERIFY YOUR EMAIL</span>
                <h1>Enter your code</h1>
                <p>We sent a 6-digit code to <strong><?= html_escape($email); ?></strong>.</p>
            </div>

            <form id="verifyForm" class="auth-form">
                <input type="hidden" name="email" value="<?= html_escape($email); ?>">
                <input type="hidden" name="otp" id="otp">
                <div class="otp-entry" role="group" aria-label="Six-digit verification code">
                    <?php for ($digit = 1; $digit <= 6; $digit++): ?>
                        <input type="text" class="otp-input form-control" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="<?= $digit === 1 ? 'one-time-code' : 'off'; ?>" aria-label="Digit <?= $digit; ?>" <?= $digit === 1 ? 'autofocus' : ''; ?>>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn auth-submit">Verify code <span aria-hidden="true">→</span></button>
            </form>
            <div class="auth-resend-row"><span>Didn’t receive the email?</span> <button type="button" id="btnResendOTP" class="auth-text-button" disabled>Resend code</button></div>
            <p class="auth-caption">For your security, the code expires after 5 minutes.</p>
        </section>
    </div>
    <footer class="auth-page-footer">© <?= date('Y'); ?> Precious Little Lights Academy</footer>
</main>

<?php $this->load->view('layouts/footer'); ?>
