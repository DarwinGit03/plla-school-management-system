<!doctype html>
<html>
<head>

    <title>Login</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-4">

            <div class="card">

                <div class="card-header text-center">
                    <h3>School Management</h3>
                </div>

                <div class="card-body">

                    <form id="loginForm">

                        <div class="mb-3">
                            <label>Username / Email</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control">
                        </div>

                        <button
                            id="btnLogin"
                            type="submit"
                            class="btn btn-primary w-100">
                            Login
                        </button>
                        
                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const BASE_URL = "<?= base_url(); ?>";
    //for test
    const CSRF = { //use reusable for future
        name: "<?= $this->security->get_csrf_token_name(); ?>",
        hash: "<?= $this->security->get_csrf_hash(); ?>"
    };
</script>
<script src="<?= base_url('assets/js/auth/login.js'); ?>"></script>
</html>