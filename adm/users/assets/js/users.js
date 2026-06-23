var USERADM_CTRL = '/' + chrLocale + '/controller/useradm';

$(document).ready(function () {
    $('#modalUsers #langId')
        .select2({
            data: locTree,
            allowClear: true,
            theme: 'bootstrap-5',
            selectionCssClass: 'select2--small',
            dropdownCssClass: 'select2--small',
            placeholder: labels.nteLanguages,
        })
        .val('0')
        .trigger('change');

    // Event to handle the deselection of an option
    $('#UserId').on('select2:unselect', function () {
        $('#UserId').val(0).trigger('change');
        $('#modalUsers #langId').val(0).trigger('change');
    });

    $('#cancel').on('click', function () {
        $('#UserId').val(0).trigger('change');
        $('#modalUsers #langId').val(0).trigger('change');
    });
});
//region table

var tableUsers = $('#tableUsers').DataTable({
    ajax: {
        url: USERADM_CTRL,
        type: 'POST',
        dataSrc: 'data',
        data: {
            action: 'R',
            part: 'T',
        },
    },
    language: {
        url: TABLELANG,
    },
    columns: [
        {
            data: 'id',
            visible: false,
            orderable: false,
        }, // 0
        {
            title: labels.frmFirstname,
            data: 'first',
            visible: true,
            orderable: true,
        }, // 1
        {
            title: labels.frmLastname,
            data: 'last',
            visible: true,
            orderable: false,
        }, // 2
        {
            title: labels.frmEmail,
            data: 'email',
            visible: true,
            orderable: false,
        }, // 3
        {
            title: labels.btnActions,
            data: 'id',
            searchable: false,
            sortable: false,
            visible: true,
            width: '20px',
            render: function (data, type, row) {
                return cellRender({
                    type: 'dropdown',
                    data,
                    text: labels.btnActions,
                    dataTags: {
                        id: data,
                    },
                    listItems: [
                        {
                            id: 'userEdit_' + data,
                            class: 'btn',
                            text: labels.lblEdit,
                            icon: 'fas fa-pencil-alt mr-15',
                            dataTags: {
                                id: data,
                            },
                        },
                        /*{
                            id: "userDelete_" + data,
                            class: "btn",
                            text: labels.lblDelete,
                            icon: "far fa-trash-alt mr-15",
                            dataTags: {
                                id: data,
                            },
                        },*/
                    ],
                });
            },
            orderable: false,
            className: 'text-right',
        }, //5
    ],
    order: [[1, 'asc']], // 🔹 columna 3, ascendente
    dom: '<"row"<"#groupCustom.col-6"><"col-6"f>><"row dt-panelmenu"<"d-none d-sm-block col-12">>ti',
    responsive: true,
    paging: false,
    scrollX: false,
    scrollY: 'calc(100vh - 345px)',
    drawCallback: function () {
        $('#groupCustom').html(
            '<button class="addUser btn btn-success float-left" type="button" data-target="#edit" id="addNewUser" data-id="0">' + labels.lblAdd + '</button>',
        );
    },
});

//region events

// Create and edit users
$(document).on('click', '[id^="userEdit_"],#addNewUser', function (e) {
    e.preventDefault();
    var modal = $('#modalUsers');
    if (modal.length === 0) {
        return;
    }
    clearUserForm();
    var id = $(this).data('id');

    modal.data('id', id);
    modal.find('h1').text(id ? labels.lblEditUser : labels.lblNewUser);

    // Check if you have “Dev” access (permission 1)
    var loggedHasDevAccess = (loggedUserAccesses & 1) !== 0;

    if (id) {
        // Cargar datos en modo edición
        $.ajax({
            type: 'post',
            url: USERADM_CTRL,
            data: {
                action: 'R',
                part: 'U',
                id: id,
            },
            dataType: 'json',
            success: function (response) {
                var data = response.data;
                modal.find('#First').val(data.first);
                modal.find('#Last').val(data.last);
                modal.find('#Email').val(data.email);
                modal.find('#usersEnabled').prop('checked', !!parseInt(data.enabled));

                modal.find("[id^='access_']").prop('checked', false);
                $(data.accesses).each(function (index, element) {
                    modal.find('#access_' + element).prop('checked', true);
                });

                modal.find('#langId').val(data.locale_id).trigger('change');
            },
        });
    }

    modal.modal('show');
});

//Dynamically load and update user information
$(document).on('change', '#UserId', function () {
    var id = $('#UserId').val();
    if (id == 0) {
        $('input#First').val('');
        $('input#Last').val('');
        $('input#Email').val('');
        $('#usersEnabled').prop('checked', true);
        $('[id^="access_"]').prop('checked', false);
        $('input[type=text]').removeClass('is-invalid');
        $('#langId').val('').trigger('change');
    } else {
        $.ajax({
            url: USERADM_CTRL,
            type: 'POST',
            data: {
                action: 'R',
                part: 'U',
                id,
            },
            success: function (response) {
                var data = response.data;
                $('input#First').val(data.first);
                $('input#Last').val(data.last);
                $('input#Email').val(data.email);
                $('#usersEnabled').prop('checked', parseInt(data.enabled));
                $('[id^="access_"]').prop('checked', false);
                $(data.accesses).each(function (index, element) {
                    $('#access_' + element).prop('checked', true);
                });
                $('#langId').val(data.locale_id).trigger('change');
            },
        });
    }
});

// Save changes on new and editing users
$('#modalUsers #save').on('click', function (event) {
    event.preventDefault();

    var valid = true;
    var data = $('#modalUsers input,select').serializeObject();
    data.id = $('#modalUsers').data('id');
    data.action = 'U';
    data.email = String(data.email || '')
        .trim()
        .toLowerCase();
    data.enabled = $('#modalUsers #usersEnabled').is(':checked') ? 1 : 0;
    data.lang_id = $('#modalUsers #langId').val() || 'en_US';
    $('input[type=text]').removeClass('is-invalid');
    // Validations
    if (data.first == '') {
        $('#modalUsers #First').addClass('is-invalid');
        valid = false;
    }
    if (data.last == '') {
        $('#modalUsers #Last').addClass('is-invalid');
        valid = false;
    }
    if (data.email == '') {
        $('#modalUsers #Email').addClass('is-invalid');
        valid = false;
    }
    if (!validateEmail(data.email)) {
        $('#modalUsers #Email').addClass('is-invalid');
        valid = false;
    }
    if (!data.lang_id || data.lang_id == '0') {
        $('#modalUsers #langId').addClass('is-invalid');
        valid = false;
    }

    if (valid) {
        $.ajax({
            type: 'POST',
            url: USERADM_CTRL,
            dataType: 'JSON',
            data: data,
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: 'success',
                        text: labels.nteSaveSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                } else {
                    if (response.error.includes('Duplicate entry')) {
                        alertNotify({
                            type: 'warning',
                            text: labels.nteDuplicateEntry,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    } else {
                        alertNotify({
                            type: 'danger',
                            text: labels.nteErrorSave,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    }
                }
                $('#modalUsers').modal('hide');
                tableUsers.ajax.reload(null, false);
            },
        });
    } else {
        alertNotify({
            type: 'warning',
            text: labels.nteFields,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});
//endregion
// region functions
function validateEmail(email) {
    var regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(String(email));
}

function clearUserForm() {
    $('#modalUsers').data('id', 0);

    // Clear values
    $("#modalUsers input[type='text'], #modalUsers input[type='email'], #modalUsers select").val('');
    $('#modalUsers #usersEnabled').prop('checked', false);
    $("#modalUsers [id^='access_']").prop('checked', false);
    $('#modalUsers #langId').val('').trigger('change');

    // Eliminate error classes
    $('#modalUsers input, #modalUsers select').removeClass('is-invalid');
}

$('#nav-users-tab').on('shown.bs.tab', function (e) {
    $(window).resize();
    //tableDataSuppliers.ajax.reload();
});

$(document).ready(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
