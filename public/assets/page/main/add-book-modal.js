$(document).ready(function () {
    var page = $('#page-title').val();
    var readingList = {};
    var box = $('.box');
    box.find('#box-confirm').text($('#box-msg-yes').text());
    box.find('#box-cancel').text($('#box-msg-no').text());

    /** Click add reading list to open add reading list modal */
    $(document).on('click', '#btn-add-reading-list', function () {
        resetAddReadingListModal();
        /** Show request modal */
        $('.new_reading_list_modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('.new_reading_list_modal .close').show();
    });

    /** Reset add reading list modal */
    function resetAddReadingListModal() {
        $('.new_reading_list_modal .modal-step').removeClass('active');
        $('#formAddReadingList #step1').addClass('active');
        $('#formAddReadingList #readingListName').val("");
        $('#formAddReadingList #courseName').val("");
        $('#formAddReadingList #courseType').val("");
        $('#formAddReadingList #courseLevel').val("").trigger('change');
        $('#formAddReadingList #studentsNumber').val("");
        $('#formAddReadingList #startDate').datepicker('setDate', null);
        $('#formAddReadingList #institutions-selector option.my-primary').prop('selected', true).trigger('change');
        $('.modal-step .error').remove();
        $('.modal-step .warning').removeClass('warning');
    }


    /** Click close book request */
    $(document).on('click', '.new_reading_list_modal #close-modal', function (e) {
        e.preventDefault();
        var addReadingListModal = $('.new_reading_list_modal');
        var actualStepId = addReadingListModal.find('.modal-step.active').attr('id');
        if (actualStepId === 'step3') {
            closeModal(addReadingListModal);
            resetAddReadingListModal();
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
                closeModal(addReadingListModal);
                box.off('click');
                $('body').removeClass('box-above-modal');
                resetAddReadingListModal();
            });
        }
    });

    /** Go next request step */
    $(document).on('click', '.new_reading_list_modal .goStep:not(.loading)', function (e) {
        e.preventDefault();
        var nextStep = $(this).data('target-step');
        readingList.readingListName = $('#formAddReadingList #readingListName').val();
        $('#formAddReadingList #readingListName .error').remove();
        if (readingList.readingListName.length > 0) {
            readingList.readingListName = $('#formAddReadingList #readingListName').val();
            sendListData(readingList);
            $(this).closest('.modal-step').removeClass('active');
            $('#formAddReadingList #' + nextStep).addClass('active');
        } else {
            //show warning
            $('#formAddReadingList #readingListName').addClass('warning')
            let errorMsg = DOMPurify.sanitize('<span class="error">' + $('#formAddReadingList #readingListName').prop('title') + '</span>');
            $('#error_msg').html(errorMsg);
        }
    });


    /** Submit book request validate & send data*/
    function sendListData(readingList) {
        submitReadingList(readingList, function (success, readingList, response) {
            if (success) {
            /** add new reading list to the list of add book modal  */
                if ($('.reading-list-item').find('.multi_institutions').length == 0) {
                    $('#reading-lists-check-container .empty-modal').remove();
                    $('#reading-lists-check-container').attr('id','');
                    $('#formAddBook #add-book-reading-list').removeClass('disabled-btn').attr('disabled', false);
                }

            let readingListsCheck =' <div class="input-field user-term small-12 medium-12 large-12 columns checkbox__item reading-list-item ">'+
                '<input   class="checkbox__input multi_institutions"'+
                'type="checkbox"'+
                'name="" id="'+response.id+'"'+
                'value="'+ response.id+'">'+
                '<label class="checlbox__label"'+
                'for="'+ response.id+'">'+readingList.readingListName+'</label></div>';
                let sanitizeReadingListsCheck = DOMPurify.sanitize(readingListsCheck);
                $('div[title="reading-lists-check"]').append(sanitizeReadingListsCheck);
                var tabNav = $('.tabs-nav ul');
                var readingListContent = $('#reading-list-content');
                $('#formAddReadingList #step2').removeClass('active');
                $('#formAddReadingList #step3').addClass('active');
                tabNav.find('li').removeClass('active');
                let tabNavActive = '<li class="active">' +
                    '                                <a href="#' + response.id + '" data-toggle="tab" data-id=' + response.id + '>' +
                    '                                    <div class="book-info">' +
                    '                                        <div class="book-name"> ' + readingList.readingListName + '</div>' +
                    '                                        <div class="nbr-books" data-id="">0 ' + $('#books-tab').text() + '</div>' +
                    '                                    </div>' +
                    '                                </a>' +
                    '                            </li>';
                let sanitizeTabNavActive = DOMPurify.sanitize(tabNavActive);
                    tabNav.append(sanitizeTabNavActive);
                readingListContent.find('.tab-pane').removeClass('active');
                let readingListContentContent = '<div class="tab-pane active" id="' + response.id + '" \n' +
                    '<div id="tab1" class="tabs_container">\n' +
                    '        <div class="reading-info nexusSans">\n' +
                    '        <div class="reading-info-container row">\n' +
                    '            <div class="reading_info_contents small-12 medium-9 large-9 columns">\n' +
                    '                <div class="reading-list-name"> ' + readingList.readingListName + ' <span>\n' +
                    '                         <a href="javascript:;" class="edit-reading-list-btn">\n' +
                    '                                        <img src="http://InspectionCopy.test/assets/img/icon-edit/icon_edit.jpg?v=2.5.3" srcset="http://InspectionCopy.test/assets/img/icon-edit/icon_edit@2x.jpg?v=2.5.3 2x,http://InspectionCopy.test/assets/img/icon-edit/icon_edit@3x.jpg?v=2.5.3 3x" class="icon_edit" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Edit"></a>\n' +
                    '                    </span>\n' +
                    '                </div>\n' +
                    '            </div>\n' +
                    '<div class="sharing sharing_header">\n' +
                    '                                <div class="container">\n' +
                    '                                    <div class="more">\n' +
                    '                                        <button id="more-btn" class="more-btn" data-toggle="tooltip" data-placement="bottom"\n' +
                    '                                                title="{{ \'tooltip.more\'|trans }}">\n' +
                    '                                            <span class="more-dot"></span>\n' +
                    '                                            <span class="more-dot"></span>\n' +
                    '                                            <span class="more-dot"></span>\n' +
                    '                                        </button>\n' +
                    '                                        <div class="more-menu">\n' +
                    '                                            <ul class="more-menu-items" tabindex="-1" role="menu"\n' +
                    '                                                aria-labelledby="more-btn" aria-hidden="true">\n' +
                    '                                                <li class="more-menu-item"\n' +
                    '                                                    role="">\n' +
                    '                                                    <div class="read-now-btn" id="remove-list-btn"\n' +
                    '                                                         data-reading-list-id="'+ readingList.readingListID +'">\n' +
                    '                                                        <a href="javascript:;"\n' +
                    '                                                           class="more-menu-btn"\n' +
                    '                                                           role="menuitem">Delete list</a>\n' +
                    '                                                    </div>\n' +
                    '                                                </li>\n' +
                    '                                            </ul>\n' +
                    '                                        </div>\n' +
                    '                                    </div>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                    </div>\n' +
                    '       </div>\n' +
                    '   <div class="list_reading_books">\n' +
                    '   </div>\n' +
                    ' </div>\n' +
                    '</div>';
                let sanitizeReadingListContentContent = DOMPurify.sanitize(readingListContentContent);
                readingListContent.append(sanitizeReadingListContentContent);
                var categories = ['', 'core', 'supplementary', 'recommended'];
                var dragText = $('#drag-books').text();
                categories.forEach(element => {
                    var categoryText = $('#category-' + element).text();
                    let readListContent = '<div class="book_type ' + element + '">' + categoryText + '</div>\n' +
                        '                    <div class="books_list ' + element + '">\n' +
                        '                        <div class="drop-zone empty-zone" id="' + element + '">\n' +
                        '                                <div class="drag-placeholder nexusSans"\n' +
                        '                                     id="placeholder_drag">' + dragText + '</div>\n' +
                        '                        </div>\n' +
                        '                    </div>';
                    let readingListId = DOMPurify.sanitize(response.id);
                    let sanitizeReadListContent = DOMPurify.sanitize(readListContent);
                    $('#reading-list-content #' + readingListId + ' .list_reading_books').append(sanitizeReadListContent);
                });
            }
        });
    }

    /** Send request book data */
    function submitReadingList(readingList, callback) {

        var submitBtn = $('#submit-request-btn');
        submitBtn.addClass('loading');
        $.ajax({
            url: $('#formAddReadingList').data('url'),
            method: 'POST',
            data: readingList,
            success: function (response) {
                submitBtn.removeClass('loading');
                callback(response.success, readingList, response);
            },
            error: function (httpObj) {
                submitBtn.removeClass('loading');
                if (httpObj.status === 401)
                   document.location.reload();
            }
        });
    }

    /** Click request book to open request book modal */
    $(document).on('click', '#add-book-btn', function () {
        resetAddBookModal();
        let isbn = $(this).data('isbn');
        var title = $(this).data('title');
        /** Delete local storage item if it used in other page */
        localStorage.removeItem('selectedIsbn');
        localStorage.removeItem('title');
        /** Add the new isbn item */
        localStorage.setItem('selectedIsbn', isbn);
        localStorage.setItem('title', title);
        $('#action-book-modal-content').html('');
        /** Show request modal */
        $.ajax({
            url: '/get-allowed-reading-list',
            method: 'get',
            data: {isbn: isbn},
            dataType: 'html',
            success: function (data) {
                $('#add-book-modal').show().addClass('active');
                $('#action-book-modal-content').html(DOMPurify.sanitize(data));
                $('#action-book-modal').show().addClass('active');
                $('#step1-add-book').addClass('active');
                modalFix(function () {
                    $('body,html').addClass('modal-open active-request scrollable');
                    if (window.innerWidth < 640) {
                        $('.modal-step-content').css('minHeight', $('#copy-book-modal').height() - 86);
                    }
                });
                $('#add-book-modal .close').show();
            }
        });
    });

    /** Close Add Book Modal */
    $(document).on('click', '.close,#browse_btn_add_book', function () {
        localStorage.removeItem('readingListId');
        resetAddBookModal();
        /** Show request modal */
        $('#add-book-modal').hide().removeClass('active');
        modalFix(function () {
            $('body,html').removeClass('modal-open active-request scrollable');
        });
        $('#add-book-modal .close').hide();
    });

    /** Reset add book modal */
    function resetAddBookModal() {
        let firstStep = $('#step1-add-book');
        let secondStep = $('#step2-add-book');
        $('#checkOneReadingListMsg').hide();
        secondStep.removeClass('active');
        firstStep.addClass('active');
        $(".multi_institutions").prop("checked", false);
    }

    $(document).on('click', '#add-book-reading-list', function (e) {
        e.preventDefault();
        var checkedReadingList = $('.reading-lists input:checked');
        var url = $('#formAddBook').data('url');
        var data = {'readingLists': []};
        localStorage.removeItem('readingListId');
        if (checkedReadingList.length == 0) {
            /** show error message*/
            $('#checkOneReadingListMsg').show();
        } else {
            $('#checkOneReadingListMsg').hide();
            $.each(checkedReadingList, function (i, elem) {
                data['readingLists'].push({'id': $(elem).attr('id')});
            });
            data['isbn'] = localStorage.getItem('selectedIsbn');
            data['bookTitle'] = localStorage.getItem('title');
            var book = $('div').find('.recommended-books-list[id="' + data['isbn'] + '"]');
            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                beforeSend: function () {
                    $(this).addClass('loading');
                },
                success: function (response) {
                    if (response.success) {
                        $('#step1-add-book').removeClass('active');
                        $('#step2-add-book').addClass('active');
                        localStorage.setItem('readingListId', data['readingLists'][0].id);
                        /** add localStorage attribute to detect redirection */
                        localStorage.setItem('addedListSelected',true);
                        $(this).removeClass('loading');
                        book.remove();

                }
            }
            });
        }
    });
});
