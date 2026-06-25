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

?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />
<link href="/lib/filepond/filepond.min.css" rel="stylesheet" />

<link rel="stylesheet" href="<?= autoVer('/usr/screens/assets/js/css/screens.css') ?>">

<div class="cat-header pb-2 border-bottom mb-3 d-flex justify-content-between align-items-center">
    <h2 class="mb-0">⚙ Catálogos</h2>
    <a href="/<?= $chrLocale ?>/screens" class="btn btn-secondary btn-sm">← Volver a Órdenes</a>
</div>

<!-- Tabs nav -->
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="brands-tab" data-bs-toggle="tab" data-bs-target="#panel-brands" type="button" role="tab">🏷 Marcas</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="models-tab" data-bs-toggle="tab" data-bs-target="#panel-models" type="button" role="tab">📺 Modelos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="parts-tab" data-bs-toggle="tab" data-bs-target="#panel-parts" type="button" role="tab">🔩 Partes / Repuestos</button>
    </li>
</ul>

<div class="tab-content pt-3" id="myTabContent">

    <!-- ==================== MARCAS ==================== -->
    <div class="tab-pane fade show active" id="panel-brands" role="tabpanel">
        <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg bg-white">
            <h5 class="pb-1 border-bottom">Marcas de TV</h5>
            <table class="table table-striped nowrap w-100" id="table-brands"></table>
        </div>
    </div>

    <!-- ==================== MODELOS ==================== -->
    <div class="tab-pane fade" id="panel-models" role="tabpanel">
        <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg bg-white">
            <h5 class="pb-1 border-bottom">Modelos de TV</h5>
            <table class="table table-striped nowrap w-100" id="table-models"></table>
        </div>
    </div>

    <!-- ==================== PARTES ==================== -->
    <div class="tab-pane fade" id="panel-parts" role="tabpanel">
        <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg bg-white">
            <h5 class="pb-1 border-bottom">Partes / Repuestos</h5>
            <table class="table table-striped nowrap w-100" id="table-parts"></table>
        </div>
    </div>

</div><!-- /cat-body -->

<!-- ====== MODALES ====== -->

<!-- Modal Marca -->
<div class="modal fade" id="modal-brand" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-brand-title">Nueva Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mb-id" value="0">
                <div class="mb-3">
                    <label class="form-label">Nombre de la marca *</label>
                    <input type="text" class="form-control form-control-sm" id="mb-nombre" placeholder="Ej: Samsung, LG...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="mb-save">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modelo -->
<div class="modal fade" id="modal-model" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-model-title">Nuevo Modelo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mm-id" value="0">
                <div class="mb-3">
                    <label class="form-label">Marca *</label>
                    <select class="form-select form-select-sm" id="mm-brand">
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Modelo *</label>
                    <input type="text" class="form-control form-control-sm" id="mm-modelo" placeholder="Ej: UN55TU8000">
                </div>
                <div class="mb-3">
                    <label class="form-label">Pantalla</label>
                    <input type="text" class="form-control form-control-sm" id="mm-pantalla" placeholder="Ej: 55 pulgadas, OLED 4K">
                </div>
                <div class="mb-3">
                    <label class="form-label">PDF del diagrama / tarjeta</label>
                    <input type="file" class="filepond" id="mm-pdf-file" name="pdf_archivo" accept="application/pdf">
                    <div id="mm-pdf-current" class="mt-2" style="display:none">
                        <span class="text-muted small">PDF actual:</span>
                        <a id="mm-pdf-link" href="#" target="_blank" class="badge bg-secondary ms-1 text-decoration-none">📄 Ver PDF</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="mm-save">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Parte -->
<div class="modal fade" id="modal-part" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-part-title">Nueva Parte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mp-id" value="0">
                <div class="mb-3">
                    <label class="form-label">Marca compatible</label>
                    <select class="form-select form-select-sm" id="mp-brand">
                        <option value="">Genérico</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre de la parte *</label>
                    <input type="text" class="form-control form-control-sm" id="mp-nombre" placeholder="Ej: Tarjeta T-CON, Backlight, Fuente...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control form-control-sm" id="mp-desc" rows="2" placeholder="Detalles adicionales..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio en colones (₡)</label>
                    <input type="number" class="form-control form-control-sm" id="mp-precio" min="0" step="500" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" class="form-control form-control-sm" id="mp-stock" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="mp-save">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>

<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>
<script src="/lib/datatables/Select-1.6.0/js/dataTables.select.min.js"></script>
<script src="/lib/filepond/filepond.min.js"></script>

<script src="<?= autoVer('/usr/screens/assets/js/screens.js') ?>"></script>
<script>
    ScreensApp.initCatalogs({
        ajaxUrl: CTRLSCREENS
    });
</script>