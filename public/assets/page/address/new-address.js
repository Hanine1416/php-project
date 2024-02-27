/** Submit add address form */
$(document).on('submit', '#add-address-form:not(.valid-form)', function (e) {
    e.preventDefault();
    var fields = $(this).find('.validate');
    fields.removeClass('warning');
    var charFields = $(this).find('[data-max-length]');
    charFields.removeClass('warning');
    $(this).find('.error').remove();
    var valid = true;

    /** Validate all fields */
    $.each(fields, function (key, element) {
        if (($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '') && $(element).is(':visible') && !$(element).hasClass('empty')) {
            let title = DOMPurify.sanitize($(element).prop('title'));
            $(element)
                .addClass('warning')
                .parent().append('<span class="error" style="position: relative;">' + title + '</span>');
            valid = false;
        }
    });

    /** If form valid then resubmit it */
    if (valid) {
        $('#add-address-btn').addClass('loading');
        $(this).addClass('valid-form').submit();
    }
});


$(document).ready(function () {
    /** Init country selector with select2 plugin */
    var countrySelector = $('#address_country');
    countrySelector.select2({
        placeholder: '',
        dropdownParent: countrySelector.parent()
    });

    /** When change the country reset all address input */
    countrySelector.on('change', function () {
        var fields = $(this).closest('form').find('input');
        $.each(fields, function (key, elem) {
            $(elem).val("");
        });
    });

});

/** Prevent user to enter more then "max-length" character */
$(document).on('keypress', '[data-max-length]', function (e) {
    if ($(this).val().length > $(this).data('max-length'))
        e.preventDefault();
});