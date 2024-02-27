$(document).ready(function () {
    $('#submit-code').on('click', function (e) {
        e.preventDefault();
        var codeInput = $('#confirmation_code');
        var code= codeInput.val().trim();
        codeInput.val(code);
        var btn = $('#submit-code');
        codeInput.removeClass('warning');
        $('.digital_code').find('.error').remove();
        var valid = true;

        if ( code === null || code === 'undefined' || code === '' || code.length < 6 ) {
            let title = DOMPurify.sanitize(codeInput.prop('title'));
            codeInput
                .addClass('warning')
                .parent().append('<span class="error" style="position: relative;">' + title + '</span>');
            valid = false;
        }

        if (valid) {
            $.ajax({
                url: btn.data('url'),
                method: 'POST',
                data: {'code':code},
                dataType: 'JSON',
                beforeSend: function () {
                    btn.addClass('loading');
                },
                success: function (response) {
                    if (response.success) {
                        /** redirection to homepage */
                        window.location = response.redirect;
                    } else
                        showErrorBox(response.message);
                    btn.removeClass('loading');
                }
            });
        } else {
          /** show error message */
        }
    });
    $('#resend-code').on('click',function (e) {
        e.preventDefault();
        $.ajax({
            url: $('#resend-code-url').data('url'),
            method: 'POST',
            dataType: 'JSON',
            success: function (response) {
                if (response.success) {
                    /** redirection to homepage */
                    $('.update-notif').show();
                    $('.update-notif').fadeOut(5000);
                    $(document).on('click', '.close-btn', function (e) {
                        e.preventDefault();
                        $(this).closest('.update-notif').remove();
                    });
                } else
                    showErrorBox(response.message);
            }
        });
    });
});
