<?php
    $this->load->view('layouts/header');
?>

    <div class="container">
        <div class="container vh-100 d-flex justify-content-center align-items-center">
            <div class="card shadow border-0 p-4" style="width:400px;">
                <div class="card-header text-center">
                    <h3>School Management</h3>
                </div>

                <div class="card-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label>Email</label>
                            <input
                                type="text"
                                name="email"
                                id="email"
                                class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
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
                <div class="text-center mt-3">
                    <a href="<?= base_url('forgot-password') ?>">
                        Forgot Password?
                    </a>
                </div>
            </div>

        </div>

    </div>

<?php
    $this->load->view('layouts/footer');
?>