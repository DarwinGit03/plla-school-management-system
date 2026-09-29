$(document).ready(function () {
    const passwordInput = $('#password');
    const confirmInput = $('#confirm_password');
    const submitButton = $('#resetForm button[type="submit"]');

    $(document).on('click', '[data-password-toggle]', function () {
        const input = document.getElementById($(this).data('password-toggle'));
        if (!input) return;

        const showPassword = input.type === 'password';
        const fieldLabel = input.id === 'confirm_password' ? 'confirm password' : 'password';
        input.type = showPassword ? 'text' : 'password';
        $(this)
            .toggleClass('is-visible', showPassword)
            .attr('aria-pressed', showPassword ? 'true' : 'false')
            .attr('aria-label', (showPassword ? 'Hide ' : 'Show ') + fieldLabel);
    });

    function updateRequirement(id, passed, label, showState) {
        const item = $('#' + id);
        item.text(label);
        item.toggleClass('is-valid', passed);
        item.toggleClass('is-invalid', showState && !passed);
    }

    function updatePasswordFeedback() {
        const password = passwordInput.val();
        const confirmation = confirmInput.val();
        const checks = [
            { id: 'checkLength', label: 'At least 8 characters', passed: password.length >= 8 },
            { id: 'checkUpper', label: 'One uppercase letter', passed: /[A-Z]/.test(password) },
            { id: 'checkLower', label: 'One lowercase letter', passed: /[a-z]/.test(password) },
            { id: 'checkNumber', label: 'One number', passed: /[0-9]/.test(password) },
            { id: 'checkSpecial', label: 'One special character', passed: /[!@#$%^&*()_\-+=<>?{}\[\]~]/.test(password) }
        ];

        let score = 0;
        checks.forEach(function (check) {
            updateRequirement(check.id, check.passed, check.label, password.length > 0);
            if (check.passed) score++;
        });

        const strength = score <= 1 ? 'Weak' : score <= 3 ? 'Fair' : score === 4 ? 'Strong' : 'Very strong';
        $('#passwordStrength').text(password.length ? strength : '—');
        $('#passwordStrength').removeClass('strength-weak strength-fair strength-strong strength-very-strong');
        if (password.length) {
            $('#passwordStrength').addClass('strength-' + strength.toLowerCase().replace(' ', '-'));
        }

        const hasConfirmation = confirmation.length > 0;
        updateRequirement('checkMatch', hasConfirmation && password === confirmation, 'Passwords match', hasConfirmation);
    }

    passwordInput.on('input', updatePasswordFeedback);
    confirmInput.on('input', updatePasswordFeedback);

    $('#resetForm').on('submit', function (event) {
        event.preventDefault();

        const password = passwordInput.val();
        const confirmation = confirmInput.val();
        const validPassword = password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password) && /[!@#$%^&*()_\-+=<>?{}\[\]~]/.test(password);
        const errorBox = $('#ResetErrorMessage');

        if (password !== confirmation) {
            errorBox.text('Passwords do not match.').show();
            return;
        }
        if (!validPassword) {
            errorBox.text('Please satisfy all password requirements.').show();
            return;
        }

        errorBox.hide().text('');
        const formData = $(this).serializeArray();
        formData.push({ name: CSRF.name, value: CSRF.hash });

        $.ajax({
            url: BASE_URL + 'auth/save_password',
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            beforeSend: function () {
                submitButton.prop('disabled', true).text('Saving password…');
            },
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password updated',
                        text: response.message,
                        confirmButtonColor: '#2563eb'
                    }).then(function () {
                        window.location = BASE_URL + 'login';
                    });
                    return;
                }

                Swal.fire({ icon: 'error', title: 'Unable to update password', text: response.message });
            },
            error: function (xhr, status, error) {
                console.error('Password reset request failed:', status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Unable to update password',
                    text: 'Please try again. If the problem continues, contact your school administrator.'
                });
            },
            complete: function () {
                submitButton.prop('disabled', false).html('Save new password <span aria-hidden="true">→</span>');
            }
        });
    });
});
