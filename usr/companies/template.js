var TEMPLATECOM = "/" + chrLocale + "/controller/companies";
var tableTemplates;

$(document).ready(function () {
    tableTemplates = $("#tableT").DataTable({
        ajax: {
            url: TEMPLATECOM,
            type: "POST",
            dataSrc: "data",
            data: {
                action: "R",
                part: "T",
            },
        },
        language: {
            url: TABLELANG,
        },
        columns: [ // <--- CORREGIDO
            {
                title: labels.lblNameCompany,
                data: "idCompany",
                orderable: true,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    let optionsHtml = CompanysNames.map(opt => {
                        var selected = String(opt.id) === String(row.idCompany) ? ' selected' : '';
                        return `<option value="${opt.id}"${selected}>${opt.text}</option>`;
                    }).join('');

                    return `<select class="selectServices select2--long" 
                                    data-idcompany="${row.idCompany}" 
                                    data-namecompany="${row.NameCompany}">
                                ${optionsHtml}
                            </select>`;
                }
            },
            {
                title: labels.lblCompanyTemplateName,
                data: "CompanyTemplateid",
                orderable: true,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    let optionsHtml = TemplatesNames.map(opt => {
                        const selected = String(opt.id) === String(row.CompanyTemplateid) ? ' selected' : '';
                        return `<option value="${opt.id}"${selected}>${opt.text}</option>`;
                    }).join('');

                    return `<select class="selectServices select2--long" 
                                    data-companytemplateid="${row.CompanyTemplateid}" 
                                    data-companytemplatename="${row.CompanyTemplateName}">
                                ${optionsHtml}
                            </select>`;
                }
            },
            {
                title: labels.tblActions,
                data: "idCompany", // o "id" si sigues usándolo así
                className: "dt-right",
                render: function (data, type, row) {
                    return cellRender({
                        type: "dropdown",
                        data,
                        text: labels.tblActions,
                        dataTags: {
                            id: data,
                            idCompany: row.idCompany,
                            CompanyTemplateid: row.CompanyTemplateid
                        },
                        listItems: [
                            {
                                id: "TemPCompany_" + data,
                                text: labels.btnDelete,
                                icon: "far fa-trash-alt",
                                dataTags: {
                                    id: data,
                                    idCompany: row.idCompany,
                                    CompanyTemplateid: row.CompanyTemplateid
                                }
                            }
                        ]
                    });
                }
            }
            
        ],
        dom: '<"row"<"#ctrlTemplateCustom.col-6"><"col-6"f>>ti',
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        drawCallback: function () {
            var html =
                '<button id="btnCreateT" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
                labels.btnNew +
                "</button>";
            $("#ctrlTemplateCustom").html(html);
            if (!document.getElementById('idServiceTypes')) {
                let calStypes = document.createElement('div');
                calStypes.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let ServicesTselect = document.createElement('select');
                ServicesTselect.classList.add('form-control', 'form-control-sm');
                ServicesTselect.id = 'idServiceTypes';
                ServicesTselect.name = 'idServiceTypes';
                ServicesTselect.required = true;

                // Crear las opciones de 'CompanysNames'
                let defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = labels.lblSelect;
                ServicesTselect.appendChild(defaultOption);
                // Añadir las opciones de las empresas
                CompanysNames.forEach(company => {
                    let option = document.createElement('option');
                    option.value = company.id; // Asumiendo que el `id` es el identificador único
                    option.textContent = company.text; // Asumiendo que `text` es el nombre de la compañía
                    ServicesTselect.appendChild(option);
                });
                calStypes.appendChild(ServicesTselect);
                document.getElementById('containerSelect2').appendChild(calStypes);
            }

            if (!document.getElementById('idAccounts')) {
                let calAccounts = document.createElement('div');
                calAccounts.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-1');

                let AccountsSelect = document.createElement('select');
                AccountsSelect.classList.add('form-control', 'form-control-sm', 'me-2');
                AccountsSelect.id = 'idAccounts';
                AccountsSelect.name = 'idAccounts';
                AccountsSelect.required = true;

                // Crear las opciones de 'TemplatesNames'
                let defOptions = document.createElement('option');
                defOptions.value = '';
                defOptions.disabled = true;
                defOptions.selected = true;
                defOptions.textContent = labels.lblSelect;

                AccountsSelect.appendChild(defOptions);
                // Añadir las opciones de las plantillas
                TemplatesNames.forEach(template => {
                    let option = document.createElement('option');
                    option.value = template.id; // Asumiendo que el `id` es el identificador único
                    option.textContent = template.text; // Asumiendo que `text` es el nombre de la plantilla
                    AccountsSelect.appendChild(option);
                });

                calAccounts.appendChild(AccountsSelect);
                document.getElementById('containerAccounts').appendChild(calAccounts);
            }
            
        }
        
    });
    tableTemplates.on('draw', function () {
        $('#tableT .selectServices').select2({
            theme: "bootstrap-5",
            selectionCssClass: "select2--small",
            dropdownCssClass: "select2--small",
        });
    });
});


$('#nav-TemplateEmail-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
    //tableDataSuppliers.ajax.reload();
});

$('#nav-Companies-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
    //tableDataSuppliers.ajax.reload();
});

$(document).on('click', '#btnCreateT', function (e) {
    e.preventDefault();
    $('#serviceStatus').text((labels.btnNew + ' - ' + labels.lblServicesTypes));
    $('#modalTypeS').modal('show');
    $('#idServiceTypes').val('').trigger('change');
    $('#idAccounts').val('').trigger('change');
});


// Manejador de clic en el botón
$(document).on('click', '#StypesSave', function (e) {
    e.preventDefault();

    var idcompany = $('#idServiceTypes').val();     // ID del tipo de servicio
    var idtemplate = $('#idAccounts').val();             // ID de la cuenta/plantilla

    $.ajax({
        type: "POST",
        url: TEMPLATECOM,
        data: {
            action: "C",
            part: "T",
            idcompany: idcompany,  // Enviar el valor seleccionado
            idtemplate: idtemplate
        },
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteCreateSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                $('#modalTypeS').modal('hide');
            } else {
                alertNotify({
                    type: 'danger',
                    text: labels.nteCreateDuplicateError,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            }
            tableTemplates.ajax.reload();
        }
    });
});

$(document).on("click", '[id^="TemPCompany_"]', function (e) {
    e.preventDefault();
    var idcompany = $(this).data('idcompany');
    var idtemplate = $(this).data('companytemplateid');          // ← usa data‑cedula

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
                    deleteComTemp(idcompany, idtemplate);
                },
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ]
    });
});

function deleteComTemp(idcompany, idtemplate) {
    $.ajax({
        type: "POST",
        url: TEMPLATECOM,
        data: {
            action: 'D',
            part: 'T',
            idcompany: idcompany,
            idtemplate: idtemplate
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
                    text: labels.nteCreateDuplicateError,
                    icon: 'fas fa-times',
                    timeout: 3000,
                });
            }
            tableTemplates.ajax.reload();
        }
    });
}

$(document).on('select2:select', '.selectServices', function (e) {
    const selectChanged = $(this);
    const tr = selectChanged.closest('tr');

    // Nuevos valores desde el DOM (lo seleccionado ahora)
    const newCompanyId = tr.find('select.selectServices[data-idcompany]').val();
    const newTemplateId = tr.find('select.selectServices[data-companytemplateid]').val();

    // Valores anteriores desde los atributos `data-*`
    const oldCompanyId = tr.find('select.selectServices[data-idcompany]').data('idcompany');
    const oldTemplateId = tr.find('select.selectServices[data-companytemplateid]').data('companytemplateid');


    $.ajax({
        url: TEMPLATECOM,
        method: 'POST',
        data: {
            action: 'U',
            part: 'T',
            oldCompanyId: oldCompanyId,
            oldTemplateId: oldTemplateId,
            newCompanyId: newCompanyId,
            newTemplateId: newTemplateId
        },
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
                    text: labels.nteCreateDuplicateError,
                    icon: 'fas fa-times',
                    timeout: 3000,
                });
            }
            tableTemplates.ajax.reload();
        }
    });
});



