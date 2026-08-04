<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">

    <title>Verify OTP</title>

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
                        Verify OTP
                    </h4>

                </div>

                <div class="card-body">

                    <form id="verifyForm">

                        <input
                            type="hidden"
                            name="email"
                            value="<?= $email ?>">

                        <div class="mb-3">

                            <label>
                                Enter OTP
                            </label>

                            <input
                                type="text"
                                name="otp"
                                class="form-control"
                                maxlength="6"
                                required>

                        </div>

                        <button
                            class="btn btn-primary w-100">

                            Verify OTP

                        </button>

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

<script src="<?= base_url('assets/js/auth/verify-otp.js'); ?>"></script>

</body>
</html>