var CTRLREGISTRATIONS = "/" + chrLocale + "/controller/registrations";

var columns = [];
var tableRegistrations = '';
var DateTime = luxon.DateTime;
const currencyFormatterCRC = new Intl.NumberFormat('es-CR', {
    style: 'currency',
    currency: 'CRC',
    minimumFractionDigits: 2
});

$(document).ready(function () {
    tableRegistrations = $("#tableRegistrations").DataTable({
        ajax: {
            url: CTRLREGISTRATIONS,
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
                title: "Nombre",
                data: "name",
                className: "dt-left dt-nowrap",
            },
            {
                title: "Apellido 1",
                data: "last_name",
                className: "dt-left dt-nowrap no-filter"
            },
            {
                title: "Apellido 2",
                data: "second_last_name",
                className: "dt-left dt-nowrap no-filter"
            },
            {
                title: "Cédula",
                data: "idcard",
                className: "dt-left dt-nowrap no-filter"
            },
            {
                title: "Edad",
                data: "age",
                className: "dt-left dt-nowrap no-filter"
            },
            {
                title: "Email",
                data: "email",
                className: "dt-left dt-nowrap no-filter"
            },
            {
                title: "Teléfono",
                data: "phone",
                className: "dt-left dt-nowrap no-filter",
                render: function (data, type, row) {
                    if (!data) return "";
                    const numeroLimpio = data.toString().replace(/\D+/g, "");
                    const numeroCR = numeroLimpio.startsWith("506")
                        ? numeroLimpio
                        : `506${numeroLimpio}`;
                    const url = `https://web.whatsapp.com/send?phone=${numeroCR}`;
                    return `
                    <a href="${url}" target="_blank" class="text-success fw-bold">
                        ${data}
                    </a>
                `;
                }
            },
            {
                title: "Pagó",
                data: "ispay",
                width: '120px',
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    const options = [
                        { value: 1, text: "Pagado" },
                        { value: 0, text: "No pagado" }
                    ];

                    const optionsHTML = options.map(opt => {
                        const selected = opt.value == data ? "selected" : "";
                        return `<option value="${opt.value}" ${selected}>${opt.text}</option>`;
                    }).join("");

                    return `<select class="selectIspay form-control form-control-sm" data-id="${row.participant_id}">
                    ${optionsHTML}
                </select>`;
                },
            },
            {
                title: "Talla",
                data: "talla",
                className: "dt-center dt-nowrap"
            },
            {
                title: "Género",
                data: "genero",
                className: "dt-center dt-nowrap"
            },
            {
                title: "Evento",
                data: "event_name",
                className: "dt-left dt-nowrap no-filter",
                render: function (data, type, row) {
                    if (type === 'display' && data) {
                        return data.length > 9 ? data.substring(0, 9) + "..." : data;
                    }
                    return data;
                }
            },
            {
                title: "Ruta",
                data: "route_name",
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (type === 'display' && data) {
                        return data.length > 9 ? data.substring(0, 9) + "..." : data;
                    }
                    return data;
                }
            },
            {
                title: "Dist.(Km)",
                data: "route_distance_km",
                className: "dt-left dt-nowrap no-filter"
            },
            {
                title: "Precio", data: "route_cost", className: "dt-left dt-nowrap",
                render: function (data) {
                    return data ? '₡' + parseFloat(data).toLocaleString('es-CR', { minimumFractionDigits: 2 }) : '';
                }
            },
            /*{
                title: labels.tblActions,
                orderable: false,
                searchable: false,
                data: "participant_id",
                className: "dt-right no-filter",
                render: function (data, type, row) {
                    return cellRender({
                        type: "dropdown",
                        data,
                        text: labels.tblActions,
                        dataTags: { id: data },
                        listItems: [
                            {
                                id: "eventEdit_" + data,
                                text: labels.btnEdit,
                                icon: "far fa-edit",
                                dataTags: { id: data },
                            },
                            {
                                id: "eventUploadImg_" + data,
                                text: "Subir Imagen",
                                icon: "fas fa-image",
                                dataTags: { uuid: data },
                            }
                        ],
                    });
                }
            }*/
        ],
        dom:
            '<"row mb-1"<"#ctrleventsCustom.col-6 gap-2 d-flex"><"col-6"f>>' +
            '<"row"<"col-12"P>>' +
            't' +
            'i',
        reponsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 395px)",
        searchPanes: {
            initCollapsed: true,
            // Activar cascadePanes para que los contadores de cada pane
            // respeten las selecciones de los demás (por ejemplo, tallas
            // mostrará cantidades filtradas por género cuando se seleccione F).
            cascadePanes: true,
            layout: 'columns-3',
            panes: [
                {
                    header: 'Pagó',
                    options: [
                        { value: 1, text: "Pagado" },
                        { value: 0, text: "No pagado" }
                    ].map(opt => ({
                        label: opt.text,
                        value: function (rowData) {
                            return rowData.ispay == opt.value;
                        }
                    }))
                }
            ]
        },
        initComplete: function () {
            const container = document.querySelector('.dtsp-panes.dtsp-panesContainer');
            if (container) {
                Array.from(container.childNodes)
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
                $(".dtsp-nameButton, .dtsp-countButton").remove();
            });
            $('#tableRegistrations').on('init.dt', function () {
                $('.dtsp-searchPane').each(function (index) {
                    if (![ 5, 6, 7, 8, 9, 10, 14].includes(index)) {
                        $(this).hide();
                    }

                });
            });
        /*(function () {
                var rebuildTimeout = null;
                var genderColIndex = 9; // Género
                var sizeColIndex = 8; // Talla

                $(document).on('click', '.dtsp-searchPane[data-column-index="' + genderColIndex + '"] .dtsp-paneOption, '
                    + '.dtsp-searchPane[data-column-index="' + genderColIndex + '"] .dtsp-paneOption input', function () {
                    clearTimeout(rebuildTimeout);
                    rebuildTimeout = setTimeout(function () {
                        try {
                            tableRegistrations.searchPanes.rebuildPane(sizeColIndex);
                        } catch (e) {
                            // Fallback: full rebuild if specific pane rebuild fails
                            try { tableRegistrations.searchPanes.rebuild(); } catch (er) { }
                        }
                    }, 150);
                });
            })(); */
        },
        drawCallback: function () {
            const $container = $("#ctrleventsCustom");
            const $rowDiv = $('<div class="row gx-2 gy-1"></div>');
            createFilterMenu($rowDiv, tableRegistrations,
                [5,6,7,8,9,10, 14], // índices activos
                "toggleFilterMenuFilter",
                { lblFilter: labels.lblFilter },
                [
                    { id: 14, header: 'Pago' }
                                ]
            );

            if (!$container.find(".btn-export-excel").length) {
                const $btnExport = $(`
                                    <div class="col-auto">
                                        <button id="ExportTransactions"
                                            class="btn btn-sm btn-primary fas fa-file-export py-2 btn-export-excel"
                                            title="${labels.btnExport}">
                                        </button>
                                    </div>
                                `);
                $rowDiv.append($btnExport);
                new bootstrap.Tooltip($btnExport.find("button")[0]);
            }
            $container.append($rowDiv);
            $('.dtsp-panesContainer .dtsp-searchPane').each(function (index) {
                $(this).attr('data-column-index', index);
                $(".dtsp-nameButton, .dtsp-countButton").remove();
            });
            
        },
        rowCallback: function (row, data) {
            if (parseInt(data.ispay) === 1) {
                // Pagado → azul suave
                $(row).find("td").css({
                    backgroundColor: "rgba(228, 239, 255, 0.6)",
                    color: "rgba(13, 71, 161, 1)",
                    fontWeight: "bold"
                });
            } else if (parseInt(data.ispay) === 0) {
                // No pagado → rojo suave
                $(row).find("td").css({
                    backgroundColor: "rgba(252, 216, 216, 0.76)",
                    color: "#bf360c",
                    fontWeight: "bold"
                });
            }
        },

    });
    tableRegistrations.on('draw', function () {
        $('#tableRegistrations .selectIspay').select2({
            theme: "bootstrap-5",
            selectionCssClass: "select2--small",
            dropdownCssClass: "select2--small",
        });
    });
})

$(document).on("change", ".filter-pane-toggle", function () {
    const colIndex = $(this).data("col");
    const isChecked = $(this).is(":checked");

    const pane = $(`.dtsp-searchPane[data-column-index="${colIndex}"]`);

    if (isChecked) {
        pane.show();
        tableRegistrations.searchPanes.rebuildPane(colIndex);
    } else {
        pane.hide();
    }
});

$(document).on("click", "#ExportTransactions", function (e) {
    e.preventDefault();
    const table = $('#tableRegistrations').DataTable();

    const rawData = table.rows({ search: 'applied' }).data().toArray();
    const headers = table.columns(':visible').header().toArray().map(th => th.innerText.trim());

    const finalData = [headers];

    table.rows({ search: 'applied' }).every(function () {
        const rowNode = $(this.node());
        const cols = rowNode.find('td').toArray();
        const rowData = formatValuesExport(cols);
        finalData.push(rowData);
    });

    const ws = formatExcelDates(finalData);
    const wb = XLSX.utils.book_new();

    const now = new Date();
    const costaRicaTime = new Date(now.toLocaleString('en-US', { timeZone: 'America/Costa_Rica' }));

    const datePart = costaRicaTime.toISOString().split('T')[0];
    const timePart = costaRicaTime.toTimeString().split(' ')[0].replace(/:/g, '-');

    const fileName = `Discovery_${datePart}_${timePart}.xlsx`;

    XLSX.utils.book_append_sheet(wb, ws, "Registrationst");
    XLSX.writeFile(wb, fileName);
});


// Evento para cuando cambia el select "Pagó"
$(document).on('change', '.selectIspay', function (e) {
    e.preventDefault();
    var $select = $(this);
    var participant = $select.data('id');
    var newValue = parseInt($select.val());

    var postData = {
        action: 'U',
        part: 'E',
        participant: participant,
        value: newValue
    };
    $.ajax({
        type: "POST",
        url: CTRLREGISTRATIONS,
        data: postData,
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: 'success',
                    text: 'Estado actualizado correctamente',
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                // Antes de recargar
                let paneVisibility = getPaneVisibility(tableRegistrations);

                tableRegistrations.ajax.reload(null, false);

                tableRegistrations.one('draw.dt', function () {
                    restorePaneVisibility(tableRegistrations, paneVisibility);
                });


            } else {
                const errorMsg = Array.isArray(response.error) && response.error.length > 0
                    ? response.error[0].message || response.error[0][2]
                    : 'Error al actualizar';
                alertNotify({
                    type: 'warning',
                    text: errorMsg,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 5000,
                });
            }
        }
        
    });
});
