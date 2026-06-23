var REGISTRATION_CTRL = "/" + 'es_CR' + "/controller/registration";

$('#eventSelect').select2({
    theme: 'bootstrap-5',
    width: '100%'
});
$('#distanceSelect').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: "Seleccione una distancia"
});
$('#shirtSize').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: "Seleccione una talla"
});
$('#gender').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: "Seleccione"
});
if ($('#eventDate').length) {
    flatpickr("#eventDate", {
        dateFormat: "d-M-Y",
        locale: "es",
        minDate: "today"
    });
}

$(document).ready(function () {
    window = window || {};
    // Inicializar mapas para cada ruta de cada evento
    if (window.eventMapsData) {
        window.eventMapsData.forEach(function (mapData) {
            setTimeout(function () {
                var mapId = mapData.mapId;
                var coords = mapData.coords;
                if (!coords.length) return;
                var mapDiv = document.getElementById(mapId);
                if (!mapDiv) return;
                mapDiv.innerHTML = "";
                if (mapDiv._leaflet_id) return;
                var map = L.map(mapId, {
                    zoomControl: false,         // 👈 habilita controles de zoom
                    attributionControl: false,
                    dragging: true,            // 👈 permite mover el mapa
                    scrollWheelZoom: false,    // 👈 desactiva zoom con rueda del mouse
                    doubleClickZoom: false,    // 👈 sin zoom doble clic
                    boxZoom: false,
                    keyboard: true,
                    tap: true
                });

                // Agregar los controles de zoom en otra posición (si quieres)
                L.control.zoom({
                    position: 'topright'       // opciones: 'topleft', 'topright', 'bottomleft', 'bottomright'
                }).addTo(map);
                var latlngs = coords.map(function (c) {
                    return [parseFloat(c[0]), parseFloat(c[1])];
                });
                var bounds = L.latLngBounds(latlngs);
                map.fitBounds(bounds, {
                    padding: [10, 10]
                });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    minZoom: 10,
                    maxZoom: 16
                }).addTo(map);
                L.polyline(latlngs, {
                    color: '#198754',
                    weight: 5
                }).addTo(map);
                if (latlngs.length) {
                    L.marker(latlngs[0], {
                        title: "Inicio"
                    }).addTo(map);
                    L.marker(latlngs[latlngs.length - 1], {
                        title: "Fin"
                    }).addTo(map);
                }
                var chartDiv = document.querySelector(`#chart-${mapId}`);
                if (chartDiv) initDesnivelChart(chartDiv, map);

            }, 200);
        });
    }
    // Bootstrap validation
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })();
});

// Abrir modal de registro con datos de la ruta
$(document).on('click', '.btn-inscribirse-ruta', function () {
    var routeUid = $(this).data('route_uid');
    var eventName = $(this).data('event_name');
    var routeName = $(this).data('route_name');
    var registrationCloseStr = $(this).data('registration_close');
    
    // Guardar los datos para usarlos si el usuario acepta inscribirse desde el modal cerrado
    $(document).data('pendingRegistration', {
        routeUid: routeUid,
        eventName: eventName,
        routeName: routeName
    });
    
    // Validar si la fecha de cierre ha pasado
    if (registrationCloseStr) {
        var registrationClose = new Date(registrationCloseStr);
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        registrationClose.setHours(0, 0, 0, 0);
        
        if (today > registrationClose) {
            // Las inscripciones están cerradas - mostrar modal
            var closedModal = new bootstrap.Modal(document.getElementById('closedRegistrationModal'));
            closedModal.show();
            return;
        }
    }
    
    // Si llegamos aquí, las inscripciones están abiertas
    showRegistrationForm(routeUid, eventName, routeName);
});

// Función auxiliar para mostrar el formulario de registro
function showRegistrationForm(routeUid, eventName, routeName) {
    $('#registrationModal')
        .attr('data-id', routeUid)
        .attr('data-event_name', eventName)
        .attr('data-route_name', routeName);
    var title = 'Formulario de inscripción';
    if (eventName) title += ' - ' + eventName;
    if (routeName) title += ' / ' + routeName;
    $('#modalEventTitle').text(title);
    var modal = new bootstrap.Modal(document.getElementById('registrationModal'));
    modal.show();
}

// Cuando el usuario acepta inscribirse desde el modal cerrado
$(document).on('click', '#proceedClosedRegistration', function () {
    var pendingData = $(document).data('pendingRegistration') || {};
    // Cerrar el modal de inscripciones cerradas
    bootstrap.Modal.getInstance(document.getElementById('closedRegistrationModal')).hide();
    
    // Mostrar el formulario de registro
    setTimeout(function () {
        showRegistrationForm(pendingData.routeUid, pendingData.eventName, pendingData.routeName);
    }, 300);
});

// Mostrar modal de Políticas de Pago sobre el de registro
$('#eventRegistrationForm').on('submit', function (e) {
    e.preventDefault();
    var form = this;
    if (form.checkValidity()) {
        // Oculta el modal de registro pero deja el fondo
        $('#registrationModal').modal('hide');
        setTimeout(function () {
            var paymentModal = new bootstrap.Modal(document.getElementById('paymentPolicyModal'));
            paymentModal.show();
        }, 400); // Espera a que termine la animación del modal anterior
    } else {
        form.classList.add('was-validated');
    }
});

$("#age").on("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
});

$("#phone").on("input", function () {
    // Solo números
    this.value = this.value.replace(/[^0-9]/g, "");
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});

$("#idcard").on("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");

    if (this.value.length > 16) {
        this.value = this.value.slice(0, 16);
    }
});


$("#firstName").on("input", function () {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]/g, "");
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});

$("#lastName").on("input", function () {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]/g, "");
    if (this.value.length > 13) {
        this.value = this.value.slice(0, 13);
    }
});

$("#secondLastName").on("input", function () {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]/g, "");
    if (this.value.length > 13) {
        this.value = this.value.slice(0, 13);
    }
});

$("#beneficiaryName").on("input", function () {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, "");
    if (this.value.length > 30) {
        this.value = this.value.slice(0, 30);
    }
});

$("#beneficiaryId").on("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
    if (this.value.length > 16) {
        this.value = this.value.slice(0, 16);
    }
});

$('#confirmRegistrationBtn').click(function (e) {
    e.preventDefault();
    var routeUid = $('#registrationModal').attr('data-id');    
    var data = {};
    $('#registrationModal [name]').each(function () {
        data[$(this).attr('name')] = $(this).val();
    });
    data.uuid = routeUid;
    console.log(data);
    $.ajax({
        type: "POST",
        url: REGISTRATION_CTRL,
        data,
        dataType: "json",
        success: function (response) {
            if (response.data && response.data.result) {
                showCustomNotify('success', '¡Registro completado con éxito!, te hemos enviado un correo con los detalles. Recuerda revisar tu carpeta de spam.');
                $('#registrationModal').one('hidden.bs.modal', function () {
                    const $form = $(this).find('form');
                    $form[0]?.reset();
                    $form.find('.select2').val(null).trigger('change');
                    $form.removeClass('was-validated');
                });
                $('#paymentPolicyModal').modal('hide');
            } else {
                showCustomNotify('error', 'Ocurrió un error al registrar. Intenta nuevamente.');
            }
        }

    });
});

$('#paymentPolicyModal').on('hidden.bs.modal', function () {
    const $form = $('#registrationModal').find('form');
    $form[0]?.reset();
    $form.find('.select2').val(null).trigger('change');
    $form.removeClass('was-validated');
});

$('#closeheaderregistration, #closeregistration').on('click', function () {
    const $form = $('#registrationModal').find('form');
    $form[0]?.reset();
    $form.find('.select2').val(null).trigger('change');
    $form.removeClass('was-validated');
});


function showCustomNotify(type, text, timeout = 3000) {
    // Eliminar si ya existe
    const existing = document.getElementById('customCenterNotify');
    if (existing) existing.remove();

    // Overlay (fondo oscuro estilo modal)
    const overlay = document.createElement('div');
    overlay.id = 'customCenterNotify';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.background = 'rgba(0, 0, 0, 0.6)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = 11000;
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.4s ease';

    // Contenedor del icono y texto
    const modalBox = document.createElement('div');
    modalBox.style.background = '#fff';
    modalBox.style.borderRadius = '12px';
    modalBox.style.padding = '30px 40px';
    modalBox.style.textAlign = 'center';
    modalBox.style.boxShadow = '0 6px 20px rgba(0,0,0,0.3)';
    modalBox.style.display = 'flex';
    modalBox.style.flexDirection = 'column';
    modalBox.style.alignItems = 'center';
    modalBox.style.animation = 'scaleIn 0.3s ease';

    // Animación con SVG
    let svgIcon = '';
    if (type === 'success') {
        svgIcon = `
    <svg width="100" height="100" viewBox="0 0 60 60">
      <circle 
        cx="30" cy="30" r="26"
        fill="none"
        stroke="#4caf50"
        stroke-width="4"
        stroke-linecap="round"
      />
      <path 
        fill="none"
        stroke="#4caf50"
        stroke-width="4"
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M18 31l7 7 16-16">
        <animate attributeName="stroke-dasharray" from="0,60" to="60,0" dur="0.6s" fill="freeze"/>
      </path>
    </svg>`;
    } else {
        svgIcon = `
    <svg width="100" height="100" viewBox="0 0 60 60">
      <circle 
        cx="30" cy="30" r="26"
        fill="none"
        stroke="#f44336"
        stroke-width="4"
        stroke-linecap="round"
      />
      <path 
        fill="none"
        stroke="#f44336"
        stroke-width="4"
        stroke-linecap="round"
        d="M20 20 40 40">
        <animate attributeName="stroke-dasharray" from="0,60" to="60,0" dur="0.6s" fill="freeze"/>
      </path>
      <path 
        fill="none"
        stroke="#f44336"
        stroke-width="4"
        stroke-linecap="round"
        d="M40 20 20 40">
        <animate attributeName="stroke-dasharray" from="0,60" to="60,0" dur="0.6s" begin="0.2s" fill="freeze"/>
      </path>
    </svg>`;
    }

    // Insertar SVG
    const iconWrapper = document.createElement('div');
    iconWrapper.innerHTML = svgIcon;

    // Texto debajo
    const message = document.createElement('div');
    message.textContent = text;
    message.style.marginTop = '20px';
    message.style.fontSize = '20px';
    message.style.color = '#333';

    // Agregar al modal
    modalBox.appendChild(iconWrapper);
    modalBox.appendChild(message);
    overlay.appendChild(modalBox);
    document.body.appendChild(overlay);

    // Animar entrada
    setTimeout(() => {
        overlay.style.opacity = '1';
    }, 50);

    // Quitar después de timeout
    setTimeout(() => {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 400);
    }, timeout);
}

// Extra: animación scaleIn
const style = document.createElement('style');
style.textContent = `
@keyframes scaleIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}`;


function initDesnivelChart(chartDiv, map) {
    var $div = $(chartDiv);
    var coords = JSON.parse($div.attr('data-coordinates'));
    if (!coords.length || !map) return;

    var heights = coords.map(() => Math.floor(Math.random() * 200) + 200);

    if (!map._routeLatLngs) {
        map._routeLatLngs = coords.map(c => [parseFloat(c[0]), parseFloat(c[1])]);
    }

    var chart = echarts.init(chartDiv);
    chart.setOption({
        xAxis: { type: 'category', show: false, data: heights.map(() => '') },
        yAxis: { type: 'value', name: 'Desnivel (m)', min: 0 },
        series: [{
            data: heights,
            type: 'line',
            smooth: true,
            lineStyle: { color: '#4bc0c0' },
            areaStyle: { color: 'rgba(75, 192, 192, 0.2)' },
            symbol: 'none'
        }],
        tooltip: { trigger: 'axis' },
        grid: { left: 0, right: 0, top: 0, bottom: 0 }
    });

    chart.getZr().on('mousemove', function (event) {
        var pointInPixel = [event.offsetX, event.offsetY];
        var pointInGrid = chart.convertFromPixel({ seriesIndex: 0 }, pointInPixel);
        var dataIndex = Math.round(pointInGrid[0]);

        if (!map._routeLatLngs[dataIndex]) return;

        if (map._hoverMarker) map.removeLayer(map._hoverMarker);

        map._hoverMarker = L.circleMarker(map._routeLatLngs[dataIndex], {
            radius: 8,
            color: 'red',
            fillColor: 'red',
            fillOpacity: 0.5
        }).addTo(map);
    });


    chart.on('mouseout', function () {
        if (map._hoverMarker) map.removeLayer(map._hoverMarker);
    });
}

$(document).on('click', '.clickable-image', function () {
    var src = $(this).attr('src');
    $('#modalImage').attr('src', src);
    $('#imageModal').modal('show');
});
