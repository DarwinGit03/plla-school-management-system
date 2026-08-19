document.addEventListener('DOMContentLoaded', function () {

    const lrnInput = document.getElementById('lrn');
    const studentNoInput = document.getElementById('student_no');
    const sameAsLrn = document.getElementById('student_no_same_as_lrn');

    /*
    |--------------------------------------------------------------------------
    | Student Number ↔ LRN [checkbox]
    |--------------------------------------------------------------------------
    */

    if (lrnInput && studentNoInput && sameAsLrn ) {
        function syncStudentNumber()
        {
            if (sameAsLrn.checked) {

                studentNoInput.value =
                    lrnInput.value;

                studentNoInput.readOnly =
                    true;

            } else {

                studentNoInput.readOnly =
                    false;

            }
        }

        sameAsLrn.addEventListener(
            'change',
            syncStudentNumber
        );

        lrnInput.addEventListener(
            'input',
            function () {
                if (
                    sameAsLrn.checked
                ) {

                    studentNoInput.value =
                        lrnInput.value;

                }
            }
        );

        syncStudentNumber();

    }

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

    lrnInput.addEventListener(
        'blur',
        function () {

            checkLrn();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Current Address → Permanent Address
    |--------------------------------------------------------------------------
    */

    const currentAddressFields = [
        'house_no',
        'street',
        'barangay',
        'city',
        'province',
        'postal_code'
    ];


    const sameAddressCheckbox =
        document.getElementById(
            'permanent_same_as_current'
        );


    function syncPermanentAddress()
    {
        if (!sameAddressCheckbox) {
            return;
        }


        currentAddressFields.forEach(
            function (field) {

                const currentField =
                    document.getElementById(
                        'current_' + field
                    );

                const permanentField =
                    document.getElementById(
                        'permanent_' + field
                    );


                if (
                    !currentField ||
                    !permanentField
                ) {
                    return;
                }


                if (
                    sameAddressCheckbox.checked
                ) {

                    permanentField.value =
                        currentField.value;

                    permanentField.readOnly =
                        true;

                } else {

                    permanentField.readOnly =
                        false;
                }

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Checkbox [/] current COPY permanent Address 
    |--------------------------------------------------------------------------
    */

    if (sameAddressCheckbox) {

        sameAddressCheckbox.addEventListener(
            'change',
            syncPermanentAddress
        );


        /*
        |--------------------------------------------------------------------------
        | Current Address Changes
        |--------------------------------------------------------------------------
        */

        currentAddressFields.forEach(
            function (field) {

                const currentField =
                    document.getElementById(
                        'current_' + field
                    );


                if (!currentField) {
                    return;
                }


                currentField.addEventListener(
                    'input',
                    function () {

                        if (
                            sameAddressCheckbox.checked
                        ) {

                            const permanentField =
                                document.getElementById(
                                    'permanent_' + field
                                );


                            if (permanentField) {

                                permanentField.value =
                                    currentField.value;

                            }

                        }

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Initial State
        |--------------------------------------------------------------------------
        */

        syncPermanentAddress();
    }


    /*
    |--------------------------------------------------------------------------
    | Dynamic Guardians
    |--------------------------------------------------------------------------
    */

    const guardianContainer =
        document.getElementById(
            'guardianContainer'
        );

    const addGuardianBtn =
        document.getElementById(
            'addGuardianBtn'
        );


    let guardianIndex = 0;

    function guardianTemplate(index, guardian = {})
    {
        return `
            <div
                class="col-12 guardian-item"
                data-guardian-index="${index}">

                <div class="card border">

                    <div class="card-header bg-light">

                        <div
                            class="d-flex justify-content-between align-items-center">

                            <strong class="guardian-title">
                                Guardian ${index + 1}
                            </strong>

                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm remove-guardian">

                                <i class="fas fa-trash me-1"></i>
                                Remove

                            </button>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Guardian Type
                                </label>

                                <select
                                    name="guardians[${index}][guardian_type]"
                                    class="form-select guardian-type">

                                    <option value="">Select Guardian Type</option>

                                    <option
                                        value="father"
                                        ${guardian.guardian_type === 'father' ? 'selected' : ''}>
                                        Father
                                    </option>

                                    <option
                                        value="mother"
                                        ${guardian.guardian_type === 'mother' ? 'selected' : ''}>
                                        Mother
                                    </option>

                                    <option
                                        value="guardian"
                                        ${guardian.guardian_type === 'guardian' ? 'selected' : ''}>
                                        Guardian
                                    </option>

                                </select>

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Relationship
                                </label>

                                <input
                                    type="text"
                                    name="guardians[${index}][relationship]"
                                    class="form-control guardian-relationship"
                                    placeholder="Relationship"
                                    value="${guardian.relationship ?? ''}">

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label d-block">
                                    Contact Preference
                                </label>

                                <div class="form-check mt-2">

                                    <input
                                        type="checkbox"
                                        class="form-check-input primary-guardian"
                                        name="guardians[${index}][is_primary]"
                                        value="1"
                                        ${guardian.is_primary == '1' ? 'checked' : ''}>

                                    <label class="form-check-label">
                                        Primary Contact (for emergency)
                                    </label>

                                </div>

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="guardians[${index}][first_name]"
                                    class="form-control"
                                    value="${guardian.first_name ?? ''}">
                                    

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Middle Name
                                </label>

                                <input
                                    type="text"
                                    name="guardians[${index}][middle_name]"
                                    class="form-control"
                                    value="${guardian.middle_name ?? ''}">

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="guardians[${index}][last_name]"
                                    class="form-control"
                                    value="${guardian.last_name ?? ''}">

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Mobile Number
                                </label>

                                <input
                                    type="text"
                                    name="guardians[${index}][mobile_no]"
                                    class="form-control"
                                    value="${guardian.mobile_no ?? ''}">

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="guardians[${index}][email]"
                                    class="form-control"
                                    value="${guardian.email ?? ''}">

                            </div>


                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Occupation
                                </label>

                                <input
                                    type="text"
                                    name="guardians[${index}][occupation]"
                                    class="form-control"
                                    value="${guardian.occupation ?? ''}">

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        `;
    }

    function renderGuardians(guardians)
    {
        guardianContainer.innerHTML = '';


        if (
            !Array.isArray(guardians) ||
            guardians.length === 0
        ) {

            guardianContainer.insertAdjacentHTML(
                'beforeend',
                guardianTemplate(0)
            );

            guardianIndex = 1;

            return;
        }


        guardians.forEach(
            function (guardian, index) {

                guardianContainer.insertAdjacentHTML(
                    'beforeend',
                    guardianTemplate(
                        index,
                        guardian
                    )
                );

            }
        );


        guardianIndex =
            guardians.length;
    }

    renderGuardians(
        submittedGuardians
    );

    /*
    |--------------------------------------------------------------------------
    | Add Guardian
    |--------------------------------------------------------------------------
    */

    if (guardianContainer && addGuardianBtn) {

        addGuardianBtn.addEventListener(
            'click',
            function () {

                guardianContainer.insertAdjacentHTML(
                    'beforeend',
                    guardianTemplate(
                        guardianIndex
                    )
                );

                guardianIndex++;

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Remove Guardian
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const removeButton =
                event.target.closest(
                    '.remove-guardian'
                );


            if (!removeButton) {
                return;
            }


            const guardianItem =
                removeButton.closest(
                    '.guardian-item'
                );


            if (!guardianItem) {
                return;
            }


            /*
            | Keep at least one guardian section.
            */

            const guardianItems =
                document.querySelectorAll(
                    '.guardian-item'
                );


            if (guardianItems.length <= 1) {

                return;
            }


            guardianItem.remove();


            /*
            | Re-number visible guardians.
            */

            document
                .querySelectorAll(
                    '.guardian-item'
                )
                .forEach(
                    function (item, index) {

                        const title =
                            item.querySelector(
                                '.guardian-title'
                            );


                        if (title) {

                            title.textContent =
                                'Guardian ' +
                                (index + 1);

                        }

                    }
                );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Only One Primary Guardian
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'change',
        function (event) {

            if (
                !event.target.classList.contains(
                    'primary-guardian'
                )
            ) {
                return;
            }


            if (!event.target.checked) {
                return;
            }


            document
                .querySelectorAll(
                    '.primary-guardian'
                )
                .forEach(
                    function (checkbox) {

                        if (
                            checkbox !==
                            event.target
                        ) {

                            checkbox.checked =
                                false;

                        }

                    }
                );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Guardian Type → Relationship
    |--------------------------------------------------------------------------
    */

    function updateGuardianRelationship(guardianItem)
    {
        if (!guardianItem) {
            return;
        }


        const guardianType =
            guardianItem.querySelector(
                '.guardian-type'
            );

        const relationship =
            guardianItem.querySelector(
                '.guardian-relationship'
            );


        if (
            !guardianType ||
            !relationship
        ) {
            return;
        }


        const selectedType =
            guardianType.value;


        /*
        |--------------------------------------------------------------------------
        | Father
        |--------------------------------------------------------------------------
        */

        if (selectedType === 'father') {

            relationship.value =
                'Father';

            relationship.readOnly =
                true;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Mother
        |--------------------------------------------------------------------------
        */

        if (selectedType === 'mother') {

            relationship.value =
                'Mother';

            relationship.readOnly =
                true;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Legal Guardian
        |--------------------------------------------------------------------------
        */

        if (
            selectedType ===
            'guardian'
        ) {

            relationship.value = '';

            relationship.readOnly =
                false;

            relationship.focus();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Existing + Dynamic Guardians
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'change',
        function (event) {

            if (
                !event.target.classList.contains(
                    'guardian-type'
                )
            ) {
                return;
            }


            const guardianItem =
                event.target.closest(
                    '.guardian-item'
                );


            updateGuardianRelationship(
                guardianItem
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initialize Existing Guardian
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.guardian-item'
        )
        .forEach(
            function (guardianItem) {

                updateGuardianRelationship(
                    guardianItem
                );

            }
        );

//DROPDOWN YEAR, GRADE, SECTION
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

        gradeLevel.disabled = true;
        section.disabled = true;
        loadAcademicYears();

        academicYear.addEventListener(
            'change',
            function () {

                resetSelect(
                    gradeLevel,
                    'Select Grade Level'
                );

                resetSelect(
                    section,
                    'Select Section'
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


        gradeLevel.addEventListener(
            'change',
            function () {

                resetSelect(
                    section,
                    'Select Section'
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
                        'Select Academic Year'
                    );


                    data.forEach(item => {

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

                    });


                    /*
                    |--------------------------------------------------------------
                    | Restore submitted Academic Year
                    |--------------------------------------------------------------
                    */

                    if (
                        submittedEnrollment.academic_year
                    ) {

                        academicYear.value =
                            submittedEnrollment.academic_year;

                        loadGradeLevels(
                            submittedEnrollment.academic_year
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
                        'Select Grade Level'
                    );


                    data.forEach(item => {

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

                    });


                    gradeLevel.disabled =
                        data.length === 0;


                    /*
                    |--------------------------------------------------------------
                    | Restore submitted Grade Level
                    |--------------------------------------------------------------
                    */

                    if (
                        submittedEnrollment.grade_level
                    ) {

                        gradeLevel.value =
                            submittedEnrollment.grade_level;

                        loadSections(
                            year,
                            submittedEnrollment.grade_level
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
                        'Select Section'
                    );


                    data.forEach(item => {

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

                    });


                    section.disabled =
                        data.length === 0;


                    /*
                    |--------------------------------------------------------------
                    | Restore submitted Section
                    |--------------------------------------------------------------
                    */

                    if (
                        submittedEnrollment.section
                    ) {

                        section.value =
                            submittedEnrollment.section;
                    }

                })
                .catch(error => {

                    console.error(
                        'Section:',
                        error
                    );

                });
        }


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

});