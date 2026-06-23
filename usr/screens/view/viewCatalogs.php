<?php

/**
 * viewCatalogs.php
 * Vista HTML del módulo de catálogos: Marcas, Modelos y Partes.
 * Se incluye desde ctrlScreenCatalogs.php cuando la petición es GET sin part.
 */

require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/usr/screens/model/modScreens.php';

$objS   = new tvScreens($_MYSQLI_);
$brands = $objS->selectBrands()['data'];
$models = $objS->selectModels()['data'];
$parts  = $objS->selectParts()['data'];

?>
<link rel="stylesheet" href="<?= autoVer('/usr/screens/assets/js/css/screens.css') ?>">

<!-- Sub-header de módulo -->

<div class="cat-header">
    <h2>⚙ Catálogos</h2>
    <a href="/<?= $chrLocale ?>/screens" class="btn btn-ghost">← Volver a Órdenes</a>
</div>

<div class="tabs">
    <div class="tab active" data-tab="brands">🏷 Marcas</div>
    <div class="tab" data-tab="models">📺 Modelos</div>
    <div class="tab" data-tab="parts">🔩 Partes / Repuestos</div>
</div>

<div class="cat-body">

    <!-- ==================== MARCAS ==================== -->
    <div class="tab-panel active" id="panel-brands">
        <div class="panel-top">
            <h3>Marcas de TV</h3>
            <button class="btn btn-primary btn-sm" id="btn-new-brand">＋ Nueva Marca</button>
        </div>
        <table class="cat-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbody-brands">
                <?php foreach ($brands as $b): ?>
                    <tr data-id="<?= $b['id'] ?>">
                        <td><?= $b['id'] ?></td>
                        <td><?= htmlspecialchars($b['nombre']) ?></td>
                        <td style="text-align:right; white-space:nowrap">
                            <button class="btn btn-ghost btn-sm btn-edit-brand"
                                data-id="<?= $b['id'] ?>" data-nombre="<?= htmlspecialchars($b['nombre']) ?>">✏</button>
                            <button class="btn btn-danger btn-sm btn-del-brand" data-id="<?= $b['id'] ?>">🗑</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ==================== MODELOS ==================== -->
    <div class="tab-panel" id="panel-models">
        <div class="panel-top">
            <h3>Modelos de TV</h3>
            <button class="btn btn-primary btn-sm" id="btn-new-model">＋ Nuevo Modelo</button>
        </div>
        <table class="cat-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Pantalla</th>
                    <th>PDF</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbody-models">
                <?php foreach ($models as $m): ?>
                    <tr data-id="<?= $m['id'] ?>">
                        <td><?= $m['id'] ?></td>
                        <td><?= htmlspecialchars($m['marca']) ?></td>
                        <td><?= htmlspecialchars($m['modelo']) ?></td>
                        <td><?= htmlspecialchars($m['pantalla']) ?></td>
                        <td>
                            <?php if ($m['pdf_ruta']): ?>
                                <a class="pdf-chip" href="/<?= htmlspecialchars($m['pdf_ruta']) ?>" target="_blank">📄 PDF</a>
                            <?php else: ?>
                                <span style="color:var(--rz-muted,#9ca3af);font-size:.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap">
                            <button class="btn btn-ghost btn-sm btn-edit-model"
                                data-id="<?= $m['id'] ?>"
                                data-brand="<?= $m['brand_id'] ?>"
                                data-modelo="<?= htmlspecialchars($m['modelo']) ?>"
                                data-pantalla="<?= htmlspecialchars($m['pantalla']) ?>"
                                data-pdf="<?= htmlspecialchars($m['pdf_ruta']) ?>">✏</button>
                            <button class="btn btn-danger btn-sm btn-del-model" data-id="<?= $m['id'] ?>">🗑</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ==================== PARTES ==================== -->
    <div class="tab-panel" id="panel-parts">
        <div class="panel-top">
            <h3>Partes / Repuestos</h3>
            <button class="btn btn-primary btn-sm" id="btn-new-part">＋ Nueva Parte</button>
        </div>
        <table class="cat-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Marca</th>
                    <th>Nombre</th>
                    <th>Precio ₡</th>
                    <th>Stock</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbody-parts">
                <?php foreach ($parts as $p): ?>
                    <tr data-id="<?= $p['id'] ?>">
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['marca']) ?></td>
                        <td>
                            <?= htmlspecialchars($p['nombre']) ?>
                            <?php if ($p['descripcion']): ?>
                                <small style="display:block;color:var(--rz-muted,#9ca3af)"><?= htmlspecialchars($p['descripcion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>₡<?= number_format($p['precio_crc'], 0, ',', '.') ?></td>
                        <td><?= $p['stock'] ?></td>
                        <td style="text-align:right; white-space:nowrap">
                            <button class="btn btn-ghost btn-sm btn-edit-part"
                                data-id="<?= $p['id'] ?>"
                                data-brand="<?= $p['brand_id'] ?>"
                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                data-desc="<?= htmlspecialchars($p['descripcion']) ?>"
                                data-precio="<?= $p['precio_crc'] ?>"
                                data-stock="<?= $p['stock'] ?>">✏</button>
                            <button class="btn btn-danger btn-sm btn-del-part" data-id="<?= $p['id'] ?>">🗑</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div><!-- /cat-body -->

<!-- ====== MODALES ====== -->

<!-- Modal Marca -->
<div class="modal-overlay" id="modal-brand">
    <div class="modal">
        <h3 id="modal-brand-title">Nueva Marca</h3>
        <input type="hidden" id="mb-id" value="0">
        <div class="form-group">
            <label>Nombre de la marca *</label>
            <input type="text" id="mb-nombre" placeholder="Ej: Samsung, LG...">
        </div>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="mb-cancel">Cancelar</button>
            <button class="btn btn-success" id="mb-save">💾 Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Modelo -->
<div class="modal-overlay" id="modal-model">
    <div class="modal">
        <h3 id="modal-model-title">Nuevo Modelo</h3>
        <input type="hidden" id="mm-id" value="0">
        <div class="form-group">
            <label>Marca *</label>
            <select id="mm-brand">
                <option value="">— Seleccionar —</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Modelo *</label>
            <input type="text" id="mm-modelo" placeholder="Ej: UN55TU8000">
        </div>
        <div class="form-group">
            <label>Pantalla</label>
            <input type="text" id="mm-pantalla" placeholder="Ej: 55 pulgadas, OLED 4K">
        </div>
        <div class="form-group">
            <label>PDF del diagrama / tarjeta</label>
            <input type="file" id="mm-pdf-file" accept=".pdf">
            <div id="mm-pdf-current" style="margin-top:.4rem;display:none">
                <span style="font-size:.8rem;color:var(--rz-muted,#9ca3af)">PDF actual:</span>
                <a id="mm-pdf-link" href="#" target="_blank" class="pdf-chip" style="margin-left:.3rem">📄 Ver PDF</a>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="mm-cancel">Cancelar</button>
            <button class="btn btn-success" id="mm-save">💾 Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Parte -->
<div class="modal-overlay" id="modal-part">
    <div class="modal">
        <h3 id="modal-part-title">Nueva Parte</h3>
        <input type="hidden" id="mp-id" value="0">
        <div class="form-group">
            <label>Marca compatible</label>
            <select id="mp-brand">
                <option value="">Genérico</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nombre de la parte *</label>
            <input type="text" id="mp-nombre" placeholder="Ej: Tarjeta T-CON, Backlight, Fuente...">
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea id="mp-desc" rows="2" placeholder="Detalles adicionales..."></textarea>
        </div>
        <div class="form-group">
            <label>Precio en colones (₡)</label>
            <input type="number" id="mp-precio" min="0" step="500" value="0">
        </div>
        <div class="form-group">
            <label>Stock</label>
            <input type="number" id="mp-stock" min="0" value="0">
        </div>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="mp-cancel">Cancelar</button>
            <button class="btn btn-success" id="mp-save">💾 Guardar</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="sc-toast" id="toast">
    <span id="toast-icon">✔</span>
    <span id="toast-msg"></span>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>

<script src="<?= autoVer('/usr/screens/assets/js/screens.js') ?>"></script>
<script>
    ScreensApp.initCatalogs({
        ajaxUrl: CTRLSCREENS
    });
</script>