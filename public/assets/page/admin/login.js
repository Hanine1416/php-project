$(document).ready(function () {
    function growl(success, message) {
        var successTitle = $('#success').text();
        var errorTitle = $('#error').text();
        if (success) {
            $.growl.notice({
                title: successTitle,
                duration: 10000,
                delayOnHover: true,
                message: message
            });
        } else {
            $.growl.error({
                title: errorTitle,
                duration: 10000,
                delayOnHover: true,
                message: message
            });
        }
    }

    $('#login-btn').off().on('click',function (e) {
        e.preventDefault();
        var username = $('#username'),
            password = $('#password'),
            formValid =true,
            form = $(this).closest('form'),
            loader = $('.loader');

        $.each([username,password],function (i,element) {
            if(!$(element).val()){
                $(element).addClass('warning');
                formValid=false;
            }
        });
        if(formValid){
            $.ajax({
                url : form.prop('action'),
                method: 'post',
                data:{password:password.val(),username:username.val()},
                dataType:'json',
                beforeSend:function () {
                    form.hide();
                    loader.show();
                },
                success:function (data) {
                    if(data.Result){
                        window.location=data.redirect;
                    }else{
                        showErrorBox(data.message);
                    }
                    loader.hide();
                    form.show();
                }
            });
        }
        form.find('input').on('keyup',function (e) {
            if($(this).val())
                $(this).removeClass('warning');
            else
                $(this).addClass('warning')
        })
    });

});