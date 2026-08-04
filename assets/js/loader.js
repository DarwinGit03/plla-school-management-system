$(window).on("load", function(){

    $("#pageLoader").fadeOut(300);

});

$("a").on("click", function(e){

    let href = $(this).attr("href");

    if(
        href &&
        href !== "#" &&
        !$(this).attr("target")
    ){
        $("#pageLoader").fadeIn(150);
    }

});

$("form").on("submit", function(){

    $("#pageLoader").fadeIn(150);

});