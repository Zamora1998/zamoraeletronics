<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnNews',
    'btnNew',
    'btnEdit',
    'btnDetailBooking',
    'btnDetails',
    'btnExpected',
    'btnExport',
    'btnSave',
    'btnNo',
    'btnYes',
    'lblRequired',
    'lblRevenue',
    'lblSearch',
    'lblRefence',
    'lblSelect'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
?>

<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/2.3.2/datatables.min.css" />
<link rel="stylesheet" href="/lib/flatpickr/themes/<?= $selDark ? 'dark' : 'light'; ?>.css">
<?= $selDark ? '<link rel="stylesheet" href="/lib/flatpickr/themes/dark.css">' : ''; ?>
<link rel="stylesheet" type="text/css" href="<?= autoVer('/usr/inbox/assets/css/inbox.css') ?>" />

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">

            <div class="inbox-wrapper">

                <!-- ── Sidebar izquierdo: lista de correos ── -->
                <div class="inbox-sidebar">
                    <div class="inbox-sidebar-header">
                        <h6 class="mb-0">Bandeja de entrada</h6>
                        <span class="badge bg-primary" id="inboxUnreadCount">0</span>
                    </div>

                    <div class="inbox-search">
                        <input type="text" class="form-control form-control-sm"
                            id="inboxSearch" placeholder="Buscar...">
                    </div>

                    <div class="inbox-list" id="inboxList">
                        <!-- Se llena desde JS -->
                        <div class="inbox-empty text-muted text-center py-5">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Sin correos</p>
                        </div>
                    </div>
                </div>

                <!-- ── Panel derecho: contenido del correo ── -->
                <div class="inbox-content" id="inboxContent">

                    <!-- Estado vacío -->
                    <div class="inbox-empty-state" id="inboxEmptyState">
                        <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Seleccioná un correo para verlo</p>
                    </div>

                    <!-- Vista del correo (oculto hasta seleccionar) -->
                    <div class="inbox-mail-view d-none" id="inboxMailView">

                        <!-- Header del correo -->
                        <div class="inbox-mail-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1" id="mailSubject"></h5>
                                    <small class="text-muted">
                                        De: <strong id="mailFrom"></strong>
                                        &nbsp;·&nbsp;
                                        <span id="mailDate"></span>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" id="btnReply">
                                    <i class="fas fa-reply me-1"></i> Responder
                                </button>
                            </div>
                        </div>

                        <!-- Info del cliente si matchea -->
                        <div class="inbox-client-card d-none" id="inboxClientCard">
                            <div class="d-flex align-items-center gap-3">
                                <div class="inbox-client-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" id="clientName"></div>
                                    <small class="text-muted" id="clientCedula"></small>
                                    <small class="text-muted d-block" id="clientEmpresa"></small>
                                </div>
                                <a href="#" class="ms-auto btn btn-sm btn-outline-primary d-none" id="btnVerCliente">
                                    Ver cliente
                                </a>
                            </div>
                        </div>

                        <!-- Cuerpo del correo -->
                        <div class="inbox-mail-body" id="mailBody"></div>

                        <!-- Panel de respuesta (oculto hasta clickear Responder) -->
                        <div class="inbox-reply d-none" id="inboxReplyPanel">
                            <div class="inbox-reply-header">
                                <small class="text-muted">Respondiendo a <strong id="replyTo"></strong></small>
                            </div>
                            <textarea class="form-control" id="replyBody" rows="5"
                                placeholder="Escribí tu respuesta..."></textarea>
                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <button class="btn btn-sm btn-outline-secondary" id="btnCancelReply">
                                    Cancelar
                                </button>
                                <button class="btn btn-sm btn-primary" id="btnSendReply">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/jquery-ui/jquery-ui.min.js"></script>
<script src="/lib/datatables/2.3.2/datatables.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/xlsx/xlsx.full.min.js"></script>
<script src="/lib/flatpickr/flatpickr.min.js"></script>
<?php if ($chrLang != 'en'): ?>
    <script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
<?php endif; ?>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/usr/inbox/assets/js/inbox.js'); ?>"></script>
</body>

</html>