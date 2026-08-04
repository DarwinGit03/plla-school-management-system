$(document).ready(function(){
    
    function showOTPError()
    {
        $('.otp-input').addClass('otp-error otp-invalid');
        setTimeout(function(){
            $('.otp-input').removeClass('otp-error');

        },500);
        setTimeout(function(){
            $('.otp-input').removeClass('otp-invalid');
            $('.otp-input').first().focus();
        },1000);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Paste Entire OTP, and automatic add the numbers
    */

    $('.otp-input').on('paste', function(e){
        // Prevent default browser paste behavior
        e.preventDefault();

        // Get clipboard text
        let paste =
            (e.originalEvent || e)
            .clipboardData
            .getData('text');

        // Remove non-numeric characters
        paste = paste.replace(/\D/g, '');

        // Validate OTP length
        if(paste.length !== 6)
        {
            return;
        }

        // Populate OTP boxes
        $('.otp-input').each(function(index){

            $(this).val(
                paste[index]
            );

        });

        // Build complete OTP value
        buildOTP();
    });


    $('.otp-input').on('input', function(){
        if($(this).val().length === 1)
        {
            $(this).next('.otp-input').focus();
        }

        buildOTP();
    });

    $('.otp-input').on('keydown', function(e){
        if(e.key === 'Backspace' && $(this).val() === '')
        {
            $(this).prev('.otp-input').focus();
        }
    });


    function buildOTP()
    {
        let otp = '';
        $('.otp-input').each(function(){
            otp += $(this).val();
        });
        $('#otp').val(otp);

        if(otp.length === 6){
            setTimeout(function(){
                $('#verifyForm').submit();
            },300);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Timer
    */

    // let seconds = 120;
    // let countdown = 
    //     setInterval(function(){
    //         seconds--;
    //         $('#timer').text(seconds);
    //         if(seconds <= 0)
    //         {
    //             clearInterval(countdown);
    //             $('#btn_resend').removeClass('disabled');
    //         }

    //     },1000);
    
    /*
    |--------------------------------------------------------------------------
    | Submit verify
    */

    $("#btn_resend").submit(function(e){

    });

    $("#verifyForm").submit(function(e){
        e.preventDefault();

        let formData = $(this).serializeArray();
        formData.push({
            name: CSRF.name,
            value: CSRF.hash
        });
        $.ajax({
            url: BASE_URL + "auth/verify_otp",
            type:"POST",
            // data: $(this).serialize(),
            data: $.param(formData),
            dataType:"json",
            success:function(response){
                if(response.status)
                {
                    const notyf = new Notyf({
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    });

                    notyf.error({
                        message: response.message,
                        duration: 3000 // 2 seconds
                    });
                    
                    setTimeout(function () {
                        window.location = BASE_URL + 'dashboard';
                    }, 1000);
                }
                else
                {
                    showOTPError();
                    const notyf = new Notyf({
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    });
                    notyf.error(response.message);

                }
            },
            error: function(xhr, status, error)
            {
                console.log(xhr.responseText);

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseText
                });
            },

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Resend OTP Countdown
    |--------------------------------------------------------------------------
    */

    startResendTimer();

    function startResendTimer() {
        let seconds = 5;

        $('#btnResendOTP').prop('disabled', true);
        let timer = setInterval(function () {
            $('#btnResendOTP').text(
                'Resend OTP (' + seconds + 's)'
            );

            seconds--;
            if (seconds < 0) {
                clearInterval(timer);
                $('#btnResendOTP')
                    .prop('disabled', false)
                    .text('Resend OTP');
            }
        }, 1000);

    }
    /*
    |--------------------------------------------------------------------------
    | Resend OTP Button
    |--------------------------------------------------------------------------
    */

    $('#btnResendOTP').click(function(){
        let formData = $(this).serializeArray();
        formData.push({
            name: CSRF.name,
            value: CSRF.hash
        });
        $.ajax({

            url:
                BASE_URL +
                'auth/resend_otp',

            type:
                'POST',
            data: $.param(formData),

            dataType:
                'json',

            beforeSend:function(){

                $('#btnResendOTP')
                    .prop(
                        'disabled',
                        true
                    );
            },

            success: function (response) {
                if (response.status) {
                    const notyf = new Notyf({
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    });

                    notyf.success({
                        message: response.message,
                        duration: 2000 // 2 seconds
                    });
                    // Swal.fire({
                    //     icon: 'success',
                    //     title: 'OTP Sent',
                    //     text: response.message
                    // });
                    startResendTimer();

                } else {
                    const notyf = new Notyf({
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    });

                    notyf.error({
                        message: response.message,
                        duration: 2000 // 2 seconds
                    });
                    
                    // Swal.fire({
                    //     icon: 'error',
                    //     title: 'Error',
                    //     text: response.message

                    // });

                    $('#btnResendOTP')
                        .prop('disabled', false)
                        .text('Resend OTP');
                }

            },
            error: function(xhr, status, error)
            {
                console.log(xhr.responseText);

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseText
                });
            },

        });

    });

});