document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | LRN Input
        |--------------------------------------------------------------------------
        */

        const lrnInput =
            document.getElementById('lrn');


        if (!lrnInput) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Check LRN
        |--------------------------------------------------------------------------
        */

        function checkLrn()
        {
            const lrn =
                lrnInput.value.trim();


            if (!lrn) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Numbers Only
            |--------------------------------------------------------------------------
            */

            if (!/^[0-9]+$/.test(lrn)) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | AJAX Data
            |--------------------------------------------------------------------------
            */

            const formData = [

                {
                    name: 'lrn',
                    value: lrn
                }

            ];


            /*
            |--------------------------------------------------------------------------
            | CSRF
            |--------------------------------------------------------------------------
            */

            formData.push({

                name: CSRF.name,

                value: CSRF.hash

            });


            /*
            |--------------------------------------------------------------------------
            | AJAX Request
            |--------------------------------------------------------------------------
            */

            $.ajax({

                url:
                    BASE_URL +
                    'students/check-lrn',

                type: 'POST',

                data: formData,

                dataType: 'json',

                success:
                    function (response) {

                        if (
                            !response.success
                        ) {

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Duplicate LRN
                        |--------------------------------------------------------------------------
                        */

                        if (
                            response.exists
                        ) {

                            lrnInput.classList.add(
                                'is-invalid'
                            );


                            /*
                            |--------------------------------------------------------------
                            | Student Name
                            |--------------------------------------------------------------
                            */

                            document.getElementById(
                                'duplicateStudentName'
                            ).textContent =
                                response.student.name || '-';


                            /*
                            |--------------------------------------------------------------
                            | Academic Year
                            |--------------------------------------------------------------
                            */

                            document.getElementById(
                                'duplicateAcademicYear'
                            ).textContent =
                                response.student.academic_year || '-';


                            /*
                            |--------------------------------------------------------------
                            | Grade Level
                            |--------------------------------------------------------------
                            */

                            document.getElementById(
                                'duplicateGradeLevel'
                            ).textContent =
                                response.student.grade_level || '-';


                            /*
                            |--------------------------------------------------------------
                            | Section
                            |--------------------------------------------------------------
                            */

                            document.getElementById(
                                'duplicateSection'
                            ).textContent =
                                response.student.section || '-';


                            /*
                            |--------------------------------------------------------------
                            | Show Modal
                            |--------------------------------------------------------------
                            */

                            const modalElement =
                                document.getElementById(
                                    'duplicateLrnModal'
                                );


                            if (
                                modalElement
                            ) {

                                const modal =
                                    bootstrap.Modal
                                        .getOrCreateInstance(
                                            modalElement
                                        );


                                modal.show();

                            }

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | LRN Available
                            |--------------------------------------------------------------------------
                            */

                            lrnInput.classList.remove(
                                'is-invalid'
                            );

                        }

                    },


                error:
                    function (
                        xhr,
                        status,
                        error
                    ) {

                        console.error(
                            'LRN check failed:',
                            status,
                            error
                        );

                        console.error(
                            xhr.responseText
                        );

                    }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Check When Leaving LRN
        |--------------------------------------------------------------------------
        */

        lrnInput.addEventListener(
            'blur',
            function () {

                checkLrn();

            }
        );

    }
);