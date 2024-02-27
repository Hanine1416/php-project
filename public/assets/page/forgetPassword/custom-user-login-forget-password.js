
$(document).ready(function () {
    var fpRecaptcha = $('#reset-password-recaptcha');
    var fpRecaptchaOn = !fpRecaptcha.hasClass('hide');
    var fpReCaptchaRendered=false;

    var loginModal = $('.login-modal');
    /** check if recaptcha is activated */
    setTimeout(function () {
        if(fpRecaptchaOn){
            fpReCaptchaRendered = renderFpRecaptchaOn();
        }
    },1000);

    // Reset password submit action
    var form = $('#custom-forget-password');
    var emailDiv = $('#forget-password-email-field');
    var email = form.find('#email');
    var modal = $('.login-modal');
    form.submit(function (e) {
        var formValid =true;
        e.preventDefault();
        email.removeClass('warning');
        var button = form.find('.log-forgot-custom');
        var emailToSend = email.val();
        form.find('.error').remove();
        if (fpRecaptchaOn && !checkReCaptchaRP(fpReCaptchaRendered)) {
            showErrorBox($('#error_captcha').text());
            formValid = false;
        }
        formValid = isMailValid(emailToSend, emailDiv) && formValid;
        var successScreen = modal.find('.success-email');
        if (formValid && !form.hasClass('loading')) {
            button.addClass('loading');
            modal.addClass('readOny');
            $.post(form.attr('action'), form.serializeArray())
                .done(function (data) {
                    if (data.result) {
                        /** reset reCaptcha */
                        if (fpRecaptchaOn) {
                            fpRecaptchaOn=false;
                            fpRecaptcha.addClass('hide');
                            grecaptcha.reset(fpReCaptchaRendered);
                        }else {
                            if (data.recaptcha === true){
                                fpRecaptchaOn=true;
                                fpRecaptcha.removeClass('hide');
                                if(fpReCaptchaRendered===false)
                                    fpReCaptchaRendered = renderFpRecaptchaOn();
                            }
                        }
                    }
                    email.val("");
                    button.removeClass('loading');
                    modal.removeClass('readOnly');
                    $('#forget-content').addClass('hidden');
                    successScreen.removeClass('hidden');
                })
                .fail(function (e) {
                    showErrorBox($('#error').text());
                    button.removeClass('loading');
                    modal.removeClass('readOnly');
                });
        }
        /** remove error/class warning from inputs form on change */
        email.keyup(function () {
            if ($(this).val() !== null && $(this).val() !== 'undefined' && $(this).val() !== '') {
                if ($(this).hasClass('warning')) {
                    $(this).removeClass('warning');
                }
                emailDiv.find('.error').remove();
            }
            isMailValid(email.val(), emailDiv);
        });
    });
    
    /** reset recaptcha when close modal */
    $('#close-login-modal,#back-login').on('click',function () {
            grecaptcha.reset(fpReCaptchaRendered);
    });

});

function renderFpRecaptchaOn() {
    return grecaptcha.render('fpRecaptcha', {
        'sitekey': captchaSite,
        'callback': reCaptchaVerify,
    });
}
function checkReCaptchaRP(reCaptcha) {
    var v = grecaptcha.getResponse(reCaptcha);
    return v.length !== 0;
}
