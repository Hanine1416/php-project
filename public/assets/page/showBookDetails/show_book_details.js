$(document).ready(function () {

    /** Fix feedback read more */
    /** show tile to calculate height ***/
    $('.review').find('.hiddenReview').show();
    $.each($('.feedback-comment'), function () {
        readMore($(this));
    });
    $('.review').find('.hiddenReview').hide();
    /** Show extra comment text */
    $(document).on('click', '.read-more a', function (e) {
        e.preventDefault();
        $(this).parent().hide().prev().show()
    });
    /** Hide extra comment text */
    $(document).on('click', '.hidden-feedback-msg a', function (e) {
        e.preventDefault();
        $(this).parent().hide().next().show()
    });

    /** Hide price info if empty */
    if ($('.retailPrice .price').length === 0)
        $('.retailPrice').remove();

    /** Show login modal **/
    $(document).on('click', '.btn-call-login', function (e) {
        e.preventDefault();
        $('.show-login').click();
        $('#register-content').removeClass('hidden');
    });
    $(document).on('click', '.total_reviews', function (e) {
        e.preventDefault();
        var reviewTab = $('.tile-tab.review');
        reviewTab.addClass('active-tile');
        $("#review.title-accordion").addClass('active');
        reviewTab.find('.hiddenReview').show();
        setTimeout(function () {
            $([document.documentElement, document.body]).animate({
                scrollTop: reviewTab.first().offset().top - 170
            }, 1000);
        });
    });
    var url = window.location.href;
    var index = url.indexOf("#review");
    if (index !== -1) {
        $(document).ready(function(){
            var reviewTab = $('.tile-tab.review');
            reviewTab.addClass('active-tile');
            $("#review.title-accordion").addClass('active');
            reviewTab.find('.hiddenReview').show();
            setTimeout(function () {
                $([document.documentElement, document.body]).animate({
                    scrollTop: reviewTab.first().offset().top - 170
                }, 1000);
            });
        });
    }
    /** Tab manipulator **/
    (function ($) {
        if( isMobile() )
        {
            $(document).on('touchstart', '.accordion a.title-accordion', function (e) {
                var elem = $(this)
                if (!elem.hasClass('active')) {
                    elem.next().show();
                    $('.accordion a.active').removeClass('active').next().hide();
                    elem.addClass('active');
                    elem.parent().addClass('active-tile')
                } else {
                    elem.removeClass('active');
                    elem.parent().removeClass('active-tile')
                    elem.next().hide();
                }
                e.preventDefault();
            });
        }
        else{
            $(document).on('click', '.accordion a.title-accordion', function (e) {
                var elem = $(this)
                if (!elem.hasClass('active')) {
                    elem.next().show();
                    $('.accordion a.active').removeClass('active').next().hide();
                    elem.addClass('active');
                    elem.parent().addClass('active-tile')
                } else {
                    elem.removeClass('active');
                    elem.parent().removeClass('active-tile')
                    elem.next().hide();
                }
                e.preventDefault();
            });
        }
    })(jQuery);
    /** Feedback zone */
    $(document).on('click', '.feedback-zone .fa-times', function (e) {
        $(this).closest('.feedback-zone').toggleClass('opened');
    });

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
    /** Related book slider */
    $('.related-books-slider').slick({
        slide: 'div',
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 1,
        prevArrow: '<button class="slick-prev"><i class="fa fa-chevron-left"></i></button>',
        nextArrow: '<button class="slick-next"><i class="fa fa-chevron-right"></i></button>',
        responsive: [
            {
                breakpoint: 1250,
                settings: {
                    slidesToShow: 4
                }
            },
            {
                breakpoint: 950,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 700,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 500,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });

    if ($(window).width() <= 480) {
        var mobileTabTitle = $('.mobileTitle');
        mobileTabTitle.unbind('click').bind('click', function () {
            if (mobileTabTitle.is('.active')) {
                mobileTabTitle.removeClass('active');
                $(this).siblings().slideUp();
            } else {
                mobileTabTitle.removeClass('active');
                $(this).addClass('active');
                mobileTabTitle.siblings().slideUp();
                $(this).siblings().slideDown();
            }
            return false;
        })
    }
    /* action on sharing dots */
    var el = $('.more');
    var menu = $('.more-menu');


    /** Click on three dot menu to show option list */
    $(document).on('click', '#more-btn', function (e) {
        e.preventDefault();
        if (menu.is(':visible'))
            hideMenu();
        else {
            showMenu();
            e.stopPropagation();
        }
    });

    /** Click anywhere from 3 dot menu to close it */
    $(document).on('click', function () {
        if ($(this).closest('.more-menu').length === 0 && menu.is(':visible'))
            hideMenu();
    });

    /** Show 3 dot menu */
    function showMenu() {
        el.addClass('show-more-menu');
        menu.attr('aria-hidden', false);
    }

    /** Hide 3 dot menu */
    function hideMenu() {
        el.removeClass('show-more-menu');
        menu.attr('aria-hidden', true);
    }

    /** Mailto education consultant  */
    $(document).on('click', '.dot-education-consultant', function () {
        window.location = $(this).find('a').attr('href');
    });
    /** Desktop copy product link into clipboard  */
    $(document).on('click', '.dot-share-product', function (e) {
        e.preventDefault();
        copyToClipboard($(this).find('a').data('link'));
    });
    /** popup copy product link into clipboard*/
    $(document).on('click', '.dot-share-product-popup', function (e) {
        e.preventDefault();
        copyToClipboard($(this).find('a').data('link'),'#share-book-modal');
    });

    /** Hide not mobile options from 3 dot menu */
    if (!isMobile())
        $('.more-menu-item.for-mobile').remove();

    /** Select first book details tile */
    var bookDescriptionTiles = $('.productTabs ' + (isMobile() ? '.mobileTitle' : '.book-description-tiles li'));
    if (bookDescriptionTiles.length > 0)
        bookDescriptionTiles.first().click();


    /** Ancillary tab click open/close folder */
    $('.folder-link').on('click', function () {
        if ($(this).hasClass('closed')) {
            $(this).find('i.far').removeClass('fa-folder').addClass('fa-folder-open')
        } else {
            $(this).find('i.far').removeClass('fa-folder-open').addClass('fa-folder')
        }
        $(this).toggleClass('closed');
    });

    /*****  send helpfull on comment & toogle active on helpful icon *******/
    $(".helpful_icon").click(function () {

        $(this).toggleClass('active');
        var feedBackId = $(this).data('feedback-id');
        var like = $(this).hasClass('active');
        var helpfullBlock = $(this).parent().find('.likes');
        var likes = parseInt(helpfullBlock.text());
        let tag = $(this);
        tag.css("pointer-events","none");
        $.ajax({
            url: '/like-review',
            method: 'post',
            data: {feedBackId:  feedBackId, like: like},
            beforeSend: function () {

            },
            success: function (resp) {
                if (resp.success) {
                    if (like == true ) {
                        helpfullBlock.text(likes+1);
                    } else{
                        helpfullBlock.text(likes-1);
                    }
                    tag.css("pointer-events","auto");
                }
            },
        });
    });

});
$('.show-preview').on('click', function() {
    var link =  $('.preview_link').find('a').data('link');
    if (link) {
        window.open(link, '_blank');
    }
});
$('.share-book').click(function(){
    $("#share-book-modal #step1").addClass("active");
});
    function readMore(elem) {
    var maxLineHeight = isMobile() ? 69 : 69;
    var readLess = elem.data('less');
    var readMore = elem.data('more');
    /** Check if feedback has more then allowed lines */
    if (elem.height() > maxLineHeight) {
        var allowedCharNumber = Math.floor((elem.width() / 2) * (isMobile() ? 2.8 : 1.1));
        var hiddenMessage = elem.text().substring(allowedCharNumber + 1);
        elem.text(elem.text().substring(0, allowedCharNumber));
        var textAppend = '<span class="hidden-feedback-msg">' + hiddenMessage + ' <a href="javascript:;">' + readLess + '</a></span><span class="read-more">... <a href="javascript:;">' + readMore + '</a></span>';
        var sanitizedData = DOMPurify.sanitize(textAppend);
        elem.append(sanitizedData);
    }

}
