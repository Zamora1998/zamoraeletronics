<?php

/**
 * ScreenEdit.php
 * Formulario de creación/edición de orden de trabajo con:
 *  - Datos del cliente (nuevo o existente)
 *  - Datos del TV (marca/modelo del catálogo o manual)
 *  - Falla, costo en colones, abono inicial
 *  - Estado y tipo de pago
 *  - Firma digital con Signature Pad (para revisión/entrega)
 *  - Link a Google Maps con la ubicación del cliente
 */

require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/usr/screens/model/modScreens.php';

$objS   = new tvScreens($_MYSQLI_);
$brands = $objS->selectBrands()['data'];
$parts  = $objS->selectParts()['data'];

// Cargar orden existente si viene orderId
$order = [];
$client = [];
$orderParts = [];
$orderId = isset($_REQUEST['orderId']) ? (int)$_REQUEST['orderId'] : (isset($orderId) ? (int)$orderId : 0);

if ($orderId) {
    $objS->setOrderId($orderId);
    $orderRes = $objS->selectOrder();
    $order    = $orderRes['data'];
    $orderParts = $order['partes'] ?? [];
}

// Cargar clientes para el select
$clients = $objS->selectClients()['data'];

// Modelos del catálogo (para JS)
$allModels = $objS->selectModels()['data'];

$isNew = !$orderId;
$pageTitle = $isNew ? 'Nueva Orden de Trabajo' : 'Orden #' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
?>
<link rel="stylesheet" href="<?= autoVer('/usr/screens/assets/js/css/screens.css') ?>">
<!-- Signature Pad desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<!-- Sub-header de módulo -->

<div class="edit-header">
    <h2><?= $isNew ? '➕ Nueva Orden' : '✏ Editando Orden #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></h2>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <?php if (!$isNew): ?>
        <button class="btn-print-pdf" id="btn-print-pdf" title="Generar PDF / Imprimir orden">
            🖨 Generar PDF
        </button>
        <?php endif; ?>
        <a href="/<?= $chrLocale ?>/screens" class="btn btn-ghost">← Volver</a>
        <button class="btn btn-success" id="btn-guardar-orden">💾 Guardar Orden</button>
    </div>
</div>

<div class="edit-body">

    <!-- Encabezado solo visible al imprimir -->
    <div class="print-header">
        <div class="print-logo">📺 RZamora Electronics</div>
        <div class="print-meta">
            <div><strong>Orden de Trabajo #<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></strong></div>
            <div>Fecha: <?= date('d/m/Y H:i') ?></div>
        </div>
    </div>

    <!-- ═══ FILA 1: Cliente | TV | Falla y Costos ═══ -->
    <div class="edit-row">

        <!-- CLIENTE -->
        <div class="section-card">
            <div class="section-title">👤 Datos del Cliente</div>
            <div class="section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sel-cliente">Cliente existente</label>
                        <select id="sel-cliente">
                            <option value="">— Nuevo cliente —</option>
                            <?php foreach ($clients as $cl): ?>
                                <option value="<?= $cl['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($cl['nombre']) ?>"
                                    data-telefono="<?= htmlspecialchars($cl['telefono']) ?>"
                                    data-ubicacion="<?= htmlspecialchars($cl['ubicacion'] ?? '') ?>"
                                    data-lat="<?= $cl['latitud'] ?? '' ?>"
                                    data-lng="<?= $cl['longitud'] ?? '' ?>"
                                    <?= ($order['client_id'] ?? 0) == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['nombre']) ?> — <?= htmlspecialchars($cl['telefono']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="inp-nombre">Nombre del cliente *</label>
                        <input type="text" id="inp-nombre" value="<?= htmlspecialchars($order['cliente_nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="inp-telefono">Teléfono de contacto *</label>
                        <input type="text" id="inp-telefono" value="<?= htmlspecialchars($order['cliente_telefono'] ?? '') ?>" placeholder="8888-8888">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="grid-column:1/-1">
                        <label for="inp-ubicacion">Ubicación / Dirección</label>
                        <input type="text" id="inp-ubicacion" placeholder="Ej: San José, Barrio Los Yoses, 100m norte de..."
                            value="<?= htmlspecialchars($order['cliente_ubicacion'] ?? '') ?>">
                        <div style="display:flex;gap:.5rem;margin-top:.4rem;align-items:center;flex-wrap:wrap;">
                            <input type="number" id="inp-latitud" step="any" placeholder="Latitud"
                                value="<?= $order['latitud'] ?? '' ?>" style="width:140px;font-size:.8rem;">
                            <input type="number" id="inp-longitud" step="any" placeholder="Longitud"
                                value="<?= $order['longitud'] ?? '' ?>" style="width:140px;font-size:.8rem;">
                            <a id="btn-maps" class="maps-link" href="#" target="_blank"
                                style="<?= ($order['latitud'] ?? '') ? '' : 'display:none' ?>">
                                🗺 Ver en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:.75rem;">
                    <button class="btn btn-primary btn-sm" id="btn-guardar-cliente">💾 Guardar cliente</button>
                    <span id="cliente-id-hidden" style="display:none" data-id="<?= $order['client_id'] ?? 0 ?>"></span>
                </div>
            </div>
        </div>

        <!-- TV -->
        <div class="section-card">
            <div class="section-title">📺 Televisor</div>
            <div class="section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sel-marca">Marca</label>
                        <select id="sel-marca">
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>"
                                    <?= ($order['brand_id'] ?? 0) == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sel-modelo">Modelo (catálogo)</label>
                        <select id="sel-modelo">
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($allModels as $m): ?>
                                <option value="<?= $m['id'] ?>"
                                    data-brand="<?= $m['brand_id'] ?>"
                                    data-pantalla="<?= htmlspecialchars($m['pantalla']) ?>"
                                    data-pdf="<?= htmlspecialchars($m['pdf_ruta']) ?>"
                                    <?= ($order['model_id'] ?? 0) == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['modelo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="inp-modelo-libre">Modelo (manual si no está en catálogo)</label>
                        <input type="text" id="inp-modelo-libre" placeholder="Ej: UN55TU8000"
                            value="<?= htmlspecialchars($order['modelo_libre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="inp-pantalla-libre">Tamaño / Tipo de pantalla</label>
                        <input type="text" id="inp-pantalla-libre" placeholder="Ej: 55 pulgadas, OLED"
                            value="<?= htmlspecialchars($order['pantalla_libre'] ?? '') ?>">
                    </div>
                </div>
                <div id="pdf-link-wrap" style="<?= ($order['pdf_ruta'] ?? '') ? '' : 'display:none' ?>">
                    <a id="pdf-link" href="/<?= htmlspecialchars($order['pdf_ruta'] ?? '') ?>" target="_blank"
                        class="maps-link">📄 Ver PDF de la tarjeta</a>
                </div>
            </div>
        </div>

        <!-- FALLA Y COSTOS -->
        <div class="section-card">
            <div class="section-title">🔧 Falla y Costos</div>
            <div class="section-body">
                <div class="form-group">
                    <label for="inp-falla">Falla reportada *</label>
                    <textarea id="inp-falla" rows="3" placeholder="Describa el problema del televisor..."><?= htmlspecialchars($order['falla_reportada'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="inp-costo">Costo estimado (₡ Colones)</label>
                        <input type="number" id="inp-costo" min="0" step="500"
                            value="<?= $order['costo_estimado'] ?? 0 ?>" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="inp-abono">Abono inicial (₡ Colones)</label>
                        <input type="number" id="inp-abono" min="0" step="500"
                            value="<?= $order['abono_inicial'] ?? 0 ?>" placeholder="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inp-notas">Notas adicionales</label>
                    <textarea id="inp-notas" rows="2" placeholder="Observaciones, accesorios entregados..."><?= htmlspecialchars($order['notas'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

    </div><!-- /edit-row fila 1 -->

    <!-- ═══ FILA 2: Estado y Pago (ancho completo) ═══ -->
    <div class="edit-row">
        <div class="section-card">
            <div class="section-title">📋 Estado y Pago</div>
            <div class="section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Estado de la orden</label>
                        <div class="status-pills" id="pills-estado">
                            <?php
                            $estados = ['pendiente' => '⏳ Pendiente', 'en_reparacion' => '🔧 En Reparación', 'listo' => '✅ Listo', 'entregado' => '📦 Entregado'];
                            $curEstado = $order['estado'] ?? 'pendiente';
                            foreach ($estados as $k => $lbl):
                            ?>
                                <button type="button" class="pill <?= $k == $curEstado ? 'active-' . $k : '' ?>" data-estado="<?= $k ?>">
                                    <?= $lbl ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="inp-estado" value="<?= $curEstado ?>">
                    </div>
                    <div class="form-group">
                        <label for="sel-pago">Tipo de pago</label>
                        <select id="sel-pago">
                            <?php
                            $pagos = ['pendiente' => '⏸ Pago Pendiente', 'sinpe' => '📱 SINPE', 'efectivo' => '💵 Efectivo', 'mixto' => '🔀 Mixto'];
                            $curPago = $order['tipo_pago'] ?? 'pendiente';
                            foreach ($pagos as $k => $lbl):
                            ?>
                                <option value="<?= $k ?>" <?= $k == $curPago ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /edit-row fila 2 -->

    <!-- ═══ FILA 3: Partes Utilizadas (ancho completo) ═══ -->
    <div class="edit-row" id="partes-section" style="<?= $isNew ? 'display:none' : '' ?>">
        <div class="section-card">
            <div class="section-title">🔩 Partes Utilizadas</div>
            <div class="section-body">
                <div class="add-part-row">
                    <div class="form-group" style="flex:2;">
                        <label for="sel-parte">Repuesto</label>
                        <select id="sel-parte">
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($parts as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    data-precio="<?= $p['precio_crc'] ?>"><?= htmlspecialchars($p['nombre']) ?> — ₡<?= number_format($p['precio_crc'], 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="width:90px;">
                        <label for="inp-cantidad">Cant.</label>
                        <input type="number" id="inp-cantidad" value="1" min="1">
                    </div>
                    <div class="form-group" style="width:130px;">
                        <label for="inp-precio-unit">Precio unit. ₡</label>
                        <input type="number" id="inp-precio-unit" value="0" min="0" step="100">
                    </div>
                    <button class="btn btn-accent btn-sm" id="btn-add-part" style="margin-bottom:2px;">＋ Agregar</button>
                </div>

                <table class="parts-table" id="parts-table">
                    <thead>
                        <tr>
                            <th>Repuesto</th>
                            <th>Cant.</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="parts-tbody">
                        <?php foreach ($orderParts as $op): ?>
                            <tr data-id="<?= $op['id'] ?>">
                                <td><?= htmlspecialchars($op['parte_nombre']) ?></td>
                                <td><?= $op['cantidad'] ?></td>
                                <td>₡<?= number_format($op['precio_unit'], 0, ',', '.') ?></td>
                                <td>₡<?= number_format($op['subtotal'], 0, ',', '.') ?></td>
                                <td><button class="btn btn-danger btn-sm btn-del-part" data-id="<?= $op['id'] ?>">✕</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" style="text-align:right; color:var(--rz-muted,#9ca3af)">Total partes:</td>
                            <td id="parts-total">₡<?= number_format(array_sum(array_column($orderParts, 'subtotal')), 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div><!-- /edit-row fila 3 -->

    <!-- ═══ FILA 4: Firma del Cliente (ancho completo) ═══ -->
    <div class="edit-row" id="firma-section" style="<?= $isNew ? 'display:none' : '' ?>">
        <div class="section-card">
            <div class="section-title">✍ Firma del Cliente <small style="font-size:.75rem;color:var(--rz-muted,#9ca3af);font-weight:400;">(se solicita al revisar / entregar el equipo)</small></div>
            <div class="section-body">
                <?php if (!empty($order['firma_ruta'])): ?>
                    <div>
                        <span class="firma-saved">✔ Firma guardada</span>
                        <a href="/<?= htmlspecialchars($order['firma_ruta']) ?>" target="_blank"
                            class="maps-link" style="margin-left:.5rem;">Ver firma</a>
                        <p style="font-size:.78rem;color:var(--rz-muted,#9ca3af);margin-top:.5rem;">Puede capturar una nueva firma para sobrescribir la anterior.</p>
                    </div>
                <?php endif; ?>

                <div class="sig-wrap" id="sig-wrap">
                    <canvas id="sig-canvas"></canvas>
                </div>
                <div class="sig-actions">
                    <button class="btn btn-ghost btn-sm" id="btn-sig-clear">🗑 Limpiar</button>
                    <button class="btn btn-success btn-sm" id="btn-sig-save">💾 Guardar Firma</button>
                </div>
                <img id="sig-preview" class="sig-preview" alt="Vista previa de la firma">
            </div>
        </div>
    </div><!-- /edit-row fila 4 -->

</div><!-- /edit-body -->


<!-- Toast -->
<div class="toast" id="toast">
    <span id="toast-icon">✔</span>
    <span id="toast-msg"></span>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>

<!-- Datos PHP para JS -->
<script>
    var SC_ORDER_ID = <?= $orderId ?>;
    var SC_MODELS_DATA = <?= json_encode($allModels, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= autoVer('/usr/screens/assets/js/screens.js') ?>"></script>
<script>
    ScreensApp.initEdit();
</script>