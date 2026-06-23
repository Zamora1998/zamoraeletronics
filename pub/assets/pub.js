$(document).ready(function () {
    if (modal != '') {
        $('#' + modal).modal('show');
        if (modal == 'reset') {
            $('#repass1').data('score', 0);
            $('#repass1').password({
                closestSelector: 'div.form-group', // Element to append to, instead of input
                enterPass: 'Type your password',
                shortPass: 'The password is too short',
                containsField: 'The password contains your username',
                steps: {
                    // Easily change the steps' expected score here
                    13: 'Really insecure password',
                    33: 'Weak; try combining letters & numbers',
                    64: 'Medium; try using special characters',
                    94: 'Strong password',
                },
                showPercent: false,
                showText: false, // shows the text tips
                animate: true, // whether or not to animate the progress bar on input blur/focus
                animateSpeed: 'fast', // the above animation speed
                field: false, // select the match field (selector or jQuery instance) for better password checks
                fieldPartialMatch: true, // whether to check for partials in field
                minimumLength: 4, // minimum password length (below this threshold, the score is 0)
                useColorBarImage: true, // use the (old) colorbar image
                customColorBarRGB: {
                    red: [0, 240],
                    green: [0, 240],
                    blue: 10,
                } // set custom rgb color ranges for colorbar.
            });
        }
    }
});

$('#login').on('click', '#lgview', function (event) {
    event.preventDefault();
    var type = $('#lgpass').attr('type');
    if (type == 'password') {
        $('#lgpass').attr('type', 'text');
        $('#lgview i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $('#lgpass').attr('type', 'password');
        $('#lgview i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

$('#reset').on('click', '#review1', function (event) {
    event.preventDefault();
    var type = $('#repass1').attr('type');
    if (type == 'password') {
        $('#repass1').attr('type', 'text');
        $('#review1 i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $('#repass1').attr('type', 'password');
        $('#review1 i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

$('#reset').on('click', '#review2', function (event) {
    event.preventDefault();
    var type = $('#repass2').attr('type');
    if (type == 'password') {
        $('#repass2').attr('type', 'text');
        $('#review2 i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $('#repass2').attr('type', 'password');
        $('#review2 i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

$('#lgcaptcha').keypress(function (e) {
    var key = e.which;
    if (key == 13) {
        // the enter key code
        $('#lglogin').click();
        return false;
    }
});

$('#login').on('click', '#lglogin', function (event) {
    var validation = true;
    $('input[id^=lg]').removeClass('is-invalid');
    var values = $('#login input').serializeObject();
    for (const [key, value] of Object.entries(values)) {
        if (value === '') {
            if (key == 'captcha' && $('#divLgCaptcha').hasClass('d-none')) {
                continue;
            } else {
                validation = false;
                $('input[id^=lg][name="' + key + '"]').addClass('is-invalid');
            }
        }
    }
    if (validation) {
        $.ajax({
            type: 'POST',
            url: '/' + chrLocale + '/controller/auth',
            data: values,
            dataType: 'json',
            success: function (response) {
                $('.captcha-input').css('background-image', 'url("/captcha/image?' + Math.floor(Date.now() / 1000) + '")');
                var data = response.data;
                if (!data.captcha) {
                    $('#lgcaptcha').addClass('is-invalid');
                } else if (!data.auth) {
                    $('#lguser').addClass('is-invalid');
                    $('#lgpass').addClass('is-invalid');
                }
                if (data.reroute !== '') {
                    location.href = data.reroute;
                }
                if (data.reqCaptcha) {
                    $('#divLgCaptcha').removeClass('d-none');
                }
            },
        });
    }
});

$('#signup').on('click', '[id^=suview]', function (event) {
    event.preventDefault();
    elNo = $(this).attr('id').substr(-1);

    var type = $('#supass' + elNo).attr('type');
    if (type == 'password') {
        $('#supass' + elNo).attr('type', 'text');
        $('#suview' + elNo + ' i')
            .removeClass('fa-eye')
            .addClass('fa-eye-slash');
    } else {
        $('#supass' + elNo).attr('type', 'password');
        $('#suview' + elNo + ' i')
            .removeClass('fa-eye-slash')
            .addClass('fa-eye');
    }
});

$('#signup').on('click', '#susignup', function (event) {
    var validation = true;
    $('input[id^=su]').removeClass('is-invalid');
    var values = $('#signup input').serializeObject();
    for (const [key, value] of Object.entries(values)) {
        if (value == '') {
            validation = false;
            $('input[id^=su][name="' + key + '"]').addClass('is-invalid');
        }
    }
    if (values.password !== values.passwordc) {
        validation = false;
        $('input[id^=supass]').addClass('is-invalid');
    }
    values.action = 'C';
    if (validation) {
        $.ajax({
            type: 'POST',
            url: '/controller/users',
            data: values,
            dataType: 'json',
            success: function (response) {
                $('.captcha-input').css('background-image', 'url("/captcha/image?' + Math.floor(Date.now() / 1000) + '")');
                var data = response.data;
                if (data.enabled) {
                    if (!data.captcha) {
                        $('#sucaptcha').addClass('is-invalid');
                    }
                }
            }
        });
    }
});

$('#forgot').on('click', '#fgsend', function (event) {
    var username = $('#fgemail').removeClass('is-invalid').val();
    var captcha = $('#fgcaptcha').removeClass('is-invalid').val();
    if (validateEmail(username)) {
        if (captcha != '') {
            $.ajax({
                type: 'post',
                url: '/controller/passRequest',
                data: {
                    captcha,
                    username
                },
                dataType: 'json',
                success: function (response) {
                    /*{"data":{"captcha":true,"mail":{"result":true,"errors":[],"mailId":43},"user":true}}*/
                    $('.captcha-input').css('background-image', 'url("/captcha/image?' + Math.floor(Date.now() / 1000) + '")');
                    if (!response.user) {
                        $('#fgemail').removeClass('is-invalid');
                    }
                    if (response.captcha && response.user && response.mail.mailId != 0) {
                        $('#forgotresult h6').html(headLabels.pwrSuccess);
                        $('#forgotresult').modal('toggle');
                        $('#forgot').modal('toggle');
                    } else if (response.captcha && response.user) {
                        $('#forgotresult h6').html(headLabels.pwrInternalError);
                        $('#forgotresult').modal('toggle');
                        $('#forgot').modal('toggle');
                    } else if (!response.captcha) {
                        $('#fgcaptcha').addClass('is-invalid');
                    } else {
                        $('#forgotresult h6').html(headLabels.pwrFail);
                        $('#forgotresult').modal('toggle');
                        $('#forgot').modal('toggle');
                    }
                }
            });
        } else {
            $('#fgcaptcha').addClass('is-invalid');
        }
    } else {
        $('#fgemail').addClass('is-invalid');
    }
});

$('#reset').on('click', '#reupdate', function (event) {
    var pass1 = $('#repass1').val();
    var pass2 = $('#repass2').val();
    var score = $('#repass1').data('score');
    if (pass1 === pass2 && score >= 64) {
        var data = $('#reset input').serializeObject();
        data.key = $(this).data('key');
        $.ajax({
            type: 'post',
            url: '/controller/passReset',
            data,
            dataType: 'json',
            success: function (response) {
                /*{"data":{"result":true,"error":"","infos":{"Rows matched":"1","Changed":"1","Warnings":"0"}}}*/
                /*{"data":{"result":true,"error":"","infos":{"Rows matched":"0","Changed":"0","Warnings":"0"}}}*/
                if (response.result && parseInt(response.infos.Changed) == 1) {
                    alertNotify({
                        type: 'success',
                        text: headLabels.ntePWResetSoccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    $('#login').modal('toggle');
                    $('#reset').modal('toggle');
                } else {
                    alertNotify({
                        type: 'warning',
                        text: headLabels.ntePWResetError,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
            }
        });
    }
});

function validateEmail(email) {
    var regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(String(email));
}

$('#repass1').on('password.score', (e, score) => {
    $('#repass1').data('score', score);
    if (score < 64) {
        $('.repass1.invalid-feedback').addClass('d-block');
        $('.repass1.valid-feedback').removeClass('d-block');
        $('#repass1').addClass('is-invalid');
        $('#repass1').removeClass('is-valid');
    } else {
        $('.repass1.valid-feedback').addClass('d-block');
        $('.repass1.invalid-feedback').removeClass('d-block');
        $('#repass1').addClass('is-valid');
        $('#repass1').removeClass('is-invalid');
    }
});

$('#repass2').on('keyup', (e) => {
    var pass1 = $('#repass1').val();
    var pass2 = $('#repass2').val();
    if (pass1 !== pass2) {
        $('.repass2.invalid-feedback').addClass('d-block');
        $('.repass2.valid-feedback').removeClass('d-block');
        $('#repass2').addClass('is-invalid');
        $('#repass2').removeClass('is-valid');
    } else {
        $('.repass2.valid-feedback').addClass('d-block');
        $('.repass2.invalid-feedback').removeClass('d-block');
        $('#repass2').addClass('is-valid');
        $('#repass2').removeClass('is-invalid');
    }
});

$('#repass1').on('blur', (e) => {
    $('#repass1').trigger('keyup');
});

$('#repass2').on('blur', (e) => {
    $('#repass2').trigger('keyup');
});
