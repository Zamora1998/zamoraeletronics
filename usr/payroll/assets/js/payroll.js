var CTRL_PAYROLL = "/" + chrLocale + "/controller/payroll";
var tablePayroll;
var tableMails;
var excelFile;
var globalMailData;
var currentMailData;
var mailTemp;
const currencyFormatterCRC = new Intl.NumberFormat('es-CR', {
    style: 'currency',
    currency: 'CRC',
    minimumFractionDigits: 2
});

$(document).on('click', '#btnLoadPayroll', function () {
    $('<input type="file" accept=".xlsx, .xls, .csv">')
        .on('change', function (e) {
            excelFile = e.target.files[0];
            loadTablePayroll(excelFile, function () {
                // ── Callback: se ejecuta cuando la tabla ya cargó
                let fp = document.querySelector("#calendPayroll")?._flatpickr;
                if (fp) {
                    fp.set('clickOpens', true);
                    $(fp.altInput).prop('disabled', false).removeClass('disabled');
                }
            });
        })
        .click();
});

loadTablePayroll();

function loadTablePayroll(excelFile, callback) {  // <-- recibe callback
    globalMailData = [];
    if (excelFile) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            let body = [];
            if (jsonData.length > 0) {
                const headers = jsonData[0];
                body = jsonData.slice(1)
                    .filter(row => row[0] && row[1] && row[2] &&
                        row[0].toString().trim() !== '' &&
                        row[1].toString().trim() !== '' &&
                        row[2].toString().trim() !== '')
                    .map((row, index) => {
                        let obj = { id: index + 1 };
                        headers.forEach((header, idx) => { obj[header] = row[idx] || ""; });
                        return obj;
                    });
            }
            globalMailData = body;
            renderTablePayroll(body, callback);  // <-- pasa callback
        };
        reader.readAsArrayBuffer(excelFile);
    } else {
        renderTablePayroll([], callback);        // <-- pasa callback
    }
}

function renderTablePayroll(body, callback) {
    if ($.fn.DataTable.isDataTable('#tablePayroll')) {
        $('#tablePayroll').DataTable().destroy();
        $('#tablePayroll').empty();
    }

    tablePayroll = $('#tablePayroll').DataTable({
        data: body,
        language: {
            url: TABLELANG,
        },
        dom: '<"row align-items-center"<"#mailPayrollButtons.col-auto mt-2 mb-2"><"#payrollDateContainer.col-auto mt-2 mb-2"><"col-6"P><"col"f>>ti',
        responsive: true,
        fixedHeader: true,
        fixedFooter: true,
        paging: false,
        scrollX: false,
        searchPanes: {
            initCollapsed: true,
            orderable: false,
            cascadePanes: true,
            columns: [1],
            className: 'mt-0 ast-pane',
        },
        scrollY: 'calc(100vh - 350px)',
        initComplete: function () {
            const buttonsConfig = [
                ['btnLoadPayroll', 'btn btn-sm px-3 py-2 btn-primary', 'Cargar nomina', 'far fa-upload', false],
                ['selectAllPayroll', 'btn btn-sm px-3 py-2 btn-secondary', 'Selecionar todos', 'far fa-check-square', false],
                ['mailPayroll', 'btn btn-sm px-3 py-2 btn-success', 'Enviar masivo', 'far fa-envelope', true]
            ];

            // ── Botones
            $('#mailPayrollButtons').attr('role', 'group').addClass('btn-group btn-group-sm');
            $(buttonsConfig.map(([id, className, title, icon, disabled]) =>
                `<button id="${id}" class="${className}" title="${title}" ${disabled ? 'disabled' : ''}><i class="${icon}"></i></button>`
            ).join('')).prependTo('#mailPayrollButtons');

            // ── Fecha en su propio contenedor
            if ($('#calendPayroll').length === 0) {
                let today = flatpickr.formatDate(new Date(), "d-M-Y");

                $('#payrollDateContainer').addClass('d-flex align-items-center gap-2').append(`
            <label class="form-label mb-0 text-muted small" for="calendPayroll">
                ${labels.lblDate ?? 'Fecha pago: '}
            </label>
            <input type="text"
                class="form-control form-control-sm"
                id="calendPayroll"
                placeholder="${today}"
                readonly
                style="width: 130px;">
        `);

                let fpPayroll = flatpickr('#calendPayroll', {
                    enableTime: false,
                    altInput: true,
                    altFormat: "d-M-Y",
                    dateFormat: "Y-m-d",
                    locale: chrLang,
                    onChange: function () {
                        let fp = document.querySelector("#calendPayroll")?._flatpickr;
                        $(fp.altInput).removeClass("is-invalid");
                        $(".calendPayroll-error").remove();
                    }
                });
                fpPayroll.set('clickOpens', false);
                $(fpPayroll.altInput).prop('disabled', true).addClass('disabled');
            }
            if (typeof callback === 'function') callback();

        },
        rowCallback: function (row, data, dataIndex) {
            $(row).attr('data-id', data.id);
        },
        columns: [
            {
                title: "Código",
                data: "Codigo",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    return cellRender({
                        type: 'button',
                        id: 'btnSendMail_' + row.NombreEmpleado,
                        text: data,
                        //icon: 'far fa-envelope',
                        class: 'btn btn-sm btn-success',
                        title: 'Enviar correo'
                    })
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
                data: "Cuentabancaria",
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
                data: "DiasLab",
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
                data: "DeduccionImpRenta",
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
        ]
    });
}


$(document).on('click', '#selectAllPayroll', function () {

    const allRowsTotal = $('#tablePayroll tbody tr:not(.disabled)').filter(function () {
        return $(this).data('id')
    })

    if (allRowsTotal.length === 0) return

    const selectedRows = $('#tablePayroll tbody tr.selected')
    const shouldSelect = selectedRows.length > 0

    shouldSelect ? allRowsTotal.removeClass('selected') : allRowsTotal.addClass('selected')
    const icon = $('#selectAllPayroll i')
    if (shouldSelect) {
        icon.removeClass('far fa-check-square').addClass('far fa-square')
    } else {
        icon.removeClass('far fa-square').addClass('far fa-check-square')
    }


    updateModalMailState()
})

async function getBase64ImageFromURL(url) {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = "anonymous";

        img.onload = () => {
            const canvas = document.createElement("canvas");
            canvas.width = img.width;
            canvas.height = img.height;
            canvas.getContext("2d").drawImage(img, 0, 0);
            resolve(canvas.toDataURL("image/png"));
        };

        img.onerror = (error) => {
            resolve(null);
        };

        img.src = url;
    });
}

async function generatePDF(data) {
    return new Promise(async (resolve, reject) => {
        try {
            const backgroundImage = await getBase64ImageFromURL(imageRoute);
            const row = Array.isArray(data) ? data[0] : data;

            if (!row) return;

            const pdfFileName = `Comprobante_Pago_${row.NombreEmpleado || 'Empleado'}_${row.Mes || 'Mes'}`.replace(/\s+/g, '_');

            const docDefinition = {
                info: {
                    title: pdfFileName,
                    author: labels.brlAraTours,
                },
                pageSize: {
                    width: 8.5 * 72,
                    height: 5.8 * 72
                },
                pageMargins: [200, 40, 40, 0],
                background: function (currentPage) {
                    if (!backgroundImage) return [];
                    return [{
                        image: backgroundImage,
                        width: 4.2 * 72,
                        height: 5.8 * 72,
                        absolutePosition: { x: -92, y: 0 }
                    }];
                },
                content: [
                    // ── ENCABEZADO: Nombre + Mes ──
                    {
                        columns: [
                            {
                                text: [{ text: row.NombreEmpleado || '', style: 'valueLarge', bold: true }],
                                width: '*',
                                margin: [0, 10, 0, 0]
                            },
                            {
                                text: [{ text: row.Mes || '', style: 'valueMedium' }],
                                width: 'auto',
                                margin: [0, 12, 10, 0]
                            }
                        ],
                        margin: [-20, 10, 0, 5]
                    },

                    // ── INFO EMPLEADO: Cédula, Cuenta, Correo ──
                    {
                        columns: [
                            {
                                text: [
                                    { text: 'Cédula: ', style: 'infoLabel' },
                                    { text: row.Cedula || '', style: 'infoValue' }
                                ],
                                width: '*',
                                margin: [45, 0, 0, 0]
                            },
                            {
                                text: [
                                    { text: 'Cta. Bancaria: ', style: 'infoLabel' },
                                    { text: row.Cuentabancaria || '', style: 'infoValue' }
                                ],
                                width: 'auto',
                                alignment: 'right',
                                margin: [0, 0, 10, 0]
                            }
                        ],
                        margin: [0, 6, 0, 8]
                    },
                    // ── TABLA INGRESOS ──
                    {
                        table: {
                            widths: ['*', 'auto'],
                            body: [
                                [
                                    { text: [{ text: 'Salario Mensual: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.SalarioMensual || 0, chrLocale), style: 'rowValue', margin: [0, 0, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Salario Laborado: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.SalarioLaborado || 0, chrLocale), style: 'rowValue', margin: [0, 5, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Días Laborados: ', style: 'rowLabel' }] },
                                    { text: String(row.DiasLab || 0), style: 'rowValue', margin: [0, 5, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Comisiones: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.Comisiones || 0, chrLocale), style: 'rowValue', margin: [0, 5, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Incapacidades: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.Incapacidades || 0, chrLocale), style: 'rowValue', margin: [0, 5, 0, 0] }
                                ]
                            ]
                        },
                        layout: {
                            hLineWidth: () => 0,
                            vLineWidth: () => 0,
                            paddingTop: () => 4,
                            paddingBottom: () => 4
                        },
                        margin: [-20, 10, 0, 5]
                    },

                    // ── TABLA DEDUCCIONES ──
                    {
                        table: {
                            widths: ['*', 'auto'],
                            body: [
                                [
                                    { text: [{ text: 'CCSS: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.CCSSDeduccion || 0, chrLocale), style: 'rowValue', margin: [0, 2, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Imp. Renta: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.DeduccionImpRenta || 0, chrLocale), style: 'rowValue', margin: [0, 5, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Anticipo: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.Anticipo || 0, chrLocale), style: 'rowValue', margin: [0, 6, 0, 0] }
                                ],
                                [
                                    { text: [{ text: 'Otros Rebajos: ', style: 'rowLabel' }] },
                                    { text: formatNumber(row.OtrosRebajos || 0, chrLocale), style: 'rowValue', margin: [0, 6, 90, 0] }
                                ],
                                [
                                    { text: [{ text: 'Total Deducciones: ', style: 'rowLabelBold' }] },
                                    {
                                        text: formatNumber(
                                            (row.CCSSDeduccion || 0) +
                                            (row.DeduccionImpRenta || 0) +
                                            (row.Anticipo || 0) +
                                            (row.OtrosRebajos || 0),
                                            chrLocale
                                        ),
                                        style: 'rowValueBold',
                                        margin: [0, 5, 90, 0]
                                    }
                                ]
                            ]
                        },
                        layout: {
                            hLineWidth: () => 0,
                            vLineWidth: () => 0,
                            paddingTop: () => 6,
                            paddingBottom: () => 6
                        },
                        margin: [-20, 10, 0, 5]
                    },

                    // ── TOTAL A PAGAR ──
                    {
                        text: [
                            { text: 'Total a Pagar: ', style: 'rowLabelBold' },
                            { text: formatNumber(row.TotalPagar || 0, chrLocale), style: 'totalValue' }
                        ],
                        alignment: 'left',
                        margin: [220, 2, 0, 2]
                    }
                ],
                styles: {
                    labelLarge: { fontSize: 10, color: '#333' },
                    valueLarge: { fontSize: 14, bold: true, color: '#000' },
                    labelMedium: { fontSize: 12, color: '#333' },
                    valueMedium: { fontSize: 12, bold: true, color: '#000' },
                    infoLabel: { fontSize: 8, color: '#555' },
                    infoValue: { fontSize: 8, bold: true, color: '#000' },
                    rowLabel: { fontSize: 8, margin: [10, 2, 0, 2] },
                    rowValue: { fontSize: 8, margin: [0, 2, 10, 2], alignment: 'right' },
                    rowLabelBold: { fontSize: 8, bold: true, margin: [10, 2, 0, 2] },
                    rowValueBold: { fontSize: 8, bold: true, margin: [0, 2, 10, 2], alignment: 'right' },
                    totalValue: { fontSize: 12, bold: true }
                },
                defaultStyle: { font: 'Roboto' }
            };

            const pdfDocGenerator = pdfMake.createPdf(docDefinition);
            pdfDocGenerator.getBlob((blob) => {
                const currentPdfData = { blob, filename: pdfFileName + '.pdf' };
                resolve(currentPdfData);
            });
        } catch (error) {
            alertNotify({
                type: "error",
                text: labels.nteErrorPDF,
                icon: "fas fa-exclamation-triangle",
                timeout: 3000
            });
            reject(error);
        }
    });
}

tablePayroll.on('dblclick', 'tbody tr', (e) => {
    const clickedCell = e.target.closest('td')
    if (clickedCell && clickedCell.classList.contains('btnaction')) {
        return
    }

    let classList = e.currentTarget.classList

    classList.contains('selected') ? classList.remove('selected') : classList.add('selected')

    updateModalMailState()
})

function updateModalMailState() {
    if ($('#tablePayroll tr.selected').length > 0) {
        $('#mailPayroll').removeAttr('disabled')
    } else {
        $('#mailPayroll').attr('disabled', '')
    }
}

function loadmailData(maildata) {
    if ($.fn.DataTable.isDataTable('#tableMails')) {
        $('#tableMails').DataTable().destroy();
        $('#tableMails').empty();
    }

    tableMails = $('#tableMails').DataTable({
        data: maildata,
        language: {
            url: TABLELANG,
        },
        dom: '<"row"<"#mailPayrollButtons.col-1 mt-2 mb-2"><"col-6"P><"col"f>>ti',
        responsive: true,
        fixedHeader: true,
        fixedFooter: true,
        paging: false,
        scrollX: false,
        searchPanes: {
            initCollapsed: true,
            orderable: false,
            cascadePanes: true,
            columns: [1],
            className: 'mt-0 ast-pane',
        },
        scrollY: 'calc(100vh - 520px)',
        scrollCollapse: true,
        drawCallback: function (settings) {
            mailTemp = []
            if (mtps && mtps.length > 0) {
                const template = mtps[0]
                if (template.mtpid && template.mtpname) {
                    const ids = template.mtpid.split(',')
                    const names = template.mtpname.split(',')
                    const subjects = template.subject ? template.subject.split(',') : []
                    const mailaccountids = template.mailaccountid ? String(template.mailaccountid).split(',') : []

                    ids.forEach((id, index) => {
                        if (id.trim() && names[index]) {
                            mailTemp.push({
                                id: id.trim(),
                                text: names[index].trim(),
                                subject: subjects[index] || '',
                                mailaccountid: mailaccountids[index] || '',
                            })
                        }
                    })
                }
            }

            function templateResult(data) {
                if (!data.id) return data.text
                var $option = $('<span>' + data.text + '</span>')
                $option.attr('data-subject', data.subject)
                return $option
            }

            function templateSelection(data) {
                if (!data.id) return data.text
                var $selected = $('<span>' + data.text + '</span>')
                $selected.attr('data-subject', data.subject)
                return $selected
            }

            $('#mailTemplate').empty().trigger('change')
            $('#mailTemplate').select2({
                dropdownParent: $('#modalBulkMails'),
                width: '100%',
                data: mailTemp,
                templateResult: templateResult,
                templateSelection: templateSelection,
            })
            mailTemp.forEach((item) => {
                $(`#mailTemplate option[value="${item.id}"]`).attr('data-subject', item.subject)
            })

            $("[id^='mailTemplate_']").empty().trigger('change')
            $("[id^='mailTemplate_']")
                .select2({
                    dropdownParent: $('#modalBulkMails'),
                    width: '100%',
                    data: mailTemp,
                    allowClear: true,
                    placeholder: { id: 0, text: '' },
                    templateResult: templateResult,
                    templateSelection: templateSelection,
                })
                .on('select2:unselecting', function () {
                    $(this).data('unselecting', true)
                })
                .on('select2:opening', function (e) {
                    if ($(this).data('unselecting')) {
                        $(this).removeData('unselecting')
                        e.preventDefault()
                    }
                })
            $("[id^='mailTemplate_']").each(function () {
                const $sel = $(this)
                mailTemp.forEach((item) => {
                    $sel.find(`option[value="${item.id}"]`).attr('data-subject', item.subject)
                })
            })

            const emailSelects = $("[id^='contactemail_ids_']")
            if (emailSelects.length > 0) {
                emailSelects.each(function () {
                    const $select = $(this)
                    if (!$select.hasClass('select2-hidden-accessible')) {
                        $select.select2({
                            dropdownParent: $('#modalBulkMails'),
                            multiple: true,
                            width: '100%',
                            tags: true,
                            tokenSeparators: [',', ';', ' '],
                            createTag: function (params) {
                                const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                                if (emailRegex.test(params.term)) {
                                    return {
                                        id: params.term,
                                        text: params.term,
                                        newTag: true,
                                    }
                                }
                                return null
                            },
                        })
                    }
                })
            }
        },
        columns: [
            {
                title: labels.lblName,
                data: 'NombreEmpleado',
                visible: true,
                sortable: false,
                width: '20%',
            },
            {
                title: labels.lblEmail,
                data: null,
                visible: true,
                sortable: false,
                //width: '35%',

                render: function (data, type, row) {
                    let mailsData = row.mails && row.mails.length > 0
                        ? row.mails
                        : [{ type: 'to', name: row.NombreEmpleado, address: row.Correo, id: row.Correo }];

                    return cellRender({
                        type: 'select',
                        options: mailsData.map(m => ({ id: JSON.stringify(m), name: m.address })),
                        id: 'contactemail_ids_' + row.id,
                        name: 'contactemail_ids[]',
                        data: JSON.stringify(mailsData[0]),
                        class: 'form-control form-control-sm',
                    })
                },
            },
            {
                title: labels.btnPreview,
                data: null,
                visible: true,
                sortable: false,
                width: '10%',
                render: function (data, type, row) {
                    return cellRender({
                        type: 'button',
                        class: 'btn btn-sm btn-primary px-1',
                        id: 'mailPreview_' + data.id,
                        title: labels.btnPreview,
                        icon: 'far fa-eye',
                        data: data.id,
                        name: 'mailPreview',
                        dataTags: {
                            id: data.id,
                            name: data.NombreEmpleado,
                        },
                    })
                },
                sortable: false,
            },
        ]
    });

}

$(document).on('click', '[id^="btnSendMail_"]', async function () {
    const id = $(this).closest('tr').attr('data-id');

    currentMailData = [];
    let match = globalMailData.find(item => item.id == id);

    let fpFrom = document.querySelector("#calendPayroll")?._flatpickr;
    let fromDate = '';

    // ── Limpiar error previo
    $(fpFrom.altInput).removeClass("is-invalid");
    $(".calendPayroll-error").remove();

    if (fpFrom.selectedDates.length > 0) {
        fromDate = fpFrom.formatDate(fpFrom.selectedDates[0], "Y-m-d");
    }

    if (!fromDate) {
        $(fpFrom.altInput).addClass("is-invalid");
        $(fpFrom.altInput).after(`<div class="invalid-feedback calendPayroll-error">Seleccione una fecha.</div>`);

        // ── Toast de error
        alertNotify({
            type: 'warning',
            text: 'Debe seleccionar una fecha.',
            icon: 'fas fa-calendar-times',
            timeout: 3000,
        });

        // ── Quitar error al seleccionar fecha
        fpFrom.config.onChange = [function () {
            $(fpFrom.altInput).removeClass("is-invalid");
            $(".calendPayroll-error").remove();
        }];

        return;
    }
    if (match) {
        currentMailData.push({
            ...match,
            NAME: match.Nombre,
            CODE: match.id.toString(),
            totalopen: formatCurrency('USD', match.Total, chrLocale),
            mails: [
                {
                    type: 'to',
                    name: match.Nombre,
                    address: match.Correo,
                    id: match.Correo
                }
            ]
        });
    }
    $('#bulkSubject').val('')

    $('#modalBulkMails').modal('show')
    loadmailData(currentMailData);
    $('#mailComment').val('')
    $('#replyto').empty().trigger('change')
    $('#replyto').select2({
        dropdownParent: $('#modalBulkMails'),
        width: '100%',
        data: replytos,
    });
});

$(document).on('click', '#mailPayroll', function () {
    currentMailData = [];
    let fpFrom = document.querySelector("#calendPayroll")?._flatpickr;
    let fromDate = '';

    // ── Limpiar error previo
    $(fpFrom.altInput).removeClass("is-invalid");
    $(".calendPayroll-error").remove();

    if (fpFrom.selectedDates.length > 0) {
        fromDate = fpFrom.formatDate(fpFrom.selectedDates[0], "Y-m-d");
    }

    if (!fromDate) {
        $(fpFrom.altInput).addClass("is-invalid");
        $(fpFrom.altInput).after(`<div class="invalid-feedback calendPayroll-error">Seleccione una fecha.</div>`);

        // ── Toast de error
        alertNotify({
            type: 'warning',
            text: 'Debe seleccionar una fecha.',
            icon: 'fas fa-calendar-times',
            timeout: 3000,
        });

        // ── Quitar error al seleccionar fecha
        fpFrom.config.onChange = [function () {
            $(fpFrom.altInput).removeClass("is-invalid");
            $(".calendPayroll-error").remove();
        }];

        return;
    }
    tablePayroll.rows('.selected').data().each(function (data) {
        let match = globalMailData.find(item => item.id == data.id);
        if (match) {
            currentMailData.push({
                ...match,
                NAME: match.Nombre,
                CODE: match.id.toString(),
                totalopen: formatCurrency('USD', match.Total, chrLocale),
                mails: [
                    {
                        type: 'to',
                        name: match.Nombre,
                        address: match.Correo,
                        id: match.Correo
                    }
                ]
            });
        }
    });

    $('#bulkSubject').val('')

    $('#modalBulkMails').modal('show')
    loadmailData(currentMailData);
    $('#mailComment').val('')
    $('#replyto').empty().trigger('change')
    $('#replyto').select2({
        dropdownParent: $('#modalBulkMails'),
        width: '100%',
        data: replytos,
    })
})

$(document).on('click', "[id^='mailPreview_']", async function () {
    $('#previewMailModal').modal('show')
    $('#ViewAttachments').attr('data-id', $(this).attr('data-id'))
    $('#subject').text($(this).attr('data-name').trim())
    document.getElementById('smpreview').innerHTML = ''
    let date = document.querySelector("#calendPayroll")?._flatpickr;
    let newdate = '';
    // ── Limpiar error previo
    $(date.altInput).removeClass("is-invalid");

    if (date.selectedDates.length > 0) {
        newdate = date.formatDate(date.selectedDates[0], "d-M-Y");
    }
    $.ajax({
        url: CTRL_PAYROLL,
        type: 'POST',
        data: {
            action: 'M',
            part: 'MP',
            date: newdate,
            mailData: [
                {
                    mailTemplate: parseInt($('#mailTemplate').val()),
                    mailSubject: 'preview',
                    mailFrom: 'preview',
                    mailTo: ['preview'],
                    mailCc: ['preview'],
                    mailReplyTo: $('#replyto').select2('data')[0]
                        ? {
                            id: $('#replyto').select2('data')[0].id,
                            name: $('#replyto').select2('data')[0].name,
                            email: $('#replyto').select2('data')[0].text,
                        }
                        : null,
                    name: $(this).attr('data-name'),
                    lang: chrLang,
                },
            ],
        },
        success: function (response) {
            if (response.result) {
                const mailResult = response;

                let body = mailResult.body;

                // Reemplazos
                body = body.replace(/{CompanyName}/g, namecompany);

                document.getElementById('smpreview').innerHTML = body;
            }
        },
        error: function () {
            document.getElementById('smpreview').innerHTML = `<body>...</body>`
        },
    })
})

$(document).on('click', '#ViewAttachments', async function () {
    $('#apAttachmentPreview').modal('show')
    $('#apAttachmentPreviewBody').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Generando PDF...</div>');

    document.getElementById('apAttachmentPreviewBody').innerHTML = ''
    try {
        ;
        const result = await generatePDF(globalMailData.find(item => item.id == $(this).attr('data-id')));
        const pdfUrl = URL.createObjectURL(result.blob);

        $('#apAttachmentPreviewBody').html(`
             <iframe 
                 src="${pdfUrl}" 
                 width="100%" 
                 height="800px" 
                 style="border: none;"
                 allowfullscreen>
             </iframe>
         `);

    } catch (error) {
        $('#apAttachmentPreviewBody').html('<div class="text-danger p-3">Error al generar la vista previa.</div>');
    }
});

$('#modalBulkMails').on('shown.bs.modal', function () {
    $('#tableMails').DataTable().columns.adjust();
});

$('#copycc').click(function (e) {
    e.preventDefault()
    $('#mailCc').val($('#replyto').select2('data')[0].text)
})


function getGlobalFormData() {
    const replyToData = $('#replyto').select2('data')[0];
    const selectedTemplateId = $('#mailTemplate').val() || '';
    const selectedTemplateData = mailTemp ? mailTemp.find(item => item.id == selectedTemplateId) || {} : {};

    return {
        mailReplyTo: replyToData
            ? { id: replyToData.id, name: replyToData.name, email: replyToData.text }
            : null,
        mailTemplate: selectedTemplateId,
        mailSubject: selectedTemplateData.subject || $('#mailTemplate option:selected').attr('data-subject') || '',
        mailaccountid: selectedTemplateData.mailaccountid,
        mailCc: ($('#mailCc').val() || '').trim(),
    };
}

function validateMailRows(tableData) {
    let valid = true;
    tableData.forEach((rowData) => {
        const $email = $(`#contactemail_ids_${rowData.id}`);
        const emails = $email.val();
        if (!emails || emails.length < 1) {
            valid = false;
            $email.addClass('is-invalid');
        } else {
            $email.removeClass('is-invalid');
        }
    });
    return valid;
}

function blobToBase64(blob) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result.split(',')[1]);
        reader.readAsDataURL(blob);
    });
}

async function buildMailEntry(rowData, globalForm) {
    const $emailSelect = $(`#contactemail_ids_${rowData.id}`);
    const emailVals = $emailSelect.val() || [];

    const mailToArr = emailVals.map(val => {
        try {
            return JSON.parse(val);
        } catch (e) {
            return {
                type: 'to',
                name: val,
                address: val,
                id: val
            };
        }
    });

    const rowTemplateId = $(`#mailTemplate_${rowData.id}`).val() || '';
    const rowTemplate = rowTemplateId || globalForm.mailTemplate;

    let mailaccountId = globalForm.mailaccountid;
    if (rowTemplateId && mailTemp) {
        const rowTempData = mailTemp.find(item => item.id == rowTemplateId);
        if (rowTempData && rowTempData.mailaccountid) {
            mailaccountId = rowTempData.mailaccountid;
        }
    }

    const ccArray = globalForm.mailCc ? [globalForm.mailCc] : [];

    const pdfResult = await generatePDF(rowData);
    const pdfBase64 = await blobToBase64(pdfResult.blob);

    return {
        id: rowData.id,
        name: rowData.Nombre || rowData.NAME || '',
        mailTo: mailToArr,
        mailTemplate: rowTemplate,
        mailSubject: globalForm.mailSubject,
        mailReplyTo: globalForm.mailReplyTo,
        mailCc: ccArray,
        mailaccountid: mailaccountId,
        fileName: pdfResult.filename,
        pdfBase64: pdfBase64,
        lang: chrLang,
        mes: rowData.Mes || '',
        total: rowData.Total || 0,
    };
}

$('#sendMailPayroll').on('click', async function () {
    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + labels.lblSending + '...');

    let date = document.querySelector("#calendPayroll")?._flatpickr;
    let newdate = '';
    let datedatabase = '';

    $(date.altInput).removeClass("is-invalid");

    if (date.selectedDates.length > 0) {
        newdate = date.formatDate(date.selectedDates[0], "d-M-Y");
        datedatabase = date.formatDate(date.selectedDates[0], "Y-m-d");
    }

    const tableData = tableMails.rows().data().toArray().slice();
    const tableNodes = tableMails.rows().nodes().toArray().slice();
    const total = tableData.length;

    if (total === 0) {
        alertNotify({ type: 'warning', text: labels.lblError, timeout: 3000 });
        $btn.prop('disabled', false).text(labels.lblSend);
        return;
    }

    if (!validateMailRows(tableData)) {
        alertNotify({ type: 'error', text: labels.nteRequiredFields, timeout: 3000 });
        $btn.prop('disabled', false).text(labels.lblSend);
        return;
    }

    const globalForm = getGlobalFormData();
    const planillaData = {};
    tableData.forEach(row => {
        const cedula = row.Cedula || row.cedula;
        if (cedula) planillaData[cedula] = row;
    });

    // ── Overlay opaco sobre el modal-body ─────────────────────
    // Cubre TODO — el usuario no puede ver nada de lo que pasa debajo
    const $modalBody = $btn.closest('.modal-body, .modal-content, .modal');
    $modalBody.css('position', 'relative');

    $('#mailOverlay').remove();
    $modalBody.append(`
        <div id="mailOverlay" style="
            position:        absolute;
            inset:           0;
            background:      var(--bs-body-bg, #fff);
            z-index:         9999;
            display:         flex;
            flex-direction:  column;
            align-items:     center;
            justify-content: center;
            gap:             1.5rem;
            padding:         2rem;
        ">
            <i class="fas fa-paper-plane fa-2x text-muted"></i>
            <div style="width: 100%; max-width: 400px;">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted" id="mailProgressLabel">0 / ${total}</small>
                    <small class="text-muted" id="mailProgressPct">0%</small>
                </div>
                <div class="progress" style="height: 8px; border-radius: 4px;">
                    <div id="mailProgressBar"
                         class="progress-bar bg-success"
                         role="progressbar"
                         style="width: 0%; transition: width 0.3s ease;">
                    </div>
                </div>
            </div>
            <small class="text-muted" id="mailProgressSub">Procesando correos...</small>
        </div>
    `);

    const barEl = document.getElementById('mailProgressBar');
    const labelEl = document.getElementById('mailProgressLabel');
    const pctEl = document.getElementById('mailProgressPct');
    const subEl = document.getElementById('mailProgressSub');

    // ── LOOP: AJAX debajo del overlay, nadie lo ve ─────────────
    const results = [];
    let successCount = 0;
    let errorCount = 0;

    for (let i = 0; i < total; i++) {
        try {
            const mailEntry = await buildMailEntry(tableData[i], globalForm);

            const formData = new FormData();
            formData.append('action', 'M');
            formData.append('part', 'SM');
            formData.append('date', newdate);
            formData.append('dateformat', datedatabase);
            formData.append('mailData', JSON.stringify([mailEntry]));
            formData.append('planillaData', JSON.stringify(planillaData));

            const response = await $.ajax({
                url: CTRL_PAYROLL,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                global: false

            });

            const ok = !!(response?.result);
            results.push(ok);
            ok ? successCount++ : errorCount++;

        } catch {
            results.push(false);
            errorCount++;
        }

        const done = i + 1;
        const pct = Math.round((done / total) * 100);
        barEl.style.width = pct + '%';
        labelEl.textContent = `${done} / ${total}`;
        pctEl.textContent = `${pct}%`;
        subEl.textContent = `Enviados: ${successCount}  |  Errores: ${errorCount}`;
    }

    // ── FIN: escribir badges con overlay aún puesto ────────────
    results.forEach((ok, i) => {
        const $lastCell = $(tableNodes[i]).find('td:last');
        $lastCell.find('.status-badge').remove();
        $lastCell.append(
            ok
                ? '<span class="badge status-badge ml-1" style="background-color:#28a745!important;color:#fff!important;">Enviado</span>'
                : '<span class="badge status-badge ml-1" style="background-color:#dc3545!important;color:#fff!important;">Error</span>'
        );
    });

    // ── Quitar overlay — el usuario ve la tabla ya terminada ───
    $('#mailOverlay').remove();

    if (errorCount === 0) {
        alertNotify({ type: 'success', text: labels.lblSuccess, icon: 'fas fa-check', timeout: 3000 });
        //setTimeout(() => $('#modalBulkMails').modal('hide'), 800);
    } else {
        alertNotify({
            type: 'warning',
            text: `Proceso finalizado. Enviados: ${successCount} | Errores: ${errorCount}`,
            icon: 'fas fa-exclamation-triangle',
            timeout: 5000
        });
    }

    $btn.prop('disabled', false).text(labels.lblSend);
});