$(document).ready(function () {
    function passwordReset(btn) {
        var reset = $('.block');
        reset.find('.warning').removeClass('warning');
        reset.find('.error').remove();
        var form = btn.closest('form');
        var valid = true;
        if ( $('#change_password_form_oldPassword').length > 0 ) {
           var oldField= $('#change_password_form_oldPassword'),
                firstField = $('#change_password_form_password_first'),
                confirmField = $('#change_password_form_password_second'),
           fieldsValidate = [oldField,firstField,confirmField ];
        } else {
            var firstField = $('#resetting_form_password_first'),
                confirmField = $('#resetting_form_password_second'),
                fieldsValidate = [firstField,confirmField ];
        }
        $.each(fieldsValidate, function (index, element) {
            if ($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '') {
                let title =  DOMPurify.sanitize($(element).prop('title'));
                $(element)
                    .addClass('warning')
                    .parent().append('<span class="error" style="position: relative;">' + title + '</span>');
                valid = false;
            }
        });
        if (registerRecaptcha.length > 0 && !checkReCaptcha(regRecaptchaRendered)) {
            let recaptchaSanitize = DOMPurify.sanitize($('#error_captcha').text());
            registerRecaptcha.parent().append('<span class="error">' + recaptchaSanitize + '</span>');
            valid = false;
        }

        var passStrength = (firstField.val()).score;
        if (firstField.val() !== confirmField.val() && firstField.val() !== '' && confirmField.val() !== '') {
            valid = false;
            let resetPassWrong = DOMPurify.sanitize($('#resetPassWrong').text());
            $(confirmField)
                .addClass('warning')
                .parent().append(sanitizeHTML('<span class="error" style="position: relative;">' + resetPassWrong + '</span>'));
        } else if (firstField.val().length > 0 && passStrength < 2) {
            let errorText = DOMPurify.sanitize(firstField.data('error'));
            $(firstField)
                .addClass('warning')
                .parent().append(DOMPurify.sanitize('<span class="error" style="position: relative;">' + errorText + '</span>'));
            valid = false;
        }

        if (valid) {
            if ( $('#change_password_form_oldPassword').length > 0) {
                var data = form.serializeArray();
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: data,
                    dataType: 'JSON',
                    beforeSend: function () {
                        btn.addClass('loading');
                    },
                    success: function (response) {
                        if (response.success) {
                            window.location= form.attr('action');
                        } else
                            showErrorBox(response.message);
                            btn.removeClass('loading');
                            $('.updatePassword').removeClass('disableHref');
                         }
                    });
            }
             else
            btn.closest('form').submit();
        } else {
            $('.updatePassword').removeClass('disableHref');
        }
    }

    $('#reset-account-pass-btn').on('click', function (e) {
        e.preventDefault();
        $('.updatePassword').addClass('disableHref');
        passwordReset($(this));
    });

    $('.resetArea input.password').on('keyup', function () {
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

});
