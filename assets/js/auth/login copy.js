$(document).ready(function () {

    $("#loginForm").submit(function (e) {
        e.preventDefault();
        
        let formData = $(this).serializeArray();
        formData.push({
            name: CSRF.name,
            value: CSRF.hash
        });
        $.ajax({
            url: BASE_URL + "auth/login",
            type: "POST",
            // data: $(this).serialize(), 
            data: $.param(formData),
            dataType: "json",
            beforeSend: function () {
                $("#btnLogin")
                    .prop("disabled", true)
                    .html("Please wait...");
            },
            success: function (res) {
                if (res.status) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: res.message
                    }).then(() => {
                        window.location = res.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: res.message
                    });
                }
            },

            error: function () {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Server error occurred."
                });

            },
            // error: function(xhr, status, error)
            // {
            //     console.log(xhr.responseText);

            //     Swal.fire({
            //         icon: "error",
            //         title: "Error",
            //         text: xhr.responseText
            //     });
            // },
            complete: function () {
                $("#btnLogin")
                    .prop("disabled", false)
                    .html("Login");
            }
        });
    });
});