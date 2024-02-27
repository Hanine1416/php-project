$(document).ready(function () {
    initPopover();
    closePopover();
    //hid carousel images and show only the first slide
    $('.new_banner .banner-box').show();
    //load more books
    var start = 11;
    var startShared = 11;
    $(document).on('click','#load-more' ,function () {
        showRecommendations();
    });

    $(document).on('click','#load-more-shared' ,function () {
        showSharedbook();
    });

    function showRecommendations() {
        $('.load-more-book').removeClass('hidden');
        $('#load-more').addClass('hidden');
        $.ajax({
            url: '/load-more',
            method: 'post',
            data: {start:start,isMobile: isMobile()},
            dataType: 'html',
            success: function (data) {
                var sanitizedData = DOMPurify.sanitize(data);
                $('#recommendation-content .load-more-btn').remove();
                $('#recommendation-content').append(sanitizedData);
                var numberOfRecommended = $('.load-recommended-books').attr('data-numberOfRecords');
                start = start+11;
                $('.load-more-book').addClass('hidden');
                $('#load-more').removeClass('hidden');
                if(parseInt(numberOfRecommended) < start) {
                    $('.load-recommended-books').addClass('hidden');
                }
                $('[data-toggle="tooltip"]').tooltip();
                initPopover();
                closePopover();
            }
        });

    }
    function showSharedbook() {
        $('.load-more-book').removeClass('hidden');
        $('#load-more-shared').addClass('hidden');
        $.ajax({
            url: '/load-more-shared-book',
            method: 'post',
            data: {start:startShared,isMobile: isMobile()},
            dataType: 'html',
            success: function (data) {
                var sanitizedData = DOMPurify.sanitize(data);
                $('#shared-book-content .load-more-btn').remove();
                $('#shared-book-content').append(sanitizedData);
                startShared = startShared+11;
                $('.load-more-book').addClass('hidden');
                $('#load-more-shared').removeClass('hidden');
                var numberOfRecords = $('.load-shared-books').attr('data-numberOfRecords');
                if(parseInt(numberOfRecords) < startShared) {
                    $('.load-shared-books').addClass('hidden');
                }
                $('[data-toggle="tooltip"]').tooltip();
                initPopover();
                closePopover();
            }
        });
    }

    var box = $('.box');
    box.find('#box-confirm').text($('#box-msg-yes').text());
    box.find('#box-cancel').text($('#box-msg-no').text());

    /**banner home */
    $('.related-banner-slider').slick({
        slide: 'div',
        dots: true,
        infinite: false,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        prevArrow: '<button class="slick-prev"><i class="fa fa-chevron-left"></i></button>',
        nextArrow: '<button class="slick-next"><i class="fa fa-chevron-right"></i></button>',
        responsive: [
            {
                breakpoint: 1250,
                settings: {
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 950,
                settings: {
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 700,
                settings: {
                    slidesToShow: 1
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

    /**Related new books slider */
    $('.related-newbooks-slider').slick({
        slide: 'div',
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 5,
        prevArrow: '<button class="slick-prev"><i class="fa fa-chevron-left"></i></button>',
        nextArrow: '<button class="slick-next"><i class="fa fa-chevron-right"></i></button>',
        responsive: [
            {
                breakpoint: 1250,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 950,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 700,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 500,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1 
                }
            }
        ]
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
    $(document).on('click', '.cover-img', function (e) {
        window.location = $(this).find('a').attr('href');
    });
    $(document).on('click', '.beta-release .give-feedback', function (e) {
        /** Open review book modal & load course details if a positive response */
        $('#feedback-step').addClass('active feedback-modal');
        /** Show request modal */
        $('.review-book-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('.review-book-modal .close').show();
    });


    /** Click on three dot menu to show option list */
    $(document).on('click', '#more-btn', function (e) {
        e.preventDefault();
        $('.more').removeClass('show-more-menu');
        if ($(this).parent().find('.more-menu').is(':visible'))
            hideMenu($(this));
        else {
            showMenu($(this));
            e.stopPropagation();
        }
    });

    /** Click anywhere from 3 dot menu to close it */
    $(document).on('click', function () {
        var moreMenus = $('.recommended-list').find('.more-menu');
        $.each(moreMenus, function (i, e) {
            if ($(e).is(':visible')) {
                hideMenu($(e));
            }
        });
    });

    /** Show 3 dot menu */
    function showMenu(el) {
        el.parent().addClass('show-more-menu');
        el.attr('aria-hidden', false);
    }

    /** Hide 3 dot menu */
    function hideMenu(el) {
        el.parent().removeClass('show-more-menu');
        el.attr('aria-hidden', true);
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
    /** Desktop copy product link into clipboard  */
    $(document).on('click', '.dot-share-product-popup', function (e) {
        e.preventDefault();
        copyToClipboard($(this).find('a').attr('data-link'),'#share-book-modal');
    });

    /** Hide not mobile options from 3 dot menu */
    if (!isMobile())
        $('.more-menu-item.for-mobile').remove();
        
    // if (isMobile())
    //     $('.engage_img_desktop').remove();
    // if (!isMobile())
    //     $('.engage_img_mobile').remove();

    // if (isMobile())
    //     $('.content_engage_banner').remove();
    // if (!isMobile())
    //     $('.content_engage_banner_mobile').remove();

    $(document).ready(function(){
        $('.play').click(function () {
            if($(this).parent().prev().get(0).paused){
                $(this).parent().prev().get(0).play();
                $('.content').hide();
            }
        });
    
        $('.video').on('ended',function(){
            $('.content').show();
        });
    })
    // if (isMobile())
    //     $('.catchy_image_desktop').remove();
    // if (!isMobile())
    //     $('.catchy_image_mobile').remove();
        

    /** Click request book to open request book modal */
  /*  $(document).on('click', '.read-now-btn .btn-blue', function () {
        resetAddBookModal();
        let isbn = $(this).data('isbn');
        /!** Delete local storage item if it used in other page *!/
        localStorage.removeItem('selectedIsbn');
        /!** Add the new isbn item *!/
        localStorage.setItem('selectedIsbn', isbn);
        /!** Show request modal *!/
        $('.request-book-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('.request-book-modal .close').show();
    });*/

    /** Reset add book modal */
    function resetAddBookModal() {
        let firstStep = $('#step1');
        $('#checkOneReadingListMsg').hide();
        $('.modal-step').removeClass('active');
        firstStep.addClass('active');
        $(".multi_institutions").prop("checked", false);
    }

    /** Click close book request */
    $(document).on('click', '.reading_list_modal #close-modal,#browse_btn', function (e) {
        //reset the scroll
        $(".modal-copy-container").scrollTop(0);
        var addBookModal = $('.request-book-modal');
        var actualStepId = addBookModal.find('.modal-step.active').attr('id');
        localStorage.removeItem('readingListId');
        if (actualStepId === 'step2') {
            closeModal(addBookModal);
            resetAddBookModal();
        } else {
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text($('#cancel-list-msg').text());
            $('body').addClass('box-above-modal');
            box.on('click', '#box-cancel', function () {
                box.removeClass('active confirm-info');
                box.off('click');
                $('body').removeClass('box-above-modal');
            }).on('click', '#box-confirm', function () {
                box.removeClass('active confirm-info');
                closeModal(addBookModal);
                box.off('click');
                $('body').removeClass('box-above-modal');
                resetAddBookModal();
            });
        }
    });

    $('.reading-lists input').on('change', function () {
        var checkedReadingList = $('.reading-lists input:checked');
        if (checkedReadingList.length > 0) {
            /** show error message*/
            $('#checkOneReadingListMsg').hide();
        } else $('#checkOneReadingListMsg').show();
    });

    /**  delete book from recommendation when click on remove recommendation button*/
    $(document).on('click', '.remove-recommendation', function (e) {
        var isbn = DOMPurify.sanitize($(this).attr('data-isbn'));
        var box = $('.box');
        box.find('#box-confirm').text($('#btn-confirm').text());
        box.find('#box-cancel').text($('#cancel-btn-box').text());
        box.find('.confirm-msg').text($('.logged-in-tab').attr('data-alert'));
        box.attr('class', 'box active confirm-info');
        box.on('click', '#box-confirm', function () {
            //call ajax de delete book from recommendation
            box.removeClass('active confirm-info');
            box.off('click');
            $('body').removeClass('modal-open');
            $.ajax({
                url: $('#you_like').attr('data-url-delete'),
                method: 'POST',
                data: {isbn: isbn},
                success: function (response) {
                    //show success step
                    if (response.success) {
                        //remove book from listing
                        $('#recommendation-content #'+isbn).remove();
                        if($('#recommendation-content .recommended-books-list').length === 0){
                            $('.no-reading-recommendation').removeClass('hidden');
                        }
                        box.removeClass('active confirm-info');
                        box.off('click');
                        $('body').removeClass('modal-open');
                    }
                }
            });
        });
        box.on('click', '#box-cancel', function () {
            //close confirmation box
            box.removeClass('active confirm-info');
            box.off('click');
            $('body').removeClass('modal-open');
        });
    });

    /** remove book from shared book tab*/
    $(document).on('click', '.remove-recommendation-shared', function (e) {
        var isbn = DOMPurify.sanitize($(this).attr('data-isbn'));
        var box = $('.box');
        box.find('#box-confirm').text($('#btn-confirm').text());
        box.find('#box-cancel').text($('#cancel-btn-box').text());
        box.find('.confirm-msg').text($('.logged-in-tab').attr('data-alert'));
        box.attr('class', 'box active confirm-info');
        box.on('click', '#box-confirm', function () {
            //call ajax de delete book from shared recommendation
            box.removeClass('active confirm-info');
            box.off('click');
            $('body').removeClass('modal-open');
            $.ajax({
                url: $('#shared_by_colleague').attr('data-url-delete'),
                method: 'POST',
                data: {isbn: isbn},
                success: function (response) {
                    //show success step
                    if (response.success) {
                        //remove book from html content
                        $('#shared-'+isbn).remove();
                        if($('#shared-book-content .recommended-books-list').length === 0){
                            $('.no-shared-book').removeClass('hidden');
                        }
                        box.removeClass('active confirm-info');
                        box.off('click');
                        $('body').removeClass('modal-open');
                    }
                }
            });
        });
        box.on('click', '#box-cancel', function () {
            //close confirmation box
            box.removeClass('active confirm-info');
            box.off('click');
            $('body').removeClass('modal-open');
        });
    });

    /** Set Continue button at the bottom of the modal for mobile */
    if (window.innerWidth < 640)
        $('.modal-step-content').css('minHeight', $('.request-book-modal').height() - 100);
    /** Prevent showing the previous steps when clicking enter in the steps different input fields */
    $(document).on('keypress', '.validate', function (e) {
        /** If the keypress is Enter then prevent the action */
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    })
    window.history.forward();

    //get hash code at next page
    var hashcode = DOMPurify.sanitize(window.location.hash);
    //move page to any specific position of next page(let that is div with id "hashcode")
    $('html,body').animate({scrollTop: $('div'+hashcode).offset().top},'slow');
});
$(document).on("pageshow", function() {
    window.history.forward();
});

//disable the back btn navigator on home page
window.history.pushState(null, null, window.location.href);
window.onpopstate = function () {
    window.history.go(1);
};

