document.addEventListener(
    'DOMContentLoaded',
    function () {

        const academicYear =
            document.querySelector(
                '#academic_year'
            );

        const gradeLevel =
            document.querySelector(
                '#grade_level'
            );

        const section =
            document.querySelector(
                '#section'
            );


        if (
            !academicYear ||
            !gradeLevel ||
            !section
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Initial State
        |--------------------------------------------------------------------------
        */

        gradeLevel.disabled = true;
        section.disabled = true;


        /*
        |--------------------------------------------------------------------------
        | Load Academic Years
        |--------------------------------------------------------------------------
        */

        loadAcademicYears();


        /*
        |--------------------------------------------------------------------------
        | Academic Year Changed
        |--------------------------------------------------------------------------
        */

        academicYear.addEventListener(
            'change',
            function () {

                resetSelect(
                    gradeLevel,
                    getPlaceholder(
                        'grade'
                    )
                );

                resetSelect(
                    section,
                    getPlaceholder(
                        'section'
                    )
                );


                gradeLevel.disabled = true;
                section.disabled = true;


                if (!this.value) {
                    return;
                }


                loadGradeLevels(
                    this.value
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Grade Level Changed
        |--------------------------------------------------------------------------
        */

        gradeLevel.addEventListener(
            'change',
            function () {

                resetSelect(
                    section,
                    getPlaceholder(
                        'section'
                    )
                );


                section.disabled = true;


                if (!this.value) {
                    return;
                }


                loadSections(
                    academicYear.value,
                    this.value
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Load Academic Years
        |--------------------------------------------------------------------------
        */

        function loadAcademicYears()
        {
            fetch(
                BASE_URL +
                'students/academic_years'
            )
                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'HTTP ' +
                            response.status
                        );
                    }

                    return response.json();

                })
                .then(data => {

                    resetSelect(
                        academicYear,
                        getPlaceholder(
                            'year'
                        )
                    );


                    data.forEach(
                        function (item) {

                            const option =
                                document.createElement(
                                    'option'
                                );


                            option.value =
                                item.year;

                            option.textContent =
                                item.year;


                            academicYear.appendChild(
                                option
                            );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Restore Academic Year
                    |--------------------------------------------------------------------------
                    */

                    const selectedYear =
                        submittedEnrollment
                            ?.academic_year || '';


                    if (selectedYear) {

                        academicYear.value =
                            selectedYear;


                        /*
                        |--------------------------------------------------------------
                        | Load Grades
                        |--------------------------------------------------------------
                        */

                        loadGradeLevels(
                            selectedYear
                        );
                    }

                })
                .catch(error => {

                    console.error(
                        'Academic Year:',
                        error
                    );

                });
        }


        /*
        |--------------------------------------------------------------------------
        | Load Grade Levels
        |--------------------------------------------------------------------------
        */

        function loadGradeLevels(year)
        {
            fetch(
                BASE_URL +
                'students/grade_levels?year=' +
                encodeURIComponent(year)
            )
                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'HTTP ' +
                            response.status
                        );
                    }

                    return response.json();

                })
                .then(data => {

                    resetSelect(
                        gradeLevel,
                        getPlaceholder(
                            'grade'
                        )
                    );


                    data.forEach(
                        function (item) {

                            const option =
                                document.createElement(
                                    'option'
                                );


                            option.value =
                                item.grade;


                            option.textContent =
                                'Grade ' +
                                item.grade;


                            gradeLevel.appendChild(
                                option
                            );
                        }
                    );


                    gradeLevel.disabled =
                        data.length === 0;


                    /*
                    |--------------------------------------------------------------------------
                    | Restore Grade Level
                    |--------------------------------------------------------------------------
                    */

                    const selectedGrade =
                        submittedEnrollment
                            ?.grade_level || '';


                    if (selectedGrade) {

                        gradeLevel.value =
                            selectedGrade;


                        /*
                        |--------------------------------------------------------------
                        | Load Sections
                        |--------------------------------------------------------------
                        */

                        loadSections(
                            year,
                            selectedGrade
                        );
                    }

                })
                .catch(error => {

                    console.error(
                        'Grade Level:',
                        error
                    );

                });
        }


        /*
        |--------------------------------------------------------------------------
        | Load Sections
        |--------------------------------------------------------------------------
        */

        function loadSections(
            year,
            grade
        )
        {
            fetch(
                BASE_URL +
                'students/sections?year=' +
                encodeURIComponent(year) +
                '&grade=' +
                encodeURIComponent(grade)
            )
                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'HTTP ' +
                            response.status
                        );
                    }

                    return response.json();

                })
                .then(data => {

                    resetSelect(
                        section,
                        getPlaceholder(
                            'section'
                        )
                    );


                    data.forEach(
                        function (item) {

                            const option =
                                document.createElement(
                                    'option'
                                );


                            option.value =
                                item.section;


                            option.textContent =
                                item.section;


                            section.appendChild(
                                option
                            );
                        }
                    );


                    section.disabled =
                        data.length === 0;


                    /*
                    |--------------------------------------------------------------------------
                    | Restore Section
                    |--------------------------------------------------------------------------
                    */

                    const selectedSection =
                        submittedEnrollment
                            ?.section || '';


                    if (selectedSection) {

                        section.value =
                            selectedSection;
                    }

                })
                .catch(error => {

                    console.error(
                        'Section:',
                        error
                    );

                });
        }


        /*
        |--------------------------------------------------------------------------
        | Placeholder
        |--------------------------------------------------------------------------
        */

        function getPlaceholder(type)
        {
            /*
            |----------------------------------------------------------------------
            | Create Page
            |----------------------------------------------------------------------
            */

            if (
                typeof classAssignmentMode !==
                'undefined'
                &&
                classAssignmentMode === 'create'
            ) {

                if (type === 'year') {
                    return '';
                }

                if (type === 'grade') {
                    return '';
                }

                if (type === 'section') {
                    return '';
                }
            }


            /*
            |----------------------------------------------------------------------
            | Filter Page
            |----------------------------------------------------------------------
            */

            return 'All';
        }


        /*
        |--------------------------------------------------------------------------
        | Reset Select
        |--------------------------------------------------------------------------
        */

        function resetSelect(
            select,
            placeholder
        )
        {
            select.innerHTML = '';


            const option =
                document.createElement(
                    'option'
                );


            option.value = '';

            option.textContent =
                placeholder;


            select.appendChild(
                option
            );
        }

        const buttons =
            document.querySelectorAll(
                '.change-status-btn'
            );

        const modalElement =
            document.getElementById(
                'changeStatusModal'
            );

        const form =
            document.getElementById(
                'changeStatusForm'
            );

        const studentName =
            document.getElementById(
                'statusStudentName'
            );

        const studentLrn =
            document.getElementById(
                'statusStudentLrn'
            );

        const studentGrade =
            document.getElementById(
                'statusStudentGrade'
            );

        const actionText =
            document.getElementById(
                'statusActionText'
            );

        const newStatusText =
            document.getElementById(
                'statusNewStatus'
            );

        const newStatus =
            document.getElementById(
                'newStudentStatus'
            );

        const reason =
            document.getElementById(
                'statusReason'
            );

        const submitButton =
            document.getElementById(
                'changeStatusSubmit'
            );

        const submitText =
            document.getElementById(
                'changeStatusSubmitText'
            );

        if (
            !modalElement ||
            !form
        ) {
            return;
        }

        buttons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const id =
                            this.getAttribute(
                                'data-id'
                            );

                        const name =
                            this.getAttribute(
                                'data-name'
                            );

                        const lrn =
                            this.getAttribute(
                                'data-lrn'
                            );

                        const grade =
                            this.getAttribute(
                                'data-grade'
                            );

                        const targetStatus =
                            this.getAttribute(
                                'data-new-status'
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | Student Information
                        |--------------------------------------------------------------------------
                        */

                        studentName.textContent =
                            name || '';

                        studentLrn.textContent =
                            lrn || '';

                        studentGrade.textContent =
                            grade || '';

                        /*
                        |--------------------------------------------------------------------------
                        | Status Information
                        |--------------------------------------------------------------------------
                        */

                        if (
                            targetStatus ===
                            'inactive'
                        ) {

                            actionText.textContent =
                                'deactivate';

                            newStatusText.textContent =
                                'Inactive';

                            submitText.textContent =
                                'Deactivate';

                            submitButton.className =
                                'btn btn-danger';

                        } else {

                            actionText.textContent =
                                'activate';

                            newStatusText.textContent =
                                'Active';

                            submitText.textContent =
                                'Activate';

                            submitButton.className =
                                'btn btn-success';

                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Hidden Status
                        |--------------------------------------------------------------------------
                        */

                        newStatus.value =
                            targetStatus;

                        /*
                        |--------------------------------------------------------------------------
                        | Reset Reason
                        |--------------------------------------------------------------------------
                        */

                        reason.value =
                            '';

                        /*
                        |--------------------------------------------------------------------------
                        | Form Action
                        |--------------------------------------------------------------------------
                        */

                        form.setAttribute(
                            'action',
                            BASE_URL +
                            'students/change-status/' +
                            id
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Show Modal
                        |--------------------------------------------------------------------------
                        */

                        const modal =
                            bootstrap.Modal
                                .getOrCreateInstance(
                                    modalElement
                                );

                        modal.show();

                    }
                );

            }
        );

    }
);