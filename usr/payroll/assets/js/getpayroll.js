var CTRL_PAYROLL = "/" + chrLocale + "/controller/payroll";
var TableDataMnth;
var DateTime = luxon.DateTime;
let deconstructionData = {};
let currentPeriods = [];

const currencyFormatterCRC = new Intl.NumberFormat('es-CR', {
    style: 'currency',
    currency: 'CRC',
    minimumFractionDigits: 2
});

$(document).ready(function () {
    // Inicializar tabla vacía
    initTable([]);

    // ── Flatpickr en un contenedor fuera del DataTables si es posible, 
    // pero como depende de DataTables dom, lo manejamos en el drawCallback
});

function initTable(tableData, dynamicColumns = null) {
    if ($.fn.DataTable.isDataTable('#tableMonthsData')) {
        $('#tableMonthsData').DataTable().clear().destroy();
        $('#tableMonthsData').empty();
    }

    let cols = dynamicColumns;
    if (!cols) {
        // Columnas por defecto (vacías)
        cols = [
            { title: "Código", data: "Codigo", className: "dt-left dt-nowrap" },
            { title: labels.lblName || "Nombre", data: "NombreEmpleado", className: "dt-left dt-nowrap" }
        ];
    }

    TableDataMnth = $("#tableMonthsData").DataTable({
        data: tableData,
        language: {
            url: TABLELANG,
        },
        columns: cols,
        dom: '<"row"<"#ctrlConfCustomMonthMonth.col-6"><"col-6"f>>ti',
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        footerCallback: function (row, data, start, end, display) {
            let api = this.api();
            if (data.length === 0) return;

            // Update footer totals
            // For each dynamic column + total (from index 2 onwards)
            for (let i = 2; i < cols.length; i++) {
                let total = api
                    .column(i, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                let ths = $(api.table().footer()).find('th');
                if (ths.length > i) {
                    $(ths[i]).html(currencyFormatterCRC.format(total));
                }
            }
        },
        drawCallback: function () {
            const containerId = 'ctrlConfCustomMonthMonth';
            const container = document.getElementById(containerId);
            if (!container) return;

            if (!document.getElementById('calenStartI')) {
                const innerContainer = document.createElement('div');
                innerContainer.classList.add('d-flex', 'align-items-center', 'gap-2');

                const calFromI = document.createElement('div');
                calFromI.classList.add('col-6', 'col-sm-6', 'col-md-6', 'col-lg-6', 'pd-1');

                const fromInputI = document.createElement('input');
                fromInputI.type = 'text';
                fromInputI.classList.add('form-control', 'form-control-sm');
                fromInputI.id = 'calenStartI';
                fromInputI.placeholder = labels.lblFrom || 'Desde';
                fromInputI.title = labels.tblStart || 'Inicio';
                fromInputI.setAttribute('readonly', '');

                calFromI.appendChild(fromInputI);
                innerContainer.appendChild(calFromI);
                container.appendChild(innerContainer);

                // ── Flatpickr con altInput
                let fp = flatpickr('#calenStartI', {
                    enableTime: false,
                    altInput: true,
                    altFormat: "d-M-Y",   // visible: 22-Apr-2026
                    dateFormat: "Y-m-d",  // valor real para el AJAX
                    locale: chrLang,
                    onChange: function (selectedDates, dateStr) {
                        if (!dateStr) return;
                        fetchData(dateStr);
                    }
                });

                // Si ya teníamos una fecha seleccionada, la mantenemos visualmente
                if (window.lastSelectedDate) {
                    fp.setDate(window.lastSelectedDate, false);
                }
            }
        }
    });

    // Agregar thead y tfoot si no existen para el footerCallback
    if ($('#tableMonthsData tfoot').length === 0) {
        let tfoot = $('<tfoot><tr></tr></tfoot>');
        cols.forEach(col => {
            tfoot.find('tr').append('<th></th>');
        });
        $('#tableMonthsData').append(tfoot);
    }
}

function fetchData(dateStr) {
    window.lastSelectedDate = dateStr;

    $.ajax({
        type: "POST",
        url: CTRL_PAYROLL,
        data: {
            action: 'R',
            part: 'B',
            startDateI: dateStr  // ya viene en Y-m-d
        },
        dataType: "json",
        success: function (response) {
            const dataR = response.data;

            // 1. Calcular los 5 periodos
            const startDate = DateTime.fromISO(dateStr);
            currentPeriods = [];
            for (let i = 0; i < 5; i++) {
                const periodDate = startDate.plus({ months: i });
                let label = periodDate.setLocale('es').toFormat('MMMM yyyy');
                currentPeriods.push({
                    label: label.charAt(0).toUpperCase() + label.slice(1),
                    yearMonth: periodDate.toFormat('yyyy-MM')
                });
            }

            if (dataR && dataR.length > 0) {
                // 2. Agrupar los datos
                let groupedData = {};
                deconstructionData = {};

                dataR.forEach(item => {
                    let key = item.cedula;
                    if (!groupedData[key]) {
                        groupedData[key] = {
                            Codigo: item.codigo,
                            NombreEmpleado: item.nombre,
                            Cedula: item.cedula,
                            Correo: item.correo_electronico,
                            CuentaBancaria: item.cuenta_bancaria,
                            periodTotals: [0, 0, 0, 0, 0],
                            TotalRow: 0
                        };
                        deconstructionData[key] = {
                            Nombre: item.nombre,
                            periods: [[], [], [], [], []]
                        };
                    }

                    let itemDate = item.fecha_pago;
                    if (itemDate) {
                        let itemYM = itemDate.substring(0, 7);
                        let pIndex = currentPeriods.findIndex(p => p.yearMonth === itemYM);

                        let totalPagar = (item.salario_laborado || 0) +
                            (item.comisiones || 0) -
                            (item.ccss || 0) -
                            (item.impuesto_renta || 0) -
                            (item.anticipo || 0) -
                            (item.otros_rebajos || 0);

                        if (pIndex !== -1) {
                            groupedData[key].periodTotals[pIndex] += totalPagar;
                            groupedData[key].TotalRow += totalPagar;
                            deconstructionData[key].periods[pIndex].push(item);
                        }
                    }
                });

                let tableData = Object.values(groupedData);

                // 3. Crear las columnas dinámicas
                let newColumns = [
                    {
                        title: "Código",
                        data: "Codigo",
                        orderable: false,
                        className: "dt-left dt-nowrap",
                        render: function (data, type, row) {
                            if (!data) return '';
                            return `
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    data-cedula="${row.Cedula}">
                                ${data}
                            </button>`;
                        }
                    },
                    {
                        title: labels.lblName || "Nombre",
                        data: "NombreEmpleado",
                        orderable: false,
                        className: "dt-left dt-nowrap",
                        render: function (data, type, row) {
                            if (!data) return '';
                            var truncated = data.length > 20 ? data.substring(0, 20) + '...' : data;
                            return '<span title="' + data + '">' + truncated + '</span>';
                        }
                    }
                ];

                currentPeriods.forEach((p, index) => {
                    newColumns.push({
                        title: p.label,
                        data: `periodTotals.${index}`,
                        orderable: false,
                        className: "dt-right dt-nowrap",
                        render: function (data) {
                            return currencyFormatterCRC.format(parseFloat(data || 0));
                        }
                    });
                });

                newColumns.push({
                    title: "Total",
                    data: "TotalRow",
                    orderable: false,
                    className: "dt-right dt-nowrap fw-bold",
                    render: function (data) {
                        return currencyFormatterCRC.format(parseFloat(data || 0));
                    }
                });

                // Reinicializar tabla
                initTable(tableData, newColumns);
            } else {
                initTable([]);
                alertNotify({ type: "warning", text: labels.nteNotData || "No data", icon: "fas fa-check", timeout: 3000 });
            }
        }
    });
}

// 4. Modal Deconstruction on Row Click
$(document).on('click', '#tableMonthsData tbody tr', function (e) {
    // Si se hace clic en el botón del código, ignorar o manejar si es necesario
    if ($(e.target).closest('button').length > 0) return;

    let data = TableDataMnth.row(this).data();
    if (!data) return;

    let cedula = data.Cedula;
    let employeeData = deconstructionData[cedula];
    if (!employeeData) return;

    $('#deconstructionModalTitle').text('Detalles de Periodos: ' + employeeData.Nombre);

    // Preparar el thead del modal
    let theadTr = $('#deconstructionHeaders');
    theadTr.empty();
    theadTr.append('<th>Concepto</th>');
    currentPeriods.forEach(p => {
        theadTr.append('<th class="text-end">' + p.label + '</th>');
    });
    theadTr.append('<th class="text-end">Total</th>');

    // Preparar el tbody del modal
    let tbody = $('#deconstructionBody');
    tbody.empty();

    let conceptos = [
        { key: 'salario_mensual', label: 'Salario Mensual', type: 'ingreso' },
        { key: 'salario_laborado', label: 'Salario Lab.', type: 'ingreso' },
        { key: 'dias_laborados', label: 'Días Lab.', type: 'info' },
        { key: 'comisiones', label: 'Comisiones', type: 'ingreso' },
        { key: 'incapacidades', label: 'Incapacidades', type: 'ingreso' },
        { key: 'ccss', label: 'CCSS', type: 'deduccion' },
        { key: 'impuesto_renta', label: 'Imp Renta', type: 'deduccion' },
        { key: 'anticipo', label: 'Anticipo', type: 'deduccion' },
        { key: 'otros_rebajos', label: 'Otros Rebajos', type: 'deduccion' }
    ];

    let totalesConcepto = {};
    conceptos.forEach(c => totalesConcepto[c.key] = 0);

    conceptos.forEach(concepto => {
        let tr = $('<tr></tr>');
        tr.append('<td>' + concepto.label + '</td>');
        let rowTotal = 0;

        currentPeriods.forEach((p, pIndex) => {
            let periodItems = employeeData.periods[pIndex];
            let sumVal = 0;
            periodItems.forEach(item => {
                sumVal += parseFloat(item[concepto.key] || 0);
            });
            rowTotal += sumVal;
            totalesConcepto[concepto.key] += sumVal;

            if (concepto.type === 'info') {
                tr.append('<td class="text-end">' + sumVal + '</td>');
            } else {
                tr.append('<td class="text-end">' + currencyFormatterCRC.format(sumVal) + '</td>');
            }
        });

        if (concepto.type === 'info') {
            tr.append('<td class="text-end fw-bold">' + rowTotal + '</td>');
        } else {
            tr.append('<td class="text-end fw-bold">' + currencyFormatterCRC.format(rowTotal) + '</td>');
        }
        tbody.append(tr);
    });

    // Preparar el tfoot del modal (Total Pagar)
    let tfoot = $('#deconstructionFooter');
    tfoot.empty();
    let trFoot = $('<tr></tr>');
    trFoot.append('<td>Total Pagar</td>');

    let granTotal = 0;
    currentPeriods.forEach((p, pIndex) => {
        let periodItems = employeeData.periods[pIndex];
        let totalPagarPeriod = 0;
        periodItems.forEach(item => {
            totalPagarPeriod += (item.salario_laborado || 0) +
                (item.comisiones || 0) -
                (item.ccss || 0) -
                (item.impuesto_renta || 0) -
                (item.anticipo || 0) -
                (item.otros_rebajos || 0);
        });
        granTotal += totalPagarPeriod;
        trFoot.append('<td class="text-end">' + currencyFormatterCRC.format(totalPagarPeriod) + '</td>');
    });
    trFoot.append('<td class="text-end">' + currencyFormatterCRC.format(granTotal) + '</td>');
    tfoot.append(trFoot);

    $('#modalDeconstruction').modal('show');
});
