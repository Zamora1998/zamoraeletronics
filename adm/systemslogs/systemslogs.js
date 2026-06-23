var CTRL_SYSLOGS = "/" + chrLocale + "/controller/systemlogs"; // Ajusta esta ruta si es diferente en tu enrutador

var tableSysLogs = null;
var activeLogTypeTables = []; // Tablas activas para filtrar

$(document).ready(function () {

    // Inicializar Select2 para filtrar por LogType
    $('#filterLogType').select2({
        theme: 'bootstrap-5',
        placeholder: labels.tblActions || 'Filtrar por módulo',
        allowClear: true,
        ajax: {
            url: CTRL_SYSLOGS,
            type: 'POST',
            dataType: 'json',
            data: function () {
                return { action: 'R', part: 'LTT' };
            },
            processResults: function (response) {
                var items = response.data || [];
                return {
                    results: items.map(function (item) {
                        return {
                            id: item.logtype_id,
                            text: item.logtype_name,
                            tables: item.tables || ''
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Cuando se selecciona un logtype, filtrar la tabla
    $('#filterLogType').on('select2:select', function (e) {
        var selected = e.params.data;
        if (selected.tables) {
            activeLogTypeTables = selected.tables.split(',').map(function (t) { return t.trim().toLowerCase(); });
        } else {
            activeLogTypeTables = [];
        }
        tableSysLogs.draw();
    });

    // Cuando se limpia el select, quitar el filtro
    $('#filterLogType').on('select2:clear', function () {
        activeLogTypeTables = [];
        tableSysLogs.draw();
    });

    // Filtro personalizado de DataTables para la columna tableh
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData) {
        if (activeLogTypeTables.length === 0) return true; // Sin filtro activo, mostrar todo
        var rowTable = (rowData.tableh || '').toLowerCase();
        // Verificar si la tabla del registro está en la lista de tablas del logtype seleccionado
        return activeLogTypeTables.some(function (t) {
            return rowTable.indexOf(t) !== -1;
        });
    });
    // Inicializamos el DataTable
    tableSysLogs = $("#tableSystemLogs").DataTable({
        ajax: {
            url: CTRL_SYSLOGS,
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
        order: [[6, "desc"]], // Orden predeterminado: La columna de fecha (índice 6) de más reciente a antiguo
        columns: [
            {
                title: "ID",
                data: "id",
                className: "dt-center align-middle",
                width: "60px",
                searchPanes: { show: false }
            }, // 0
            {
                title: labels.tblUsers,
                data: "full_name",
                className: "align-middle",
                searchPanes: { show: true },
                render: function (data, type, row) {
                    var userStr = data ? data : (row.email ? 'Sin nombre' : 'Invitado/API');
                    var emailStr = row.email ? row.email : 'N/A';
                    if (type === 'display') {
                        return `<strong>${userStr}</strong><br><small class="text-muted"><i class="fas fa-envelope me-1"></i>${emailStr}</small>`;
                    }
                    return userStr;
                },
                width: "60px"

            }, // 1
            {
                title: labels.tblMethod + ' / ' + labels.tblActions,
                data: "method",
                className: "dt-center align-middle dt-nowrap",
                searchPanes: { show: true },
                render: function (data, type, row) {
                    var plainText = data + (row.action_code ? ` ${row.action_code}` : '');
                    if (type !== 'display') return plainText;

                    let badgeClass = "bg-secondary";
                    if (data === 'POST') badgeClass = "bg-success";
                    else if (data === 'PUT') badgeClass = "bg-warning text-dark";
                    else if (data === 'DELETE') badgeClass = "bg-danger";

                    let actionBadgeClass = "bg-dark";
                    if (row.action_code === 'C') actionBadgeClass = "bg-success";
                    else if (row.action_code === 'UP' || row.action_code === 'U') actionBadgeClass = "bg-warning text-dark";
                    else if (row.action_code === 'DELETE' || row.action_code === 'D') actionBadgeClass = "bg-danger";

                    let actionCode = row.action_code ? ` <span class="badge ${actionBadgeClass}">${row.action_code}</span>` : '';
                    return `<h5><span class="badge ${badgeClass}">${data}</span>${actionCode}</h5>`;
                }
            }, // 2
            {
                title: labels.tblUrl,
                data: "url",
                className: "align-middle",
                searchPanes: { show: true },
                render: function (data, type, row) {
                    if (!data) return '';
                    if (type !== 'display') return data;

                    const maxLength = 30;
                    let displayText = data.length > maxLength ? data.substr(0, maxLength) + '...' : data;
                    return `<span title="${data}" class="text-truncate" style="max-width: 250px;">${displayText}</span>`;
                }
            }, // 3
            {
                title: labels.tblTable,
                data: "tableh",
                className: "dt-center align-middle",
                width: "60px",
                searchPanes: { show: false }
            }, // 0
            {
                title: labels.tblJsonData,
                data: "payload",
                className: "align-middle payload-cell",
                width: "20%",
                searchPanes: { show: false },
                render: function (data, type, row) {
                    if (!data || data === 'null' || data === '[]' || data === '{}') {
                        return '<span class="text-muted fst-italic">Sin datos adicionales</span>';
                    }

                    let summaryHtml = '';
                    let fullHtml = '';
                    try {
                        let parsedData = JSON.parse(data);
                        let audit = parsedData.audit || null;

                        fullHtml = JSON.stringify(parsedData, null, 2);

                        // Resumen: solo old/new clave-valor
                        if (audit && audit.diff) {
                            let diffOld = audit.diff.old || {};
                            let diffNew = audit.diff.new || {};
                            let allKeys = Array.from(new Set([...Object.keys(diffOld), ...Object.keys(diffNew)]));

                            if (allKeys.length > 0) {
                                let lines = [];
                                allKeys.forEach(k => {
                                    let oVal = diffOld[k] !== undefined ? diffOld[k] : null;
                                    let nVal = diffNew[k] !== undefined ? diffNew[k] : null;

                                    let soVal = oVal !== null ? String(oVal).substring(0, 50) + (String(oVal).length > 50 ? '...' : '') : '';
                                    let snVal = nVal !== null ? String(nVal).substring(0, 50) + (String(nVal).length > 50 ? '...' : '') : '';

                                    soVal = $('<div>').text(soVal).html();
                                    snVal = $('<div>').text(snVal).html();

                                    if (oVal !== null && nVal !== null) {
                                        lines.push(`<div class="text-truncate"><strong>${k}:</strong> <span class="text-danger text-decoration-line-through">${soVal}</span> <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 0.8em;"></i> <span class="text-success">${snVal}</span></div>`);
                                    } else if (nVal !== null) {
                                        lines.push(`<div class="text-truncate"><strong>${k}:</strong> <span class="text-success">${snVal}</span></div>`);
                                    } else if (oVal !== null) {
                                        lines.push(`<div class="text-truncate"><strong>${k}:</strong> <span class="text-danger">${soVal}</span></div>`);
                                    }
                                });
                                summaryHtml = `<div style="font-size: 0.85em; max-height: 150px; overflow-y: auto;">${lines.join('')}</div>`;
                            } else {
                                summaryHtml = `<div class="text-muted" style="font-size: 0.85em;"><i class="fas fa-info-circle"></i> ${labels.tblActions}: ${row.action_code || 'N/A'} (${labels.lblAnyChangesRegistrered})</div>`;
                            }
                        } else {
                            summaryHtml = `<div class="text-muted" style="font-size: 0.85em;"><i class="fas fa-info-circle"></i> ${labels.lblIncompleteInfo}</div>`;
                        }

                    } catch (e) {
                        summaryHtml = `<div class="text-muted" style="font-size: 0.85em;"><i class="fas fa-info-circle"></i> Datos (Formato no JSON)</div>`;
                        // Fallback original para datos no json
                        fullHtml = `<pre class="mb-0 payload-full" style="display: none; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 0.85em; background-color: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #dee2e6;">${$('<div>').text(data).html()}</pre>`;
                    }

                    if (!fullHtml.includes('payload-full')) {
                        let safeFull = $('<div>').text(fullHtml).html();
                        fullHtml = `<pre class="mb-0 payload-full" style="display: none; max-height: 200px; overflow-y: auto; font-family: Menlo, Consolas, monospace; font-size: 0.85em; background-color: #212529; color: #a1b87a; padding: 10px; border-radius: 6px;">${safeFull}</pre>`;
                    }
                    return `<div class="payload-wrapper">
                        <div class="payload-summary" 
                            style="cursor: pointer; padding: 5px; background-color: #f8f9fa; border-radius: 6px; border: 1px dashed #dee2e6;" 
                            title="${labels.lblDoubleClicJson}">
                            ${summaryHtml}
                        </div>
                        ${fullHtml}
                    </div>`;
                }
            }, // 4
            {
                title: labels.tblDate,
                data: "created_at",
                className: "dt-center align-middle dt-nowrap",
                searchPanes: { show: false },
                render: function (data) {
                    if (!data) return '';

                    const fecha = flatpickr.formatDate(
                        new Date(data.replace(' ', 'T')),
                        'd-M-Y H:i:S'
                    );

                    return `<span class="strong">${fecha}</span>`;
                },
            } // 6
        ],
        searchPanes: {
            viewTotal: true,
            cascadePanes: true,
            layout: 'columns-3',
            initCollapsed: true
        },
        dom: '<"row align-items-center mb-3"<"#ctrlSyslongs.col-sm-12 col-md-4"><"col-sm-12 col-md-4"P><"col-sm-12 col-md-4"f>>rt<"row"<"col-6"i><"col-6"p>>',
        responsive: false,
        scrollX: false,
        scrollY: "calc(100vh - 390px)",
        paging: true,
        pageLength: 25,
        drawCallback: function () {
            let $container = $("#ctrlSyslongs");
            if (
                $("#ExportSyslogs").length === 0
            ) {
                let $row = $('<div class="row g-2 align-items-center"></div>');

                let $btnColExport = $('<div class="col-auto"></div>');
                let $btnExport = $(`
                        <button id="ExportSyslogs"
                                class="btn btn-sm btn-primary py-2"
                                title="${labels.btnExport}">
                                <i class="fas fa-file-export"></i>
                        </button>
                    `);
                $btnColExport.append($btnExport);

                let $btnColRefresh = $('<div class="col-auto"></div>');
                let $btnRefresh = $(`
                        <button id="RefreshSyslogs"
                                class="btn btn-sm btn-secondary py-2"
                                title="${labels.lblSync || 'Refrescar'}">
                                <i class="fas fa-sync-alt"></i>
                        </button>
                    `);
                $btnColRefresh.append($btnRefresh);

                $row.append($btnColExport);
                $row.append($btnColRefresh);

                $container.append($row);
                new bootstrap.Tooltip(document.getElementById('ExportSyslogs'));
                new bootstrap.Tooltip(document.getElementById('RefreshSyslogs'));
            }

            // Aplicar limpieza a SearchPanes (se ejecuta cada vez que se dibuja o refresca)
            const containerSP = document.querySelector('.dtsp-panes.dtsp-panesContainer');
            if (containerSP) {
                Array.from(containerSP.childNodes)
                    .filter(node => node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '')
                    .forEach(node => node.remove());
            }

            $('.dtsp-panesContainer .dtsp-searchPane').each(function (index) {
                const $pane = $(this);
                const paneId = parseInt($pane.attr('data-col'), 10);
                if (paneId) {
                    $pane.attr('data-column-index', paneId);
                } else {
                    $pane.attr('data-column-index', index);
                }
            });
            // Ocultar botones de count/name permanentemente de todo el documento si existen
            $(".dtsp-nameButton, .dtsp-countButton").remove();
        },
    });
    setInterval(function () {
        if (tableSysLogs) {
            tableSysLogs.ajax.reload(null, false);
        }
    }, 120000);

    // Manejador del botón de Refrescar
    $(document).on("click", "#RefreshSyslogs", function () {
        if (tableSysLogs) {
            let $btn = $(this);
            let $icon = $btn.find('i');
            $icon.addClass('fa-spin');
            $btn.prop("disabled", true);
            tableSysLogs.ajax.reload(function () {
                $icon.removeClass('fa-spin');
                $btn.prop("disabled", false);
                alertNotify({
                    type: "success",
                    text: labels.nteUpdateInformation,
                    icon: "fas fa-info-circle",
                    timeout: 3000
                });
            }, false);
        }
    });

    // Manejador del botón de Exportar a Excel
    $(document).on("click", "#ExportSyslogs", async function () {
        let exportData = tableSysLogs.rows({ search: 'applied' }).data().toArray();
        if (exportData.length === 0) {
            alertNotify({
                type: "info",
                text: labels.nteNotDataToExport,
                icon: "fas fa-info-circle",
                timeout: 3000
            });
            return;
        }

        // Opcional: mostrar un indicador de carga en el botón
        let $btn = $(this);
        let originHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> ' + labels.lblGenerate).prop("disabled", true);

        try {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet("SystemLogs");

            const dtColumns = tableSysLogs.settings().init().columns;
            const excelColumns = [];

            dtColumns.forEach(col => {
                let colWidth = 20;
                if (col.data === 'id') colWidth = 10;
                else if (col.data === 'payload') colWidth = 80;
                else if (col.data === 'url') colWidth = 50;
                else if (col.data === 'full_name') colWidth = 35;

                excelColumns.push({
                    header: col.title,   // Toma el título directo del DataTable
                    key: col.data,       // La misma llave de datos
                    width: colWidth
                });
            });
            worksheet.columns = excelColumns;

            // Diseñar cabecera
            worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
            worksheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F81BD' } };
            worksheet.getRow(1).alignment = { horizontal: 'center' };

            exportData.forEach(row => {
                let rowData = {};

                dtColumns.forEach(col => {
                    let key = col.data;
                    let val = row[key] || '';

                    if (key === 'full_name') {
                        let userStr = row.full_name ? row.full_name : (row.email ? 'N/A' : 'GUEST');
                        val = row.email ? `${userStr} (${row.email})` : userStr;
                    }
                    else if (key === 'method') {
                        val = row.method || '';
                        if (row.action_code) val += ` [${row.action_code}]`;
                    }
                    else if (key === 'created_at') {
                        if (row.created_at) {
                            try {
                                val = flatpickr.formatDate(new Date(row.created_at.replace(' ', 'T')), 'd-M-Y H:i:S');
                            } catch (e) { }
                        }
                    }
                    else if (key === 'payload') {
                        if (val && val !== 'null' && val !== '[]' && val !== '{}') {
                            try {
                                let parsed = JSON.parse(val);
                                val = JSON.stringify(parsed, null, 2);
                            } catch (e) { }
                        } else {
                            val = '';
                        }
                    }

                    rowData[key] = val;
                });

                worksheet.addRow(rowData);
            });

            if (worksheet.getColumn('url')) worksheet.getColumn('url').alignment = { wrapText: true, vertical: 'top' };
            if (worksheet.getColumn('payload')) worksheet.getColumn('payload').alignment = { wrapText: true, vertical: 'top' };
            if (worksheet.getColumn('full_name')) worksheet.getColumn('full_name').alignment = { vertical: 'top' };

            worksheet.views = [
                { state: 'frozen', xSplit: 0, ySplit: 1 }
            ];

            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);

            let fileNameDate = '';
            try { fileNameDate = flatpickr.formatDate(new Date(), 'Y-m-d_H-i-s'); } catch (e) { }
            link.download = `${labels.nteSystemLog}_${fileNameDate}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alertNotify({
                type: "success",
                text: labels.nteExportSuccess,
                icon: "fas fa-check-circle",
                timeout: 3000
            });

        } catch (error) {
            alertNotify({
                type: "error",
                text: labels.nteError,
                icon: "fas fa-exclamation-circle",
                timeout: 3000
            });
        } finally {
            $btn.html(originHtml).prop("disabled", false);
        }
    });

    // ========== Payload: doble clic expande, clic afuera colapsa ==========
    $('#tableSystemLogs tbody').on('dblclick', '.payload-summary', function () {
        let $summary = $(this);
        let $full = $summary.siblings('.payload-full');

        // Cerrar cualquier otro que esté abierto
        $('.payload-full:visible').not($full).each(function () {
            let $otherFull = $(this);
            $otherFull.slideUp("fast");
            $otherFull.siblings('.payload-summary').slideDown("fast");
        });

        // Toggle animado nativo
        $summary.slideUp("fast", function () {
            $full.slideDown("fast");
        });
    });

    $(document).on('click', function (e) {
        // Si haces click en la celda o afuera de ella, cerramos lo que esté abierto y restauramos resúmenes.
        // Solo para no interferir si se hace doble click justo después, revisamos que no sea la misma área exacta, o lo anidamos.
        if (!$(e.target).closest('.payload-wrapper').length) {
            $('.payload-full:visible').each(function () {
                let $full = $(this);
                $full.slideUp("fast", function () {
                    $full.siblings('.payload-summary').slideDown("fast");
                });
            });
        }
    });

    // ========== Tab 2: DataTable Log Types ==========
    var tableLogTypes = $("#tableLogTypes").DataTable({
        ajax: {
            url: CTRL_SYSLOGS,
            type: "POST",
            dataSrc: "data",
            data: { action: "R", part: "LT" },
        },
        language: { url: TABLELANG },
        columns: [
            {
                title: "ID",
                data: "id",
                className: "dt-center align-middle",
                width: "60px"
            },
            {
                title: labels.tblName,
                data: "name",
                className: "align-middle"
            },
            {
                title: labels.tblActions || "Actions",
                data: null,
                className: "dt-center align-middle",
                orderable: false,
                width: "80px",
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary editLogType" data-id="${row.id}" data-name="${row.name}" title="${labels.btnEdit || 'Editar'}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger deleteLogType" data-id="${row.id}" title="${labels.btnDelete || 'Eliminar'}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        dom: '<"row"<"#ctrlLogTypes.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 295px)",
        drawCallback: function () {
            var html =
                '<button id="createType" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
                (labels.btnNew || 'Nuevo') +
                "</button>";
            $("#ctrlLogTypes").html(html);
        },
    });

    // ========== Handlers for Log Types ==========

    // Open Modal to CREATE
    $(document).on("click", "#createType", function () {
        $('#modalLogType').attr('data-id', 0);
        $('#logTypeName').val('');
        $('#modalLogTypeLabel').text((labels.btnNew || 'Nuevo') + ' Log Type');
        $('#modalLogType').modal('show');
    });

    // Open Modal to EDIT
    $(document).on("click", ".editLogType", function () {
        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');
        $('#modalLogType').attr('data-id', id);
        $('#logTypeName').val(name);
        $('#modalLogTypeLabel').text((labels.btnEdit || 'Editar') + ' Log Type');
        $('#modalLogType').modal('show');
    });

    // Save (Create / Update)
    $(document).on("click", "#logTypeSave", function () {
        let id = $('#modalLogType').attr('data-id');
        let name = $('#logTypeName').val();

        if (name.trim() === '') {
            alertNotify({
                type: "warning",
                text: "Debe ingresar un nombre",
                icon: "fas fa-exclamation-triangle"
            });
            return;
        }

        let action = (id == 0) ? 'C' : 'U';
        let $btn = $(this);
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: CTRL_SYSLOGS,
            type: "POST",
            dataType: "json",
            data: {
                action: action,
                part: "LT",
                id: id,
                name: name
            },
            success: function (res) {
                if (res.result) {
                    alertNotify({
                        type: "success",
                        text: labels.nteCreateSuccess,
                        icon: "fas fa-check-circle"
                    });
                    $('#modalLogType').modal('hide');
                    tableLogTypes.ajax.reload(null, false);
                } else {
                    alertNotify({
                        type: "error",
                        text: res.message || (labels.nteError || 'Error'),
                        icon: "fas fa-times-circle"
                    });
                }
            },
            complete: function () {
                $btn.prop("disabled", false).html(labels.btnSave || "Guardar");
            }
        });
    });

    // Delete Log Type
    $(document).on("click", ".deleteLogType", function () {
        let id = $(this).attr('data-id');
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
                        $.ajax({
                            url: CTRL_SYSLOGS,
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: 'D',
                                part: 'LT',
                                id: id
                            },
                            success: function (res) {
                                if (res.result) {
                                    alertNotify({
                                        type: "success",
                                        text: labels.nteDeleteSuccess,
                                        icon: "fas fa-check-circle"
                                    });
                                    tableLogTypes.ajax.reload(null, false);
                                } else {
                                    alertNotify({
                                        type: "error",
                                        text: res.message || (labels.nteError || 'Error'),
                                        icon: "fas fa-times-circle"
                                    });
                                }
                            }
                        });
                    }
                },
                {
                    type: 'danger',
                    text: labels.btnNo || 'No',
                    icon: 'fas fa-times',
                },
            ]
        });
    });

    // ========== Tab 3: DataTable Log Type Tables ==========
    var tableLogTypeTables = $("#tableLogTypeTables").DataTable({
        ajax: {
            url: CTRL_SYSLOGS,
            type: "POST",
            dataSrc: "data",
            data: {
                action: "R",
                part: "LTT"
            },
        },
        language: {
            url: TABLELANG
        },
        columns: [
            {
                title: "ID",
                data: "logtype_id",
                className: "dt-center align-middle",
                width: "80px"
            },
            {
                title: "Log Type",
                data: "logtype_name",
                className: "align-middle"
            },
            {
                title: labels.tblTables + ' - ' + labels.lblDoubleAdd,
                data: "tables",
                className: "align-middle tables-cell",
                render: function (data, type, row) {
                    let logtypeId = row.logtype_id;
                    let badges = '';
                    if (data) {
                        badges = data.split(',').map(function (t) {
                            let tableName = t.trim();
                            return `<span class="badge bg-secondary me-1 mb-1" style="font-size: 0.85em;">
                                        ${tableName} 
                                        <i class="fas fa-times ms-1 text-light delete-table" style="cursor:pointer;" data-logtype="${logtypeId}" data-table="${tableName}" title="Eliminar"></i>
                                    </span>`;
                        }).join('');
                    } else {
                        badges = '<span class="text-muted fst-italic no-tables-msg">Sin tablas (Doble clic para añadir)</span>';
                    }
                    return `<div class="badges-container d-flex flex-wrap align-items-center">${badges}</div>`;
                }
            }
        ],
        dom: '<"row"<"#ctrlTypesTables.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 295px)",
        drawCallback: function () {
            var html =
                '<button id="createTableType" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
                (labels.btnNew || 'Nuevo') +
                "</button>";
            $("#ctrlTypesTables").html(html);
        },
    });

    // ========== Handlers for Log Type Tables ==========

    // Doble clic para añadir tabla
    tableLogTypeTables.on('dblclick', 'td.tables-cell', function (e) {
        if ($(e.target).hasClass('delete-table') || $(e.target).hasClass('add-table-input')) {
            return;
        }

        let $td = $(this);
        let rowData = tableLogTypeTables.row($td).data();
        if (!rowData) return;

        // Evitar que haya más de un input al mismo tiempo en la celda
        if ($td.find('.add-table-input').length > 0) return;

        // Ocultar mensaje 
        $td.find('.no-tables-msg').hide();

        let $container = $td.find('.badges-container');
        let $input = $(`<input type="text" class="form-control form-control-sm add-table-input mx-1 mb-1" placeholder="Nombre Tabla, Enter..." style="width:160px;">`);
        $input.attr('data-logtype', rowData.logtype_id);

        $container.append($input);
        $input.focus();
    });


    tableLogTypeTables.on('keypress blur', '.add-table-input', function (e) {
        if (e.type === 'keypress' && e.which !== 13) return;

        let $input = $(this);
        let tableName = $input.val().trim();
        let logtypeId = $input.attr('data-logtype');

        if (tableName === '') {
            $input.remove();
            return;
        }

        if ($input.prop('disabled')) return;
        $input.prop('disabled', true);

        $.ajax({
            url: CTRL_SYSLOGS,
            type: "POST",
            dataType: "json",
            data: {
                action: 'C',
                part: 'LTT',
                logtype_id: logtypeId,
                table: tableName
            },
            success: function (res) {
                if (res.result) {
                    alertNotify({
                        type: "success",
                        text: labels.nteInsertSuccess,
                        icon: "fas fa-check-circle"
                    });
                } else {
                    alertNotify({
                        type: "error",
                        text: labels.nteError || 'Error',
                        icon: "fas fa-times-circle"
                    });
                }
                tableLogTypeTables.ajax.reload(null, false);
            },
            error: function () {
                $input.prop('disabled', false);
            }
        });
    });
    tableLogTypeTables.on('click', '.delete-table', function (e) {
        e.stopPropagation();

        let logtypeId = $(this).attr('data-logtype');
        let tableName = $(this).attr('data-table');

        alertNotify({
            type: 'warning',
            text: labels.nteDeleteWarn || `¿Estás seguro de desvincular la tabla '${tableName}'?`,
            icon: 'fas fa-exclamation',
            buttons: [
                {
                    type: 'warning',
                    text: labels.btnYes || 'Sí',
                    icon: 'fas fa-check',
                    callback: function () {
                        $.ajax({
                            url: CTRL_SYSLOGS,
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: 'D',
                                part: 'LTT',
                                logtype_id: logtypeId,
                                table: tableName
                            },
                            success: function (res) {
                                if (res.result) {
                                    alertNotify({
                                        type: "success",
                                        text: labels.nteDeleteSuccess || "Tabla eliminada",
                                        icon: "fas fa-check-circle"
                                    });
                                    tableLogTypeTables.ajax.reload(null, false);
                                } else {
                                    alertNotify({
                                        type: "error",
                                        text: res.message || "Error al eliminar",
                                        icon: "fas fa-times-circle"
                                    });
                                }
                            }
                        });
                    }
                },
                {
                    type: 'danger',
                    text: labels.btnNo || 'No',
                    icon: 'fas fa-times',
                },
            ]
        });
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // ========== Modal Múltiples Tablas (LogTypeTables) ==========

    // Select2 para seleccionar LogType al añadir tablas
    $('#selectLogTypeToAdd').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalLogTypeTables'),
        placeholder: "Seleccione un Módulo (Log Type)",
        ajax: {
            url: CTRL_SYSLOGS,
            type: 'POST',
            dataType: 'json',
            data: function () {
                // Usamos la misma función LT que nos trae id y name de logtypes
                return {
                    action: 'R',
                    part: 'LT'
                };
            },
            processResults: function (response) {
                var items = response.data || [];
                return {
                    results: items.map(function (item) {
                        return { id: item.id, text: item.name };
                    })
                };
            },
            cache: true
        }
    });

    // Select2 con tags para ingresar múltiples tablas
    $('#inputMultipleTables').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalLogTypeTables'),
        tags: true,
        multiple: true,
        tokenSeparators: [',', ' '],
        placeholder: "Ej: users, user_accesses..."
    });

    // Abrir Modal de añadir varias tablas
    $(document).on("click", "#createTableType", function () {
        $('#selectLogTypeToAdd').val(null).trigger('change');
        $('#inputMultipleTables').val(null).trigger('change');
        $('#modalLogTypeTables').modal('show');
    });

    // Botón Guardar Múltiples Tablas
    $(document).on("click", "#logTypeTablesSave", function () {
        let logtypeId = $('#selectLogTypeToAdd').val();
        let tables = $('#inputMultipleTables').val() || [];

        if (!logtypeId) {
            alertNotify({
                type: "warning",
                text: "Debe seleccionar un Log Type",
                icon: "fas fa-exclamation-triangle"
            });
            return;
        }

        if (tables.length === 0) {
            alertNotify({
                type: "warning",
                text: "Debe escribir al menos una tabla",
                icon: "fas fa-exclamation-triangle"
            });
            return;
        }

        let $btn = $(this);
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: CTRL_SYSLOGS,
            type: "POST",
            dataType: "json",
            data: {
                action: 'C',
                part: 'LTT',
                logtype_id: logtypeId,
                table: tables // Se envía como array
            },
            success: function (res) {
                if (res.result) {
                    alertNotify({ type: "success", text: "Tablas añadidas exitosamente", icon: "fas fa-check-circle" });
                    $('#modalLogTypeTables').modal('hide');
                    tableLogTypeTables.ajax.reload(null, false);
                    // Opcionalmente recargar logtypes si lo necesitas: tableLogTypes.ajax.reload(null, false);
                } else {
                    alertNotify({ type: "error", text: res.message || "Error al guardar", icon: "fas fa-times-circle" });
                }
            },
            complete: function () {
                $btn.prop("disabled", false).html(labels.btnSave || "Guardar");
            }
        });
    });

});
