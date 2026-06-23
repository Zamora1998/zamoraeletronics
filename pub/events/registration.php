<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/pub/events/modregistration.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'ntyAproved',
    'ntyDenied',
    'lblMirror'
);
$headLabels = $objLabel->getLabels($labelSels, $chrLang);

$objPost = new modRegistration($_MYSQLI_);
$events = $objPost->select2Events()['data'];
$dataevents = $objPost->selectEvents()['data'];

// Agrupar rutas por evento
$groupedEvents = [];
foreach ($dataevents as $event) {
    $eid = $event['id'];
    if (!isset($groupedEvents[$eid])) {
        $groupedEvents[$eid] = $event;
        $groupedEvents[$eid]['routes'] = [];
    }
    $groupedEvents[$eid]['routes'][] = [
        'route_name' => $event['route_name'],
        'route_description' => $event['route_description'],
        'distance_km' => $event['route_distance_km'],
        'coordinates' => $event['coordinates'],
        'route_uid' => $event['route_uid'],
        'registered_count' => $event['registered_count'],
        'start_time' => $event['start_time'],
        'end_time' => $event['end_time'],
        'cost' => $event['cost'],
    ];
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="EDMI - Soluciones Empresariales">
    <meta name="generator" content="">
    <title>EDMI - Soluciones Empresariales</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/LogoD.svg">
    <link href="/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= autoVer('/assets/css/custom.css'); ?>" rel="stylesheet">
    <link href="/lib/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="/lib/select2/select2.min.css" rel="stylesheet" />
    <link href="/lib/select2/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="/lib/flatpickr/flatpickr.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />

    <link href="<?= autoVer('/pub/events/registration.css'); ?>" rel="stylesheet">

</head>

<body class="d-flex h-100 text-center text-bg-light position-relative" style="
    background: url('assets/images/events/1000282277.png') no-repeat center center;
    background-size: cover;
">

    <!-- Overlay difuminado -->
    <div style="
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(255,255,255,0.5); /* color semitransparente */
        backdrop-filter: blur(5px); /* difuminado */
        z-index: 0;
    "></div>
    <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
        <div class="container d-flex justify-content-between align-items-center py-3">
            <!-- Logo y texto -->
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="/assets/images/LogoD.svg" alt="Logo" height="40">
                <span class="ms-3 fw-bold fs-5">EDMI - Soluciones Empresariales</span>
            </a>

            <!-- Botón hamburguesa -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-3">
                    <li class="nav-item">
                        <a class="nav-link active fs-5 fw-semibold" href="#main">Eventos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-5 fw-semibold" href="https://discoveryadventurecr.com/">Tours</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

        <div id="alertcontainer" class="container p-5"></div>
        <main id="main" class="px-3" data-event_id="">
            <!-- EVENTOS CARDS -->
            <div class="container-fluid mb-5 pb-5">
                <?php if (!empty($groupedEvents)): ?>
                    <?php foreach ($groupedEvents as $event): ?>
                        <div class="card event-card">
                            <div class="event-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                <div>
                                    <div class="event-title"><?= htmlspecialchars($event['event_name']) ?></div>
                                    <div class="event-meta">
                                        <i class="fa fa-calendar-alt me-1"></i>
                                        <?= date('d-M-Y H:i', strtotime($event['start_datetime'])) ?>
                                        &mdash; <?= date('d-M-Y H:i', strtotime($event['end_datetime'])) ?>
                                        <span class="mx-2">
                                            <i class="fa fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($event['location']) ?>
                                        </span>
                                    </div>

                                </div>

                                <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end w-100 w-md-auto">
                                    <span class="badge fs-6" style="background-color: rgb(210, 151, 88) !important;">
                                        Cupo máximo: <?= htmlspecialchars($event['max_participants']) ?>
                                    </span>
                                    <span class="badge fs-6" style="background-color: rgb(210, 151, 88) !important;">
                                        Cupos Disponibles: <?= htmlspecialchars($event['registered_count']) ?>
                                    </span>
                                    <span class="badge badge-custom fs-6">Inscripciones</span>
                                    <span class="badge badge-custom fs-6">
                                        Desde: <?= date('d-M-Y', strtotime($event['registration_open'])) ?>
                                    </span>
                                    <span class="badge badge-custom fs-6">
                                        Hasta: <?= date('d-M-Y', strtotime($event['registration_close'])) ?>
                                    </span>

                                </div>
                            </div>
                            <!-- 👇 Aquí van las dos cards -->
                            <div class="card-body text-start">
                                <div class="row mt-3">
                                    <!-- Columna de descripción -->
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 rounded bg-light shadow-sm">

                                            <?php
                                            // Texto original
                                            $description = $event['description'];

                                            // Separar por saltos de línea dobles o puntos
                                            $parts = preg_split('/(\.|\n+)/', $description);

                                            foreach ($parts as $part) {
                                                $part = trim($part);
                                                if (empty($part)) continue;

                                                // Detectar si hay un número de WhatsApp (ejemplo: 7237-8467)
                                                if (preg_match('/(\d{4})[- ]?(\d{4})/', $part, $matches)) {
                                                    $phone = "506" . $matches[1] . $matches[2]; // 50672378467
                                                    $waLink = "https://wa.me/{$phone}?text=" . urlencode("Hola, deseo más información sobre el evento.");

                                                    echo '<div class="route-title px-3 py-1 mb-1 bg-white rounded shadow-sm">';
                                                    echo 'Consultas o información al <a href="' . $waLink . '" target="_blank">';
                                                    echo '<i class="fab fa-whatsapp me-1 text-success"></i>' . htmlspecialchars($matches[1] . '-' . $matches[2]) . '</a>';
                                                    echo '</div>';
                                                } else {
                                                    echo '<div class="route-title px-3 py-1 mb-1 bg-white rounded shadow-sm">'
                                                        . htmlspecialchars($part) .
                                                        '</div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <!-- Columna de imágenes -->
                                    <div class="col-12 col-md-6">
                                        <!-- Contenedor gris con borde redondeado -->
                                        <div class="p-3 rounded bg-light shadow-sm">
                                            <div class="row">
                                                <?php if (!empty($event['imageone'])): ?>
                                                    <div class="col-6 mb-3 d-flex justify-content-end">
                                                        <div class="gradient-border p-1 rounded">
                                                            <div class="card shadow-sm h-100 rounded" style="max-height: 200px; width: auto;">
                                                                <img src="<?= htmlspecialchars($event['imageone']) ?>"
                                                                    class="card-img-top img-fluid rounded clickable-image"
                                                                    alt="Imagen 1"
                                                                    style="object-fit: cover; height: 200px; width: auto; cursor:pointer;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($event['imagetwo'])): ?>
                                                    <div class="col-6 mb-3 d-flex justify-content-center">
                                                        <div class="gradient-border p-1 rounded">
                                                            <div class="card shadow-sm h-100 rounded" style="max-height: 200px; width: auto;">
                                                                <img src="<?= htmlspecialchars($event['imagetwo']) ?>"
                                                                    class="card-img-top img-fluid rounded clickable-image"
                                                                    alt="Imagen 2"
                                                                    style="object-fit: cover; height: 200px; width: auto; cursor:pointer;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>


                                </div>


                                <?php foreach ($event['routes'] as $i => $route):
                                    $mapId = "map-{$event['id']}-$i";
                                ?>
                                    <div class="card my-3 shadow-sm border-0 route-card">
                                        <div class="card-body">
                                            <!-- Fila superior: título de la ruta + horas -->
                                            <div class="row align-items-center mb-3">
                                                <div class="col-md-6">
                                                    <h5 class="route-title px-3 py-2 mb-0">
                                                        <i class="fa fa-route me-1"></i><?= htmlspecialchars($route['route_name']) ?>
                                                    </h5>
                                                </div>
                                                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                                    <span class="mx-2">
                                                        <i class="fa fa-clock me-1"></i><span class="me-1">Inicio</span>
                                                        <input type="text" class="form-control form-control-sm d-inline-block bg-white"
                                                            id="start_time_<?= $event['id'] ?>"
                                                            value="<?= htmlspecialchars($route['start_time']) ?>"
                                                            style="width: 80px;" disabled>
                                                    </span>
                                                    <span class="mx-2">
                                                        <i class="fa fa-clock me-1"></i><span class="me-1">Fin</span>
                                                        <input type="text" class="form-control form-control-sm d-inline-block bg-white"
                                                            id="end_time_<?= $event['id'] ?>"
                                                            value="<?= htmlspecialchars($route['end_time']) ?>"
                                                            style="width: 80px;" disabled>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Fila inferior: mapa + datos -->
                                            <div class="row align-items-start">
                                                <div class="col-md-6">
                                                    <div id="<?= $mapId ?>" class="leaflet-container mb-2"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="route-info p-2">
                                                        <!-- Distancia -->
                                                        <div class="route-title px-3 py-1 mb-1 bg-white rounded shadow-sm">
                                                            <i class="fa fa-ruler-horizontal me-1"></i>
                                                            Distancia: <?= htmlspecialchars($route['distance_km']) ?> km
                                                        </div>

                                                        <!-- Precio -->
                                                        <div class="route-title px-3 py-1 mb-1 bg-white rounded shadow-sm">
                                                            <i class="fa fa-money-bill-wave me-1"></i>
                                                            Precio: ₡<?= number_format((float)$route['cost'], 2, '.', ',') ?>
                                                        </div>

                                                        <!-- Descripción -->
                                                        <div class="route-title px-3 py-1 mb-1 bg-white rounded shadow-sm">
                                                            <i class="fa fa-align-left me-1"></i>
                                                            <strong>Descripción:</strong> <?= htmlspecialchars($route['route_description']) ?>
                                                        </div>

                                                        <!-- Desnivel -->
                                                        <div class="route-title px-3 py-2 mb-3 bg-white rounded shadow-sm w-100">
                                                            <i class="fa fa-mountain me-1"></i><strong>Desnivel:</strong>
                                                            <div class="desnivel-chart mt-2"
                                                                id="chart-<?= $mapId ?>"
                                                                data-map-id="<?= $mapId ?>"
                                                                data-coordinates='<?= htmlspecialchars($route['coordinates'], ENT_QUOTES) ?>'
                                                                style="height:40px;">
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <button
                                                        class="btn btn-success btn-sm mt-3 btn-inscribirse-ruta"
                                                        data-route_uid="<?= htmlspecialchars($route['route_uid'] ?? '') ?>"
                                                        data-event_name="<?= htmlspecialchars($event['event_name']) ?>"
                                                        data-route_name="<?= htmlspecialchars($route['route_name']) ?>"
                                                        data-registration_close="<?= htmlspecialchars($event['registration_close']) ?>">
                                                        <i class="fa fa-calendar-plus me-1"></i>Inscribirse a esta ruta
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card event-card shadow-sm mx-auto my-5" style="max-width: 600px;">
                        <div class="card-body text-center p-5">
                            <i class="fa fa-calendar-times fa-4x text-muted mb-3"></i>
                            <h3 class="card-title text-secondary">No hay eventos disponibles</h3>
                            <p class="card-text lead text-muted mt-3">
                                Actualmente no tenemos eventos abiertos para inscripción.
                            </p>
                            <p class="card-text mt-4">
                                Si tiene alguna consulta respecto a un evento, por favor escribir al número 7237-8467 o hacer click en el siguiente enlace
                            </p>
                            <a href="https://wa.me/50672378467" target="_blank" class="btn btn-success btn-lg mt-2">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp Discovery Adventure CR
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Modal para imágenes -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content bg-transparent border-0">
                        <img id="modalImage" src="" class="img-fluid rounded" alt="Imagen ampliada">
                    </div>
                </div>
            </div>

            <!-- Modal de Registro de Evento -->
            <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true" data-id="" data-event_name="" data-route_name="">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow-lg">
                        <div class="modal-header">
                            <h5 class="modal-title" id="registrationModalLabel">
                                <i class="fa fa-calendar-plus me-2"></i>
                                <span id="modalEventTitle">Formulario de inscripción</span>
                            </h5>
                            <button type="button" id="closeheaderregistration" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form id="eventRegistrationForm" class="needs-validation" novalidate>
                            <div class="modal-body bg-light">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="firstName" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lastName" class="form-label">Primer Apellido</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="secondLastName" class="form-label">Segundo Apellido</label>
                                        <input type="text" class="form-control" id="secondLastName" name="secondLastName" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="gender" class="form-label">Género</label>
                                        <select id="gender" class="form-select" name="gender" required>
                                            <option value="">Seleccione</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="age" class="form-label">Edad</label>
                                        <input type="number" class="form-control" id="age" name="age" required min="1">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="age" class="form-label">Cedula</label>
                                        <input type="number" class="form-control" id="idcard" name="idcard" required min="9">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="email" class="form-label">Correo electrónico</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phone" class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="shirtSize" class="form-label">Talla de Camisa *</label>
                                        <select id="shirtSize" class="form-select" name="shirtSize" required>
                                            <option value="">Seleccione una talla</option>
                                            <option value="10">10</option>
                                            <option value="14">14</option>
                                            <option value="XS">XS</option>
                                            <option value="S">S</option>
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                            <option value="XXL">XXL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="beneficiaryName" class="form-label">Nombre del beneficiario de póliza</label>
                                        <input type="text" class="form-control" id="beneficiaryName" name="beneficiaryName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="beneficiaryId" class="form-label">Cédula del beneficiario de póliza</label>
                                        <input type="text" class="form-control" id="beneficiaryId" name="beneficiaryId" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" id="closeregistration" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Inscribirse</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal de Inscripciones Cerradas -->
        <div class="modal fade" id="closedRegistrationModal" tabindex="-1" aria-labelledby="closedRegistrationLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="closedRegistrationLabel">
                            <i class="fa fa-calendar-times me-2"></i>Inscripciones Cerradas
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body text-center py-5">
                        <i class="fa fa-lock fa-3x text-danger mb-3 d-block"></i>
                        <p class="fs-5 mb-3">Las inscripciones para este evento ya se han cerrado.</p>
                        <p class="text-muted mb-4">
                            Si desea participar, el <strong>PAQUETE DE CARRERA se le entregará DESPUÉS del evento</strong>, ya que se encuentra en producción.
                        </p>
                        <p class="small text-secondary">¿Desea continuar con la inscripción de todas formas?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="proceedClosedRegistration">
                            <i class="fa fa-check me-1"></i>Sí, deseo participar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Políticas de Pago -->
        <div class="modal fade" id="paymentPolicyModal" tabindex="-1" aria-labelledby="paymentPolicyLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentPolicyLabel">Políticas de Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body text-start">
                        <ul>
                            <li>No se hacen devoluciones una vez hecho el pago por los costos de producción.</li>
                            <li>Cancelación de inscripción: si no puede participar, puede transferir a otro participante.</li>
                            <li>Asegúrese de recibir la copia de su inscripción en su correo de forma inmediata. Si por alguna razón paga y no llena el formulario, la organización no se hace responsable. No tendrá derecho a paquete de carrera.</li>
                            <li>Marque la casilla correspondiente de su talla, no se hacen cambios.</li>
                        </ul>
                        <p>¿Desea continuar con la inscripción?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary" id="confirmRegistrationBtn">Sí, continuar</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="mt-auto text-white-50">
            <p>© 2024 by AccessO. All rights reserved.</p>
        </footer>
    </div>

    <script src="/lib/jquery/jquery-3.6.3.min.js"></script>
    <script src="/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/lib/select2/select2.min.js"></script>
    <script src="/lib/flatpickr/flatpickr.min.js"></script>
    <script src="/lib/flatpickr/l10n/es.js"></script>
    <!-- ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= autoVer('/pub/events/registration.js'); ?>"></script>
    <script>
        var chrLang = '<?= $chrLang ?>';
        window.eventMapsData = [];
        <?php foreach ($groupedEvents as $event):
            foreach ($event['routes'] as $i => $route):
                $mapId = "map-{$event['id']}-$i";
                $coords = json_decode($route['coordinates'], true);
                if (!$coords || count($coords) < 2) continue;
        ?>
                window.eventMapsData.push({
                    mapId: "<?= $mapId ?>",
                    coords: <?= json_encode($coords) ?>
                });
        <?php endforeach;
        endforeach; ?>
    </script>
</body>

</html>