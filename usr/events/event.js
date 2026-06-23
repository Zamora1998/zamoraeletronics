var CTRL_EVENTS = "/" + chrLocale + "/controller/Events";
var CTRL_ELEVATION = "/" + chrLocale + "/controller/Elevation";
var columns = [];
var tableEvents;
var DateTime = luxon.DateTime;
let input;
let tagsContainer;
let planilla = {};
var TableDataMnth;
var tableeventRoutes;
let mapRoute;
let currentSegment = null;

const currencyFormatterCRC = new Intl.NumberFormat('es-CR', {
    style: 'currency',
    currency: 'CRC',
    minimumFractionDigits: 2
});

$(document).ready(function () {
    tableEvents = $("#tableEvents").DataTable({
        ajax: {
            url: CTRL_EVENTS,
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
                data: "event_name",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) return '';
                    var truncated = data.length > 20 ? data.substring(0, 20) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Descripción",
                data: "description",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return '';
                    var truncated = data.length > 30 ? data.substring(0, 30) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Inicio",
                data: "start_datetime",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return "";
                    return flatpickr.formatDate(new Date(data), "d-M-Y H:i");
                }
            },
                        {
                title: "Tipo de Evento",
                data: "event_type_name",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Fin",
                data: "end_datetime",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return "";
                    return flatpickr.formatDate(new Date(data), "d-M-Y H:i");
                }
            },
            {
                title: "Lugar",
                data: "location",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return '';
                    var truncated = data.length > 10 ? data.substring(0, 10) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
            },
            {
                title: "Estado",
                data: "status_description",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Distancia (Km)",
                data: "distance_km",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Cupo Máx.",
                data: "max_participants",
                orderable: false,
                className: "dt-left dt-nowrap",
            },
            {
                title: "Inscripción Abre",
                data: "registration_open",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return "";
                    return flatpickr.formatDate(new Date(data), "d-M-Y");
                }
            },
            {
                title: "Inscripción Cierra",
                data: "registration_close",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data) {
                    if (!data) return "";
                    return flatpickr.formatDate(new Date(data), "d-M-Y");
                }
            },
            {
                title: labels.tblActions,
                orderable: false,
                searchable: false,
                data: "id",
                className: "dt-right",
                render: function (data, type, row) {
                    return cellRender({
                        type: "dropdown",
                        data,
                        text: labels.tblActions,
                        dataTags: {
                            id: data,
                            uuid: row.uid,   // 👈 nuevo data-uuid

                        },
                        listItems: [
                            {
                                id: "eventEdit_" + data,
                                text: labels.btnEdit,
                                icon: "far fa-edit",
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: "MapEdit_" + data,
                                text: labels.btnCheckpoints,
                                icon: "far fa-map-marked-alt",
                                dataTags: { id: data },
                            },
                            {
                                id: "eventFields_" + data,
                                text: "Campos",
                                icon: "fas fa-list-alt",
                                dataTags: { id: data },
                            },
                            /*{
                                id: 'eventDelete_' + data,
                                text: labels.btnDelete,
                                icon: 'far fa-trash-alt',
                                dataTags: {
                                    id: data
                                },
                            },*/
                            {
                                id: 'eventUploadImg_' + data,
                                text: 'Subir Imagen',
                                icon: 'fas fa-image',
                                dataTags: {
                                    id: data,
                                    uuid: row.uid,
                                },
                            }
                        ],
                    });
                },
            }, //11
        ],
        dom: '<"row"<"#ctrleventsCustom.col-6"><"col-6"f>>ti',
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        drawCallback: function () {
            var html =
                '<button id="eventCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
                labels.btnNew +
                "</button>";
            $("#ctrleventsCustom").html(html);
        }
    });
});

$('#nav-Events-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
});

$('#nav-Routes-tab').on('shown.bs.tab', function (e) {
    $(window).resize()
});

const fpRegistrationOpen = flatpickr("#registration_open", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d-M-Y",
    locale: chrLang,
    disableMobile: true
});

const fpRegistrationClose = flatpickr("#registration_close", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d-M-Y",
    locale: chrLang,
    disableMobile: true
});

const fpStart = flatpickr("#start_datetime", {
    enableTime: true,
    altInput: true,
    altFormat: "d-M-Y H:i",
    dateFormat: "Y-m-d H:i:S",
    minDate: "today",
    locale: chrLang
});
const fpStarttime = flatpickr("#start_time", {
    enableTime: true,   // habilita selección de hora
    noCalendar: true,   // oculta calendario
    dateFormat: "H:i",  // formato 24h: hora:minuto
    time_24hr: true,    // formato 24h
    altInput: true,
    altFormat: "H:i",
    locale: chrLang
});

const fpEndTime = flatpickr("#end_time", {
    enableTime: true,   // habilita selección de hora
    noCalendar: true,   // oculta calendario
    dateFormat: "H:i",  // formato 24h: hora:minuto
    time_24hr: true,    // formato 24h
    altInput: true,
    altFormat: "H:i",
    locale: chrLang
});


const fpEnd = flatpickr("#end_datetime", {
    enableTime: true,
    altInput: true,
    altFormat: "d-M-Y H:i",
    dateFormat: "Y-m-d H:i:S",
    minDate: "today",
    locale: chrLang
});


$(document).on('click', '[id^="eventEdit_"],#eventCreate', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#eventModal').data('id', id);
    $('#eventModalLabel').text((id ? labels.lblEditEvent : labels.lblNewEvent));

    $('#eventModalLabel').text((id ? labels.lblEditEvent : labels.lblNewEvent));

    if (id) {
        $.ajax({
            type: "POST",
            url: CTRL_EVENTS,
            data: {
                action: 'R',
                part: 'E',
                id: id
            },
            dataType: "json",
            success: function (response) {
                var data = response.data[0] || {};

                $('#name').val(data.event_name || '');
                $('#descriptionevent').val(data.description || '');
                $('#location').val(data.location || '');
                $('#distance_km').val(data.distance_km || '');
                $('#max_participants').val(data.max_participants || '');
                $('#latitude').val(data.latitude || '');
                $('#longitude').val(data.longitude || '');

                // Estado con Select2
                $('#status').val(data.status_description || '').trigger('change');

                // Tipo de Evento
                $('#event_type_id').val(data.event_type_id || '').trigger('change');

                // Fechas con Flatpickr
                if (data.start_datetime) {
                    fpStart.setDate(data.start_datetime, true, "Y-m-d H:i:S");
                }
                if (data.end_datetime) {
                    fpEnd.setDate(data.end_datetime, true, "Y-m-d H:i:S");
                }
                if (data.registration_open) {
                    fpRegistrationOpen.setDate(data.registration_open, true, "Y-m-d");
                }
                if (data.registration_close) {
                    fpRegistrationClose.setDate(data.registration_close, true, "Y-m-d");
                }
            }

        });
    } else {
        // Limpiar formulario si es creación
        $('#eventForm')[0].reset();

    }
    $('#eventModal').modal('show');
});


$('#eventSave').click(function (e) {
    e.preventDefault();
    var data = $("#eventModal input, #eventModal select, #eventModal textarea").serializeObject();
    data.id = $('#eventModal').data('id');;
    data.part = 'E'
    data.action = 'C'
    // Ensure event_type_id is the selected numeric id (0 when none)
    var selectedType = $('#event_type_id').val();
    data.event_type_id = (selectedType && selectedType !== '') ? parseInt(selectedType) : 0;
    let hasError = false;

    $.each(data, function (key, value) {
        let $el = $('[name="' + key + '"]'); // siempre el oculto

        if (value === "" || value === null || value === undefined) {
            if ($el[0] && $el[0]._flatpickr && $el[0]._flatpickr.altInput) {
                $($el[0]._flatpickr.altInput).addClass("is-invalid");
            } else {
                $el.addClass("is-invalid");
            }
            hasError = true;
        } else {
            if ($el[0] && $el[0]._flatpickr && $el[0]._flatpickr.altInput) {
                $($el[0]._flatpickr.altInput).removeClass("is-invalid");
            } else {
                $el.removeClass("is-invalid");
            }
        }
    });
    if (hasError) {
        return false;
    }

    $.ajax({
        type: "POST",
        url: CTRL_EVENTS,
        data,
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: "success",
                    text: labels.nteUpdateSuccess,
                    icon: "fas fa-check",
                    timeout: 3000,
                });
            } else {
                alertNotify({
                    type: "warning",
                    text: labels.nteError,
                    icon: "fas fa-times",
                    timeout: 3000,
                });
            }
            $('#eventModal').modal('hide');
            tableEvents.ajax.reload();
        }
    });
});


$(document).ready(function () {
    $('#status').select2({
        theme: "bootstrap-5",
        selectionCssClass: "select2--small",
        dropdownCssClass: "select2--small",
        dropdownParent: $('#eventModal') // Asegura que el dropdown se renderice dentro del modal
    });
});


$("#max_participants").on("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
});

$("#distance_km").on("input", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
});

$(document).on('input change', 'input, select, textarea', function () {
    const valor = $(this).val();
    let $target = $(this);

    // Si este input es el oculto de Flatpickr, usar el visible
    if (this._flatpickr && this._flatpickr.altInput) {
        $target = $(this._flatpickr.altInput);
    }

    if (valor !== null && valor.trim() !== '') {
        $target.removeClass('is-invalid');
    }
});


$('#eventModal').on('hidden.bs.modal', function () {
    $(this).find('input, select, textarea').each(function () {
        $(this).val('').removeClass('is-invalid').trigger('change');
    });

    $(this).find('select').each(function () {
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).val(null).trigger('change');
        }
    });

    $(this).find('.datepicker').each(function () {
        if (this._flatpickr) {
            this._flatpickr.clear();
        }
    });
});

$(document).on('click', '[id^=basetypesDelete_]', function (e) {
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

function deleteBasetype(id) {
    $.ajax({
        type: "POST",
        url: CTRL_EVENTS,
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


// Handler for "Campos" button
$(document).on('click', '[id^="eventFields_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#modalFields').data('id', id); // Store ID in modal
    populateRegistrationTable(id);
    $('#modalFields').modal('show');
});
// Handler for Saving Registration Config
$('#saveRegistrationConfig').click(function (e) {
    e.preventDefault();

    let eventId = $('#modalFields').data('id');
    let registrationConfig = [];

    $('#tblRegistrationConfig tbody tr').each(function (i) {
        let row = $(this);

        registrationConfig[i] = {
            field_name: row.find('.field-name').val(),
            is_enabled: row.find('.is-enabled').is(':checked') ? 1 : 0,
            is_required: row.find('.is-required').is(':checked') ? 1 : 0
        };
    });

    $.ajax({
        type: "POST",
        url: CTRL_EVENTS,
        data: {
            action: 'C',
            part: 'CR',
            id: eventId,
            registrationConfig: registrationConfig
        },
        traditional: false,
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: "success",
                    text: labels.nteUpdateSuccess,
                    icon: "fas fa-check",
                    timeout: 3000,
                });
                $('#modalFields').modal('hide');
            } else {
                alertNotify({
                    type: "warning",
                    text: labels.nteError,
                    icon: "fas fa-times",
                    timeout: 3000,
                });
            }
        }
    });
});

Dropzone.autoDiscover = false;
let dropzoneSettings = false;

$(document).on("click", "[id^='eventUploadImg_']", function () {
    const uuid = $(this).data("uuid");
    const id = $(this).data("id");
    const uploadUrl = CTRL_EVENTS;

    if (dropzoneSettings) {
        dropzoneSettings.destroy();
    }

    dropzoneSettings = new Dropzone("#dropSettings", {
        url: uploadUrl,
        paramName: "file[]",
        uploadMultiple: true,
        autoProcessQueue: false,
        previewsContainer: "#previewsContainer",
        previewTemplate: document.querySelector("#template-container").innerHTML,
        maxFilesize: 5,
        maxFiles: 2,
        acceptedFiles: "image/*",
        addRemoveLinks: true,
        dictRemoveFile: "Eliminar",

        init: function () {
            this.options.uuid = uuid;
            this.options.id = id;

            this.on("addedfile", function (file) {
                console.log("Archivo agregado:", file.name);
            });

            this.on("sending", function (file, xhr, formData) {
                const index = this.getAcceptedFiles().indexOf(file) + 1;
                const newName = this.options.uuid + "_image" + index;

                console.log("Renombrando archivo:", file.name, "→", newName);

                formData.append("uuid", this.options.uuid);
                formData.append("id", this.options.id);
                formData.append("uuid_image" + index, newName);
                formData.append("action", "U");
                formData.append("part", "U");
                formData.append("filename", newName);
            });

            this.on("success", function (file, response) {
                console.log("✅ Archivo subido:", response);
            });

            this.on("error", function (file, errorMessage) {
                console.error("❌ Error al subir archivo:", errorMessage);
            });

            this.on("maxfilesexceeded", function (file) {
                this.removeFile(file);
                alertNotify({
                    type: "warning",
                    text: "Solo puedes subir 2 imágenes",
                    icon: "fas fa-exclamation-circle",
                    timeout: 3000,
                });
            });

            this.on("queuecomplete", function () {
                alertNotify({
                    type: "success",
                    text: "Imágenes subidas correctamente",
                    icon: "fas fa-check",
                    timeout: 3000,
                });
                $('#modalSettings').modal('hide');
            });
        }
    });

    const modalSettings = new bootstrap.Modal(document.getElementById('modalSettings'), {
        backdrop: 'static',
        keyboard: false
    });
    modalSettings.show();
});

$(document).on("click", "#settingsSave", function () {
    if (dropzoneSettings.getQueuedFiles().length > 0) {
        dropzoneSettings.processQueue();
    } else {
        $.ajax({
            type: "POST",
            url: CTRL_EVENTS,
            data: {
                action: "U",
                part: "U",
                uuid: dropzoneSettings.options.uuid,
                id: dropzoneSettings.options.id
            },
            dataType: "json",
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: "success",
                        text: "Datos guardados correctamente",
                        icon: "fas fa-check",
                        timeout: 3000,
                    });
                    $('#modalSettings').modal('hide');
                } else {
                    alertNotify({
                        type: "danger",
                        text: "Error al guardar los datos",
                        icon: "fas fa-exclamation-triangle",
                        timeout: 3000,
                    });
                }
            }
        });
    }
});


// #region mapa puntos
var mapCompleteRoute; // variable global
var routeColors = ['green', 'blue', 'orange', 'purple', 'brown', 'pink']; // colores predefinidos
function getRouteColor(index) {
    return routeColors[index % routeColors.length];
}

$(document).on('click', '[id^="MapEdit_"]', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        type: "POST",
        url: CTRL_EVENTS,
        data: { action: 'R', part: 'ER', id: id },
        dataType: "json",
        success: function (response) {
            const routes = response.data || [];
            const $modal = $("#modalCheckPoints");

            $modal.modal("show");

            $modal.off("shown.bs.modal").on("shown.bs.modal", function () {
                $("#mapCompleteRoute").css({ height: '500px', width: '100%' });

                if (window.mapCompleteRoute) {
                    mapCompleteRoute.remove();
                }
                mapCompleteRoute = L.map('mapCompleteRoute').setView([9.934739, -84.087502], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/">OSM</a> contributors'
                }).addTo(mapCompleteRoute);
                renderRouteInfo(routes);

                let allPoints = [];

                routes.forEach((route, index) => {
                    if (!route.coordinates) return;

                    let points = [];
                    try {
                        points = JSON.parse(route.coordinates).map(p => [parseFloat(p[0]), parseFloat(p[1])]);
                    } catch {
                        console.warn("Coordenadas inválidas para ruta " + route.name);
                        return;
                    }

                    if (points.length) {
                        const color = getRouteColor(index);
                        L.polyline(points, { color: color, weight: 4 }).addTo(mapCompleteRoute);
                        L.circleMarker(points[0], { color: color, radius: 6 }).addTo(mapCompleteRoute).bindPopup(route.name + " Inicio");
                        L.circleMarker(points[points.length - 1], { color: color, radius: 6 }).addTo(mapCompleteRoute).bindPopup(route.name + " Fin");

                        allPoints = allPoints.concat(points); // para ajustar el bounds global
                    }
                });

                if (allPoints.length) {
                    mapCompleteRoute.fitBounds(L.latLngBounds(allPoints));
                }
                //$("#routeCheckPoints").val(JSON.stringify(routes.map(r => r.coordinates)));
            });
        }
    });
});

function renderRouteInfo(routes) {
    const $container = $(".row.align-items-end");
    $container.empty(); // Limpiar contenido previo

    const routeColors = ['green', 'blue', 'orange', 'purple', 'brown', 'pink', 'cyan'];
    const getRouteColor = (index) => routeColors[index % routeColors.length];

    routes.forEach((route, index) => {
        const color = getRouteColor(index);

        const $col = $(`
            <div class="col-auto mb-2">
                <div class="p-2 rounded shadow-sm" style="background-color:white; display:flex; align-items:center; gap:8px;">
                    <span style="width:12px; height:12px; border-radius:50%; background-color:${color}; display:inline-block;"></span>
                    <span><strong>${route.route_name}</strong> (${route.route_distance_km} km)</span>
                </div>
            </div>
        `);

        $container.append($col);
    });
}

function populateRegistrationTable(eventId) {
    const fields = [
        { name: 'name', label: 'Nombre' },
        { name: 'last_name', label: 'Apellido' },
        { name: 'second_last_name', label: 'Segundo Apellido' },
        { name: 'idcard', label: 'Cédula / Pasaporte' },
        { name: 'age', label: 'Edad' },
        { name: 'poliza_beneficiary_name', label: 'Nombre Beneficiario' },
        { name: 'poliza_beneficiary_id', label: 'ID Beneficiario' },
        { name: 'email', label: 'Correo Electrónico' },
        { name: 'phone', label: 'Teléfono' },
        { name: 'talla', label: 'Talla Camisa' },
        { name: 'genero', label: 'Género' }
    ];


    const $tbody = $('#tblRegistrationConfig tbody');
    $tbody.empty();

    let existingConfig = {};

    // Helper to render rows
    const renderRows = () => {
        fields.forEach(field => {
            let config = existingConfig[field.name] || {};
            let isEnabled = config.is_enabled == 1 ? 'checked' : '';
            let isRequired = config.is_required == 1 ? 'checked' : '';

            let row = `
                <tr>
                    <td>
                        ${field.label}
                        <input type="hidden" class="field-name" value="${field.name}">
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input is-enabled" ${isEnabled}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input is-required" ${isRequired}>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });
    };

    if (eventId > 0) {
        $.ajax({
            type: "POST",
            url: CTRL_EVENTS,
            data: {
                action: 'R',
                part: 'RC', // Get Registration Config
                id: eventId
            },
            dataType: "json",
            success: function (response) {
                if (response.data) {
                    response.data.forEach(item => {
                        existingConfig[item.field_name] = item;
                    });
                }
                renderRows();
            },
            error: function () {
                renderRows(); // Fallback to defaults on error
            }
        });
    } else {
        // Default enabled fields for new event?
        // Let's enable all by default or let user choose. 
        // For now, render with defaults (all disabled/empty except name/email maybe?)
        // Assuming empty start is fine.
        renderRows();
    }
}
