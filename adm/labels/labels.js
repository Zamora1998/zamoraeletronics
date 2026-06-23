var LABELS_CTRL = '/' + chrLocale + '/controller/labels';

var tableLabels;
var tableData;
var columns = [];
loadTable();

function loadTable() {
    $.ajax({
        type: 'POST',
        url: LABELS_CTRL,
        data: {
            action: 'R',
            part: 'A',
        },
        dataType: 'json',
        success: function (response) {
            var data = response.data;
            tableData = data.data;
            var columnNames = data.languages.map(function (el) { return el.name; });
            var columnData = Object.keys(data.data[0]);
            for (var i in columnData) {
                if (i > 0) {
                    if (i < 2) {
                        columns.push({
                            data: columnData[i],
                            title: labels.tblName,
                        });
                    } else {
                        columns.push({
                            data: columnData[i],
                            title: columnNames[i - 2],
                            render: function (data, type, row) {
                                if (data == null) {
                                    data = '';
                                }
                                return data.length > 50 ? data.substring(0, 50) + ' ...' : data;
                            },
                        });
                    }
                }
            }
            columns.push({
                title: labels.tblActions,
                data: columnData[0],
                searchable: false,
                sortable: false,
                vivible: true,
                width: '30px',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'dropdown',
                        data,
                        text: labels.tblActions,
                        dataTags: {
                            id: data,
                        },
                        listItems: [
                            {
                                id: 'labelEdit_' + data,
                                text: labels.btnEdit,
                                icon: 'far fa-edit',
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: 'labelDelete_' + data,
                                text: labels.btnDelete,
                                icon: 'far fa-trash-alt',
                                dataTags: {
                                    id: data,
                                },
                            },
                        ],
                    });
                },
            });
            tableLabels = $('#tableLabels').DataTable({
                dom: 'P<"row"<"#ctrlCustom.col-sm-12 col-md-6"><"col-sm-12 col-md-6"f>>ti',
                destroy: true,
                data: tableData,
                columns: columns,
                responsive: true,
                fixedHeader: true,
                paging: false,
                scrollY: 'calc(100vh - 261px)',
                language: {
                    url: TABLELANG,
                },
                drawCallback: function () {
                    var html = '<div class="input-group">' +
                        '<button id="labelCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' + labels.btnNew + '</button>' +
                        '</div>';
                    $('#ctrlCustom').html(html);
                },
            });
        },
    });
}

function reloadTable() {
    columns = [];
    tableLabels.destroy();
    $('#tableLabels').empty();
    loadTable();
}

$(document).on('click', '[id^="labelEdit_"],#labelCreate', function () {
    var id = $(this).data('id');
    clearLabelForm();
    $('#modalLabel').data('id', id);
    $('#modalLabel h1').text(id ? labels.lblEditLabel : labels.lblNewLabel);

    if (id) {
        $.ajax({
            type: 'POST',
            url: LABELS_CTRL,
            data: {
                action: 'R',
                part: 'L',
                id,
            },
            dataType: 'json',
            success: function (response) {
                var data = response.data.data[0];
                $('#laName').val(data.name);
                $('[id^="description_"]').each(function (index, element) {
                    $(element).val(data[element.id]);
                });
            },
        });
    } else {
        const searchValue = $('#tableLabels_filter input[type="search"]').val().trim();
        if (searchValue.length >= 1) {
            $('#laName').val(searchValue);
        }
    }
    $('#modalLabel').modal('show');
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
                    deleteLabel(id);
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

function deleteLabel(id) {
    $.ajax({
        type: 'post',
        url: LABELS_CTRL,
        data: {
            action: 'D',
            part: 'L',
            id,
        },
        dataType: 'json',
        success: function (response) {
            var data = response.data;
            if (data.result) {
                if (data.affected) {
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
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteError + '<br>' + data.error,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
            reloadTable();
        },
    });
}

$('#labelSave').click(function (e) {
    e.preventDefault();
    var validation = true;
    $('#laName').removeClass('is-invalid');
    var id = $('#modalLabel').data('id');
    var values = $('#modalLabel input, #modalLabel textarea').serializeObject();
    if (values.laName == '') {
        $('#laName').addClass('is-invalid');
        validation = false;
    }
    values.id = id;
    values.action = 'C';
    values.part = 'L';
    if (validation) {
        $.ajax({
            type: 'post',
            url: LABELS_CTRL,
            data: values,
            dataType: 'json',
            success: function (response) {
                if (response.data.result) {
                    alertNotify({
                        type: 'success',
                        text: labels.nteUpdateSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    $('#modalLabel').modal('hide');
                    reloadTable();
                } else {
                    alertNotify({
                        type: 'danger',
                        text: labels.nteError,
                        icon: 'fas fa-exclamation-triangle',
                        timeout: 3000,
                    });
                }
            },
        });
    }
});

function clearLabelForm() {
    $('#modalLabel').data('id', 0);
    $('#laName').val('');
    $('[id^="description_"]').each(function (index, element) {
        $(element).val('');
    });
}
