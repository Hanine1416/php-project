var institutionsData = [];
var cities = null;
var searchPlaceholder = $('#search-placeholder').text();
var clickCount = 0;
var click = 0;
var source = '';
$.fn.select2.defaults.set('searchInputPlaceholder', searchPlaceholder);
var currentLang = "en";
var current_region = "7";
var currentCountry = $('#current_country').data('country').toLowerCase();
var introText = $('#virtual-tour-intro').text(),
    step1Text = $('#virtual-tour-step1').text(),
    step2Text = $('#virtual-tour-step2').text(),
    step3Text = $('#virtual-tour-step3').text(),
    step4Text = $('#virtual-tour-step4').text(),
    step5Text = $('#virtual-tour-step5').text(),
    nextBtnText = $('#virtual-tour-next').text(),
    skipBtnText = $('#virtual-tour-skip').text(),
    doneBtnText = $('#virtual-tour-done').text(),
    backBtnText = $('#virtual-tour-back').text();

/** Intro virtual tour */
var homePageGuide = introJs(),
    homePageGuideUSANZ = introJs();

/** If is mobile */
/* Test if user first login start driver plugin*/
if ($(window).width() <= 1025) {
    var catalogSwitcher = '.mobile-catalog-switcher .catalog-links ul';
} else {
    catalogSwitcher = '.desktop-catalog-switcher .catalog-links';
}
/** Homepage guide for countries EN,FR,ES and DE */
homePageGuide.setOptions({
    nextLabel: nextBtnText + ' →',
    skipLabel: skipBtnText,
    prevLabel: '← ' + backBtnText,
    doneLabel: doneBtnText,
    exitOnOverlayClick: false,
    steps: [
        {
            element: '.discover-block',
            intro: introText,
            position: 'auto'
        },
        {
            element: '.dropdown-notif',
            intro: step1Text,
            position: 'auto'
        },
        {
            element: '.profile',
            intro: step2Text,
            position: 'auto'
        },
        {
            element: catalogSwitcher,
            intro: step3Text,
            position: 'auto'
        },
        {
            element: '#language-switcher',
            intro: step4Text,
            position: 'auto'
        },
        {
            element: '#click-here',
            intro: step5Text,
            position: 'auto',
            highlightClass: '#e6782b'
        }
    ]
});
/** Homepage guide for countries US,ANZ and India */
homePageGuideUSANZ.setOptions({
    nextLabel: nextBtnText + ' →',
    skipLabel: skipBtnText,
    prevLabel: '← ' + backBtnText,
    doneLabel: doneBtnText,
    exitOnOverlayClick: false,
    steps: [
        {
            element: '.discover-block',
            intro: introText,
            position: 'bottom'
        },
        {
            element: '.dropdown-notif',
            intro: step1Text,
            position: 'bottom'
        },
        {
            element: '.profile',
            intro: step2Text,
            position: 'bottom'
        },
        {
            element: '#click-here',
            intro: step5Text,
            position: 'bottom',
            backgroundColor_: '#ffff',
        }
    ]
});

var catalogueLang = DOMPurify.sanitize(Cookies.get('site-lang'));
var userPhase = $('.discover-block').data('user-phase');
var langCataSwitcher = $('.switcher-container');
var updateDone = false;


if (langCataSwitcher.length > 0 && userPhase === 'first login') {
    // homePageGuide.start();
    click = 3;
} else if (langCataSwitcher.length === 0 && userPhase === 'first login') {
    // homePageGuideUSANZ.start();
    click = 3;
}

/** Update guide phase in user table */
function updateGuide(phase) {
    $.ajax({
        url: $('.discover-block').data('update-guide-url'),
        method: 'POST',
        data: {phase: phase},
        success: function (response) {
            if (response.success) {
                updateDone = true;
            }
        }
    });
}

/* sanitize Data */
function sanitizeHTML(html) {
// Define the allowed tags and attributes
    if (typeof html !== 'string') {
        return html; // Return the input as is if it's not a string
    }
// Define the allowed tags and attributes
    const allowedTags = ['p', 'a', 'b', 'i', 'em', 'strong', 'br','input','span','li','ul','div','img'];
    const allowedAttributes = ['href'];
// Create a regular expression pattern to match allowed tags and attributes
    const tagPattern = new RegExp(`<(${allowedTags.join('|')})[^>]*>`, 'gi');
    const attributePattern = new RegExp(`(\\s(${allowedAttributes.join('|')})="[^"]*")`, 'gi');
// Remove disallowed tags and attributes
    html = html.replace(tagPattern, '');
    html = html.replace(attributePattern, '');
    return html;
}
/** Notification box **/
function growl(success, message) {
    return;
    var successTitle = $('#success').text();
    var errorTitle = $('#error').text();
    if (success) {
        $.growl.notice({
            title: successTitle,
            duration: 10000,
            delayOnHover: true,
            message: message
        });
    } else {
        $.growl.error({
            title: errorTitle,
            duration: 10000,
            delayOnHover: true,
            message: message
        });
    }
}

/**
 * Validate url input
 * @return {boolean}
 */
function ValidURL(str) {
    var urlRegex = '^(?!mailto:)(?:(?:http|https|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?$';
    var url = new RegExp(urlRegex, 'i');
    return str.length < 2083 && url.test(str);
}

/** Return url parameters */
function urlParam(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return (results) ? results[1] : null;
}

/** Validate phone number for a specific country **/
function validatePhoneNumber(phone, region) {
    phone.removeClass('warning').next('.error').remove();
    const phoneNumber = phone.val();
    try {
        const parsedNumber = libphonenumber.parsePhoneNumberFromString(phoneNumber, region);
        const isValid = parsedNumber && parsedNumber.isValid();
        console.log(parsedNumber);
        if ((isValid) && (parsedNumber.country === region)) {
            phone.removeClass('warning');
        } else {
            let wrongPhone = DOMPurify.sanitize( phone.data('wrong'));
            phone
                .addClass('warning')
                .parent().append('<span class="error">' + wrongPhone + '</span>');
        }
        return isValid;
    } catch (error) {
        console.error('Error validating phone number:', error);
        return  false;
    }
}
/** Check if recaptcha is verified **/
function reCaptchaVerify(response) {
    if (response === document.querySelector('.g-recaptcha-response').value) {
        doSubmit = true;
    }
}

function reCaptchaCallback() {
}

/** Check email validity */
function isMailValid(email, appendTo) {
    email = sanitizeHTML(email);
    appendTo =sanitizeHTML(appendTo);
    var error1103 = DOMPurify.sanitize($('#error_1103').text());
    var error1074 = DOMPurify.sanitize($('#error_1074').text());

    var alreadyError = appendTo.find('.error');
    var emailValidation = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
    if (email.length < 1) {
        if (alreadyError.length && appendTo.selector !== '#continue_registration')
            alreadyError.text($('#error_1103').text());
        else {
            if (appendTo.selector === '#continue_registration') {
                appendTo.append('<span class="error"  style="position: relative;">' + error1103 + '</span>');
                appendTo.css('padding', '8px 16px');
                $('#request_register_email').addClass('warning');
            } else {
                appendTo.append('<span class="error">' + error1103 + '</span>');
                appendTo.find('input').addClass('warning');
            }
            return false;
        }
    } else if (!emailValidation.test(email) && email.length > 0) {
        if (alreadyError.length && appendTo.selector !== '#continue_registration')
            alreadyError.text($('#error_1074').text());
        else {
            if (appendTo.selector === '#continue_registration') {
                appendTo.append('<span class="error"  style="position: relative;">' + error1074 + '</span>');
                appendTo.css('padding', '8px 16px');
                $('#request_register_email').addClass('warning');
            } else {
                appendTo.append('<span class="error">' + error1074 + '</span>');
                appendTo.find('input').addClass('warning');
            }
            return false;
        }
    } else {
        appendTo.find('input').removeClass('warning');
        if (appendTo.selector !== '#continue_registration')
            appendTo.find('.error').remove();
    }
    return true;
}

/** Close modal **/
function closeModal(modal) {
    /** remove all validation errors */
    modal.find('.error').remove();
    modal.find('.warning').removeClass('warning');
    modal.fadeOut(200, function () {
        modal.removeClass('active');
    });
    modalFix(function () {
        $('body,html').removeClass('modal-open scrollable');
    });
}

/** Show Login / forget password modal **/
function showLoginBox() {
    $('.login-modal #login-content').removeClass('hidden');
    $('.login-modal #forget-content').addClass('hidden');
    $('.login-modal #register-content').addClass('hidden');
    $('.login-modal .success-email').addClass('hidden');
    $('.login-modal').addClass('active').fadeIn(200, function () {
        modalFix(function () {
            $('body,html').addClass('modal-open');
        });
    });
}

/** Check if the field is Valid */
function checkFieldEmpty($field) {
    if ($field.val() !== null && $field.val() !== 'undefined' && $field.val() !== '') {
        if ($field.hasClass('warning')) {
            $field.removeClass('warning');
        }
        $field.parent().find('.error').remove();
    }
}

/** Login action */
function login(button) {
    var formValid = true;
    var form = button.closest('form');
    var url = form.prop('action');
    var email = form.find('.username');
    var password = form.find('.password');
    var rememberMe = form.find('#_remember_me');
    var modal = $('.login-modal');
    /** Validate form */
    $.each([email, password], function (i, element) {
        /** Remove previews error */
        element.removeClass('warning');
        element.parent().find('.error').remove();
        /** Check if the input is empty */
        if (element.val().length === 0) {
            let title = DOMPurify.sanitize($(element).prop('title'));
            $(element).addClass('warning').parent().append('<span class="error">' + title + '</span>');
            formValid = false;
        }
    });
    /** Validate email */
    var emailValid = isMailValid(email.val(), email.parent());
    formValid = formValid && emailValid;

    /** If the form is valid then do the ajax request for authentication */
    if (formValid) {
        $.ajax({url: baseUrl + '/clear-books-session', success: function(result){}});
        var data = {username: email.val(), password: password.val()};
        rememberMe.prop('checked') ? data.remember = 'on' : data.remember = null;
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            dataType: 'JSON',
            beforeSend: function () {
                modal.addClass('readOnly');
                button.addClass('loading')
            },
            success: function (data) {
                if (data.success) {
                     if (!window.location.pathname.includes('/book/details')){
                        window.location = DOMPurify.sanitize(data.redirect);
                    }
                    else{
                        location.reload();
                    }
                } else {
                    if (data.reply !== 'undefined' && data.reply === "1105") {
                        let errorText =  DOMPurify.sanitize($('#error_1105').text());
                        var box = showErrorBox(errorText);
                        box.find('#box-ok').text($('#box-msg-close').text());
                    } else if (clickCount === 3) {
                        clickCount = 0;
                        let prompetForget = DOMPurify.sanitize($('#prompet-forget-pwd-msg').html());
                        var box = showInfoBox(prompetForget);
                        box.find('#box-ok').text($('#close-msg-reset-password').text());
                    } else {
                        let errorText =  DOMPurify.sanitize($('#error_1121').text());
                        showErrorBox(errorText);
                    }
                    button.removeClass('loading');
                    modal.removeClass('readOnly');
                }
            },
            fail: function () {
                let errorText =  DOMPurify.sanitize($('#error_1121').text());
                showErrorBox(errorText);
                button.removeClass('loading');
                modal.removeClass('readOnly');
            }
        });
    }
    email.on('keyup', function () {
        isMailValid(email.val(), email.parent());
    });
    password.on('keyup', function () {
        let title = DOMPurify.sanitize($(this).prop('title'));
        if (!$(this).val())
            $(this).addClass('warning').parent().append('<span class="error">' + title + '</span>');
        else
            $(this).removeClass('warning').parent().find('.error').remove();
    })
}
$(document).ready(function () {
    if ($(window).width() <= 900) {
        $('.header').css("margin", "12px 0");
        $('.row.header').css("padding-top", "16px !important");
    }
    /** Update phase status when user clicks on guide buttons */
    $(document).on('click touchstart', ".introjs-button, .introjs-bullets li", function () {
        $(".link-ic .introjs-showElement").parent().addClass("orange-bg");
        /** Update phase status when user views the virtual tour of homepage */
        if (click === 3) {
            if (updateDone === false) {
                updateGuide('homepage done');
                click = 2;
            }
        }
    });
    $(document).on('click touchstart', ".introjs-donebutton, .introjs-skipbutton", function () {
        homePageGuide.exit();
        homePageGuideUSANZ.exit();
        $(".link-ic").removeClass("orange-bg");
        if ($(window).width() <= 900) {
            $('.header').css("margin", '22px 0');
        }
    });

    /** toogle eye password */
    $(".toggle-password").click(function () {

        $(this).toggleClass("eye-icon eye-icon-off");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });

    var feedbackRecaptchaRendered;
    var feedbackRecaptcha = $('#feedRecaptcha');
    var recaptchaShow = true;
    $('.give-feedback').on('click', function () {
        $(window).scrollTop('0');
        if (feedbackRecaptcha.length > 0) {
            feedbackRecaptchaRendered = grecaptcha.render('feedRecaptcha', {
                'sitekey': captchaSite,
                'callback': reCaptchaVerify
            });
        } else recaptchaShow = false;
    });

    // search bar return books

    $('#search-input, .search-input').on('keyup touchEnd', function(){
        var status = false;
        if ($(this).val().length >= 3 && status == false){
            status = true;
            var search =  DOMPurify.sanitize($(this).val());
            $.ajax({
                url: baseUrl + '/search-bar-show',
                method: 'POST',
                data: {'val' : $(this).val()},

                success: function (data) {
                    $(".result_search_book").empty();
                    if (data.success === true){
                        $.each(data.message, function( index, value ) {
                            var booktitle = value.title;
                            booktitle = booktitle.replace(search, "<strong>"+search+"</strong>");
                            $(".result_search_book").append('<li><a href="'+baseUrl+'/book/details/'+value.isbn+'"><img class="" id="'+value.isbn+'" src="'+value.image+'" onerror="imgError(this);" alt="'+value.title+'"><p>'+booktitle+'</p></a></li>');
                        });
                    }
                },
                done: function (){
                    status = false;
                }
            });
        }
        if($(this).val().length == 0){
            $(".result_search_book").empty();
        }
        if (isMobile()) {
            $('.result_search_book').css('width', $(window).width());
        }
    });


    /** reset recaptcha when close modal */
    $('#close-feedback-modal').on('click', function () {
        grecaptcha.reset(feedbackRecaptchaRendered);
    });

    $('#give-public-feedback').on('click', function () {

        let valid = true;
        let url = $(this).data('url');
        let feedBack = $('#feedBack');

        if (feedbackRecaptcha.length > 0 && !checkReCaptcha(feedbackRecaptchaRendered)) {
            if ($('#feedback-recaptcha').find('.error').length == 0) {
                let errorCaptcha =  DOMPurify.sanitize($('#error_captcha').text());
                feedbackRecaptcha.parent().append('<span class="error">' + errorCaptcha+ '</span>');
            }
            valid = false;
        } else {
            $('#feedback-recaptcha').find('.error').remove();
        }

        if (feedBack.val() !== '') {
            $('#feedBack').removeClass('warning');
            /** test if captcha is showing **/
            if (valid && recaptchaShow) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {feedback: feedBack.val(), 'g-recaptcha-response': $('#g-recaptcha-response').val()},
                    success: function (data) {
                        if (data.status) {
                            $('#feedBack').val('');
                            $('.review-book-modal').modal('hide');
                            $('#feedback-step').remove();
                            $('#finish-step').css('display', 'initial');
                        }
                    }
                });
            } else console.log('Recaptcha is missing! Reload page');
        } else {
            feedBack.addClass('warning');
            let feedBackError = DOMPurify.sanitize(feedBack.attr('data-error'));
            if ($('.feedback-txt').find('.error').length == 0) {
                $('.feedback-txt').append('<span class="error">' + feedBackError + '</span>');
            }

        }
    })
    $('.continue-browsing, .review-book-modal .close').on('click', function () {
        $('.review-book-modal').modal('hide');
        $('#feedBack').val('');
    })

    $('#close-feedback-modal').on('click', function (){
        if ($("#feedback-step").length === 0) {
            location.reload();
        }
    });
    /** Get current site language */
    currentLang = $('#current_lang').data('lang');
    current_region = $('#current_region').data('region');
    if (!Cookies.get('default-lang')) {
        Cookies.set('default-lang', currentLang);
    }
    /** set the site langue Cookies*/
    if (!catalogueLang) {
        Cookies.set('site-lang', currentLang);
    }

    /** Click close covid banner */
    $(document).on('click', '#close-covid-banner', function (e) {
        $('.covid-banner').fadeOut();
    });

    var catlogSwitcher = $('ul.catalog-links li');
    if (isMobile()) {
        catlogSwitcher = $('.catalog-links .dropdown-menu li');
        $('.mobile-catalog-switcher #language-' + currentLang).addClass('active');
    } else {
        $('#language-' + currentLang).addClass('active');
    }

    $('.active-catalog').text($('#language-' + currentLang).text());

    /** Show confirm logout box */
    $(document).on('click', '.logout', function (e) {
        /** Here show the confirm box */
        var box = $('.box');
        var logoutLink = $(this).data('url');
        box.attr('class', 'box active confirm-info ');
        let yesText = DOMPurify.sanitize($('#box-msg-yes').text());
        box.find('#box-confirm').html(yesText + '<span></span>');
        box.find('#box-cancel').text($('#box-msg-no').text());
        box.find('.confirm-msg').html($(this).data('msg'));
        $('body,html').addClass('modal-open scrollable');
        box.on('click', '#box-confirm', function () {
            box.off('click');
            $(this).addClass('loading');
            window.location = logoutLink;
        });
        box.on('click', '#box-cancel', function () {
            box.removeClass('active confirm-info ');
            $('body,html').removeClass('modal-open scrollable');
            box.off('click');
        });
    });

    /** Show forget password when click on link from multiple wrong attempts*/
    $(document).on('click', '#show-forget', function (e) {
        e.preventDefault();
        $('#show-forget-pwd-content').click();
        $('#box-ok').click();
    });

    /** Remove banner bullet */
    $('.example-pager span').empty();
    $('.grid-view').show();
    $('.list-view').hide();

    /** Call login box if user is not logged in, else redirect it to my inspection copies page  (l=tnavigationpro-toprue in the url) */
    if (urlParam('l') != null && urlParam('l') === 'true') {
        var myIc = $('#my_ic_page');
        if (myIc.data('log') === 0)
            showLoginBox();
        else {
            let icUrl = DOMPurify.sanitize(myIc.data('url'));
            window.location = icUrl;
        }

    }
    /** Call reset password box if r=true in the url */
    if (urlParam('r') != null && urlParam('r') === 'true') {
        showLoginBox();
        setTimeout(function () {
            $('#show-forget-pwd-content').click();
        }, 100)
    }

    /** Burger menu */
    $('.burguerMenu').click(function () {
        if ($('.mobileMenu ').hasClass('open-menu')) {
            $('.mobileMenu').removeClass('open-menu');
            $(".search.mobile-search-icon").show();
            modalFix(function () {
                $('body,html').removeClass('modal-open')
            })
        } else {
            $('.mobileMenu').addClass('open-menu');
            $(".search.mobile-search-icon").hide();
            modalFix(function () {
                $('body,html').addClass('modal-open')
            });
        }
    });
    $('.dropdown-notif').click(function () {
        setTimeout(function () {
            if (isMobile()) {
                if ($('.dropdown-notif').hasClass('open')) {
                    $(".search.mobile-search-icon").hide();
                    modalFix(function () {
                        $('body,html').addClass('modal-open')
                    })
                } else {
                    $(".search.mobile-search-icon").show();
                    modalFix(function () {
                        $('body,html').removeClass('modal-open')
                    })
                }
            }
            //    if window size under 767px then hide search icon
            //     if($(window).width() <= 767) {
            //         if ($('.dropdown-notif').hasClass('open')) {
            //             $(".search.mobile-search-icon").hide();
            //             modalFix(function () {
            //                 $('body,html').addClass('modal-open')
            //             })
            //         } else {
            //             $(".search.mobile-search-icon").show();
            //             modalFix(function () {
            //                 $('body,html').removeClass('modal-open')
            //             })
            //         }
            //     }
        }, 200)

    });
    if (isMobile()) {
        $('.top-sub-menu li').on('click', function (e) {
            if ($('.menuItem.dropdown').hasClass('open')) {
                e.stopPropagation();
                $(this).closest('.dropdown').find('.main-menu-item.dropdown-toggle').attr("data-toggle", "false");
            }
        });
    }

    $('.menuItem.dropdown').click(function () {
        setTimeout(function () {
            if (isMobile()) {

                if ($('.menuItem.dropdown').hasClass('open')) {
                    modalFix(function () {
                        $('body,html').addClass('modal-open')
                    })
                } else {
                    modalFix(function () {
                        $('body,html').removeClass('modal-open')
                    })
                }
            }
        }, 200)

    });
    /** Click Login button to submit form */
    $('.login-btn').on('click', clickCount, function (e) {
        e.preventDefault();
        clickCount += 1;
        login($(this));
    });

    /** Click go from login to forget password */
    $(document).on('click', '#show-forget-pwd-content', function (e) {
        e.preventDefault();
        $('.login-modal #login-content').addClass('hidden');
        $('.login-modal #forget-content').removeClass('hidden');
    });

    /** Click return from forget password to login */
    $(document).on('click', '#back-login', function (e) {
        e.preventDefault();
        $('.login-modal #login-content').removeClass('hidden');
        $('.login-modal #forget-content').addClass('hidden');
    });


    /*Click return form info message and redirect to the evolve site */
    $(document).on('click', '.show-modal-evolve', function (e) {
        /** Here show the confirm box */
        var box = $('.box');
        /* set the evolve link */
        var evolveLink = "https://evolve.elsevier.com/cs/";
        box.attr('class', 'box active confirm-info ');
        let yesText = DOMPurify.sanitize($('#box-msg-yes').text());
        box.find('#box-confirm').html(yesText + '<span></span>');
        box.find('#box-cancel').text($('#box-msg-no').text());
        /* add content message */
        box.find('.confirm-msg').text($('#evolve-msg').text());
        $('body,html').addClass('modal-open scrollable');
        box.on('click', '#box-confirm', function () {
            window.open(evolveLink, '_blank');
            box.removeClass('active confirm-info ');
            $('body,html').removeClass('modal-open scrollable');
            box.off('click');
        });
        box.on('click', '#box-cancel', function () {
            box.removeClass('active confirm-info ');
            $('body,html').removeClass('modal-open scrollable');
            box.off('click');
        });
    });

    /** Click show login modal **/
    $('.show-login').on('click', showLoginBox);
    /** Click close login / forget password Modal **/
    $(document).on('click', '.close', function () {
        closeModal($(this).closest('.modal'));
    });

    // add this block to replace comment function below "isMobile"
    // for devices under or equal 767px
    if($(window).width() <= 767) {
        $('.search, .search-mobile').on('click', function () {
            $('body,html').addClass('modal-open');
            $('.subMenuSection').slideUp();
            $('.searchBarSection').fadeIn();
        });
        $('.container-search .back, .container-search .close').on('click touchStart', function () {
            $(this).closest('.searchBarSection').fadeOut();
        });
        /** Show search box (mobile mode) */
        $('.mobileMenu .search').on('click', function () {
            $('.menu-expand').slideUp();
            $('.burguerMenu').removeClass('open');
            $('.searchBarSection').fadeToggle();
        });
        /** New search, cleans search inputs content */
        $('#searchActive').on('click', function () {
            $('#search-input').val('');
            $('input[name=type]').removeAttr('checked');
            $('#searchTitle').prop('checked', true);
        });

        $('#searchActive').removeClass('search-desktop');
        $('.hidden-mobile').css('display', 'none');
    }
    // for devices over or equal 768px
    if ($(window).width() >= 768) {
        $('.mainMenu .menuItem:not(.search)').unbind('click').on('click', function () {
            var menuItem = $(this).text();
            $('.menuItem.active').removeClass('active');
            $(this).addClass('active');
            $('.searchBarSection').slideUp();
            var siblingItem = $('[data-discipline="' + menuItem + '"]');
            siblingItem.siblings('.subMenuSection').slideUp();
            setTimeout(function () {
                siblingItem.slideToggle();
            }, 500);
            if (siblingItem.is(':visible')) {
                $(this).removeClass('active');
                $('.menuItem').removeClass('active');
            }
        });
        $('#searchActive').addClass('search-desktop');

        $('.hidden-desktop').css('display', 'none');
    }
    // condition if(isMobile) replaced with the code above
    if(isMobile()) {
        /** Show search box */
        /* $('.search, .search-mobile').on('click', function () {
             $('body,html').addClass('modal-open');
             $('.subMenuSection').slideUp();
             $('.searchBarSection').fadeIn();
         });
         $('.container-search .back, .container-search .close').on('click touchStart', function () {
             $(this).closest('.searchBarSection').fadeOut();
         });
         /!** Show search box (mobile mode) *!/
         $('.mobileMenu .search').on('click', function () {
             $('.menu-expand').slideUp();
             $('.burguerMenu').removeClass('open');
             $('.searchBarSection').fadeToggle();
         });
         /!** New search, cleans search inputs content *!/
         $('#searchActive').on('click', function () {
             $('#search-input').val('');
             $('input[name=type]').removeAttr('checked');
             $('#searchTitle').prop('checked', true);
         });*/
        /** Show subcategory list in navbar */
        /*        if ($(window).width() >= 1024) {
                    $('.mainMenu .menuItem:not(.search)').unbind('click').bind('click', function () {
                        var menuItem = $(this).text();
                        $('.menuItem.active').removeClass('active');
                        $(this).addClass('active');
                        $('.searchBarSection').slideUp();
                        var siblingItem = $('[data-discipline="' + menuItem + '"]');
                        siblingItem.siblings('.subMenuSection').slideUp();
                        setTimeout(function () {
                            siblingItem.slideToggle();
                        }, 500);
                        if (siblingItem.is(':visible')) {
                            $(this).removeClass('active');
                            $('.menuItem').removeClass('active');
                        }
                    });
                }
                $('#searchActive').removeClass('search-desktop');
                $('.hidden-mobile').css('display', 'none');
            } else {
                $('.hidden-desktop').css('display', 'none');
            }*/
    }



    function search() {
        Cookies.set('search', '');
        // if(isMobile()) {
        //     var searchInput = $('.searchInput #search-input').val();
        // } else {
        //     var searchInput = $('.search-input').val();
        // }
        // check window width if under or equal 767px
        if($(window).width() <= 767) {
            var searchInput = $('.searchInput #search-input').val();
        }
        // check window width if over or equal 768px
        else {
            var searchInput = $('.search-input').val();
        }
        var searchType = 'description';
        var sortBy = 'Date';

        sessionStorage.searchedContent = searchInput;
        sessionStorage.searchType = searchType;
        var url = baseUrl + '/books/all';
        //search by all
        if (searchInput)
            url += '?sb=description&s=' + searchInput;

        window.location.href = url;
    }

    /** Click search book filter  */
    $(document).on('click', '.search-container .searchIcon, .search-desktop', function () {
        search()
    });
    /** Press enter to search */
    $(document).on('keypress', '#search-input, .search-input', function (e) {
        var keyCode = (e.keyCode ? e.keyCode : e.which);
        if (keyCode === 13) {
            search();
        }
    });
    /** add cursor in input on focus */
    $('.search-mobile,#searchActive').on('click', function (e) {
        setTimeout(function () {
            $('#search-input').focus();
        }, 0);
    });
    $('#search-input').on('focus', function (e) {
        $(this).parent().addClass('focus-input');
    })
    $('#search-input').on('blur', function (e) {
        $(this).parent().removeClass('focus-input');
    })


    /** Admin lang switcher */
    $('#langSwitcher').on('change', function () {
        var selectedClass = $(this).find('option:selected').data('flag-class');
        $(this).parent().find('.flag-icon').prop('class', selectedClass);
    });

    /** Remove field error if they got a value */
    $(document).on('change keyup', 'textarea,input:not(.phone),select', function () {
        if (($(this).val() && $(this).hasClass('warning')) || ($(this).attr('type') === "checkbox" && $(this).is(':checked')))
            $(this).removeClass('warning').parent().find('.error').remove();
    });

    /** Fix blurred image on small devices */
    $.each($('.retina-reload'), function (i, img) {
        var src = $(this).attr('src');
        if (src.indexOf("@1x.") >= 0) {
            var imgRation = window.devicePixelRatio > 3 ? 3 : window.devicePixelRatio < 1 ? 1 : window.devicePixelRatio;
            src = src.replace("@1x.", "@" + Math.floor(imgRation) + "x.");
        }
        $(this).attr('src', src);
        $(img).trigger('onload');
    });

    /** Init tooltip */
    $('[data-toggle="tooltip"]').tooltip();
    if (!isMobile())

        $('.profile-nav').dropdown();
    fixLoginModalIpad();
    /** init select2 for filter sortBy */
    $('.filter-selector-list').select2();

    /** Prevent user to enter more then "max-length" character */
    $(document).on('keypress', '[data-max-length]', function (e) {
        if ($(this).val().length >= $(this).data('max-length'))
            e.preventDefault();
    });
    $(document).on('change', '[data-max-length]', function (e) {
        if ($(this).val().length > $(this).data('max-length'))
            $(this).val($(this).val().substring(0, $(this).data('max-length')))
    });

    /** Input number type prevent entering letter */
    $(document).on('keypress', 'input[type="number"]', function (e) {
        if (isNaN(e.key) && !$(this).hasClass('phone'))
            e.preventDefault();
        else {
            if ($(this).hasClass('phone') && isNaN(e.key) && (e.key !== '+' || $(this).val().length > 0))
                e.preventDefault();
            if ($(this).val().length === 0 && e.key === '+')
                $(this).val('+');
        }
    })
    /** update the list of subcategories on the sidebar according to the selected categorie H&S or S&T */
    if (!Cookies.get('category-type')) {
        Cookies.set('category-type', 'hs');
    }

    if ((catalogueLang == "en") && $(window).width() >= 1024) {
        $('.mainMenu .top-sub-menu >li').on('click', function () {
            Cookies.set('category-type', $(this).parent().data('type'));
            Cookies.set('search', '');
        });
    }
    $('#coversTab .nav-tabs li a').on('click', function () {
        $('#coversTab .nav-link').removeClass('active');
        $('#coversTab .nav-link').attr('aria-selected', 'false');
        $(this).attr('aria-selected', 'true');
        $(this).addClass('active');
        $('#coversTab .tab-pane').addClass('hidden');
        $('#' + $(this).data('id')).removeClass('hidden');
    })

    /** update the site langue cookies when click on lang switcher */
    $('.language-links .dropdown-menu li a').on('click', function (e) {
        /** the india version site sees the switch menu but do not have the right to change the language **/
        if (currentLang !== "in") {
            Cookies.set('site-lang', $(this).data('lang'));
            Cookies.set('switch-catalog', $(this).data('cat'));
            $.ajax({url: baseUrl + '/clear-books-session', success: function(result){}});
            location.reload();
        }
    });
    /** update the site catalog cookies when click on catalog switcher */

    if ($(window).width() <= 1024)
    {
        $('.catalog-links li ul li').on('click', function (e) {
            /** the india version site sees the switch menu but do not have the right to change the language **/
            if (currentLang !== "in") {
                Cookies.set('site-lang', $(this).data('lang'));
                location.reload();
            }
        });
    }
    else{
        $('.catalog-links li').on('click', function (e) {
            /** the india version site sees the switch menu but do not have the right to change the language **/
            if (currentLang !== "in") {
                Cookies.set('site-lang', $(this).data('lang'));
                location.reload();
            }
        });
    }
    /** Add the class active for the current language */

    if (catalogueLang) {
        $('.language-links .dropdown-menu li').removeClass('active');
        if (catalogueLang == "in" || catalogueLang == "us") {
            $('#lang-en').addClass('active');
        } else {
            $('#lang-' + catalogueLang).addClass('active');
            $('.active-language').text($('#lang-' + catalogueLang).text());
        }
        $('#current_lang').data('lang', catalogueLang);

    }

    /**explore books outside of the default settings*/
    catlogSwitcher.on('click', function (e) {
        Cookies.set('search', '');
        var region = $(this).data('region');
        var lang = $(this).data('lang');
        Cookies.set('switch-catalog', '/' + region + '/' + lang + '/');
        //Cookies.set('site-lang',lang);
        //call ajax to clean boos session
        $.ajax({url: baseUrl + '/clear-books-session', success: function(result){}});
        if ($('.home').length > 0) {
            location.reload();
        } else {
            window.location.replace('/books/all');
        }
    });
    /** update the list of subcategories on the sidebar according to the selected categorie H&S or S&T */
    if (!Cookies.get('category-type')) {
        Cookies.set('category-type', 'hs');
    }
    if ((catalogueLang === "en") && $(window).width() >= 1024) {
        $('.mainMenu .top-sub-menu >li').on('click', function () {
            Cookies.set('category-type', $(this).parent().data('type'));
            Cookies.set('search', '');
        });
    }
    $('#coversTab .nav-tabs li a').on('click', function () {
        $('#coversTab .nav-link').removeClass('active').attr('aria-selected', 'false');
        $(this).attr('aria-selected', 'true');
        $(this).addClass('active');
        $('#coversTab .tab-pane').addClass('hidden');
        $('#' + $(this).data('id')).removeClass('hidden');
    })

    if ($('.update-version').length > 0) {
        $('.update-version').on('click', function () {
            $.ajax({
                url: baseUrl + '/profile/update-version',
                method: 'GET',
                dataType: 'JSON',
                success: function (data) {
                    $(this).removeClass('update-version');
                }
            });
        })
    }

    //show login modal when we have source feedback on home page and user not logged in else redirect logged in user to my IC page
    const queryString = DOMPurify.sanitize(window.location.search);
    const urlParams = new URLSearchParams(queryString);
    source = urlParams.get('source');
    if (source == "feedback") {
        let myIc = $('#my_ic_page');
        if (myIc.data('log') === 0)
            showLoginBox();
        else {
            let icUrl = DOMPurify.sanitize(myIc.data('url'));
            window.location = icUrl;
        }

    }

    /** Export book details */
    $(document).on('click', '#update-librarian', function (e) {
        e.preventDefault();
        var today = new Date();
        var day = today.getDate();
        var monthIndex = today.getMonth();
        var year = today.getFullYear();
        var todayDate = day + '/' + monthIndex + '/' + (year);
        $.ajax({
            url: $(this).data('url'),
            method: 'POST'
        }).done(function (data) {
            let encodedData = DOMPurify.sanitize(JSON.stringify(data));
            var sanitizeData = JSON.parse(encodedData);
            var sanitizedFileURL = sanitizeData.file;
            var base64regex = /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/;
            if(base64regex.test(sanitizedFileURL)) {
                var $a = $("<a>");
                $a.attr("href", "data:application/vnd.ms-word;base64,"+sanitizedFileURL);
                $("body").append($a);
                $a.attr("download", "Book details(" + todayDate + ").docx");
                $a[0].click();
                $a.remove();
            }

        }).error(function () {
            location.reload();
        });
    });

    //notifications
    $('.link-book').bind('touchstart click', function (event) {
        var hash = DOMPurify.sanitize($(this).attr('data-isbn'));
        var activeTab = '';
        if($(this).attr('href').indexOf("shared") > -1) {
            //open tab pane shared
            activeTab = 'shared_by_colleague';
        }
        if($(this).attr('href').indexOf("like") > -1) {
            //open tabPane like
            activeTab = 'you_like';
        }
        if(activeTab !== '') {
            event.preventDefault();
            $('.nav-link[data-id="' + activeTab + '"').trigger('click');
        }
        //call ajax to read notification
        $(this).parent().removeClass('new-notif');
        $.ajax({
            url: baseUrl + '/profile/update-notification',
            method: 'GET',
            dataType: 'JSON',
            success: function (data) {
                $('#notifications-count').css('display','none')
            }
        });
    });
    $( document ).on( "mousemove",".singleProduct", function( event ) {
        $( ".tooltip_booktitle" ).css({
            "left" : event.pageX + 15,
            "top" : event.pageY - $(window).scrollTop()
        });
    });
});


/** Load cities by country from ajax request from local files city.json */
function loadCities(country, citySelector) {
    if (citySelector.length > 0) {
        $.ajax({
            url: baseUrl + "/cities/" + country,
            beforeSend: function () {
                citySelector.addClass('s2-loading-data');
                citySelector.html("");
            },
            success: function (data) {
                citySelector.append("<option></option>");
                cities = [];
                if (data.status) {
                    cities.push({id: '', text: ''});
                    /** Populate city selector options from ajax API */
                    $.each(data.results, function (i, city) {
                        var citySelectorOption = new Option();
                        citySelectorOption.text = city;
                        citySelectorOption.value = city;
                        citySelector.append(citySelectorOption);
                        cities.push({id: city, text: city});
                    });
                }
                citySelector.removeClass('s2-loading-data');
            }
        })
    }
}


$('#new-institution-btn').on('click', function () {
    if (currentLang == 'in' || currentCountry === 'us' || currentCountry.replace('+', ' ') == 'united states') {
        var field = 'field';
        $('#institution_institutionId').prop('disabled', true);
        $('.btn-new-institution').addClass('disabled');
        if ($('.city-filter').length > 0) {
            field = 'city';
        } else if ($('.state-filter').length > 0) field = 'State';
        $('.select2-selection__placeholder').remove();
        $('#select2-institution_institutionId-container').append('<span class="select2-selection__placeholder">Select ' + field + ' above</span>');
    }
});

$('#city_filter_modal,#state_filter_modal').change(function () {
    if ((currentLang == 'in' || currentLang == 'us' || currentCountry === 'united states')
        && document.getElementById('institution_institutionId').disabled) {
        $('#institution_institutionId').prop('disabled', false);
        $('.btn-new-institution').removeClass('disabled');
        $('.select2-selection__placeholder').remove();
    }
});

/** Load states by country from ajax request  */
function loadStates(country, stateSelector) {
    if (stateSelector.length > 0) {
        $.ajax({
            url: baseUrl + "/states/" + country,
            beforeSend: function () {
                stateSelector.addClass('s2-loading-data');
                stateSelector.html("");
            },
            success: function (data) {
                stateSelector.append("<option></option>");
                states = [];
                if (data.status) {
                    states.push({id: '', text: ''});
                    /** Populate city selector options from ajax API */
                    $.each(data.results, function (i, state) {
                        var stateSelectorOption = new Option();
                        stateSelectorOption.text = state;
                        stateSelectorOption.value = state;
                        stateSelector.append(stateSelectorOption);
                        states.push({id: state, text: state});
                    });
                }
                stateSelector.removeClass('s2-loading-data');
            }
        })
    }
}

/** Load institutions by country from ajax request call SalesLogix API */
function loadInstitutions(country, city, zipCode, state, block, callback) {
    var institutionSelector = block.find('.institution-id');
    var institutionText = block.find('.institution-name');
    var departmentSelector = block.find('.department-id');
    var departmentText = block.find('.department-name');
    if (zipCode != '') {
        $('#zip_filter_modal').addClass('loading-data');
    }
    if (country.toLowerCase() == 'us') country = 'United States';
    institutionSelector.addClass('s2-loading-data');
    institutionText.addClass('loading-data');
    block.find('.department-text-block,.department-selector-block').removeClass('f-hidden');
    block.find('.department-text-block,.department-selector-block').removeClass('hidden');

    /** ajax call */
    $.post(baseUrl + "/institutions", {
        country: country,
        city: city,
        zipCode: zipCode.replace(/\s/g, ''),
        state: state
    }, function (data) {
        institutionSelector.html("<option value=''></option>");
        departmentSelector.html("<option value=''></option>");
        if (data.status) {
            institutionSelector.parent().show();
            institutionSelector.parent().removeClass('hidden');
            institutionText.parent().hide();
            if (!departmentSelector.parent().hasClass('is-br')) {
                departmentSelector.parent().show();
                departmentText.parent().hide();
            }
            if (city === "" && zipCode === "") {
                institutionsData = [new Option()];
            }
            for (var key in data.results) {
                var institutionOption = new Option();
                institutionOption.text = data.results[key]['name'];
                institutionOption.value = key;
                institutionOption.setAttribute('data-type', data.results[key]['type']);
                if (city === "" && zipCode === "")
                    institutionsData.push(institutionOption);
                institutionSelector.append(institutionOption);
            }
            if (Object.keys(data.results).length > 200 && isIE()) {
                institutionSelector.select2({minimumInputLength: 2});
            }
            institutionSelector.parent().find('.warning').removeClass('warning');
            institutionSelector.parent().find('.error').remove();
            block.find('.btn-cancel-new-institution').show();
            $('.select2-selection__placeholder').remove();
        } else {
            if (zipCode.length > 0) {
                var zipInput = $(block).find('.zip-filter');
                zipInput.addClass('warning');
                zipInput.parent().append('<span class="error">' + zipInput.data('wrong') + '</span>');
            }
            institutionSelector.parent().hide();
            institutionText.val("").parent().show();
            departmentText.val("").parent().show();
            departmentSelector.parent().hide();
            /** Hide cancel to not be able to switch back to institution selector */
            block.find('.btn-cancel-new-institution').hide();
            block.find('.btn-cancel-new-department').hide();
            block.find('.btn-new-department').hide();
        }
        if (zipCode != '') {
            $('#zip_filter_modal').removeClass('loading-data');
        }
        departmentSelector.parent().removeClass('hidden');
        institutionSelector.removeClass('s2-loading-data');
        institutionText.removeClass('loading-data');
        block.removeClass('loading');
        if (callback)
            callback();
    });
}

/** Load department by selected institution */
function loadDepartments(institution, block, open) {
    var departmentSelector = block.find('.department-id');
    var departmentText = block.find('.department-name');
    departmentSelector.addClass('s2-loading-data');
    departmentText.addClass('loading-data');
    var profession = block.data('lang') === 'in' ? block.find('.profession').val() : '';
    $('#institution_departmentName').val('');
    /** Ajax call */
    $.post(baseUrl + "/departments", {
        institution: institution,
        profession: profession
    }, function (data) {
        departmentSelector.html("");
        departmentSelector.data('loaded', 1);
        if (data.status) {
            departmentSelector.append("<option></option>");
            /** build department selector options from ajax API */
            for (var key in data.results) {
                var departmentOption = new Option();
                departmentOption.text = data.results[key];
                departmentOption.value = key;
                departmentOption.selected = departmentSelector.data('value') === key;
                departmentSelector.append(departmentOption)
            }
            departmentSelector.parent().show();
            departmentText.parent().hide();
            block.find('.btn-new-department').show();
            if (block.data('lang') === 'in') {
                if (!profession.toLowerCase().includes('veterinary')) {
                    block.find('.btn-new-department').addClass('f-hidden');
                    block.find('.btn-cancel-new-department').click();
                } else {
                    block.find('.btn-new-department').removeClass('f-hidden');
                }
            }
        } else {
            /** Manipulate department */
            block.find('.department-text-block').show();
            block.find('.department-selector-block').hide();
            /** Hide cancel to not be able to switch back to department selector */
            block.find('.btn-cancel-new-department').hide();
        }
        departmentSelector.removeClass('s2-loading-data');
        departmentText.removeClass('loading-data');
        if (open)
            departmentSelector.select2('open')
    });
}

/** Load speciality by profession */
function loadSpecialities(profession, block, open) {
    var specialitySelector = block.find('.speciality');
    specialitySelector.addClass('s2-loading-data');
    specialitySelector.removeClass('empty');
    specialitySelector.removeClass('warning').parent().find('.error').remove();
    /** Request ajax to get all specialities by profession */
    $.post(baseUrl + "/specialities", {
        profession: profession
    }, function (data) {
        specialitySelector.html("");
        specialitySelector.data('loaded', 1);
        if (data.status) {
            if (!specialitySelector.parent().is(':visible'))
                specialitySelector.parent().show();
            specialitySelector.append("<option></option>");
            var specialityValue = specialitySelector.data('value');
            /** Append each speciality from ajax request */
            for (var key in data.results) {
                var specialityOption = new Option();
                specialityOption.value = data.results[key];
                specialityOption.text = data.results[key];
                specialityOption.selected = specialityValue === data.results[key];
                specialitySelector.append(specialityOption)
            }
        } else {
            specialitySelector.addClass('empty');
        }
        specialitySelector.removeClass('s2-loading-data');
        if (open)
            specialitySelector.select2('open')
    });
}

/** Reset update block */
function cancelUpdateBlock(block) {
    $.each(block.find('[data-value]'), function (i, elem) {
        if ($(elem).data('value') !== $(elem).val()) {
            if ($(elem).is('input[type="checkbox"]'))
                $(elem).prop('checked', $(elem).data('value')).trigger('change');
            else
                $(elem).val($(elem).data('value')).trigger('change');
            if ($(elem).is('select'))
                $(elem).trigger('change');
        }
    });
    var newInstitution = $('.new-institution-block');
    if (newInstitution.length > 0)
        newInstitution.parent().remove();
    block.find('.update-btn').attr('disabled', true);
}

/** Show box to save or cancel update */
function showSaveUpdateBox(block, confirmCallback) {
    /** Here show the confirm box */
    var box = $('.box');
    box.attr('class', 'box active confirm-info ');
    $('body,html').addClass('modal-open scrollable');
    box.on('click', '#box-confirm', function () {
        cancelUpdateBlock(block);
        confirmCallback();
        box.removeClass('active confirm-info ');
        $('body,html').removeClass('modal-open scrollable');
        box.off('click');
    });
    box.on('click', '#box-cancel', function () {
        box.removeClass('active confirm-info ');
        $('body,html').removeClass('modal-open scrollable');
        box.off('click');
    });
}

/** Show box with error message */
function showErrorBox(message) {
    var box = $('.box');
    var errorContainer = box.find('.error-msg');
    errorContainer.html(message);
    box.attr('class', 'box active error-info');
    box.find('#box-ok').text($('#ok-btn-box').text());
    box.on('click', '#box-ok', function () {
        box.removeClass('active error-info');
        errorContainer.text("");
        box.off('click');
    });
    return box;
}

function showInfoBox(message) {
    var box = $('.box');
    var errorContainer = box.find('.info-msg');
    errorContainer.html(message);
    box.attr('class', 'box active warning-info');
    box.find('#box-ok').text($('#ok-btn-box').text());
    box.on('click', '#box-ok', function () {
        box.removeClass('active warning-info');
        errorContainer.text("");
        box.off('click');
    });
    return box;
}


function isMobile() {
    return /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4));
}

function checkReCaptcha(reCaptcha) {
    var v = grecaptcha.getResponse(reCaptcha);
    return v.length !== 0;
}

/** Function to copy text into clipboard */
function copyToClipboard(text, element= "body") {
    var temp = $("<input>");
    var body = $(element);
    body.append(temp);
    temp.val(text).select();
    document.execCommand("copy");
    temp.remove();
    var copyText = DOMPurify.sanitize($('#clipboard-copy').text());
    body.append('<div class="clipboard-toaster"><p class="clipboard-toaster-text">' + copyText + '</p></div>');
    $('.clipboard-toaster').fadeOut(4000, function () {
        $('.clipboard-toaster').remove();
    });
}

function modalFix(callback) {
    var body = $('body'),
        html = $('html');
    var previousScrollY = window.scrollY ? window.scrollY : body.scrollTop() ? body.scrollTop() : html.scrollTop();
    callback();
    body.scrollTop(previousScrollY);
    html.scrollTop(previousScrollY);
}

function fixLoginModalIpad() {
    if (navigator.userAgent.match(/iPad/i) && $(window).width() > 640)
        $('.modal-dialog').addClass("top-modal-ipad");

}

function isIE() {
    return navigator.userAgent.match(/Trident.*rv\:11\./);
}

/**
 * Return unique id
 * @return {string}
 */
function uniqueID() {
    return 'new_' + (Date.now().toString(36) + Math.random().toString(36).substr(2, 5)).toUpperCase();
}

/** Show reset password box when url params contain forgot-password */
let searchParam = DOMPurify.sanitize(window.location.search);
var params = new window.URLSearchParams(searchParam);
if (params.has('forgot-password')) {
    showLoginBox();
    $('.login-modal #login-content').addClass('hidden');
    $('.login-modal #forget-content').removeClass('hidden');
}
/** add reset password param to url when click on forget password link */
$('#show-forget-pwd-content').on('click', function () {
    window.history.pushState({}, '', '?forgot-password');
});
/** remove forgot-password param from url when back and when close login modal */
$('#back-login,#close-login-modal').on('click', function () {
    window.history.pushState(null, null, window.location.pathname);
});
$(document).ready(function () {
    $('.update-profile-cookie').fadeOut(5000);
    $(document).on('click', '.close-btn', function (e) {
        e.preventDefault();
        $(this).closest('.update-profile-cookie').remove();
    });
    /** this code is to set navigation only for search page **/
    if(document.referrer.includes('/books/')) {
        sessionStorage.setItem('source', 'search');
    } else if (!document.referrer.includes('/book/details') && sessionStorage.getItem('navigate') == false) {
        sessionStorage.setItem('source', 'other');
    }
    if(window.location.pathname.includes('/books/')) {
        sessionStorage.setItem('navigate', 'true');
    }
    $('.navigate a').on('click',function () {
        sessionStorage.setItem('navigate', 'true');
    })
    $('.singleProduct a').on('click',function () {
        sessionStorage.setItem('navigate', 'false');
    })
    //remove navigation when link of book from other page or new tab
    //old condition to check
    //if(sessionStorage.getItem('source') !== 'search' || (sessionStorage.getItem('source') == 'search' && history.length == 1) || sessionStorage.getItem('navigate') == 'false') {
    if(sessionStorage.getItem('source') !== 'search'|| sessionStorage.getItem('navigate') == 'false') {
        $('.navigate').remove();
    }
    $('#language-name').change(function(){
        var lang = $(this).prop('selectedIndex');
        $("#catalogues-name").prop('selectedIndex',lang);
    });
    $('#catalogues-name').change(function(){
        var lang = $(this).prop('selectedIndex');
        $("#language-name").prop('selectedIndex',lang);
    });

    $("#popuplangcat").submit(function(e){
        e.preventDefault();
        var url = $('#popuplangcat').attr('data-url');
        var lang = $('#language-name').val();
        var cat = $('#catalogues-name').val();
        $.ajax({url: baseUrl + '/clear-books-session', success: function(result){}});
        $.ajax({
            type: "POST",
            url: url,
            data: {lang: lang, cat: cat},
            success: function(data){
                Cookies.set('site-lang', lang);
                Cookies.set('switch-catalog', cat);
                location.reload();
                //alert("success");
            }
        });
    });
});
//login when press enter on the keyboard
$(document).keyup(function (event) {
    if (event.keyCode === 13 && $('.login-modal.active').length > 0) {
        login($('.login-btn'))
    }
});

$('#started-btn').on('click', function () {
    var userInterests = [];
    if ($('input[name="choice"]:checked').length === 0) {
        let errorText =  DOMPurify.sanitize($('#error_interests').text());
        var box = showErrorBox(errorText);
        box.find('#box-ok').text($('#box-msg-close').text());
    } else {
        //call web service to set user categories
        $.each($('input[name="choice"]:checked'), function (i, el) {
            userInterests.push($(this).val());
        });
        $(this).attr('disabled', 'disabled')
        $.ajax({
            url: $('.user-interests-page').data('update-user'),
            method: 'POST',
            data: {userInterests: userInterests},
            success: function (response) {
                if (response.success) {
                    window.location.href = "/";
                }
            }
        });
    }
});
/*sticky menu*/
$(window).scroll(function() {
    var sticky = $('.header'),
        scroll = $(window).scrollTop();

    if (scroll >= 55) {
        sticky.addClass('fixed'); }
    else {
        sticky.removeClass('fixed');

    }
});
/* remove double click on tooltip popover to open it ***/
$('body').on('hidden.bs.popover', function (e) {
    $(e.target).data("bs.popover").inState.click = false;
});


function initPopover() {
    /** Trigger popover */
    $('[data-toggle="popover"]').popover().on('show.bs.popover', function () {
        var currentPopover = $(this);
        $.each($('.popover.in'), function () {
            if (currentPopover !== $(this))
                $(this).prev().click();
        })
    });
}
function closePopover() {
    /** Close popover */
    $('body').on('click touch', function (e) {
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }

    });
}


var popup = document.getElementById('languageModal');
$('#languageModal').modal('hide');
if (popup) {
      if (!Cookies.get('reloadPage'))
      {
          Cookies.set('reloadPage', "reload");
      }
    if (Cookies.get('reloadPage') === "reload")
    {
        $('#languageModal').modal('show');
        Cookies.set('reloadPage', "reloaded");
    }
    else if (!localStorage.getItem('firstVisit')) {
        $('#languageModal').modal('show');
        localStorage.setItem('firstVisit', 'true');
    }
    else{
        $('#languageModal').modal('hide');
    }
}

//get hash code at next page
var hashCode = DOMPurify.sanitize(window.location.hash);
if(hashCode) {
    goToBook(hashCode);
}
$('.link-book').bind('touchstart click', function () {
    var hash = DOMPurify.sanitize($(this).attr('data-isbn'));
    if (hash !== "" && $(this).attr('href').indexOf("my-books") > -1) {
        event.preventDefault();
        goToBook('#'+hash);
    } else {
        let encodedUrl = DOMPurify.sanitize($(this).attr('href'));
        window.location = encodedUrl;
    }
});

/**
 * go to a specific hash on the my ic books
 * @param hashcode
 */
function goToBook(hashcode) {
    var EncodedHashcode = sanitizeHTML(hashcode);
    $('.book_item ').css('background','initial');
    //move page to any specific position of next page(let that is div with id "hashcode")
    $('.tabs-nav #myBooksList a').tab('show');
    if($('.myBooksList div'+EncodedHashcode).offset()) {
        var top_section = $('.myBooksList div'+EncodedHashcode).offset().top;
        if (isMobile()) {
            setTimeout(function () {$('li.active a').trigger('click');});
            window.scrollTo({
                top: top_section-600,
                behavior: 'smooth'
            });
        } else {
            window.scrollTo({
                top: top_section-250,
                behavior: 'smooth'
            });
        }
        $('.myBooksList div'+EncodedHashcode).parent().parent().css('background','#d6e9f06b');
    }

    if (isMobile()) {
        $('.title-notif').trigger('click');
    }
}
$( function() {
    $.ajax({
        url: baseUrl + '/save_books-in-session',
        method: 'POST',
        success: function (response) {
        }
    });
} );