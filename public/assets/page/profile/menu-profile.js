$(document).ready(function () {
    //get url of clicked link
    var url = window.location.pathname;
    urlRegExp = new RegExp(url.replace(/\/$/, '') + "$");
    $('.profile_menu_container a').each(function () {
        if (urlRegExp.test(this.href.replace(/\/$/, ''))) {
            $(this).parent().addClass('active');
            //get position of selected element and scroll to his position
            var element = document.querySelector(".profile_menu_container li.active a");
            element.scrollIntoView({behavior: "instant" ,block: "end"});
        }
    });

    $('.started-btn .submitBtn').on('click', function () {
        var userInterests = [];
        if ($('input[name="choice"]:checked').length === 0) {
            var box = showErrorBox($('#error_interests').text());
            box.find('#box-ok').text($('#box-msg-close').text());
        } else {
            //call web service to set user categories
            $.each($('input[name="choice"]:checked'), function (i, el) {
                userInterests.push($(this).val());
            });
            //$(this).attr('disabled', 'disabled')
            $.ajax({
                url: $('.user-interests-page').data('update-user'),
                method: 'POST',
                data: {userInterests: userInterests},
                success: function (response) {
                    if (response.success) {
                        $('.alert-success-interest').fadeIn(500);
                        setTimeout(function() {
                            $('.alert-success-interest').fadeOut();
                        }, 5000);
                    }
                }
            });
        }
    });
});