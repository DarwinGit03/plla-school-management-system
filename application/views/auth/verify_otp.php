<?php
    $this->load->view('layouts/header');
?>

    <div class="container">
        <div class="container vh-100 d-flex justify-content-center align-items-center">
            <div class="card shadow border-0 p-4" style="width:400px;">

                <div class="text-center">
                    <img src="<?= base_url('assets/images/OTP.png') ?>" width="100">
                    <h3 class="mt-3 fw-bold">Account Verification</h3>
                    <small class="text-muted">Enter Verify Code Below</small>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <input type="text" class="otp-input form-control" maxlength="1">
                    <input type="text" class="otp-input form-control" maxlength="1">
                    <input type="text" class="otp-input form-control" maxlength="1">
                    <input type="text" class="otp-input form-control" maxlength="1">
                    <input type="text" class="otp-input form-control" maxlength="1">
                    <input type="text" class="otp-input form-control" maxlength="1">
                </div>
                
                <form id="verifyForm">
                    <input type="hidden" name="email" value="<?= $email ?>">
                    <input type="hidden" name="otp" id="otp">
                    <button type="submit" class="btn btn-info text-white w-100 mt-4">
                        Verify Code
                    </button>
                </form>


                <div class="text-center mt-3">
                    <button
                        type="button"
                        id="btnResendOTP"
                        class="btn btn-link text-decoration-none link-hover-underline p-0 shadow-none fw-bold"
                        
                        disabled>
                        Resend OTP
                    </button>
                    <!-- <div>
                        <small class="text-muted">
                            Resend available in
                            <span id="resendTimer">
                                120
                            </span>
                            seconds
                        </small>

                    </div> -->

                </div>
            </div>
        </div>
    </div>

<?php
    $this->load->view('layouts/footer');
?>