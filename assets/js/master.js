document.addEventListener('DOMContentLoaded', function () {

    // Keep the mobile Bootstrap offcanvas open when a user expands a menu group.
    // Close it only when they choose a real navigation destination.
    const mobileSidebar = document.getElementById('sidebarMobile');
    if (mobileSidebar) {
        mobileSidebar.querySelectorAll('.nav-link:not([data-bs-toggle="collapse"])').forEach(function (link) {
            link.addEventListener('click', function () {
                const href = link.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:')) {
                    return;
                }

                const instance = bootstrap.Offcanvas.getOrCreateInstance(mobileSidebar);
                instance.hide();
            });
        });
    }

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
