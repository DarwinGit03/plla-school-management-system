$(document).ready(function(){

    
    let inactivityTimer;
    // function resetInactivityTimer()
    // {
    //     clearTimeout(inactivityTimer);

    //     inactivityTimer = setTimeout(function(){
    //         Swal.fire({
    //             icon: 'warning',
    //             title: 'Session Expired',
    //             text: 'No activity detected for 5 minutes.',
    //             allowOutsideClick: false
    //         }).then(() => {
    //             window.location =
    //                 BASE_URL + 'auth/logout';
    //         });
    //     // }, 300000); // 5 minutes
    //     }, 10000);
    // }

    // $(document).on('mousemove keypress click scroll',function(){
    //         resetInactivityTimer();
    //     }
    // );

    // resetInactivityTimer();
    //

    function checkPasswordMatch()
    {
        let password = $("#password").val();
        let confirm = $("#confirm_password").val();
        if(confirm === "")
        {
            $("#checkMatch").html("➖ Passwords match");
            return;
        }

        if(password === confirm)
        {
            $("#checkMatch").html("✅ Passwords match");
        }
        else
        {
            $("#checkMatch").html("❌ Passwords match");
        }
    }

    $("#confirm_password").on("keyup",function(){
            checkPasswordMatch();
        }
    );

     $("#password").on("keyup", function(){
            let password = $(this).val();
            let score = 0;
            
            if(password.length >= 8){
                $("#checkLength").html("✅ At least 8 characters");
                score++;
            } else {
                $("#checkLength").html("❌ At least 8 characters");
            }


            if(/[A-Z]/.test(password)) {
                $("#checkUpper").html("✅ One uppercase letter");
                score++;
            } else {
                $("#checkUpper").html("❌ One uppercase letter");
            }

            if(/[a-z]/.test(password)) {
                $("#checkLower").html("✅ One lowercase letter");
                score++;
            } else {
                $("#checkLower").html("❌ One lowercase letter");
            }

            if(/[0-9]/.test(password)) {
                $("#checkNumber").html("✅ One number");
                score++;
            } else {
                $("#checkNumber").html("❌ One number");
            }

            if(/[!@#$%^&*()_\-+=<>?{}[\]~]/.test(password))
            {
                $("#checkSpecial").html("✅ One special character");
                score++;
            } else {
                $("#checkSpecial").html("❌ One special character");
            }

            let text = "-";

            if(score <= 1)
            {
                text = "🔴 Weak";
            }
            else if(score <= 3)
            {
                text = "🟡 Medium";
            }
            else if(score == 4)
            {
                text = "🟢 Strong";
            }
            else if(score == 5)
            {
                text = "🟢🟢 Very Strong";
            }

            $("#passwordStrength").html(text);

        }
    );

    $("#resetForm").submit(function(e){
        e.preventDefault();

        $("#passwordError").hide().html("");
        let password =$("#password").val();
        let confirm =$("#confirm_password").val();
        let errors = [];

        if(password !== confirm)
        {
            $("#ResetErrorMessage").html("Passwords do not match.").show();
        }

        if(
            password.length < 8 ||
            !/[A-Z]/.test(password) ||
            !/[a-z]/.test(password) ||
            !/[0-9]/.test(password) ||
            !/[!@#$%^&*()_\-+=<>?{}[\]~]/.test(password)
        )
        {
            $("#ResetErrorMessage").html("Please satisfy all password requirements.").show();

            return;
        }


        let formData = $(this).serializeArray();
        formData.push({
            name: CSRF.name,
            value: CSRF.hash
        });
        $.ajax({
            url: BASE_URL + "auth/save_password",
            type:"POST",
            data: $.param(formData),
            dataType:"json",
            success:function(response)
            {
                if(response.status)
                {
                    Swal.fire({
                        icon:'success',
                        title:'Success',
                        text:response.message

                    }).then(function(){

                        window.location =
                            BASE_URL +
                            'login';

                    });
                }
                else
                {
                    Swal.fire({
                        icon:'error',
                        title:'Error',
                        text:response.message

                    });
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