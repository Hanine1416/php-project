(function(EmailsInput) {
  'use strict'
    var box = $('.box');
    $('#share-book-modal').on('shown.bs.modal', function (e) {
        const inputContainerNode = document.querySelector('#emails-input')
        const emailsInput = EmailsInput(inputContainerNode);
        // expose instance for quick access in playground
        window.emailsInput = emailsInput;
    })
    $(document).on('click', '#share-book, .share-book', function(){

        var title   = $(this).attr('data-title');
        var subtitle = $(this).attr('data-subtitle');
        var isbn = DOMPurify.sanitize($(this).attr('data-isbn'));
        var editionNumber = $(this).attr('data-editionNumber');
        if(isbn) {
            //set Modal Data
            $('#share-book-modal .book_title').text(title);
            $('#share-book-modal .book_subtitle').text(subtitle);
            if(editionNumber) {
                $('#share-book-modal .edition_num').text( $('#share-book-modal .edition_num').attr('data-text-edition')+ ' ' +editionNumber);
            }
            $("#share-book-modal .popup-title").attr('data-book_isbn',isbn);
            var src = DOMPurify.sanitize($('#'+isbn).find('img').attr('src'));
            if (!src){
                src = DOMPurify.sanitize($('#shared-'+isbn).find('img').attr('src'));
            }
            $('#share-book-modal .cover').attr('src',src);
            var url =$('#share-book-modal .dot-share-product-popup').find('a').attr('data-link');
            if (url){
                var elements = url.split('/');
                elements.pop();
                elements.push(isbn);
                var nouvelleURL = elements.join('/');
                $('#share-book-modal .dot-share-product-popup').find('a').attr('data-link',nouvelleURL);
            }

        }
    });

    $('.share-btn').on('click',function () {
        const emails = emailsInput.getValue();
        var message = $('#msgShare').val();
        var isbn  = $("#share-book-modal .popup-title").attr('data-book_isbn');
        //if no mail valid or we have at least on invalid mail show error msg
        if(emails.length === 0 && $('.email-chip.invalid').length === 0) {
            $('.error-invalid-mail').addClass('hidden');
            $('.error-no-mail').removeClass('hidden');
            $('#emails-input .emails-input,.error-no-mail').addClass('warning');
        } else if($('.email-chip.invalid').length > 0){
            $('.error-no-mail').addClass('hidden');
            $('.error-invalid-mail').removeClass('hidden');
            $('#emails-input .emails-input,.error-invalid-mail').addClass('warning');
        } else {
            $('.error-no-mail').addClass('hidden');
            $('#emails-input .emails-input').removeClass('warning');
            //do ajax call
            //show warning modal if no message
            if($('#msgShare').val().length === 0) {
                box.find('#box-confirm').text($('#share-msg-yes').text());
                box.find('#box-cancel').text($('#box-msg-yes').text());
                box.attr('class', 'box active confirm-info');
                box.find('.confirm-msg').text($('#no-share-msg').text());
                box.on('click', '#box-cancel', function () {
                  sendData(emails, message, isbn);
                });
                box.on('click', '#box-confirm', function () {
                    box.removeClass('active confirm-info');
                    box.off('click');
                    $('body').removeClass('modal-open');
                });
            } else {
                //call ajax to save data
                sendData(emails, message, isbn);
            }
        }
    });

    $('#share-book-modal').on('hidden.bs.modal', function () {
        //clear mail input
        var node = document.getElementById('emails-input');
        node.innerHTML = ""
        //clear textarea
        $('#share-book-modal .cover').attr('src','');
        $('#share-book-modal .detailDescription .book_title, #share-book-modal .detailDescription .book_subtitle,#share-book-modal .detailDescription .edition ').text('');
        $('#msgShare').val('');
        $('#share-book-modal #step1').addClass('active');
        $('#share-book-modal #step2').removeClass('active');
        $('.error-no-mail, .error-invalid-mail').addClass('hidden');
        var box = $('.box');
        box.find('#box-confirm').text($('#box-msg-yes').text());
        box.find('#box-cancel').text($('#box-msg-no').text());
    });
    $('#add_mail_btn').on('click', function () {
        //trigger the keypress event
        var e = jQuery.Event("keypress");
        e.which = 44; // # Some key code value
        $(document).trigger(e);
    })
    function sendData(emails, message, isbn) {
        if(emails.length > 0) {
            $.ajax({
                url: $('#share-book-modal').data('url'),
                method: 'POST',
                data: {emails: emails, message:message, isbn: isbn},
                success: function (response) {
                    //show success step
                    if (response.success) {
                        box.removeClass('active confirm-info');
                        box.off('click');
                        $('body').removeClass('modal-open');
                        $('#share-book-modal #step1').removeClass('active');
                        $('#share-book-modal #step2').addClass('active');
                    }
                }
            });
        }
    }
    $(document).on('click', '.continue_browsing_btn', function(){
        $('#share-book-modal').modal('hide');
    });
}(window.lib.EmailsInput, window.lib.utils.random))
