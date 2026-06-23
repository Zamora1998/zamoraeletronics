var DateTime = luxon.DateTime;
var GROUPS_CTRL = '/' + chrLocale + '/controller/posts';

var tablePosts = jQuery("#tablePosts").DataTable({
    responsive: true,
    fixedHeader: true,
    paging: false,
    scrollY: 'calc(100vh - 330px)',
    language: {
        "url": TABLELANG
    },
    columnDefs: [
    ],
    "ajax": {
        "url": GROUPS_CTRL,
        "type": "POST",
        "dataSrc": "data.data",
        "data": function (d) {
            d.action = 'R',
                d.part = 'A'
        }
    },
    dom: '<"row"<"#ctrlCustom.col-sm-12 col-md-6"><"col-sm-12 col-md-6"f>>ti',
    /*buttons: [
        'copy', 'excel', 'pdf'
    ],*/
    columns: [
        {
            title: labels.tblName,
            data: "name"
        }, // 0
        {
            title: labels.tblLink,
            data: "uuid",
            render: function (data, type, row) {
                var aclass = '';
                if (!parseInt(row.enabled)) {
                    aclass = 'pe-none link-secundary';
                }
                return '<a ' + aclass + 'href="//trail.discoveryadventurecr.com/post/' + data + '" target="_blank">https://trail.discoveryadventurecr.com/post/' + data + '</a><i id="postLink_' + row.id + '" class="ms-1 link-primary far fa-clone" role="button" title="' + labels.btnCopyLink + '" data-link="https://trail.discoveryadventurecr.com/post/' + data + '"></i>'
            }
        }, //1
        {
            title: 'Distancias KM',
            data: "distances",
        }, // 2
        {
            title: labels.tblEnabled,
            data: "enabled",
            render: function (data, type, row) {
                return cellRender({
                    type: 'checkbox',
                    data,
                    id: 'postCheck_' + row.id,
                    class: 'form-check-input',
                    dataTags: {
                        id: row.id
                    }
                })
            }
        }, // 2
        {
            title: 'Marca de Salida',
            data: "Mark",
            className: "dt-center",
            render: function (data, type, row) {

                if (data == 1) {
                    return '<span style="color:green;font-weight:bold;">✔</span>';
                }

                return '<span style="color:red;font-weight:bold;">✖</span>';
            }
        }, // 2
                {
            title: 'Permite multiples escaneos',
            data: "allow_multiple_scans",
            className: "dt-center",
            render: function (data, type, row) {

                if (data == 1) {
                    return '<span style="color:green;font-weight:bold;">✔</span>';
                }

                return '<span style="color:red;font-weight:bold;">✖</span>';
            }
        }, // 2
        {
            title: labels.tblActions,
            orderable: false,
            searchable: false,
            data: "id",
            className: "dt-right",
            render: function (data, type, row) {
                return cellRender({
                    type: "dropdown",
                    data,
                    text: labels.tblActions,
                    dataTags: {
                        key: data,
                        type: row.type
                    },
                    listItems: [
                        {
                            id: 'postLink_' + data,
                            text: labels.btnCopyLink,
                            icon: 'far fa-clone',
                            dataTags: {
                                id: data,
                                link: 'https://trail.discoveryadventurecr.com/post/' + data
                            },
                        },
                        {
                            id: 'postEdit_' + data,
                            text: labels.btnEdit,
                            icon: 'far fa-edit',
                            dataTags: {
                                id: data
                            },
                        },
                        {
                            id: 'AssingRoutes_' + data,
                            text: labels.btnAssignRoutes,
                            icon: 'far fa-edit',
                            dataTags: {
                                id: data
                            },
                        },
                        {
                            id: 'postDelete_' + data,
                            text: labels.btnDelete,
                            icon: 'far fa-trash-alt',
                            dataTags: {
                                id: data
                            },
                        }
                    ]
                })
            }
        }, // 3
    ],
    drawCallback: function () {
        var html = '<div class="input-post">' +
            '<!--<input id="postName" type="text" class="form-control form-control-sm" placeholder="' + labels.lblOrganizationName + '" aria-label="' + labels.lblOrganizationName + '" aria-describedby="postCreate" maxlength="128" />-->' +
            '<button id="postCreate" type="button" class="btn btn-sm btn-success">' + labels.btnNew + '</button>' +
            '</div>';
        $('#ctrlCustom').html(html);
        if (!document.getElementById('idDistancesSelect')) {
            let calDistances = document.createElement('div');
            calDistances.id = 'idDistancesSelect';
            calDistances.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

            let selectDistances = document.createElement('select');
            selectDistances.id = 'selectDistances';
            selectDistances.classList.add('form-control', 'form-control-sm');
            selectDistances.name = 'Distances';
            selectDistances.setAttribute('multiple', 'multiple'); // MULTISELECT

            // Insertar opciones
            distances.forEach(distance => {
                let option = document.createElement('option');
                option.value = distance.Id;
                option.textContent = distance.text;
                selectDistances.appendChild(option);
            });

            calDistances.appendChild(selectDistances);
            document.getElementById('containerDistances').appendChild(calDistances);

            // ✔ Select2 correctamente aplicado al select correcto
            $('#selectDistances').select2({
                theme: 'bootstrap-5',
                placeholder: labels.lblselectDistances,
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalAssignRoutes'),
            });
        }
    }
});


$(document).on('click', '[id^="AssingRoutes_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#modalAssignRoutes').data('id', id);

    if (id) {
        $.ajax({
            type: "POST",
            url: GROUPS_CTRL,
            data: {
                action: 'R',
                part: 'CF',
                id
            },
            dataType: "json",
            success: function (response) {
                let dataArray = response.data.data || [];
                let data = dataArray[0] || {};

                let routeIdsArr = [];

                if (data.RouteIds != null) {
                    let routeIdsStr = String(data.RouteIds);

                    if (routeIdsStr.includes('-')) {
                        routeIdsArr = routeIdsStr
                            .split('-')
                            .map(id => id.trim())
                            .filter(id => id.length > 0);
                    } else {
                        routeIdsArr = [routeIdsStr.trim()];
                    }
                }
                $('#selectDistances').val(routeIdsArr).trigger('change.select2');
            }
        });
    }
    $('#modalAssignRoutes').modal('show');
});


$(document).on('click', '#postCreate', function (e) {
    e.preventDefault();
    $('#modalPost').data('id', 0);
    clearPostForm();
    $('#modalpost') > $('h1').text(labels.lblNewPost);
    $('#modalPost').modal('show');
});

$(document).on('click', '[id^=postEdit_]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#modalPost').data('id', id);
    clearPostForm();
    $('#modalpost') > $('h1').text(labels.lblEditPost);
    $.ajax({
        type: "post",
        url: GROUPS_CTRL,
        data: {
            action: 'R',
            part: 'P',
            id
        },
        dataType: "json",
        success: function (response) {
            var data = response.data.data[0];
            $('#grName').val(data.name);
            $('#grEnabled').prop('checked', parseInt(data.enabled));
            $('#modalPost').modal('show');
        }
    });
});

$(document).on('click', '[id^=postDelete_]', function (e) {
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
                    deletePost(id);
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

function deletePost(id) {
    $.ajax({
        type: "post",
        url: GROUPS_CTRL,
        data: {
            action: 'D',
            part: 'P',
            id,
        },
        dataType: "json",
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
            tablePosts.ajax.reload(false);
        }
    });
}

$(document).on('change', 'input:checkbox[id^=postCheck_]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var enabled = + $(this).is(':checked');

    $.ajax({
        type: "post",
        url: GROUPS_CTRL,
        data: {
            action: 'U',
            part: 'P',
            id,
            enabled,
        },
        dataType: "json",
        success: function (response) {
            tablePosts.ajax.reload(false);
        }
    });
});

$(document).on('click', '#postSave', function (e) {
    e.preventDefault();
    var id = $('#modalPost').data('id');
    var values = $('#modalPost input, #modalPost select').serializeObject();
    values.id = id;
    values.action = 'C';
    values.part = '';

    $.ajax({
        type: "post",
        url: GROUPS_CTRL,
        data: values,
        dataType: "json",
        success: function (response) {
            if (response.data.result) {
                var text = response.data.new ? labels.nteCreateSuccess : labels.nteUpdateSuccess;
                alertNotify({
                    type: 'success',
                    text,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                $('#modalPost').modal('hide');
                tablePosts.ajax.reload(false);
            } else {
                var text = response.data.new ? labels.nteCreateError : labels.nteUpdateError;
                alertNotify({
                    type: 'danger',
                    text,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            }
        }
    });
});

$(document).on('click', '[id^=postLink_]', function () {
    navigator.clipboard.writeText($(this).data('link'));
    alertNotify({
        type: 'info',
        text: labels.nteLinkCopied,
        icon: 'fas fa-info',
        timeout: 3000,
    });
});

function clearPostForm() {
    $('#grName').val('');
    $('#grEnabled').prop('checked', false);
}
