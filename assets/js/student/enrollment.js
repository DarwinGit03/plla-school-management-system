document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.js-enrollment-search');
    if (searchForm) {
        const yearSelect = searchForm.querySelector('[name="academic_year"]');
        const gradeSelect = searchForm.querySelector('[name="grade_level"]');
        const sectionSelect = searchForm.querySelector('[name="section"]');
        const gradeUrl = searchForm.dataset.gradeUrl;
        const sectionUrl = searchForm.dataset.sectionUrl;
        let gradeRequest = 0;
        let sectionRequest = 0;

        function resetSelect(select, label) {
            select.replaceChildren(new Option(label, ''));
        }

        async function loadGrades(year) {
            const requestId = ++gradeRequest;
            ++sectionRequest;
            resetSelect(gradeSelect, 'All grades');
            resetSelect(sectionSelect, 'All sections');
            gradeSelect.disabled = true;
            sectionSelect.disabled = true;

            if (!year) return;

            try {
                const response = await fetch(gradeUrl + '?year=' + encodeURIComponent(year));
                if (!response.ok || requestId !== gradeRequest) return;

                const grades = await response.json();
                grades.forEach(function (grade) {
                    gradeSelect.add(new Option(grade.grade, grade.grade));
                });
                gradeSelect.disabled = false;
            } catch (error) {
                gradeSelect.disabled = true;
            }
        }

        async function loadSections(year, grade) {
            const requestId = ++sectionRequest;
            resetSelect(sectionSelect, 'All sections');
            sectionSelect.disabled = true;

            if (!year || !grade) return;

            try {
                const url = sectionUrl
                    + '?year=' + encodeURIComponent(year)
                    + '&grade=' + encodeURIComponent(grade);
                const response = await fetch(url);
                if (!response.ok || requestId !== sectionRequest) return;

                const sections = await response.json();
                sections.forEach(function (section) {
                    sectionSelect.add(new Option(section.section, section.section));
                });
                sectionSelect.disabled = false;
            } catch (error) {
                sectionSelect.disabled = true;
            }
        }

        yearSelect.addEventListener('change', function () {
            loadGrades(yearSelect.value);
        });

        gradeSelect.addEventListener('change', function () {
            loadSections(yearSelect.value, gradeSelect.value);
        });
    }

    const setupForm = document.querySelector('.js-enrollment-setup');
    if (setupForm) {
        setupForm.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                setupForm.submit();
            });
        });
    }
});
