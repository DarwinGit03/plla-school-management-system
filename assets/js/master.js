document.addEventListener('DOMContentLoaded', function () {

    function handleSidebarResize() {

        if (window.innerWidth < 992) {

            return;

        }


        const sidebar =
            document.getElementById('sidebarMobile');


        if (!sidebar) {

            return;

        }


        const instance =
            bootstrap.Offcanvas.getInstance(sidebar);


        if (instance) {

            instance.hide();

        }


        /*
        |------------------------------------------------------
        | Safety cleanup
        |------------------------------------------------------
        */

        document
            .querySelectorAll('.offcanvas-backdrop')
            .forEach(function (backdrop) {

                backdrop.remove();

            });


        document.body.classList.remove(
            'offcanvas-backdrop'
        );

        document.body.classList.remove(
            'offcanvas-open'
        );

        document.body.style.removeProperty(
            'overflow'
        );

        document.body.style.removeProperty(
            'padding-right'
        );

    }


    window.addEventListener(
        'resize',
        handleSidebarResize
    );


    /*
    |----------------------------------------------------------
    | Initial check
    |----------------------------------------------------------
    */

    handleSidebarResize();

});
