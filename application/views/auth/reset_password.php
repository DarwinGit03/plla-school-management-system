<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">

    <title>Reset Password</title>

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-header text-center">

                    <h4>
                        Reset Password
                    </h4>

                </div>

                <div class="card-body">

                    <form id="resetForm">

                        <input
                            type="hidden"
                            name="email"
                            value="<?= $email ?>">

                        <div
                            id="ResetErrorMessage"
                            class="alert alert-danger"
                            style="display:none;">
                        </div>

                        <div class="mb-3">

                            <label>
                                New Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                id="confirm_password"
                                class="form-control"
                                required>

                        </div>
                        <div class="mt-2">
                                <small>
                                    Password Strength:
                                </small>

                                <div
                                    id="passwordStrength"
                                    class="fw-bold">
                                    -

                                </div>

                            </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            Reset Password

                        </button>

                        <div class="mt-3">

                            <small class="fw-bold">
                                Password Requirements
                            </small>

                            <ul
                                class="list-unstyled mt-2"
                                id="passwordChecklist">

                                <li id="checkLength">
                                    ❌ At least 8 characters
                                </li>

                                <li id="checkUpper">
                                    ❌ One uppercase letter
                                </li>

                                <li id="checkLower">
                                    ❌ One lowercase letter
                                </li>

                                <li id="checkNumber">
                                    ❌ One number
                                </li>

                                <li id="checkSpecial">
                                    ❌ One special character
                                </li>

                                <li id="checkMatch">
                                    ➖ Passwords match
                                </li>

                            </ul>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const BASE_URL = "<?= base_url(); ?>";
    const CSRF = {
            name: "<?= $this->security->get_csrf_token_name(); ?>",
            hash: "<?= $this->security->get_csrf_hash(); ?>"
        }; 
</script>

<script src="<?= base_url('assets/js/auth/reset-password.js'); ?>"></script>

</body>
</html>