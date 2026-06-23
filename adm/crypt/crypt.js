const CTRL_CRYPT = '/controller/crypt';

$('#crencrypt').click(function (e) {
    e.preventDefault();
    formReset();
    let validated = true;
    let data = $('#crypt input, #crypt textarea').serializeObject();
    if (data.plain.trim() == '') {
        validated = false;
        $('#crPlain').addClass('is-invalid');
    }
    if (data.key.trim() == '') {
        validated = false;
        $('#crKey').addClass('is-invalid');
    }

    if (validated == true) {
        data.action = 'C';
        data.part = 'E';
        $.ajax({
            type: 'post',
            url: CTRL_CRYPT,
            data,
            dataType: 'json',
            success: function (response) {
                $('#crHash').val(response.hash).addClass('is-valid');
            },
        });
    }
});

$('#crdecrypt').click(function (e) {
    e.preventDefault();
    formReset();
    let validated = true;
    let data = $('#crypt input, #crypt textarea').serializeObject();
    if (data.hash.trim() == '') {
        validated = false;
        $('#crHash').addClass('is-invalid');
    }
    if (data.key.trim() == '') {
        validated = false;
        $('#crKey').addClass('is-invalid');
    }

    if (validated == true) {
        data.action = 'C';
        data.part = 'D';
        $.ajax({
            type: 'post',
            url: CTRL_CRYPT,
            data,
            dataType: 'json',
            success: function (response) {
                $('#crPlain').val(response.plain).addClass('is-valid');
            },
        });
    }
});

$('#crpwcrypt').click(function (e) {
    e.preventDefault();
    formReset();
    let validated = true;
    let data = $('#crypt input, #crypt textarea').serializeObject();
    data.action = 'C';
    data.part = 'P';
    if (data.plain.trim() == '') {
        validated = false;
        $('#crPlain').addClass('is-invalid');
    }

    if (validated == true) {
        $.ajax({
            type: 'post',
            url: CTRL_CRYPT,
            data,
            dataType: 'json',
            success: function (response) {
                $('#crHash').val(response.hash).addClass('is-valid');
            },
        });
    }
});

function formReset() {
    $('#crypt input, #crypt textarea').removeClass('is-valid').removeClass('is-invalid');
}
