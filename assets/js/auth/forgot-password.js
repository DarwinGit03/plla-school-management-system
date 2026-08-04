$(document).ready(function(){

    $("#forgotForm").submit(function(e){
        e.preventDefault();
        
        let formData = $(this).serializeArray();
        formData.push({
            name: CSRF.name,
            value: CSRF.hash
        });

        $.ajax({
            // url: BASE_URL + "auth/send_otp",
            url: BASE_URL + "auth/send_otp",
            type:"POST",
            // data: $(this).serialize(),
            data: $.param(formData),
            dataType:"json",
            // success:function(res){
            //     if(res.status)
            //     {
            //         Swal.fire(
            //             "Success",
            //             res.message,
            //             "success"
            //         ).then(() => {

            //             window.location =
            //                 BASE_URL +
            //                 "verify-otp";

            //         });
            //     }
            //     else
            //     {
            //         Swal.fire(
            //             "Error",
            //             res.message,
            //             "error"
            //         );
            //     }
            // },
            success:function(response)
            {
                if(response.status)
                {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title:'Verified',
                        text:response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function () {
                        window.location = BASE_URL + 'verify-otp-page';
                    }, 1000);
                }
                else
                {
                    Swal.fire({

                        icon:'error',

                        title:'Invalid OTP',

                        text:
                            response.message

                    });

                    if(response.remaining !== undefined)
                    {
                        $('#attempts')
                            .text(
                                response.remaining
                            );
                    }

                    shakeOTP();
                }
            },
            // error: function () {

            //     Swal.fire({
            //         icon: "error",
            //         title: "Error",
            //         text: "Server error occurred."
            //     });

            // },
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