document.addEventListener("DOMContentLoaded", function () {

    const loader = document.getElementById("pageLoader");

    document.querySelectorAll("a").forEach(function (link) {

        const href = link.getAttribute("href");

        if (
            href &&
            href !== "#" &&
            !href.startsWith("javascript:")
        ) {
            link.addEventListener("click", function () {

                loader.classList.remove("d-none");
                loader.classList.add("d-flex");

            });
        }

    });


    document.querySelectorAll('#sidebarMobile .nav-link')
    .forEach(function(link){

        link.addEventListener('click',function(){

            let sidebar =
                bootstrap.Offcanvas.getInstance(
                    document.getElementById('sidebarMobile')
                );

            if(sidebar){

                sidebar.hide();

            }

        });

    });
});
