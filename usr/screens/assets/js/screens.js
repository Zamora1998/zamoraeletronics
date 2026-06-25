/**
 * screens.js
 * JavaScript principal del módulo de gestión de televisores — RZamora Electronics.
 * Maneja:
 *  - Vista de listado (filtros, eliminación de órdenes)
 *  - Formulario de edición (cliente, TV, falla, estado, partes, firma, Maps)
 *  - Catálogos (marcas, modelos con PDF, partes)
 */
'use strict';
var CTRLSCREENS = "/" + chrLocale + "/controller/screens";
var CTRLSCREENCATALOGS = "/" + chrLocale + "/controller/screencatalogs";

var ScreensApp = (function () {

    // =========================================================
    // UTILIDADES COMUNES
    // =========================================================

    function toast(msg, type) {
        type = type || 'success';
        if (typeof alertNotify === 'function') {
            alertNotify({
                type: type === 'error' ? 'danger' : 'success',
                text: msg,
                icon: type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle',
                timeout: 3500
            });
        } else {
            alert(msg);
        }
    }

    function ajax(url, data, callback, isFormData) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        if (!isFormData) {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    callback(null, JSON.parse(xhr.responseText));
                } catch (e) {
                    callback('Respuesta inválida del servidor.');
                }
            } else {
                callback('Error HTTP ' + xhr.status);
            }
        };
        xhr.onerror = function () { callback('Error de red.'); };
        if (isFormData) {
            xhr.send(data);
        } else {
            var params = Object.keys(data).map(function (k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
            }).join('&');
            xhr.send(params);
        }
    }

    function formatCRC(n) {
        n = parseFloat(n) || 0;
        return '₡' + n.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // =========================================================
    // MÓDULO: LISTADO DE ÓRDENES
    // =========================================================

    function initList(opts) {
        var grid = document.getElementById('orders-grid');
        var selEstado = document.getElementById('filtro-estado');
        var selPago = document.getElementById('filtro-pago');
        var inpBuscar = document.getElementById('filtro-buscar');
        var modal = document.getElementById('modal-confirm');
        var btnCancel = document.getElementById('modal-cancel');
        var btnOk = document.getElementById('modal-ok');
        var pendingDel = null;

        // Resetear filtros al cargar (evita que el navegador restaure valores anteriores)
        if (selEstado) selEstado.value = '';
        if (selPago) selPago.value = '';
        if (inpBuscar) inpBuscar.value = '';

        function filterCards() {
            var estado = selEstado ? selEstado.value : '';
            var pago = selPago ? selPago.value : '';
            var buscar = inpBuscar ? inpBuscar.value.toLowerCase() : '';
            var cards = grid.querySelectorAll('.order-card');
            cards.forEach(function (c) {
                var ok = true;
                if (estado && c.dataset.estado !== estado) ok = false;
                if (pago && c.dataset.pago !== pago) ok = false;
                if (buscar && c.dataset.search.indexOf(buscar) === -1) ok = false;
                c.style.display = ok ? '' : 'none';
            });
        }

        if (selEstado) selEstado.addEventListener('change', filterCards);
        if (selPago) selPago.addEventListener('change', filterCards);
        if (inpBuscar) inpBuscar.addEventListener('input', filterCards);

        // Eliminar orden
        grid.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-del-order');
            if (!btn) return;
            pendingDel = btn.dataset.id;
            modal.style.display = 'flex';
        });

        if (btnCancel) btnCancel.addEventListener('click', function () {
            modal.style.display = 'none';
            pendingDel = null;
        });

        if (btnOk) btnOk.addEventListener('click', function () {
            if (!pendingDel) return;
            var id = pendingDel;
            modal.style.display = 'none';
            pendingDel = null;
            ajax(opts.ajaxUrl, { action: 'D', part: 'OR', orderId: id }, function (err, res) {
                if (err || !res.result) {
                    toast(err || res.error || 'Error al eliminar.', 'error');
                } else {
                    var card = grid.querySelector('[data-id="' + id + '"]');
                    if (card) card.remove();
                    toast('Orden eliminada.');
                }
            });
        });
    }

    // =========================================================
    // MÓDULO: FORMULARIO DE EDICIÓN
    // =========================================================

    function initEdit() {
        var orderId = window.SC_ORDER_ID || 0;
        var ajaxUrl = CTRLSCREENS;
        var modelsData = window.SC_MODELS_DATA || [];

        // --- Elementos ---
        var selCliente = document.getElementById('sel-cliente');
        var inpNombre = document.getElementById('inp-nombre');
        var inpTelefono = document.getElementById('inp-telefono');
        var inpUbicacion = document.getElementById('inp-ubicacion');
        var inpLatitud = document.getElementById('inp-latitud');
        var inpLongitud = document.getElementById('inp-longitud');
        var btnMaps = document.getElementById('btn-maps');
        var btnGuardarCli = document.getElementById('btn-guardar-cliente');
        var clienteIdEl = document.getElementById('cliente-id-hidden');

        var selMarca = document.getElementById('sel-marca');
        var selModelo = document.getElementById('sel-modelo');
        var inpModeloLibre = document.getElementById('inp-modelo-libre');
        var inpPantallaLibre = document.getElementById('inp-pantalla-libre');
        var pdfLinkWrap = document.getElementById('pdf-link-wrap');
        var pdfLink = document.getElementById('pdf-link');

        var inpFalla = document.getElementById('inp-falla');
        var inpCosto = document.getElementById('inp-costo');
        var inpAbono = document.getElementById('inp-abono');
        var inpNotas = document.getElementById('inp-notas');

        var pillsEstado = document.querySelectorAll('.pill');
        var inpEstado = document.getElementById('inp-estado');
        var selPago = document.getElementById('sel-pago');

        var btnGuardar = document.getElementById('btn-guardar-orden');

        var partesSection = document.getElementById('partes-section');
        var firmaSection = document.getElementById('firma-section');

        // Partes
        var selParte = document.getElementById('sel-parte');
        var inpCantidad = document.getElementById('inp-cantidad');
        var inpPrecioUnit = document.getElementById('inp-precio-unit');
        var btnAddPart = document.getElementById('btn-add-part');
        var partsTbody = document.getElementById('parts-tbody');
        var partsTotal = document.getElementById('parts-total');

        // --- Cliente existente → rellenar campos ---
        if (selCliente) {
            selCliente.addEventListener('change', function () {
                var opt = this.options[this.selectedIndex];
                if (!opt.value) return;
                inpNombre.value = opt.dataset.nombre || '';
                inpTelefono.value = opt.dataset.telefono || '';
                inpUbicacion.value = opt.dataset.ubicacion || '';
                inpLatitud.value = opt.dataset.lat || '';
                inpLongitud.value = opt.dataset.lng || '';
                clienteIdEl.dataset.id = opt.value;
                updateMapsLink();
            });
        }

        // --- Google Maps link ---
        function updateMapsLink() {
            if (!btnMaps) return;
            var lat = inpLatitud.value.trim();
            var lng = inpLongitud.value.trim();
            var dir = inpUbicacion.value.trim();
            if (lat && lng) {
                btnMaps.href = 'https://maps.google.com/?q=' + lat + ',' + lng;
                btnMaps.style.display = 'inline-flex';
            } else if (dir) {
                btnMaps.href = 'https://maps.google.com/?q=' + encodeURIComponent(dir);
                btnMaps.style.display = 'inline-flex';
            } else {
                btnMaps.style.display = 'none';
            }
        }

        if (inpUbicacion) inpUbicacion.addEventListener('input', updateMapsLink);
        if (inpLatitud) inpLatitud.addEventListener('input', updateMapsLink);
        if (inpLongitud) inpLongitud.addEventListener('input', updateMapsLink);
        updateMapsLink();

        // --- Guardar / crear cliente ---
        if (btnGuardarCli) {
            btnGuardarCli.addEventListener('click', function () {
                var nombre = inpNombre.value.trim();
                var tel = inpTelefono.value.trim();
                if (!nombre || !tel) { toast('Nombre y teléfono son requeridos.', 'error'); return; }
                ajax(ajaxUrl, {
                    action: 'U', part: 'CL',
                    clientId: clienteIdEl.dataset.id || 0,
                    nombre: nombre,
                    telefono: tel,
                    ubicacion: inpUbicacion.value.trim(),
                    latitud: inpLatitud.value.trim(),
                    longitud: inpLongitud.value.trim()
                }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error al guardar cliente.', 'error'); return; }
                    clienteIdEl.dataset.id = res.data.clientId;
                    toast('Cliente guardado correctamente.');
                    updateMapsLink();
                });
            });
        }

        // --- Filtrar modelos por marca ---
        if (selMarca) {
            selMarca.addEventListener('change', function () {
                var brandId = this.value;
                var opts = selModelo.options;
                for (var i = 0; i < opts.length; i++) {
                    var opt = opts[i];
                    if (!opt.value) continue;
                    var md = modelsData.find(function (m) { return m.id == opt.value; });
                    opt.style.display = (!brandId || (md && md.brand_id == brandId)) ? '' : 'none';
                }
                selModelo.value = '';
                pdfLinkWrap.style.display = 'none';
            });
        }

        // --- Seleccionar modelo → rellenar pantalla y PDF ---
        if (selModelo) {
            selModelo.addEventListener('change', function () {
                var modelId = this.value;
                if (!modelId) { pdfLinkWrap.style.display = 'none'; return; }
                var md = modelsData.find(function (m) { return m.id == modelId; });
                if (md) {
                    inpPantallaLibre.value = md.pantalla || '';
                    if (md.pdf_ruta) {
                        pdfLink.href = '/' + md.pdf_ruta;
                        pdfLinkWrap.style.display = '';
                    } else {
                        pdfLinkWrap.style.display = 'none';
                    }
                }
            });
        }

        // --- Pills de estado ---
        pillsEstado.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pillsEstado.forEach(function (p) {
                    p.className = 'pill';
                });
                var e = this.dataset.estado;
                this.className = 'pill active-' + e;
                inpEstado.value = e;
            });
        });

        // --- Parte seleccionada → rellenar precio ---
        if (selParte) {
            selParte.addEventListener('change', function () {
                var opt = this.options[this.selectedIndex];
                if (opt.dataset.precio) inpPrecioUnit.value = opt.dataset.precio;
            });
        }

        // --- Calcular total partes ---
        function recalcPartsTotal() {
            if (!partsTotal) return;
            var rows = partsTbody.querySelectorAll('tr');
            var total = 0;
            rows.forEach(function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length >= 4) {
                    var subtxt = cells[3].textContent.replace(/[^0-9]/g, '');
                    total += parseInt(subtxt) || 0;
                }
            });
            partsTotal.textContent = formatCRC(total);
        }

        // --- Agregar parte a la orden ---
        if (btnAddPart) {
            btnAddPart.addEventListener('click', function () {
                var partId = selParte.value;
                if (!partId) { toast('Seleccione un repuesto.', 'error'); return; }
                if (!orderId) { toast('Primero guarde la orden principal.', 'error'); return; }
                var cantidad = parseInt(inpCantidad.value) || 1;
                var precioUnit = parseFloat(inpPrecioUnit.value) || 0;
                ajax(ajaxUrl, {
                    action: 'U', part: 'OP',
                    orderId: orderId,
                    orderPartId: 0,
                    partId: partId,
                    cantidad: cantidad,
                    precioUnit: precioUnit
                }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error al agregar parte.', 'error'); return; }
                    var partNombre = selParte.options[selParte.selectedIndex].text.split('—')[0].trim();
                    var subtotal = cantidad * precioUnit;
                    var tr = document.createElement('tr');
                    var newOrderPartId = res.data.orderPartId;
                    tr.dataset.id = newOrderPartId;
                    tr.innerHTML = '<td>' + partNombre + '</td>' +
                        '<td>' + cantidad + '</td>' +
                        '<td>' + formatCRC(precioUnit) + '</td>' +
                        '<td>' + formatCRC(subtotal) + '</td>' +
                        '<td><button class="btn btn-danger btn-sm btn-del-part" data-id="' + newOrderPartId + '">✕</button></td>';
                    partsTbody.appendChild(tr);
                    recalcPartsTotal();
                    toast('Parte agregada.');
                    selParte.value = '';
                    inpCantidad.value = 1;
                    inpPrecioUnit.value = 0;
                });
            });
        }

        // --- Eliminar parte ---
        if (partsTbody) {
            partsTbody.addEventListener('click', function (e) {
                var btn = e.target.closest('.btn-del-part');
                if (!btn) return;
                var partRowId = btn.dataset.id;
                ajax(ajaxUrl, { action: 'D', part: 'OP', orderId: orderId, orderPartId: partRowId }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                    var row = partsTbody.querySelector('[data-id="' + partRowId + '"]');
                    if (row) row.remove();
                    recalcPartsTotal();
                    toast('Parte eliminada.');
                });
            });
        }

        // --- GUARDAR ORDEN ---
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function () {
                var clientId = clienteIdEl ? clienteIdEl.dataset.id : 0;
                if (!clientId) { toast('Primero guarde o seleccione un cliente.', 'error'); return; }
                var falla = inpFalla.value.trim();
                if (!falla) { toast('La falla reportada es requerida.', 'error'); return; }

                ajax(ajaxUrl, {
                    action: 'U',
                    part: 'OR',
                    orderId: orderId,
                    clientId: clientId,
                    brandId: selMarca ? selMarca.value : 0,
                    modelId: selModelo ? selModelo.value : 0,
                    modeloLibre: inpModeloLibre ? inpModeloLibre.value.trim() : '',
                    pantallaLibre: inpPantallaLibre ? inpPantallaLibre.value.trim() : '',
                    fallaReportada: falla,
                    costoEstimado: inpCosto ? inpCosto.value : 0,
                    abonoInicial: inpAbono ? inpAbono.value : 0,
                    estado: inpEstado ? inpEstado.value : 'pendiente',
                    tipoPago: selPago ? selPago.value : 'pendiente',
                    notas: inpNotas ? inpNotas.value.trim() : ''
                }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error al guardar orden.', 'error'); return; }
                    toast('Orden guardada correctamente.');
                    if (!orderId) {
                        orderId = res.data.orderId;
                        window.SC_ORDER_ID = orderId;
                        // Mostrar secciones de partes y firma
                        if (partesSection) partesSection.style.display = '';
                        if (firmaSection) firmaSection.style.display = '';
                        // Actualizar URL sin recargar
                        if (window.history && window.history.pushState) {
                            history.pushState({}, '', '/screens/edit?orderId=' + orderId);
                        }
                    }
                });
            });
        }

        // --- FIRMA CON SIGNATURE PAD ---
        var sigCanvas = document.getElementById('sig-canvas');
        var sigPad = null;

        if (sigCanvas && window.SignaturePad) {
            // Ajustar tamaño del canvas al wrapper
            function resizeCanvas() {
                var wrap = document.getElementById('sig-wrap');
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                sigCanvas.width = wrap.offsetWidth * ratio;
                sigCanvas.height = 200 * ratio;
                sigCanvas.getContext('2d').scale(ratio, ratio);
                if (sigPad) sigPad.clear();
            }
            sigPad = new SignaturePad(sigCanvas, { backgroundColor: 'rgb(255,255,255)', penColor: '#111827' });
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();
        }

        var btnSigClear = document.getElementById('btn-sig-clear');
        var btnSigSave = document.getElementById('btn-sig-save');
        var sigPreview = document.getElementById('sig-preview');

        if (btnSigClear && sigPad) {
            btnSigClear.addEventListener('click', function () { sigPad.clear(); if (sigPreview) sigPreview.style.display = 'none'; });
        }

        if (btnSigSave && sigPad) {
            btnSigSave.addEventListener('click', function () {
                if (sigPad.isEmpty()) { toast('Por favor dibuje la firma primero.', 'error'); return; }
                if (!orderId) { toast('Primero guarde la orden.', 'error'); return; }
                var dataUrl = sigPad.toDataURL('image/png');
                if (sigPreview) { sigPreview.src = dataUrl; sigPreview.style.display = 'block'; }
                ajax(ajaxUrl, {
                    action: 'U',
                    part: 'FI',
                    orderId: orderId,
                    firmaBase64: dataUrl
                }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error al guardar firma.', 'error'); return; }
                    toast('Firma guardada correctamente.');
                });
            });
        }

        // --- GENERAR PDF / IMPRIMIR ORDEN ---
        var btnPrintPdf = document.getElementById('btn-print-pdf');
        if (btnPrintPdf) {
            btnPrintPdf.addEventListener('click', function () {
                // Si hay una firma guardada en el servidor pero sin preview cargado,
                // intentamos mostrar el sig-preview (ya podría estar seteado desde el guardado)
                // Si sig-preview no tiene src pero existe un enlace de firma, lo buscamos.
                var firmaSaved = document.querySelector('.firma-saved');
                var firmaLink = firmaSaved ? firmaSaved.nextElementSibling : null;
                if (sigPreview && !sigPreview.src && firmaLink && firmaLink.href) {
                    sigPreview.onload = function () { window.print(); };
                    sigPreview.src = firmaLink.href;
                    sigPreview.style.display = 'block';
                } else {
                    window.print();
                }
            });
        }
    }

    // =========================================================
    // MÓDULO: CATÁLOGOS
    // =========================================================

    function initCatalogs(opts) {
        var ajaxUrl = CTRLSCREENCATALOGS;

        // --- DataTables ---
        var dtLangUrl = window.TABLELANG || "//cdn.datatables.net/plug-ins/1.13.2/i18n/es-ES.json";

        // --- MARCAS DATATABLE ---
        var tableBrands = $('#table-brands').DataTable({
            language: { url: dtLangUrl },
            dom: '<"row"<"#ctrlCustomBrands.col-6"><"col-6"f>>ti',
            responsive: true,
            scrollX: false,
            scrollY: 'calc(85vh - 265px)',
            ajax: { url: ajaxUrl, type: 'POST', dataSrc: 'data', data: { part: 'BR' } },
            columns: [
                { title: '#', data: 'id' },
                { title: 'Nombre', data: 'nombre' },
                {
                    title: '', data: 'id', orderable: false, className: 'text-end',
                    render: function (data, type, row) {
                        return '<button class="btn btn-sm btn-primary btn-edit-brand" data-id="' + data + '" data-nombre="' + row.nombre + '"><i class="far fa-edit"></i></button> ' +
                            '<button class="btn btn-sm btn-danger btn-del-brand" data-id="' + data + '"><i class="far fa-trash-alt"></i></button>';
                    }
                }
            ],
            drawCallback: function () {
                var html = '<button id="btn-new-brand" type="button" class="btn btn-sm btn-success me-1">＋ Nueva Marca</button>';
                $('#ctrlCustomBrands').html(html);
            }
        });

        // --- MODELOS DATATABLE ---
        var tableModels = $('#table-models').DataTable({
            language: { url: dtLangUrl },
            dom: '<"row"<"#ctrlCustomModels.col-6"><"col-6"f>>ti',
            responsive: true,
            scrollX: false,
            scrollY: 'calc(85vh - 265px)',
            ajax: { url: ajaxUrl, type: 'POST', dataSrc: 'data', data: { part: 'MD' } },
            columns: [
                { title: '#', data: 'id' },
                { title: 'Marca', data: 'marca' },
                { title: 'Modelo', data: 'modelo' },
                { title: 'Pantalla', data: 'pantalla' },
                {
                    title: 'PDF', data: 'pdf_ruta', orderable: false,
                    render: function (data) {
                        if (data) return '<a class="badge bg-secondary text-decoration-none" href="/' + data + '" target="_blank">📄 PDF</a>';
                        return '<span class="text-muted">—</span>';
                    }
                },
                {
                    title: '', data: 'id', orderable: false, className: 'text-end',
                    render: function (data, type, row) {
                        return '<button class="btn btn-sm btn-primary btn-edit-model" ' +
                            'data-id="' + data + '" data-brand="' + row.brand_id + '" data-modelo="' + row.modelo + '" data-pantalla="' + row.pantalla + '" data-pdf="' + (row.pdf_ruta || '') + '"><i class="far fa-edit"></i></button> ' +
                            '<button class="btn btn-sm btn-danger btn-del-model" data-id="' + data + '"><i class="far fa-trash-alt"></i></button>';
                    }
                }
            ],
            drawCallback: function () {
                var html = '<button id="btn-new-model" type="button" class="btn btn-sm btn-success me-1">＋ Nuevo Modelo</button>';
                $('#ctrlCustomModels').html(html);
            }
        });

        // --- PARTES DATATABLE ---
        var tableParts = $('#table-parts').DataTable({
            language: { url: dtLangUrl },
            dom: '<"row"<"#ctrlCustomParts.col-6"><"col-6"f>>ti',
            responsive: true,
            scrollX: false,
            scrollY: 'calc(85vh - 265px)',
            ajax: { url: ajaxUrl, type: 'POST', dataSrc: 'data', data: { part: 'PT' } },
            columns: [
                { title: '#', data: 'id' },
                { title: 'Marca', data: 'marca' },
                {
                    title: 'Nombre', data: 'nombre',
                    render: function (data, type, row) {
                        var html = data;
                        if (row.descripcion) html += '<small class="d-block text-muted">' + row.descripcion + '</small>';
                        return html;
                    }
                },
                {
                    title: 'Precio ₡', data: 'precio_crc',
                    render: function (data) {
                        return '₡' + parseFloat(data).toLocaleString('es-CR');
                    }
                },
                { title: 'Stock', data: 'stock' },
                {
                    title: '', data: 'id', orderable: false, className: 'text-end',
                    render: function (data, type, row) {
                        return '<button class="btn btn-sm btn-primary btn-edit-part" ' +
                            'data-id="' + data + '" data-brand="' + row.brand_id + '" data-nombre="' + row.nombre + '" data-desc="' + (row.descripcion || '') + '" data-precio="' + row.precio_crc + '" data-stock="' + row.stock + '"><i class="far fa-edit"></i></button> ' +
                            '<button class="btn btn-sm btn-danger btn-del-part" data-id="' + data + '"><i class="far fa-trash-alt"></i></button>';
                    }
                }
            ],
            drawCallback: function () {
                var html = '<button id="btn-new-part" type="button" class="btn btn-sm btn-success me-1">＋ Nueva Parte</button>';
                $('#ctrlCustomParts').html(html);
            }
        });

        // ---- MARCAS ----
        var modalBrand;
        if (document.getElementById('modal-brand')) {
            modalBrand = new bootstrap.Modal(document.getElementById('modal-brand'));
        }
        $(document).on('click', '#btn-new-brand', function () {
            document.getElementById('mb-id').value = 0;
            document.getElementById('mb-nombre').value = '';
            document.getElementById('modal-brand-title').textContent = 'Nueva Marca';
            if (modalBrand) modalBrand.show();
        });

        $(document).on('click', '.btn-edit-brand', function () {
            document.getElementById('mb-id').value = this.dataset.id;
            document.getElementById('mb-nombre').value = this.dataset.nombre;
            document.getElementById('modal-brand-title').textContent = 'Editar Marca';
            if (modalBrand) modalBrand.show();
        });

        document.getElementById('mb-save').addEventListener('click', function () {
            var nombre = document.getElementById('mb-nombre').value.trim();
            if (!nombre) { toast('Ingrese el nombre de la marca.', 'error'); return; }
            ajax(ajaxUrl, {
                action: 'U', part: 'BR',
                brandId: document.getElementById('mb-id').value,
                brandNombre: nombre
            }, function (err, res) {
                if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                toast('Marca guardada.');
                if (modalBrand) modalBrand.hide();
                tableBrands.ajax.reload(null, false);
            });
        });

        $(document).on('click', '.btn-del-brand', function () {
            var btn = this;
            alertNotify({
                type: 'warning',
                text: '¿Eliminar esta marca?',
                icon: 'fas fa-exclamation',
                buttons: [
                    {
                        type: 'warning',
                        text: 'Sí',
                        icon: 'fas fa-check',
                        callback: function () {
                            ajax(ajaxUrl, { action: 'D', part: 'BR', brandId: btn.dataset.id }, function (err, res) {
                                if (err || !res.result) { toast(err || res.error || 'Error. Puede tener modelos asociados.', 'error'); return; }
                                toast('Marca eliminada.');
                                tableBrands.ajax.reload(null, false);
                            });
                        }
                    },
                    {
                        type: 'danger',
                        text: 'No',
                        icon: 'fas fa-times'
                    }
                ]
            });
        });

        // ---- MODELOS ----
        var modalModel;
        if (document.getElementById('modal-model')) {
            modalModel = new bootstrap.Modal(document.getElementById('modal-model'));
        }
        var pond = null;
        var pondElement = document.getElementById('mm-pdf-file');
        if (pondElement) {
            pond = FilePond.create(pondElement, {
                storeAsFile: true,
                labelIdle: 'Arrastre su PDF aquí o <span class="filepond--label-action">Examine</span>'
            });
        }

        $(document).on('click', '#btn-new-model', function () {
            document.getElementById('mm-id').value = 0;
            document.getElementById('mm-brand').value = '';
            document.getElementById('mm-modelo').value = '';
            document.getElementById('mm-pantalla').value = '';
            if (pond) pond.removeFiles();
            document.getElementById('mm-pdf-current').style.display = 'none';
            document.getElementById('modal-model-title').textContent = 'Nuevo Modelo';
            if (modalModel) modalModel.show();
        });

        $(document).on('click', '.btn-edit-model', function () {
            document.getElementById('mm-id').value = this.dataset.id;
            document.getElementById('mm-brand').value = this.dataset.brand;
            document.getElementById('mm-modelo').value = this.dataset.modelo;
            document.getElementById('mm-pantalla').value = this.dataset.pantalla;
            if (pond) pond.removeFiles();
            var pdfCur = document.getElementById('mm-pdf-current');
            if (this.dataset.pdf) {
                document.getElementById('mm-pdf-link').href = '/' + this.dataset.pdf;
                pdfCur.style.display = '';
            } else {
                pdfCur.style.display = 'none';
            }
            document.getElementById('modal-model-title').textContent = 'Editar Modelo';
            if (modalModel) modalModel.show();
        });

        document.getElementById('mm-save').addEventListener('click', function () {
            var brandId = document.getElementById('mm-brand').value;
            var modelo = document.getElementById('mm-modelo').value.trim();
            if (!brandId || !modelo) { toast('Marca y modelo son requeridos.', 'error'); return; }

            var fd = new FormData();
            fd.append('action', 'U');
            fd.append('part', 'MD');
            fd.append('modelId', document.getElementById('mm-id').value);
            fd.append('brandId', brandId);
            fd.append('modelNombre', modelo);
            fd.append('pantalla', document.getElementById('mm-pantalla').value.trim());
            if (pond) {
                var pdfFiles = pond.getFiles();
                if (pdfFiles.length > 0) {
                    fd.append('pdf_archivo', pdfFiles[0].file);
                }
            }

            ajax(ajaxUrl, fd, function (err, res) {
                if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                toast('Modelo guardado.');
                if (modalModel) modalModel.hide();
                tableModels.ajax.reload(null, false);
            }, true);
        });

        $(document).on('click', '.btn-del-model', function () {
            var btn = this;
            alertNotify({
                type: 'warning',
                text: '¿Eliminar este modelo?',
                icon: 'fas fa-exclamation',
                buttons: [
                    {
                        type: 'warning',
                        text: 'Sí',
                        icon: 'fas fa-check',
                        callback: function () {
                            ajax(ajaxUrl, { action: 'D', part: 'MD', modelId: btn.dataset.id }, function (err, res) {
                                if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                                toast('Modelo eliminado.');
                                tableModels.ajax.reload(null, false);
                            });
                        }
                    },
                    {
                        type: 'danger',
                        text: 'No',
                        icon: 'fas fa-times'
                    }
                ]
            });
        });

        // ---- PARTES ----
        var modalPart;
        if (document.getElementById('modal-part')) {
            modalPart = new bootstrap.Modal(document.getElementById('modal-part'));
        }
        $(document).on('click', '#btn-new-part', function () {
            document.getElementById('mp-id').value = 0;
            document.getElementById('mp-brand').value = '';
            document.getElementById('mp-nombre').value = '';
            document.getElementById('mp-desc').value = '';
            document.getElementById('mp-precio').value = 0;
            document.getElementById('mp-stock').value = 0;
            document.getElementById('modal-part-title').textContent = 'Nueva Parte';
            if (modalPart) modalPart.show();
        });

        $(document).on('click', '.btn-edit-part', function () {
            document.getElementById('mp-id').value = this.dataset.id;
            document.getElementById('mp-brand').value = this.dataset.brand;
            document.getElementById('mp-nombre').value = this.dataset.nombre;
            document.getElementById('mp-desc').value = this.dataset.desc;
            document.getElementById('mp-precio').value = this.dataset.precio;
            document.getElementById('mp-stock').value = this.dataset.stock;
            document.getElementById('modal-part-title').textContent = 'Editar Parte';
            if (modalPart) modalPart.show();
        });

        document.getElementById('mp-save').addEventListener('click', function () {
            var nombre = document.getElementById('mp-nombre').value.trim();
            if (!nombre) { toast('El nombre de la parte es requerido.', 'error'); return; }
            ajax(ajaxUrl, {
                action: 'U',
                part: 'PT',
                partId: document.getElementById('mp-id').value,
                brandId: document.getElementById('mp-brand').value,
                partNombre: nombre,
                partDesc: document.getElementById('mp-desc').value.trim(),
                precioCrc: document.getElementById('mp-precio').value,
                stock: document.getElementById('mp-stock').value
            }, function (err, res) {
                if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                toast('Parte guardada.');
                if (modalPart) modalPart.hide();
                tableParts.ajax.reload(null, false);
            });
        });

        $(document).on('click', '.btn-del-part', function () {
            var btn = this;
            alertNotify({
                type: 'warning',
                text: '¿Eliminar esta parte?',
                icon: 'fas fa-exclamation',
                buttons: [
                    {
                        type: 'warning',
                        text: 'Sí',
                        icon: 'fas fa-check',
                        callback: function () {
                            ajax(ajaxUrl, { action: 'D', part: 'PT', partId: btn.dataset.id }, function (err, res) {
                                if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                                toast('Parte eliminada.');
                                tableParts.ajax.reload(null, false);
                            });
                        }
                    },
                    {
                        type: 'danger',
                        text: 'No',
                        icon: 'fas fa-times'
                    }
                ]
            });
        });
    }

    // Exponer API pública
    return {
        initList: initList,
        initEdit: initEdit,
        initCatalogs: initCatalogs
    };

})();
