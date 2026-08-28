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

                // studentNoInput.readOnly =
                //     true;

            } 
            // else {

            //     studentNoInput.readOnly =
            //         false;

            // }
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
                                <span class="text-danger">*</span>

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
                                <span class="text-danger">*</span>

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
                                <span class="text-danger">*</span>

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
                                <span class="text-danger">*</span>

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
                                <span class="text-danger">*</span>

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
                                <span class="text-danger">*</span>

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
            if(classAssignmentMode === 'create'){
                relationship.value = '';
            }

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

});