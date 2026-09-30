document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.querySelector('form[action*="students/section-assignments"]');
    const yearSelect = document.getElementById('assignmentYear');
    const gradeSelect = document.getElementById('assignmentGrade');

    // Refresh the server-rendered choices as each level is selected.
    // This enables the next select immediately after the page reloads.
    if (filterForm && yearSelect && gradeSelect) {
        yearSelect.addEventListener('change', function () {
            gradeSelect.value = '';
            filterForm.requestSubmit();
        });

        gradeSelect.addEventListener('change', function () {
            filterForm.requestSubmit();
        });
    }

    const selectAll = document.getElementById('selectAllStudents');
    const checkboxes = Array.from(document.querySelectorAll('.student-assignment-checkbox'));
    const count = document.getElementById('selectedStudentCount');
    const submit = document.getElementById('assignSelectedButton');
    const submitText = document.getElementById('assignSelectedButtonText');
    const targetSection = document.getElementById('assignmentTargetSection');
    const form = document.getElementById('sectionAssignmentForm');

    if (!selectAll || !count || !submit || !targetSection || !form) return;

    function updateSelection() {
        const selectable = checkboxes.filter(function (checkbox) { return !checkbox.disabled; });
        const selectedCheckboxes = selectable.filter(function (checkbox) { return checkbox.checked; });
        const selected = selectedCheckboxes.length;
        const hasAssigned = selectedCheckboxes.some(function (checkbox) {
            return checkbox.dataset.hasSection === '1';
        });
        const hasUnassigned = selectedCheckboxes.some(function (checkbox) {
            return checkbox.dataset.hasSection !== '1';
        });
        count.textContent = String(selected);
        submitText.textContent = hasAssigned && hasUnassigned
            ? 'Assign / update selected'
            : (hasAssigned ? 'Update selected' : 'Assign selected');
        targetSection.disabled = selectable.length === 0 || selected === 0;
        submit.disabled = selected === 0 || targetSection.value === '';
        selectAll.disabled = selectable.length === 0;
        selectAll.checked = selectable.length > 0 && selected === selectable.length;
        selectAll.indeterminate = selected > 0 && selected < selectable.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) {
            if (!checkbox.disabled) checkbox.checked = selectAll.checked;
        });
        updateSelection();
    });

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSelection);
    });

    targetSection.addEventListener('change', updateSelection);

    form.addEventListener('submit', function (event) {
        const selected = checkboxes.filter(function (checkbox) { return !checkbox.disabled && checkbox.checked; }).length;
        if (selected === 0 || targetSection.value === '' || !window.confirm('Assign or move ' + selected + ' selected student(s) to this section?')) {
            event.preventDefault();
        }
    });

    updateSelection();
});
