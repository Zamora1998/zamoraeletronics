var MailAccounts_CTRL_URL = "/" + chrLocale + "/controller/mailaccounts";

var tableAccounts = $("#tableMailAccounts").DataTable({
    ajax: {
        url: MailAccounts_CTRL_URL,
        type: "POST",
        dataSrc: "data",
        data: {
            action: "R",
            part: "A",
        },
    },
    language: {
        url: TABLELANG,
    },
    columns: [
        {
            title: labels.tblUsername,
            data: "username",
            orderable: true,
            className: "dt-left dt-nowrap",
        },
        {
            title: labels.tblReplyto,
            data: "replyto",
            orderable: true,
            className: "dt-left dt-nowrap",
        },
        {
            title: labels.tblHost,
            data: "host",
            orderable: false,
            className: "dt-center dt-nowrap",
        },
        {
            title: labels.tblProtocol,
            data: "protocol",
            orderable: false,
            className: "dt-center dt-nowrap",
        },
        {
            title: labels.tblPort,
            data: "port",
            orderable: false,
            className: "dt-center dt-nowrap",
        },
        {
            title: labels.tblSmtpsecure,
            data: "smtpsecure",
            orderable: false,
            className: "dt-center dt-nowrap",
        },
        {
            title: labels.tblPassword,
            data: "password",
            orderable: false,
            className: "dt-center dt-nowrap",
            render: function () {
                return "****************";
            }
        },
        {
            title: labels.tblSmtpauth,
            data: "smtpauth",
            orderable: true,
            className: "dt-center dt-nowrap",
            render: function (data, type, row) {
                return cellRender({
                    type: "check",
                    data,
                    id: "smtpauth" + row.id,
                });
            }
        },
        {
            title: labels.tblOauth,
            data: "oauth",
            orderable: true,
            className: "dt-center dt-nowrap",
            render: function (data, type, row) {
                return cellRender({
                    type: "check",
                    data,
                    id: "oauthCheck_" + row.id,
                });
            }
        },
        {
            title: labels.tblDebug,
            data: "debug",
            orderable: true,
            className: "dt-center dt-nowrap",
            render: function (data, type, row) {
                return cellRender({
                    type: "check",
                    data,
                    id: "debugCheck_" + row.id,
                });
            }
        },
        {
            title: labels.tblProtected,
            data: "protected",
            orderable: true,
            className: "dt-center dt-nowrap",
            render: function (data, type, row) {
                return cellRender({
                    type: "check",
                    data,
                });
            }
        },
        {
            title: labels.tblEnabled,
            data: "enabled",
            orderable: true,
            className: "dt-center dt-nowrap",
            render: function (data, type, row) {
                return cellRender({
                    type: "check",
                    data,
                });
            },
        },
        {
            title: labels.tblActions,
            orderable: false,
            searchable: false,
            data: "id",
            className: "dt-right",
            render: function (data, type, row) {
                return cellRender({
                    type: "dropdown",
                    data: data,
                    text: labels.tblActions,
                    class: row.disabled ? 'btn btn-sm btn-primary disabled' : 'btn btn-sm btn-primary',
                    dataTags: {
                        id: data,
                    },
                    listItems: [
                        {
                            id: "accountEdit_" + data,
                            text: labels.btnEdit,
                            icon: "far fa-edit",
                            disabled: row.disabled ? 1 : 0,
                            dataTags: {
                                id: data,
                            },
                        },
                        {
                            id: 'accountDelete_' + data,
                            text: labels.btnDelete,
                            icon: 'far fa-trash-alt',
                            disabled: row.disabled ? 1 : 0,
                            dataTags: {
                                id: data,
                            },
                        }
                    ],
                });
            },
        }
    ],
    dom: '<"row"<"#createmailaccount.col-6"><"col-6"f>>ti',
    responsive: true,
    paging: false,
    scrollX: false,
    scrollY: "calc(100vh - 323px)",
    drawCallback: function () {
        var html =
            '<button id="MailAccountCreate" type="button" class="btn btn-sm btn-success me-2" data-id="0">' +
            labels.btnNew +
            "</button>";
        $("#createmailaccount").html(html);
    },
});

$(document).on('click', '[id^="accountEdit_"],#MailAccountCreate', function (e) {
    e.preventDefault();
    clearRouteForm();
    var id = $(this).data('id');
    $('#mailAccount').data('id', id);
    $('#mailAccount h1').text((id ? labels.lblEditMailAccount : labels.lblNewMailAccount));

    $.ajax({
        type: "post",
        url: MailAccounts_CTRL_URL,
        data: {
            action: 'R',
            part: 'S',
            id: id
        },
        dataType: "json",
        success: function (response) {
            var data = response.data[0] || [];
            $('#idUser').val(data.username || '');
            $('#idPass').val(data.password);
            $('#idHost').val(data.host || '');
            $('#idPort').val(data.port || '');
            $('#idSmtpsecure').val(data.smtpsecure || 'tls');
            $('#idProtocol').val(data.protocol || 'smtp');
            $('#idReplyto').val(data.replyto || '');
            $('#idSmtpauth').prop("checked", data.smtpauth == 1 || 0);
            $('#idProtected').prop("checked", data.protected == 1 || 0);
            $('#idEnabled').prop("checked", data.enabled == 1 || 0);
            $('#idDebug').prop("checked", data.debug == 1 || 0);
            if (data.oauth == 1) {
                $('#idOauth').prop("checked", true);
                $('#sectionOauthType').removeClass('d-none');
                $('#sectionOauthClientId').removeClass('d-none');
                $('#sectionOauthClientSecret').removeClass('d-none');
                $('#sectionOauthRefreshToken').removeClass('d-none');
                $('#sectionOauthTenantId').removeClass('d-none');
                $('#idOauthType').val(data.oauth_type || '');
                $('#idOauthClientId').val(data.oauth_client_id || '');
                $('#idOauthClientSecret').val(data.oauth_client_secret || '');
                $('#idOauthRefreshToken').val(data.oauth_refresh_token || '');
                $('#idOauthTenantId').val(data.oauth_tenant_id || '');;
            } else {
                $('#idOauth').prop("checked", false);
                $('#sectionOauthType').addClass('d-none');
                $('#sectionOauthClientId').addClass('d-none');
                $('#sectionOauthClientSecret').addClass('d-none');
                $('#sectionOauthRefreshToken').addClass('d-none');
                $('#sectionOauthTenantId').addClass('d-none');
            }
        }
    });
    $('#mailAccount').modal('show');
});

function clearRouteForm() {
    $("#mailAccount").data("id", 0);
    $('#idUser').val("");
    $('#idPass').val("");
    $('#idHost').val("");
    $('#idPort').val("");
    $('#idReplyto').val("");
    $("#idUser").removeClass("is-invalid");
    $("#idPass").removeClass("is-invalid");
    $("#idHost").removeClass("is-invalid");
    $("#idPort").removeClass("is-invalid");
    $('#idSmtpauth').prop("checked", false);
    $('#idProtected').prop("checked", false);
    $('#idEnabled').prop("checked", false);
    $('#idDebug').prop("checked", false);
    $('#idSmtpsecure').val('tls').trigger('change');
    $('#idProtocol').val('smtp').trigger('change');
}

$('#accountSave').click(function (e) {
    e.preventDefault();

    var validation = true;
    var emailPattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var data = $('#mailAccount input, #mailAccount select').serializeObject();

    data.id = $('#mailAccount').data('id');
    data.action = 'C';
    data.part = 'C';
    $('#mailAccount input, #mailAccount select').removeClass('is-invalid');

    if (data.elUser === '') {
        validation = false;
        $('#idUser').addClass('is-invalid').removeClass('is-valid');
    } else if (!emailPattern.test(data.elUser)) {
        validation = false;
        $('#idUser').addClass('is-invalid').removeClass('is-valid');
    }
    if (data.elPass == '' && data.elOauth == 0) {
        validation = false;
        $('#idPass').addClass('is-invalid');
    }
    if (data.elHost == '') {
        validation = false;
        $('#idHost').addClass('is-invalid');
    }
    if (data.elPort == '' || !/^\d+$/.test(data.elPort)) {
        validation = false;
        $('#idPort').addClass('is-invalid');
    }
    if (data.elOauth == 1) {
        if (data.elOauthClientId == '') {
            validation = false;
            $('#idOauthClientId').addClass('is-invalid');
        }
        if (data.elOauthClientSecret == '') {
            validation = false;
            $('#idOauthClientSecret').addClass('is-invalid');
        }
        if (data.elOauthRefreshToken == '') {
            validation = false;
            $('#idOauthRefreshToken').addClass('is-invalid');
        }
        if (data.elOauthType == '') {
            validation = false;
            $('#idOauthType').addClass('is-invalid');
        }
    }
    data.elSmtpsecure = $('#idSmtpsecure').val();
    data.elProtocol = $('#idProtocol').val();

    data.elSmtpauth = $('#idSmtpauth').is(':checked') ? 1 : 0;
    data.elOauth = $('#idOauth').is(':checked') ? 1 : 0;
    data.elOauthType = $('#idOauthType').val();
    data.elProtected = $('#idProtected').is(':checked') ? 1 : 0;
    data.elEnabled = $('#idEnabled').is(':checked') ? 1 : 0;
    data.elDebug = $('#idDebug').is(':checked') ? 1 : 0;

    if (validation) {
        $.ajax({
            type: "post",
            url: MailAccounts_CTRL_URL,
            data: data,
            dataType: "json",
            success: function (response) {
                if (response.result) {
                    var text = response ? labels.nteCreateSuccess : labels.nteUpdateSuccess;
                    alertNotify({
                        type: "success",
                        text,
                        icon: "fas fa-check",
                        timeout: 3000,
                    });
                } else {
                    var text = response ? labels.nteCreateError : labels.nteUpdateError;
                    alertNotify({
                        type: "warning",
                        text,
                        icon: "fas fa-exclamation-triangle",
                        timeout: 3000,
                    });
                }
                $('#mailAccount').modal('hide');
                clearRouteForm();
                tableAccounts.ajax.reload();
            }
        });
    }
});

$('#accountTestMail').click(function (e) {
    e.preventDefault();

    var validation = true;
    var emailPattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var data = $('#mailAccount input, #mailAccount select').serializeObject();

    data.id = $('#mailAccount').data('id');
    data.action = 'R';
    data.part = 'T';
    $('#mailAccount input, #mailAccount select').removeClass('is-invalid');

    if (data.elUser === '') {
        validation = false;
        $('#idUser').addClass('is-invalid').removeClass('is-valid');
    } else if (!emailPattern.test(data.elUser)) {
        validation = false;
        $('#idUser').addClass('is-invalid').removeClass('is-valid');
    }

    if (data.elPass == '' && data.elOauth == 0) {
        validation = false;
        $('#idPass').addClass('is-invalid');
    }
    if (data.elHost == '') {
        validation = false;
        $('#idHost').addClass('is-invalid');
    }
    if (data.elPort == '' || !/^\d+$/.test(data.elPort)) {
        validation = false;
        $('#idPort').addClass('is-invalid');
    }
    if (data.elOauth == 1) {
        if (data.elOauthClientId == '') {
            validation = false;
            $('#idOauthClientId').addClass('is-invalid');
        }
        if (data.elOauthClientSecret == '') {
            validation = false;
            $('#idOauthClientSecret').addClass('is-invalid');
        }
        if (data.elOauthRefreshToken == '') {
            validation = false;
            $('#idOauthRefreshToken').addClass('is-invalid');
        }
        if (data.elOauthType == '') {
            validation = false;
            $('#idOauthType').addClass('is-invalid');
        }
    }

    data.elSmtpsecure = $('#idSmtpsecure').val();
    data.elProtocol = $('#idProtocol').val();

    data.elSmtpauth = $('#idSmtpauth').is(':checked') ? 1 : 0;
    data.elOauth = $('#idOauth').is(':checked') ? 1 : 0;
    data.elOauthType = $('#idOauthType').val();
    data.elProtected = $('#idProtected').is(':checked') ? 1 : 0;
    data.elEnabled = $('#idEnabled').is(':checked') ? 1 : 0;
    data.elDebug = $('#idDebug').is(':checked') ? 1 : 0;

    if (validation) {
        $.ajax({
            type: "post",
            url: MailAccounts_CTRL_URL,
            data: data,
            dataType: "json",
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: "success",
                        text: labels.nteTestSuccess,
                        icon: "fas fa-check",
                        timeout: 3000,
                    });
                } else {
                    alertNotify({
                        type: "warning",
                        text: response.error ? response.error : labels.nteTestError,
                        icon: "fas fa-check",
                        timeout: 3000,
                    });
                }
            }
        });
    }
});

$(document).on('click', '[id^=accountDelete_]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    alertNotify({
        type: 'warning',
        text: labels.nteDeleteWarn,
        icon: 'fas fa-exclamation',
        buttons: [
            {
                type: 'warning',
                text: labels.btnYes,
                icon: 'fas fa-check',
                callback: function () {
                    accountDelete(id);
                },
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ]
    })
})

function accountDelete(id) {
    $.ajax({
        type: "post",
        url: MailAccounts_CTRL_URL,
        data: {
            action: 'D',
            part: 'S',
            id,
        },
        dataType: "json",
        success: function (response) {
            var data = response;
            if (data.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteDeleteSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteDeleteError,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
            tableAccounts.ajax.reload();
        }
    });
}

$('#mailAccount input').on('input change', function () {
    if ($(this).val() !== '') {
        $(this).removeClass('is-invalid');
    }
});

$('#nav-mailAccount-tab').on('shown.bs.tab', function (e) {
    tableAccounts.ajax.reload();
});

$('#sectionPassword').on('click', '#review', function (event) {
    event.preventDefault();
    var input = $('#idPass');
    var type = input.attr('type');
    if (type === 'password') {
        input.attr('type', 'text');
        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

$(document).on('click', '#idOauth', function (e) {
    //e.preventDefault();
    if ($(this).is(':checked')) {
        $('#sectionOauthClientId').removeClass('d-none');
        $('#sectionOauthClientSecret').removeClass('d-none');
        $('#sectionOauthRefreshToken').removeClass('d-none');
        $('#sectionOauthType').removeClass('d-none');
        $('#sectionOauthTenantId').removeClass('d-none');
    } else {
        $('#sectionOauthClientId').addClass('d-none');
        $('#sectionOauthClientSecret').addClass('d-none');
        $('#sectionOauthRefreshToken').addClass('d-none');
        $('#sectionOauthType').addClass('d-none');
        $('#sectionOauthTenantId').addClass('d-none');
    }
});

// Listener para recibir el Refresh Token desde el popup de OAuth2
window.addEventListener('message', function (event) {
    if (event.data && event.data.oauthRefreshToken) {
        $('#idOauthRefreshToken').val(event.data.oauthRefreshToken).removeClass('is-invalid');
        alertNotify({
            type: "success",
            text: "Refresh Token capturado correctamente.",
            icon: "fas fa-key",
            timeout: 3000,
        });
    }
}, false);

// Botón para obtener el Refresh Token
$(document).on('click', '#btnGetOauthToken', function (e) {
    e.preventDefault();
    var provider = $('#idOauthType').val();
    var clientId = $('#idOauthClientId').val();
    var clientSecret = $('#idOauthClientSecret').val();

    if (provider == '' || clientId == '' || clientSecret == '') {
        alertNotify({
            type: "warning",
            text: "Por favor, completa el Provider, Client ID y Client Secret antes de solicitar el token.",
            icon: "fas fa-exclamation-triangle",
            timeout: 5000,
        });
        if (provider == '') $('#idOauthType').addClass('is-invalid');
        if (clientId == '') $('#idOauthClientId').addClass('is-invalid');
        if (clientSecret == '') $('#idOauthClientSecret').addClass('is-invalid');
        return;
    }

    var url = '/vendor/phpmailer/phpmailer/get_oauth_token.php' +
        '?prefillProvider=' + encodeURIComponent(provider) +
        '&clientId=' + encodeURIComponent(clientId) +
        '&clientSecret=' + encodeURIComponent(clientSecret);

    var width = 600, height = 700;
    var left = (screen.width / 2) - (width / 2);
    var top = (screen.height / 2) - (height / 2);

    window.open(url, 'OAuthTokenGen',
        'menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,' +
        'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
});