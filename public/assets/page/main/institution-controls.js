
function submitInstitutionFromModal(form,newInstitution,callback) {
    var valid = true;
    var fields = form.find('.institution-name:visible,.institution-id:visible,.department-name:visible,' +
        '.department-id:visible,.profession:visible,.speciality:visible');
    var instSelector =  $('#institutions-selector');
    var zipFilter= form.find('.zip-filter');
    fields.removeClass('warning');
    form.find('.error').remove();
    if (zipFilter.length>0 && zipFilter.hasClass('warning')) zipFilter.parent().append('<span class="error">' + zipFilter.data('wrong') + '</span>');
    if(zipFilter.length>0 && form.find('.zip-filter-block').hasClass('hidden') === false)
        fields.push(form.find('.zip-filter ').first());
    if(form.find('.state-filter ').length>0 && form.find('.state-filter-block').hasClass('hidden') === false)
        fields.push(form.find('.state-filter ').first());
    $.each(fields,function (i,e) {
        if(!$(e).val()){
            $(e).parent().find('.error').remove();
            let title = DOMPurify.sanitize($(e).prop('title'));
            $(e).addClass('warning').parent().append('<span class="error">' + title+ '</span>');
            valid=false;
        }
    });
    zipFilter.length>0 && zipFilter.parent().children().hasClass('error')? valid=false : null;
    var instId          = $('#institution_id');
    var institutionId   = $('#institution_institutionId');
    var institutionName = $('#institution_institutionName');
    var departmentId    = $('#institution_departmentId');
    var departmentName  = $('#institution_departmentName');
    var profession      = $('#institution_profession');
    var speciality      = $('#institution_speciality');
    var isPrimary       = $('#institution_isPrimary');
    var country         = $('#complete_register_country');

    var data = [
        {name:'institution_institution_id',     value:institutionId.val()?institutionId.val():''},
        {name:'institution_institution_name',   value:institutionName.val()},
        {name:'institution_department_id',      value:departmentId.val()?departmentId.val():''},
        {name:'institution_department_name',    value:departmentName.val()},
        {name:'institution_profession',         value:profession.val()},
        {name:'institution_speciality',         value:speciality.val()},
        {name:'institution_primary',            value:isPrimary.is(':checked')?1:0},
        {name:'institution_id',                 value:instId.val()?instId.val():''}
    ];
    if($('#nb-institution').length >0 ) {
        if($('#nb-institution').data('nb-institution') == 0 && country.val() != '') {
            data.push({name:'country',     value: country.val()})
        }
    }

    if(form.find('.institution-name').is(':visible'))
        data[0]['value']="";
    if(form.find('.department-name').is(':visible'))
        data[2]['value']="";
    if(valid){
        if ($('#step1').data('first-request') === false || $('.my-institution-page').length>0) {
            $.ajax({
                url:form.attr('action'),
                method:newInstitution?'POST':'PUT',
                dataType:'JSON',
                data:data,
                beforeSend:function(){
                    form.addClass('loading');
                },
                success: function (result) {
                    if(instSelector.data('inst'))
                        instSelector.data()['inst'] +=1;
                    callback(result);
                },
                error:function (httpObj) {
                    form.removeClass('loading');
                    if(httpObj.status===401)
                        showErrorBox($('#loggedOutMsg').text());
                    else
                        showErrorBox($('#error').text());
                }
            });
        } else {
            let instUniqueId = uniqueID();
            let dataInst =
                { success: true,
                    institution:
                        {
                            id: institutionId.val() ? institutionId.val() : '',
                            institution_name: institutionName.val(),
                            department_id: departmentId.val() ? departmentId.val() : '',
                            department_name: departmentName.val(),
                            profession: profession.val(),
                            speciality: speciality.val(),
                            primary: isPrimary.is(':checked') ? 1 : 0,
                            institution_id: instUniqueId
                        }
                };
            if(instSelector.data('first') === 0){
                dataInst.institution['country'] = country.val();
            }
            instSelector.data()['inst'] +=1;
            instSelector.attr('data-first', 1);
            form.removeClass('loading');
            closeInstitutionModal();
            callback(dataInst);
        }
    }
}

/** Init institution block dropdown with select2 plugin */
function initAllSelectorWithSelect2(block,country) {
    var institution = block.find('.institution-id');
    var department = block.find('.department-id');
    var profession = block.find('.profession');

    var speciality = block.find('.speciality');
    var cityFilterBlock = block.find('.city-filter');
    var stateFilterBlock = block.find('.state-filter');
    var zipFilterBlock = block.find('.zip-filter');
    var currentCountry = $('#current_country').data('country').toLowerCase();
    var placeholderText='';
    $.each(institutionsData,function (i,elem) {
        var institutionOption = new Option();
        institutionOption.text =  DOMPurify.sanitize(elem.text);
        institutionOption.value = elem.value;
        institutionOption.setAttribute('data-type',elem.getAttribute('data-type'));
        institution.append(institutionOption);
    });
    if(stateFilterBlock.length>0 && (currentCountry=='united states' || currentCountry=='us' )){
        placeholderText= 'Select your institution';
    }
    if (!$('#institutions-selector').data('first'))
    {
        let firstCountry = $('.country-block').find('#complete_register_country');
        firstCountry.select2({
            placeholder: "",
            dropdownParent: firstCountry.parent(),
        }).on('change',function () {
            if ( $(this).val()){
                if ( ($('#current_region').data('region') ===1  && $('#current_country').data('country') === 'australia')
                || ($('#current_region').data('region') ===9  && (currentCountry=='united states' || currentCountry=='us') )) {
                    loadStates($(this).val(),stateFilterBlock);
                } else
                loadInstitutions($(this).val(),'','','', block);
                if(block.data('lang') === 'in' ){
                    loadCities($(this).val(),cityFilterBlock)
                }
            }
        });
    }
    institution.select2({
        placeholder: placeholderText,
        dropdownParent: institution.parent(),
    }).on('change',function (e) {
        var institutionName = block.find('.institution-name');
        var selectedOption = $(this).find('option:selected');
        institutionName.val(selectedOption.text());
        if($(this).val()){
            if(block.data('lang')==='de' && selectedOption.data('type').toLowerCase()!=='university'){
                block.find('.department-text-block,.department-selector-block').addClass('f-hidden');
            }else{
                block.find('.department-text-block,.department-selector-block').removeClass('f-hidden');
                loadDepartments($(this).val(),$(this).closest('.institution-block'));
            }
        }
    });
    department.select2({
        placeholder: "",
        dropdownParent: department.parent()
    }).on('select2:opening', function (e) {
        /** Init all institution block with select2 */
        if (department.data('loaded')!==1 && !department.hasClass('s2-loading-data'))
            loadDepartments(institution.val(), block, true);

        if (department.hasClass('s2-loading-data'))
            e.preventDefault();

    }).on('change',function () {
        var departmentName = block.find('.department-name');
        departmentName.val($(this).find('option:selected').text());
    });
    speciality.select2({
        placeholder: "",
        dropdownParent: speciality.parent()
    }).on('select2:opening', function (e) {
        if (speciality.data('loaded')!==1 && !speciality.hasClass('s2-loading-data') && $(this).val())
            loadSpecialities(profession.data('value'), speciality.closest('.institution-block'), true);
        if (speciality.hasClass('s2-loading-data'))
            e.preventDefault();
    });
    profession.select2({
        placeholder: "",
        dropdownParent: profession.parent()
    }).on('change',function () {
        if($(this).val()){
            loadSpecialities($(this).val(),block);
            if(block.data('lang') === 'in' && institution.val()){
                loadDepartments(institution.val(),block)
            }
        }
    });
    if(cityFilterBlock.length>0){
        cityFilterBlock.select2({
            placeholder: "",
            dropdownParent: cityFilterBlock.parent()
        }).on('change',function () {
            if ($('#step1').data('first-request') === true)
            { loadInstitutions($('#complete_register_country').val(),$(this).val(),'','', block); } else {
                loadInstitutions($(this).data('country'),$(this).val(),'','', block);
            }
        });
        if(country){
            cityFilterBlock.data('country',country);
            zipFilterBlock.data('country',country);
            loadCities(country,cityFilterBlock)
        }else if (cityFilterBlock.data('country')){
            loadCities(cityFilterBlock.data('country'),cityFilterBlock)
        }
    }
    if(country){
        zipFilterBlock.data('country',country);
    }
    if(zipFilterBlock.length>0){
        $(zipFilterBlock).on('change',function (e) {
            var selectedCountry = $(this).data('country');
            if(selectedCountry == "") {
                selectedCountry = $('#complete_register_country').val();
            }
            loadInstitutions(selectedCountry,'',$(this).val(),'',block);
        });
    }

    if(stateFilterBlock.length>0){
        stateFilterBlock.select2({
            placeholder: "",
            dropdownParent: stateFilterBlock.parent()
        }).on('change',function () {
            loadInstitutions($(this).data('country'),'','',$(this).val(), block) ;
        });
        if(country){
            stateFilterBlock.data('country',country);
            loadStates(country,stateFilterBlock);
        }else if (stateFilterBlock.data('country')){
            loadStates(stateFilterBlock.data('country'),stateFilterBlock);
        }
    }
}

function resetInstitutionModalInput(form) {
    $.each(form.find('input[type="text"],select'),function (i,elem) {
        $(elem).val("").trigger('change.select2').trigger('change');
    });
    $(form).find('select').data('loaded',0).data('value','');
    $(form).find('input').data('value','');
    $('.institution-modal .institution-id').select2('data',institutionsData);
    var btnCancelInst = $('.btn-cancel-new-institution');
    var btnCancelDep = $('.btn-cancel-new-department');
    if($('.institution-id option').length>1){
        btnCancelInst.click().hide();
        btnCancelDep.click().show();
    }
    if ($('#institutions-selector').attr('data-first') != 0) {
        $('#institution_isPrimary').prop('checked',false);
        $('#institution_isPrimary').attr('disabled',false);
    }
}

/** Close add institution  modal **/
function closeInstitutionModal() {
    /** remove all validation errors */
    $('.institution-modal .error').remove();
    $('.institution-modal .warning').removeClass('warning');
    if($('.request-book-modal.active').length===0){
        modalFix(function () {
            $('body,html').removeClass('modal-open scrollable');
        });
    }
    $('.institution-modal.active').fadeOut(200, function () {
        $('.institution-modal').removeClass('active');
    });
    $('.institution-modal').removeClass('edit-mode').find('[data-value]').data('value','');
    $('.institution-modal .fieldset').removeClass('readOnly');
    $('#updateInstitution').attr('disabled',true).removeClass('hidden');
    $('#disableInstitution').addClass('hidden');
    $('#deleteInstitution').removeClass('hidden');
    $('#primary-block').show();
    resetInstitutionModalInput($('.institution-modal form'));
}
$(document).ready(function () {
    $('#complete_register_country').on('change', function () {
        if(['deutschland', 'österreich', 'schweiz'].includes($(this).val().toLowerCase()) > 0){
            $('.zip-filter-block').removeClass('hidden');
        } else {
            $('.zip-filter-block').addClass('hidden');
        }
    });
    initAllSelectorWithSelect2($('.institution-block'));
    /** Click other institution to show text input */
    $(document).on('click','.btn-new-institution',function () {
        var currentInstitutionBlock = $(this).closest('.institution-block');
        currentInstitutionBlock.find('.institution-selector-block').hide();
        currentInstitutionBlock.find('.institution-text-block').show().find('input').val("");
        currentInstitutionBlock.find('.btn-cancel-new-department').hide();
        currentInstitutionBlock.find('.btn-new-department').click();
        $('.department-text-block,.department-selector-block').removeClass('f-hidden');
        currentInstitutionBlock.find('.btn-cancel-new-institution').parent().show();
        currentInstitutionBlock.find('.btn-cancel-new-institution').parent().removeClass('hidden');
        currentInstitutionBlock.find('.btn-cancel-new-institution').show();
    });

    /** Click cancel new institution to show dropdown */
    $(document).on('click','.btn-cancel-new-institution',function () {
        var currentInstitutionBlock = $(this).closest('.institution-block');
        currentInstitutionBlock.find('.institution-selector-block').show().find('select').val("").trigger('change');
        currentInstitutionBlock.find('.institution-text-block').hide();
        currentInstitutionBlock.find('.btn-cancel-new-department').click();
        currentInstitutionBlock.find('.btn-new-department').show();
        currentInstitutionBlock.find('.btn-cancel-new-department').show();
        currentInstitutionBlock.find('.btn-cancel-new-department').parent().show();
    });

    /** Click other department to show text input */
    $(document).on('click','.btn-new-department',function () {
        var currentInstitutionBlock = $(this).closest('.institution-block');
        currentInstitutionBlock.find('.department-selector-block').hide();
        currentInstitutionBlock.find('.department-text-block').show().find('input').val("");
    });

    /** Click cancel new department to show dropdown */
    $(document).on('click','.btn-cancel-new-department',function () {
        var currentInstitutionBlock = $(this).closest('.institution-block');
        currentInstitutionBlock.find('.department-selector-block').show().find('select').val("").trigger('change');
        currentInstitutionBlock.find('.department-text-block').hide();
    });
});
