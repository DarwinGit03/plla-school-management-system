$(document).ready(function () {
    const forgotForm = $('#forgotForm');
    const submitButton = forgotForm.find('button[type="submit"]');
    let requestInProgress = false;
    let redirectingToOtp = false;

    forgotForm.on('submit', function (event) {
        event.preventDefault();

        if (requestInProgress || !this.checkValidity()) {
            return;
        }

        requestInProgress = true;
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Sending code...');

        const formData = $(this).serializeArray();
        formData.push({ name: CSRF.name, value: CSRF.hash });

        $.ajax({
            url: BASE_URL + 'auth/send_otp',
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    redirectingToOtp = true;
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP sent',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function () {
                        window.location = BASE_URL + 'verify-otp-page';
                    });
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Unable to send code',
                    text: response.message || 'Please try again.'
                });
            },
            error: function (xhr, status, error) {
                console.error('Forgot password request failed:', status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Unable to send code',
                    text: 'We could not process the request. Please try again.'
                });
            },
            complete: function () {
                if (!redirectingToOtp) {
                    requestInProgress = false;
                    submitButton.prop('disabled', false).text('Send verification code');
                }
            }
        });
    });
});
