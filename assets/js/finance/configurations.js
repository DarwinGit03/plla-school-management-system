document.addEventListener('DOMContentLoaded', function () {
    const sourceYear = document.getElementById('duplicate-source-year');
    const targetYear = document.getElementById('duplicate-target-year');

    if (!sourceYear || !targetYear) return;

    function updateTargetYears() {
        Array.from(targetYear.options).forEach(function (option) {
            if (!option.value) return;
            option.disabled = option.value === sourceYear.value;
            if (option.selected && option.disabled) targetYear.value = '';
        });
    }

    sourceYear.addEventListener('change', updateTargetYears);
    updateTargetYears();
});
