var ROUTES_CTRL = '/' + chrLocale + '/controller/routes';

var tableRoutes = null;
var tableData;
var columns = [];
var methods = [
    { id: 'get', text: 'get' },
    { id: 'post', text: 'post' },
    { id: 'put', text: 'put' },
    { id: 'patch', text: 'patch' },
    { id: 'delete', text: 'delete' },
    { id: 'any', text: 'any' },
];

loadTableRoutes();

function loadTableRoutes() {
    tableRoutes = $('#tableRoutes').DataTable({
        ajax: {
            url: ROUTES_CTRL,
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
                data: 'label_name',
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 0
            {
                title: labels.tblType,
                data: 'type',
                orderable: true,
                searchable: false,
                className: 'dt-left dt-nowrap',
            }, // 1
            {
                title: labels.tblIcon,
                data: 'icon',
                orderable: true,
                searchable: false,
                visible: false,
                className: 'dt-left dt-nowrap',
            }, // 2
            {
                title: labels.tblUrl,
                data: 'url',
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 3
            {
                title: labels.tblFile,
                data: 'file',
                orderable: false,
                className: 'dt-left dt-nowrap',
            }, // 4
            {
                title: labels.tblPosition,
                data: 'position',
                orderable: true,
                searchable: false,
                className: 'dt-left dt-nowrap',
            }, // 5
            {
                title: labels.tblMethod,
                data: 'method',
                orderable: true,
                searchable: false,
                className: 'dt-left dt-nowrap',
            }, // 6
            {
                title: labels.tblIspublic,
                data: 'ispublic',
                orderable: true,
                searchable: false,
                className: 'dt-center dt-nowrap',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'checkbox',
                        data,
                        id: 'routeIsPublic_' + row.id,
                        class: 'form-check-input',
                        dataTags: {
                            id: row.id,
                        },
                    });
                },
            }, // 7
            {
                title: labels.tblIsalluser,
                data: 'isalluser',
                orderable: true,
                searchable: false,
                className: 'dt-center dt-nowrap',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'checkbox',
                        data,
                        id: 'routeAlluser_' + row.id,
                        class: 'form-check-input',
                        dataTags: {
                            id: row.id,
                        },
                    });
                },
            }, // 8
            {
                title: labels.tblCategory,
                data: 'category_name',
                orderable: true,
                searchable: false,
                visible: false,
                className: 'dt-left dt-nowrap',
            }, // 9
            {
                title: labels.tblParentCategory,
                data: 'parent_labelname',
                orderable: true,
                searchable: false,
                className: 'dt-left dt-nowrap',
                render: function (data, type, row) {
                    // Verifica si el dato es null o vacío
                    if (data === null || data.trim() === '') {
                        return '';
                    }
                    const truncatedData = data.length > 6 ? data.substring(0, 6) + '...' : data;
                    const tooltip = data.length > 6 ? data : '';
                    return `<span title="${tooltip}">${truncatedData}</span>`;
                },
            }, // 10
            /*
            {
                title: labels.tblChildCategory,
                data: "child_labelname",
                orderable: true,
                searchable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (data === null || data.trim() === '') {
                        return '';  
                    }
                    const truncatedData = data.length > 6 ? data.substring(0, 6) + '...' : data;
                    const tooltip = data.length > 6 ? data : '';
                    return `<span title="${tooltip}">${truncatedData}</span>`;
                }
            }, // 10
            */
            {
                title: labels.tblActions,
                orderable: false,
                searchable: false,
                data: 'id',
                className: 'dt-right',
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
                                id: 'routeEdit_' + data,
                                text: labels.btnEdit,
                                icon: 'far fa-edit',
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: 'editAccess_' + data,
                                text: labels.lblEditAccess,
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
            }, //11
        ],
        dom: '<"row"<"#ctrlCustom.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        paging: false,
        scrollX: false,
        scrollY: 'calc(100vh - 328px)',
        drawCallback: function () {
            var html = '<button id="routeCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' + labels.btnNew + '</button>';
            $('#ctrlCustom').html(html);
            if (!document.getElementById('idParentSelect2')) {
                let ServicesTselect = document.createElement('select');
                ServicesTselect.classList.add('form-control', 'form-control-sm');
                ServicesTselect.id = 'idParentSelect2';
                ServicesTselect.name = 'elcategoryParent';
                ServicesTselect.required = true;

                let defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.disabled = false;
                defaultOption.selected = true;
                defaultOption.textContent = labels.tblParentCategory;

                ServicesTselect.appendChild(defaultOption);
                // Agregar las opciones dinámicas
                routesNames.forEach(function (route) {
                    let option = document.createElement('option');
                    option.value = route.id;
                    option.textContent = route.text;
                    ServicesTselect.appendChild(option);
                });
                document.getElementById('containerSelect2Childs').appendChild(ServicesTselect);
            }
            $('#idParentSelect2').select2({
                maximumSelectionLength: 2, // Limitar la selección a 3 opciones
                theme: 'bootstrap-5',
                selectionCssClass: 'select2--small',
                dropdownCssClass: 'select2--small',
                minimumResultsForSearch: Infinity,
            });
        },
    });
}

$(document).on('click', '[id^="editAccess_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    clearRouteForm();
    $('#modalAccess').data('id', id);
    $('#modalAccess') > $('h1').text(id ? labels.lblEditAccess : labels.lblNewAccess);
    $('#modalAccess').modal('show');
    if (id) {
        $.ajax({
            type: 'post',
            url: ROUTES_CTRL,
            data: {
                action: 'R',
                part: 'E',
                id: id,
            },
            dataType: 'json',
            success: function (response) {
                if (response.result) {
                    var accessNames = response.data;
                    $('[id^="acParam_"]').val('');
                    $('[id^="acOptional_"]').prop('checked', false);
                    $('[id^="acRequired_"]').prop('checked', false);
                    accessNames.forEach(function callback(accessName, index) {
                        $('#acParam_' + accessName.id).val(accessName.param);
                        $('#acOptional_' + accessName.id).prop('checked', parseInt(accessName.optional));
                        $('#acRequired_' + accessName.id).prop('checked', parseInt(accessName.required));
                    });
                }
            },
        });
    }
});

$(document).on('change', 'input:checkbox[id^=routeIsPublic_]', function (e) {
    e.preventDefault();
    var ispublicid = $(this).data('id');
    var enabledCheck = document.getElementById('routeIsPublic_' + ispublicid).checked;
    var enabledValue = enabledCheck ? 1 : 0;
    if (ispublicid) {
        $.ajax({
            type: 'POST',
            url: ROUTES_CTRL,
            data: {
                action: 'U',
                part: 'I',
                id: ispublicid,
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
                        type: 'warning',
                        text: labels.nteError,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
                reloadTable();
            },
        });
    }
});

$(document).on('change', 'input:checkbox[id^=routeAlluser_]', function (e) {
    e.preventDefault();
    var isalluserid = $(this).data('id');
    var enabledCheck = document.getElementById('routeAlluser_' + isalluserid).checked;
    var enabledValue = enabledCheck ? 1 : 0;
    if (isalluserid) {
        $.ajax({
            type: 'POST',
            url: ROUTES_CTRL,
            data: {
                action: 'U',
                part: 'C',
                id: isalluserid,
                enabled: enabledValue,
            },
            dataType: 'json',
            success: function (response) {
                if (response.result) {
                    if (response.error === '') {
                        alertNotify({
                            type: 'success',
                            text: labels.nteUpdateSuccess,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    } else {
                        alertNotify({
                            type: 'warning',
                            text: labels.nteError + '<br>' + response.error,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    }
                } else {
                    alertNotify({
                        type: 'warning',
                        text: labels.nteError,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
                reloadTable();
            },
        });
    }
});

$(document).on('click', '[id^="routeEdit_"],#routeCreate', function (e) {
    e.preventDefault();
    clearRouteForm();
    var id = $(this).data('id');
    $('#modalRoutes').data('id', id);
    $('#modalRoutes h1').text(id ? labels.lblEditRoute : labels.lblNewRoute);

    $.ajax({
        type: 'post',
        url: ROUTES_CTRL,
        data: {
            action: 'R',
            part: 'S',
            id: id,
        },
        dataType: 'json',
        success: function (response) {
            var catego = response.categories;
            var data = response.data[0] || [];

            $('#idLabelname').val(data.label_name || '');
            $('#idType').val(data.type || 0);
            $('#idIcon').val(data.icon || '');
            $('#idUrl').val(data.url || '');
            $('#idFile').val(data.file || '');
            $('#numberInput').val(data.position || 0);
            $('#idAllusers').prop('checked', data.isalluser == 1 || 0);
            $('#idPublic').prop('checked', data.ispublic == 1 || 0);
            $('#idMethod').select2({
                data: methods,
                theme: 'bootstrap-5',
                selectionCssClass: 'select2--small',
                dropdownCssClass: 'select2--small',
                dropdownParent: modalRoutes,
            });

            $('#dropdownCat').html('');
            var container = document.getElementById('dropdownCat');
            var dropdownMenu = buildDropdownMenu(catego);
            var htmlContent = dropdownMenu.innerHTML;
            container.innerHTML = htmlContent;
            bootstrap.Dropdown.loadEvents();

            $('#idMethod')
                .val(data.method || 'get')
                .trigger('change');

            var selectedCategory = data.category_name || '/';
            var selectedCategoryId = data.routecategory_id || 0;
            $('#MenuButton')
                .html(selectedCategory + ' <span class="caret"></span>')
                .val(selectedCategoryId)
                .trigger('change');

            var parentId = data.parent_route_id || '';
            var parentText = data.parent_labelname || '';

            if ($('#idParentSelect2').length && parentId !== '') {
                if ($("#idParentSelect2 option[value='" + parentId + "']").length === 0) {
                    let newOption = new Option(parentText, parentId, true, true);
                    $('#idParentSelect2').append(newOption).trigger('change');
                } else {
                    $('#idParentSelect2').val(parentId).trigger('change');
                }
            }
        },
    });
    $('#modalRoutes').modal('show');
});

function buildDropdownMenu(items) {
    var divs = document.createElement('div');

    var button = document.createElement('button');
    button.setAttribute('type', 'button');
    button.setAttribute('class', 'btn btn-primary dropdown-toggle');
    button.setAttribute('id', 'MenuButton');
    button.setAttribute('data-bs-toggle', 'dropdown');
    button.setAttribute('aria-haspopup', 'true');
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('name', 'elCategory');
    button.appendChild(document.createTextNode(labels.lblCategory));
    divs.appendChild(button);

    var div = document.createElement('div');
    div.setAttribute('class', 'dropdown-menu');
    div.setAttribute('aria-labelledby', 'MenuButton');

    function buildChildrens(items, parentDiv) {
        items.forEach((item) => {
            var hasChildren = item.children && item.children.length > 0;

            if (hasChildren) {
                var divp = document.createElement('div');
                divp.setAttribute('class', 'btn-group dropdown dropend');

                var ab = document.createElement('a');
                ab.setAttribute('class', 'btn');
                ab.appendChild(document.createTextNode(item.name));
                ab.setAttribute('id', 'element-' + item.id);
                divp.appendChild(ab);

                var ac = document.createElement('a');
                ac.setAttribute('class', 'btn dropdown-toggle');
                ac.setAttribute('href', '#');
                ac.setAttribute('id', 'dropdown-' + item.id);
                ac.setAttribute('data-tag', item.name);
                ac.setAttribute('data-bs-toggle', 'dropdown');
                ac.setAttribute('aria-haspopup', 'true');
                ac.setAttribute('aria-expanded', 'false');
                divp.appendChild(ac);

                var divc = document.createElement('div');
                divc.setAttribute('class', 'dropdown-menu');
                divc.setAttribute('aria-labelledby', 'dropdown-' + item.id);

                buildChildrens(item.children, divc);
                divp.appendChild(divc);
                parentDiv.appendChild(divp);
            } else {
                var ap = document.createElement('a');
                ap.setAttribute('class', 'dropdown-item');
                ap.setAttribute('data-id', +item.id);
                ap.setAttribute('data-tag', item.name);
                ap.setAttribute('href', '#');
                ap.appendChild(document.createTextNode(item.name));
                parentDiv.appendChild(ap);
            }
        });
    }
    buildChildrens(items, div);
    divs.appendChild(div);
    return divs;
}

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
                    deleteRoute(id);
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

function deleteRoute(id) {
    $.ajax({
        type: 'post',
        url: ROUTES_CTRL,
        data: {
            action: 'D',
            part: 'R',
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

$('#AccessSave').click(function (e) {
    e.preventDefault();
    var validation = true;
    var data = $('#modalAccess input, #modalAccess select').serializeObject();
    data.id = $('#modalAccess').data('id');
    data.action = 'U';
    data.part = 'A';
    if (validation) {
        $.ajax({
            type: 'post',
            url: ROUTES_CTRL,
            data,
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
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
                $('#modalAccess').modal('hide');
                clearRouteForm();
                reloadTable();
            },
        });
    }
});

$('[id^="acRequired_"]').click(function (e) {
    //e.preventDefault();
    if (this.checked) {
        var id = $(this).data('id');
        $('#acOptional_' + id).prop('checked', true);
    }
});

$('[id^="acOptional_"]').click(function (e) {
    //e.preventDefault();
    if (!this.checked) {
        var id = $(this).data('id');
        $('#acRequired_' + id).prop('checked', false);
    }
});
$(document).ready(function () {
    $('#dropdownCat').on('click', '.dropdown-item, .btn:not(.dropdown-toggle)', function (e) {
        e.preventDefault();
        var selText = $(this).text();
        $('#MenuButton').html(selText + ' <span class="caret"></span>');
        var selectedId = $(this).data('id');
        $('#MenuButton').val(selectedId);
    });
});

$('#routeSave').click(function (e) {
    e.preventDefault();
    var validation = true;
    var data = $('#modalRoutes input, #modalRoutes select').serializeObject();
    data.id = $('#modalRoutes').data('id');
    data.action = 'U';
    data.part = 'R';

    data.elCategory = $('#MenuButton').val();
    data.elMethod = $('#idMethod').val();

    $('#modalRoutes input').removeClass('is-invalid');
    if (data.elLabelname == '') {
        validation = false;
        $('#idLabelname').addClass('is-invalid');
    }
    if (data.elcategoryParent == '') {
        data.elcategoryParent = 0;
    }
    if (data.elType == '') {
        validation = false;
        $('#idType').addClass('is-invalid');
    }
    if (data.elUrl == '') {
        validation = false;
        $('#idUrl').addClass('is-invalid');
    }

    if (data.elFile == '') {
        validation = false;
        $('#idFile').addClass('is-invalid');
    }

    data.elAllusers = $('#idAllusers').is(':checked') ? 1 : 0;
    data.elPublic = $('#idPublic').is(':checked') ? 1 : 0;

    if (validation) {
        $.ajax({
            type: 'post',
            url: ROUTES_CTRL,
            data,
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
                $('#modalRoutes').modal('hide');
                clearRouteForm();
                reloadTable();
            },
        });
    }
});

function reloadTable() {
    columns = [];
    tableRoutes.destroy();
    $('#tableRoutes').empty();
    loadTableRoutes();
}

function clearRouteForm() {
    $('#modalRoutes').data('id', 0);
    $('#idLabelname').val('');
    $('#numberInput').val(1);
    $('#idType').val('');
    $('#idIcon').val('');
    $('#idUrl').val('');
    $('#idFile').val('');
    $('#idParentSelect2').val('').trigger('change');
    $('#idLabelname').removeClass('is-invalid');
    $('#idUrl').removeClass('is-invalid');
    $('#idFile').removeClass('is-invalid');
    $('#idAllusers').prop('checked', false);
    $('#idPublic').prop('checked', false);
}

$(document).ready(function () {
    $('#numberInput').on('input', function () {
        var value = this.value;
        if (value === '' || (parseInt(value) >= 0 && parseInt(value) <= 255)) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
        if (value.length > 3) {
            this.value = value.slice(0, 3);
        }
    });

    $('#numberInput').on('keydown', function (event) {
        if (!/[0-9]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Tab') {
            event.preventDefault();
        }
        if (this.value.length >= 3 && !['Backspace', 'Tab', 'Enter'].includes(event.key)) {
            event.preventDefault();
        }
    });
});
