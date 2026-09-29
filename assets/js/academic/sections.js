document.addEventListener('DOMContentLoaded', function () {
    const optionsNode = document.getElementById('sectionGradeOptions');
    const form = document.getElementById('sectionForm');
    const modalNode = document.getElementById('sectionModal');
    const yearSelect = document.getElementById('sectionYear');
    const gradeSelect = document.getElementById('sectionGrade');
    const modal = modalNode ? bootstrap.Modal.getOrCreateInstance(modalNode) : null;

    if (!optionsNode || !form || !modal || !yearSelect || !gradeSelect) return;

    let gradeOptions = {};
    try {
        gradeOptions = JSON.parse(optionsNode.textContent || '{}');
    } catch (error) {
        console.error('Unable to load configured grade options.', error);
    }

    function populateGrades(year, selectedGrade) {
        const grades = gradeOptions[year] || [];
        gradeSelect.replaceChildren();

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = grades.length ? 'Select grade' : 'No configured grades';
        gradeSelect.appendChild(placeholder);
        grades.forEach(function (grade) {
            const option = document.createElement('option');
            option.value = grade;
            option.textContent = 'Grade ' + grade;
            option.selected = String(grade) === String(selectedGrade || '');
            gradeSelect.appendChild(option);
        });

        gradeSelect.disabled = grades.length === 0;
    }

    function resetForm() {
        form.reset();
        document.getElementById('sectionId').value = '';
        document.getElementById('sectionModalTitle').textContent = 'Add section';
        document.getElementById('sectionName').value = '';
        document.getElementById('sectionStatus').value = 'Active';
        const currentYear = document.getElementById('filterYear').value;
        yearSelect.value = currentYear;
        populateGrades(currentYear, document.getElementById('filterGrade').value);
    }

    document.getElementById('addSectionButton').addEventListener('click', function () {
        resetForm();
        modal.show();
    });

    yearSelect.addEventListener('change', function () {
        populateGrades(yearSelect.value, '');
    });

    document.querySelectorAll('.edit-section-button').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('sectionId').value = button.dataset.id;
            document.getElementById('sectionModalTitle').textContent = 'Edit section';
            document.getElementById('sectionName').value = button.dataset.section || '';
            document.getElementById('sectionStatus').value = button.dataset.status || 'Active';
            yearSelect.value = button.dataset.year || '';
            populateGrades(yearSelect.value, button.dataset.grade || '');
            modal.show();
        });
    });

    document.querySelectorAll('[data-confirm-status]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            const action = button.dataset.confirmStatus;
            if (!window.confirm('Are you sure you want to ' + action + ' this section?')) {
                event.preventDefault();
            }
        });
    });
});
