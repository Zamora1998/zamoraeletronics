var CTRL_PAYROLL = "/" + chrLocale + "/controller/Payroll";
var columns = [];
var TableCompanies;
var DateTime = luxon.DateTime;
let input;
let tagsContainer;
let planilla = {};
var TableDataMnth;

const currencyFormatterCRC = new Intl.NumberFormat('es-CR', {
    style: 'currency',
    currency: 'CRC',
    minimumFractionDigits: 2
});

$(document).ready(function () {
    TableCompanies = $("#tablePayroll").DataTable({
        data: [],
        language: {
            url: TABLELANG,
        },
        columns: [
            {
                title: "Código",
                data: "Codigo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';

                    return `
                    <button type="button"
                            id="colaborador_${row.Cedula}"
                            class="btn btn-primary btn-sm"
                            data-cedula="${row.Cedula}">
                        ${data}
                    </button>`;
                
                }
            },            
            {
                title: labels.lblName,
                data: "NombreEmpleado",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';
                    var truncated = data.length > 10 ? data.substring(0, 10) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: labels.lblEmail,
                data: "Correo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';
                    var truncated = data.length > 10 ? data.substring(0, 10) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },            
            { 
                title: "Cédula",
                data: "Cedula",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            { 
                title: "Cuenta Bancaria",
                data: "CuentaBancaria",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            { 
                title: "Salario Mensual",
                data: "SalarioMensual",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Salario Lab.",
                data: "SalarioLaborado",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Días Lab.",
                data: "DiasLaborados",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            { 
                title: "Comisiones",
                data: "Comisiones",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Incapacidades",
                data: "Incapacidades",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "CCSS",
                data: "CCSSDeduccion",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Imp Renta",
                data: "DeduccionRenta",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Anticipo",
                data: "Anticipo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Otros Rebajos",
                data: "OtrosRebajos",
                orderable: false,
                className: "dt-left dt-nowrap", 
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            { 
                title: "Total Pagar",
                data: "TotalPagar",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
        ],
        dom: '<"row"<"#ctrlConfCustom.col-6"><"col-6"f>>ti',
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        drawCallback: function () {
            if (!document.getElementById('selectCompanyOption')) {
                let container = document.createElement('div');
                container.classList.add('d-inline-flex', 'align-items-center', 'mt-0');

                // Select para templates
                let select = document.createElement('select');
                select.classList.add('form-select', 'form-select-sm', 'select2', 'w-auto');
                select.id = 'selectCompanyOption';
                select.name = 'selectCompanyOption';

                let defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Seleccione';
                select.appendChild(defaultOption);

                templates.forEach(template => {
                    let option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = `${template.text}`;
                    option.setAttribute('data-id', template.id);
                    option.setAttribute('data-companyid', template.Companyid);
                    select.appendChild(option);
                });

                container.appendChild(select);

                // Input de archivo
                let fileInputContainer = document.createElement('div');
                fileInputContainer.classList.add('d-inline-block', 'ms-3');

                let fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.id = 'excelUpload';
                fileInput.accept = '.xls,.xlsx';
                fileInput.style.display = 'none';

                let fileLabel = document.createElement('label');
                fileLabel.setAttribute('for', 'excelUpload');
                fileLabel.classList.add('btn', 'btn-sm', 'btn-success', 'me-1');
                fileLabel.textContent = labels.btnImportExcel || 'Importar Excel';
                fileLabel.classList.add('disabled');
                fileLabel.style.pointerEvents = 'none';
                fileLabel.style.opacity = '0.6';

                fileInputContainer.appendChild(fileInput);
                fileInputContainer.appendChild(fileLabel);

                container.appendChild(fileInputContainer);

                // Botón enviar correos
                let sendButton = document.createElement('button');
                sendButton.type = 'button';
                sendButton.id = 'enviarEmails';
                sendButton.classList.add('btn', 'btn-sm', 'btn-primary', 'ms-2');
                sendButton.textContent = labels.btnSendEmails || 'Enviar correos';
                sendButton.setAttribute('data-id', 0);
                sendButton.disabled = true;
                container.appendChild(sendButton);

                // Datepicker al lado del botón de envío
                if (!document.getElementById('CalDate')) {
                    let calFromContainer = document.createElement('div');
                    calFromContainer.classList.add('d-inline-flex', 'align-items-center', 'ms-2');

                    let calFromDiv = document.createElement('div');
                    calFromDiv.classList.add('input-group', 'date');
                    calFromDiv.setAttribute('data-provide', 'datepicker');

                    let calInput = document.createElement('input');
                    calInput.type = 'text';
                    calInput.classList.add('form-control', 'form-control-sm');
                    calInput.id = 'CalDate';
                    calInput.placeholder = labels.lblFrom;
                    calInput.title = labels.tblStart;
                    calInput.setAttribute('readonly', '');

                    let calSpan = document.createElement('span');
                    calSpan.classList.add('input-group-text', 'input-group-addon');
                    let calIcon = document.createElement('i');
                    calIcon.classList.add('far', 'fa-calendar-alt');
                    calSpan.appendChild(calIcon);

                    let calFeedback = document.createElement('div');
                    calFeedback.classList.add("invalid-feedback");
                    calFeedback.textContent = labels.lblRequired;

                    calFromDiv.appendChild(calInput);
                    calFromDiv.appendChild(calSpan);
                    calFromDiv.appendChild(calFeedback);

                    calFromContainer.appendChild(calFromDiv);
                    container.appendChild(calFromContainer);
                }

                // Agregar al contenedor principal
                document.getElementById('ctrlConfCustom').appendChild(container);

                // Inicializar select2
                $('#selectCompanyOption').select2({
                    theme: 'bootstrap-5',
                    placeholder: "Seleccione",
                    selectionCssClass: "select2--small",
                    dropdownCssClass: "select2--small"
                });

                $('#selectCompanyOption').on('select2:select', function (e) {
                    const selectedOption = $(e.params.data.element);
                    const dataId = selectedOption.attr('data-id');
                    const companyId = selectedOption.attr('data-companyid');

                    $('#enviarEmails')
                        .attr('data-id', dataId)
                        .attr('data-companyid', companyId);

                    fileLabel.classList.remove('disabled');
                    fileLabel.style.pointerEvents = '';
                    fileLabel.style.opacity = '';
                });
            }

            // Inicializar datepicker
            $('.input-group.date').datepicker({
                language: chrLocale,
                daysOfWeekHighlighted: "0.6",
                clearBtn: true,
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom",
                format: "dd-M-yyyy"
            });
        }
        
    });
});

$(document).on("change", "#excelUpload", function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();

    reader.onload = function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: "array" });

        const sheetName = workbook.SheetNames[0];
        const sheet = workbook.Sheets[sheetName];

        const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

        // Limpiar planilla antes de cargar nueva
        planilla = {};

        const parsedRows = jsonData.slice(2).map(row => {
            const isEmptyRow = row.every(cell => cell === null || cell === undefined || cell === "");
            if (isEmptyRow) return null;

            const dataRow = {
                Codigo: row[0] || "",
                NombreEmpleado: row[1] || "",
                Correo: row[2] || "",
                Cedula: row[3] || "",
                CuentaBancaria: row[4] || "",
                SalarioMensual: parseFloat(row[5]) || 0,
                SalarioLaborado: parseFloat(row[6]) || 0,
                DiasLaborados: parseFloat(row[7]) || 0,
                Comisiones: parseFloat(row[8]) || 0,
                Incapacidades: parseFloat(row[9]) || 0,
                CCSSDeduccion: parseFloat(row[10]) || 0,
                DeduccionRenta: parseFloat(row[11]) || 0,
                Anticipo: parseFloat(row[12]) || 0,
                OtrosRebajos: parseFloat(row[13]) || 0,
                TotalPagar: parseFloat(row[14]) || 0
            };

            // Si la cédula existe, usarla como clave
            if (dataRow.Cedula) {
                planilla[dataRow.Cedula] = dataRow;
            }

            return dataRow;
        }).filter(row => row !== null);

        TableCompanies.clear().rows.add(parsedRows).draw();

        if (parsedRows.length > 0) {
            $('#enviarEmails').prop('disabled', false);
        }
    };

    reader.readAsArrayBuffer(file);
});

// Manejador de clic en el botón
$(document).on('click', '#enviarEmails', function (e) {
    e.preventDefault();
    var selectedCompanyText = $('#selectCompanyOption option:selected').text().trim(); // Captura del texto del <select>
    console.log('text',selectedCompanyText);
    var id = $(this).data('id'); // Recuperas el data-id
    var companyid = $(this).data('companyid'); // Recuperas el data-id

    // Validar fecha del datepicker
    var selectedDate = $('#CalDate').val().trim();
    if (!selectedDate) {
        $('#CalDate').addClass('is-invalid');
        return; // No continuar si está vacío
    } else {
        $('#CalDate').removeClass('is-invalid');
    }
    var date = selectedDate.replace(/-/g, ' ');
    var today = new Date();
    var dateformat = today.toISOString().split('T')[0]; // 'YYYY-MM-DD'
    $.ajax({
        type: "POST",
        url: CTRL_PAYROLL,
        data: {
            action: "R",
            part: "S",
            id: id,
            companyid: companyid,
            dateformat: dateformat,
            date: date,
            companyName: selectedCompanyText,
            planilla: JSON.stringify(planilla),
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
            } else {
                alertNotify({
                    type: 'danger',
                    text: labels.nteCreateError,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            }
        }
    });
});


$(document).on("click", '[id^="colaborador_"]', function (e) {
    e.preventDefault();
    var id = $('#enviarEmails').data('id');   // ← lo leemos aquí
    const cedula = $(this).data('cedula');          // ← usa data‑cedula
    if (!cedula || !planilla[cedula]) return;       // seguridad
    var CompanyText = $('#selectCompanyOption option:selected').text().trim(); // Captura del texto del <select>
    const planillaColab = { [cedula]: planilla[cedula] };
    var selectedDate = $('#CalDate').val().trim();
    if (!selectedDate) {
        $('#CalDate').addClass('is-invalid');
        return; // No continuar si está vacío
    } else {
        $('#CalDate').removeClass('is-invalid');
    }
    var date = selectedDate.replace(/-/g, ' ');
    $.ajax({
        type: "POST",
        url: CTRL_PAYROLL,
        data: {
            action: "R",
            part: "C",
            id: id,
            date: date,
            companyName: CompanyText,
            planilla: JSON.stringify(planillaColab) 
        },
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: 'success',
                    text: labels.lblSendMailsSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            } else {
                alertNotify({
                    type: 'danger',
                    text: labels.lblSendMailsError,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            }
        }
    });
});

function dateLargeMysql(dateStr, chrLocale = 'es-ES') {
    const parsedDate = new Date(dateStr);

    if (isNaN(parsedDate)) {
        console.error("Fecha inválida:", dateStr);
        return null;
    }

    const year = parsedDate.getFullYear();
    const month = String(parsedDate.getMonth() + 1).padStart(2, '0'); // meses base 0
    const day = String(parsedDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}




$(document).ready(function () {
    TableDataMnth = $("#tableMonthsData").DataTable({
        data: [],
        language: {
            url: TABLELANG,
        },
        columns: [
            {
                title: "Código",
                data: "Codigo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';

                    return `
                    <button type="button"
                            id="colaborador_${row.Cedula}"
                            class="btn btn-primary btn-sm"
                            data-cedula="${row.Cedula}">
                        ${data}
                    </button>`;

                }
            },
            {
                title: labels.lblName,
                data: "NombreEmpleado",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';
                    var truncated = data.length > 10 ? data.substring(0, 10) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: labels.lblEmail,
                data: "Correo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';
                    var truncated = data.length > 10 ? data.substring(0, 10) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Cédula",
                data: "Cedula",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Cuenta Bancaria",
                data: "CuentaBancaria",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Salario Mensual",
                data: "SalarioMensual",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Salario Lab.",
                data: "SalarioLaborado",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Días Lab.",
                data: "DiasLaborados",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Comisiones",
                data: "Comisiones",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Incapacidades",
                data: "Incapacidades",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "CCSS",
                data: "CCSSDeduccion",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Imp Renta",
                data: "DeduccionRenta",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Anticipo",
                data: "Anticipo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Otros Rebajos",
                data: "OtrosRebajos",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
            {
                title: "Total Pagar",
                data: "TotalPagar",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return currencyFormatterCRC.format(parseFloat(data || 0));
                }
            },
        ],
        dom: '<"row"<"#ctrlConfCustomMonthMonth.col-6"><"col-6"f>>ti',
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        drawCallback: function () {
            const containerId = 'ctrlConfCustomMonthMonth';
            const container = document.getElementById(containerId);

            if (!container) return; // Evita errores si el contenedor aún no existe

            // Evita agregar múltiples veces
            if (!document.getElementById('selectCompanyOptionsMonths')) {
                // Crear contenedor interno (flex)
                const innerContainer = document.createElement('div');
                innerContainer.classList.add('d-flex', 'align-items-center', 'gap-2');

                // ▸ SELECT COMPANY
                const select = document.createElement('select');
                select.classList.add('form-select', 'form-select-sm', 'select2', 'w-auto');
                select.id = 'selectCompanyOptionsMonths';
                select.name = 'selectCompanyOptionsMonths';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Seleccione';
                select.appendChild(defaultOption);

                templates.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = template.text;
                    option.setAttribute('data-companyid', template.Companyid);
                    select.appendChild(option);
                });

                innerContainer.appendChild(select);

                // ▸ DATEPICKER
                if (!document.getElementById('calenStartI')) {
                    const calFromI = document.createElement('div');
                    calFromI.classList.add('col-3', 'col-sm-6', 'col-md-3', 'col-lg-3', 'pd-1');

                    const fromDiv = document.createElement('div');
                    fromDiv.classList.add('input-group', 'date');
                    fromDiv.setAttribute('data-provide', 'datepicker');

                    const fromInputI = document.createElement('input');
                    fromInputI.type = 'text';
                    fromInputI.classList.add('form-control', 'form-control-sm');
                    fromInputI.id = 'calenStartI';
                    fromInputI.placeholder = labels.lblFrom;
                    fromInputI.title = labels.tblStart;
                    fromInputI.setAttribute('readonly', '');
                    fromInputI.setAttribute('disabled', ''); // 🔒 DESACTIVADO POR DEFECTO

                    const fromSpan = document.createElement('span');
                    fromSpan.classList.add('input-group-text', 'input-group-addon');
                    const fromIcon = document.createElement('i');
                    fromIcon.classList.add('far', 'fa-calendar-alt');
                    fromSpan.appendChild(fromIcon);

                    const fromFeedback = document.createElement('div');
                    fromFeedback.classList.add("invalid-feedback");
                    fromFeedback.textContent = labels.lblRequired;

                    fromDiv.appendChild(fromInputI);
                    fromDiv.appendChild(fromSpan);
                    fromDiv.appendChild(fromFeedback);

                    calFromI.appendChild(fromDiv);

                    // Agregamos el datepicker al contenedor interno
                    innerContainer.appendChild(calFromI);
                }

                // Agregamos todo al contenedor principal
                container.appendChild(innerContainer);

                // Inicializar select2
                $('#selectCompanyOptionsMonths').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Seleccione',
                    selectionCssClass: 'select2--small',
                    dropdownCssClass: 'select2--small'
                });

                // Inicializar datepicker
                $('.input-group.date').datepicker({
                    language: chrLocale,
                    daysOfWeekHighlighted: "0.6",
                    clearBtn: true,
                    autoclose: true,
                    todayHighlight: true,
                    orientation: "bottom",
                    format: "dd-M-yyyy"
                });
                $('#selectCompanyOptionsMonths').on('change', function () {
                    const selected = $(this).val();
                    if (selected) {
                        $('#calenStartI').removeAttr('disabled'); // ← Remueve el atributo
                    } else {
                        $('#calenStartI').attr('disabled', 'disabled'); // ← Lo vuelve a poner si no hay selección
                    }
                });

            }
        }
    });
});



$(document).on('change', "#calenStartI", function (e) {
    e.preventDefault();
    var companyId = $('#selectCompanyOptionsMonths option:selected').data('companyid');
    var startDateI = $('#calenStartI').val();
    startDateI = dateShortToSql(startDateI, chrLocale);
        $.ajax({
            type: "POST",
            url: CTRL_PAYROLL,
            data: {
                action: 'R',
                part: 'B',
                startDateI: startDateI,
                companyId: companyId
            },
            dataType: "json",
            success: function (response) {
                const dataR = response.data;
                const tableData = [];

                if (dataR && dataR.length > 0) {
                    dataR.forEach(function (item) {
                        tableData.push({
                            Codigo: item.codigo,
                            NombreEmpleado: item.nombre,
                            Correo: item.correo_electronico,
                            Cedula: item.cedula,
                            CuentaBancaria: item.cuenta_bancaria,
                            SalarioMensual: item.salario_mensual,
                            SalarioLaborado: item.salario_laborado,
                            DiasLaborados: item.dias_laborados,
                            Comisiones: item.comisiones,
                            Incapacidades: item.incapacidades,
                            CCSSDeduccion: item.ccss,
                            DeduccionRenta: item.impuesto_renta,
                            Anticipo: item.anticipo || 0,
                            OtrosRebajos: item.otros_rebajos || 0,
                            TotalPagar: (
                                (item.salario_laborado || 0) +
                                (item.comisiones || 0)
                                - (item.ccss || 0)
                                - (item.impuesto_renta || 0)
                                - (item.anticipo || 0)
                                - (item.otros_rebajos || 0)
                            )
                        });
                    });
                    // Actualizar la tabla
                    TableDataMnth.clear();
                    TableDataMnth.rows.add(tableData);
                    TableDataMnth.draw();

                } else {
                    TableDataMnth.clear().draw();
                    alertNotify({
                        type: "warning",
                        text: labels.nteNotData,
                        icon: "fas fa-check",
                        timeout: 3000,
                    });
                }
            }
            
        });
    
});

$('#nav-MonthsData-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
    //tableDataSuppliers.ajax.reload();
});
$('#nav-Payroll-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
    //tableDataSuppliers.ajax.reload();
});
