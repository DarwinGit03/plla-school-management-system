let timeout = 30 * 60 * 1000;

let warning = 28 * 60 * 1000;

setTimeout(function(){

    Swal.fire({

        icon:'warning',

        title:'Session Expiring',

        text:'You will be logged out in 2 minutes.',

        confirmButtonText:'Stay Logged In'

    });

}, warning);

setTimeout(function(){
    window.location =
        BASE_URL + "logout";
}, timeout);



setInterval(function () {
    $.get(
        BASE_URL + "keep-alive"
    );

}, 60000);