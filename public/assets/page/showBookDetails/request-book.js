/** Validate book request 3rd step */
function validateStep3() {
    var valid = true;
    var step3 = $('#step3');
    if (!step3.hasClass('hidden')) {
        var fields = step3.find('.validate:visible');
        step3.find('.error').remove();
        $.each(fields, function (i, e) {
            if (!$(e).val()) {
                let title = DOMPurify.sanitize($(e).prop('title'));
                var textAppend = '<span class="error">' + title + '</span>';
                $(e).addClass('warning')
                    .parent().append(textAppend);
                valid = false;
            }
        });
    }
    return valid;
}

/** Validate book request 4th step */
function validateStep4() {
    var valid = true;
    var step4 = $('#step4');
    var fields = step4.find('.validate:visible');
    fields.removeClass('warning');
    step4.find('.error').remove();
    $.each(fields, function (i, e) {
        if (!$(e).val()) {
            let title = DOMPurify.sanitize($(e).prop('title'));
            var textAppend = '<span class="error">' + title + '</span>';
            $(e).addClass('warning')
                .parent().append(textAppend);
            valid = false;
        }
    });
    var numberReg = new RegExp(/^\d+$/),
        nameReg = new RegExp(/[A-z]{3,}/g);

    var course = $('#courseType'),
        studentNumber = $('#studentsNumber');

    /** Validate course name */
    if(course.val() != undefined) {
        if (!(course.val().match(nameReg)) && !(course.hasClass('.warning'))) {
            course.addClass('warning');
            valid = false;
        }
    }

    /** Validate student number */
    if ((!$.isNumeric(studentNumber.val()) || studentNumber.val() <= 0 || studentNumber.val().match(numberReg) == null) && !(studentNumber.val() === '')) {
        let studentNumberMsg = DOMPurify.sanitize(studentNumber.data('msg'));
        studentNumber.addClass('warning')
            .parent().append('<span class="error">' + studentNumberMsg + '</span>');
        valid = false;
    }
    return valid;
}

/** Validate book request 5th step */
function validateStep5() {
    var valid = true;
    var step5 = $('#step5');
    var radioBtns = step5.find('.checkbox__input');
    radioBtns.removeClass('warning');
    if (!step5.hasClass('hidden')) {
        step5.find('.error').remove();
        if ($('#step5 input[type=radio]:checked').length === 0) {
            $.each(radioBtns, function (i, e) {
                $(e).addClass('warning');
                valid = false;
            });
        }
    }
    return valid;
}

/** Reset error & old value for next institution steps */
function resetStepsForNextInstitution() {
    $('#step3 input:not([type="radio"],[type="checkbox"])').val('');
    $('#step4 input:not([type="radio"],[type="checkbox"])').val('');
    $('#courseType').val('').trigger('change');
    $('#courseLevel').val('').trigger('change');
    $('#core,#recommended,#supplementary,#undecided').prop('checked', false).trigger('change');
    $('#endDate').datepicker('setDate', null);
    $('#startDate').datepicker('setDate', null);
    $('#noCurrentTitle').prop('checked', true).trigger('change');
    $('.modal-step .error').remove();
    $('.modal-step .warning').removeClass('warning');
}

/** Send request book data */
function submitBookRequest(bookRequest, callback) {

    /** Fix so can be used with formBuilder bind request */
    var data = {
        'bookRequest[bookIsbn]': bookRequest.bookIsbn,
        'bookRequest[bookFormat]': bookRequest.bookFormat,
        'bookRequest[preOrder]': bookRequest.preOrder,
        'bookRequest[institutions]': bookRequest.institutions,
        'bookRequest[addressId]': bookRequest.addressId,
        'country': bookRequest.country,
    };
    var submitBtn = $('#submit-request-btn');
    submitBtn.addClass('loading');
    var confirmBtn = $('#confirm-btn');
    confirmBtn.addClass('loading');
    $.ajax({
        url: $('#formBookRequest').data('url'),
        method: 'POST',
        data: data,
        success: function (response) {
            submitBtn.removeClass('loading');
            confirmBtn.removeClass('loading');
            $('#step1').data('first-request', false);
            callback(response.success, bookRequest, response);
        },
        error: function (httpObj) {
            submitBtn.removeClass('loading');
            confirmBtn.removeClass('loading');
            if (httpObj.status === 401)
                showErrorBox($('#loggedOutMsg').text());
        }
    })
}


/** Submit book request validate & send data*/
function sendRequestData(bookRequest) {
    if (validateStep3() && validateStep4()) {
        submitBookRequest(bookRequest, function (success, bookRequest, response) {
            if (success) {
                $('#step5').removeClass('active');
                $('#step6').removeClass('active');
                $('#step7').addClass('active');
                let requestBtn = $(".request-book-btn");
                let selectedFormatBtn = $("input[name='radio-group']:checked");
                let digitalButton = selectedFormatBtn.attr('data-format', "Digital");
                let printButton =  $('#print-request');
                /** Change button message and disable it */
                /*var button = $('.request-book-btn[data-format="' + bookRequest.bookFormat + '"]');*/
                selectedFormatBtn.attr('data-format', bookRequest.bookFormat);
                selectedFormatBtn.addClass('radioDisabled');
                selectedFormatBtn.attr('disabled',"true");
                selectedFormatBtn.prop('checked',false);
                /** If preorder book show success message preorder else show message request*/
                if (bookRequest.preOrder === 1)
                    $('#preorder').show();
                else $('#request').show();
                /** If book format is print then disabled digital request btn */
                if (bookRequest.bookFormat === 'Print') {
                    /*$('.request-book-btn[data-format="Digital"]');*/
                    if (digitalButton) {
                        digitalButton.addClass('radioDisabled');
                        digitalButton.attr('disabled',"true");
                        selectedFormatBtn.prop('checked',false);
                        requestBtn.text(selectedFormatBtn.data('requested'));
                        $('.info-print-requested').removeClass('hidden');
                    }
                    requestBtn.removeClass('request-book-btn').addClass('btnDisabled');
                }
                else if (bookRequest.bookFormat === 'Digital' && $("input[name='radio-group']").length===1 )  {
                    requestBtn.removeClass('request-book-btn').addClass('btnDisabled');
                }
                else {
                    printButton.prop('checked',true);
                    printButton.data('request') === 'request' ? requestBtn.text('request print'): requestBtn.text($('#preorder-book').text());
                    if(printButton.data('request') != 'request')
                    {
                        requestBtn.attr('data-preorder', '1');
                        bookRequest.preorder = 1;
                    }
                }
            }
        });

    }
}

/** Create new book request object */
function newBookRequest(isbn, format, preOrder) {
    return {
        bookIsbn: isbn,
        bookFormat: format,
        preOrder: preOrder ? 1 : 0,
        institutions: [],
        addressId: ''
    }
}

/** Reset request book modal */
function resetRequestModal() {
    let firstStep = $('#step1');
    let instSelector = $('#institutions-selector');
    resetStepsForNextInstitution();
    $('.modal-step').removeClass('active');
    $('#courseType').val("").trigger('change');
    $('#courseLevel').val("").trigger('change');
    firstStep.addClass('active');
    $('#singleInstitution').prop('checked', true).trigger('change');
    $('#core,#recommended,#supplementary,#undecided').prop('checked', false).trigger('change');
    $('input:not([type="radio"],[type="checkbox"])').val('');
    $('[name="institution-check-list"]:not(.my-primary)').prop('checked', false).trigger('change');
    $('[name="institution-check-list"].my-primary').prop('checked', true).trigger('change');
    $('#institutions-selector option.my-primary').prop('selected', true).trigger('change');
    $('#checkOneInstitutionMsg').hide();
    $('#addressList').show();
    $('#confirmAddress').hide();
    if (firstStep.data('first-request') === true) {
        $('#institutions-selector option').remove();
        $('#institution-check-container').empty();
    }
    instSelector.attr('data-first', 0);
    if (instSelector.data('inst') === 0 || instSelector.data('first') === 0 && firstStep.data('first-request') === true) {
        $('#institution_isPrimary').prop('checked', true);
        $('#institution_isPrimary').attr('disabled', true);
    }
    instSelector.data()['inst'] = 0;
}

$(document).ready(function () {
    if($('#print-request').parent().attr('data-show') == 'false') {
        $('#print-request').parent().remove();
    }
    /** default check for print btn **/
    if($('.radio-request-btn').length === 1 && $('#print-request').length === 1) {
        //we have only one btn and it is a print btn
        if($('#print-request').hasClass('radioDisabled')) {
            $('.request-book-btn').addClass('btnDisabled');
        } else {
            $('#print-request').prop('checked', true);
        }
    }
    var today = new Date();
    var day = today.getDate();
    var monthIndex = today.getMonth();
    var year = today.getFullYear();
    var endDate = day + '-' + monthIndex + '-' + (year + 5);
    var startDate = day + '-' + monthIndex + '-' + (year - 1);
    var startDateInput = $('#startDate');
    var endDateInput = $('#endDate');
    var courseType = $('#courseType');
    var courseLevel = $('#courseLevel');
    var box = $('.box');

    box.find('#box-confirm').text($('#box-msg-yes').text());
    box.find('#box-cancel').text($('#box-msg-no').text());

    startDateInput.datepicker({
        autoclose: true,
        container: startDateInput.closest('.modal-step'),
        minDate: new Date(),
        maxDate: new Date(),
        startDate: startDate,
        endDate: endDate,
        orientation: 'top',
        format: 'dd-M-yyyy',
        language: currentLang,
        todayHighlight: true,
        templates: {
            leftArrow: '<i class="fa fa-chevron-left"></i>',
            rightArrow: '<i class="fa fa-chevron-right"></i>'
        },
        widgetPositioning: {
            horizontal: "auto",
            vertical: "auto"
        }
    }).on('changeDate', function (selected) {
        if (selected.date) {
            var minDate = new Date(selected.date.valueOf());
            minDate.setDate(minDate.getDate() + 1);
            endDateInput.datepicker('setStartDate', minDate);
        } else
            endDateInput.datepicker('setStartDate', startDate);

    });
    endDateInput.datepicker({
        autoclose: true,
        container: endDateInput.closest('.modal-step'),
        minDate: new Date(),
        maxDate: new Date(),
        startDate: startDate,
        endDate: endDate,
        orientation: 'top',
        format: 'dd-M-yyyy',
        language: currentLang,
        todayHighlight: true,
        clearBtn: true,
        templates: {
            leftArrow: '<i class="fa fa-chevron-left"></i>',
            rightArrow: '<i class="fa fa-chevron-right"></i>'
        },
        widgetPositioning: {
            horizontal: "auto",
            vertical: "auto"
        }
    }).on('changeDate', function (selected) {
        if (selected.date) {
            var maxDate = new Date(selected.date.valueOf());
            maxDate.setDate(maxDate.getDate() - 1);
            startDateInput.datepicker('setEndDate', maxDate);
        } else
            startDateInput.datepicker('setEndDate', endDate);
    });
    var bookRequest = null;
    var currentInstitutionRequest = null;
    var institutionSelector = $('#institutions-selector');
    /** Init institutions selector with select 2 */
    institutionSelector.select2({
        placeholder: "",
        dropdownParent: institutionSelector.parent()
    });
    /** Init courses selector with select 2 */
    courseType.select2({
        placeholder: "",
        dropdownParent: courseType.parent()
    });

    /** Init level selector with select 2 */
    var levelPlaceholder = $('#courseLevel').attr('placeholder');
    courseLevel.select2({
        placeholder: levelPlaceholder,
        dropdownParent: courseLevel.parent()
    });
    /** Hide warning radio btn step 5 */
    $(document).on('click', '#core,#recommended,#supplementary,#undecided', function () {
        $('#core,#recommended,#supplementary,#undecided').removeClass('warning');
    });

    /** Show institution multiple or single select */
    $(document).on('change', '[name="multipleInstitution"]', function () {

        var multipleInstCheck = $('#multiple-institutions');
        var singleInstitution = $('#single-institution');
        if ($('#multipleInstitution').is(':checked')) {
            multipleInstCheck.show();
            singleInstitution.hide();
        } else {
            singleInstitution.show();
            multipleInstCheck.hide();
        }
    });

    /** Click other course level to show text input */
    $(document).on('click','.new-level',function () {
        $('.level-selector-block').hide();
        $('.level-text-block').show().find('input').val("");
    });

    /** Click cancel new level to show dropdown */
    $(document).on('click','.cancel-level',function () {
        $('.level-selector-block').show().find('select').val("").trigger('change');
        $('.level-text-block').hide();
    });

    $(document).on('click', '.complete-url', function (e) {
        e.stopPropagation();
        e.preventDefault();
        box.attr('class', 'box active error-info');
        box.find('.error-msg').html(DOMPurify.sanitize($('#complete-url-msg').html()));
        $('body').addClass('modal-open');
        box.on('click', '#box-ok', function () {
            box.removeClass('active error-info');
            box.off('click');
            $('body').removeClass('modal-open');
        });
    });

    /** Click request book to open request book modal */
    $(document).on('click', '.request-book-btn:not(.complete-url)', function () {
        resetRequestModal();
        let selectedBtn = $("input[name='radio-group']:checked");
        let isbn = selectedBtn.data('isbn');
        let format = selectedBtn.data('format');
        let preOrder = selectedBtn.data('preorder') !== undefined;
        bookRequest = newBookRequest(isbn, format, preOrder);

        /** Show request modal */
        $('.request-book-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('.request-book-modal .close').show();
    });

    /** Go next request step */
    $(document).on('click', '.goStep:not(.loading)', function () {
        var nextStep = $(this).data('target-step');
        var goNextStep = true;
        var selectAddress = $('.select-address');
        var endDate = $('#endDate');
        let firstStep = $('#step1');
        let singleInstitution = $('#singleInstitution');

        /** Save selected institution(s) */
        switch (nextStep) {
            case "step2" :
                if (($('#institutions-selector').data('inst') === 0 && firstStep.data('first-request') === true) || ($('#institutions-selector option').length === 0)) {
                    $('#new-institution-btn').click();
                }
                break;
            case "step3" :
                if (singleInstitution.is(':checked')) {
                    $('.current-inst-name').hide();
                    let selectedInstitution = $('#institutions-selector option:selected');
                    if (firstStep.data('first-request') === false) {
                        currentInstitutionRequest = {
                            institutionName: selectedInstitution.text(),
                            institutionId: selectedInstitution.attr('value'),
                            done: false
                        };
                    } else {
                        currentInstitutionRequest = {
                            institutionName: selectedInstitution.text(),
                            institutionId: (selectedInstitution.attr('value') !== '') ?selectedInstitution.attr('value') : selectedInstitution.data('institution-id'),
                            institution: selectedInstitution.data('institution'),
                            institutionInstId: selectedInstitution.data('institution-id'),
                            department: selectedInstitution.data('department'),
                            departmentId: selectedInstitution.data('department-id'),
                            profession: selectedInstitution.data('profession'),
                            speciality: selectedInstitution.data('speciality'),
                            primary: selectedInstitution.data('primary'),
                            done: false
                        };
                        if(selectedInstitution.attr('value') == '') {
                            currentInstitutionRequest['institutionId'] = selectedInstitution.data('institution-id');
                        }
                        bookRequest['country'] = selectedInstitution.data('country');
                    }
                    bookRequest.institutions.push(currentInstitutionRequest);
                } else {
                    $.each($('#multiple-institutions input:checked'), function (i, elem) {
                        if (i === 0) {
                            if (firstStep.data('first-request') === false) {
                                currentInstitutionRequest = {
                                    institutionName: $(elem).data('name'),
                                    institutionId: $(elem).attr('value'),
                                    done: false
                                };
                            } else {
                                currentInstitutionRequest = {
                                    institutionName: $(elem).data('name'),
                                    institutionId:($(elem).attr('value') !== '') ? $(elem).attr('value') : $(elem).data('institution-id'),
                                    institution: $(elem).data('institution'),
                                    institutionInstId: $(elem).data('institution-id'),
                                    department: $(elem).data('department'),
                                    departmentId: $(elem).data('department-id'),
                                    profession: $(elem).data('profession'),
                                    speciality: $(elem).data('speciality'),
                                    primary: $(elem).data('primary'),
                                    done: false
                                };
                                bookRequest['country'] = $(elem).data('country');
                            }
                            bookRequest.institutions.push(currentInstitutionRequest);
                        } else {
                            if (firstStep.data('first-request') === false) {
                                bookRequest.institutions.push({
                                    institutionName: $(elem).data('name'),
                                    institutionId: $(elem).attr('value'),
                                    done: false
                                });
                            } else {
                                bookRequest.institutions.push({
                                    institutionName: $(elem).data('name'),
                                    institutionId: ($(elem).attr('value') !== '') ? $(elem).attr('value') : $(elem).data('institution-id') ,
                                    institution: $(elem).data('institution'),
                                    institutionInstId: $(elem).data('institution-id'),
                                    department: $(elem).data('department'),
                                    departmentId: $(elem).data('department-id'),
                                    profession: $(elem).data('profession'),
                                    speciality: $(elem).data('speciality'),
                                    primary: $(elem).data('primary'),
                                    done: false,
                                    course:(courseType.val() !== undefined) ? courseType.val() : false
                                });
                                bookRequest['country'] = $(elem).data('country');
                            }
                        }
                    });
                    /** If no institution is checked then show an error message */
                    if (bookRequest.institutions.length === 0) {
                        goNextStep = false;
                        $('#checkOneInstitutionMsg').show();
                    }
                    $('.current-inst-name').text(currentInstitutionRequest.institutionName).show();
                }
                break;
            case "step4":
                goNextStep = validateStep3();
                if (goNextStep) {
                    currentInstitutionRequest['bookUsedReason'] = $('[name="bookUsedReason"]:checked').val();
                    currentInstitutionRequest['currentUsedBook'] = $('#currentUsedBook').val();
                }
                break;
            case "step5":
                goNextStep = validateStep4();
                if (goNextStep) {
                    currentInstitutionRequest['course'] = (courseType.val() !== undefined) ? courseType.val() : false;
                    currentInstitutionRequest['courseName']     =   $('#courseName').val();
                    currentInstitutionRequest['courseCode']     =   $('#courseCode').val();
                    currentInstitutionRequest['courseLevel']    =   (courseLevel.val()!=="") ? courseLevel.val() : $('#courseLevelInput').val();
                    currentInstitutionRequest['studentsNumber'] =   $('#studentsNumber').val();
                    currentInstitutionRequest['startDate']      =   moment($('#startDate').val(), 'DD-MMM-YYYY').format('DD-MM-YYYY');
                    if (endDate.val()) {
                        currentInstitutionRequest['endDate']    =   moment(endDate.val(), 'DD-MMM-YYYY').format('DD-MM-YYYY');
                    } else {
                        currentInstitutionRequest['endDate'] =   endDate.val()
                    }
                }
                break;
            case "step6" :
                goNextStep = validateStep5();
                if (goNextStep) {
                    var checkedRecLevel = $('.modal-step-content').find($('[name="recLevel"]:checked'));
                    if ( checkedRecLevel.length > 0 && checkedRecLevel.val() != null) {
                        currentInstitutionRequest['recLevel'] = checkedRecLevel.val();
                    }
                    currentInstitutionRequest.done = true;
                    currentInstitutionRequest = null;
                    for (var i = 0; i < bookRequest.institutions.length; i++) {
                        if (bookRequest.institutions[i].done === false) {
                            currentInstitutionRequest = bookRequest.institutions[i];
                            break;
                        }
                    }
                    if (currentInstitutionRequest) {
                        resetStepsForNextInstitution();
                        $('.current-inst-name').text(currentInstitutionRequest.institutionName);
                        $(this).closest('.modal-step').removeClass('active');
                        $('#step3').addClass('active');
                        goNextStep = false;
                    }
                    else {
                        if (bookRequest.bookFormat === 'Print') {
                            $('#step5').removeClass('active');
                            $('#step6').addClass('active');
                            if (selectAddress.length === 0) {
                                $('.new-address-modal').addClass('no-address');
                                $('.btn-new-address').click();
                                /** Set institution name and department */
                                for (var i = 0; i < bookRequest.institutions.length; i++) {
                                    if (bookRequest.institutions[i].primary === 1) {
                                        $('#address_address2').val(bookRequest.institutions[i].institution);
                                        $('#address_address3').val(bookRequest.institutions[i].department);
                                    }
                                }
                                /** Set user country */
                                if (bookRequest.country !== '' && bookRequest.country!==undefined ) {
                                    $('#address_country').val(bookRequest.country);
                                    $('#address_country').trigger('change');
                                } else {
                                    var form = $('#add-address-form');
                                    var countryContent = form.find('#select2-address_country-container');
                                    var countrySelector = form.find('#address_country');
                                    countrySelector.val(countrySelector.data('value'));
                                    countryContent.text(countrySelector.data('value'));
                                }

                            } else if (selectAddress.length === 1)
                                selectAddress.first().click();

                        } else {
                            if($('.request-book-btn').attr('data-preorder') != null && $('.request-book-btn').attr('data-preorder') != undefined) {
                                bookRequest.preOrder = $('.request-book-btn').attr('data-preorder')
                            }
                            sendRequestData(bookRequest);
                            goNextStep = false;
                        }

                    }
                }
                break;
            case "step7":
                goNextStep = false;
                bookRequest.addressId = $('.address-info.active').data('address-id');
                if($('.request-book-btn').attr('data-preorder') != null && $('.request-book-btn').attr('data-preorder') != undefined) {
                    bookRequest.preOrder = $('.request-book-btn').attr('data-preorder')
                }
                sendRequestData(bookRequest);
                break;
        }
        if (goNextStep) {
            $(this).closest('.modal-step').removeClass('active');
            $('#' + nextStep).addClass('active');
        }
    });

    /** Hide/Show other book title input */
    $(document).on('change', '[name="bookUsedReason"]', function () {
        var usedBookBlock = $('#usedBookBlock');
        if ($('#alternative').is(':checked'))
            usedBookBlock.show();
        else
            usedBookBlock.hide();
    });

    /** Prevent normal form submit */
    $(document).on('submit', '.fakeForm', function (e) {
        e.preventDefault();
    });

    $(document).ready(function () {
        let digitalRequest = $("#digital-request");
        let printRequest = $("#print-request");
        let requestBtn = $(".request");
        let formatBtn = $("input[name='radio-group']");

        if (formatBtn.length === 0) {
            requestBtn.hide();
        }

        if ($('.radioDisabled').length > 1 || (printRequest.length === 0 && digitalRequest.hasClass('radioDisabled')) )
        {
            requestBtn.addClass('disabled');
            requestBtn.removeClass('request-book-btn').addClass('btnDisabled');
            digitalRequest.prop('checked',"false");
        }
        else {
            if (printRequest.is(':checked') || (digitalRequest.hasClass('radioDisabled') && !(printRequest.hasClass('radioDisabled') ))) {
                printRequest.prop("checked", "true");
                if (printRequest.data('request') === 'request')
                    requestBtn.text($('#request-book').text());
                else if($('#ukpubstatus').text() === 'RPT - Reprinting'){
                    requestBtn.text($('#back-stock-book').text());
                } 
                else{
                    requestBtn.text($('#preorder-book').text());
                    requestBtn.attr('data-preorder', '1');
                    bookRequest.preorder = 1;
                } 
            }
            else if (digitalRequest.is(':checked') ) {
                if (digitalRequest.data('request') === 'request')
                    requestBtn.text($('#request-book').text());
                else if($('#ukpubstatus').text() === 'RPT - Reprinting'){
                    requestBtn.text($('#back-stock-book').text());
                }
                else{
                    requestBtn.text($('#preorder-book').text());
                    requestBtn.attr('data-preorder', '1');                    
                } 
            }

            printRequest.click(function () {
                $("#digital-request").prop("checked", false);
                printRequest.prop("checked", "true");
                if (printRequest.data('request') === 'request')
                    requestBtn.text($('#request-book').text());
                else if($('#ukpubstatus').text() === 'RPT - Reprinting'){
                    requestBtn.text($('#back-stock-book').text());
                }
                else{
                    requestBtn.text($('#preorder-book').text());
                    requestBtn.attr('data-preorder', '1');
                    bookRequest.preorder = 1;
                } 
            });

            digitalRequest.click(function () {
                printRequest.prop("checked", "false");
                digitalRequest.prop("checked", "true");
                if (digitalRequest.data('request') === 'request')
                    requestBtn.text($('#request-book').text());
                else if($('#ukpubstatus').text() === 'RPT - Reprinting'){
                    requestBtn.text($('#back-stock-book').text());
                }
                else{
                    requestBtn.text($('#preorder-book').text());
                    requestBtn.attr('data-preorder', '1');
                    bookRequest.preorder = 1;
                } 
            });
        }
    });

    /** Click close book request */
    $(document).on('click', '#close-modal', function (e) {
        var requestModal = $('.request-book-modal');
        var actualStepId = requestModal.find('.modal-step.active').attr('id');
        if (actualStepId === 'step7') {
            closeModal(requestModal);
        }
        else {
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text($('#cancel-request-msg').text());
            $('body').addClass('box-above-modal');
            box.on('click', '#box-cancel', function () {
                box.removeClass('active confirm-info');
                box.off('click');
                $('body').removeClass('box-above-modal');
            }).on('click', '#box-confirm', function () {
                box.removeClass('active confirm-info');
                closeModal(requestModal);
                box.off('click');
                $('body').removeClass('box-above-modal');
                resetRequestModal();
            });
        }
    });

    /** Set Continue button at the bottom of the modal for mobile */
    if (window.innerWidth < 640)
        $('.modal-step-content').css('minHeight', $('.request-book-modal').height() - 86);

    /** Click select an address for print request */
    $(document).on('click', '.select-address', function (e) {
        e.preventDefault();
        $('.address-info.active').removeClass('active');
        var selectedAddressId = $(this).data('address-id');
        var selectedAddressInfo = $('.address-info[data-address-id="' + selectedAddressId + '"]');
        $('#addressList').hide();
        $('#confirmAddress').show();
        selectedAddressInfo.addClass('active');
        selectedAddressInfo.show();
    });

    /** Click select another address to return to address list */
    $(document).on('click', '.select-other-address', function (e) {
        e.preventDefault();
        $('#addressList').show();
        $('#confirmAddress').hide();
        $('.address-info').hide();
    });


    /** Prevent showing the previous steps when clicking enter in the steps different input fields */
    $(document).on('keypress', '.validate', function (e) {
        /** If the keypress is Enter then prevent the action */
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
});

