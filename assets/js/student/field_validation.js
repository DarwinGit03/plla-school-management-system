document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Numeric Fields
    |--------------------------------------------------------------------------
    */

    [
        '#lrn',
        '#student_no'
    ].forEach(function (selector) {

        const input = document.querySelector(selector);

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            validateNumericField(this);
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Name Fields
    |--------------------------------------------------------------------------
    */

    [
        '#first_name',
        '#middle_name',
        '#last_name'
    ].forEach(function (selector) {

        const input = document.querySelector(selector);

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            validateNameField(this);
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */

    const mobile =
        document.querySelector('#mobile_no');

    if (mobile) {

        mobile.addEventListener('input', function () {
            validateMobileField(this);
        });

    }


    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    const email =
        document.querySelector('#email');

    if (email) {

        email.addEventListener('input', function () {
            validateEmailField(this);
        });

    }


    /*
    |--------------------------------------------------------------------------
    | Form Submit
    |--------------------------------------------------------------------------
    */

    const form =
        document.querySelector(
            'form[action*="students/create"]'
        );

    if (form) {

        form.addEventListener('submit', function (event) {

            let valid = true;


            /*
            |--------------------------------------------------------------
            | Required fields
            |--------------------------------------------------------------
            */

            const requiredFields =
                form.querySelectorAll(
                    '[required]'
                );

            requiredFields.forEach(function (input) {

                if (
                    input.value.trim() === ''
                ) {

                    showFieldError(
                        input,
                        'This field is required.'
                    );

                    valid = false;

                }

            });


            /*
            |--------------------------------------------------------------
            | LRN
            |--------------------------------------------------------------
            */

            const lrn =
                document.querySelector('#lrn');

            if (
                lrn &&
                !validateNumericField(lrn)
            ) {

                valid = false;

            }


            /*
            |--------------------------------------------------------------
            | Student Number
            |--------------------------------------------------------------
            */

            const studentNo =
                document.querySelector(
                    '#student_no'
                );

            if (
                studentNo &&
                !validateNumericField(studentNo)
            ) {

                valid = false;

            }


            /*
            |--------------------------------------------------------------
            | Names
            |--------------------------------------------------------------
            */

            [
                '#first_name',
                '#middle_name',
                '#last_name'
            ].forEach(function (selector) {

                const input =
                    document.querySelector(
                        selector
                    );

                if (
                    input &&
                    input.value.trim() !== '' &&
                    !validateNameField(input)
                ) {

                    valid = false;

                }

            });


            /*
            |--------------------------------------------------------------
            | Mobile
            |--------------------------------------------------------------
            */

            if (
                mobile &&
                mobile.value.trim() !== '' &&
                !validateMobileField(mobile)
            ) {

                valid = false;

            }


            /*
            |--------------------------------------------------------------
            | Email
            |--------------------------------------------------------------
            */

            if (
                email &&
                email.value.trim() !== '' &&
                !validateEmailField(email)
            ) {

                valid = false;

            }


            /*
            |--------------------------------------------------------------
            | Stop submission
            |--------------------------------------------------------------
            */

            if (!valid) {

                event.preventDefault();

                const firstError =
                    form.querySelector(
                        '.is-invalid'
                    );

                if (firstError) {

                    firstError.focus();

                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                }

                /*
                |--------------------------------------------------------------------------
                | Guardian Validation
                |--------------------------------------------------------------------------
                */

                const guardians =
                    form.querySelectorAll(
                        '[name^="guardians"]'
                    );

                guardians.forEach(function (input) {

                    const name = input.name;


                    /*
                    |----------------------------------------------------------------------
                    | Required Guardian Fields
                    |----------------------------------------------------------------------
                    */

                    if (
                        (
                            name.endsWith('[guardian_type]')
                            ||
                            name.endsWith('[relationship]')
                            ||
                            name.endsWith('[first_name]')
                            ||
                            name.endsWith('[last_name]')
                            ||
                            name.endsWith('[mobile_no]')
                            ||
                            name.endsWith('[email]')
                        )
                        &&
                        input.value.trim() === ''
                    ) {

                        showFieldError(
                            input,
                            'This field is required.'
                        );

                        valid = false;

                        return;
                    }


                    /*
                    |----------------------------------------------------------------------
                    | Guardian Name
                    |----------------------------------------------------------------------
                    */

                    if (
                        name.endsWith('[first_name]')
                        ||
                        name.endsWith('[middle_name]')
                        ||
                        name.endsWith('[last_name]')
                    ) {

                        if (
                            input.value.trim() !== ''
                            &&
                            !validateNameField(input)
                        ) {

                            valid = false;

                        }

                    }


                    /*
                    |----------------------------------------------------------------------
                    | Guardian Mobile
                    |----------------------------------------------------------------------
                    */

                    if (
                        name.endsWith('[mobile_no]')
                    ) {

                        if (
                            !validateMobileField(input)
                        ) {

                            valid = false;

                        }

                    }


                    /*
                    |----------------------------------------------------------------------
                    | Guardian Email
                    |----------------------------------------------------------------------
                    */

                    if (
                        name.endsWith('[email]')
                    ) {

                        if (
                            !validateEmailField(input)
                        ) {

                            valid = false;

                        }

                    }

                });

            }

        });

    }



    function validateNumericField(input)
    {
        const value = input.value.trim();

        if (value === '') {

            clearFieldError(input);

            return true;
        }

        if (!/^[0-9]+$/.test(value)) {

            showFieldError(
                input,
                'This field must contain numbers only.'
            );

            return false;
        }

        clearFieldError(input);

        return true;
    }

    function validateNameField(input)
    {
        const value = input.value.trim();

        if (value === '') {

            clearFieldError(input);

            return true;
        }

        if (
            !/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(value)
        ) {

            showFieldError(
                input,
                'This field must contain letters only.'
            );

            return false;
        }

        clearFieldError(input);

        return true;
    }


    function validateMobileField(input)
    {
        const value = input.value.trim();

        if (value === '') {

            clearFieldError(input);

            return true;
        }

        if (!/^[0-9]+$/.test(value)) {

            showFieldError(
                input,
                'Mobile Number must contain numbers only.'
            );

            return false;
        }

        if (value.length !== 11) {

            showFieldError(
                input,
                'Mobile Number must contain exactly 11 digits.'
            );

            return false;
        }

        clearFieldError(input);

        return true;
    }

    function validateEmailField(input)
    {
        const value = input.value.trim();

        if (value === '') {

            clearFieldError(input);

            return true;
        }

        if (
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
        ) {

            showFieldError(
                input,
                'Please enter a valid email address.'
            );

            return false;
        }

        clearFieldError(input);

        return true;
    }


    function showFieldError(input, message)
    {
        input.classList.add('is-invalid');

        let error =
            input.parentElement.querySelector(
                '.js-field-error'
            );

        if (!error) {

            error =
                document.createElement('div');

            error.className =
                'invalid-feedback js-field-error';

            input.parentElement.appendChild(
                error
            );
        }

        error.textContent = message;
    }


    function clearFieldError(input)
    {
        input.classList.remove('is-invalid');

        const error =
            input.parentElement.querySelector(
                '.js-field-error'
            );

        if (error) {
            error.remove();
        }
    }



    /*
    |--------------------------------------------------------------------------
    | Guardian Validation
    |--------------------------------------------------------------------------
    */

    document.addEventListener('input', function (event) {

        const input = event.target;

        /*
        |--------------------------------------------------------------------------
        | Guardian Names
        |--------------------------------------------------------------------------
        */

        if (
            input.matches(
                'input[name^="guardians"][name$="[first_name]"],' +
                'input[name^="guardians"][name$="[middle_name]"],' +
                'input[name^="guardians"][name$="[last_name]"]'
            )
        ) {

            validateNameField(input);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Guardian Mobile
        |--------------------------------------------------------------------------
        */

        if (
            input.matches(
                'input[name^="guardians"][name$="[mobile_no]"]'
            )
        ) {

            validateMobileField(input);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Guardian Email
        |--------------------------------------------------------------------------
        */

        if (
            input.matches(
                'input[name^="guardians"][name$="[email]"]'
            )
        ) {

            validateEmailField(input);

            return;
        }

    });
});
