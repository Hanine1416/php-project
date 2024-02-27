$(document).ready(function () {
    var box = $('.box');
    let confirmText =  DOMPurify.sanitize($('#box-msg-yes').text() );
    box.find('#box-confirm').html(confirmText + ' <span></span>');
    let cancelText = sanitizeHTML($('#box-msg-no'));
    box.find('#box-cancel').text(cancelText);

    var faqForm = $('#formFaq'),
        newFaqBtn = $('#newFaqBtn'),
        editFaqBtn = $('#editFaqBtn');


    /** init tinymce foreach textArea with class wysiwyg */
    function initMCEall(e) {
        tinyMCE.init({
            max_chars: 200, // max. allowed chars
            default_link_target: "_blank",
            menubar: false,
            selector: e,
            statusbar: true,
            forced_root_block: false,
            ui_container: '.modal',
            plugins: ' print preview fullpage searchreplace autolink directionality  visualblocks visualchars fullscreen  link  table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help',
            toolbar1: ' bold italic underline | link ',
            init_instance_callback: function (editor) {
                $('.mce-tinymce').show('fast');
                $(editor.getContainer()).find(".mce-path").css("display", "none");
                $(editor.getContainer()).find(".tox-statusbar__branding").css("display", "none");
            }
        });
    }

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

    initMCEall('#faq-answer');

    /** Prevent Bootstrap dialog from blocking focusin */
    $(document).on('focusin', function (e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });

    /** Prevent links update from blocking focusin */
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".tox-textfield").length)
            e.stopImmediatePropagation();
    });

    /** validate faq form and return it's data */
    function validateFaqInformation() {
        var faqQuestion = $('#faq-question'),
            faqAnswer = tinyMCE.get('faq-answer').getContent({format: 'html'}),
            faqAnswerField = $('#faq-answer'),
            questionNameError = $('#questionError'),
            answerNameError = $('#answerError'),
            formValid = true;

        /** validation form */
        if (faqQuestion.val().length < 1) {
            formValid = false;
            questionNameError.removeClass('hide')
        }
        if (faqAnswer.length < 1) {
            formValid = false;
            answerNameError.removeClass('hide')
        }


        faqQuestion.on('keyup', function () {
            if ($(this).val().length < 1)
                questionNameError.removeClass('hide');
            else
                questionNameError.addClass('hide');
        });
        faqAnswerField.on('keyup', function () {
            if ($(this).val().length < 1)
                answerNameError.removeClass('hide');
            else
                answerNameError.addClass('hide');
        });

        if (formValid)
            return {
                answer: faqAnswer,
                question: faqQuestion.val()
            };
        else
            return null;
    }

    $('#formFaqPage').on('submit', function (e) {
        if (!$(this).hasClass('allowSubmit'))
            e.preventDefault();
    });
    $(document).on('click', '#formFaqPage .btn-submit', function () {
        $('#formFaqPage').addClass('allowSubmit').submit();
    });

    /** Click preview to display faq page for user  */
    $(document).on('click', '.preview-faq-btn:not(.complete-url)', function () {
        var block = $('.tabsList');
        block.html('');
        var elements = $('#sortable>li');
        var previewModal = $('.preview-faq-modal');
        $.each(elements,function(i,element) {
            var question=  DOMPurify.sanitize($(element).find('.faq-question').text());
            var answer = DOMPurify.sanitize($(element).find('.faq-answer').val());
            block.append(
                '<div class="tile-tab">' +
                    '<a class="title-accordion">'+question+' ' +
                    '<i class="expand-tile" data-toggle="tooltip" data-placement="bottom"></i>' +
                        '<i class="collapse-tile" data-toggle="tooltip" data-placement="bottom" ></i>' +
                    '</a>' +
                    '<div class="hiddenDescription content">' +
                        '<div class="descriptionContent">'+answer+'</div>' +
                    '</div>' +
                '</div>');
        });
        /** Show preview faq modal */
        previewModal.show().addClass('active');
    });

    /** Publish the new oder of elements */
    $(document).on('click','.publish-faq-btn', function () {

        var elements = $('#sortable>li');
        var url = $('.publish-faq-btn').data('url');
        var  data ={'faqs': []};

        $.each(elements,function(i,element) {
            var token = $(element).data('token');
             data['faqs'].push({'token': token,'newOrder': i});
        });
        console.log(data);
        if (data) {
            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'JSON',
                success: function (data) {
                    if (data.success) {
                        $('.btn-cancel-preview').click();
                    } else {
                        showErrorBox(false, 'an error occurred please repeat the process')
                    }
                }
            })
        }
    });

    /** Submit add new faq form */
    $(document).on('click', '.btn-save-faq:not(.loading)', function () {
        newFaqBtn.removeClass('hide');
        editFaqBtn.addClass('hide');
        var url = faqForm.data('url');
        /** submit the form by ajax request if valid */
        var data = validateFaqInformation();
        data.order = $('#sortable>li').length+1;
        if (data) {
            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'JSON',
                beforeSend: function () {
                    newFaqBtn.addClass('loading');
                },
                success: function (data) {
                    data = sanitizeHTML(data);
                    if (data.success) {
                        let token = sanitizeHTML(data.faq.token);
                        let editUrl = sanitizeHTML(data.editUrl);
                        let answer = sanitizeHTML(data.faq.answer);
                        var faq =
                            '<li class="ui-state-default" data-token="'+ token +'"  data-order="'+data.faq.order+'"> ' +
                            '<p class="faq-question" >' + data.faq.question +'</p>' +
                            '<input type="hidden" name="faq['+token+']" class="faq-answer">'+
                            '<i class="edit-faq" data-url="'+ editUrl +'" ></i>' + '<i class="delete-faq" data-url ="'+ editUrl +'"></i>' +
                            '<i class="sort-faq"></i></li>';
                        let sanitizeFaq = DOMPurify.sanitize(faq);
                        $('#sortable').append(sanitizeFaq);
                       // $('[name="faq['+token+']"').val(answer);
                        $('.btn-reset-form').click();
                    } else {
                        showErrorBox(false, 'an error occurred please repeat the process')
                    }
                    newFaqBtn.removeClass('loading');
                }
            })
        }
    });

    /** Edit faq information */
    $(document).on('click', '.edit-faq', function (e) {

        e.preventDefault();
        $('.edit-faq.selected').removeClass('selected');
        $(this).addClass('selected');
        /** extract old data from the list */
        var faqQuestion = $(this).closest('li').find('.faq-question').html(),
            faqAnswer = $(this).closest('li').find('.faq-answer').val();

        /** Bin value to edit form */
        var form = $('#formFaq');
        form.find('#faq-question').val(faqQuestion);
        form.find('#faq-answer').val(faqAnswer);

        /** Bind value tinymce content */
        tinymce.get('faq-answer').setContent(faqAnswer);

        /** Open the modal after filling the old data to the form */
        newFaqBtn.addClass('hide');
        editFaqBtn.removeClass('hide');
        $('#openModal').click();
    });

    /** Click save faq after editing */
    $(document).on('click','#editFaqBtn:not(.loading)',function () {
        var editingRow = $('.edit-faq.selected');
        var elem= editingRow.parent();
        var url = editingRow.data('url');
        /** validate form & return its data */
        var data = validateFaqInformation();
        editFaqBtn.addClass('loading');
        if (data) {
            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'JSON',
                success: function (data) {
                    let question = sanitizeHTML(data.faq.question)
                    let answer = sanitizeHTML(data.faq.answer);
                    if (data.success) {
                        elem.find('.faq-question').text(question);
                        elem.find('.faq-answer').val(answer);
                        $('.btn-reset-form').click();
                    } else {
                        showErrorBox(false, 'an error occurred please repeat the process');
                        $('.btn-reset-form').click();
                    }
                    editFaqBtn.removeClass('loading');
                    editingRow.removeClass('selected');
                }
            })
        }
    });

    /** Delete faq */
    $(document).on('click', '.delete-faq:not(.loading)', function (e) {
        e.preventDefault();
        var elem = $(this),
            url = elem.data('url');
        /** here show the confirm box */
        box.attr('class', 'box active confirm-info');
        box.find('.confirm-msg').text($('#delete-faq-msg').text());
        box.find('#box-cancel').css('display', '');
        box.on('click', '#box-confirm', function () {
            var btn = $(this);
            btn.prev().hide();
            $.ajax({
                url: url,
                method: 'post',
                dataType: 'json',
                beforeSend: function () {
                    elem.addClass('loading');
                },
                success: function (data) {
                    if (data.success)
                        elem.closest('li').remove();
                    else
                        showErrorBox(false, 'an error occurred please repeat the process');
                    elem.removeClass('loading')
                }
            });
            box.removeClass('active');
        });
        box.on('click', '#box-cancel', function () {
            box.attr('class', 'box');
            box.off('click');
        });
    });

    /** Reset add faq form */
    $('.btn-reset-form').on('click', function () {
            /** remove the marked row that was edited if exist */
            $('.editRow').removeClass('editRow');
            /** reset modal button to initial state */
            newFaqBtn.removeClass('hide');
            editFaqBtn.addClass('hide');
            /** remove all errors */
            $('.error:not(.hide)').addClass('hide');
            /** reset answer input */
            faqForm.find('#faq-question').val('');
            /** reset tinymce content */
            tinymce.get('faq-answer').setContent('');
        });

    /** Tab manipulator **/
    $(document).on('click touchstart','.accordion .title-accordion', function(j) {
        var elem = $(this);
        if(!elem.hasClass('active')){
            elem.next().show();
            $('.accordion .title-accordion.active').removeClass('active').next().hide();
            elem.addClass('active');
        }else{
            elem.removeClass('active');
            elem.next().hide();
        }
        j.preventDefault();
    });

    $("#sortable").sortable({
        connectWith: ".connectedSortable"
    });
});
