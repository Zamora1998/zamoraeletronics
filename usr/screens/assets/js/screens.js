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

var ScreensApp = (function () {

    // =========================================================
    // UTILIDADES COMUNES
    // =========================================================

    function toast(msg, type) {
        type = type || 'success';
        var el = document.getElementById('toast');
        var ico = document.getElementById('toast-icon');
        var txt = document.getElementById('toast-msg');
        if (!el) return;
        el.className = 'toast show ' + type;
        ico.textContent = type === 'success' ? '✔' : '✖';
        txt.textContent = msg;
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.classList.remove('show'); }, 3500);
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
                    clienteIdEl.dataset.id = res.clientId;
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
                    tr.dataset.id = res.orderPartId;
                    tr.innerHTML = '<td>' + partNombre + '</td>' +
                        '<td>' + cantidad + '</td>' +
                        '<td>' + formatCRC(precioUnit) + '</td>' +
                        '<td>' + formatCRC(subtotal) + '</td>' +
                        '<td><button class="btn btn-danger btn-sm btn-del-part" data-id="' + res.orderPartId + '">✕</button></td>';
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
                        orderId = res.orderId;
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
    }

    // =========================================================
    // MÓDULO: CATÁLOGOS
    // =========================================================

    function initCatalogs(opts) {
        var ajaxUrl = opts.ajaxUrl || '/screens/catalogs';

        // --- TABS ---
        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
                document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
                tab.classList.add('active');
                var panel = document.getElementById('panel-' + tab.dataset.tab);
                if (panel) panel.classList.add('active');
            });
        });

        // ---- MARCAS ----
        var modalBrand = document.getElementById('modal-brand');
        document.getElementById('btn-new-brand').addEventListener('click', function () {
            document.getElementById('mb-id').value = 0;
            document.getElementById('mb-nombre').value = '';
            document.getElementById('modal-brand-title').textContent = 'Nueva Marca';
            modalBrand.classList.add('open');
        });
        document.getElementById('mb-cancel').addEventListener('click', function () { modalBrand.classList.remove('open'); });

        document.querySelectorAll('.btn-edit-brand').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('mb-id').value = btn.dataset.id;
                document.getElementById('mb-nombre').value = btn.dataset.nombre;
                document.getElementById('modal-brand-title').textContent = 'Editar Marca';
                modalBrand.classList.add('open');
            });
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
                toast('Marca guardada. Recargando...');
                setTimeout(function () { location.reload(); }, 800);
            });
        });

        document.querySelectorAll('.btn-del-brand').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('¿Eliminar esta marca?')) return;
                ajax(ajaxUrl, { action: 'D', part: 'BR', brandId: btn.dataset.id }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error. Puede tener modelos asociados.', 'error'); return; }
                    btn.closest('tr').remove();
                    toast('Marca eliminada.');
                });
            });
        });

        // ---- MODELOS ----
        var modalModel = document.getElementById('modal-model');
        document.getElementById('btn-new-model').addEventListener('click', function () {
            document.getElementById('mm-id').value = 0;
            document.getElementById('mm-brand').value = '';
            document.getElementById('mm-modelo').value = '';
            document.getElementById('mm-pantalla').value = '';
            document.getElementById('mm-pdf-file').value = '';
            document.getElementById('mm-pdf-current').style.display = 'none';
            document.getElementById('modal-model-title').textContent = 'Nuevo Modelo';
            modalModel.classList.add('open');
        });
        document.getElementById('mm-cancel').addEventListener('click', function () { modalModel.classList.remove('open'); });

        document.querySelectorAll('.btn-edit-model').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('mm-id').value = btn.dataset.id;
                document.getElementById('mm-brand').value = btn.dataset.brand;
                document.getElementById('mm-modelo').value = btn.dataset.modelo;
                document.getElementById('mm-pantalla').value = btn.dataset.pantalla;
                document.getElementById('mm-pdf-file').value = '';
                var pdfCur = document.getElementById('mm-pdf-current');
                if (btn.dataset.pdf) {
                    document.getElementById('mm-pdf-link').href = '/' + btn.dataset.pdf;
                    pdfCur.style.display = '';
                } else {
                    pdfCur.style.display = 'none';
                }
                document.getElementById('modal-model-title').textContent = 'Editar Modelo';
                modalModel.classList.add('open');
            });
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
            var pdfFile = document.getElementById('mm-pdf-file').files[0];
            if (pdfFile) fd.append('pdf_archivo', pdfFile);

            ajax(ajaxUrl, fd, function (err, res) {
                if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                toast('Modelo guardado. Recargando...');
                setTimeout(function () { location.reload(); }, 800);
            }, true);
        });

        document.querySelectorAll('.btn-del-model').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('¿Eliminar este modelo?')) return;
                ajax(ajaxUrl, { action: 'D', part: 'MD', modelId: btn.dataset.id }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                    btn.closest('tr').remove();
                    toast('Modelo eliminado.');
                });
            });
        });

        // ---- PARTES ----
        var modalPart = document.getElementById('modal-part');
        document.getElementById('btn-new-part').addEventListener('click', function () {
            document.getElementById('mp-id').value = 0;
            document.getElementById('mp-brand').value = '';
            document.getElementById('mp-nombre').value = '';
            document.getElementById('mp-desc').value = '';
            document.getElementById('mp-precio').value = 0;
            document.getElementById('mp-stock').value = 0;
            document.getElementById('modal-part-title').textContent = 'Nueva Parte';
            modalPart.classList.add('open');
        });
        document.getElementById('mp-cancel').addEventListener('click', function () { modalPart.classList.remove('open'); });

        document.querySelectorAll('.btn-edit-part').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('mp-id').value = btn.dataset.id;
                document.getElementById('mp-brand').value = btn.dataset.brand;
                document.getElementById('mp-nombre').value = btn.dataset.nombre;
                document.getElementById('mp-desc').value = btn.dataset.desc;
                document.getElementById('mp-precio').value = btn.dataset.precio;
                document.getElementById('mp-stock').value = btn.dataset.stock;
                document.getElementById('modal-part-title').textContent = 'Editar Parte';
                modalPart.classList.add('open');
            });
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
                toast('Parte guardada. Recargando...');
                setTimeout(function () { location.reload(); }, 800);
            });
        });

        document.querySelectorAll('.btn-del-part').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('¿Eliminar esta parte?')) return;
                ajax(ajaxUrl, { action: 'D', part: 'PT', partId: btn.dataset.id }, function (err, res) {
                    if (err || !res.result) { toast(err || res.error || 'Error.', 'error'); return; }
                    btn.closest('tr').remove();
                    toast('Parte eliminada.');
                });
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
