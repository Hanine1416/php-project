$(document).ready(function () {
    $('.update-profile-cookie').fadeOut(5000);
    $(document).on('click','.close-btn',function (e) {
        e.preventDefault();
        $(this).closest('.update-profile-cookie').remove();
    });
});

$(document).ready(function () {
    console.log("hgkjg")
});
