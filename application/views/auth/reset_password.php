<?php $this->load->view('layouts/header'); ?>

<main class="auth-page">
    <div class="auth-card auth-card-compact">
        <aside class="auth-brand-panel">
            <div class="auth-brand-lockup">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <div class="auth-brand-copy">
                <span class="auth-eyebrow">ALMOST THERE</span>
                <h2>Choose a password that’s yours alone.</h2>
                <p>Create a strong password to secure your school account.</p>
            </div>
            <div class="auth-brand-footer">A strong password helps keep your account safe.</div>
        </aside>

        <section class="auth-form-panel">
            <div class="auth-mobile-brand">
                <img src="<?= base_url('assets/images/logo.png'); ?>" alt="PLLA logo">
                <span>PLLA <small>School Management</small></span>
            </div>
            <div class="auth-heading">
                <span class="auth-step">NEW PASSWORD</span>
                <h1>Reset your password</h1>
                <p>Set a new password for <strong><?= html_escape($email); ?></strong>.</p>
            </div>

            <form id="resetForm" class="auth-form">
                <input type="hidden" name="email" value="<?= html_escape($email); ?>">
                <div id="ResetErrorMessage" class="alert alert-danger auth-error" role="alert" style="display:none;"></div>
                <div class="auth-field">
                    <label for="password">New password</label>
                    <div class="auth-password-control">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" placeholder="Create a new password" required>
                        <button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                            <svg class="auth-eye-show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.7"/></svg>
                            <svg class="auth-eye-hide" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6.1 0 9.5 6 9.5 6a16 16 0 0 1-3 3.6M6.2 6.8C3.8 8.4 2.5 12 2.5 12s3.4 6 9.5 6c1.3 0 2.5-.3 3.5-.7"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                </div>
                <div class="auth-field">
                    <label for="confirm_password">Confirm new password</label>
                    <div class="auth-password-control">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" autocomplete="new-password" placeholder="Enter it again" required>
                        <button type="button" class="auth-password-toggle" data-password-toggle="confirm_password" aria-label="Show confirm password" aria-pressed="false">
                            <svg class="auth-eye-show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.7"/></svg>
                            <svg class="auth-eye-hide" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6.1 0 9.5 6 9.5 6a16 16 0 0 1-3 3.6M6.2 6.8C3.8 8.4 2.5 12 2.5 12s3.4 6 9.5 6c1.3 0 2.5-.3 3.5-.7"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                </div>
                <div class="auth-password-meter">
                    <div><span>Password strength</span><strong id="passwordStrength">—</strong></div>
                </div>
                <div class="auth-requirements">
                    <h2>Password requirements</h2>
                    <ul id="passwordChecklist">
                        <li id="checkLength">At least 8 characters</li>
                        <li id="checkUpper">One uppercase letter</li>
                        <li id="checkLower">One lowercase letter</li>
                        <li id="checkNumber">One number</li>
                        <li id="checkSpecial">One special character</li>
                        <li id="checkMatch">Passwords match</li>
                    </ul>
                </div>
                <button type="submit" class="btn auth-submit">Save new password <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </div>
    <footer class="auth-page-footer">© <?= date('Y'); ?> Precious Little Lights Academy</footer>
</main>

<?php $this->load->view('layouts/footer'); ?>
