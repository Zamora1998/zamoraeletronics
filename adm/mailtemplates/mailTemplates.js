var MAILERTEMPLATES_CTRL = '/controller/mailTemplates';
var modalTemplate = $('#modalTemplate');
var elEmailName = $('#mtName');
var elEmailSubject = $('#mtSubject');
var elAltBody = $('#mtAltBody');
var elAccount = $('#mtAccount');
var elCompany = $('#mtCompany');
var searchlbl = null;
var mailTemplateId = 0;
var tableTemplates;

var elBody = $('#mtBody').summernote({
    dialogsInBody: true,
    placeholder: labels.lblAddNewTemplate,
    height: 350,
    width: '100%',
    htmlEditorEmpty: true,
    focus: true,
    marginBottom: 0,
    lang: localeSN,
    toolbar: [
        ['style', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol', 'paragraph', 'style']],
        ['font', ['strikethrough', 'superscript', 'subscript']],
        ['table', ['table']],
        ['insert', ['link']],
        ['misc', ['codeview', 'undo', 'redo', 'help']],
        ['view', ['fullscreen',]],
    ]
}).on('summernote.paste', function (e) {
    var thisNote = $(this);
    var updatePastedText = function (someNote) {
        var original = someNote.summernote('code');
        var cleaned = CleanPastedHTML(original);
        someNote.summernote('code', cleaned);
    };
    setTimeout(function () {
        updatePastedText(thisNote);
    }, 10);
});

var tableTemplates = $("#tableTemplates").DataTable({
    ajax: {
        url: MAILERTEMPLATES_CTRL,
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
            title: labels.tblName,
            data: "name",
            orderable: false,
            className: "dt-left dt-nowrap",
        }, // 0
        {
            title: labels.tblEmailAccount,
            data: "username",  // Esto solo es para mostrar el texto
            orderable: false,
            className: "dt-left dt-nowrap",
        },
        {
            title: labels.tblSubject,
            data: "subject_label",  // Esto solo es para mostrar el texto
            orderable: false,
            className: "dt-left dt-nowrap",
        },
        {
            title: labels.tblAltBody,
            data: "altbody",
            orderable: false,
            className: "dt-left dt-nowrap text-truncate",
            render: function (data, type, row) {
                if (!data) return ''; // Si no hay dato, devolver vacío

                // Escapamos el contenido para evitar inyección HTML
                const safeData = escapeHtml(data);

                const maxLength = 20;
                let displayText = safeData;

                // Truncar solo en modo 'display' (para mostrar en tabla)
                if (type === 'display' && safeData.length > maxLength) {
                    displayText = safeData.substr(0, maxLength) + '...';
                }

                return `<span title="${safeData}" class="text-truncate" style="max-width: 250px;">${displayText}</span>`;
            }
        },
        {
            title: labels.tblActions,
            data: "id",
            className: "dt-right",
            render: function (data, type, row) {
                return cellRender({
                    type: 'dropdown',
                    data,
                    text: labels.tblActions,
                    dataTags: {
                        id: data
                    },
                    listItems: [
                        {
                            id: "mailTemplateEdit_" + data,
                            text: labels.btnEdit,
                            class: 'mailTemplateEdit',
                            icon: "far fa-edit",
                            dataTags: {
                                id: data
                            },
                        },
                        {
                            id: "duplicateTemplate_" + data,
                            text: labels.btnDuplicate,
                            icon: "far fa-copy",
                            dataTags: {
                                id: data,
                            },
                        },
                        {
                            id: "mailTemplateDelete_" + data,
                            text: labels.btnDelete,
                            icon: "far fa-trash-alt",
                            dataTags: {
                                id: data
                            },
                        },
                    ],
                });
            },
        }
    ],
    dom: '<"row"<"#ctrlCustom.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
    responsive: true,
    fixedHeader: true,
    paging: false,
    scrollX: false,
    scrollY: "calc(100vh - 323px)",
    drawCallback: function () {
        var html =
            '<button id="templateCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
            labels.btnNew +
            "</button>";
        $("#ctrlCustom").html(html);
    }
});

$(document).on('click', '[id^="duplicateTemplate_"]', function () {
    var id = $(this).data('id');
    $.ajax({
        url: MAILERTEMPLATES_CTRL,
        type: 'POST',
        dataSrc: 'data',
        data: {
            action: 'C',
            part: 'D',
            id,
            name: labels.lblCopy
        },
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: "success",
                    text: labels.nteDuplicateSuccess,
                    icon: "fas fa-check",
                    timeout: 3000,
                });
            } else {
                alertNotify({
                    type: "warning",
                    text: labels.nteDuplicateError,
                    icon: "fas fa-exclamation-triangle",
                    timeout: 3000,
                });
            }
            tableTemplates.ajax.reload();
        }
    });
});

$(document).on('click', '#templateCreate, .mailTemplateEdit', function () {
    cleanInputsMailTemplate();
    var id = $(this).data('id');
    modalTemplate.data('id', id);
    $('#liTemplates').trigger('click');
    $('#templateSave').data('id', id);
    $('#variableSave').data('id', id);
    elEmailName.val('').removeClass('is-invalid');
    elEmailSubject.val('').removeClass('is-invalid');
    elBody.summernote('code', '');
    elAltBody.val('').removeClass('is-invalid');
    elAccount.val(null);
    elCompany.val(null);
    $('#templateVariables').html(createTabularVariables([]));

    if (id != "0") {
        $.ajax({
            url: MAILERTEMPLATES_CTRL,
            type: 'POST',
            dataSrc: 'data',
            data: {
                action: 'R',
                part: 'T',
                id,
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                elEmailName.val(data.name);
                elEmailSubject.val(data.subject_label);
                elBody.summernote('code', data.body);
                elAltBody.val(data.altbody);
                elAccount.val(data.mailaccount_id);
                elCompany.val(data.idCompany);
                $('#templateVariables').html(createTabularVariables(response.variables));
                tableSortable();
                labelSelect();
                reloadAccount();
                reloadCompany();
            }
        });
    } else {
        modalTemplate.data('id', id)
        $('#liVariables').addClass('disabled');
    }
    reloadAccount();
    reloadCompany();
    modalTemplate.modal('show');
});

function reloadAccount() {
    elAccount.select2({
        theme: "bootstrap-5",
        selectionCssClass: "select2--small",
        dropdownCssClass: "select2--small",
        dropdownParent: modalTemplate,
        placeholder: labels.tblEmailAccount,
        allowClear: true,
        minimumResultsForSearch: 10,
    }).trigger('change');
}

function reloadCompany() {
    elCompany.select2({
        theme: "bootstrap-5",
        selectionCssClass: "select2--small",
        dropdownCssClass: "select2--small",
        dropdownParent: modalTemplate,
        placeholder: 'Seleccione una cuenta', // Placeholder que se ve cuando no se ha seleccionado
        allowClear: true,
        minimumResultsForSearch: 10,
    }).trigger('change');
}



function createTabularVariables(data) {
    var table = document.createElement('div');
    table.classList.add('col-12');
    table.id = 'varsTable';
    var row = document.createElement('div');
    row.classList.add('d-flex');

    var col1 = document.createElement('div');
    col1.classList.add('me-1');
    var sortSpan = document.createElement('span');
    sortSpan.classList.add('span', 'btn', 'btn-sm', 'btn-secundary');
    var sortIcon = document.createElement('i');
    sortIcon.classList.add('fas', 'fa-arrows-v');
    sortSpan.appendChild(sortIcon);
    col1.appendChild(sortSpan);
    row.appendChild(col1);

    var col2 = document.createElement('div');
    col2.classList.add('flex-grow-1', 'w-100', 'me-1', 'pt-2', 'h6', 'ps-2');
    col2.innerText = labels.lblVariable;
    row.appendChild(col2);

    var col3 = document.createElement('div');
    col3.classList.add('flex-grow-0', 'w-100', 'pt-2', 'h6', 'ps-2');
    col3.innerText = labels.lblLabel;
    row.appendChild(col3);
    table.appendChild(row);

    for (var obj of data) {
        row = document.createElement('div');
        row.classList.add('d-flex', 'datarow', 'mb-1');

        var col1 = document.createElement('div');
        col1.classList.add('me-1');
        var sortSpan = document.createElement('span');
        sortSpan.classList.add('span', 'spanPosition', 'btn', 'btn-sm', 'btn-primary');
        var sortIcon = document.createElement('i');
        sortIcon.classList.add('fas', 'fa-arrows-v');
        sortSpan.appendChild(sortIcon);
        col1.appendChild(sortSpan);
        row.appendChild(col1);

        var col2 = document.createElement('div');
        col2.classList.add('flex-grow-1', 'w-100', 'me-1');
        var varsInput = document.createElement('input');
        varsInput.classList.add('form-control', 'form-control-sm');
        varsInput.setAttribute('id', 'mtVariable_' + obj.position);
        varsInput.setAttribute('name', 'mtVariables[]');
        varsInput.setAttribute('value', obj.variable);
        varsInput.setAttribute('data-id', obj.position);
        varsInput.setAttribute('disabled', true);
        col2.appendChild(varsInput);
        row.appendChild(col2);

        var col3 = document.createElement('div');
        col3.classList.add('flex-grow-0', 'w-100');
        var labelsSelect = document.createElement('select');
        var selectOption = document.createElement('option');
        selectOption.value = obj.label_name;
        selectOption.text = obj.label_name;
        labelsSelect.classList.add('variableSelect', 'form-control', 'form-control-sm');
        labelsSelect.setAttribute('id', 'mtLabel_' + obj.position);
        labelsSelect.setAttribute('name', 'mtLabels[]');
        labelsSelect.setAttribute('data-id', obj.position);
        labelsSelect.style = 'width: 100%;';
        labelsSelect.appendChild(selectOption);
        col3.appendChild(labelsSelect);
        row.appendChild(col3);
        table.appendChild(row);
    }

    return table;
}

function tableSortable() {
    $("#varsTable").sortable({
        axis: "y",
        handle: ".spanPosition"
    });
}

function labelSelect() {
    $('.variableSelect').select2({
        allowClear: true,
        placeholder: labels.lblLabel,
        dropdownParent: modalTemplate,
        minimumInputLength: 4,
        width: 'resolve',
        templateSelection: function (params) {
            if (!params.id) {
                return params.text;
            }
            return params.id;
        },
        delay: 250,
        cache: false,
        ajax: {
            type: 'POST',
            url: MAILERTEMPLATES_CTRL,
            data: function (term) {
                return {
                    action: 'R',
                    part: 'L',
                    term: term.term,
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.data
                };
            }
        },
        Selection: function (data) {
            return data.name || data.text;
        }
    })
};

$(document).on('click', '#templateSave', function () {
    var id = modalTemplate.data('id');
    var values = $('#tabTemplates input, #tabTemplates select, #tabTemplates textarea').serializeObject();
    var MAX_LEN = 50;
    var errores = [];

    Object.entries(values).forEach(([key, value]) => {
        if (typeof value === 'string') {
            const matches = value.match(/\{([^}]+)\}/g);

            if (matches) {
                matches.forEach(m => {
                    const contenido = m.slice(1, -1);

                    if (contenido.length > MAX_LEN) {
                        errores.push(labels.ntePlaceHolderSizeLimit);
                    }
                });
            }
        }
    });

    if (errores.length > 0) {
        alertNotify({
            type: 'warning',
            text: errores.join('\n'),
            icon: 'fas fa-check',
            timeout: 3000,
        });
        return false;
    }
    values.id = id;
    values.action = 'C';
    values.part = 'T';

    mailTemplateId = $(this).data('id')

    elEmailName.removeClass('is-invalid');
    elEmailSubject.removeClass('is-invalid');
    elAltBody.removeClass('is-invalid');

    var validated = true;
    if (values.mtSubject == '') {
        elEmailSubject.addClass('is-invalid');
        validated = false;
    }
    if (values.mtName == '') {
        elEmailName.addClass('is-invalid');
        validated = false;
    }
    if (values.mtAltBody == '') {
        elAltBody.addClass('is-invalid');
        validated = false;
    }

    if (validated === true) {
        $.ajax({
            type: 'POST',
            url: MAILERTEMPLATES_CTRL,
            data: values,
            success: function (response) {
                if (response.result) {
                    var data = response.data;
                    var mailTemplateId = data.id;
                    elEmailName.val(data.name);
                    elEmailSubject.val(data.subject_label);
                    elBody.summernote('code', data.body);
                    elAltBody.val(data.altbody);
                    $('#templateSave').data('id', mailTemplateId);
                    $('#variableSave').data('id', mailTemplateId);
                    $('#templateVariables').html(createTabularVariables(response.variables));
                    tableSortable();
                    labelSelect();
                    $('#liVariables').tab('show');
                    alertNotify({
                        type: 'success',
                        text: labels.nteSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                } else {
                    alertNotify({
                        type: 'warning',
                        text: labels.nteError + '<br>' + data.error,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
            }
        });
    }
});

$(document).on('click', '#variableSave', function () {
    var id = modalTemplate.data('id');
    var values = $('#varsTable input, #varsTable select').serializeObject();
    values.id = id;
    values.action = 'U';
    values.part = 'V';

    $.ajax({
        type: 'POST',
        url: MAILERTEMPLATES_CTRL,
        data: values,
        success: function (response) {
            var data = response;
            if (data.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                modalTemplate.modal('hide');
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteError + '<br>' + data.error,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
        }
    });
});

$(document).on('click', '[id^="mailTemplateDelete_"]', function () {
    mailTemplateId = $(this).data('id')
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
                    deleteMailTemplate(mailTemplateId);
                },
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ]
    })
});

$('#btnPreview').click(function (e) {
    e.preventDefault();
    var id = modalTemplate.data('id');
    $.ajax({
        type: "post",
        url: MAILERTEMPLATES_CTRL,
        data: {
            action: 'R',
            part: 'P',
            id
        },
        dataType: "json",
        success: function (response) {
            $('#preview').html(response.body);
        }
    });
});

function deleteMailTemplate(id) {
    $.ajax({
        type: 'POST',
        url: MAILERTEMPLATES_CTRL,
        data: {
            action: 'D',
            part: 'T',
            id: mailTemplateId
        },
        success: function (result) {
            if (result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                tableTemplates.ajax.reload();
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteError + '<br>' + data.error,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
        }
    });
}

modalTemplate.on('hidden.bs.modal', function () {
    tableTemplates.ajax.reload();
});

function cleanInputsMailTemplate() {
    $('#emailName').val("");
    $('#emailSubject').val("");
    $('#emailBody').summernote('reset')
    $('#emailBody').summernote('code', '');
    $('#altBody').val("");
}

$('#nav-mailtemplate-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
    //tableDataSuppliers.ajax.reload();
});

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "<")
        .replace(/>/g, ">")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
