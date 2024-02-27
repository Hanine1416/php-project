/** Validate add reading list first step */
function validateStep1() {
    var valid = true;
    var step1 = $('#verify-account-modal #step1');
    var fields = step1.find('.validate:visible');
    fields.removeClass('warning');
    step1.find('.error').remove();
    $.each(fields, function (i, e) {
        if (!$(e).val()) {
            let title = DOMPurify.sanitize( $(e).prop('title'));
            $(e).addClass('warning')
                .parent().append('<span class="error">' + title + '</span>');
            valid = false;
        }
    });
    return valid;
}

/** Validate add reading list 2th step */
function validateStep2() {
    var valid = true;
    var urlInput = $('#url');
    /** Show error msg when user com from es or us*/
    var professionalUrl = $('#url');
    var urlAndFile = $('.url-and-upload-fields');
        urlAndFile.removeClass('warning').parent().find('.error').remove();
        var dropzoneEmpty = $('.dropzone')[0].dropzone.getAcceptedFiles().length === 0 ;
        var professionalUrlEmpty = (professionalUrl.val() === null || professionalUrl.val() === 'undefined' || professionalUrl.val() === '');
        if (professionalUrlEmpty && dropzoneEmpty && !$('#dropzone-upload').hasClass('has-file') ) {
            let title = DOMPurify.sanitize(professionalUrl.prop('title'));
            urlAndFile
                .addClass('warning')
                .parent().append('<span class="error" style="position: initial">' + title + '</span>');
            valid = false;
        } else if (ValidURL(urlInput.val())){
            $('.url-and-upload-fields').removeClass('warning');
            $('.url-file-container .error').remove();
        }
    return valid;
}
Dropzone.autoDiscover = false;

$(document).ready( function () {
    /** Call confirm close popup */
    var box = $('.box');
    box.find('#box-confirm').text($('#box-msg-yes').text());
    box.find('#box-cancel').text($('#box-msg-no').text());
    /** Declare variables */
    var institutionSelector = $('#institutions-selector-0');
    var subjectSelector = $('#program-selector-0');
    var formData = new FormData();
    var verifyAccount = {};
    var counter = 1;
    /** Init institution selector with select 2 */
    institutionSelector.select2({
        placeholder: "",
        dropdownParent: institutionSelector.parent()
    });
    /** Init subject selector with select 2 */
    subjectSelector.select2({
        placeholder: "",
        dropdownParent: subjectSelector.parent()
    });

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
        let deleteMsg = DOMPurify.sanitize($('.dropzone').data('delete-msg'));
        $('.dropzone').append( "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-image\"><img data-dz-thumbnail src=\"/assets/img/file-icon/file.png\"/></div>\n<a class=\"dz-remove delete-img\" href=\"javascript:undefined;\" >"+ deleteMsg + "</a> </div>");
    }
    /** Remove file img when click on delete button */
    $('.delete-img').on('click', function () {
        $('.dz-file-preview').remove();
        $('#dropzone-upload').removeClass('has-file');
    });

    /** Open modal when click on verify */
    $(document).on('click', '#verify-btn', function () {
        /** Reset modal */
        resetVerifyAccountModal();
        /** Show remove modal */
        $('#verify-account-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
    });

    /** Open modal when click on verify */
    $(document).on('click', '.ctaDetails #verify-btn, .read-now-btn #verify-btn', function () {
        /** Reset modal */
        resetVerifyAccountModal();
        $('#verify-account-modal #step1').removeClass('active');
        $('#verify-account-modal #step0').addClass('active');
        /** Show remove modal */
        $('#verify-account-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
    });

    /** Open modal when click on pending */
    $(document).on('click', '#pending-btn', function () {
        $('#pending-qualification-modal #step1').addClass('active');
        /** Show pending message modal */
        $('#pending-qualification-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
    });

    /** Click close book request */
    $(document).on('click', '#verify-account-modal #close-modal', function (e) {
        //reset the scroll
        $(".modal-copy-container").scrollTop(0);
        var verifyModal = $('#verify-account-modal');
        var actualStepId = verifyModal.find('.modal-step.active').attr('id');
        localStorage.removeItem('readingListId');
        if (actualStepId === 'step3' || actualStepId === 'step0') {
            closeModal(verifyModal);
            resetVerifyAccountModal();
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
                closeModal(verifyModal);
                box.off('click');
                $('body').removeClass('box-above-modal');
                resetVerifyAccountModal();
            });
        }
    });

    /** Click pending modal message */
    $(document).on('click', '#pending-qualification-modal #close-modal', function (e) {
        closeModal($('#pending-qualification-modal'));
    });

    /** Go next request step */
    $(document).on('click', '#verify-account-modal .goStep:not(.loading)', function (e) {
        e.preventDefault();
        var nextStep = $(this).data('target-step');
        var goNextStep = true;

        /** Save selected institution and program */
        switch (nextStep) {
            case "step2" :
                goNextStep = validateStep1();
                let selectedInstitution ;
                let selectedProgram ;
                let institutionInfo = {};
                let institutionName;
                verifyAccount.institutions =[];
                if (goNextStep) {
                    for (let i = 0; i < counter; i++) {
                        selectedInstitution = $('#institutions-selector-'+i+' option:selected');
                        selectedProgram = $('#program-selector-'+i+' option:selected');
                        if ( selectedInstitution.text() != '') {
                            institutionName = $.trim(selectedInstitution.text());
                        } else institutionName = $('#institutions-selector-'+i).parent().parent().find('#institutionName').val();
                        institutionInfo = {
                            institutionName: institutionName,
                            institutionId: selectedInstitution.attr('value'),
                            programName: $.trim(selectedProgram.text()) };
                        verifyAccount.institutions.push(institutionInfo);
                    }
                }
                break;
            case "step3" :
                goNextStep = validateStep2();
                var professionalUrl = $('#verify-account-modal #url');
                var dropzone = $('.dropzone')[0] ;
                verifyAccount.file = false;
                if (goNextStep) {
                    if ( professionalUrl.val() != null && professionalUrl.val() !== '') {
                        verifyAccount.url = professionalUrl.val();
                    }
                    if (dropzone.dropzone.getAcceptedFiles().length > 0 ){
                        formData.set("userFile",$('.dropzone')[0].dropzone.getAcceptedFiles()[0] );
                        verifyAccount.file = true;
                    }
                    goNextStep = false;
                    sendVerificationData(verifyAccount);
                }
                break;
        }
        if (goNextStep) {
            $(this).closest('.modal-step').removeClass('active');
            $('#verify-account-modal #' + nextStep).addClass('active');
        }
    });

    /** Submit book request validate & send data*/
    function sendVerificationData(verifyAccount) {

        if (validateStep2()) {
            submitVerificationRequest(verifyAccount, function (success, verifyAccount, response) {
                if (success) {
                    var verifyBtn = $('#verify-btn');
                    var banner =  $('.featuredSection').find('.not-qualified-banner-block');
                    $('#verify-account-modal #step2').removeClass('active');
                    $('#verify-account-modal #step3').addClass('active');
                    banner.length>0 ? banner.remove() : false;
                    verifyBtn.addClass('pending');
                    if ( verifyBtn.parent().hasClass('ctaDetails') || verifyBtn.parent().hasClass('read-now-btn')) {
                        $('.pending').attr('id','pending-btn');
                    } else {
                        $('.pending').removeAttr('id');
                        verifyBtn.text($('#pending-btn').text());
                    }
                }
            });
        }
    }

    /** Send verification data */
    function submitVerificationRequest(verifyAccount, callback) {

        var submitBtn = $('#submit-request-btn');
        submitBtn.addClass('loading');
        $.ajax({
            url: $('#formVerifyAccount').data('url'),
            method: 'POST',
            data: verifyAccount,
            success: function (response) {
                if (verifyAccount.file === true) {
                    $.ajax({
                        url: baseUrl + '/upload-file',
                        method: 'post',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (result) {
                            if (result.success) {
                                submitBtn.removeClass('loading');
                                callback(response.success, verifyAccount, response);
                            } else {
                                showErrorBox(result.message);
                            }
                        }
                    });
                }
               else {
                   submitBtn.removeClass('loading');
                   callback(response.success, verifyAccount, response);
                }
            },
            error: function (httpObj) {
                submitBtn.removeClass('loading');
                if (httpObj.status === 401)
                    showErrorBox($('#loggedOutMsg').text());
            }
        });
    }

    $(document).on('click', '#new-block-btn', function (e) {
        e.preventDefault();
        var institutionBlock = $('#verify-account-modal #single-institution');
        var validationMsg = $('#validation-msg').text();
        var selectInst = $('#select-institution').text();
        var selectProg = $('#select-program').text();
        var institution = $('#institution').text();
        var addBtn = $('#add-btn').text();
        var cancelBtn = $('#cancel-btn').text();
        var contextualHelp = $('#contextual-help-inst').text();
        var instLength = institutionBlock.data('institutions-length');
        var displaySelector = "display: block;";
        var displayInput = "display: none;";
        if (instLength == 0 ) {
             displaySelector = "display: none;";
             displayInput = "display: block;";
        }
        let program = '  <div class="institution-block block block-'+counter+'" >' +
            '<div class="small-12 medium-12 large-12 columns input-step pl-0 pr-0 right"><a id="delete-block-btn"></a></div>' +
            '<div class="small-12 medium-12 large-12 columns input-step institution-selector-block pl-0 pr-0 mb-24" style="'+displaySelector+'">\n' +
            '                                    <label class="label-title"\n' +
            '                                           for="institutions-selector-'+counter+'">'+selectInst+'</label>\n' +
            '                                    <select name="institutions-list" id="institutions-selector-'+counter+'" class="validate"\n' +
            '                                             title="'+validationMsg+'"\n' +
            '                                        <option value=""></option>\n' +
            '                                    </select>\n' +
            '                                <div class="">\n' +
            '                                            <a class="pull-right btn-new-institution decorate">'+addBtn+'</a>\n' +
            '                                        </div>\n' +
            '                                        <span class="contextual-txt mb-0">'+contextualHelp+'</span>\n'+
            '                                </div>\n' +
            '                                    <div class=" input-step institution-text-block small-12 medium-12 large-12 columns pl-0 pr-0 mb-24" style="'+displayInput+'">\n' +
            '                                        <label for="institutionName">'+institution+'</label>\n' +
            '                                        <input type="text" id="institutionName" name="institutionName" class="institution-name validate" title="'+validationMsg+'">\n' +
            '                                        <div class="small-12 medium-12 large-12 columns  pl-0 pr-0 hidden" style="'+displaySelector+'">\n' +
            '                                            <a class="pull-right btn-cancel-new-institution decorate ">'+cancelBtn+'</a>\n' +
            '                                        </div>\n' +
            '                                    </div>\n'+
            '                                <div class="small-12 medium-12 large-12 columns input-step pl-0 pr-0 mb-24">\n' +
            '                                    <label for="program-selector-'+counter+'"\n' +
            '                                           class="label-title ">'+selectProg+'</label>\n' +
            '                                    <select name="professions-list" id="program-selector-'+counter+'" class="validate"\n' +
            '                                            title="'+validationMsg+'"\n' +
            '                                            placeholder="">\n' +
            '                                        <option value=""></option>\n' +
            '                                    </select>\n' +
            '                                </div></div>';
        let sanitizeprogram = DOMPurify.sanitize(program);
        $('.institution-program-block').append(sanitizeprogram);
        var professions = institutionBlock.data('professions');
        /** Init institution selector with select 2 */
        $('#institutions-selector-'+counter).select2({
            placeholder: "",
            dropdownParent: $('#institutions-selector-'+counter).parent()
        });
        /** Init subject selector with select 2 */
        $('#program-selector-'+counter).select2({
            placeholder: "",
            dropdownParent: subjectSelector.parent()
        });
        loadInstitutions( institutionBlock.data('country'), $('#institutions-selector-'+counter));
        for (var profession in professions) {
            var professionOption = new Option();
            professionOption.text = profession;
            professionOption.value = profession;
            professionOption.setAttribute('data-type', profession);
            let sanitizeProfessionOption = DOMPurify.sanitize(professionOption);
            $('#program-selector-'+counter).append(sanitizeProfessionOption);
        }
        counter ++;
    });

    $(document).on('click', '#delete-block-btn', function (e) {
        e.preventDefault();
        $(this).parent().parent().remove();
    });

    /** load institution from ajax and populate selectors */
    function loadInstitutions(country,institutionSelector,callback){
        /** ajax call */
        $.post(baseUrl + "/institutions", {
            country: country,
            city: '',
            zipCode: '',
            state: ''
        }, function (data) {
            institutionSelector.html("<option value=''></option>");
            if (data.status) {
                institutionSelector.parent().show();
                institutionSelector.parent().removeClass('hidden');
                for (var key in data.results) {
                    var institutionOption = new Option();
                    institutionOption.text = data.results[key]['name'];
                    institutionOption.value = key;
                    institutionOption.setAttribute('data-type', data.results[key]['type']);
                    let newInstitutionOption = DOMPurify.sanitize(institutionOption);
                    institutionSelector.append(newInstitutionOption);
                }
                if (Object.keys(data.results).length > 200 && isIE()) {
                    institutionSelector.select2({minimumInputLength: 2});
                }
                institutionSelector.parent().find('.warning').removeClass('warning');
                institutionSelector.parent().find('.error').remove();
                $('.select2-selection__placeholder').remove();
            }
            if (callback)
                callback();
        });
    }
    /** Reset account verification modal */
    function resetVerifyAccountModal() {
        let firstStep = $('#verify-account-modal #step1');
        let url = $('#verify-account-modal #url');
        let instSelector = $('#institutions-selector-0');
        $('.modal-step').removeClass('active');
        instSelector.val("").trigger('change');
        $('#verify-account-modal #program-selector-0').val("").trigger('change');
        $('#verify-account-modal #institutionName').val('');
        url.val(url.data('user-url'));
        firstStep.addClass('active');
        $('.dropzone')[0].dropzone.removeAllFiles();
        $('.institution-program-block .block').remove();
        var instLength = $('#verify-account-modal #single-institution').data('institutions-length');
        $('.block-0 .institution-selector-block').show();
        $('.block-0 .institution-text-block').hide();
        $('.block-0 .btn-cancel-new-institution').parent().hide();
        if ( instLength == 0 ) {
            $('.block-0 .institution-selector-block').hide();
            $('.block-0 .institution-text-block').show();
            $('.block-0 .btn-cancel-new-institution').parent().hide();
        }
        counter = 1;
    }

    /** Click other institution to show text input */
    $(document).on('click', '.btn-new-institution', function () {
        var currentInstitutionBlock = $(this).closest('.institution-block');
        currentInstitutionBlock.find('.institution-selector-block').hide();
        currentInstitutionBlock.find('.institution-text-block').show();
        currentInstitutionBlock.find('.btn-cancel-new-institution').parent().show();
    });

    /** Click cancel new institution to show dropdown */
    $(document).on('click', '.btn-cancel-new-institution', function () {
        var currentInstitutionBlock = $(this).closest('.institution-block');
        currentInstitutionBlock.find('.institution-selector-block').show();
        currentInstitutionBlock.find('.institution-text-block').hide();
        currentInstitutionBlock.find('.btn-cancel-new-institution').parent().hide();
    });
    $(document).on('click', '.controls_books .request, .read-now-btn a', function () {
        var isbn = $(this).attr('data-isbn')?$(this).attr('data-isbn'):$(this).parent().attr('data-isbn');
        console.log(isbn);
        if(isbn) {
            $.post(baseUrl + "/save-book", {
                isbn: isbn,
            });
        }
    });
});

$('#verify-account-modal #single-institution').on('select2:open', function (e) {
    const evt = "scroll.select2";
    $(e.target).parents().off(evt);
    $(window).off(evt);
});