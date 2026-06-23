<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/usr/screens/model/modScreens.php';

$objS   = new tvScreens($_MYSQLI_);
$orders = $objS->selectOrders()['data'];
$brands = $objS->selectBrands()['data'];

$estadoLabels = [
    'pendiente'    => ['label' => 'Pendiente',     'class' => 'badge-warning'],
    'en_reparacion' => ['label' => 'En Reparación', 'class' => 'badge-info'],
    'listo'        => ['label' => 'Listo',          'class' => 'badge-success'],
    'entregado'    => ['label' => 'Entregado',      'class' => 'badge-secondary'],
];
$pagoLabels = [
    'sinpe'    => 'SINPE',
    'efectivo' => 'Efectivo',
    'mixto'    => 'Mixto',
    'pendiente' => 'Pago Pendiente',
];
?>
<link rel="stylesheet" href="<?= autoVer('/usr/screens/assets/js/css/screens.css') ?>">
<style>
    /* === Layout full-height sin scroll de página === */
    html,
    body {
        height: 100%;
        margin: 0;
        overflow: hidden;
        background-color: var(--bs-body-bg);
    }

    /* main ya existe en usrhead; lo hacemos columna flex */
    body>main {
        display: flex;
        flex-direction: column;
        height: 100%;
        /* 100% es más seguro que 100vh en navegadores móviles */
        overflow: hidden;
    }

    /* El header del sistema ocupa su tamaño natural */
    body>main>header.menu-bg {
        flex-shrink: 0;
        z-index: 10;
    }

    /* Sub-header del módulo: fijo en tamaño */
    .sc-header,
    .edit-header,
    .cat-header {
        flex-shrink: 0;
        z-index: 5;
    }

    /* Tabs de catálogos: fija */
    .tabs {
        flex-shrink: 0;
        z-index: 4;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    /* Toolbar de filtros: fija */
    .sc-toolbar {
        flex-shrink: 0;
        z-index: 4;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    /* El contenedor es el área que crece y hace scroll */
    .sc-container,
    .edit-body,
    .cat-body {
        flex: 1 1 0;
        overflow-y: auto;
        min-height: 0;
        padding-bottom: 3rem;
        /* Espacio extra al final */
        background-color: var(--bs-tertiary-bg);
        /* Fondo ligeramente distinto para resaltar las cards */
    }

    /* Scrollbar estilizado para el contenedor */
    .sc-container::-webkit-scrollbar,
    .edit-body::-webkit-scrollbar,
    .cat-body::-webkit-scrollbar {
        width: 8px;
    }

    .sc-container::-webkit-scrollbar-track,
    .edit-body::-webkit-scrollbar-track,
    .cat-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .sc-container::-webkit-scrollbar-thumb,
    .edit-body::-webkit-scrollbar-thumb,
    .cat-body::-webkit-scrollbar-thumb {
        background-color: var(--bs-border-color);
        border-radius: 10px;
    }

    .sc-container::-webkit-scrollbar-thumb:hover,
    .edit-body::-webkit-scrollbar-thumb:hover,
    .cat-body::-webkit-scrollbar-thumb:hover {
        background-color: var(--bs-secondary-color);
    }
</style>

<div class="sc-header">
    <h1>📺 Órdenes de Trabajo</h1>
    <div class="hdr-actions">
        <a href="/<?= $chrLocale ?>/screens/edit" class="btn btn-primary" id="btn-nueva-orden">
            ＋ Nueva Orden
        </a>
        <a href="/<?= $chrLocale ?>/screens/catalogs" class="btn btn-ghost" id="btn-catalogs">
            ⚙ Catálogos
        </a>
    </div>
</div>

<div class="sc-toolbar">
    <div class="filter-group">
        <label for="filtro-estado">Estado:</label>
        <select id="filtro-estado">
            <option value="">Todos</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_reparacion">En Reparación</option>
            <option value="listo">Listo</option>
            <option value="entregado">Entregado</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="filtro-pago">Pago:</label>
        <select id="filtro-pago">
            <option value="">Todos</option>
            <option value="pendiente">Pendiente</option>
            <option value="sinpe">SINPE</option>
            <option value="efectivo">Efectivo</option>
            <option value="mixto">Mixto</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="filtro-buscar">Buscar:</label>
        <input type="text" id="filtro-buscar" placeholder="Cliente, modelo, falla...">
    </div>
</div>

<div class="sc-container">
    <!-- Stats -->
    <div class="stats-bar" id="stats-bar">
        <?php
        $counts = ['pendiente' => 0, 'en_reparacion' => 0, 'listo' => 0, 'entregado' => 0];
        foreach ($orders as $o) {
            if (isset($counts[$o['estado']])) $counts[$o['estado']]++;
        }
        $statLabels = ['pendiente' => 'Pendiente', 'en_reparacion' => 'Reparando', 'listo' => 'Listo', 'entregado' => 'Entregado'];
        $statColors = ['pendiente' => 'var(--bs-warning)', 'en_reparacion' => 'var(--bs-info)', 'listo' => 'var(--bs-success)', 'entregado' => 'var(--bs-secondary)'];
        foreach ($counts as $k => $n):
        ?>
            <div class="stat-chip">
                <span class="stat-n" style="color:<?= $statColors[$k] ?>"><?= $n ?></span>
                <span class="stat-l"><?= $statLabels[$k] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cards de órdenes -->
    <div class="orders-grid" id="orders-grid">
        <?php if (empty($orders)): ?>
            <div class="empty-state" style="grid-column:1/-1">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2>Sin órdenes aún</h2>
                <p>Haz clic en "Nueva Orden" para comenzar.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $o):
                $estado = $o['estado'];
                $es = $estadoLabels[$estado] ?? ['label' => $estado, 'class' => 'badge-secondary'];
                $modelo = trim(($o['marca'] ? $o['marca'] . ' ' : '') . $o['modelo']);
                $costo  = number_format((float)$o['costo_estimado'], 0, ',', '.');
                $abono  = number_format((float)$o['abono_inicial'],  0, ',', '.');
                $saldo  = (float)$o['costo_estimado'] - (float)$o['abono_inicial'];
            ?>
                <article
                    class="order-card estado-<?= htmlspecialchars($estado) ?>"
                    data-estado="<?= htmlspecialchars($estado) ?>"
                    data-pago="<?= htmlspecialchars($o['tipo_pago']) ?>"
                    data-search="<?= htmlspecialchars(strtolower($o['cliente_nombre'] . ' ' . $modelo . ' ' . $o['falla_reportada'])) ?>"
                    data-id="<?= $o['id'] ?>">
                    <div class="card-top">
                        <span class="card-id">#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span class="badge <?= $es['class'] ?>"><?= $es['label'] ?></span>
                    </div>

                    <div>
                        <div class="card-client"><?= htmlspecialchars($o['cliente_nombre']) ?></div>
                        <div class="card-phone">📞 <?= htmlspecialchars($o['cliente_telefono']) ?></div>
                    </div>

                    <?php if ($modelo): ?>
                        <div class="card-tv">📺 <?= htmlspecialchars($modelo) ?>
                            <?php if ($o['pantalla'] ?? ''): ?>
                                <span class="text-secondary"> — <?= htmlspecialchars($o['pantalla']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card-falla">🔧 <?= htmlspecialchars($o['falla_reportada']) ?></div>

                    <div class="card-footer">
                        <div>
                            <div class="card-cost">₡<?= $costo ?></div>
                            <div class="card-pago">
                                Abono: ₡<?= $abono ?> |
                                Saldo: ₡<?= number_format($saldo, 0, ',', '.') ?> |
                                <?= $pagoLabels[$o['tipo_pago']] ?? $o['tipo_pago'] ?>
                            </div>
                            <?php if ($o['firma_ruta']): ?>
                                <span class="firma-chip">✔ Firma</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-actions">
                            <a href="/<?= $chrLocale ?>/screens/edit?orderId=<?= $o['id'] ?>" class="btn btn-ghost btn-sm" title="Editar">✏</a>
                            <button class="btn btn-danger btn-sm btn-del-order" data-id="<?= $o['id'] ?>" title="Eliminar">🗑</button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div id="modal-confirm" class="sc-modal-overlay">
    <div class="sc-modal" style="text-align:center;">
        <h3>¿Eliminar orden?</h3>
        <p class="text-secondary mb-4">Esta acción no se puede deshacer.</p>
        <div class="d-flex gap-3 justify-content-center">
            <button id="modal-cancel" class="btn btn-secondary">Cancelar</button>
            <button id="modal-ok" class="btn btn-danger">Eliminar</button>
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
    // Inicializar vista de listado — URL dinámica via CTRLSCREENS definido en screens.js
    ScreensApp.initList({
        ajaxUrl: CTRLSCREENS
    });
</script>