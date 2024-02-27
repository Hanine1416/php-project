$(document).ready(function () {
    /** Open modal address form */
    $(document).ready(function () {
        /** Click add new address to open modal */
        $(document).on('click', '#new-address-btn', function (e) {
            e.preventDefault();
            modalFix(function () {
                $('body,html').addClass('modal-open scrollable');
            });
            $('.new-address-modal').addClass('active').fadeIn(200, function () {
                $('.new-address-modal .close').show();
                var scrollModal = $('.new-address-modal').offset();
                var scrollTop = scrollModal.top - 60;
                $('body,html').animate({
                    scrollTop: scrollTop
                }, 800);
            });
        });
    });

    /** Close address modal */
    $(document).on('click', '#close', function () {
        $('.new-address-modal .close').click();

    });

    /** Click tile to open/close */
    $(document).on('click', 'button.tile-toggle', function (e) {
        var elem = $(this);
        var enabledBtnUpdate = $('.update-address-btn:not(:disabled)').first();
        var tile = elem.closest('.tile-block');

        /** If the user didn't make any changes then close or open the tile */
        if (enabledBtnUpdate.length === 0) {
            var openTile = !$(this).closest('.tile-block').hasClass('active');
            $('.validate').removeClass('warning');
            tile.find('.error').remove();
            $('.tile-block.active').removeClass('active');
            if (openTile) {
                tile.addClass('active');
            }
        } else
            showSaveUpdateBox(enabledBtnUpdate.closest('.tile-block.active'), function () {
                elem.click();
            });
    });

    /** Init all country selector with select2 plugin */
    $.each($('.country-input'), function (i, elem) {
        $(elem).select2({
            placeholder: '',
            dropdownParent: $(elem).parent()
        })
    });

    /** Check if address block has been updated */
    function addressBlockChanged(block) {
        var changed = false;
        $.each(block.find('select[data-value],input[data-value]'), function (i, e) {
            if (($(e).val() != $(e).data('value') && !($(e).val() === null && $(e).data('value') == "")) && $(e).attr('type') !== "hidden") {
                changed = true;
                return false
            }
        });
        return changed;
    }

    /** Check when edit address form changed then enable update button */
    $(document).on('change keyup', 'select,input', function () {
        var block = $(this).closest('.address-block');
        block.find('.update-address-btn').attr('disabled', !addressBlockChanged(block))
    });

    /** Check if all address block input are valid */
    function checkAddressBlockValid(block) {
        var fields = block.find('.validate');
        fields.removeClass('warning');
        block.find('.error').remove();
        var valid = true;
        /** validate all fields */
        $.each(fields, function (key, element) {
            if (($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '') && $(element).is(':visible') && !$(element).hasClass('empty') && !$(element).hasClass('warning')) {
                let title = DOMPurify.sanitize($(element).prop('title'));
                $(element)
                    .addClass('warning')
                    .parent().append('<span class="error" style="position: relative;">' + title + '</span>');
                valid = false;
            }
        });
        return valid;
    }

    /** Get address block data from inputs */
    function getAddressBlockData(block) {
        return {
            'address_id': block.find('.address-id').val(),
            'address1': block.find('.address1-input').val(),
            'address2': block.find('.address2-input').val(),
            'address3': block.find('.address3-input').val(),
            'address4': block.find('.address4-input').val(),
            'city': block.find('.city-input').val(),
            'state': block.find('.state-input').val(),
            'postal_code': block.find('.postalCode-input').val(),
            'country': block.find('.country-input').val()
        };
    }

    /** Update existing address */
    $(document).on('click', '.update-address-btn:not(.loading)', function (e) {
        var elem = $(this);
        var block = elem.closest('.address-block');
        if (checkAddressBlockValid(block)) {
            var addressData = getAddressBlockData(block);
            $.ajax({
                url: baseUrl + '/profile/delivery-address/update',
                method: 'PUT',
                data: addressData,
                dataType: 'JSON',
                beforeSend: function () {
                    elem.addClass('loading');
                    elem.closest('.fieldset').addClass('readOnly');
                },
                success: function (response) {
                    if (response.success) {
                        block.find('.address1-input').data('value', addressData['address1']);
                        block.find('.address2-input').data('value', addressData['address2']);
                        block.find('.address3-input').data('value', addressData['address3']);
                        block.find('.address4-input').data('value', addressData['address4']);
                        block.find('.city-input').data('value', addressData['city']);
                        block.find('.state-input').data('value', addressData['state']);
                        block.find('.postalCode-input').data('value', addressData['postal_code']);
                        block.find('.country-input').data('value', addressData['country']);
                        /** If there was only one address then remove it's unique class */
                        var uniqueAddress = $('.unique-address');
                        if (uniqueAddress.length === 1) {
                            uniqueAddress.removeClass('unique-address');
                            uniqueAddress.find('.address-del-btn').addClass('hidden');
                        }
                        elem.attr('disabled', true);
                    } else
                        showErrorBox(response.message);
                    elem.closest('.fieldset').removeClass('readOnly');
                    elem.removeClass('loading');
                }
            });
        }
    });
});

/** Click delete address */
$(document).on('click', '.delete-address-btn', function () {
    var block = $(this).closest('.address-info');
    var addressId = $(this).data('address-id');
    /** Here show the confirm box */
    var box = $('.box');
    /** Show warning popup message if address in BR */
    if ($('.address-info.br-address').length === 1) {
        box.find('.error-msg').text($('#delete-last-address-msg').text());
        box.attr('class', 'box active error-info');
        box.on('click', '#box-ok', function () {
            box.removeClass('active error-info');
            box.off('click');
        });
    }
    /** Show confirm popup if address is address in ES/IN/EN */
    else {
        if ($('.address-info').length === 1) {
            box.find('.confirm-msg').text($('#delete-last-address-msg').text());
        }
        else
            box.find('.confirm-msg').text($('#delete-last-address-msg').text());
        box.attr('class', 'box active confirm-info');
        /** Delete address when click confirm */
        box.on('click', '#box-confirm', function () {
            var btn = $(this);
            var myAddresses = $('#my-addresses');
            var addressBlock = $('.address-info');
            btn.prev().hide();
            $(this).addClass('loading');
            $.ajax({
                url: baseUrl + '/profile/delete-address',
                method: 'delete',
                data: {address: addressId},
                dataType: 'JSON',
                success: function (response) {
                    if (response.success) {
                        if (addressBlock.length === 1) {
                            window.location = myAddresses.data('url');
                        }
                        else {
                            block.remove();
                            box.removeClass('active confirm-info');
                        }
                    } else {
                        showErrorBox(response.reply)
                    }
                    if (addressBlock.length > 1)
                    {
                        btn.prev().show();
                        btn.removeClass('loading');
                    }
                }
            });
            box.off('click');
        });
        box.on('click', '#box-cancel', function () {
            box.attr('class', 'box');
            box.off('click');
        });
    }
});

/** Disable address */
$(document).on('click', '.disable-address-btn', function () {
    var block = $(this).closest('.address-info');
    var addressId = $(this).data('address-id');
    var box = $('.box');
    /** Show warning popup message if last address in BR */
    if ($('.address-info.br-address').length === 1) {
        box.find('.error-msg').text($('#disable-last-address-msg').text());
        box.attr('class', 'box active error-info');
        box.on('click', '#box-ok', function () {
            box.removeClass('active error-info');
            box.off('click');
        });
    }
    /** Show warning popup message if address in EN/IN/ES */
    else {
        if ($('.address-info').length === 1) {
            box.find('.confirm-msg').text($('#disable-last-address-msg').text());
        }
        else
            box.find('.confirm-msg').text($('#disable-address-msg').text());
        box.attr('class', 'box active confirm-info');
        /** Disable address when click confirm */
        box.on('click', '#box-confirm', function () {
            var btn = $(this);
            btn.prev().hide();
            btn.addClass('loading');
            $.ajax({
                url: baseUrl + '/profile/disable-address',
                method: 'PUT',
                data: {address: addressId},
                dataType: 'JSON',
                beforeSend: function () {
                    btn.addClass('loading');
                    block.addClass('loading')
                },
                success: function (data) {
                    if (data.success) {
                        box.removeClass('active confirm-info');
                        var viewDetails = $('.address-info[data-address-id="' + addressId + '"]');
                        viewDetails.addClass('disabled-inst');
                        viewDetails.appendTo('.addresses-container');
                    } else {
                        showErrorBox(data.reply);
                    }
                    btn.prev().show();
                    btn.removeClass('loading');
                }
            });
            box.off('click');
        });
        box.on('click', '#box-cancel', function () {
            box.attr('class', 'box');
            box.off('click');
        });
    }
});

/** Limit input address max length */
$(document).on('keypress', '[data-max-length]', function (e) {
    if ($(this).val().length > $(this).data('max-length'))
        e.preventDefault();
});

/** Rename address blocks */
function renameAddressBlock() {
    var jsMessages = $('#address-js-message');
    var addressText = jsMessages.data('address');
    $.each($('.address-block'), function (i, elem) {
        $(elem).find('.address-label').text(addressText + " " + (i + 1));
    });
}
