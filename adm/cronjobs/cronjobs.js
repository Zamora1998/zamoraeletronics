var CRONJOBS_CTRL = '/' + chrLocale + '/controller/cronjobs';

var tableCronjobs = null;
var tableData;
var columns = [];

loadTableCronjobs();

function loadTableCronjobs() {
    tableCronjobs = $('#tableCronjobs').DataTable({
        ajax: {
            url: CRONJOBS_CTRL,
            type: 'POST',
            dataSrc: 'data',
            data: {
                action: 'R',
                part: 'A',
            },
        },
        language: {
            url: TABLELANG,
        },
        columns: [
            {
                title: labels.tblScript,
                data: 'script',
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 0
            {
                title: labels.tblSchedule,
                data: 'schedule',
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 1
            {
                title: labels.tblEnabled,
                data: 'enabled',
                orderable: true,
                className: 'dt-center dt-nowrap',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'checkbox',
                        data,
                        disabled: row.disabled ? true : false, // Si es protegido o está deshabilitado, se bloquea
                        id: 'cronjobEnabled_' + row.id,
                        class: 'form-check-input',
                        dataTags: {
                            id: row.id,
                        },
                    });
                },
            }, // 3
            {
                title: labels.tblProtected,
                data: 'protected',
                orderable: true,
                className: 'dt-center dt-nowrap',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'check',
                        data,
                    });
                },
            },
            {
                title: labels.tblActions,
                data: 'id',
                className: 'dt-right',
                render: function (data, type, row) {
                    const isDisabled = row.disabled ? 'btn btn-sm btn-primary disabled' : 'btn btn-sm btn-primary';
                    return cellRender({
                        type: 'dropdown',
                        data,
                        text: labels.tblActions,
                        class: isDisabled,
                        dataTags: {
                            id: data,
                        },
                        listItems: [
                            {
                                id: 'syncCronjob_' + data,
                                text: labels.lblSync,
                                icon: 'fas fa-sync',
                                disabled: row.disabled ? 1 : 0,
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: 'labelEdit_' + data,
                                text: labels.btnEdit,
                                icon: 'far fa-edit',
                                disabled: row.disabled ? 1 : 0,
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: 'labelDelete_' + data,
                                text: labels.btnDelete,
                                icon: 'far fa-trash-alt',
                                disabled: row.disabled ? 1 : 0,
                                dataTags: {
                                    id: data,
                                },
                            },
                        ],
                    });
                },
            },
        ],
        dom: '<"row"<"#ctrlCustom.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        paging: false,
        scrollX: false,
        scrollY: 'calc(100vh - 265px)',
        drawCallback: function () {
            var html = '<button id="cronjobCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' + labels.btnNew + '</button>';
            $('#ctrlCustom').html(html);
        },
    });
}

$(document).on('change', 'input:checkbox[id^=cronjobEnabled_]', function (e) {
    e.preventDefault();
    var cronjobid = $(this).data('id');
    var enabledCheck = document.getElementById('cronjobEnabled_' + cronjobid).checked;
    var enabledValue = enabledCheck ? 1 : 0;
    if (cronjobid) {
        $.ajax({
            type: 'POST',
            url: CRONJOBS_CTRL,
            data: {
                action: 'U',
                part: 'C',
                id: cronjobid,
                enabled: enabledValue,
            },
            dataType: 'json',
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: 'success',
                        text: labels.nteUpdateSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                } else {
                    alertNotify({
                        type: 'error',
                        text: labels.nteError + '<br>' + response.error,
                        icon: 'fas fa-times',
                        timeout: 3000,
                    });
                }
                reloadTable();
            },
        });
    }
});

$(document).on('click', '[id^="syncCronjob_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    if (id) {
        $.ajax({
            type: 'POST',
            url: CRONJOBS_CTRL,
            data: {
                action: 'R',
                part: 'S',
                id,
            },
            dataType: 'json',
            success: function (response) {
                $('#modalSync').attr('data-id', id);
                $('#modalSync').modal('show');
                var div = document.getElementById('idSuccess');
                div.innerHTML = '';
                var icon = document.createElement('i');
                var label = document.createElement('label');

                if (Array.isArray(response)) {
                    let allSuccess = true;
                    let errors = [];
                    for (let item of response) {
                        if (!item.result) {
                            allSuccess = false;
                        }
                        if (item.errors && item.errors.length > 0) {
                            errors.push(...item.errors);
                        }
                    }
                    if (allSuccess) {
                        label.innerHTML = labels.lblSyncStatus + ':&nbsp;';
                        icon.setAttribute('class', 'fa fa-check text-success');
                        $('#idMessegeResult').val(JSON.stringify(response, null, 2));
                    } else {
                        label.innerHTML = labels.lblSyncStatus + ':&nbsp;';
                        icon.setAttribute('class', 'fa fa-times text-danger');
                        $('#idMessegeResult').val(labels.lblErrorsFound + '\n' + JSON.stringify(errors, null, 2));
                    }
                } else {
                    label.innerHTML = labels.lblSyncStatus + ':&nbsp;';
                    if (response.result) {
                        icon.setAttribute('class', 'fa fa-check text-success');
                        $('#idMessegeResult').val(JSON.stringify(response, null, 2));
                    } else {
                        icon.setAttribute('class', 'fa fa-times text-danger');
                        $('#idMessegeResult').val(response.error);
                    }
                }
                div.appendChild(label);
                div.appendChild(icon);
                var textarea = document.getElementById('idMessegeResult');
                autoResizeTextarea(textarea);
            },
        });
    }
});

$(document).on('click', '[id="sync"]', function (e) {
    e.preventDefault();
    var id = $('#modalSync').attr('data-id');
    if (id) {
        $.ajax({
            type: 'POST',
            url: CRONJOBS_CTRL,
            data: {
                action: 'R',
                part: 'S',
                id,
            },
            dataType: 'json',
            success: function (response) {
                var div = document.getElementById('idSuccess');
                div.innerHTML = '';
                var icon = document.createElement('i');
                var label = document.createElement('label');

                if (Array.isArray(response)) {
                    let allSuccess = true;
                    let errors = [];
                    for (let item of response) {
                        if (!item.result) {
                            allSuccess = false;
                        }
                        if (item.errors && item.errors.length > 0) {
                            errors.push(...item.errors);
                        }
                    }
                    if (allSuccess) {
                        label.innerHTML = labels.lblSyncStatus + ':&nbsp;';
                        icon.setAttribute('class', 'fa fa-check text-success');
                        $('#idMessegeResult').val(JSON.stringify(response, null, 2));
                    } else {
                        label.innerHTML = labels.lblSyncStatus + ':&nbsp;';
                        icon.setAttribute('class', 'fa fa-times text-danger');
                        $('#idMessegeResult').val(labels.lblErrorsFound + '\n' + JSON.stringify(errors, null, 2));
                    }
                } else {
                    label.innerHTML = labels.lblSyncStatus + ':&nbsp;';
                    if (response.result) {
                        icon.setAttribute('class', 'fa fa-check text-success');
                        $('#idMessegeResult').val(JSON.stringify(response, null, 2));
                    } else {
                        icon.setAttribute('class', 'fa fa-times text-danger');
                        $('#idMessegeResult').val(response.error);
                    }
                }
                div.appendChild(label);
                div.appendChild(icon);
                var textarea = document.getElementById('idMessegeResult');
                autoResizeTextarea(textarea);
            },
        });
    }
});

function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    const newHeight = textarea.scrollHeight + 'px';
    $(textarea).animate({ height: newHeight }, 300); // 300ms de duración
}

$('#modalSync').on('shown.bs.modal', function () {
    var textarea = document.getElementById('idMessegeResult');
    autoResizeTextarea(textarea);
});

$('#idMessegeResult').on('input', function () {
    autoResizeTextarea(this);
});

$(document).on('click', '[id^="labelEdit_"],#cronjobCreate', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    clearCronjobForm();
    $('#modalCronjob').data('id', id);
    $('#modalCronjob') > $('h1').text(id ? labels.lblEditCronJob : labels.lblNewCronJob);
    if (id) {
        $.ajax({
            type: 'POST',
            url: CRONJOBS_CTRL,
            data: {
                action: 'R',
                part: 'L',
                id,
            },
            dataType: 'json',
            success: function (response) {
                var data = response.data[0] || [];
                $('#idScript').val(data.script || '');
                $('#idSchedule').val(data.schedule || '');
                $('#idEnabled').prop('checked', data.enabled == 1 || 0);
                $('#idProtected').prop('checked', data.protected == 1) || 0;
            },
        });
    }
    $('#modalCronjob').modal('show');
});

$(document).on('click', '[id^=labelDelete_]', function (e) {
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
                    deleteCronjob(id);
                },
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ],
    });
});

function deleteCronjob(id) {
    $.ajax({
        type: 'post',
        url: CRONJOBS_CTRL,
        data: {
            action: 'D',
            part: 'L',
            id,
        },
        dataType: 'json',
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
            reloadTable();
        },
    });
}

$('#cronjobSave').click(function (e) {
    e.preventDefault();

    var validation = true;
    var data = $('#modalCronjob input').serializeObject();
    data.id = $('#modalCronjob').data('id');
    data.action = 'C';
    data.part = 'C';

    $('#modalCronjob input').removeClass('is-invalid');
    if (data.elScript == '') {
        validation = false;
        $('#idScript').addClass('is-invalid');
    }
    if (data.elSchedule == '') {
        validation = false;
        $('#idSchedule').addClass('is-invalid');
    }

    if (validation) {
        $.ajax({
            type: 'POST',
            url: CRONJOBS_CTRL,
            data,
            dataType: 'json',
            success: function (response) {
                if (response.result) {
                    var text = response ? labels.nteCreateSuccess : labels.nteUpdateSuccess;
                    alertNotify({
                        type: 'success',
                        text,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    $('#modalCronjob').modal('hide');
                    reloadTable();
                } else {
                    var text = response ? labels.nteCreateError : labels.nteUpdateError;
                    alertNotify({
                        type: 'danger',
                        text,
                        icon: 'fas fa-exclamation-triangle',
                        timeout: 3000,
                    });
                }
            },
        });
    }
});

function reloadTable() {
    columns = [];
    tableCronjobs.destroy();
    $('#tableCronjobs').empty();
    loadTableCronjobs();
}

function clearCronjobForm() {
    $('#modalCronjob').data('id', 0);
    $('#idScript').val('');
    $('#idSchedule').val('');
    $('#idEnabled').prop('checked', false);
    $('#idProtected').prop('checked', false);
    $('#idScript').removeClass('is-invalid');
    $('#idSchedule').removeClass('is-invalid');
}
