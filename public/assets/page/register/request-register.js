Dropzone.autoDiscover = false;
$(document).ready(function () {
    var correctEmail = false;
    $('#request_register_email').on('mouseleave touchend', e => {
        var email = $('#request_register_email').val();
        if (email.length > 0) {
            var lang = $('#current_lang').data('lang');
            if (lang !== 'de' && email.includes("@")) {
                $.ajax({
                    url: baseUrl + '/email-validator',
                    method: 'post',
                    data: {email: email},
                    dataType: 'JSON',
                    success: function (result) {
                        if (result.success) {
                            correctEmail = true;
                            $('#email-msg').addClass('hidden');
                        } else {
                            correctEmail = false;
                            $('#email-msg').removeClass('hidden');
                            // $('#email-msg').text(result.reply)
                        }
                    }
                });
            }
        } else {
            $('#email-msg').addClass('hidden');
        }

    });


    var regRecaptchaRendered;
    var registerRecaptcha = $('#regRecaptcha');
    setTimeout(function () {
        if (registerRecaptcha.length > 0) {
            regRecaptchaRendered = grecaptcha.render('regRecaptcha', {
                'sitekey': captchaSite,
                'callback': reCaptchaVerify
            });
        }
    }, 300);
    /** Submit register form */
    $(document).on('submit', '#register_form', function (e) {
        e.preventDefault();
        var lang = $('#current_lang').data('lang');
        var region = $('#current_region').data('region');
        var form = $(this);
        /** Send data if form is valid */
        if (formIsValid()) {
            $('#email-msg').addClass('hidden');
            if (lang === 'de' || (lang === 'en' && region === 1)) {
                $.ajax({
                    url: baseUrl + '/email-validator',
                    method: 'post',
                    data: {email: $('#request_register_email').val()},
                    dataType: 'JSON',
                    beforeSend: function () {
                        form.find('#submit-register').addClass('loading');
                    },
                    success: function (result) {
                        if (result.success) {
                            submitRegister(form);
                        } else {
                            showErrorBox(result.reply);
                            form.find('#submit-register').removeClass('loading');
                        }
                    }
                });
            } else {
                submitRegister(form);
            }
        }

    });
    /* Prevent showing the previous steps when clicking enter in the steps different input fields */
    $(document).on('keypress', '.validate', function (e) {
        /** If the keypress is Enter then prevent the action */
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    function submitRegister(form) {
        let utm_source = $('#utm-source').data('utm-source');
        let urlRegister = baseUrl + '/register';
        if (utm_source != '') {
            urlRegister = urlRegister + '?utm_source=' + utm_source;
        }
        $.ajax({
            url: urlRegister,
            method: 'POST',
            data: form.serializeArray(),
            beforeSend: function () {
                form.find('#submit-register').addClass('loading');
            },
            success: function (dataResult) {
                if (dataResult.success) {
                    $('#step-1').remove();
                    $('#step-2').removeClass('hidden');
                    $('body,html').animate({
                        scrollTop: 60
                    }, 800);
                } else {
                    showErrorBox(dataResult.message);
                }
                form.find('#submit-register').removeClass('loading');
            }
        });
    }

    $(document).on('keyup', '#request_register_password_first', function () {
        $('.ps-st-meter-wrapper').removeClass('hide');
        var pwd = $(this).val();
        var passStrengthContainer = $('#ps-st-meter-container');
        var passStrength = zxcvbn(pwd);
        switch (passStrength.score) {
            case 1 :
                passStrengthContainer.removeClass().css('width', '25%').addClass('pwd-bad');
                break;
            case 2 :
                passStrengthContainer.removeClass().css('width', '50%').addClass('pwd-medium');
                break;
            case 3 :
                passStrengthContainer.removeClass().css('width', '75%').addClass('pwd-good');
                break;
            case 4 :
                passStrengthContainer.removeClass().css('width', '100%').addClass('pwd-good');
                break;
            default:
                passStrengthContainer.removeClass().css('width', pwd.length === 0 ? '0%' : '25%').addClass('pwd-bad');
                break;
        }
    });

    /** Validate register form inputs */
    function formIsValid() {

        var step1 = $('#step-1'),
            firstName = $('#request_register_firstname'),
            lastName = $('#request_register_lastname'),
            email = $('#request_register_email'),
            firstField = DOMPurify.sanitize($('#request_register_password_first')),
            confirmationField = DOMPurify.sanitize($('#request_register_password_second'));

        step1.find('.warning').removeClass('warning');
        step1.find('.error').remove();
        var valid = true;

        $.each([firstName, lastName, firstField, confirmationField], function (key, element) {
            if ($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '') {
                $(element).addClass('warning')
                let title = DOMPurify.sanitize($(element).prop('title'));
                $('#continue_registration')
                    .append('<span class="error"  style="position: relative;">' + title + '</span>');
                $('#continue_registration').css("padding", "8px 16px");
                valid = false;
            }
        });
        /** Check email format valid */
        valid = isMailValid(email.val(), $('#continue_registration')) && valid;
        /** Show error msg when user com from es or us*/

        var passStrength = zxcvbn(firstField.val()).score;
        if (firstField.val() !== confirmationField.val() && firstField.val() !== '' && confirmationField.val() !== '') {
            valid = false;
            let resetPWrongText = DOMPurify.sanitize($('#resetPassWrong').text());
            $(confirmationField)
                .addClass('warning')
            $('#continue_registration').append('<span class="error" style="position: relative;">' + resetPWrongText + '</span>');
        } else if (firstField.val().length > 0 && passStrength < 2) {
            let pWrongText = DOMPurify.sanitize(firstField.data('error'));
            $(firstField.val() !== '')
                .addClass('warning')
            $('#continue_registration').append('<span class="error" style="position: relative;">' + pWrongText + '</span>');
            valid = false;
        }

        /** Check if user accepted term & condition */
        var termsAndConditions = $('#confirm_register_tC');
        if (!termsAndConditions.is(':checked')) {
            let termsAcceptText = DOMPurify.sanitize($('#terms_accept').data('text'));
            if ($('#terms_conditions').length === 0)
                $('#continue_registration').append(sanitizeHTML('<span id="terms_conditions" class="error">' + termsAcceptText + '</span>'));
            valid = false;
        }

        if (registerRecaptcha.length > 0 && !checkReCaptcha(regRecaptchaRendered)) {
            let errorCaptchaText = DOMPurify.sanitize($('#error_captcha').text());
            $('#continue_registration').append(sanitizeHTML('<span class="error" style="position: relative">' + errorCaptchaText + '</span>'));
            valid = false;
        }

        return valid;
    }

    /** Trigger popover */
    $('[data-toggle="popover"]').popover().on('show.bs.popover', function () {
        var currentPopover = $(this);
        $.each($('.popover.in'), function () {
            if (currentPopover !== $(this))
                $(this).prev().click();
        })
    });

    /** Close popover */
    $('body').on('click touch', function (e) {
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }

    });
});

