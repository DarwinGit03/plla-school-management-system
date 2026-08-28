document.addEventListener(
    'DOMContentLoaded',
    function () {

        const lrnInput = document.getElementById('lrn');

        /*
        |--------------------------------------------------------------------------
        | LRN CHECKER => EXIST
        |--------------------------------------------------------------------------
        */

        if (!lrnInput) {return;}
        let lastCheckedLrn = '';
        function checkLrn()
        {
            const lrn =
                lrnInput.value.trim();


            if (!lrn) {

                return;
            }


            if (!/^[0-9]+$/.test(lrn)) {

                return;
            }


            if (lrn === lastCheckedLrn) {

                return;
            }


            lastCheckedLrn = lrn;


            const formData = [

                {
                    name: 'lrn',
                    value: lrn
                }

            ];


            formData.push({

                name: CSRF.name,

                value: CSRF.hash

            });


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


                        if (
                            response.exists
                        ) {

                            lrnInput.classList.add(
                                'is-invalid'
                            );


                            document.getElementById(
                                'duplicateStudentName'
                            ).textContent =
                                response.student.name || '-';


                            document.getElementById(
                                'duplicateAcademicYear'
                            ).textContent =
                                response.student.academic_year || '-';


                            document.getElementById(
                                'duplicateGradeLevel'
                            ).textContent =
                                response.student.grade_level || '-';


                            document.getElementById(
                                'duplicateSection'
                            ).textContent =
                                response.student.section || '-';


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

                    }

            });

        }

        lrnInput.addEventListener(
            'input',
            function () {

                lrnInput.classList.remove(
                    'is-invalid'
                );

                lastCheckedLrn = '';

            }
        );

        // lrnInput.addEventListener(
        //     'input',
        //     function () {
        //         checkLrn();

        //     }
        // );

        let initialLrnValue = "";

        lrnInput.addEventListener('focus', (e) => {
            initialLrnValue = e.target.value;
        });

        //Call only the function once has change specially in edit

        lrnInput.addEventListener('blur', (e) => {
            const currentValue = e.target.value;
            
            if (currentValue !== initialLrnValue) {
                checkLrn();
            }
        });
    }
);