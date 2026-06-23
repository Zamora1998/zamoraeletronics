<?
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/usr/events/modevent.php';

$objLabel = new labels($_MYSQLI_);


$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnEdit',
    'btnDetails',
    'btnNew',
    'btnUpdate',
    'btnSave',
    'btnYes',
    'btnNo',
    'nteCreateError',
    'nteCreateSuccess',
    'nteDeleteError',
    'nteDeleteSuccess',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'navConfiguration',
    'lblNewLabel',
    'lblYear',
    'lblidentificationcard',
    'lblOrganizationName',
    'lblidentificationcard',
    'lblTypeCompany',
    'lblAddress',
    'tblPhone',
    'tblEmail',
    'socWebPage',
    'lblIdateofentry',
    'lblNewSettings',
    'lblRequired',
    'lblEnabled',
    'lblFrom',
    'lblEveryMonth',
    'lblMonths',
    'lblType',
    'btnNew',
    'lblColumns',
    'nteNotData',
    'lblMonthadded',
    'tblPhone',
    'navMonthsData',
    'btnCheckpoints',
    'lblIdateofentry',
    'lblName',
    'lblSelectMonths',
    'lblEditEvent',
    'lblEmail',
    'lblNewEvent',
    'btnExport',
    'lblRefence',
    'lblInputsRequired',
    'lblAplyReference',
    'lblVoucher',
    'lblSelect',
    'lblBooking',
    'lblAmount',
    'lblFilter',
    'lblChart',
    'lblCurrency',
    'navPayroll',
    'lblSearch',
    'lblSendMailsSuccess',
    'lblSendMailsError',
    'lblEvents',
    'lblCreaEvent',
    'lblCreaRoutes',
    'tblActions'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$setLabels = $objLabel->getPrefixedLabels('set', $chrLang);
$pageTitle = $labels['lblEvents'];
$objtEvents = new modevents();
$srvEvents = $objtEvents->select2Events()['data'];
$eventTypes = $objtEvents->select2EventTypes()['data'];

?>
<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/bootstrap-datepicker/css/bootstrap-datepicker.standalone.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Buttons-2.3.4/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/vendor/enyo/dropzone/dist/min/dropzone.min.css" />
<link rel="stylesheet" type="text/css" href="<?= autoVer('/usr/events/event.css'); ?>" />

<!-- Leaflet core -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<link rel="stylesheet" href="/lib/flatpickr/flatpickr.min.css">

<div class="" id="table">
    <div class="p-4 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-Events-tab" data-bs-toggle="tab" data-bs-target="#nav-Events" type="button" role="tab" aria-controls="nav-Events" aria-selected="true"><?= $labels['lblCreaEvent'] ?></button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane  fade show active" id="nav-Events" role="tabpanel" aria-labelledby="nav-Events-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tableEvents" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCheckPoints" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="h1Route" aria-hidden="true" data-id="0">
    <div class="modal-dialog modal-dialog-scrollable modal-lg custom-modal-width">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="h1Route"><?= $labels['btnNew'] ?> Ruta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row align-items-end">
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label">Mapa</label>
                        <div id="mapCompleteRoute" class="map-container-Complete"></div>
                        <small class="text-muted">Haz clic en el mapa para añadir puntos. Se dibujará la ruta automáticamente.</small>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label">Coordenadas</label>
                        <textarea id="routeCheckPoints" class="form-control form-control-sm" rows="3" name="routeCheckPoints" readonly required></textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 d-flex align-items-center">
                        <span id="routeDistanceLabel" class="ms-3">
                            Distancia total (km): <span id="routeDistanceValue">0.00</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveCheckPointsT" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Settings Modal con Dropzone -->
<div class="modal fade" id="modalSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $labels['lblNewSettings'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Zona de Dropzone -->
                <form id="dropSettings" class="dropzone">
                    <div class="dz-message">Arrastra los archivos aquí o haz clic para seleccionar</div>
                    <!-- Contenedor visible de previews -->
                    <div class="dropzone-previews mt-3" id="previewsContainer"></div>
                </form>

                <!-- Template para previews (oculto) -->
                <div id="template-container" class="d-none">
                    <div class="dz-preview dz-image-preview dz-file-preview">
                        <div class="dz-image">
                            <img data-dz-thumbnail>
                        </div>
                        <div class="dz-details">
                            <div class="dz-filename"><span data-dz-name></span></div>
                            <div class="dz-size" data-dz-size></div>
                        </div>
                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                        <div class="dz-success-mark">✔</div>
                        <div class="dz-error-mark">✖</div>
                        <a class="dz-remove bg-danger text-white px-2 py-1" href="javascript:undefined;" data-dz-remove>Eliminar</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="settingsSave" type="button" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog modal-xl"> <!-- Extra ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eventModalLabel"><?= $labels['btnNew'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre del evento</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="event_type_id" class="form-label">Tipo de Evento</label>
                            <select class="form-select form-select-sm" id="event_type_id" name="event_type_id" required>
                                <option value="">Seleccionar tipo...</option>
                                <? foreach ($eventTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= $type['name'] ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft">Borrador</option>
                                <option value="published">Publicado</option>
                                <option value="initiated">Iniciado</option>
                                <option value="closed">Cerrado</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control form-control-sm" id="descriptionevent" name="descriptionevent" rows="3" required></textarea>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_datetime" class="form-label">Fecha y hora inicio</label>
                            <input type="text" class="form-control form-control-sm" id="start_datetime" name="start_datetime" required placeholder="1-Sep-2025 10:00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_datetime" class="form-label">Fecha y hora fin</label>
                            <input type="text" class="form-control form-control-sm" id="end_datetime" name="end_datetime" placeholder="1-Sep-2025 12:00" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Lugar de salida</label>
                            <input type="text" class="form-control form-control-sm" id="location" name="location" required>
                            <ul id="suggestions" class="list-group"></ul>
                        </div>
                        <div class="col-md-3">
                            <label for="latitude" class="form-label">Latitud</label>
                            <input step="0.00000001" class="form-control form-control-sm" id="latitude" name="latitude" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="longitude" class="form-label">Longitud</label>
                            <input step="0.00000001" class="form-control form-control-sm" id="longitude" name="longitude" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="distance_km" class="form-label">Distancia (km)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="distance_km" name="distance_km" required>
                        </div>
                        <div class="col-md-3">
                            <label for="max_participants" class="form-label">Cupo máximo</label>
                            <input type="number" class="form-control form-control-sm" id="max_participants" name="max_participants" required>
                        </div>

                        <div class="col-md-3">
                            <label for="registration_open" class="form-label">Inscripciones abiertas</label>
                            <input type="text" class="form-control form-control-sm datepicker" id="registration_open" name="registration_open" autocomplete="off" required>
                        </div>
                        <div class="col-md-3">
                            <label for="registration_close" class="form-label">Inscripciones cerradas</label>
                            <input type="text" class="form-control form-control-sm datepicker" id="registration_close" name="registration_close" autocomplete="off" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="eventSave" class="btn btn-sm btn-primary">
                    <?= $labels['btnSave'] ?>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <?= $labels['btnCancel'] ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFields" tabindex="-1" aria-labelledby="modalFieldsLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFieldsLabel">Campos de Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="p-3">
                    <table class="table table-striped" id="tblRegistrationConfig">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th class="text-center">Habilitado</th>
                                <th class="text-center">Requerido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Javascript will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveRegistrationConfig" class="btn btn-sm btn-primary">Guardar Configuración</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<? require_once __ROOT__ . '/components/usr/usrfoot.php' ?>

<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>
<script src="/lib/datatables/Select-1.6.0/js/dataTables.select.min.js"></script>
<script src="/lib/colorpicker/spectrum.js"></script>
<script src="/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/vendor/enyo/dropzone/dist/min/dropzone.min.js"></script>
<script src="/lib/xlsx/xlsx.full.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/lib/flatpickr/flatpickr.min.js"></script>
<script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/gpx.min.js"></script>

<?
if (file_exists(__ROOT__ . '/lib/bootstrap-datepicker/locales/bootstrap-datepicker.' . str_replace('_', '-', $chrLocale) . '.min.js')) {
?>
    <script src="/lib/bootstrap-datepicker/locales/bootstrap-datepicker.<?= str_replace('_', '-', $chrLocale) ?>.min.js"></script>
<?
} elseif (file_exists(__ROOT__ . '/lib/bootstrap-datepicker/locales/bootstrap-datepicker.' . $chrLang . '.min.js')) {
?>
    <script src="/lib/bootstrap-datepicker/locales/bootstrap-datepicker.<?= $chrLang ?>.min.js"></script>
<?
} elseif (file_exists(__ROOT__ . '/lib/colorpicker/i18n/jquery.spectrum-' . $chrLang . '.js')) {
?>
    <script src="/lib/colorpicker/i18n/jquery.spectrum-<?= $chrLang ?>.min.js"></script>
<?
}
if (file_exists(__ROOT__ . '/lib/colorpicker/i18n/jquery.spectrum-' . $chrLang . '.js')) {
?>
    <script src="/lib/colorpicker/i18n/jquery.spectrum-<?= $chrLang; ?>.js"></script>
<?
}
?>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer(url: '/usr/events/event.js'); ?>"></script>
<script src="<?= autoVer(url: '/usr/routes/routes.js'); ?>"></script>

</body>

</html>