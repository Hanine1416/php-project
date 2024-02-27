/** Reset review/renew single course modal */
function resetReviewModal() {
    $('.review-rating-book-modal .modal-step.active').removeClass('active');
    $('.review-rating-book-modal .error').remove();
    $('review-rating-book-modal .warning').removeClass('warning');
    $('#feedback-step .review-container .star').removeClass('active');
    $('#feedback_public, #feedback_name_public').attr('checked',false);
    $('#feedBack').val('');
    if ($(".modal-backdrop")[0]){
        $(".modal-backdrop").remove();
    }
}

/** Validate current review step  for both single & multiple*/
function currentStepValid() {
    var valid = true;
    var fieldsToValidate = $('.modal-step.active .validate:visible');
    var feedbackPublic =  $('#feedback_name_public');
    fieldsToValidate.removeClass('warning');
    $('.modal-step.active').find('.error').remove();
    $.each(fieldsToValidate, function (i, e) {
        if (!$(e).val()) {
            let title = DOMPurify.sanitize($(e).prop('title'));
            $(e).addClass('warning')
                .parent().append('<span class="error">' + title + '</span>');
            valid = false;
        }
    });

    feedbackPublic.removeClass('warning');
    feedbackPublic.find('.error').remove();
        if ($('#feedback_name_public:checked').length === 0) {
            feedbackPublic.addClass('warning');
                valid = false;
        }
    return valid;
}

$(document).ready(function () {

    /** star reviews **/
    $('.review-rating-book-modal .star').on('click',function(e){
        e.preventDefault();
        var star = $('.review-rating-book-modal .review-container .star');
        var selected = star.index($(this));

        if ( $(this).hasClass("active") && selected == $('.review-rating-book-modal .star.active').length-1) {
            $(this).removeClass("active");
        } else {
            star.each(function( index ) {
                if(index <= selected){
                    $(this).addClass("active");
                }else{
                    $(this).removeClass("active");
                }
            });
        }


    });

    /** Open review book modal & load course details if a positive response */
    $(document).on('click', '.review-book', function () {
        resetReviewModal();
        /** Open review book modal & load course details if a positive response */
        $('#feedback-step').addClass('active feedback-modal');
        /** Show request modal */
        $('.review-rating-book-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('.review-rating-book-modal .close').show();
    });

    /** Submit book review */
    $(document).on('click', '.submit-book-review', function (e) {
        e.preventDefault();
        var btn = $(this);
        if (currentStepValid()) {
            var data = {};
                var feedBackText = $('#feedBack').val();
                if(feedBackText.length>0)
                    data.feedback = {
                        message: feedBackText,
                        public: $('#feedback_public').is(':checked') ? 1 : 0,
                        name_public: $('#feedback_name_public').is(':checked') ? 1 : 0,
                        rating: $('.review-rating-book-modal .star.active').length,
                        isbn: $('#feedback-step .popup_header').data('book_isbn')
                    } ;
            submitCoursesReviews(data, btn);
        }
    });

    /** Set Continue button at the bottom of the modal for mobile */
    if (window.innerWidth < 640)
        // $('.modal-step-content').css('minHeight', $('.review-rating-book-modal').height() - 86);
        // $('.modal-step-content').css('minHeight', $('.export-books-modal').height() - 86);

    /** Show message after check show publicly feedback*/
    $(document).on('click', '#feedback_public', function (e) {
        if ( $('#feedback_public').is(':checked')) {
            $('.public-info').show();
        }
        else $('.public-info').hide();
    });

    /* Prevent showing the previous steps when clicking enter in the steps different input fields */
    $(document).on('keypress', '.validate', function (e) {
        /* If the keypress is Enter then prevent the action */
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });


    /** Submit courses review */
    function submitCoursesReviews(data, btn) {
        $.ajax({
            url: btn.closest('form').data('url'),
            method: 'POST',
            data: data,
            dataType: 'JSON',
            beforeSend: function () {
                btn.addClass('loading');
            },
            success: function (response) {
                if (response.success) {
                    /** Hide actual step */
                    $('.modal-step.active').removeClass('active');
                    /** Show success message */
                    $('#finish-step').addClass('active');
                    btn.removeClass('loading');
                    urlrate = $('.path_for_ajax').attr('url-data');
                    urlaverage = $('.path_for_ajax_average').attr('url-data');
                    $.get(urlrate, function( data ) {
                        let newData = DOMPurify.sanitize(data);
                        $('.content_review_rating_comments').html(newData);
                    });
                    $.get(urlaverage, function( average ) {
                        let newAverage = DOMPurify.sanitize(average);
                        $('.average_reviews').html(newAverage);
                    });
                }
            },
            error: function (httpObj) {
                btn.removeClass('loading');
                if (httpObj.status === 401)
                    showErrorBox($('#logout-feedback-msg').text());
                else
                    showErrorBox($('#error').text());
            }
        });
    }
});
