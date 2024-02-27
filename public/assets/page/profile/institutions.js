/** Check if institution block has been updated */
function institutionBlockChanged(block) {
    var changed = false;
    $.each(block.find('select,input'), function (i, e) {
        /** Check if the element is a checkbox & was unchecked or checked */
        if ($(e).attr('type') === "checkbox" && $(e).prop('checked') != $(e).data('value')) {
            changed = true;
        } else if (($(e).val() != $(e).data('value') && !($(e).val() === null && $(e).data('value') == "")) && $(e).attr('type') !== "hidden" && $(e).attr('type') !== "checkbox") {
            changed = true;
        }
        if (changed)
            return false;
    });
    return changed;
}

function loadInstitutionInsideModal(elem){
    var institutionDetails = elem.closest('.institution-details-block');
    var instId = institutionDetails.data('inst-id');
    var institutionId = institutionDetails.data('institution-id');
    var institutionName = institutionDetails.data('institution-name');
    var departmentId = institutionDetails.data('department-id');
    var departmentName = institutionDetails.data('department-name');
    var profession = institutionDetails.data('profession');
    var speciality = institutionDetails.data('speciality');
    var isPrimary = institutionDetails.data('primary');
    $('#primary-block').css('display',isPrimary?'none':'block');
    $('#institution_id').val(instId);
    if(institutionId)
        $('#institution_institutionId').data('value',institutionId).val(institutionId).trigger('change.select2').trigger('change');
    else{
        $('.btn-new-institution').click();
    }
    $('#institution_institutionName').data('value',institutionName).val(institutionName);
    if(departmentId){
        $('#institution_departmentId').data('value',departmentId)
            .append('<option selected value="'+departmentId+'">'+departmentName+'</option>');
    }else
        $('.btn-new-department').click();
    $('#institution_departmentName').data('value',departmentName).val(departmentName);
    $('#institution_profession').data('value',profession).val(profession).trigger('change.select2');
    $('#institution_speciality').data('value',speciality).append('<option value="'+speciality+'" selected>'+speciality+'</option>');
    $('#institution_isPrimary').data('value',isPrimary).prop('checked',isPrimary);
}

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
    var box = $('.box');
    let msgYes = DOMPurify.sanitize($('#box-msg-yes').text());
    box.find('#box-confirm').html(msgYes+' <span></span>');
    let msgNo = DOMPurify.sanitize($('#box-msg-no').text());
    box.find('#box-cancel').text(msgNo);
    /** Click add new institution to open modal */
    $(document).on('click', '#new-institution-btn', function (e) {
        e.preventDefault();
        modalFix(function () {
            $('body,html').addClass('modal-open scrollable');
        });
        $('#institution-form').find('.input-field').removeClass('readOnly');
        $('.institution-modal').removeClass('edit-mode').addClass('active').fadeIn(200, function () {
            $('.institution-modal  .close-institution-modal').show();
        });
    });

    /** Click edit institution open modal */
    $(document).on('click', '.edit-institution-btn', function (e) {
        e.preventDefault();
        modalFix(function () {
            $('body,html').addClass('modal-open scrollable');
        });
        loadInstitutionInsideModal($(this));
        var hasRequest = $(this).closest('.institution-details-block').data('has-request');
        if(hasRequest){
            $('.institution-modal #disableInstitution').removeClass('hidden');
            $('.institution-modal #deleteInstitution').addClass('hidden');
            setTimeout(function () {
                $('.institution-modal .fieldset .input-field:not(#primary-block)').addClass('readOnly');
            },500);
        }
        $('.institution-modal').addClass('active edit-mode').fadeIn(200, function () {
            $('.institution-modal  .close-institution-modal').show();
        });
    });

    /** Check tile has new update */
    $(document).on('change keyup','.institution-modal.edit-mode input,.institution-modal.edit-mode select',function () {
        var institutionBlock = $(this).closest('.institution-block');
        institutionBlock.find('#updateInstitution').attr('disabled', !institutionBlockChanged(institutionBlock))
    });

    /** Click close institution modal */
    $(document).on('click','.close-institution-modal', function (e) {
        if(!$('#updateInstitution').attr('disabled')){
            $('body').addClass('box-above-modal');
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text($('#confirm-close-msg').text());
            box.on('click', '#box-cancel', function () {
                box.removeClass('active confirm-info');
                box.off('click');
                $('body').removeClass('box-above-modal');
            }).on('click','#box-confirm',function () {
                box.removeClass('active confirm-info');
                closeInstitutionModal();
                box.off('click');
                $('body').removeClass('box-above-modal');
            });
        }else
            if($('.institution-modal').hasClass('active'))
                closeInstitutionModal();
        $('.institution-modal .fieldset .input-field:not(#primary-block)').removeClass('readOnly');
    });

    /** Submit new institution form */
    $(document).on('click','#addInstitution',function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        submitInstitutionFromModal(form,true,function (result) {
            if(result.success){
                $('.country-block').remove();
                var institutionContainer = $('#institutions-container');
                /** if the new institution is primary put it in top */
                if(result.institution.primary){
                    $('.institution-details-block[data-primary="1"]').data('primary',0);
                    institutionContainer.prepend(result.view);
                }else{
                    var firstDisabledInstitution = $('.institution-details-block.disabled-inst').first();
                    if(firstDisabledInstitution.length>0)
                        $(result.view).insertBefore(firstDisabledInstitution);
                    else
                        $(result.view).insertBefore('.add-institution-button');
                       /* institutionContainer.append(result.view);*/
                }
                /** Close modal */
                closeInstitutionModal();
            }
            form.removeClass('loading');
        })
    });

    /** Submit edit institution form */
    $(document).on('click','#updateInstitution',function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var hasRequest = form.find('.institution-details-block').data('has-request');
        var deleteBtn = $('#deleteInstitution');
        deleteBtn.text(hasRequest?deleteBtn.data('disable'):deleteBtn.data('delete'));
        submitInstitutionFromModal(form,false,function (result) {
            form.find('.fieldset').addClass('readOnly');
            if(result.success){
                var institutionContainer = $('#institutions-container');
                var editElem = $(".institution-details-block[data-inst-id='"+result.institution.id+"']");
                if(result.institution.primary){
                    $('.institution-details-block[data-primary="1"]').data('primary',0);
                    institutionContainer.prepend(result.view);
                    editElem.remove();
                }else
                    editElem.replaceWith(result.view);
                /** Close modal */
                closeInstitutionModal();
            }
            form.removeClass('loading');
        })
    });

    /** Click delete institution */
    $(document).on('click', '#deleteInstitution', function () {
        var block = $(this).closest('.institution-block');
        var institutionId = $('#institution_id').val();
        /** here show the confirm box */
        box.attr('class', 'box active confirm-info');
        box.find('.confirm-msg').text($('#delete-institution-msg').text());
        box.find('#box-cancel').css('display','');
        box.on('click', '#box-confirm', function () {
            var btn = $(this);
            btn.prev().hide();
            $.ajax({
                url: baseUrl + '/user/institution',
                method: 'delete',
                data: {institution: institutionId},
                dataType: 'JSON',
                beforeSend:function(){
                    block.addClass('loading');
                    btn.addClass('loading');
                },
                success: function (data) {
                    btn.removeClass('loading');
                    if (data.success) {
                        let instId = DOMPurify.sanitize(institutionId);
                        var viewDetails =  $('.institution-details-block[data-inst-id="'+instId+'"]');
                        /** Check if this block was primary */
                        let primary =  DOMPurify.sanitize(data.primary);
                        let dataValue = DOMPurify.sanitize(block.find('.isPrimary').data('value'));
                        if (dataValue)
                            $('.institution-details-block[data-inst-id="'+primary+'"]').data('primary',1);
                        if(data.disabled){
                            viewDetails.addClass('disabled-inst');
                            viewDetails.insertBefore('.add-institution-button');
                            /*viewDetails.appendTo('#institutions-container');*/
                        }else
                            viewDetails.remove();
                        /** Close modal */
                        closeInstitutionModal();
                        box.removeClass('active confirm-info');
                        btn.prev().show();
                    }else{
                        showErrorBox(data.reply);
                    }
                    block.removeClass('loading');
                    btn.removeClass('loading');
                },
                error:function (httpObj) {
                    block.removeClass('loading');
                    btn.removeClass('loading');
                    if(httpObj.status===401)
                        showErrorBox($('#loggedOutMsg').text());
                    else
                        showErrorBox($('#error').text());
                }
            });
            box.off('click');
        });
        box.on('click', '#box-cancel', function () {
            box.attr('class', 'box');
            box.off('click');
        });
    });

    /** Disable institution */
    $(document).on('click', '#disableInstitution', function () {
        var block = $(this).closest('.institution-block');
        var institutionId = $('#institution_id').val();
        var btn = $(this);
        $.ajax({
            url: baseUrl + '/user/institution/disable',
            method: 'PUT',
            data: {institution: institutionId},
            dataType: 'JSON',
            beforeSend: function () {
                btn.addClass('loading');
                block.addClass('loading')
            },
            success: function (data) {
                if (data.success) {
                    let instId = DOMPurify.sanitize(institutionId);
                    var viewDetails =  $('.institution-details-block[data-inst-id="'+instId+'"]');
                    /** Check if this block was primary */
                    let primary = DOMPurify.sanitize(data.primary);
                    if (block.find('.isPrimary').data('value'))
                        $('.institution-details-block[data-inst-id="'+dprimary+'"]').data('primary',1).prependTo('#institutions-container');
                    viewDetails.addClass('disabled-inst');
                    viewDetails.insertBefore('.add-institution-button');
                    /*viewDetails.appendTo('#institutions-container');*/
                    /** Close modal */
                    closeInstitutionModal();
                } else {
                    showErrorBox(data.reply);
                }
                block.removeClass('loading');
                btn.removeClass('loading');
            },
            error:function (httpObj) {
                block.removeClass('loading');
                btn.removeClass('loading');
                if(httpObj.status===401)
                    showErrorBox($('#loggedOutMsg').text());
                else
                    showErrorBox($('#error').text());
            }
        })

    });
});

