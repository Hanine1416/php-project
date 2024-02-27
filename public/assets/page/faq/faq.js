$(document).ready(function () {
    /** Tab manipulator **/
    (function($) {
        $('.accordion .title-accordion').on('click touchstart', function(j) {
            var elem = $(this)
            if(!elem.hasClass('active')){
                elem.next().show();
                $('.accordion .title-accordion.active').removeClass('active').next().hide();
                elem.addClass('active');
            }else{
                elem.removeClass('active');
                elem.next().hide();
            }
            j.preventDefault();
        });
    })(jQuery);
    $('.degital-book').on('click touchstart', function() {
        $(this).closest(".tile-tab").find(".title-accordion.active").removeClass('active').next().hide();
        $("#request-link").closest(".hiddenDescription").prev().addClass('active').next().show();
    });
});