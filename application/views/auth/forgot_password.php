<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">

    <title>Forgot Password</title>

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link href="assets/css/auth/forgot-password.css"
        rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container">
        <div class="container vh-100 d-flex justify-content-center align-items-center">
            <div class="card shadow border-0 p-4" style="width:400px;">
                <div class="text-center">
                    <img src="<?= base_url('assets/images/forgot-password.png') ?>" width="150">
                </div>
                <h1>Forgot Password</h1>
                <p>
                    Enter your e-mail address, and we'll give you an OTP
                </p>

                <form id="forgotForm">
                    <div class="mb-3">
                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="envelope">M</i>

                            </span>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter E-mail Address"
                                required>

                        </div>

                    </div>
                    <button
                        type="submit"
                        class="btn btn-primary w-100">
                        Send OTP
                    </button>
                </form>

                <div
                    class="text-center mt-3">

                    <a href="<?= base_url('login')?>">

                        Back to Login

                    </a>

                </div>
        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_URL ="<?= base_url(); ?>";
    
    const CSRF = { //use reusable for future
        name: "<?= $this->security->get_csrf_token_name(); ?>",
        hash: "<?= $this->security->get_csrf_hash(); ?>"
    };
</script>

<script src="<?= base_url('assets/js/auth/forgot-password.js'); ?>"></script>

</body>
</html>