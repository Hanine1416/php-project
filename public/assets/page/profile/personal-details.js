Dropzone.autoDiscover = false;

/** Check if personal details block has been updated */
function profileBlockChanged(block) {
    var changed = false;
    $.each(block.find('select[data-value],input[data-value]'), function (i, e) {
        /** Check if the element is a checkbox & was unchecked or checked */
        if ( ($(e).attr('type') === "checkbox" && $(e).prop('checked') != $(e).data('value'))) {
            changed = true;
        } else if ( ($(e).val() != $(e).data('value') && !($(e).val() === null && $(e).data('value') == "")) && $(e).attr('type') !== "hidden" && $(e).attr('type') !== "checkbox") {
            changed = true;
        }
        if (changed)
            return false;
    });
    if($('.dropzone').length > 0) {
        if ($('.dropzone')[0].dropzone.getAcceptedFiles().length>0) {
            changed = true;
        }
    }

    return changed;
}

$(document).ready(function () {

    /** Initialise dropzone plugin*/
    $('.dropzone' ).each(function (i, el) {
        $(el).find('.upload-btn').on('click',function (e) {
            e.preventDefault();
            $(el).click();
            $(this).blur();
        });
        const name = 'g_' + $(el).data('field');
        var myDropzone = new Dropzone(el, {
            url: window.location.pathname,
            parallelUploads: 100,
            maxFiles: 1,
            paramName: name,
            thumbnailHeight: 120,
            thumbnailWidth: 100,
            addRemoveLinks: true,
            resizeWidth:100,
            resizeHeight:120,
            dictDefaultMessage: $(el).data('drag-msg'),
            dictRemoveFile: $(el).data('delete-msg'),
            acceptedFiles:'.rtf,.doc, .docx, .png , .pages, .jpg,.pdf',
            autoProcessQueue:false

        }).on("complete", function(file) {
            myDropzone.removeFile(file);
        }).on("addedfile", function(file){
            /** Get file extension */
            let ext = file.name.split('.').pop();
            $('.dropzone').removeClass('file-img');
            /* remove error after uploading file*/
            $('.url-and-upload-fields').removeClass('warning');
            $('.url-file-container .error').remove();
            /** Check image extension */
            if (ext !== 'png' && ext !== 'jpg' && ext !== 'jpeg'){
                this.emit("thumbnail", file, "/assets/img/file-icon/file.png");
                $('.dropzone').addClass('file-img');
            }
            $('#update-detail-btn').attr('disabled', false);
            if (file.size > 1000000) {
                showErrorBox($('#max-size-file-upload').text());
                myDropzone.removeFile(file);
            }
        }).on("removedfile",function () {
           $('#update-detail-btn').attr('disabled', !profileBlockChanged($('#edit-details-form')));
        });

    });

    /** If the user has already a file uploaded show file img*/
    if ($('#dropzone-upload').hasClass('has-file') ) {
        $('.dropzone').append( "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-image\"><img data-dz-thumbnail src=\"/assets/img/file-icon/file.png\"/></div>\n<a class=\"dz-remove delete-img\" href=\"javascript:undefined;\" >"+ $('.dropzone').data('delete-msg') + "</a> </div>");
    }
    /** Remove file img when click on delete button */
    $('.delete-img').on('click', function () {
        $('.dz-file-preview').remove();
        $('#dropzone-upload').removeClass('has-file');
        var fileId = $('#dropzone-upload').data('drag-file-id');
        $.ajax({
            url: baseUrl + '/update-profile-file-provided',
            method: 'post',
            data: {'fileId' : fileId },
            dataType: 'JSON',
            success: function () {
            }
        });
    });

    var country = $('#details_country');
    var phone = $('#details_mobile');
    country.select2({
        placeholder: "",
        dropdownParent: country.parent()
    });

    /** Cancel book request */
    $('.cancelRequest').on('click', function () {
        if (confirm($('#cancelRequest').text())) {
            $(".cancelRequest").attr('disabled', 'disabled');
            return true;
        } else {
            return false;
        }
    });

    /** Check when edit details form changed then enable update button */
    $(document).on('change keyup', '#edit-details-form select,#edit-details-form input', function () {
        var form = $(this).closest('form');

        form.find('#update-detail-btn').attr('disabled', !profileBlockChanged(form))
    });

    /** Submit edit personal details form */
    $(document).on('click', '#update-detail-btn', function (e) {
        e.preventDefault();
        var button = $(this);
        var form = button.closest('form');
        var fields = form.find('.validate');
        fields.removeClass('warning').parent().find('.error').remove();
        fields.parent().find('.error-phone').remove();
        var valid = true;

        /** Validate all fields */
        $.each(fields, function (key, element) {
            if (($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '') && $(element).is(':visible') && !$(element).hasClass('empty')) {
                $(element) .addClass('warning');
                if ($(element).parent().hasClass('country') ) {
                    let invalidtext =  DOMPurify.sanitize($(element).data('invalid'));
                    $(element).parent()
                        .append('<span class="error" style="position: relative;">' + invalidtext + '</span>');
                }
                else
                {
                    let title =  DOMPurify.sanitize( $(element).prop('title') );
                    $(element).parent()
                        .append('<span class="error" style="position: relative;">' + title + '</span>');
                }
                valid = false;
            }
        });

        /** Validate url input */
        if($('#details_url').length > 0) {
            var urlInput = $('#details_url');
            if (urlInput.val().length > 0 && !ValidURL(urlInput.val())) {
                let noUrl =  DOMPurify.sanitize(urlInput.attr("data-no-url"));
                urlInput.addClass('warning')
                    .parent().append('<span class="error">' + noUrl + '</span>');
                valid = valid && false;
            }
        }

        /** Validate phone number */
        if (!phone.hasClass('warning') && !validatePhoneNumber(phone, country.find('option:selected').data('st'))) {
            return;
        }

        let region = $('#current_region').data('region');
        /** Show error msg when user com from es or us*/
        var professionalUrl = $('#details_url');
        var urlAndFile = $('.url-and-upload-fields');
        if ( region === 6 || region === 0 || region === 9) {
            urlAndFile.removeClass('warning').parent().find('.error').remove();
            var dropzoneEmpty = $('.dropzone')[0].dropzone.getAcceptedFiles().length === 0 ;
            var professionalUrlEmpty = (professionalUrl.val() === null || professionalUrl.val() === 'undefined' || professionalUrl.val() === '');
            if (professionalUrlEmpty && dropzoneEmpty) {
                let professionalUrlTitle =  DOMPurify.sanitize(professionalUrl.prop('title'));
                urlAndFile
                     .addClass('warning')
                    .parent().append('<span class="error" style="position: initial">' + professionalUrlTitle + '</span>');
                 valid = false;
            } else if (ValidURL(urlInput.val())){
                $('.url-and-upload-fields').removeClass('warning');
                $('.url-file-container .error').remove();
            }
        }
        /** Ajax call */
        if (valid) {
            var data = form.serializeArray();
            $.ajax({
                url: form.attr('action'),
                method: 'PUT',
                data: data,
                dataType: 'JSON',
                beforeSend: function () {
                    button.addClass('loading');
                    form.closest('fieldset').attr('disabled', true)
                },
                success: function (response) {
                    if (response.success) {
                        if ((region === 7 || region===4 || region===6 || region===11 || region === 9 ) && region !== 1) {
                            if ( ($('.dropzone')[0].dropzone.getAcceptedFiles().length>0) ) {
                                let formData = new FormData();
                                formData.append("userFile",$('.dropzone')[0].dropzone.getAcceptedFiles()[0] );
                                $.ajax({
                                    url: baseUrl + '/upload-file',
                                    method: 'post',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function (result) {
                                        if (result.success) {
                                            /** Set each input data-value to the new value */
                                            $.each(form.find('[data-value]'), function (i, elem) {
                                                $(elem).data('value', elem.value);
                                            });
                                            button.attr('disabled', true);
                                            window.location=form.attr('action');
                                        } else {
                                            showErrorBox(result.message);
                                        }
                                    }
                                });
                            } else {
                                /** Set each input data-value to the new value */
                                $.each(form.find('[data-value]'), function (i, elem) {
                                    $(elem).data('value', elem.value);
                                });
                                button.attr('disabled', true);
                                window.location=form.attr('action');
                            }
                        } else {
                            /** Set each input data-value to the new value */
                            $.each(form.find('[data-value]'), function (i, elem) {
                                $(elem).data('value', elem.value);
                            });
                            button.attr('disabled', true);
                            window.location=form.attr('action');
                        }
                    } else
                        showErrorBox(response.message);
                    button.removeClass('loading');
                    form.closest('fieldset').attr('disabled', false);

                }
            });
        }

    });

    /** Remove field error if they got a value */
    $(document).on('change keyup', 'input,select', function () {
        if (($(this).val() && $(this).hasClass('warning')) || ($(this).attr('type') === "checkbox" && $(this).is(':checked')))
            $(this).removeClass('warning').parent().find('.error').remove();

        /** Validate phone number */
        if($(this).attr('id')===phone.attr('id') && $(this).val().length>0)
            validatePhoneNumber($(this), country.find('option:selected').data('st'));

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

});
