var ACCESS_CTRL = '/' + chrLocale + '/controller/access';
var CTRL_COMPLABELS = '/' + chrLocale + '/controller/cLabels';

var columns = [];
var TableAccess;
var DateTime = luxon.DateTime;
var label;

$(document).ready(function () {
    TableAccess = $('#tableAccess').DataTable({
        ajax: {
            url: ACCESS_CTRL,
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
                title: labels.tblName,
                data: 'name',
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 0
            {
                title: labels.tblDescription,
                data: 'description',
                orderable: false,
                className: 'dt-left dt-nowrap',
                visible: true,
            }, // 0
            {
                title: labels.tblLabelID,
                data: 'id',
                orderable: false,
                className: 'dt-left dt-nowrap',
                visible: false,
            },
            {
                title: labels.tblActions,
                data: 'text',
                className: 'dt-right',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'dropdown',
                        data,
                        text: labels.btnActions,
                        dataTags: {
                            id: data,
                            access: row.text,
                            num: row.id,
                        },
                        listItems: [
                            {
                                id: 'AccessEdit_' + data,
                                text: labels.btnEdit,
                                icon: 'far fa-edit',
                                dataTags: {
                                    id: data,
                                    access: row.text,
                                    num: row.id,
                                },
                            },
                            {
                                id: 'AccessDelete_' + data,
                                text: labels.btnDelete,
                                icon: 'far fa-trash-alt',
                                dataTags: {
                                    id: data,
                                    access: row.text,
                                    num: row.id,
                                },
                            },
                        ],
                    });
                },
            },
        ],
        dom: '<"row"<"#ctrlAccessCustom.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: 'calc(100vh - 345px)',
        drawCallback: function () {
            $('#ctrlAccessCustom').html(
                '<button id="CreateAccess" type="button" class="btn btn-sm btn-success me-1" data-id="" data-num="0">' + labels.btnNew + '</button>',
            );

            if ($('#idDivInputs').length) return;

            let inputs = $('<div>', {
                id: 'idDivInputs',
                class: 'col-md-12 col-lg-9 mx-auto mb-3',
                css: { paddingTop: '10px' },
            });

            let rowName = $('<div>', { class: 'row mb-3' });
            rowName.append(createSelect2('Select2Name', labels.lblAccessName, $('#modalAccess')));

            let rowDesc = $('<div>', { class: 'row mb-3' });
            rowDesc.append(createSelect2('Select2Description', labels.lblDescription, $('#modalAccess')));

            inputs.append(rowName, rowDesc);
            $('#containerAccess').append(inputs);
        },
    });
});

function createSelect2(id, placeholder, dropdownParent) {
    let col = $('<div>', { class: 'col-12' });
    let flex = $('<div>', { class: 'input-group select2-with-button' });

    let select = $('<select>', {
        id: id,
        name: id,
        class: 'form-control form-control-sm selectAccess',
    }).append($('<option>', { value: '', text: labels.lblSelectOption }));

    // ⬇ Crear botón + sin id fijo
    let button = $('<button>', {
        type: 'button',
        class: 'btn btn-outline-primary btn-sm farfaplus', // clase para seleccionar
        html: '<i class="fas fa-plus"></i>',
        'data-target': id, // Add data-target to identify which select this button belongs to
    });

    flex.append(select, button);
    col.append(flex);

    select.select2({
        theme: 'bootstrap-5',
        selectionCssClass: 'select2--small',
        dropdownCssClass: 'select2--small',
        dropdownParent: dropdownParent,
        minimumInputLength: 4,
        allowClear: true,
        placeholder: placeholder,
        delay: 250,
        ajax: {
            url: ACCESS_CTRL,
            type: 'POST',
            data: (params) => ({
                action: 'R',
                part: 'L',
                term: params.term,
            }),
            processResults: (data) => ({ results: data.data }),
        },
        templateSelection: (data) => data.id || data.text,
    });

    return col;
}
$(document).on('click', '[id^="AccessEdit_"],#CreateAccess', function (e) {
    e.preventDefault();

    var id = $(this).data('id');
    var access = $(this).data('access');
    var num = $(this).data('num');

    $('#modalAccess').data('id', id);
    $('#modalAccess').data('access', access);
    $('#modalAccess').data('num', num);

    $('#h1Access').text(id ? labels.btnEdit + ' - ' + labels.btnAccess : labels.lblnewAccess);

    var $selectName = $('#Select2Name');
    var $selectDesc = $('#Select2Description');

    $.ajax({
        type: 'POST',
        url: ACCESS_CTRL,
        data: { action: 'R', part: 'S', id: access, num: num },
        dataType: 'json',
        success: function (response) {
            if (response.data && response.data.length > 0) {
                var data = response.data[0];
                $('#modalAccess').attr('data-num', data.num);

                var cleanName = data.id.replace(/\s+/g, '').replace(/N\/A/gi, '');
                var cleanDesc = data.description ? data.description.replace(/\s+/g, '') : '';

                // Set Select2
                $selectName
                    .empty()
                    .append($('<option>', { value: cleanName, text: data.text, selected: true }))
                    .trigger('change');
                $selectDesc
                    .empty()
                    .append($('<option>', { value: cleanDesc, text: data.description || '', selected: true }))
                    .trigger('change');
                $('.farfaplus[data-target="Select2Name"]').data('id', cleanName);
                $('.farfaplus[data-target="Select2Description"]').data('id', cleanDesc);
            } else {
                $('#modalAccess').attr('data-num', 0);
                $('#modalAccess').attr('data-access', '');

                $selectName
                    .empty()
                    .append($('<option>', { value: '', text: labels.lblSelectOption, selected: true }))
                    .trigger('change');
                $selectDesc
                    .empty()
                    .append($('<option>', { value: '', text: labels.lblSelectOption, selected: true }))
                    .trigger('change');

                // Limpiar botones +
                $('.farfaplus').data('id', '');
            }
        },
    });

    $('#modalAccess').modal('show');
});

$(document).on('click', '#AccessSave', function (e) {
    e.preventDefault();
    let data = $('#modalAccess select').serializeObject();
    data.id = $('#modalAccess').data('num');
    data.action = 'C';
    data.part = 'A';
    let validation = true;

    $('#Select2Name, #Select2Description').removeClass('is-invalid');

    if (!data.Select2Name || data.Select2Name === '') {
        validation = false;
        $('#Select2Name').addClass('is-invalid');
    }

    if (!data.Select2Description || data.Select2Description === '') {
        validation = false;
        $('#Select2Description').addClass('is-invalid');
    }

    if (!validation) return;

    $.ajax({
        type: 'POST',
        url: ACCESS_CTRL,
        data: data,
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
                    type: 'warning',
                    text: labels.nteError,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            }

            $('#modalAccess').modal('hide');
            TableAccess.ajax.reload();
        },
    });
});

// Marca un Select2 como inválido
function markInvalid(selectId) {
    $('#' + selectId).addClass('is-invalid');
    $('#' + selectId)
        .next('.select2-container')
        .find('.select2-selection')
        .addClass('is-invalid');
}

// Quita la clase de error de un Select2
function removeInvalid(selectId) {
    $('#' + selectId).removeClass('is-invalid');
    $('#' + selectId)
        .next('.select2-container')
        .find('.select2-selection')
        .removeClass('is-invalid');
}

$(document).on('click', '[id^=AccessDelete_]', function (e) {
    e.preventDefault();
    var id = $(this).data('num');
    $.ajax({
        type: 'POST',
        url: ACCESS_CTRL,
        data: {
            action: 'D',
            part: 'A',
            id,
        },
        dataType: 'json',
        success: function (response) {
            if (response.result) {
                if (response.data.is_configured) {
                    alertNotify({
                        type: 'warning',
                        text: labels.nteDeleteWarnUsed,
                        icon: 'fas fa-exclamation',
                        buttons: [
                            {
                                type: 'warning',
                                text: labels.btnYes,
                                icon: 'fas fa-check',
                                callback: function () {
                                    deleteAcess(id);
                                },
                            },
                            {
                                type: 'danger',
                                text: labels.btnNo,
                                icon: 'fas fa-times',
                            },
                        ],
                    });
                } else {
                    alertNotify({
                        type: 'warning',
                        text: labels.nteDeleteWarnUnused,
                        icon: 'fas fa-exclamation',
                        buttons: [
                            {
                                type: 'primary',
                                text: labels.btnYes,
                                icon: 'fas fa-check',
                                callback: function () {
                                    deleteAcess(id);
                                },
                            },
                            {
                                type: 'danger',
                                text: labels.btnNo,
                                icon: 'fas fa-times',
                            },
                        ],
                    });
                }
            }
        },
    });
});

function deleteAcess(id) {
    $.ajax({
        type: 'POST',
        url: ACCESS_CTRL,
        data: {
            action: 'D',
            part: 'D',
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
            TableAccess.ajax.reload();
        },
    });
}

$(document).on('click', '.farfaplus', function (e) {
    e.preventDefault();

    var $currModal = $('.modal.show').last();
    $currModal.addClass('modal-blurred');
    var $newModal = $('#modallabelsComponent');
    var idToPass = $(this).data('id') || '';
    $('#h1modalLabelCom').text(idToPass ? labels.btnEdit + ' - ' + labels.lblLabel : labels.lblNewLabel);

    $('#laNameLabel').val(idToPass).trigger('input');

    if (idToPass) {
        $.ajax({
            type: 'POST',
            url: CTRL_COMPLABELS,
            data: {
                action: 'R',
                part: 'D',
                id: idToPass,
            },
            dataType: 'json',
            success: function (response) {
                var data = response.data[0] || {};
                $('#laNameLabel')
                    .val(data.id || '')
                    .trigger('input');

                $('#modallabelsComponent').attr('data-id', data.id || '');

                $('[id^="Compdescription_"]').each(function () {
                    var lang = this.id.replace('Compdescription_', '');
                    var value = data['description_' + lang] || '';
                    $(this).val(value).trigger('input');
                });
            },
        });
    } else {
        $('#laNameLabel').val('').trigger('input');
        $('[id^="Compdescription_"]').each(function () {
            $(this).val('').trigger('input');
        });
        $('#modallabelsComponent').attr('data-id', '');
    }

    $newModal.addClass('modal-stack').modal('show');
    $('.modal-backdrop').not('.modal-backdrop-stack').last().addClass('modal-backdrop-stack');

    $newModal.off('hidden.bs.modal').on('hidden.bs.modal', function () {
        $newModal.removeClass('modal-stack');
        $('.modal-backdrop-stack').removeClass('modal-backdrop-stack');
        $currModal.removeClass('modal-blurred');

        // Limpiar inputs y data-id al cerrar
        $('#laNameLabel').val('').trigger('input');
        $('[id^="Compdescription_"]').each(function () {
            $(this).val('').trigger('input');
        });
        $('#modallabelsComponent').attr('data-id', '');
    });
});

$('#nav-Access-tab').on('shown.bs.tab', function (e) {
    $(window).resize();
    //tableDataSuppliers.ajax.reload();
});

$('#modalAccess').on('hidden.bs.modal', function () {
    $(this).data('id', '');
    $(this).data('access', '');
    $(this).data('num', '');

    $(this).attr('data-id', '');
    $(this).attr('data-access', '');
    $(this).attr('data-num', '');

    $('#Select2Name, #Select2Description').val(null).trigger('change');
});
