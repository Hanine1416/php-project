$(document).ready(function () {
    var country = "";
    /** Click add new institution to open modal */
    $(document).on('click','#new-institution-btn',function (e) {
        e.preventDefault();
        $('.institution-modal').addClass('active').fadeIn(200, function () {
            $('.institution-modal .close').show();
        });
        if ( $('#institutions-selector').attr('data-first') != 0){
            $('.country-block').hide();
            //init city
            if(country != "") {
                $('#complete_register_country').val(country).trigger('change');
            }
        } else { $('.country-block').show(); }

        if ( $('#current_region').data('region') ===1  && $('#state_filter_modal').data('country') === 'Australia') {
            $('.institution-block').find('.institution-id').html("<option value=''></option>");
        }
        //enable the button if we have at least one institution selected
        if($('.multi_institutions:checked').length > 0){
            $('#institution_isPrimary').prop('checked', false);
            $('#institution_isPrimary').attr('disabled', false);
        } else {
            $('#institution_isPrimary').prop('checked', true);
            $('#institution_isPrimary').attr('disabled', true);
        }
    });
    /** Click close institution modal either in overlay or close button */
    $(document).on('click','.close-institution-modal', function (e) {
        let firstStep = $('#step1');
        let instSelector = $('#institutions-selector');
        if($('.institution-modal') .hasClass('active')) {
            if (instSelector.data('inst') === 0 &&  firstStep.data('first-request') === true ) {
                $('#step2' ).removeClass('active');
                firstStep.addClass('active');
                }
            closeInstitutionModal();
        }

    });

    /** Submit new institution form */
    $(document).on('click','#addInstitution',function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        submitInstitutionFromModal(form,true,function (result) {
            if(result.institution.primary === 1) {
                //only one institutions should be primary
                if( $('.multi_institutions').data('primary') == 1)  $('.multi_institutions').data('primary','0');
            }

            if(result.success){
                 var institutionName = result.institution.institution_name+" - "+result.institution.department_name+" - "+result.institution.profession+" - "+result.institution.speciality;
                /** Close modal */
                resetInstitutionModalInput(form);
                $('.institution-modal .close-institution-modal').click();
                $('#institutions-selector').append('<option value="'+result.institution.id+'" selected ' +
                    'data-institution="'+result.institution.institution_name+'" data-institution-id="'+result.institution.institution_id+''+
                    '" data-department="'+result.institution.department_name+'" data-department-id="'+result.institution.department_id+'' +
                    '" data-profession="'+result.institution.profession+'" data-speciality="'+result.institution.speciality+'"' +
                    ' data-primary="'+result.institution.primary+ '" data-country="'+result.institution.country+'">'+institutionName+'</option>').trigger('change');
                $('#institution-check-container').append('<div class="input-field user-term small-12 medium-12 large-12 columns checkbox__item">' +
                    '<input name="institution-check-list" class="checkbox__input multi_institutions" type="checkbox" checked value="'+result.institution.id+'' +
                    '" data-institution="'+result.institution.institution_name+'" id="'+result.institution.id+'" data-institution-id="'+result.institution.institution_id+'' +
                    '" data-department="'+result.institution.department_name+'" data-department-id="'+result.institution.department_id+'' +
                    '" data-profession="'+result.institution.profession+'" data-speciality="'+result.institution.speciality+'"' +
                    ' data-primary="'+result.institution.primary+'" data-name="'+institutionName+'" data-country="'+result.institution.country+'">' +
                    '<label for="'+result.institution.id+'" class="checlbox__label">'+institutionName+'</label></div>').trigger('change');
                    $("#institutions-selector").select2({});
                    country = result.institution.country;
                    if ( result.institution.primary && result.address != undefined){
                        $('#address_address1').data('value',result.address.address1);
                        $('#address_address2').data('value',result.address.address2);
                        $('#address_address3').data('value',result.address.address3);
                        $('#address_address4').data('value',result.address.address4);
                        $('#address_state').data('value',result.address.state);
                        $('#address_city').data('value',result.address.city);
                        $('#address_postalCode').data('value',result.address.postalCode);
                    }
            } else{
                growl(false,result.message)
            }
            form.removeClass('loading');
        })
    });

});
