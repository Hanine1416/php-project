$(document).ready(function () {
    disableCopyMove();
    function disableCopyMove() {
        //get count list
        let numberOfList = $('.tabs-nav li').length;
        //disable copy and move wen book from old requested exist on all others liste
        $(".requested_books #copy-book-btn").each(function () {
            let isbn = this.getAttribute('data-isbn');
            if($('.block_cover_book[id='+isbn+']').length == numberOfList) {
                $('.requested_books #copy-book-btn[data-isbn='+isbn+']').addClass('isDisabled');
                $('.requested_books #copy-book-btn[data-isbn='+isbn+'] a').addClass('isDisabled');
            } else {
                $('.requested_books #copy-book-btn[data-isbn='+isbn+']').removeClass('isDisabled');
                $('.requested_books #copy-book-btn[data-isbn='+isbn+'] a').removeClass('isDisabled');
            }
        });
    }

    //get hash code at next page
    var hashcode = DOMPurify.sanitize(window.location.hash);
    var removeButton = false;
    if(hashcode) {
        goToBook(hashcode);
    }
    $('.link-book').on('touchstart click', function (event) {
        var hash = DOMPurify.sanitize($(this).attr('data-isbn'));
        if (hash !== "" && $(this).attr('href').indexOf("my-books") > -1) {
            event.preventDefault();
            goToBook('#'+hash);
        } else {
            window.location = DOMPurify.sanitize($(this).attr('href'));
        }
    });

    /**
     * go to a specific hash on the my ic books
     * @param hashcode
     */
    function goToBook(hashcode) {
        $('.book_item ').css('background','initial');
        //move page to any specific position of next page(let that is div with id "hashcode")
        $('.tabs-nav #myBooksList a').tab('show');
        if($('.myBooksList div'+hashcode).offset()) {
            var top_section = $('.myBooksList div'+hashcode).offset().top;
            if (isMobile()) {
                setTimeout(function () {$('li.active a').trigger('click');});
                window.scrollTo({
                    top: top_section-600,
                    behavior: 'smooth'
                });
            } else {
                window.scrollTo({
                    top: top_section-250,
                    behavior: 'smooth'
                });
            }
            $('.myBooksList div'+hashcode).parent().parent().css('background','#d6e9f06b');
        }
        if (isMobile()) {
            $('.title-notif').trigger('click');
        }
    }
    initPopover();
    closePopover();
    resizeInput($('#reading-list-content .input-section-name'));
    var readingList = {};
    var box = $('.box');
    box.find('#box-confirm').text($('#box-msg-yes').text());
    box.find('#box-cancel').text($('#box-msg-no').text());

    var readingListID = sanitizeHTML(localStorage.getItem('readingListId'));
    var addedListSelected = sanitizeHTML(localStorage.getItem('addedListSelected'));
    if ( addedListSelected == "true" && readingListID != null){
        var tabNav = $('.tabs-nav ul');
        var readingListContent = $('#reading-list-content');
        tabNav.find('li').removeClass('active');
        readingListContent.find('.tab-pane').removeClass('active');
        readingListContent.find('.tab-pane#'+readingListID).addClass('active');
        tabNav.find("a[data-id='" + readingListID + "']").parent().addClass('active');
        localStorage.removeItem('addedListSelected');
        localStorage.removeItem('readingListId');
    }

    /** close move modal */
    $(document).on('click', '.reading_list_modal #close-modal,#finish-move-book', function (e) {
        e.preventDefault();
        let action = DOMPurify.sanitize(localStorage.getItem('action'));
        resetActionBookModal(action);
        modalFix(function () {
            $('body,html').removeClass('modal-open active-request scrollable');
            /** Show remove modal */
            $('#action-book-modal').hide().removeClass('active');
        });
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0,0)
        $('html,body').animate({scrollTop: 0},'slow');
        return false;
    });

    /** switch between lists */
    $('.tabs-nav').on('click',function(){
        $('[data-toggle="tooltip"]').tooltip();
        initPopover();
        closePopover();
    });


    /** Click copy to open move book modal */
    $('body').on('click', '#move-book-btn', function (e) {
        e.preventDefault();
        if ($(this).find('.more-menu-btn').hasClass('isDisabled')){
            return false;
        }
        var loader = $('.loader-list .loading-spinner');
        loader.show();
        $('html,body').animate({scrollTop: 0},'slow');
        let action = 'move';
        localStorage.setItem('action', action);
        let isbn = $(this).data('isbn');
        let readingListID = $(this).data('reading-list-id');
        localStorage.setItem('selectedIsbn', isbn);
        localStorage.setItem('reading-list-id', readingListID);
        localStorage.setItem('title', $(this).data('title'));
        localStorage.setItem('author', $(this).data('author'));
        $.ajax({
            url: '/reading-list-copy-move-modal',
            method: 'get',
            data: {isbn: isbn, action: action},
            dataType: 'html',
            success: function (data) {
                if (data.length === 0 || data.length === 2) {
                    document.location.reload();
                } else {
                    e.preventDefault();
                    resetActionBookModal(action);
                    /** Show copy modal */
                    $('#action-book-modal').show().addClass('active');
                    modalFix(function () {
                        $('body,html').addClass('modal-open active-request scrollable');
                    });
                    let sanitizeData = DOMPurify.sanitize(data);
                    $('#action-book-modal-content').html(sanitizeData);
                }
            }, complete: function () {
                $('#action-book-modal .reading-list-item :checkbox').change(function () {
                    if ($('#action-book-modal .reading-list-item :checkbox:checked').length === 0) {
                        $('#copy-book-reading-list,#move-book-reading-list').addClass('btnDisabled');
                    } else {
                        $('#copy-book-reading-list,#move-book-reading-list').removeClass('btnDisabled');
                    }
                });
                loader.hide();
            }
        });
    });


    /** call back function to execute add book into webservice  */
    function addBook(data,activeReadingList, activeReadingListName) {
        data['action'] = DOMPurify.sanitize(localStorage.getItem('action'));
        data['bookTitle'] = DOMPurify.sanitize(localStorage.getItem('title'));
        data['listName'] = activeReadingListName;
        $.when($.ajax({
            url: '/add-book',
            method: 'POST',
            data: data,
            async: false,
            beforeSend: function () {
                $(this).addClass('loading');
            },
            success: function (response) {
                if (response.success) {
                    $('.modal-step').removeClass('active');
                    /** Close modal and show notif */
                    closeModal($('#action-book-modal'));
                    $('#copy-remove-success .cookies-content').text($('#move-book-msg').text());
                    $('.update-notif').show();
                    $('.update-notif').fadeOut(5000);
                    $(document).on('click', '.close-btn', function (e) {
                        e.preventDefault();
                        $(this).closest('.update-notif').remove();
                    });
                    localStorage.removeItem('selectedIsbn');
                    localStorage.setItem('readingListId', data['readingLists'][0].id);
                    $(this).removeClass('loading');
                    /** increment number of books in readinglists navbar  */
                    data['readingLists'].forEach(readingList => {
                        let currentNbr = $('div').find("[data-id='" + readingList.id + "']").children().children().eq(1).text().match(/\d+/)[0];
                        let replaced = $('div').find("[data-id='" + readingList.id + "']").children().children().eq(1).text().replace(/[0-9]+/, parseInt(currentNbr) + 1);
                        $('div').find("[data-id='" + readingList.id + "']").children().children().eq(1).text(replaced);
                        let sanitizeReadingList = DOMPurify.sanitize(readingList.id);
                        let dropzone = $("div[data-title='" + sanitizeReadingList + "']").first();
                        if (parseInt(currentNbr) === 0) {
                            var tabPane= $('.tab-pane#'+sanitizeReadingList);
                            tabPane.find('.list_reading_books.have-books').show();
                            tabPane.find('.list_reading_books.no-books').hide();
                            tabPane.find('.add-section-bloc').show();
                            tabPane.find('.new-section-bloc').show();
                            //update input section name
                            tabPane.find('.new-section-bloc .input-section-name').val($('.initial-section-name').text());
                            tabPane.find('li.export-list .export-list-btn').parent().show();
                            if(dropzone.length > 0) {
                                if (dropzone[0].className.indexOf("empty-zone") !== -1) {
                                    dropzone[0].classList.remove('empty-zone');
                                }
                            }
                            dropzone.html(data['book']);
                        } else {
                            dropzone.append(data['book']);
                        }
                    });
                } else {
                    console.log('erreur')
                    //window.location = document.location.origin;
                }
            }
        })).then(function () {
            removeBook(data,activeReadingList);
        });

    }

    /** copy book after reading lists selection */
    $(document).on('click', '#move-book-reading-list', function (e) {
        e.preventDefault();
        var checkedReadingList = $('#copy-move input:checked');
        var data = {'readingLists': []};
        var listsCount = $('div .reading-list-item').find($("input")).length;
        localStorage.removeItem('readingListId');
        if (checkedReadingList.length > 0) {
            $.each(checkedReadingList, function (i, elem) {
                data['readingLists'].push({'id': $(elem).val()});
            });
            data['isbn'] = DOMPurify.sanitize(localStorage.getItem('selectedIsbn'));
            data['title'] = DOMPurify.sanitize(localStorage.getItem('title'));
            data['existInAll'] = checkedReadingList.length === listsCount;
            var activeReadingList = $('.tabs-nav  li.active a').data('id');
            var activeReadingListName = $('.tabs-nav  li.active .book-name').text();
            // data['section'] = $('.tab-pane.active #book-item[data-isbn="' + data['isbn'] + '"]').parent().parent().parent().find('.input-section-name').val();
            $.when(
                $.when($.ajax({
                    url: 'reading-list-book-content',
                    method: 'GET',
                    data: {isbn: data['isbn'], readingListID: activeReadingList, existInAll:data['existInAll']},
                    success: function (bookContent) {
                        data['book'] = bookContent;
                    }
                })).then(function (){
                    addBook(data,activeReadingList, activeReadingListName);
                    //when remove book from list all btn should be enabled
                    let copybtn =  $('div').find("#copy-book-btn[data-isbn='" + data['isbn'] + "']");
                    let movebtn =  $('div').find("#move-book-btn[data-isbn='" + data['isbn'] + "']");
                    for(var i = 0 ; i<copybtn.length;i++) {
                        copybtn[i].children[0].classList.remove('isDisabled');
                        movebtn[i].children[0].classList.remove('isDisabled');
                    }
                }));
        }
    });


    function removeBook(data,readingListID){
        $.ajax({
            url: '/reading-list-remove-book',
            method: 'GET',
            data: {isbn: data['isbn'], readingListID: readingListID, option: '', bookTitle: data['title']},
            async: false,
            success: function (resp) {
                if (resp.Result === true) {
                    $('.tab-pane.active #book-item[data-isbn="' + data['isbn'] + '"]')[0].style.display = 'none';
                    $('.tab-pane.active #book-item[data-isbn="' + data['isbn'] + '"]')[0].remove();
                    let bookElement = $('div').find("[data-id='" + readingListID + "']").children().children().eq(1);
                    /** get current number of books and decrease it  */
                    let currentNbr = bookElement.text().match(/\d+/)[0];
                    let replaced = bookElement.text().replace(/[0-9]+/, currentNbr - 1);
                    bookElement.text(replaced);
                    if (currentNbr-1 ==0) {
                        var tabPane= $('.tab-pane#'+readingListID);
                        tabPane.find('.list_reading_books.have-books').hide();
                        tabPane.find('.list_reading_books.no-books').show();
                        tabPane.find('li.export-list .export-list-btn').parent().hide();
                        tabPane.find('.add-section-bloc, .new-section-bloc, .NewSection').hide();
                    }
                    initDragAndDrop();
                    resetDropZones();
                    disableCopyMove();
                } else {
                    //window.location = document.location.origin;
                }
            },
        });
    }



    /** change dropzone style if is empty */
    function resetDropZones() {
        $(".drop-zone").each(function (index, value) {
            for (var i = 0; i < value.children.length; i++) {
                if (value.children[i].className.indexOf("draggable") === -1) {
                    value.children[i].remove();
                }
            }
            if (value.children.length === 0) {
                value.classList.add('empty-zone');
                value.classList.add('placeholder');

            } else {
                value.classList.remove('empty-zone');
            }
        });
    }

    /** Open Remove List Modal */
    $(document).on('click', '#remove-list-btn', function (e) {
        var box = $('.box');
        box.find('#box-confirm').text($('#btn-confirm').text());
        box.find('#box-cancel').text($('#cancel-btn').text());
        box.attr('class', 'box active confirm-info');
        box.find('.confirm-msg').text($('#remove-list-text').text());
        box.find('#box-cancel').css('display', '');
        box.on('click', '#box-confirm', function () {
            box.removeClass('active error-info');
            box.off('click');
            $('#box-confirm').addClass('loading');
            $.ajax({
                url: '/reading-list-remove-list',
                method: 'DELETE',
                data: {readingListID:  $('.tabs-nav  li.active a').data('id')},
                async: false,
                beforeSend: function () {
                    $('#box-confirm').addClass('loading');
                },
                success: function (resp) {
                    $('.tabs-nav  li.active').remove();
                    $('.tab-pane.active').remove();
                    $('.tabs-nav  li a').first().click();
                    if ( $('.tabs-nav li').length == 0 ){
                        $('#reading-list-content').remove();
                        $('.list_reading_books').show();
                    }
                    $('#box-confirm').removeClass('loading');
                    /** show notification list deleted successfully */
                    $('.update-profile-cookie').show();
                    $('.update-profile-cookie').fadeOut(5000);
                    $(document).on('click', '.close-btn', function (e) {
                        e.preventDefault();
                        $(this).closest('.update-profile-cookie').remove();
                    });
                },
            });
            $('#box-confirm').removeClass('loading');
        });
        box.on('click', '#box-cancel', function () {
            box.removeClass('active error-info');
            box.off('click');
        });
    });

    /** Close Remove List Modal */
    $(document).on('click', '#cancel-remove-list,#close-remove-list-modal,#browse_btn_remove_list', function (e) {
        $('#remove-list-modal').hide().removeClass('active');
        modalFix(function () {
            $('body,html').removeClass('modal-open active-request scrollable');
            /** Show remove modal */
            $('#action-book-modal').hide().removeClass('active');
        });
        return false;
    });

    /** Click copy to open copy book modal */
    $(document).on('click', '#copy-book-btn', function (e) {
        if ($(this).find('.more-menu-btn').hasClass('isDisabled')){
            return false;
        }
        var loader = $('.loader-list .loading-spinner');
        loader.show();
        $('html,body').animate({scrollTop: 0},'slow');
        let isbn = $(this).data('isbn');
        let action = 'copy';
        localStorage.setItem('action', action);
        localStorage.setItem('selectedIsbn', isbn);
        let bookRender = $(this).closest(".draggable")[0].innerHTML;
        localStorage.setItem('title', $(this).data('title'));
        localStorage.setItem('author', $(this).data('author'));
        localStorage.setItem('bookRender', bookRender);
        $.ajax({
            url: '/reading-list-copy-move-modal',
            method: 'get',
            data: {isbn: isbn, action: action},
            dataType: 'html',
            success: function (data) {
                if (data.length === 0 || data.length === 2) {
                    document.location.reload();
                } else {
                    /** Show copy modal */
                    let sanitizeData = DOMPurify.sanitize(data);
                    $('#action-book-modal-content').html(sanitizeData);
                    resetActionBookModal(action);
                    $('#action-book-modal').show().addClass('active');
                    modalFix(function () {
                        $('body,html').addClass('modal-open active-request scrollable');
                        if (window.innerWidth < 640) {
                            $('.modal-step-content').css('minHeight', $('#copy-book-modal').height() - 86);
                        }
                    });
                }
            },complete: function () {
                $('#action-book-modal .reading-list-item :checkbox').change(function () {
                    if ($('#action-book-modal .reading-list-item :checkbox:checked').length === 0) {
                        $('#copy-book-reading-list,#move-book-reading-list').addClass('btnDisabled');
                    } else {
                        $('#copy-book-reading-list,#move-book-reading-list').removeClass('btnDisabled');
                    }
                });
                loader.hide();
            }
        });
        disableCopyMove();
    });


    /** close copy modal */
    $(document).on('click', '.reading_list_modal #close-modal,#finish-copy-book', function (e) {
        e.preventDefault();
        let action = DOMPurify.sanitize(localStorage.getItem('action'));
        resetActionBookModal(action);
        modalFix(function () {
            $('body,html').removeClass('modal-open active-request scrollable');
            /** Show remove modal */
            $('#action-book-modal').hide().removeClass('active');
        });
        return false;
    });

    /** copy book after reading lists selection */
    $(document).on('click', '#copy-book-reading-list', function (e) {
        e.preventDefault();
        $('#copy-book-reading-list').prop('disabled', true);
        var checkedReadingList = $('#copy-move input:checked');
        var data = {'readingLists': []};
        var activeReadingList = $('.tabs-nav  li.active a').data('id');
        // localStorage.removeItem('readingListId');
        var listsCount = $('div .reading-list-item').find($("input")).length;
        if (checkedReadingList.length > 0) {
            $.each(checkedReadingList, function (i, elem) {
                data['readingLists'].push({'id': $(elem).val()});
            });
            data['isbn'] = DOMPurify.sanitize(localStorage.getItem('selectedIsbn'));
            data['existInAll'] = checkedReadingList.length === listsCount;
            //data['section'] = $('.tab-pane.active #book-item[data-isbn="' + data['isbn'] + '"]').parent().parent().parent().find('.input-section-name').val();
            let book = '';
            $.when($.ajax({
                url: 'reading-list-book-content',
                method: 'GET',
                data: {isbn: data['isbn'], readingListID: activeReadingList,existInAll:data['existInAll']},
                success: function (bookContent) {
                    book = bookContent;
                }
            })).then(function () {
                data['action'] = 'copy';
                data['bookTitle'] = DOMPurify.sanitize(localStorage.getItem('title'));
                data['listName'] = $('.tabs-nav  li.active .book-name').text();
                $.ajax({
                    url: '/add-book',
                    method: 'POST',
                    data: data,
                    beforeSend: function () {
                        $(this).addClass('loading');
                    },
                    success: function (response) {
                        if (response.success) {
                            $('.modal-step').removeClass('active');
                            /** Close modal and show notif */
                            closeModal($('#action-book-modal'));
                            $('#copy-remove-success .cookies-content').text($('#copy-book-msg').text());
                            $('.update-notif').show();
                            $('.update-notif').fadeOut(5000);
                            $(document).on('click', '.close-btn', function (e) {
                                e.preventDefault();
                                $(this).closest('.update-notif').remove();
                            });

                            localStorage.removeItem('selectedIsbn');
                            localStorage.setItem('readingListId', data['readingLists'][0].id);
                            /** increment number of books in readinglists navbar  */


                            if(data['existInAll']) {
                                $('div').find("#copy-book-btn[data-isbn='" + data['isbn'] + "'] a").addClass('isDisabled');
                                $('div').find("#move-book-btn[data-isbn='" + data['isbn'] + "'] a").addClass('isDisabled');
                                /*for(var i = 0 ; i<copybtn.length;i++) {
                                    copybtn[i].children[0].classList.add('isDisabled');
                                    movebtn[i].children[0].classList.add('isDisabled');
                                    //copybtn[i].id = '';
                                    //movebtn[i].id = '';
                                }*/
                            }
                            data['readingLists'].forEach(readingList => {
                                let currentNbr = $('div').find("[data-id='" + readingList.id + "']").children().children().eq(1).text().match(/\d+/)[0];
                                let replaced = $('div').find("[data-id='" + readingList.id + "']").children().children().eq(1).text().replace(/[0-9]+/, parseInt(currentNbr) + 1);
                                $('div').find("[data-id='" + readingList.id + "']").children().children().eq(1).text(replaced);
                                let  sanitizeReadingList = DOMPurify.sanitize(readingList.id);
                                let dropzone = $("div[data-title='" + sanitizeReadingList + "']").first();
                                if (parseInt(currentNbr) === 0) {
                                    var tabPane= $('.tab-pane#'+sanitizeReadingList);
                                    tabPane.find('.list_reading_books.have-books').show();
                                    tabPane.find('.list_reading_books.no-books').hide();
                                    tabPane.find('li.export-list .export-list-btn').parent().show();
                                    tabPane.find('.add-section-bloc').show();
                                    tabPane.find('.new-section-bloc').show();
                                    //tabPane.find('.new-section-bloc .input-section-name').val( data['section']);
                                    if(dropzone.length > 0) {
                                        if (dropzone[0].className.indexOf("empty-zone") !== -1) {
                                            dropzone[0].classList.remove('empty-zone');
                                        }
                                    }
                                    dropzone.html(book);
                                } else {
                                    dropzone.append(book);
                                }

                            });
                            initDragAndDrop();
                        }
                    }
                });
            });

        }
    });

    /** Set Continue button at the bottom of the modal for mobile */
    if (window.innerWidth < 640) {
        $('.modal-step-content').css('minHeight', $('.export-books-modal').height() - 86);
        $('.modal-step-content').css('minHeight', $('#remove-book-modal').height() - 86);
        $('.modal-step-content.finish-remove').css('minHeight', $('#remove-book-modal').height() - 130);
    }


    /** Click remove to open remove book modal */
    $(document).on('click', '#remove-btn', function () {
        let isbn = $(this).data('isbn');
        let readingListID = $('.tabs-nav  li.active a').data('id');
        localStorage.setItem('selectedIsbn', isbn);
        localStorage.setItem('reading-list-id', readingListID);
        localStorage.setItem('title', $(this).attr('data-title'));
        resetRemoveBookModal();
        /** Show remove modal */
        $('#remove-book-modal').show().addClass('active');
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
    });

    /** remove book from reading list Ajax-Request */
    $(document).on('click', '#remove-book-reading-list', function (e) {
        e.preventDefault();
        let option = $('input[name="remove-option"]:checked').val();
        let isbn = DOMPurify.sanitize(localStorage.getItem('selectedIsbn'));
        let readingListID = DOMPurify.sanitize(localStorage.getItem('reading-list-id'));
        let bookTitle = DOMPurify.sanitize(localStorage.getItem('title'));
        var loader = $('.loader-list-modale .loading-spinner');
        loader.show();
        $.ajax({
            url: '/reading-list-remove-book',
            method: 'GET',
            data: {isbn: isbn, readingListID: readingListID, option: option, bookTitle: bookTitle},
            success: function (resp) {
                if (resp.Result === true) {
                    e.preventDefault();
                    $('.tab-pane.active #book-item[data-isbn="' + isbn + '"]')[0].style.display = 'none';
                    $('.tab-pane.active #book-item[data-isbn="' + isbn + '"]')[0].remove();
                    let parent = $('div').find("[data-isbn='" + isbn + "']").parent();
                    let bookElement = $('div').find("[data-id='" + readingListID + "']").children().children().eq(1);
                    /** add style if drop zone category is empty */
                    if (parent.children().length === 0) {
                        parent.addClass('empty-zone');
                        let sanitizeAppend =  DOMPurify.sanitize($('.append'));
                        parent.append(sanitizeAppend);
                        $('.append').css('display', 'block');
                    }


                    /** active copy and remove buttons */
                    $('div').find("[title=''][data-isbn='" + isbn + "']").removeClass('isDisabled');
                    $('div').find("[title=''][data-isbn='" + isbn + "'] a").removeClass('isDisabled');

                    if (parseInt(option) === 0) {
                        /** get current number of books and decrease it  */
                        let currentNbr = bookElement.text().match(/\d+/)[0];
                        let replaced = bookElement.text().replace(/[0-9]+/, currentNbr - 1);
                        bookElement.text(replaced);
                        if (currentNbr-1 === 0) {
                            var tabPane= $('.tab-pane#'+readingListID);
                            tabPane.find('.list_reading_books.have-books').hide();
                            tabPane.find('li.export-list .export-list-btn').parent().hide();
                            tabPane.find('.add-section-bloc, .new-section-bloc, .NewSection').hide();
                            tabPane.find('.list_reading_books.no-books, .moved-bloc').show();
                        }
                    } else if (parseInt(option) === 1) {
                        resp.Data.forEach(readingList => {
                            let currentNbr = $('div').find("[data-id='" + readingList + "']").children().children().eq(1).text().match(/\d+/)[0];
                            let replaced = $('div').find("[data-id='" + readingList + "']").children().children().eq(1).text().replace(/[0-9]+/, currentNbr - 1);
                            $('div').find("[data-id='" + readingList + "']").children().children().eq(1).text(replaced);
                            if(parseInt(currentNbr) === 1) {
                                $('div').find(".tab-pane#"+ readingList).find('.list_reading_books.have-books').hide();
                                $('div').find(".tab-pane#"+ readingList).find('li.export-list .export-list-btn').parent().hide();
                                $('div').find(".tab-pane#"+ readingList).find('.add-section-bloc, .new-section-bloc, .NewSection').hide();
                                $('div').find(".tab-pane#"+ readingList).find('.list_reading_books.no-books, .moved-bloc').show();
                            }
                            //remove book from all reading list
                            $.each( $('.tab-pane #book-item[data-isbn="' + isbn + '"]'), function (i, e) {
                                $(this).css('display','none');
                                $(this).remove();
                            });
                            $.each( $('.have-books'), function (i, e) {
                                if($(this).find('.new-section-bloc').length === 1 && $(this).find('.book_item').length === 0) {
                                    $(this).css('display','none');
                                }
                            });

                        });

                    }

                    /** Hide first step and display the second  */
                    $('#step1').removeClass('active');
                    $('#close-modal').hide();
                    $('#cancel-remove').hide();
                    $('#step2').addClass('active');
                    resetDropZones();
                } else {
                    //window.location = document.location.origin;
                }
                loader.hide();
            }
        });
        disableCopyMove();
    });

    /** Submit and move to step 3 */
    $(document).on('click', '#browse_btn', function (e) {
        e.preventDefault();
        $('.modal-step').removeClass('active');
        $('#step3').addClass('active');
    });


    /** choose remove book reason & close modal */
    $(document).on('click', '.reading_list_modal #close-modal,#cancel-remove,#remove-book-reading-list-reason,#skip-btn', function (e) {
        e.preventDefault();
        resetRemoveBookModal();
        modalFix(function () {
            $('body,html').removeClass('modal-open active-request scrollable');
            /** Show remove modal */
            $('#remove-book-modal').hide().removeClass('active');
        });
        return false;
    });


    /** Reset copy or move book modal */
    function resetActionBookModal(action) {
        let firstStep = $('#' + action + '-step1');
        $('.modal-step').removeClass('active');
        firstStep.addClass('active');
        $("#reading-lists-check-container .multi_institutions").prop("checked", false);
    }

    /** Reset remove book modal */
    function resetRemoveBookModal() {
        $('#close-modal').show();
        $('#cancel-remove').show();
        let firstStep = $('#step1');
        $('.modal-step').removeClass('active');
        firstStep.addClass('active');
        $("#remove-book-modal .reason").prop("checked", false);
        $("#remove-book-modal #remove-one").prop("checked", true);
        $("#advanced").prop("checked", true);
        /** clear textarea for other reason */
        $('#other_reason_block').css('display', 'none');
        $('#other_reason_block textarea').val('');
    }

    /** show text area when other reason is selected */
    $('body').on('change', 'input:radio[name="scales"]', function () {
        if (this.checked && this.id === 'other_reason') {
            $('#other_reason_block').css('display', 'block');
        } else {
            $('#other_reason_block').css('display', 'none');
        }
    });

    if (isMobile()) {
        $('.tabs-content').hide();
        $('.see-menu-btn').hide();
        $('.export-list.mobile').hide();
        $(document).on('click', '.tabs-nav a', function (e) {
            $('.tabs-content').show();
            $('.tabs-nav').hide();
            $('.see-menu-btn').show();
            $('.export-list.mobile').show();
            $('html,body').animate({scrollTop: 0},'slow');
        });
        $('.see-menu-btn').click(function () {
            $('.tabs-nav').show();
            $('.tabs-content').hide();
            $('.see-menu-btn').hide();
            $('.export-list.mobile').hide();
        });
    }
    if (!isMobile()) {
        $('.tabs-nav a').click(function () {
            window.scrollTo(0, 0);
            $('html,body').animate({scrollTop: 0},'slow');
        });
    }
    /** Init pending tooltip */
    $('[data-toggle-pending="tooltip"]').tooltip({trigger: "hover"});

    /** Click on three dot menu to show option list */
    $(document).on('click', '#more-btn', function (e) {
        e.preventDefault();
        //hide all visible menu
        $('.more').removeClass('show-more-menu');
        if ($(this).parent().find('.more-menu').is(':visible'))
            hideMenu($(this));
        else {
            showMenu($(this));
            e.stopPropagation();
        }
    });

    /** Click anywhere from 3 dot menu to close it */
    $(document).on('click', function () {
        var moreMenus = $('.list_reading_books, .reading-info-container').find('.more-menu');
        $.each(moreMenus, function (i, e) {
            if ($(e).is(':visible')) {
                hideMenu($(e));
            }
        });
    });

    /** Show 3 dot menu */
    function showMenu(el) {
        el.parent().addClass('show-more-menu');
        el.attr('aria-hidden', false);
    }

    /** Hide 3 dot menu */
    function hideMenu(el) {
        el.parent().removeClass('show-more-menu');
        el.attr('aria-hidden', true);
    }


    /** Hide not mobile options from 3 dot menu */
    if (!isMobile())
        $('.more-menu-item.for-mobile').remove();

    /** Open export books modal */
    $(document).on('click', '#export-list', function (e) {
        e.preventDefault();
        var readingListId = $(this).data('reading-list-id');
        var containers = [{'.approved-container': '#' + readingListId + ' .book_item'}];
        var loader = $('#formExportBooks .loading-spinner');
        loader.show();
        localStorage.setItem('readingListId', readingListId);
        /** Loop through books displayed in the page */
        setTimeout(function () {
            $.each(containers, function () {
                $.each(this, function (container, filter) {
                    $(filter).each(function (i, obj) {
                        var url = $(obj).find('.imgBlock').attr('href'),
                            parts = url.split("/"),
                            isbn = parts[parts.length - 1],
                            id_book = uniqueID();
                        $('.books-filtered').append(DOMPurify.sanitize('<li class="list-books"> <div class="small-12 medium-12 large-12  checkbox__item last-check-box undefined books-details"> <input id="' + id_book + '" type="checkbox" class="checkbox__input" data-isbn="' + isbn + '"> <label for="' + id_book + '" class="checlbox__label small-checkbox ">' + obj.innerHTML + '</label></div></li>'));
                        /** replace tag a with span */
                        $(".books-filtered .list-books").each(function () {
                            let imgBlock =  DOMPurify.sanitize($(this).find(".imgBlock").html());
                            let title = DOMPurify.sanitize($(this).find(".title-book").html());
                            let author =  DOMPurify.sanitize($(this).find(".author-name").html());
                            $(this).find(".imgBlock").replaceWith('<span class="imgBlock">' + imgBlock+ "</span>");
                            $(this).find(".title-book").replaceWith('<span class="title-book">' + title + "</span>");
                            $(this).find(".author-name").replaceWith('<div class="author-name">' + author + "</div>");
                        });
                        /** Remove the non showed information */
                        $('.books-filtered .bookStatus').remove();
                        $('.books-filtered .ancillary-block').remove();
                        $('.books-filtered .sharing').remove();
                        $('input:checkbox').prop('checked', true);
                    });
                });
            });
            $('.list-books :checkbox').change(function () {
                if ($('.list-books :checkbox:checked').length === 0) {
                    $('#export-books').addClass('btnDisabled');
                    $('#select-all').trigger('click');
                } else {
                    $('#export-books').removeClass('btnDisabled');
                }
            });
            $('#select-all').change(function () {
                if ($('.list-books :checkbox:checked').length === 0) {
                    $('#export-books').addClass('btnDisabled');
                } else {
                    $('#export-books').removeClass('btnDisabled');
                }
            });
            loader.hide();
        });
        /** Show export books modal */
        $('.export-books-modal').show().addClass('active');
        $('#select-deselect').text($('#select-all').data('deselect-all'));
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('#export-books').text($('#continue-btn').text());
        $('.export-books-modal .close').show();
    });

    function closeExportModal() {
        $('.export-books-modal').hide();
        $('.books-filtered').html("");
        $('input:checkbox').prop('checked', false);
        localStorage.removeItem('readingListId');
    }

    /** Clear export modal when click on close */
    $('.export-books-modal .close').on('click', function () {
        $('#export-books').removeClass('loading');
        closeExportModal();
    });

    /** Select and deselect books in export modal */
    $('#select-all').on('click', function () {
        if ($('#select-all').is(':checked')) {
            $('input:checkbox').prop('checked', true);
            $('#select-deselect').text($(this).data('deselect-all'));
        } else {
            $('input:checkbox').prop('checked', false);
            $('#select-deselect').text($(this).data('select-all'));
        }

    });

    /** Export reading list */
    $('#export-books').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('btnDisabled')) {
            return false;
        }
        var booksData = [];
        var today = new Date();
        var day = today.getDate();
        var monthIndex = today.getMonth()+1;
        var year = today.getFullYear();
        var todayDate = day + '/' + monthIndex + '/' + (year);
        var box = $('.box');
        var checkboxes = $('input[type=checkbox]:checked');
        var exportBooks = $('#export-books');
        var fileTitle = $('#book-list-export').text();
        if (checkboxes.length === 0) {
            var errorText = $(this).data('error-text');
            box.find('.error-msg').text(errorText);
            box.attr('class', 'box active error-info');
            box.on('click', '#box-ok', function () {
                box.removeClass('active error-info');
                box.off('click');
            });
        } else {
            exportBooks.addClass('loading');
            var listId = DOMPurify.sanitize(localStorage.getItem('readingListId'));
            checkboxes.each(function () {
                var bookIsbn = $(this).data('isbn');
                var book = {
                    isbn: bookIsbn
                };
                booksData.push(book);
            });
            $.ajax({
                url: $('#export-list').data('url'),
                method: 'POST',
                data: {
                    books: booksData,
                    readingListId: listId
                },
                success: function (data) {
                    let encodedData = DOMPurify.sanitize(JSON.stringify(data));
                    var sanitizeData = JSON.parse(encodedData);
                    let fileInfo = sanitizeData.file;
                    var $a = $("<a>");
                    var base64regex = /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/;
                    if(base64regex.test(fileInfo)) {
                        $a.attr("href", 'data:application/vnd.ms-word;base64,'+fileInfo);
                        $("body").append($a);
                        $a.attr("download", fileTitle+" (" + todayDate + ").docx");
                        $a[0].click();
                        $a.remove();
                        exportBooks.removeClass('loading');
                        closeModal($('.export-books-modal'));
                        $('.export-books-modal .close').on('click');
                        closeExportModal();
                    }
                },
                error: function () {
                   document.location.reload();
                }
            });
        }
    });


    /** draggable area ***/
    initDragAndDrop();

    function initDragAndDrop() {
        // Collect all draggable elements and drop zones
        let draggables = document.querySelectorAll(".draggable");
        let dropZones = document.querySelectorAll(".drop-zone");
        initDraggables(draggables);
        initDropZones(dropZones);
    }

    function initDraggables(draggables) {
        for (const draggable of draggables) {
            initDraggable(draggable);
        }
    }

    function initDropZones(dropZones) {
        for (let dropZone of dropZones) {
            initDropZone(dropZone);
        }
    }

    /**
     * Set all event listeners for draggable element
     * https://developer.mozilla.org/en-US/docs/Web/API/DragEvent#Event_types
     */
    function initDraggable(draggable) {
        draggable.addEventListener("dragstart", dragStartHandler);
        draggable.addEventListener("drag", dragHandler);
        draggable.addEventListener("dragend", dragEndHandler);

        // set draggable elements to draggable
        draggable.setAttribute("draggable", "true");
    }

    /**
     * Set all event listeners for drop zone
     * https://developer.mozilla.org/en-US/docs/Web/API/DragEvent#Event_types
     */
    function initDropZone(dropZone) {
        dropZone.addEventListener("dragenter", dropZoneEnterHandler);
        dropZone.addEventListener("dragover", dropZoneOverHandler);
        dropZone.addEventListener("dragleave", dropZoneLeaveHandler);
        dropZone.addEventListener("drop", dropZoneDropHandler);
    }

    /**
     * Start of drag operation, highlight drop zones and mark dragged element
     * The drag feedback image will be generated after this function
     * https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#dragfeedback
     */
    function dragStartHandler(e) {
        setDropZonesHighlight();
        this.classList.add('dragged', 'drag-feedback');
        // we use these data during the drag operation to decide
        // if we handle this drag event or not
        e.dataTransfer.setData("type/dragged-box", 'dragged');
        e.dataTransfer.setData("text/plain", this.textContent.trim());
        deferredOriginChanges(this, 'drag-feedback');
    }

    /**
     * While dragging is active we can do something
     */
    function dragHandler() {
        // do something... if you want
    }

    /**
     * Very last step of the drag operation, remove all added highlights and others
     */
    function dragEndHandler() {
        setDropZonesHighlight(false);
        this.classList.remove('dragged');
        jQuery(".drop-zone").each(function (index, value) {
            for (var i = 0; i < value.children.length; i++) {
                if (value.children[i].className.indexOf("draggable") === -1) {
                    value.children[i].remove();
                }
            }
            if (value.children.length === 0) {
                value.classList.add('empty-zone');
                value.classList.add('placeholder');
            } else {
                value.classList.remove('empty-zone');
            }
        });
    }

    /**
     * When entering a drop zone check if it should be allowed to
     * drop an element here and highlight the zone if needed
     */
    function dropZoneEnterHandler(e) {
        // we can only check the data transfer type, not the value for security reasons
        // https://www.w3.org/TR/html51/editing.html#drag-data-store-mode
        if (e.dataTransfer.types.includes('type/dragged-box')) {
            this.classList.add("over-zone");
            // The default action of this event is to set the dropEffect to "none" this way
            // the drag operation would be disallowed here we need to prevent that
            // if we want to allow the dragged element to be drop here
            // https://developer.mozilla.org/en-US/docs/Web/API/Document/dragenter_event
            // https://developer.mozilla.org/en-US/docs/Web/API/DataTransfer/dropEffect
        }
    }

    /**
     * When moving inside a drop zone we can check if it should be
     * still allowed to drop an element here
     */
    function dropZoneOverHandler(e) {
        if (e.dataTransfer.types.includes('type/dragged-box')) {
            // The default action is similar as above, we need to prevent it
            e.preventDefault();
        }
    }

    /**
     * When we leave a drop zone we check if we should remove the highlight
     */
    function dropZoneLeaveHandler(e) {
        /*if (e.dataTransfer.types.includes('type/dragged-box') &&
            e.relatedTarget !== null &&
            e.currentTarget !== e.relatedTarget.closest('.drop-zone')) {
            this.classList.remove("over-zone");
        }*/
    }

    /**
     * On successful drop event, move the element
     */
    function dropZoneDropHandler(e) {
        // We have checked in the "dragover" handler (dropZoneOverHandler) if it is allowed
        // to drop here, so it should be ok to move the element without further checks
        let draggedElement = document.querySelector('.dragged');
        e.currentTarget.appendChild(draggedElement);
        let isbn = draggedElement.getAttribute('data-isbn');
        let readingListID = draggedElement.getAttribute('data-reading-list-id');
        let category = e.currentTarget.getAttribute('data-section-name');
        $.ajax({
            url: '/reading-list-book-category-update',
            method: 'put',
            data: {readingListID: readingListID, isbn: isbn, category: category},
            success: function (result) {

            }
        });

        // We  drop default action (eg. move selected text)
        // default actions detailed here:
        e.preventDefault();

    }


    /**
     * Highlight all drop zones or remove highlight
     */
    function setDropZonesHighlight(highlight = true) {
        const dropZones = document.querySelectorAll(".drop-zone");
        for (const dropZone of dropZones) {
            if (highlight) {
                dropZone.classList.add("active-zone");
            } else {
                dropZone.classList.remove("active-zone");
                dropZone.classList.remove("over-zone");
            }
        }
    }

    /**
     * After the drag feedback image has been generated we can remove the class we added
     * for the image generation and/or change the originally dragged element
     * https://javascript.info/settimeout-setinterval#zero-delay-settimeout
     */
    function deferredOriginChanges(origin, dragFeedbackClassName) {
        setTimeout(() => {
            origin.classList.remove(dragFeedbackClassName);
        });
    }
    //get the max list number
    var maxListNumber = 1;
    var defaultListName = $('#readingListName').attr('data-default-text');
    /** Click add reading list to open add reading list modal */
    $(document).on('click', '.btn-add-reading-list', function () {
        resetAddReadingListModal();
        /** Show request modal */
        $('.new_reading_list_modal').show().addClass('active');
        maxListNumber =  getMaxListNumber();
        $('#formAddReadingList #readingListName').val(defaultListName + ' ' + maxListNumber);
        modalFix(function () {
            $('body,html').addClass('modal-open active-request scrollable');
        });
        $('.new_reading_list_modal .close').show();
    });

    /** Reset add reading list modal */
    function resetAddReadingListModal() {
        $('.new_reading_list_modal .modal-step').removeClass('active');
        $('#formAddReadingList #step1').addClass('active');
        $('#formAddReadingList #courseName').val("");
        $('#formAddReadingList #courseType').val("");
        $('#formAddReadingList #courseLevel').val("").trigger('change');
        $('#formAddReadingList #studentsNumber').val("");
        $('#formAddReadingList #startDate').datepicker('setDate', null);
        $('#formAddReadingList #institutions-selector option.my-primary').prop('selected', true).trigger('change');
        $('.modal-step .error').remove();
        $('.modal-step .warning').removeClass('warning');
    }

    function getMaxListNumber() {
        if($('.book-name').length > 0) {
            $.each($('.book-name'), function (i, elem) {
                var listName = $(this).text().split(' ');
                var lastChar = listName[listName.length - 1];
                if (parseInt(lastChar)) {
                    if(maxListNumber <= parseInt(lastChar)) {
                        listName.pop();
                        var newListName = $.trim(listName.join(' '));
                        if (newListName === defaultListName) {
                            maxListNumber = parseInt(lastChar)+1;
                        }
                    }
                }
            });
        }
        return maxListNumber;
    }
    /** Click close book request */
    $(document).on('click', '.new_reading_list_modal #close-modal', function (e) {
        e.preventDefault();
        box.find('#box-confirm').text($('#box-msg-yes').text());
        box.find('#box-cancel').text($('#box-msg-no').text());
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
                //scroll
                window.scrollTo(0, 0);
                $('html,body').animate({scrollTop: 0},'slow');
            });
        }
    });

    /** Go next request step */
    $(document).on('click', '.new_reading_list_modal .goStep:not(.loading)', function (e) {
        e.preventDefault();
        var nextStep = $(this).data('target-step');
        readingList.readingListName = $('#formAddReadingList #readingListName').val();
        $('#formAddReadingList .error').remove();
        //make all button copy and move enabled
        $('a.more-menu-btn.isDisabled').removeClass('isDisabled');
        if (readingList.readingListName.length >0) {
            readingList.readingListName = $('#formAddReadingList #readingListName').val();
            sendListData(readingList);
            $(this).closest('.modal-step').removeClass('active');
            $('#formAddReadingList #' + nextStep).addClass('active');
        } else {
            //show warning
            let title = sanitizeHTML($('#formAddReadingList #readingListName').prop('title'));
            let error = DOMPurify.sanitize('<span class="error">' + title + '</span>');
            $('#formAddReadingList #readingListName').addClass('warning')
                .parent().append(error);
        }
        disableCopyMove();
    });

    /** Submit book request validate & send data*/
    function sendListData(readingList) {
        //get current date
        var today = new Date();
        submitReadingList(readingList, function (success, readingList, response) {
            let tooltipTitle = $('#tooltip-more').val();
            var noListsText = $('#no-books-reading-list').text();
            var seeHistory =  $(".see-history-text").text();
            var removeList = $('#remove-list').text();
            var exportList = $('#export-list').text();
            var exportUrl = $('#export-url').text();
            var editList  = $('.edit_list').text();
            if (success) {
                var tabNav = $('.tabs-nav ul');
                var readingListContent = $('#reading-list-content');
                $('#formAddReadingList #step2').removeClass('active');
                $('#formAddReadingList #step3').addClass('active');
                var lastUpdate = today.getDate()+' '+(today.toLocaleString('en-us', { month: 'short' }))+' '+today.getFullYear()+', '+ (today.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true }));
                tabNav.find('li').removeClass('active');
                tabNav.append(DOMPurify.sanitize('<li class="active">' +
                    '                                <a href="#' + response.id + '" data-toggle="tab" data-id=' + response.id + '>' +
                    '                                    <div class="book-info">' +
                    '                                        <div class="book-name"> ' + readingList.readingListName + '</div>' +
                    '                                        <div class="nbr-books" data-id="">0 ' + $('#books-tab').text() + '</div>' +
                    '                                    </div>' +
                    '                                </a>' +
                    '                            </li>'));
                if (readingListContent.length === 0) {
                    $('.reading-list-container').append(DOMPurify.sanitize(' <div class="reading-list tabs-content" id="reading-list-content">'));
                    readingListContent = $('#reading-list-content');
                }
                readingListContent.find('.tab-pane').removeClass('active');
                readingListContent.append(DOMPurify.sanitize('<div class="tab-pane active" id="' + response.id + '" \n' +
                    '<div id="tab1" class="tabs_container">\n' +
                    '        <div class="reading-info nexusSans">\n' +
                    '        <div class="reading-info-container row">\n' +
                    '           <div class="reading-list-name small-12 medium-6 large-6 columns pr-0">\n' +
                    '                        <div class="">\n' +
                    '                                 <input type="text" class="input-list-name input-section-name readonly" value="'+ readingList.readingListName +'" readonly maxlength="50">\n' +
                    '                        </div>\n' +
                    '                        <div class="update-actions hidden"><span class="save-update"><div class="check"></div></span><span class="cancel-update">\n' +
                    '                                    <div class="close_section"></div>\n' +
                    '                                </span>\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '<div class="right_block small-12 large-6 medium-6 pr-0 pl-0 columns">\n' +
                    '<div class="small-12 medium-2 large-2 columns sharing_menu"><div class="sharing sharing_header">\n' +
                    '                                <div class="container">\n' +
                    '                                    <div class="more">\n' +
                    '                                        <button id="more-btn" class="more-btn" data-toggle="tooltip" data-placement="bottom"\n' +
                    '                                                title="'+tooltipTitle+'">\n' +
                    '                                            <span class="more-dot"></span>\n' +
                    '                                            <span class="more-dot"></span>\n' +
                    '                                            <span class="more-dot"></span>\n' +
                    '                                        </button>\n' +
                    '                                        <div class="more-menu">\n' +
                    '                                            <ul class="more-menu-items" tabindex="-1" role="menu"\n' +
                    '                                                aria-labelledby="more-btn" aria-hidden="true">\n' +
                    '                                           <li class="more-menu-item export-list"  style="display: none" >\n' +
                    '                                                        <a class="btn-blue export-list-btn" id="export-list"\n' +
                    '                                                           data-url="'+exportUrl+'"\n' +
                    '                                                           data-reading-list-id="'+ response.id +'">'+exportList+'</a>\n' +
                    '                                                    </li>\n' +
                    '                                                   <li class="more-menu-item edit-list-name-item" id="edit-list" > <div>\n' +
                    '                                                        <a href="javascript:;" class="more-menu-btn edit-list-name"\n' +
                    '                                                            role="presentation">'+editList+'</a></div>\n' +
                    '                                                    </li>\n' +
                    '                                                <li class="more-menu-item export-list"\n' +
                    '                                                    role="">\n' +
                    '                                                    <div class="read-now-btn" id="remove-list-btn"\n' +
                    '                                                         data-reading-list-id="'+ response.id +'">\n' +
                    '                                                        <a href="javascript:;"\n' +
                    '                                                           class="more-menu-btn"\n' +
                    '                                                           role="menuitem">'+ removeList +'</a>\n' +
                    '                                                    </div>\n' +
                    '                                                </li>\n' +
                    '                                            </ul>\n' +
                    '                                        </div>\n' +
                    '                                    </div>\n' +
                    '                                </div>\n' +
                    '                            </div></div>\n' +
                    ' <div class="date_container"><div class="date"> Last updated  '+lastUpdate+ '</div> ' +
                    '<div class="link-history"> <a href="javascript:;" class="see-history" ' +
                    'data-list-id="'+ response.id+'" data-list-name="'+readingList.readingListName+'">'+seeHistory+'</a> ' +
                    '</div> </div></div></div></div></div></div>'));
                if ( $('.tabs-nav li').length === 1 ){
                    $('.reading-list-container .list_reading_books').hide();
                }
                var imgIcon =$('.edit-section-name img').attr('src');
                var initialSectionName = $('.initial-section-name').text();
                var imgIconDelete = DOMPurify.sanitize($('.delete-section-btn img').attr('src'));
                var titleEdit = $('.edit-section-name img').attr('data-original-title');
                var titleDelete = $('.delete-section-btn img').attr('data-original-title');
                var addSection = $('.add-section-text').text();
                var noSectionText = DOMPurify.sanitize($('.no-section').html());
                let responseId = sanitizeHTML(response.id);
                let newList = '<div class="list_reading_books have-books" style="display: none">' +
                    '<a href="javascript:;" class="delete-section-btn"> <img src="'+imgIconDelete+'" srcset="'+baseUrl+'/assets/img/icon-delete/icon_delete@2x.png?v=2.5.3 2x,'+baseUrl+'/assets/img/icon-delete/icon_delete@3x.png?v=2.5.3 3x" class="icon_edit" title="'+titleDelete+'"' +
                    ' data-original-title="'+titleDelete+'" data-toggle="tooltip" data-placement="bottom"></a>' +
                    '<div class="book_type text-center section_name"> ' +
                    '<div class="new-section-bloc"> ' +
                    '<input type="text" class="input-section-name readonly" style="width:auto" value="'+initialSectionName+'" readonly maxlength="65"> ' +
                    '<a href="javascript:;" class="edit-section-name"> ' +
                    '<img src="'+imgIcon+'" srcset="'+baseUrl+'/assets/img/icon-edit/icon_edit@2x.jpg?v=2.5.3 2x,'+baseUrl+'/assets/img/icon-edit/icon_edit@3x.jpg?v=2.5.3 3x" class="icon_edit" title="'+titleEdit+'" data-original-title="'+titleEdit+'" data-toggle="tooltip" data-placement="bottom"></a> ' +
                    '</div> ' +
                    '<div class="update-actions hidden"><span class="save-update"><div class="check"></div></span><span class="cancel-update"> ' +
                    '<div class="close_section"></div> ' +
                    '</span> </div> </div>' +
                    '<div class="books_list">'+
                    '<div class="drop-zone empty-zone" data-title="'+ responseId +'">'+
                    '<div class="drag-placeholder nexusSans" id="placeholder_drag">'+noSectionText+'</div>'+
                    '</div></div></div>' +
                    '<div class="text-center mt-24 add-section-bloc" style="display: none;">\n' +
                    '                    <span class="add-new-section" style="cursor: pointer">'+addSection+'</span>\n' +
                    '                </div>'+

                    '<div class="list_reading_books no-books" id="'+ responseId +'">\n' +
                    '                    <div class="text-center no-list-books nexusSans">\n' +
                    '                        <div class="empty_book-img" id="'+ responseId +'" >\n' +
                    '                            <img class="retina-reload" src="'+baseUrl+'/assets/img/empty_book/book_empty@1x.png"\n' +
                    '                                 style="height: 152px;width: 152px;">\n' +
                    '                        </div>\n' +
                    '                        <div class="empty_book-msg">\n' +
                    '                           <p>\n' +  noListsText +
                    '                           </p>\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                </div>';
                var cleanList = DOMPurify.sanitize(newList);
                $('.tab-pane.active').append(cleanList);
                //init input
                resizeInput($('#reading-list-content .input-section-name'));
            }
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
            if (isMobile()) {
                setTimeout(function () {$('li.active a').trigger('click');});
            }
            window.scrollTo(0,0);
            $('html,body').animate({scrollTop: 0},'slow');
        });
        disableCopyMove();
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
                /** active copy and remove buttons */
                let copybtn =  $('div').find("[title='copybtn']");
                let movebtn =  $('div').find("[title='movebtn']");
                for(var i = 0 ; i < copybtn.length; i++) {
                    copybtn[i].id = 'copy-book-btn';
                    movebtn[i].id = 'move-book-btn';
                    copybtn[i].children[0].classList.remove('isDisabled');
                    movebtn[i].children[0].classList.remove('isDisabled');

                }
                callback(response.success, readingList, response);
                initPopover();
                closePopover();
            },
            error: function (httpObj) {
                submitBtn.removeClass('loading');
                if (httpObj.status === 401)
                    document.location.reload();
            }
        });
    }
    $(document).on('click','.delete-section-btn', function () {
        removeButton = true;
        var box = $('.box');
        box.find('#box-confirm').text($('#btn-confirm').text());
        box.find('#box-cancel').text($('#cancel-btn').text());
        box.attr('class', 'box active confirm-info');
        box.find('.confirm-msg').text($('.remove-section-alert').text());
        box.find('#box-cancel').css('display', '');
        var btnDelete = $(this);
        box.on('click', '#box-confirm', function () {
            box.removeClass('active error-info');
            box.off('click');
            var listId = $('.tab-pane.active').attr('id');
            if(btnDelete.closest('.list_reading_books').find('.book_item').length > 0) {
                $.each(btnDelete.closest('.list_reading_books').find('.book_item'), function (i, elem) {
                    //remove Book from list
                    var data = {}
                    data['isbn'] = DOMPurify.sanitize($(this).attr('data-isbn'));
                    data['title'] = $(this).find('.prodTitle').data("book-title");
                    removeBook(data,listId);
                });
                //activate all copy and move button
                $('a.more-menu-btn.isDisabled').removeClass('isDisabled');
            }

            //section without books
            btnDelete.closest('.list_reading_books').remove();
            btnDelete.parent().remove();
            if($('#reading-list-content .tab-pane.active input').length == 0) {
                //show empty block
                $('#reading-list-content .tab-pane.active .no-books').show();
                $('#reading-list-content .tab-pane.active .add-new-section').hide();
            }
        });
        box.on('click', '#box-cancel', function () {
            box.removeClass('active error-info');
            box.off('click');
        });
    });

    // show and hide tooltip on mobile
    if (isMobile()) {
        $('[data-toggle="tooltip"]').on('touchstart', function () {
            $(this).closest('.book_item.draggable').attr('draggable', '');
        });
        $('.book_item.draggable').on('click', function () {
            $('.book_item.draggable').attr('draggable', 'true');
            $('[data-toggle="tooltip"]').tooltip('hide');
        });
        $('.student_ressources,.instrutor_ressources').on('click', function (event) {
            event.stopPropagation();
        });
    }
    var textInput = '';
    var textInputListName ='';
    $(document).on('click', '.edit-section-name', function (e) {
        if ( removeButton === true ) {
            removeButton = false;
        } else {
            if($(this).parent().find('.input-section-name').hasClass('readonly')) {
                $(this).parent().find('.input-section-name').removeAttr('readonly');
                $(this).parent().find('.input-section-name').removeClass('readonly');
                $(this).addClass('hidden');
                $(this).parent().parent().find('.update-actions').removeClass('hidden');
                $(this).parent().parent().find('.book_type.section_name').addClass('edited');
            } else {
                $(this).parent().find('.input-section-name').attr('readonly', 'readonly');
                $(this).parent().find('.input-section-name').addClass('readonly');
                $(this).removeClass('hidden');
                $(this).parent().parent().find('.update-actions').addClass('hidden');
                $(this).parent().parent().find('.book_type.section_name').removeClass('edited');
            }
            textInput = $(this).parent().find('.input-section-name').val();
        }

    });

    $(document).on('click', '.section_name .save-update', function (e) {
        $(this).parent().parent().find('.input-section-name').attr('readonly', 'readonly');
        $(this).parent().parent().find('.input-section-name').addClass('readonly');
        $(this).parent().parent().find('.edit-section-name').removeClass('hidden');
        $(this).parents().eq(5).find('.book_type.section_name').removeClass('edited');
        $(this).parent().parent().find('.update-actions').addClass('hidden');
        var newSectionName = $(this).parent().parent().find('.input-section-name').val();
        $(this).parent().parent().parent().find('.drop-zone').attr('data-section-name',newSectionName);
        //get all books inside section and update category
        $.each($(this).parents().eq(5).find('.book_item'), function (i, elem) {
            var isbn = DOMPurify.sanitize($(this).attr('data-isbn'));
            var listId = $(this).attr('data-reading-list-id');
            $.ajax({
                url: '/reading-list-book-category-update',
                method: 'put',
                data: {readingListID: listId, isbn: isbn, category: newSectionName},
                success: function (result) {

                }
            });
        });

        resizeInput($('#reading-list-content .input-section-name'));
        //call ajax to save section (to do)

    });
    $(document).on('click', '.section_name .cancel-update', function (e) {
        var container =  $(this).parent().parent();
        container.find('.input-section-name').attr('readonly', 'readonly');
        container.find('.input-section-name').addClass('readonly');
        container.find('.edit-section-name').removeClass('hidden');
        container.find('.update-actions').addClass('hidden');
        $(this).parents().eq(5).find('.book_type.section_name').removeClass('edited');
        container.find('.input-section-name').val(textInput);
        resizeInput($('#reading-list-content .input-section-name'));
    });
    function resizeInput($elem) {
        $.each($elem, function (i, elem) {
            this.style.width = this.value.length + "ch";
        });
    }
    //add new section block
    $(document).on('click','.add-new-section', function () {
        var defaultSectionName = DOMPurify.sanitize($('.default-section-name').text());
        var noSectionText = DOMPurify.sanitize($('.no-section').html());
        var imgIconDelete = DOMPurify.sanitize($('.delete-section-btn img').attr('src'));
        var id = defaultSectionName;
        var editTooltip= DOMPurify.sanitize($('#tooltip-edit').text());
        var moreTooltip= DOMPurify.sanitize($('#tooltip-more').text());
        var removeTooltip= DOMPurify.sanitize($('#tooltip-remove').text());
        var titleDelete = DOMPurify.sanitize($('.delete-section-btn img').attr('data-original-title'));
        var htmlSection = ' <div class="list_reading_books">' +
            '<a href="javascript:;" class="delete-section-btn"> <img src="'+imgIconDelete+'" srcset="'+baseUrl+'/assets/img/icon-delete/icon_delete@2x.png?v=2.5.3 2x,'+baseUrl+'/assets/img/icon-delete/icon_delete@3x.png?v=2.5.3 3x"class="icon_edit" title="'+titleDelete+'" data-original-title="'+titleDelete+'" data-toggle="tooltip" data-placement="bottom"></a>' +
            ' <div class="book_type new-section text-center section_name '+id+'"> ' +
            '<div class="new-section-bloc">' +
            '<div class="input-container">'+
            '<input type="text" class="input-section-name readonly" value="'+defaultSectionName+'" readonly maxlength="65">'+
            '<div class="update-actions hidden"><span class="save-update"><div class="check"></div></span><span class="cancel-update"><div class="close_section"></div></span></div>'+
            '</div>' +
            '  <div class="sharing edit-section-name">\n' +
            '                                        <div class="container">\n' +
            '                                            <div class="more">\n' +
            '                                                <button id="more-btn" class="more-btn" data-toggle="tooltip" data-placement="bottom"\n' +
            '                                                        title="'+moreTooltip+'">\n' +
            '                                                    <span class="more-dot"></span>\n' +
            '                                                    <span class="more-dot"></span>\n' +
            '                                                    <span class="more-dot"></span>\n' +
            '                                                </button>\n' +
            '                                                <div class="more-menu">\n' +
            '                                                    <ul class="more-menu-items" tabindex="-1" role="menu"\n' +
            '                                                        aria-labelledby="more-btn" aria-hidden="true">\n' +
            '                                                        <li class="more-menu-item"\n' +
            '                                                            role="presentation">\n' +
            '                                                            <div>\n' +
            '                                                                <a href="javascript:;" class="more-menu-btn edit-section-name"\n' +
            '                                                                   role="menuitem">'+editTooltip+'</a>\n' +
            '                                                            </div>\n' +
            '                                                        </li>\n' +
            '                                                        <li class="more-menu-item"\n' +
            '                                                            role="presentation">\n' +
            '                                                            <div>\n' +
            '                                                                <a href="javascript:;" class="more-menu-btn delete-section-btn" role="menuitem">'+removeTooltip+'</a>\n' +
            '                                                            </div>\n' +
            '                                                        </li>\n' +
            '                                                    </ul>\n' +
            '                                                </div>\n' +
            '                                            </div>\n' +
            '                                        </div>\n' +
            '                                    </div>'+
            '</div> ' +
            '<div class="books_list"> <div class="drop-zone empty-zone" id="'+id+'" data-section-name="'+id+'"> <div class="drag-placeholder nexusSans" id="placeholder_drag">'+noSectionText+'</div> </div></div> ' +
            '</div>';
        $(this).before(DOMPurify.sanitize(htmlSection));
        resetDropZones();
        initDragAndDrop();
        resizeInput($('#reading-list-content .input-section-name'));
        $('[data-toggle="tooltip"]').tooltip();
        initPopover();
        closePopover();
    });

    $(document).on('click', '.see-history', function (e) {
        //get data with js
        var listId = $(this).data('list-id');
        var listName = $(this).data('list-name');
        $('.history-modal').remove();
        $.ajax({
            url: '/get-list-history',
            method: 'post',
            data: {readingListId: listId, ListName: listName},
            dataType: 'html',
            success: function (data) {
                if (data.length === 0 || data.length === 2) {
                    document.location.reload();
                } else {
                    /** Show copy modal */
                    let sanitizeData = DOMPurify.sanitize(data);
                    $('.reading-list-page').append(sanitizeData);
                    $('#history-modal-'+listId).modal('show');
                    modalFix(function () {
                        $('body,html').addClass('modal-open active-request scrollable');
                    });
                    $('.modal-backdrop').css('display', 'none');
                }
            }
        });
    });
    /** Edit list name */
    $(document).on('click', '#edit-list', function (e) {
        if($(this).parents().eq(8).find('.input-list-name').hasClass('readonly')) {
            $(this).parents().eq(8).find('.input-list-name').removeAttr('readonly');
            $(this).parents().eq(8).find('.input-list-name').removeClass('readonly');
            $(this).parents().eq(8).find('.update-actions').removeClass('hidden');
        } else {
            $(this).parents().eq(8).find('.input-list-name').attr('readonly', 'readonly');
            $(this).parents().eq(8).find('.input-list-name').addClass('readonly');
            $(this).parents().eq(8).find('.update-actions').addClass('hidden');
        }
        textInputListName = $(this).parents().eq(8).find('.input-list-name').val();
    });

    $(document).on('click', '.reading-list-name .save-update', function (e) {
        var readingListDiv = $(this).parent().parent();
        readingListDiv.find('.input-list-name').attr('readonly', 'readonly');
        readingListDiv.find('.input-list-name').addClass('readonly');
        readingListDiv.find('.edit-list-name').removeClass('hidden');
        readingListDiv.find('.update-actions').addClass('hidden');
        var newListName = $.trim(readingListDiv.find('.input-list-name').val());
        if ( newListName !='') {
            //get all books inside section and update category
            var listId = $('.tab-pane.active').attr('id');
            $.ajax({
                url: '/list-name-update',
                method: 'put',
                data: {readingListID: listId, listName: newListName},
                success: function (result) {
                    $('li.active').find('.book-name').text(newListName);
                }
            });
            resizeInput($('.reading-info-container .input-list-name'));
        } else {
            readingListDiv.find('.input-list-name').val( $('li.active').find('.book-name').text());
        }
        //call ajax to save section (to do)
    });

    $(document).on('click', '.reading-list-name .cancel-update', function (e) {
        var readingListDiv = $(this).parent().parent();
        readingListDiv.find('.input-list-name').attr('readonly', 'readonly').addClass('readonly');
        readingListDiv.find('.update-actions').addClass('hidden');
        readingListDiv.find('.input-list-name').val(textInputListName);
        resizeInput($('.reading-info-container .input-list-name'));
    });

    $(document).on('click', '.review-book', function(){

        var title   = $(this).attr('data-title');
        var subtitle = $(this).attr('data-subtitle');
        var isbn = DOMPurify.sanitize($(this).attr('data-isbn'));
        var editionNumber = $(this).attr('data-editionNumber');
        if(isbn) {
            //set Modal Data
            $('#rating-book-modal .book_title').text(title);
            $('#rating-book-modal .book_subtitle').text(subtitle);
            if(editionNumber) {
                $('#rating-book-modal .edition_num').text( $('#rating-book-modal .edition_num').attr('data-text-edition')+ ' ' +editionNumber);
            }
            $("#rating-book-modal .popup-title").attr('data-book_isbn',isbn);
            var src = $('#'+isbn).find('img').attr('src');
            $('#rating-book-modal .cover').attr('src',src);
        }
    });

});

function showStepOne(){
    $("#share-book-modal #step1").addClass("active");
}
/** Desktop copy product link into clipboard  */
$(document).on('click', '.dot-share-product-popup', function (e) {
    e.preventDefault();
    copyToClipboard($(this).find('a').attr('data-link'),'#share-book-modal');
});