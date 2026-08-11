<div class="container-fluid">

    <!-- =====================================================
         Header
    ====================================================== -->

    <div class="d-flex
                flex-column
                flex-lg-row
                justify-content-between
                align-items-lg-center
                gap-3
                mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">

                Register Student

            </h1>

            <p class="text-muted mb-0">

                Create a new student record.

            </p>

        </div>


        <a
            href="<?= site_url('students'); ?>"
            class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>

            Back to Students

        </a>

    </div>


    <!-- =====================================================
         Validation Errors
    ====================================================== -->

    <!-- < ?php if (validation_errors()): ?>

        <div class="alert alert-danger">

            <div class="fw-semibold mb-2">
                Please correct the following errors:
            </div>

            < ?= validation_errors(); ?>

        </div>

    < ?php endif; ?> -->
    
        <?php if ($this->session->flashdata('error')): ?>

            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert">

                <?= $this->session->flashdata('error'); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>

            </div>

        <?php endif; ?>



    <!-- =====================================================
         Registration Form
    ====================================================== -->

    <form
        method="post"
        action="<?= site_url('students/create'); ?>">

        <input
        type="hidden"
        name="<?= $this->security->get_csrf_token_name(); ?>"
        value="<?= $this->security->get_csrf_hash(); ?>">
        <!-- =================================================
             Personal Information
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-user text-primary me-2"></i>

                    Student Information

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">

                    <!-- LRN -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="lrn"
                            class="form-label">

                            LRN

                        </label>
                        <span class="text-danger">*</span>

                          <input
                            type="text"
                            name="lrn"
                            id="lrn"
                            class="form-control <?= form_error('lrn') ? 'is-invalid' : ''; ?>"
                            value="<?= set_value('lrn'); ?>"
                            autocomplete="off"
                            inputmode="numeric"
                            maxlength="20" required
                        >

                        <?= form_error(
                            'lrn',
                            '<div class="invalid-feedback">',
                            '</div>'
                        ); ?>


                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="student_no_same_as_lrn"
                                name="student_no_same_as_lrn"
                                value="1"
                                <?= set_checkbox(
                                    'student_no_same_as_lrn',
                                    '1'
                                ); ?>>

                            <label
                                class="form-check-label"
                                for="student_no_same_as_lrn">

                                Student No. is the same as LRN

                            </label>

                        </div>

                    </div>

                    <!-- Student Number -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="student_no"
                            class="form-label">

                            Student No.

                        </label>
                        <span class="text-danger">*</span>

                        <input required
                            type="text"
                            name="student_no"
                            id="student_no"
                            class="form-control <?= form_error('student_no') ? 'is-invalid' : ''; ?>"
                            value="<?= set_value('student_no'); ?>"
                            autocomplete="off"
                            inputmode="numeric"
                            maxlength="20"
                            pattern="[0-9]+" required>

                        <?php if (form_error('student_no')): ?>

                            <div class="invalid-feedback">

                                <?= form_error('student_no'); ?>

                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                    </div>


                    <!-- First Name -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="first_name"
                            class="form-label">

                            First Name
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            class="form-control"
                            value="<?= set_value('first_name'); ?>"
                            required>

                    </div>


                    <!-- Middle Name -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="middle_name"
                            class="form-label">

                            Middle Name

                        </label>

                        <input
                            type="text"
                            id="middle_name"
                            name="middle_name"
                            class="form-control"
                            value="<?= set_value('middle_name'); ?>">

                    </div>


                    <!-- Last Name -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="last_name"
                            class="form-label">

                            Last Name
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            class="form-control"
                            value="<?= set_value('last_name'); ?>"
                            required>

                    </div>


                    <!-- Suffix -->

                    <div class="col-12 col-md-6 col-lg-4" hidden>

                        <label
                            for="suffix"
                            class="form-label">

                            Suffix

                        </label>

                        <select
                            id="suffix"
                            name="suffix"
                            class="form-select">

                            <option value="">
                                None
                            </option>

                            <option
                                value="Jr."
                                <?= set_select(
                                    'suffix',
                                    'Jr.'
                                ); ?>>

                                Jr.

                            </option>

                            <option
                                value="Sr."
                                <?= set_select(
                                    'suffix',
                                    'Sr.'
                                ); ?>>

                                Sr.

                            </option>

                            <option
                                value="II"
                                <?= set_select(
                                    'suffix',
                                    'II'
                                ); ?>>

                                II

                            </option>

                            <option
                                value="III"
                                <?= set_select(
                                    'suffix',
                                    'III'
                                ); ?>>

                                III

                            </option>0

                        </select>

                    </div>


                    <!-- Gender -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="gender"
                            class="form-label">

                            Gender
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            id="gender"
                            name="gender"
                            class="form-select"
                            required>

                            <option value="">
                                Select Gender
                            </option>

                            <option
                                value="Male"
                                <?= set_select(
                                    'gender',
                                    'Male'
                                ); ?>>

                                Male

                            </option>

                            <option
                                value="Female"
                                <?= set_select(
                                    'gender',
                                    'Female'
                                ); ?>>

                                Female

                            </option>

                        </select>

                    </div>


                    <!-- Birth Date -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="birth_date"
                            class="form-label">

                            Birth Date
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="date"
                            id="birth_date"
                            name="birth_date"
                            class="form-control"
                            value="<?= set_value('birth_date'); ?>"
                            required>

                    </div>


                    <!-- Birth Place -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="birth_place"
                            class="form-label">

                            Birth Place

                        </label>

                        <input
                            type="text"
                            id="birth_place"
                            name="birth_place"
                            class="form-control"
                            value="<?= set_value('birth_place'); ?>">

                    </div>


                    <!-- Nationality -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="nationality"
                            class="form-label">

                            Nationality

                        </label>

                        <input
                            type="text"
                            id="nationality"
                            name="nationality"
                            class="form-control"
                            value="<?= set_value(
                                'nationality',
                                'Filipino'
                            ); ?>">

                    </div>


                    <!-- Civil Status -->

                    <!-- <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="civil_status"
                            class="form-label">

                            Civil Status

                        </label>

                        <select
                            id="civil_status"
                            name="civil_status"
                            class="form-select">

                            <option value="">
                                Select
                            </option>

                            <option value="Single">
                                Single
                            </option>

                            <option value="Married">
                                Married
                            </option>

                            <option value="Widowed">
                                Widowed
                            </option>

                        </select>

                    </div> -->

                </div>

            </div>

        </div>

        
        <!-- =================================================
             Contact Information
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-address-book text-primary me-2"></i>

                    Contact Information

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <!-- Mobile -->

                    <div class="col-12 col-md-6">

                        <label
                            for="mobile_no"
                            class="form-label">

                            Mobile Number

                        </label>

                        <input
                            type="tel"
                            id="mobile_no"
                            name="mobile_no"
                            class="form-control"
                            value="<?= set_value('mobile_no'); ?>"
                            placeholder="09XXXXXXXXX">

                    </div>


                    <!-- Telephone -->

                    <!-- <div class="col-12 col-md-6">

                        <label
                            for="telephone"
                            class="form-label">

                            Telephone

                        </label>

                        <input
                            type="tel"
                            id="telephone"
                            name="telephone"
                            class="form-control"
                            value="< ?= set_value('telephone'); ?>">

                    </div> -->


                    <!-- Email -->

                    <div class="col-12 col-md-6">

                        <label
                            for="email"
                            class="form-label">

                            Email Address

                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= set_value('email'); ?>">

                    </div>

                </div>

            </div>

        </div>

        
        <!-- =================================================
             Guardian information
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">
                            <i class="fas fa-hands-holding-child text-primary me-2"></i>
                            Guardian Information
                        </h5>

                        <small class="text-muted">
                            Add the student's parent or <b>legal guardian.</b>
                        </small>

                    </div>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        id="addGuardianBtn">

                        <i class="fas fa-plus me-1"></i>

                        Add Guardian

                    </button>

                </div>

            </div>


            <div class="card-body">

                <div
                    id="guardianContainer"
                    class="row g-3">


                    <!-- Guardian 1 -->

                    <div
                        class="col-12 guardian-item"
                        data-guardian-index="0">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div
                                    class="d-flex justify-content-between align-items-center">

                                    <strong class="guardian-title">

                                        Guardian 1

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


                                    <!-- Guardian Type -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Guardian Type
                                            
                                            <span class="text-danger">*</span>

                                        </label>

                                        <select
                                            name="guardians[0][guardian_type]"
                                            class="form-select guardian-type">

                                            <option value="">
                                            </option>
                                            
                                            <option value="father">
                                                Father
                                            </option>

                                            <option value="mother">
                                                Mother
                                            </option>

                                            <option value="legal_guardian">
                                                Legal Guardian
                                            </option>

                                        </select>

                                    </div>


                                    <!-- Relationship -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Relationship
                                            <span class="text-danger">*</span>

                                        </label>

                                        <input required
                                            type="text"
                                            name="guardians[0][relationship]"
                                            class="form-control guardian-relationship"
                                            placeholder="Relationship">

                                    </div>


                                    <!-- Primary -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label d-block">

                                            Contact Preference

                                        </label>

                                        <div class="form-check mt-2">

                                            <input
                                                type="checkbox"
                                                class="form-check-input primary-guardian"
                                                name="guardians[0][is_primary]"
                                                value="1">

                                            <label class="form-check-label">

                                                Primary Contact (for emergency)

                                            </label>

                                        </div>

                                    </div>


                                    <!-- First Name -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            First Name
                                            <span class="text-danger">*</span>

                                        </label>

                                        <input
                                            type="text"
                                            name="guardians[0][first_name]"
                                            class="form-control">

                                    </div>


                                    <!-- Middle Name -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Middle Name

                                        </label>

                                        <input
                                            type="text"
                                            name="guardians[0][middle_name]"
                                            class="form-control">

                                    </div>


                                    <!-- Last Name -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Last Name
                                            <span class="text-danger">*</span>

                                        </label>

                                        <input
                                            type="text"
                                            name="guardians[0][last_name]"
                                            class="form-control">

                                    </div>


                                    <!-- Mobile -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Mobile Number
                                            <span class="text-danger">*</span>

                                        </label>

                                        <input
                                            type="text"
                                            name="guardians[0][mobile_no]"
                                            class="form-control">

                                    </div>


                                    <!-- Email -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Email
                                            <span class="text-danger">*</span>

                                        </label>

                                        <input
                                            type="email"
                                            name="guardians[0][email]"
                                            class="form-control">

                                    </div>


                                    <!-- Occupation -->

                                    <div class="col-12 col-md-4">

                                        <label class="form-label">

                                            Occupation

                                        </label>

                                        <input
                                            type="text"
                                            name="guardians[0][occupation]"
                                            class="form-control">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
        
        <!-- =================================================
             currect Address
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

            
                <h5 class="fw-bold mb-1">
                    <i class="fas fa-house text-primary me-2"></i>
                    Current Address
                </h5>

                <small class="text-muted">
                    Student's current residential address
                </small>

            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-12 col-md-4">

                        <label class="form-label">
                            House / Building No.
                        </label>

                        <input
                            type="text"
                            name="current_house_no"
                            id="current_house_no"
                            class="form-control"
                            value="<?= set_value('current_house_no'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label class="form-label">
                            Street
                        </label>

                        <input
                            type="text"
                            name="current_street"
                            id="current_street"
                            class="form-control"
                            value="<?= set_value('current_street'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label class="form-label">
                            Barangay
                        </label>

                        <input
                            type="text"
                            name="current_barangay"
                            id="current_barangay"
                            class="form-control"
                            value="<?= set_value('current_barangay'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label class="form-label">
                            City / Municipality
                        </label>

                        <input
                            type="text"
                            name="current_city"
                            id="current_city"
                            class="form-control"
                            value="<?= set_value('current_city'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label class="form-label">
                            Province
                        </label>

                        <input
                            type="text"
                            name="current_province"
                            id="current_province"
                            class="form-control"
                            value="<?= set_value('current_province'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label class="form-label">
                            Postal Code
                        </label>

                        <input
                            type="text"
                            name="current_postal_code"
                            id="current_postal_code"
                            class="form-control"
                            value="<?= set_value('current_postal_code'); ?>">

                    </div>

                </div>

            </div>

        </div>

        <!-- =================================================
             Check for same Address
        ================================================== -->

        <div class="form-check my-3">
            <input
                class="form-check-input"
                type="checkbox"
                id="permanent_same_as_current"
                name="permanent_same_as_current"
                value="1"
                <?= set_checkbox(
                    'permanent_same_as_current',
                    '1'
                ); ?>>

            <label
                class="form-check-label text-danger"
                for="permanent_same_as_current">

                Permanent address is the same as current address

            </label>

        </div>

        <!-- =================================================
             permanent address
        ================================================== -->
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-1">
                    <i class="fas fa-house text-primary me-2"></i>
                    Permanent Address
                </h5>

                <small class="text-muted">
                    Student's Permanent residential address
                </small>

            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-12 col-md-4">

                        <label for="permanent_house_no" class="form-label">
                            House / Building No.
                        </label>

                        <input
                            type="text"
                            name="permanent_house_no"
                            id="permanent_house_no"
                            class="form-control"
                            value="<?= set_value('permanent_house_no'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label for="permanent_street" class="form-label">
                            Street
                        </label>

                        <input
                            type="text"
                            name="permanent_street"
                            id="permanent_street"
                            class="form-control"
                            value="<?= set_value('permanent_street'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label for="permanent_barangay" class="form-label">
                            Barangay
                        </label>

                        <input
                            type="text"
                            name="permanent_barangay"
                            id="permanent_barangay"
                            class="form-control"
                            value="<?= set_value('permanent_barangay'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label for="permanent_city" class="form-label">
                            City / Municipality
                        </label>

                        <input
                            type="text"
                            name="permanent_city"
                            id="permanent_city"
                            class="form-control"
                            value="<?= set_value('permanent_city'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label for="permanent_province" class="form-label">
                            Province
                        </label>

                        <input
                            type="text"
                            name="permanent_province"
                            id="permanent_province"
                            class="form-control"
                            value="<?= set_value('permanent_province'); ?>">

                    </div>


                    <div class="col-12 col-md-4">

                        <label for="permanent_postal_code" class="form-label">
                            Postal Code
                        </label>

                        <input
                            type="text"
                            name="permanent_postal_code"
                            id="permanent_postal_code"
                            class="form-control"
                            value="<?= set_value('permanent_postal_code'); ?>">

                    </div>

                </div>
            </div>
        </div>

        <!-- =================================================
             Initial Enrollment
        ================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">

                    <i class="fas fa-graduation-cap text-primary me-2"></i>

                    Initial Enrollment

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    <!-- Academic Year -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="academic_year"
                            class="form-label">

                            Academic Year
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            id="academic_year"
                            name="academic_year"
                            class="form-select"
                            required>

                            <option value="">
                                Select Academic Year
                            </option>

                        </select>

                    </div>


                    <!-- Grade -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="grade_level"
                            class="form-label">

                            Grade Level
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="grade_level"
                            id="grade_level"
                            class="form-select"
                            disabled>

                            <option value="">
                                Select Grade Level
                            </option>

                        </select>

                    </div>


                    <!-- Program -->

                    <!-- <div class="col-12 col-md-6 col-lg-3">

                        <label
                            for="program"
                            class="form-label">

                            Program

                        </label>

                        <input
                            type="text"
                            id="program"
                            name="program"
                            class="form-control"
                            value="<?= set_value('program'); ?>"
                            placeholder="e.g. STEM">

                    </div> -->


                    <!-- Section -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="section"
                            class="form-label">

                            Section
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="section"
                            id="section"
                            class="form-select"
                            disabled>

                            <option value="">
                                Select Section
                            </option>

                        </select>

                    </div>


                    <!-- Admission Type -->

                    <div class="col-12 col-md-6 col-lg-4">

                        <label
                            for="admission_type"
                            class="form-label">

                            Admission Type

                        </label>

                        <select
                            id="admission_type"
                            name="admission_type"
                            class="form-select">

                            <option
                                value="New Student"
                                <?= set_select(
                                    'admission_type',
                                    'new',
                                    true
                                ); ?>>

                                New Student

                            </option>

                            <option value="Transferee">

                                Transferee

                            </option>

                            <option value="Returning Student">

                                Returning Student

                            </option>

                        </select>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             Actions
        ================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div
                    class="d-flex
                           flex-column
                           flex-sm-row
                           justify-content-end
                           gap-2">

                    <a
                        href="<?= site_url('students'); ?>"
                        class="btn btn-outline-secondary">

                        Cancel

                    </a>


                    <button
                        type="reset"
                        class="btn btn-light">

                        Reset

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>

                        Register Student

                    </button>

                </div>

            </div>

        </div>



    </form>

</div>

<script>
    const BASE_URL =
        <?= json_encode(base_url()); ?>;
</script>

<script src="<?= base_url('assets/js/student/create.js'); ?>"></script>
<script src="<?= base_url('assets/js/student/field_validation.js'); ?>"></script>