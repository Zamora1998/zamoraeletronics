var CTRLSTARTEVENT = "/" + chrLocale + "/controller/startevent";
let isMassSelected = 0;
var selectedCount = 0;
let tableEvents;
let scannerTimes = {};

$(document).ready(function () {

    /* ===============================
     *  CSS indicadores
     * =============================== */
    $("head").append(`
        <style>
            .dot {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin-right: 5px;
            }
            .dot-red {
                background-color: red;
                animation: blink 1s infinite;
            }
            .dot-green {
                background-color: #28a745;
                animation: blink 1s infinite;
            }
            .dot-gray {
                background-color: #ccc;
            }
            @keyframes blink {
                0%, 50%, 100% { opacity: 1; }
                25%, 75% { opacity: 0; }
            }
        </style>
    `);

    // Iniciar carga de datos y refrescar cada 15 segundos
    loadTableData();
    setInterval(loadTableData, 15000);

});

function loadTableData() {
    $.ajax({
        url: CTRLSTARTEVENT,
        type: "POST",
        dataType: "json",
        data: { action: "R", part: "A" },
        success: function (response) {

            var participants = response.registrations.data || [];
            var posts = response.posts || [];
            var scans = response.scans.data || [];

            /* ===============================
             *  Mapa de escaneos (ahora con arrays)
             * =============================== */
            const startPost = posts.find(post => post.mark === 1);
            const startPostUUID = startPost ? startPost.post_uuid : null;

            scannerTimes = {};
            scans.forEach(scan => {
                if (!scannerTimes[scan.idcard]) {
                    scannerTimes[scan.idcard] = {};
                }
                if (!scannerTimes[scan.idcard][scan.post_uuid]) {
                    scannerTimes[scan.idcard][scan.post_uuid] = [];
                }
                scannerTimes[scan.idcard][scan.post_uuid].push(scan.scanned_at);
            });

            // Si la tabla ya existe, actualizamos datos sin parpadeo
            if ($.fn.DataTable.isDataTable('#tableStartEvent')) {

                // Guardar selección actual
                let selectedIds = [];
                $('#tableStartEvent tbody tr.selected').each(function () {
                    let id = $(this).attr('data-idcard');
                    if (id) selectedIds.push(id);
                });

                // Actualizar datos
                tableEvents.clear();
                tableEvents.rows.add(participants);
                tableEvents.draw(false);

                // Restaurar selección
                if (selectedIds.length > 0) {
                    $('#tableStartEvent tbody tr').each(function () {
                        let rowId = $(this).attr('data-idcard');
                        if (selectedIds.includes(rowId)) {
                            $(this).addClass('selected');
                            $(this).find('input[type="checkbox"]').prop('checked', true);
                        }
                    });
                }

                return;
            }

            /* ===============================
             *  Inicialización (solo primera vez)
             * =============================== */
            let columns = [
                {
                    title: "Nombre completo",
                    data: null,
                    className: "dt-left dt-nowrap",
                    render: function (data, type, row) {
                        return `${row.name} ${row.last_name} ${row.second_last_name}`;
                    }
                },
                {
                    title: "Ruta",
                    data: "route_distance_km",
                    className: "dt-center",
                    render: function (data) {
                        return `${data} km`;
                    }
                }
            ];

            posts.forEach(post => {
                columns.push({
                    title: post.name,
                    data: null,
                    className: "dt-center",
                    createdCell: function (td, cellData, rowData) {
                        $(td)
                            .attr("data-post-uuid", post.post_uuid)
                            .attr("data-idcard", rowData.idcard);
                    },
                    render: function (data, type, row) {
                        const idcard = row.idcard;
                        const uuid = post.post_uuid;
                        const routeKm = row.route_distance_km;
                        const leToca = post.distances.includes(routeKm);
                        const times = scannerTimes[idcard]?.[uuid];

                        if (times && times.length > 0) {
                            // Mostrar múltiples horas separadas por "/"
                            const horas = times.map(t => t.split(' ')[1]).join(' / ');
                            return `<span class="dot dot-green"></span>${horas}`;
                        }
                        if (leToca) {
                            return `<span class="dot dot-red"></span>--:--:--`;
                        }
                        return `<span class="dot dot-gray"></span>--:--:--`;
                    }
                });
            });

            tableEvents = $("#tableStartEvent").DataTable({
                data: participants,
                columns: columns,
                language: { url: TABLELANG },
                dom:
                    '<"row m-0 py-0 align-items-center"' +
                    '<"col-9 d-flex gap-2 align-items-center m-0 p-0"' +
                    '<"#ctrleventsCustom" class="m-0 p-0">' +
                    'P' +
                    '>' +
                    '<"col-3 text-end m-0 p-0"f>' +
                    '>' +
                    't' +
                    'i',
                responsive: true,
                fixedHeader: true,
                paging: false,
                scrollX: false,
                scrollY: "calc(100vh - 365px)",
                destroy: true,
                searchPanes: {
                    initCollapsed: true,
                    cascadePanes: true,
                },
                createdRow: function (row, data) {
                    $(row).attr("data-idcard", data.idcard);
                },
                initComplete: function () {
                    var multipleSelectButton = $('<button class="btn btn-primary" id="multipleSelectBtn">Seleccionar Múltiples</button>');
                    $('#ctrleventsCustom').append(multipleSelectButton);

                    var clockButton = $('<button class="btn btn-secondary" id="clockBtn" disabled><i class="fa fa-clock"></i></button>');
                    if (startPostUUID) {
                        clockButton.attr('data-post-uuid', startPostUUID);
                    }
                    $('#ctrleventsCustom').append(clockButton);

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
                    $('#tableStartEvent').on('init.dt', function () {
                        $('.dtsp-searchPane').each(function (index) {
                            if (![1].includes(index)) {
                                $(this).hide();
                            }
                        });
                    });
                },
            });
        },
        error: function (xhr, status, error) {
            console.error("Error AJAX:", error);
        }
    });
}

$(document).on('click', '#multipleSelectBtn', function () {
    const allVisibleRows = $('#tableStartEvent tbody tr:visible');
    const $btnCloseMassive = $('#clockBtn');

    if (isMassSelected === 0) {
        allVisibleRows.addClass('selected');
        allVisibleRows.find('input[type="checkbox"]').prop('checked', true);
        isMassSelected = 1;
    } else {
        $('#tableStartEvent tbody tr').removeClass('selected');
        $('#tableStartEvent tbody tr').find('input[type="checkbox"]').prop('checked', false);
        isMassSelected = 0;
    }
    const selectedCount = $('#tableStartEvent tbody tr.selected').length;
    $btnCloseMassive.prop('disabled', selectedCount <= 1);
});

$(document).on('click', '#clockBtn', function () {
    const selectedRows = $('#tableStartEvent tbody tr.selected');

    if (selectedRows.length === 0) {
        alertNotify({
            type: 'warning',
            text: labels.nteStartTimeRunners,
            icon: 'fas fa-exclamation-triangle',
            timeout: 3000,
        });
        return;
    }
    alertNotify({
        type: 'warning',
        text: labels.nteStartTimeRunners,
        icon: 'fas fa-exclamation-triangle',
        buttons: [
            {
                type: 'warning',
                text: 'Sí, cerrar',
                icon: 'fas fa-check',
                callback: 'confirmCloseMassive()'
            },
            {
                type: 'danger',
                text: 'No, cancelar',
                icon: 'fas fa-times',
            },
        ]
    });
});


function confirmCloseMassive() {

    const selectedRows = $('#tableStartEvent tbody tr.selected');
    const postUUID = $('#clockBtn').data('post-uuid');

    if (!postUUID) {
        alert('No hay puesto seleccionado');
        return;
    }

    if (!selectedRows.length) {
        alert('No hay participantes seleccionados');
        return;
    }

    const action = 'U';
    const part = 'E';
    const selectedData = [];

    selectedRows.each(function () {

        const idcard = $(this).data('idcard');

        if (!idcard) return;

        selectedData.push({
            idcard: idcard,
            post_uuid: postUUID
        });
    });

    const jsonData = JSON.stringify(selectedData);

    $.ajax({
        url: CTRLSTARTEVENT,
        type: 'POST',
        dataType: 'json',
        data: {
            action: action,
            part: part,
            data: jsonData
        },
        success: function () {
            alertNotify({
                type: 'success',
                text: labels.nteCreateSuccess,
                icon: 'fas fa-check',
                timeout: 3000,
            });
        },
        error: function () {
            alertNotify({
                type: 'warning',
                text: labels.nteCreateError,
                icon: 'fas fa-exclamation-triangle',
                timeout: 3000,
            });
        }
    });
    tableEvents.ajax.reload(null, false);
}
