$(document).ready(function () {

    $('#loginForm').submit(function(e){
        e.preventDefault();
        let formData = $(this).serializeArray();
        formData.push({
            name: CSRF.name,
            value: CSRF.hash
        });
        $.ajax({
            url: BASE_URL + 'auth/login',
            type:'POST',
            data: $.param(formData),
            dataType:'json',
            success:function(response){
                if(response.status)
                {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Login successfully",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function () {
                        window.location = BASE_URL + 'dashboard';
                    }, 1000);
                } 
                else if(response.remaining == '2')
                {
                    
                    const notyf = new Notyf({
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    });

                    notyf.error({
                        message: response.message + "\nRemaining attempts: " + response.remaining + 'and your account will be Lock for 5 minutes',
                        duration: 3000 // 2 seconds
                    });

                } 
                else if(response.remaining == '0')
                {
                    Swal.fire({
                        icon:'warning',
                        title:'Login Failed',
                        text:
                            "One more failed attempt will lock your account for 5 minutes."
                    });
                    return;

                } 
                else if(response.message.includes("locked"))
                {
                    Swal.fire({
                        icon: "error",
                        title: "Account Locked",
                        text: response.message
                    });
                    
                    return;
                }
                else
                {
                    const notyf = new Notyf({
                        position: {
                            x: 'right',
                            y: 'top'
                        }
                    });

                    notyf.error({
                        message: response.message + " Please Enter the correct credentials",
                        duration: 3000 // 2 seconds
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