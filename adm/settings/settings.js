var SETTINGS_CTRL_URL = '/' + chrLocale + '/controller/settings';
var DateTime = luxon.DateTime;

var documentDzSettings = loadDrop();
Dropzone.autoDiscover = false;
var docOldReadyUp = false;
var myDropzone;

var types = [
    { id: 'B', text: 'Bool' },
    { id: 'C', text: 'Color' },
    { id: 'D', text: 'Date' },
    { id: 'P', text: 'Password' },
    { id: 'T', text: 'Text' },
    { id: 'U', text: 'Upload' },
];

loadTableSettings();

function loadTableSettings() {
    tableSettings = $('#tableSettings').DataTable({
        ajax: {
            url: SETTINGS_CTRL_URL,
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
                title: labels.tblKey,
                data: 'key',
                orderable: true,
                className: 'dt-left dt-nowrap',
            }, // 0
            {
                title: labels.tblValue,
                data: 'value',
                orderable: true,
                className: 'dt-left dt-nowrap',
                render: function (data, type, row) {
                    if (row.type == 'C') {
                        return cellRender({
                            type: 'color',
                            data,
                        });
                    } else if (row.type == 'D') {
                        return dateSqlToShort(data, chrLocale); // Usar la función existente
                    } else if (row.type == 'P') {
                        return '****************';
                    } else if (row.type == 'B') {
                        return cellRender({
                            type: 'check',
                            data,
                            id: 'settingsCheck_' + row.key,
                        });
                    } else {
                        return data;
                    }
                },
            }, // 1
            {
                title: labels.tblType,
                data: 'type',
                searchable: false,
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 2
            {
                title: labels.tblActions,
                orderable: false,
                searchable: false,
                data: 'key',
                className: 'dt-right',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'dropdown',
                        data,
                        text: labels.tblActions,
                        dataTags: {
                            key: data,
                            type: row.type,
                        },
                        listItems: [
                            {
                                id: 'settingsEdit_' + data,
                                text: labels.btnEdit,
                                icon: 'far fa-edit',
                                dataTags: {
                                    key: data,
                                    type: row.type,
                                },
                            },
                            {
                                id: 'settingsDelete_' + data,
                                text: labels.btnDelete,
                                icon: 'far fa-trash-alt',
                                dataTags: {
                                    key: data,
                                    type: row.type,
                                },
                            },
                        ],
                    });
                },
            }, //3
        ],
        dom: '<"row"<"#ctrlCustom.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        paging: false,
        scrollX: false,
        scrollY: 'calc(100vh - 263px)',
        drawCallback: function () {
            var html = '<button id="settingsCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' + labels.btnNew + '</button>';
            $('#ctrlCustom').html(html);
        },
        initComplete: function () {},
    });
}

$('#sectionPassword').on('click', '#review', function (event) {
    event.preventDefault();
    var input = $('#repass');
    var type = input.attr('type');
    if (type === 'password') {
        input.attr('type', 'text');
        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

$(document).on('click', '[id^="settingsDelete_"]', function (e) {
    e.preventDefault();
    var sKey = $(this).data('key');
    var sType = $(this).data('type');
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
                    deleteSettings(sKey, sType);
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

function deleteSettings(sKey, sType) {
    $.ajax({
        type: 'POST',
        url: SETTINGS_CTRL_URL,
        data: {
            action: 'D',
            part: 'S',
            sKey,
            sType,
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
                reloadTable();
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteDeleteError,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
        },
    });
}

$(document).on('click', '[id^="settingsEdit_"],#settingsCreate', function (e) {
    e.preventDefault();
    clearCategoryForm();
    var sKey = $(this).data('key');
    $('#modalSettings').modal('show');

    if (sKey) {
        $.ajax({
            url: SETTINGS_CTRL_URL,
            type: 'POST',
            dataSrc: 'data',
            data: {
                action: 'R',
                part: 'S',
                sKey,
            },
            dataType: 'json',
            success: function (response) {
                var data = response.data[0];
                var sKey = data.key;
                var sType = data.type;
                var sVal = data.value;
                loadForm(sKey, sVal, sType);
            },
        });
    } else {
        loadForm('', '', '0', true);
    }
});

function loadForm(sKey, sVal, sType, isNew) {
    $('#sectionNew').show();
    clearCategoryForm();

    $('#settingsColor').spectrum({
        showAlpha: true,
        preferredFormat: 'rgb',
        showInput: true,
        type: 'text',
        color: 'rgb(0, 0, 0)',
        showInitial: true,
    });

    $('#selectSettingsType').select2({
        data: types,
        theme: 'bootstrap-5',
        selectionCssClass: 'select2--small',
        dropdownCssClass: 'select2--small',
        dropdownParent: modalSettings,
    });

    $('#sectionText, #sectionCheck, #sectionColor, #sectionFile, #sectionDate, #sectionPassword').hide();
    $('input[id^="settings"]').val('');
    $('input[id^="settings"]').removeClass('is-invalid');
    $('#settingsKey').val(sKey);
    $('#settingsSave').data('key', sKey);
    $('#settingsSave').data('type', sType);
    $('#settingsSave').data('value', sVal);
    $('#selectSettingsType').prop('disabled', !isNew);
    $('#settingsKey').prop('disabled', !isNew);
    //$('#settingsSave').prop("disabled", true);

    switch (sType) {
        case '':
            $('#sectionNew').show();
            $('#selectSettingsType').val(null).trigger('change');
            break;
        case 'B':
            $('#settingCheck').prop('checked', parseInt(sVal) === 1);
            $('#sectionCheck').show();
            break;
        case 'D':
            $('#sectionDate').show();
            var DateFormat = dateSqlToShort(sVal, chrLocale);
            $('.input-group.date input').val(DateFormat);
            $('.input-group.date').datepicker('update', DateFormat);
            break;
        case 'T':
            $('#settingsText').val(sVal);
            $('#sectionText').show();
            break;
        case 'C':
            $('#settingsColor').spectrum('set', sVal);
            $('#sectionColor').show();
            break;
        case 'P':
            $('#repass').val(sVal);
            $('#sectionPassword').show();
            break;
        case 'U':
            $('#sKey').val(sKey);
            $('#selectSettingsType').val(sType).trigger('change');
            docOldReadyUp = false;
            documentDzSettings.removeAllFiles();
            $('#sectionFile').show();
            var filename = sVal;
            if (filename != '') {
                docOldReadyUp = true;
                var objFile = {
                    filename,
                    status: 'success',
                    accepted: true,
                };
                documentDzSettings.files.push(objFile);
                documentDzSettings.emit('addedfile', objFile);
                documentDzSettings.emit('thumbnail', objFile, filename);
                documentDzSettings.emit('complete', objFile);
            }
            break;
    }
    $('#selectSettingsType').val(sType).trigger('change');
    $('#modalSettings').modal('show');
}

$('#selectSettingsType').on('change', function () {
    var selectedType = $(this).val();
    updateFormFields(selectedType);
});

function updateFormFields(type) {
    $('#sectionText, #sectionCheck, #sectionColor, #sectionFile, #sectionDate, #sectionPassword').hide();
    switch (type) {
        case 'T':
            $('#sectionText').show();
            break;
        case 'D':
            $('#sectionDate').show();
            break;
        case 'C':
            $('#sectionColor').show();
            break;
        case 'U':
            $('#sectionFile').show();
            break;
        case 'P':
            $('#sectionPassword').show();
            break;
        case 'B':
            $('#sectionCheck').show();
            break;
    }
}

function loadDrop() {
    myDropzone = new Dropzone('form#dropSettings', {
        url: SETTINGS_CTRL_URL,
        uploadMultiple: false,
        maxFiles: 1,
        acceptedFiles: '.jpg, .png, .svg',
        dictCancelUpload: null,
        thumbnailHeight: 120,
        thumbnailWidth: 120,
        clickable: ['#addLogo', '.dz-clickable'],
        previewTemplate: document.querySelector('#template-container').innerHTML,
        autoProcessQueue: false, // Disable auto-send
        accept: function (file, dom) {
            if (docOldReadyUp) {
                dom(labels.lblfileUploadFailed);
            } else {
                dom();
            }
        },
        init: function () {
            this.on('complete', function (response) {
                if (response.xhr) {
                    docOldReadyUp = true;
                    var text = response ? labels.nteCreateSuccess : labels.nteUpdateSuccess;
                    alertNotify({
                        type: 'success',
                        text,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    $('#modalSettings').modal('hide');
                }
            });
            this.on('error', function () {
                var text = response ? labels.nteCreateError : labels.nteUpdateError;
                alertNotify({
                    type: 'danger',
                    text,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            });

            this.on('removedfile', function (dpresponse) {
                var sKey = $('#settingsSave').data('key');
                if (!dpresponse.accepted || !docOldReadyUp) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: SETTINGS_CTRL_URL,
                    data: {
                        action: 'D',
                        part: 'F',
                        sKey,
                        fileName: dpresponse.name, // Send the name of file to server
                    },
                    success: function (result) {
                        if (result['result']) {
                            alertNotify({
                                type: 'success',
                                text: labels.nteDeleteSuccess,
                                icon: 'fas fa-check',
                                timeout: 3000,
                            });
                            docOldReadyUp = false;
                        } else {
                            alertNotify({
                                type: 'warning',
                                text: labels.nteDeleteError,
                                icon: 'fas fa-check',
                                timeout: 3000,
                            });
                        }
                        if ($('.dz-preview').length) {
                            $('#divDropSettings').addClass('dz-clickable');
                        } else {
                            $('#divDropSettings').removeClass('dz-started');
                        }
                        $('#modalSettings').modal('hide');
                        reloadTable();
                    },
                });
            });
        },
    });
    return myDropzone;
}

$('#settingsSave').click(function (e) {
    e.preventDefault();
    var validation = true;
    var visibleFields = $('#modalSettings input:visible, #modalSettings select:visible');
    var data = visibleFields.serializeObject();
    data.action = 'C';
    data.part = 'C';

    if (data.elselectSettingsType == 'D' && data.elvalue == '') {
        validation = false;
        $('#calenStart').addClass('is-invalid');
    } else if (data.elselectSettingsType == 'D') {
        $('#calenStart').removeClass('is-invalid');
        data.elvalue = dateShortToSql(data.elvalue, chrLang);
    } else if (data.elselectSettingsType == 'U') {
        data.part = 'U';
        data.elvalue = '';
    } else if (data.elselectSettingsType == 'C') {
        var color = $('#settingsColor').spectrum('get').toRgb();
        data.elvalue = `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
    }

    if ((data.elselectSettingsType == 'P' && data.elvalue == '') || data.elselectSettingsType == '') {
        validation = false;
        if (data.elselectSettingsType == 'P') {
            $('#repass').addClass('is-invalid');
        }
    } else {
        if (data.elselectSettingsType == 'P') {
            $('#repass').removeClass('is-invalid');
        }
        if (data.elselectSettingsType == '') {
            $('#selectSettingsType').removeClass('is-invalid');
        }
    }

    if (data.elselectSettingsType == '') {
        $('#selectSettingsType').addClass('is-invalid');
    } else {
        $('#selectSettingsType').removeClass('is-invalid');
    }

    if (data.elkey == '') {
        validation = false;
        $('#settingsKey').addClass('is-invalid');
    } else {
        $('#settingsKey').removeClass('is-invalid');
    }

    if (validation) {
        if (data.elselectSettingsType == 'U' && myDropzone.getQueuedFiles().length > 0) {
            myDropzone.on('sending', function (file, xhr, formData) {
                $.each(data, function (key, value) {
                    formData.append(key, value);
                });
            });
            myDropzone.processQueue();
        } else {
            $.ajax({
                type: 'post',
                url: SETTINGS_CTRL_URL,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.result) {
                        var text = response ? labels.nteCreateSuccess : labels.nteUpdateSuccess;
                        alertNotify({
                            type: 'success',
                            text: text,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                        $('#modalSettings').modal('hide');
                    } else {
                        var text = response ? labels.nteCreateError : labels.nteUpdateError;
                        alertNotify({
                            type: 'danger',
                            text: text,
                            icon: 'fas fa-exclamation-triangle',
                            timeout: 3000,
                        });
                    }
                },
            });
        }
        reloadTable();
    }
});

function reloadTable() {
    columns = [];
    tableSettings.destroy();
    $('#tableSettings').empty();
    loadTableSettings();
}

function clearCategoryForm() {
    $('#settingsCheck').prop('checked', false).val('0');
    $('#modalSettings input').removeClass('is-invalid');
    $('#settingsKey').val('');
    $('#settingsText').val('');
    $('#selectSettingsType').removeClass('is-invalid');
    $('#settingsColor').val('');
    $('#repass').val('');
    $('#calenStart').val('');
    $('#selectSettingsType').val(null).trigger('change');
    $('#sectionText, #sectionCheck, #sectionColor, #sectionFile, #sectionDate, #sectionPassword').hide();
}

$('#calenStart').on('keyup', function () {
    var input = $('#calenStart').val();
    if (input.length === 6) {
        formattedDate = parseTinyDate(input);
        if (formattedDate !== false) {
            $('#calenStart').val(formattedDate);
            $('#input-group.date').datepicker('update', formattedDate);
        }
    }
    if (input.length === 8) {
        formattedDate = parseShortDate(input);
        if (formattedDate !== false) {
            $('#calenStart').val(formattedDate);
            $('#calenStart').datepicker('update', formattedDate);
        }
    }
});

$('.input-group.date').datepicker({
    language: chrLocale,
    daysOfWeekHighlighted: '0.6',
    clearBtn: true,
    autoclose: true,
    todayHighlight: true,
    orientation: 'bottom', //'auto', 'top', 'bottom', 'left', 'right',
    container: '#modalSettings',
    format: 'dd-M-yyyy', // Formato personalizado: día-mes abreviado-año
});
