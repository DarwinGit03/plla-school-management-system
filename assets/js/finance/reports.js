$(function () {
    const form = $('#report-school-year').closest('form');

    $('#report-school-year').on('change', function () {
        $('#report-grade, #report-section').val('');
        form.trigger('submit');
    });

    $('#report-grade').on('change', function () {
        $('#report-section').val('');
        form.trigger('submit');
    });
});
