(function () {
    const yearSelect = document.getElementById('subjectSchoolYear');
    const gradeSelect = document.getElementById('subjectGrade');
    if (!yearSelect || !gradeSelect) return;

    function updateGrades() {
        const year = yearSelect.value;
        let firstAvailable = null;
        Array.from(gradeSelect.options).forEach(function (option) {
            if (!option.value) return;
            const available = option.dataset.schoolYear === year;
            option.disabled = !available;
            option.hidden = !available;
            if (available && !firstAvailable) firstAvailable = option;
        });

        const selectedOption = gradeSelect.options[gradeSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value || selectedOption.disabled) {
            gradeSelect.selectedIndex = firstAvailable ? firstAvailable.index : 0;
        }
    }

    yearSelect.addEventListener('change', updateGrades);
    updateGrades();
})();
