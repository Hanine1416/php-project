$(document).ready(function () {
    /** init all need elements */
    var form = $('#contact_form'),
        email = $('#contact_us_email'),
        phone = $('#contact_us_phone'),
        country = $('#contact_us_country'),
        institution = $('#institutions'),
        otherInstitution = $('input#other_institution'),
        otherInstitutionBtn = $('#btn-other-institution'),
        institutionBlock = $('#contact_institution_block'),
        realInstitutionInput = $('#contact_us_institution'),
        spam = $('#more_details'),
        subject = $('#contact_us_subject');

    var recaptchaRendered;
    var recaptcha = $('#recaptcha');
    setTimeout(function () {
        recaptchaRendered = grecaptcha.render('recaptcha', {
            'sitekey': captchaSite,
            'callback': reCaptchaVerify
        });
    },2000);

    country.select2({
        placeholder:"",
        dropdownParent:country.parent()
    });

    institution.select2({
        placeholder:"",
        dropdownParent:institution.parent()
    });

    subject.select2({
        placeholder:"",
        dropdownParent:subject.parent()
    });

    /** load institutions by country from ajax request call SalesLogix API */
    function loadInstitutions(country, url) {
        /** remove all institution old data */
        institution.find('option:not([disabled])').remove();
        institution.addClass('s2-loading-data');
        /** ajax call */
        $.post(url, {
            country: country
        }, function (data) {
            /** remove all error before hide an show */
            institutionBlock.find('.warning').removeClass('warning');
            institutionBlock.find('.error').remove();
            /** test if there is any returned institution */
            if (data.status === true) {
                if (institution.length > 0) {
                    /** Hide other institution option */
                    institution.next().show();
                    otherInstitution.hide();
                    otherInstitutionBtn.show();
                    otherInstitutionBtn.text(otherInstitutionBtn.data('other'));
                    /** Add all institutions to select as option  and select user institution if found*/
                    var notFoundId = true;
                    for (var key in data.results) {
                        var $institutionOption = new Option();
                        $institutionOption.text = data.results[key]['name'];
                        $institutionOption.value = data.results[key]['name'];
                        $institutionOption.selected = realInstitutionInput.data('preselect-id') === key;
                        if (realInstitutionInput.data('preselect-id') === key)
                            notFoundId = false;
                        institution.append(DOMPurify.sanitize($institutionOption));
                    }
                    if(Object.keys(data.results).length>200 && isIE()){
                        institution.select2({minimumInputLength:2});
                    }
                    if (notFoundId && (realInstitutionInput.data('preselect-name') !== undefined && realInstitutionInput.data('preselect-name').length > 0)) {
                        institution.next().hide();
                        otherInstitution.show();
                        otherInstitution.val(realInstitutionInput.data('preselect-name'));
                        otherInstitutionBtn.text(otherInstitutionBtn.data('cancel'));
                        otherInstitutionBtn.show();
                        realInstitutionInput.data('preselect-name', "");
                    }
                    realInstitutionInput.data('preselect-id', "");
                }
            } else {
                /** if there is no available institution for chosen country then show other option */
                institution.next().hide();
                otherInstitution.show();
                otherInstitution.val(realInstitutionInput.data('preselect-name'));
                otherInstitutionBtn.hide();
                realInstitutionInput.data('preselect-name', "");
            }
            institution.removeClass('s2-loading-data');
        });
    }

    /**  change institution list when changing the country */
    country.change(function () {
        var url = $(this).data('institutions') + '/institutions';
        loadInstitutions($(this).find('option:selected').text(), url);
        /** validate phone number if not empty */
        if (phone.val().length > 0)
            validatePhoneNumber(phone, $(this).find('option:selected').data('st'));
    });

    otherInstitutionBtn.on('click', function () {
        /** remove all error before hide an show */
        institutionBlock.find('.warning').removeClass('warning');
        institutionBlock.find('.error').remove();
        if (institution.next().is(':visible')) {
            institution.next().hide();
            institution.find('option[disabled]').prop('selected', true);
            otherInstitution.show();
            $(this).text(otherInstitutionBtn.data('cancel'))
        } else {
            institution.next().show();
            otherInstitution.val("").hide();
            $(this).text(otherInstitutionBtn.data('other'))
        }
    });

    /** add placeholder for all select **/
    $.each(form.find('select'), function (key, element) {
        var placeholder = DOMPurify.sanitize($(element).attr('placeholder'));
        var select = $(this).find('option[selected="selected"]').length > 0 ? '' : 'selected';
        $(element).prepend('<option data-real="'+placeholder+'" disabled ' + select + '>' + placeholder + '</option>')
    });

    /** check if country has preselected data from current user then load it's institutions */
    if (country.val() !== null) {
        country.trigger('change');
    }

    /** remove error/class warning from inputs form on change */
    email.keyup(function () {
        if ($(this).val() !== null && $(this).val() !== 'undefined' && $(this).val() !== '') {
            if ($(this).hasClass('warning')) {
                $(this).removeClass('warning');
            }
            $(this).parent().find('.error').remove();
        }
        isMailValid(email.val(), $(this).parent());
    });
    /** trying to submit contact form */
    $('#submitContact').on('click', function (e) {
        e.preventDefault();
        /** checking required field that are empty */
        var fields = form.find('.validate');
        fields.removeClass('warning');
        form.find('.error').remove();
        var valid = true;
        /** validate all fields */
        $.each(fields, function (key, element) {
            if (($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '') &&
                ( ($(element).is(':visible') && !$(element).is('select')) || ($(element).is('select') && $(element).next().is(':visible')) ) && !$(element).hasClass('empty')) {
               let title = DOMPurify.sanitize($(element).prop('title'));
                $(element)
                    .addClass('warning')
                    .parent().append('<span class="error">' + title + '</span>');
                valid = false;
            }
        });

        if ( spam.val() != null && spam.val() != 'undefined' && spam.val() != '' ) {
            valid = false;
        }

        /** validate email **/
        var emailValid = isMailValid(email.val(), email.parent());
        valid = valid && emailValid;

        /** validate phone number if not empty */
        if (phone.val() && country.val()) {
            var phoneValid = validatePhoneNumber(phone, country.find('option:selected').data('st'));
            valid = valid && phoneValid;
        } else {
            /** if the phone number is empty remove all error */
            phone.removeClass('warning').parent().find('error').remove();
        }

        /** Check recaptcha is valid */
        if (recaptcha.length > 0 && !checkReCaptcha(recaptchaRendered)) {
            let recaptchaText =  DOMPurify.sanitize($('#error_captcha').text());
            recaptcha.parent().append('<span class="error">' + recaptchaText + '</span>');
            valid = false;
        } else {
            $('#recaptcha').parent().find('.error').remove();
        }


        /** if all fields are valid then submit the form */
        if (valid) {
            /** check if other institution is chosen then copy data to real institution input */
            if (otherInstitution.is(':visible')) {
                realInstitutionInput.val(otherInstitution.val());
                otherInstitution.prop('disabled', true);
            } else {
                realInstitutionInput.val(institution.val());
                institution.prop('disabled', true);
            }
            form.submit();
        }

        /** if required input change and become empty then show error messages */
        $('input:not(#contact_us_email).validate,select.validate,textarea.validate').on('keyup change ', function () {
            if ($(this).val()) {
                $(this).removeClass('warning').parent().find('.error').remove();
            } else {
                let title = DOMPurify.sanitize($(this).prop('title'));
                if (!$(this).hasClass('warning'))
                    $(this).addClass('warning')
                        .parent().append('<span class="error">' + title + '</span>');
            }
        });
    });


    /** remove number error when is empty */
    phone.on('keyup', function (e) {
        if (!$(this).val())
            $(this).removeClass('warning').parent().find('.error').remove();
        else if (country.val())
            validatePhoneNumber(phone, country.find('option:selected').data('st'));
    });

    /** keep watching input that has max length attribute */
    $('[data-maxlength]').on('keypress', function (e) {
        var maxLength = $(this).data('maxlength');
        var elem = $(this);
        if (elem.val().length === maxLength) {
            e.preventDefault();
            elem.addClass('warning');
            setTimeout(function () {
                elem.removeClass('warning')
            }, 2000)
        }
    });

    /** responsive fix form*/
    $(window).on('resize load', function () {
        /** 575 px is when the form is not good looking :D */
        if ($(this).width() < 575) {
            /** transform each label into a placeholder */
            $.each($('form .input-field'), function (i, o) {
                var label = $(o).find('label');
                var input = $(o).find('input');
                var select = $(o).find('select');
                var textArea = $(o).find('textArea');
                $.each([textArea, select, input], function (j, element) {
                    if ($(element).length > 0) {
                        /** if the element is a select then the case is a bit different */
                        if ($(element).is('select')) {
                            var option = $(element).find('option:disabled');
                            $(option).text(label.text());
                        }
                      /*  $(element).prop('placeholder', label.text())*/
                    }
                });
              /*  label.addClass('hidden');*/
            });
        } else {
            /** reset form normal status */
            $.each($('form .input-field'), function (i, o) {
                var label = $(o).find('label');
                var input = $(o).find('input');
                var select = $(o).find('select');
                var textArea = $(o).find('textArea');
                $.each([textArea, select, input], function (j, element) {
                    if ($(element).length > 0) {
                        /** if the element is a select then the case is a bit different */
                        if ($(element).is('select')) {
                            var option = $(element).find('option:disabled');
                            $(option).text($(option).data('real'));
                        } else
                            $(element).prop('placeholder', '')
                    }
                });
                label.removeClass('hidden');
            });
        }
    })
});
