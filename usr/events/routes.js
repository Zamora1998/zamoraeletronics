var CTRL_ROUTES = "/" + chrLocale + "/controller/Events";

let elevationChart = null;
let mapHoverMarker = null;
let routeMarkersAuto = [];
let routeMarkersManual = [];
let routePolylineAuto;
let routePolylineManual;
let routeSegments = [];
let currentMode = "auto";
$(document).ready(function () {
    tableeventRoutes = $("#tableeventRoutes").DataTable({
        ajax: {
            url: CTRL_ROUTES,
            type: "POST",
            dataSrc: "data",
            data: {
                action: "R",
                part: "R",
            },
        },
        language: {
            url: TABLELANG,
        },
        columns: [
            {
                title: "Evento",
                data: "event_name",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return '';
                    var truncated = data.length > 20 ? data.substring(0, 20) + "..." : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Ruta",
                data: "route_name",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return '';
                    var truncated = data.length > 20 ? data.substring(0, 20) + "..." : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Descripción Ruta",
                data: "route_description",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return '';
                    var truncated = data.length > 30 ? data.substring(0, 30) + "..." : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Distancia Ruta (Km)",
                data: "distance_km",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Precio",
                data: "cost",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (data === null || data === undefined || data === "") return "";
                    return `₡ ${Number(data).toLocaleString("es-CR", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
            },
            {
                title: "Coordenadas",
                data: "coordinates",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    return data ? '<span title="' + data + '">' + data.substring(0, 25) + "..." : '';
                }
            },
            {
                title: "Fecha Creación Ruta",
                data: "route_created_at",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return "";
                    return flatpickr.formatDate(new Date(data), "d-M-Y H:i");
                }
            },
            {
                title: labels.tblActions,
                orderable: false,
                searchable: false,
                data: "route_id", // usamos el ID de la ruta
                className: "dt-right",
                render: function (data, type, row) {
                    return cellRender({
                        type: "dropdown",
                        data,
                        text: labels.tblActions,
                        dataTags: {
                            id: data,
                        },
                        listItems: [
                            {
                                id: "routeEdit_" + data,
                                text: labels.btnEdit,
                                icon: "far fa-edit",
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: "routeDelete_" + data,
                                text: labels.btnDelete,
                                icon: "far fa-trash-alt",
                                dataTags: {
                                    id: data,
                                },
                            }
                        ],
                    });
                },
            },
        ],
        dom: '<"row"<"#ctrlroutesCustom.col-6"><"col-6"f>>ti',
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        drawCallback: function () {
            var html =
                '<button id="routeCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
                labels.btnNew +
                "</button>";
            $("#ctrlroutesCustom").html(html);
        }
    });
});

function initRouteMap() {
    mapRoute = L.map('mapRoute').setView([9.934739, -84.087502], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OSM</a> contributors'
    }).addTo(mapRoute);

    startNewSegment(currentMode);

    mapRoute.on('click', function (e) {
        let latlng = e.latlng;
        let marker = L.marker(latlng).addTo(mapRoute);

        if (currentMode === "auto") {
            routeMarkersAuto.push(marker);
        } else {
            routeMarkersManual.push(marker);
        }

        currentSegment.points.push(latlng);

        if (currentMode === "auto" && currentSegment.points.length > 1) {
            let coords = currentSegment.points.map(p => `${p.lng},${p.lat}`).join(';');
            let url = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.routes && data.routes.length > 0) {
                        let route = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                        currentSegment.polyline.setLatLngs(route);
                        updateRouteCoords();
                    } else {
                        currentSegment.polyline.setLatLngs(currentSegment.points);
                        updateRouteCoords();
                    }
                });
        } else {
            currentSegment.polyline.setLatLngs(currentSegment.points);
            updateRouteCoords();
        }
    });

    $("#manualRouteMode").on("change", function () {
        currentMode = $(this).is(":checked") ? "manual" : "auto";
        startNewSegment(currentMode);
    });
}

function startNewSegment(mode) {
    let color = mode === "auto" ? "blue" : "orange";
    let dash = mode === "manual" ? "10,10" : null;
    let polyline = L.polyline([], { color, weight: 5, dashArray: dash }).addTo(mapRoute);

    currentSegment = { mode, points: [], polyline };
    routeSegments.push(currentSegment);
}

function exportToKMLFromInput() {
    const raw = $("#routeCoords").val();
    let coords;

    try {
        coords = JSON.parse(raw);
    } catch (e) {
        console.error("Error parsing routeCoords:", e);
        alert("Formato inválido en los datos de ruta.");
        return;
    }

    let kml = `<?xml version="1.0" encoding="UTF-8"?>
    <kml xmlns="http://www.opengis.net/kml/2.2">
    <Document><name>Ruta exportada</name><Placemark><LineString><coordinates>`;

    coords.forEach(([lat, lng]) => {
        kml += `${lng},${lat},0\n`; // KML usa orden lng,lat
    });

    kml += `</coordinates></LineString></Placemark></Document></kml>`;

    const blob = new Blob([kml], { type: "application/vnd.google-earth.kml+xml" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "ruta.kml";
    a.click();
}

async function updateRouteCoords() {
    let allCoords = [];
    let allLatLngs = [];

    routeSegments.forEach(seg => {
        let latlngs = seg.polyline.getLatLngs();
        latlngs.forEach(p => {
            let lat = p.lat;
            let lng = p.lng;
            allCoords.push([lat.toFixed(6), lng.toFixed(6)]);
            allLatLngs.push(L.latLng(lat, lng));
        });
    });

    $("#routeCoords").val(JSON.stringify(allCoords));

    let totalDistance = 0;
    for (let i = 1; i < allLatLngs.length; i++) {
        totalDistance += allLatLngs[i - 1].distanceTo(allLatLngs[i]);
    }
    $("#routeDistanceLabel #routeDistanceValue").text((totalDistance / 1000).toFixed(2));

    if (allLatLngs.length > 1) {
        drawElevationChartFromLatLngs(allLatLngs);
    }
}

$('#modalRoute').on('shown.bs.modal', function () {
    if (!mapRoute) {
        initRouteMap();
    } else {
        mapRoute.invalidateSize();
    }
    // Elimina todos los segmentos y polylines
    if (routeSegments.length) {
        routeSegments.forEach(seg => seg.polyline.remove());
    }
    routeSegments = [];
    startNewSegment(currentMode);
});

async function fetchElevationData(latlngs) {
    const chunkSize = 100;
    const allElevations = [];

    for (let i = 0; i < latlngs.length; i += chunkSize) {
        const chunk = latlngs.slice(i, i + chunkSize);
        const locations = chunk.map(p => `${p.lat},${p.lng}`).join("|");
        const url = `${CTRL_ELEVATION}?locations=${encodeURIComponent(locations)}`;

        try {
            const response = await fetch(url);
            const data = await response.json();
            const elevations = data.results.map(r => r.elevation);
            allElevations.push(...elevations);
            await delay(300);
        } catch (error) {
            console.error("Error fetching elevation chunk:", error);
        }
    }

    return allElevations;
}

// 2. Dibujar el gráfico con los datos obtenidos
async function drawElevationChartFromLatLngs(latlngs) {
    const elevations = await fetchElevationData(latlngs);

    const labels = elevations.map((_, i) => i);
    const data = {
        labels: labels,
        datasets: [{
            label: "Altitud (m)",
            data: elevations,
            fill: true,
            backgroundColor: "rgba(75,192,192,0.2)",
            borderColor: "rgba(75,192,192,1)",
            pointRadius: 0,
            tension: 0.1
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const idx = context.dataIndex;
                            const latlng = latlngs[idx];
                            return `Altitud: ${context.raw} m - Posición: ${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)}`;
                        }
                    }
                }
            },
            onHover: (event, elements) => {
                if (elements.length > 0) {
                    const idx = elements[0].index;
                    const latlng = latlngs[idx];

                    if (!mapHoverMarker) {
                        mapHoverMarker = L.circleMarker(latlng, {
                            radius: 8,
                            color: 'red'
                        }).addTo(mapRoute);
                    } else {
                        mapHoverMarker.setLatLng(latlng);
                    }
                } else {
                    if (mapHoverMarker) {
                        mapRoute.removeLayer(mapHoverMarker);
                        mapHoverMarker = null;
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: false
                    },
                    ticks: {
                        font: { size: 10 }
                    }
                },
                x: {
                    display: false
                }
            }
        }
    };

    if (elevationChart) {
        elevationChart.destroy();
    }

    const ctx = document.getElementById('elevationChart').getContext('2d');
    elevationChart = new Chart(ctx, config);
}

function validateFormFields($container) {
    let hasError = false;

    $container.find("input:not([type=checkbox]), select, textarea").each(function () {
        const $el = $(this);

        // Excluir el campo de subida de archivos por ID
        if ($el.attr("id") === "gpxUpload") {
            return; // Salta este campo
        }

        const value = $el.val();
        const isEmpty = value === "" || value === null || value === undefined;

        const isFlatpickr = $el[0] && $el[0]._flatpickr && $el[0]._flatpickr.altInput;
        const $target = isFlatpickr ? $($el[0]._flatpickr.altInput) : $el;

        $target.toggleClass("is-invalid", isEmpty);

        if (isEmpty) {
            hasError = true;
        }
    });

    return !hasError;
}

$('#saveRouteT').click(function (e) {
    e.preventDefault();

    const $form = $("#modalRoute");
    const data = $form.find("input:not([type=checkbox]), select, textarea").serializeObject();

    if (!validateFormFields($form)) {
        return false;
    }

    data.action = 'C';
    data.part = 'R';
    console.log(data);

    $.ajax({
        type: "POST",
        url: CTRL_ROUTES,
        data,
        dataType: "json",
        success: function (response) {
            const notifyType = response.result ? "success" : "warning";
            const notifyText = response.result ? labels.nteUpdateSuccess : labels.nteError;
            const notifyIcon = response.result ? "fas fa-check" : "fas fa-times";

            alertNotify({
                type: notifyType,
                text: notifyText,
                icon: notifyIcon,
                timeout: 3000,
            });

            if (response.result) {
                $('#modalRoute').modal('hide');
            }

            tableeventRoutes.ajax.reload();
        }
    });
});


$(document).ready(function () {
    $("#location").on("input", function () {
        var query = $(this).val();
        var $suggestions = $("#suggestions");
        if (query.length < 4) {
            $suggestions.hide();
            return;
        }
        $.ajax({
            url: "https://photon.komoot.io/api/",
            data: { q: query, limit: 5 },
            dataType: "json",
            success: function (response) {
                $suggestions.empty();

                if (!response.features || response.features.length === 0) {
                    $suggestions.hide();
                    return;
                }

                response.features.forEach(function (place) {
                    var name = place.properties.name || "";
                    var city = place.properties.city || "";
                    var country = place.properties.country || "";
                    var display = [name, city, country].filter(Boolean).join(", ");

                    var $li = $("<li>")
                        .addClass("list-group-item list-group-item-action")
                        .text(display)
                        .on("click", function () {
                            $("#location").val(display);
                            $suggestions.hide();

                            document.getElementById("latitude").value = place.geometry.coordinates[1];
                            document.getElementById("longitude").value = place.geometry.coordinates[0];
                        });
                    $suggestions.append($li);
                });
                $suggestions.show();
            },
            error: function () {
                $suggestions.hide();
            }
        });
    });
});

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

$(document).ready(function () {
    $('#eventos').select2({
        theme: "bootstrap-5",
        selectionCssClass: "select2--small",
        dropdownCssClass: "select2--small",
        dropdownParent: $('#modalRoute')
    });
});

$("#routeDistance").on("input", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
});

$("#Price").on("input", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
});


$('#modalRoute').on('hidden.bs.modal', function () {
    // Limpiar campos del formulario
    $(this).find('input, select, textarea').each(function () {
        $(this).val('').removeClass('is-invalid').trigger('change');
    });

    $(this).find('select').each(function () {
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).val(null).trigger('change');
        }
    });

    // Eliminar todos los polylines del mapa
    if (routeSegments.length) {
        routeSegments.forEach(seg => {
            if (seg.polyline) {
                seg.polyline.remove();
            }
        });
    }

    // Eliminar marcadores automáticos
    if (routeMarkersAuto.length) {
        routeMarkersAuto.forEach(marker => mapRoute.removeLayer(marker));
        routeMarkersAuto = [];
    }

    // Eliminar marcadores manuales
    if (routeMarkersManual.length) {
        routeMarkersManual.forEach(marker => mapRoute.removeLayer(marker));
        routeMarkersManual = [];
    }

    // Eliminar marcador flotante del gráfico
    if (mapHoverMarker) {
        mapRoute.removeLayer(mapHoverMarker);
        mapHoverMarker = null;
    }

    // Limpiar gráfico de elevación
    if (elevationChart) {
        elevationChart.destroy();
        elevationChart = null;

        const canvas = document.getElementById('elevationChart');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    // Resetear arrays
    routeSegments = [];
    allCoords = [];
    allLatLngs = [];

    // Resetear campos visibles
    $("#routeCoords").val("");
    $("#routeName").val("");
    $("#routeDistance").val("");

    // Recentrar el mapa en la ubicación por defecto
    if (mapRoute) {
        mapRoute.setView([9.934739, -84.087502], 13);
    }
    $(this).attr('data-id', 0);
    // Iniciar nuevo segmento limpio
    startNewSegment(currentMode);
});

$(document).on('click', '[id^=routeDelete_]', function (e) {
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
                    deleteBasetype(id);
                }
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ]
    });
});

$(document).on('click', '#routeCreate', function (e) {
    e.preventDefault();
    $('#modalRoute').attr('data-id', 0).modal('show');
});

function deleteroute(id) {
    $.ajax({
        type: "POST",
        url: CTRL_ROUTES,
        data: {
            action: 'D',
            part: 'R',
            id: id
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
                    text: labels.nteDeleteError,
                    icon: 'fas fa-times',
                    timeout: 3000,
                });
            }
            tableeventRoutes.ajax.reload();
        }
    });
}

$("#btnUploadGpx").on("click", function () {
    const fileInput = document.getElementById("gpxUpload");
    if (!fileInput.files.length) {
        alert("Selecciona un archivo GPX.");
        return;
    }
    const file = fileInput.files[0];
    const reader = new FileReader();
    reader.onload = function (e) {
        const gpxText = e.target.result;
        try {
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(gpxText, "application/xml");
            const trkpts = xmlDoc.getElementsByTagName("trkpt");
            if (!trkpts.length) {
                alert("No se encontraron puntos de ruta en el GPX.");
                return;
            }
            // Limpiar segmentos y marcadores previos
            if (routeSegments.length) {
                routeSegments.forEach(seg => seg.polyline.remove());
            }
            routeSegments = [];
            routeMarkersAuto.forEach(marker => mapRoute.removeLayer(marker));
            routeMarkersAuto = [];
            routeMarkersManual.forEach(marker => mapRoute.removeLayer(marker));
            routeMarkersManual = [];

            let coords = [];
            let latlngs = [];
            for (let i = 0; i < trkpts.length; i++) {
                const lat = trkpts[i].getAttribute("lat");
                const lng = trkpts[i].getAttribute("lon");
                coords.push([lat.toString(), lng.toString()]); // <-- string formato
                latlngs.push(L.latLng(parseFloat(lat), parseFloat(lng)));
            }
            $("#routeCoords").val(JSON.stringify(coords));

            // Dibujar la ruta en el mapa
            let polyline = L.polyline(latlngs, { color: "blue", weight: 5 }).addTo(mapRoute);
            routeSegments.push({ mode: "auto", points: latlngs, polyline });

            // Marcador de inicio
            if (latlngs.length) {
                let startMarker = L.marker(latlngs[0], { icon: L.Icon.Default.prototype }).addTo(mapRoute);
                routeMarkersAuto.push(startMarker);
                // Marcador de fin
                if (latlngs.length > 1) {
                    let endMarker = L.marker(latlngs[latlngs.length - 1], { icon: L.Icon.Default.prototype }).addTo(mapRoute);
                    routeMarkersAuto.push(endMarker);
                }
            }
            mapRoute.fitBounds(polyline.getBounds());
            updateRouteCoords();
        } catch (err) {
            alert("Error procesando el archivo GPX.");
            console.error(err);
        }
    };
    reader.readAsText(file);
});