$(document).ready(function () {
    var box = $('.box');
    var bannerForm = $('#formBanner'),
        newBannerBtn = $('#newBannerBtn'),
        editBannerBtn = $('#editBannerBtn');
    /** Disable language switcher in banner page */
    $('.langSwitcher').attr('disabled',true);
    $('#langSwitcher option:selected').text('English');
    var numberActiveBanner = 0;
    // get number of active banner
    $.ajax({
        url: '/admin/getActiveBanner',
        method: 'get',
        dataType: 'json',
        success: function (nbActiveBanner) {
            numberActiveBanner = nbActiveBanner.result;
        }
    });

    /** init tinymce foreach textArea with class wysiwyg */
    function initMCEall(e) {
        tinyMCE.init({
            max_chars: 200, // max. allowed chars
            setup: function (ed) {
                var allowedKeys = [8, 37, 38, 39, 40, 46]; // backspace, delete and cursor keys
                ed.on('keydown', function (e) {
                    if (allowedKeys.indexOf(e.keyCode) != -1) return true;
                    if (tinymce_getContentLength() + 1 > this.settings.max_chars) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    return true;
                });
                ed.on('keyup', function (e) {
                    tinymce_updateCharCounter(this, tinymce_getContentLength());
                });
            },
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

    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".tox-textfield").length)
            e.stopImmediatePropagation();
    });

    function tinymce_updateCharCounter(el, len) {
        $('#' + el.id).prev().find('.char_count').text(len + '/' + el.settings.max_chars);
    }
    function tinymce_getContentLength() {
        return tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText.length;
    }

    initMCEall('#banner-content-en');
    initMCEall('#banner-content-es');
    initMCEall('#banner-content-de');
    initMCEall('#banner-content-fr');
    initMCEall('#banner-content-anz');

    /** Prevent Bootstrap dialog from blocking focusin */
    $(document).on('focusin', function (e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });

    $(document).on('keyup','.banner-content-es',function () {
        if (tinyMCE.get('banner-content-es').getContent({format: 'text'}).length>200) {
            tinyMCE.get('banner-content-es').getBody().setAttribute('contenteditable', false);
        }
    });


    /** validate banner form and return it's data */
    function validateBannerInformation() {
        var bannerTitleEN = $('#banner-title-en'),
            bannerTitleFR = $('#banner-title-fr'),
            bannerTitleES = $('#banner-title-es'),
            bannerTitleDE = $('#banner-title-de'),
            bannerTitleANZ = $('#banner-title-anz'),
            bannerContentEN = tinyMCE.get('banner-content-en').getContent({format: 'html'}),
            bannerContentFR = tinyMCE.get('banner-content-fr').getContent({format: 'html'}),
            bannerContentES = tinyMCE.get('banner-content-es').getContent({format: 'html'}),
            bannerContentDE = tinyMCE.get('banner-content-de').getContent({format: 'html'}),
            bannerContentANZ = tinyMCE.get('banner-content-anz').getContent({format: 'html'}),
            formValid = true;


        /** Validate fields */
        var form =  $('#bannerModal');
        var titleFields = form.find('.validate-title');
        var fields = form.find('.validate');
        titleFields.removeClass('warning');
        fields.removeClass('warning');
        form.find('.error').remove();

        /** Validate all fields */
        $.each(titleFields, function (key, element) {
            var input = $(element).val().replace(/^\s+/, '').replace(/\s+$/, '');
            if (($(element).val() === null || $(element).val() === 'undefined' || $(element).val() === '' || input ==='') && $(element).is(':visible') && !$(element).hasClass('empty')) {
                $(element)
                    .addClass('warning')
                    .parent().append('<span class="error text-center" style="position: relative;">' + 'Please complete the mandatory field' + '</span>');
                formValid = false;
            }
        });

       $.each(fields, function (key, element) {
           var input = tinyMCE.get($(element)[0].id).getContent({format: 'text'}).replace(/^\s+/, '').replace(/\s+$/, '');
            if ( ( tinyMCE.get($(element)[0].id).getContent({format: 'html'}).length <1) || input === '' ) {
                $(element)
                    .addClass('warning')
                    .parent().append('<span class="error text-center" style="position: relative;">' + 'Please complete the mandatory field' + '</span>');
                formValid = false;
            }
        });

        /** Remove field error if they got a value */
        $(document).on('change keyup', 'input,select', function () {
            if (($(this).val() && $(this).hasClass('warning')) || ($(this).attr('type') === "checkbox" && $(this).is(':checked')))
                $(this).removeClass('warning').parent().find('.error').remove();
        });

        if (formValid)
            return {
                contentEN: bannerContentEN,
                contentFR: bannerContentFR,
                contentES: bannerContentES,
                contentDE: bannerContentDE,
                contentANZ: bannerContentANZ,
                titleEN: bannerTitleEN.val(),
                titleFR: bannerTitleFR.val(),
                titleES: bannerTitleES.val(),
                titleDE: bannerTitleDE.val(),
                titleANZ: bannerTitleANZ.val(),
                bannerClose: $('#banner_close').is(':checked') ? 1 : 0,
                bannerActive:  $('#banner_active').is(':checked') ? 1 : 0,
                bannerType: $('#banner-type').find('option:selected').val()
            };
        else
            return null;
    }

    $('#formBannerPage').on('submit', function (e) {
        if (!$(this).hasClass('allowSubmit'))
            e.preventDefault();
    });
    $(document).on('click', '#formBannerPage .btn-submit', function () {
        $('#formBannerPage').addClass('allowSubmit').submit();
    });

    /** Click preview to display banner page for user  */
    $(document).on('click', '.preview-banner:not(.complete-url)', function () {
        var lang = $('#current_lang').data('lang');
        var block = $('.show-banner');
        block.html('');
        var selected =  $('.preview-banner.selected');
        selected.removeClass('selected');
        var selectedPreview=$(this).addClass('selected');
        var banner = selectedPreview.parent();
        var previewModal = $('.preview-banner-modal');
        /** select admin language*/
        selectElement('banner-language', lang);
        var bannerTitle;
        var bannerContent;
        switch (lang) {
            case 'es':
                bannerTitle= banner[0].dataset.titleEs;
                bannerContent= banner[0].dataset.contentEs;
                break;
            case 'en':
                bannerTitle= banner[0].dataset.titleEn;
                bannerContent= banner[0].dataset.contentEn;
                break;
            case 'de':
                bannerTitle= banner[0].dataset.titleDe;
                bannerContent= banner[0].dataset.contentDe;
                break;
            case 'fr':
                bannerTitle= banner[0].dataset.titleFr;
                bannerContent= banner[0].dataset.contentFr;
                break;
            case 'anz':
                bannerTitle= banner[0].dataset.titleAnz;
                bannerContent= banner[0].dataset.contentAnz;
                break;
        }
        var bannerClose = banner[0].dataset.close ;
        var bannerType = banner[0].dataset.type ;
            block.append(
                '<div class="row covid-banner '+bannerType+'">' +
                '<div class="covid-container">' +
                '<span class="covid-tag">' + bannerTitle + ' ' +'</span>' +
                '<span class="covid-content">' +bannerContent + ' ' + '</span>' +
                '</div>' +
                '</div>');
            if (bannerClose ==="1") {
              $('.covid-banner').append(
                  '<div class="close" id="close-covid-banner"></div>');
            }
        /** Show preview banner modal */
        previewModal.show().addClass('active');
    });

    $(document).on('change','#banner-language',function(){
        var selectedPreview =  $('.preview-banner.selected');
        var banner = selectedPreview.parent();
        var lang = $('#banner-language :selected').val();
        console.log(lang);
        var bannerTitle, bannerContent;
        switch (lang) {
            case 'es':
                 bannerTitle= banner[0].dataset.titleEs;
                 bannerContent= banner[0].dataset.contentEs;
                break;
            case 'en':
                 bannerTitle= banner[0].dataset.titleEn;
                 bannerContent= banner[0].dataset.contentEn;
                break;
            case 'de':
                 bannerTitle= banner[0].dataset.titleDe;
                 bannerContent= banner[0].dataset.contentDe;
                break;
            case 'fr':
                 bannerTitle= banner[0].dataset.titleFr;
                 bannerContent= banner[0].dataset.contentFr;
                break;
            case 'anz':
                bannerTitle= banner[0].dataset.titleAnz;
                bannerContent= banner[0].dataset.contentAnz;
                break;
        }
        /** set title and content of banner according to the selected language */
        $('.covid-container span.covid-tag').text(bannerTitle);
        $('.covid-container span.covid-content').html(bannerContent);
    });

    /** Submit add new banner form */
    $(document).on('click', '.btn-save-banner', function () {
        newBannerBtn.removeClass('hide');
        var url = $('#formBanner').data('url');
        /** submit the form by ajax request if valid */
        var data = validateBannerInformation();
        var activeBanners = $( 'li.banner-info' ).not( ".disabled-banner" );
        if (data.bannerActive == 1 && activeBanners.length > 0) {
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text('You should activate only one banner. Deactivate others and try again');
            box.find('#box-cancel').html(DOMPurify.sanitize($('#ok-btn-box').text()));
            box.find('#box-cancel').css('display', '');
            $('#box-confirm').addClass('hidden');
            $('.modal-backdrop').css('display', 'none');
            $('#bannerModal').css('z-index', '2');
            box.on('click', '#box-cancel', function () {
                box.attr('class', 'box');
                box.off('click');
            });
        } else {
            if (data) {
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    beforeSend: function () {
                        newBannerBtn.addClass('loading');
                    },
                    success: function (data) {
                        if (data.success) {
                            var banner =
                                '<li class="ui-state-default banner-info ' + data.banner.type + '" data-token="' + data.banner.token + '" data-title-es="'+ data.banner.title_e_s +
                                    '" data-title-fr="'+ data.banner.title_f_r + '" data-title-de="'+ data.banner.title_d_e + '" data-title-en="'+ data.banner.title_e_n + '" data-title-anz="'+ data.banner.title_a_n_z +
                                '" data-content-es="'+ data.banner.content_e_s +  '" data-content-fr="'+ data.banner.content_f_r + '" data-content-de="'+ data.banner.content_d_e +
                                '" data-content-en="'+ data.banner.content_e_n +'" data-content-anz="'+ data.banner.content_a_n_z +  '" data-active="'+ data.banner.is_disabled +  '" data-close="'+ data.banner.close_banner + '" data-type="'+ data.banner.type + '"> ' +
                                '<p class="title-banner" >' + data.banner.title_e_n + '</p>' +
                                '<input type="hidden" name="banner[' + data.banner.token + ']" class="banner-content" value="'+ data.banner.contentEN + ' ">' +
                                '<i class="edit-banner" data-url="' + data.editUrl + '"title="edit banner" ></i>' +
                                '<i class="delete-banner" data-url ="' + data.deleteUrl + '"title="delete banner"></i>' +
                                '<i class="disable-banner" data-url ="' +data.disableUrl + '"title="disable banner"></i>' +
                                '<i class="preview-banner" title="preview banner"></i>' +
                                '</li>';
                            let sanitizeBanner = DOMPurify.sanitize(data.banner.content);
                            $('#sortable').append(sanitizeBanner);
                            let sanitizeBannerContent = DOMPurify.sanitize(data.banner.content);
                            let sanitizeBannerToken = DOMPurify.sanitize(data.banner.token);
                            $('[name="banner[' + sanitizeBannerToken + ']"').val(sanitizeBannerContent);
                            $('.btn-reset-form').click();
                            location.reload();
                        } else {
                            showErrorBox(false, 'an error occurred please repeat the process');
                        }
                        newBannerBtn.removeClass('loading');
                    }
                });
            }
        }
    });

    /** Edit banner information */
    $(document).on('click', '.edit-banner', function (e) {

        e.preventDefault();
        var selectedEdit =  $('.edit-banner.selected');
        selectedEdit.removeClass('selected');
        $(this).addClass('selected');
        var elem =  $(this).parent();
        /** extract old data from the list */
        var bannerTitleEN= elem[0].dataset.titleEn;
        var bannerTitleFR= elem[0].dataset.titleFr;
        var bannerTitleDE= elem[0].dataset.titleDe;
        var bannerTitleES= elem[0].dataset.titleEs;
        var bannerTitleANZ= elem[0].dataset.titleAnz;
        var contentES = elem[0].dataset.contentEs,
            contentEN = elem[0].dataset.contentEn,
            contentFR = elem[0].dataset.contentFr,
            contentDE = elem[0].dataset.contentDe,
            contentANZ = elem[0].dataset.contentAnz;
        var type= elem[0].dataset.type;
        var active, close;
        active = (elem[0].dataset.active === "1") ? 1 : 0;
        close = (elem[0].dataset.close === "1") ? 1 : 0;
        /** Bin value to edit form */
        var form = $('#formBanner');
        /** Bind values title with form */
        form.find('#banner-title-es').val(bannerTitleES);
        form.find('#banner-title-en').val(bannerTitleEN);
        form.find('#banner-title-de').val(bannerTitleDE);
        form.find('#banner-title-fr').val(bannerTitleFR);
        form.find('#banner-title-anz').val(bannerTitleANZ);
        /** Bind value tinymce content */
        tinymce.get('banner-content-en').setContent(contentEN);
        tinymce.get('banner-content-es').setContent(contentES);
        tinymce.get('banner-content-de').setContent(contentDE);
        tinymce.get('banner-content-fr').setContent(contentFR);
        tinymce.get('banner-content-anz').setContent(contentANZ);
        /** Bind others values */
        form.find('#banner_active').data('active',active).prop('checked',active);
        form.find('#banner_close').data('close',close).prop('checked',close);
        selectElement('banner-type', type);
        /** Open the modal after filling the old data to the form */
        newBannerBtn.addClass('hide');
        editBannerBtn.removeClass('hide');
        var form =  $('#bannerModal');
        var titleFields = form.find('.validate-title');
        var fields = form.find('.validate');
        titleFields.removeClass('warning');
        fields.removeClass('warning');
        form.find('.error').remove();
        $('#openModal').click();
    });

    /** Element selector by value */
    function selectElement(id, valueToSelect) {
        var element = document.getElementById(id);
        element.value = valueToSelect;
    }

    /** Click save banner after editing */
    $(document).on('click', '#editBannerBtn:not(.loading)', function () {
        var editingRow = $('.edit-banner.selected');
        var elem = editingRow.parent();
        var url = editingRow.data('url');
        var data = validateBannerInformation();
        var activeBanners = $( 'li.banner-info' ).not( ".disabled-banner" );
        if (data.bannerActive == 1 && elem[0].dataset.active != 1 && numberActiveBanner > 0) {
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text('You should activate only one banner. Deactivate others and try again');
            box.find('#box-cancel').html(DOMPurify.sanitize($('#ok-btn-box').text()));
            box.find('#box-cancel').css('display', '');
            $('#box-confirm').addClass('hidden');
            $('.modal-backdrop').css('display', 'none');
            $('#bannerModal').css('z-index', '2');
            box.on('click', '#box-cancel', function () {
                box.attr('class', 'box');
                box.off('click');
            });
        } else {
            /** validate form & return its data */

            if (data != null) {
                editBannerBtn.addClass('loading');
            }
            if (data) {
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function (data) {
                        if (data.success) {
                            elem.find('.title-banner').text(data.banner.title_e_n);
                            elem.find('.banner-content').val(data.banner.content_e_n);
                            elem[0].dataset.titleEs=data.banner.title_e_s;
                            elem[0].dataset.titleFr=data.banner.title_f_r;
                            elem[0].dataset.titleEn=data.banner.title_e_n;
                            elem[0].dataset.titleDe=data.banner.title_d_e;
                            elem[0].dataset.titleAnz=data.banner.title_a_n_z;
                            elem[0].dataset.contentEs=data.banner.content_e_s;
                            elem[0].dataset.contentFr=data.banner.content_f_r;
                            elem[0].dataset.contentEn=data.banner.content_e_n;
                            elem[0].dataset.contentDe=data.banner.content_d_e;
                            elem[0].dataset.contentAnz=data.banner.content_a_n_z;
                            elem[0].dataset.type= data.banner.type;
                            elem[0].dataset.close= data.banner.close_banner;
                            elem[0].dataset.active = data.banner.is_disabled;
                            $('.btn-reset-form').click();
                            location.reload();
                        } else {
                            showErrorBox(false,'an error occurred please repeat the process');
                            $('.btn-reset-form').click();
                            editBannerBtn.removeClass('loading');
                        }
                        editBannerBtn.removeClass('loading');
                        editingRow.removeClass('selected');
                    },
                    fail: function () {
                        showErrorBox(false, data.message);
                        $('.btn-reset-form').click();
                        editBannerBtn.removeClass('loading');
                    }
                })
            }
            editBannerBtn.removeClass('loading');
        }


    });

    /** Disable banner */
    $(document).on('click', '.disable-banner:not(.loading)', function (e) {
        e.preventDefault();
        var elem = $(this),
            url = elem.data('url');
        /** here show the confirm box */
        box.attr('class', 'box active confirm-info');
        box.find('.confirm-msg').text($('#deactivate-banner').text());
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
                    if (data.success) {
                        var disabledBanner = elem.closest('li');
                        disabledBanner.addClass('disabled-banner');
                       /* disabledBanner.appendTo('#sortable');*/
                    }
                    else
                        showErrorBox(false, 'an error occurred please repeat the process');
                    elem.removeClass('loading')
                    location.reload();
                }
            });
            box.removeClass('active');
        });
        box.on('click', '#box-cancel', function () {
            box.attr('class', 'box');
            box.off('click');
        });
    });

    /** Disable banner */
    $(document).on('click', '.enable-banner:not(.loading)', function (e) {
        e.preventDefault();
        var elem = $(this),
            url = elem.data('url');
        var activeBanners = $( 'li.banner-info' ).not( ".disabled-banner" );
        var numberActiveBanner = activeBanners.length;
        if(numberActiveBanner == 0) {
            // get dynamic number of active banner
            $.ajax({
                url: '/admin/getActiveBanner',
                method: 'get',
                dataType: 'json',
                success: function (nbActiveBanner) {
                    if(nbActiveBanner.result == 1) {
                        box.attr('class', 'box active confirm-info');
                        box.find('.confirm-msg').text('You should activate only one banner. Deactivate others and try again');
                        box.find('#box-cancel').html(DOMPurify.sanitize($('#ok-btn-box').text()));
                        box.find('#box-cancel').css('display', '');
                        $('#box-confirm').addClass('hidden');
                    }
                    numberActiveBanner = nbActiveBanner.result;
                }
            });
        }
        if (numberActiveBanner > 0) {
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text('You should activate only one banner. Deactivate others and try again');
            box.find('#box-cancel').html(DOMPurify.sanitize($('#ok-btn-box').text()));
            box.find('#box-cancel').css('display', '');
            $('#box-confirm').addClass('hidden');

        } else {

            /** here show the confirm box */
            box.attr('class', 'box active confirm-info');
            box.find('.confirm-msg').text($('#activate-banner').text());
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
                        if (data.success) {
                            var disabledBanner = elem.closest('li');
                            disabledBanner.removeClass('disabled-banner');
                        }
                        else
                            showErrorBox(false, 'an error occurred please repeat the process');
                        elem.removeClass('loading');
                        location.reload();
                    }
                });
                box.removeClass('active');
            });
        }
        box.on('click', '#box-cancel', function () {
            box.attr('class', 'box');
            box.off('click');
            $('#box-confirm').removeClass('hidden');
            box.find('#box-cancel').html(DOMPurify.sanitize($('#cancel-btn-box').text()));
            location.reload();
        });
    });


    /** Delete banner */
    $(document).on('click', '.delete-banner:not(.loading)', function (e) {
        e.preventDefault();
        var elem = $(this),
            url = elem.data('url');
        /** here show the confirm box */
        box.attr('class', 'box active confirm-info');
        box.find('.confirm-msg').text('Are you sure you want to delete tha banner for all languages.');
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

    /** Reset add banner form */
    $('.btn-reset-form').on('click', function () {
        /** remove the marked row that was edited if exist */
        $('.editRow').removeClass('editRow');
        /** reset modal button to initial state */
        newBannerBtn.removeClass('hide');
        editBannerBtn.addClass('hide');
        /** remove all errors */
        $('.error:not(.hide)').addClass('hide');
        /** reset answer input */
        bannerForm.find('#banner-title-en').val('');
        bannerForm.find('#banner-title-es').val('');
        bannerForm.find('#banner-title-fr').val('');
        bannerForm.find('#banner-title-de').val('');
        bannerForm.find('#banner-title-anz').val('');
        /** reset tinymce content */
        tinymce.get('banner-content-en').setContent('');
        tinymce.get('banner-content-es').setContent('');
        tinymce.get('banner-content-de').setContent('');
        tinymce.get('banner-content-fr').setContent('');
        tinymce.get('banner-content-anz').setContent('');
        /** Reset checks in form*/
        bannerForm.find('#banner_close').data('close',false).prop('checked',false);
        bannerForm.find('#banner_active').data('active',false).prop('checked',false);
        var form =  $('#bannerModal');
        var titleFields = form.find('.validate-title');
        var fields = form.find('.validate');
        titleFields.removeClass('warning');
        fields.removeClass('warning');
        form.find('.error').remove();
        location.reload();

    });

});
