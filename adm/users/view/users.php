<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/assets/php/libBitCtrl.php';
require_once __ROOT__ . '/adm/users/model/modAccess.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/components/usr/labels/cLabels.php';

$labelSels = array(
    'accAdmin',
    'accDev',
    'btnActions',
    'btnCancel',
    'btnSendPasswordResetEmail',
    'btnSave',
    'btnResetPassword',
    'frmConfirmPassword',
    'frmEmail',
    'frmFirstname',
    'frmLastname',
    'frmPassword',
    'lblAccessRights',
    'tblActions',
    'lblAdd',
    'lblAlreadyHaveAccount',
    'lblBackToSignIn',
    'lblDontHaveAccount',
    'lblEdit',
    'tblEnabled',
    'lblForgotPassword',
    'lblLoading',
    'lblNewUser',
    'lblEditUser',
    'lblSelectUser',
    'lblResetPassword',
    'lblSignIn',
    'lblSignUp',
    'navUsers',
    'nteDuplicateEntry',
    'nteErrorSave',
    'nteFieldReqired',
    'nteFields',
    'nteInsertAnEmail',
    'nteLanguages',
    'ntePWNotSame',
    'ntePWNotStrongEnough',
    'ntePWResetError',
    'ntePWResetSoccess',
    'ntePWSame',
    'lblDescription',
    'ntePWStrongEnough',
    'nteSaveSuccess',
    'nteSecurePassword',
    'pwrFail',
    'pwrInternalError',
    'pwrSuccess',
    'tblEmail',
    'tblEnabled',
    'tblFirstName',
    'tblLastName',
    'btnDelete',
    'btnEdit',
    'btnDetails',
    'btnNew',
    'btnUpdate',
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
    'lblAgent',
    'lblIRFS15',
    'lblNewLabel',
    'btnAccess',
    'tblName',
    'lblYear',
    'lblAccessName',
    'lblNewSettings',
    'lblRequired',
    'lblSearching',
    'lblEveryMonth',
    'lblMonths',
    'lblWriteElements',
    'lblLabel',
    'lblNotResults',
    'lblCharacters',
    'lblType',
    'lblColumns',
    'lblLanguages',
    'lblName',
    'lblSelectOption',
    'navAccess',
    'lblnewAccess',
    'lblMonthadded',
    'lblSelectMonths',
    'btnExport',
    'lblRefence',
    'lblAplyReference',
    'lblVoucher',
    'lblSelect',
    'lblBooking',
    'lblAmount',
    'lblFilter',
    'lblChart',
    'nteDeleteWarnUsed',
    'nteDeleteWarnUnused',
    'lblCurrency',
    'lblSearch',
    'tblKey',
    'tblLabelID',
    'tblDescription'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$locTree = $objLoc->selectTree()['data'];
$objBitCtrl = new BitControl;
$hasProtected = in_array('dev', $authParams);
?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/bootstrap-datepicker/css/bootstrap-datepicker.standalone.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Buttons-2.3.4/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="<?= autoVer('/adm/users/assets/css/access.css') ?>" />

<div class="" id="table">
    <div class="p-4 pb-0 align-items-start rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab-users" role="tablist">
                <button class="nav-link active" id="nav-users-tab" data-bs-toggle="tab" data-bs-target="#nav-users"
                    type="button" role="tab" aria-controls="nav-users"
                    aria-selected="true"><?= $labels['navUsers'] ?></button>
                <?php if ($hasProtected): ?>
                    <button class="nav-link" id="nav-Access-tab" data-bs-toggle="tab" data-bs-target="#nav-Access"
                        type="button" role="tab" aria-controls="nav-Access"
                        aria-selected="true">
                        <?= $labels['navAccess'] ?>
                    </button>
                <?php endif; ?>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active pt-3" id="nav-users" role="tabpanel" aria-labelledby="nav-users-tab"
                tabindex="0">
                <table id="tableUsers" class="table table-striped nowrap"></table>
            </div>
            <?php if ($hasProtected): ?>

                <div class="tab-pane fade  pt-3" id="nav-Access" role="tabpanel" aria-labelledby="nav-Access" tabindex="0">
                    <div class="row mt-2">
                        <div class="col-12">
                            <table id="tableAccess" class="table table-hover dataTable table-striped w-full no-footer table-responsive">
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAccess" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="h1Access" aria-hidden="true" data-id="" data-access="0">
    <div class="modal-dialog"> <!-- Aquí se agrega la clase modal-xl -->
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="h1Access"><?= $labels['btnNew'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Nombre anterior -->
                    <div id='containerAccess' class="col-12 mb-3">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="AccessSave"
                    class="btn btn-sm btn-primary">&nbsp;<?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary"
                    data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal New-edit-->
<div class="modal fade" id="modalUsers" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblNewUser'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="input-group has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-user"></i>
                            </span>
                            <input id="First" name="first" class="form-control form-control-sm" type="text" placeholder="<?= $labels['frmFirstname'] ?>" required>
                            <div class="invalid-feedback"><?= $labels['nteFieldReqired'] ?></div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="input-group has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-user"></i>
                            </span>
                            <input id="Last" name="last" class="form-control form-control-sm" type="text" placeholder="<?= $labels['frmLastname'] ?>" required>
                            <div class="invalid-feedback"><?= $labels['nteFieldReqired'] ?></div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="input-group has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-at"></i>
                            </span>
                            <input id="Email" name="email" class="form-control form-control-sm" type="text" placeholder="<?= $labels['frmEmail'] ?>" required>
                            <div class="invalid-feedback"><?= $labels['nteFieldReqired'] ?></div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="input-group has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-globe"></i>
                            </span>
                            <select id="langId" class="form-control form-control-sm selectLang" name="lang_id" data-placeholder="<?= $labels['nteLanguages'] ?>">
                                <option value="" disabled selected><?= $labels['nteLanguages'] ?></option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="description mt-3" for="Enabled">
                            <?= $labels['tblEnabled'] ?>
                            <input id="usersEnabled" type="checkbox" class="form-check-input checkboxActive checkboxAll" value="1" name="enabled">
                        </label>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <h5><b><?= $labels['lblAccessRights'] ?></b></h5>
                    <div id="accessCont">
<?php
// Get available accesses
$objAccess = new Access($_MYSQLI_);
$objAccess->setLanguageId($chrLang);
$response = $objAccess->selectAll();

$accesses = $response['data'];
$sessionAccess = $_SESSION['access'] ?? 0;

// Get access of the selected user
$userAccesses = $data['accesses'] ?? [];

foreach ($accesses as $access) {
    $accessId = pow(2, $access['id'] - 1);
    $checked = in_array($accessId, $userAccesses) ? 'checked' : '';
    $disabled = ($sessionAccess & $accessId) || ($sessionAccess & 1) || ($sessionAccess & 2) ? '' : 'disabled';

    // If user does not have “Dev” permission, hide it
    if ($access['id'] == 1 && !($sessionAccess & 1)) {
        continue;
    }
    $description = htmlspecialchars($access['description'] ?? '', ENT_QUOTES, 'UTF-8');
?>
                            <label <?= ($disabled) ? "class='d-none'" : '' ?>>
                                <input
                                    id="access_<?= $accessId ?>"
                                    data-id="<?= $accessId ?>"
                                    type="checkbox"
                                    class="form-check-input"
                                    <?= $checked ?>
                                    <?= $disabled ?>
                                    name="accesses[]"
                                    value="<?= $accessId ?>">
                                &nbsp;<?= htmlspecialchars($access['name'], ENT_QUOTES, 'UTF-8') ?>

<?php if (!empty($description)): ?>
                                <i
                                    class="access-info far fa-info-circle"
                                    title="<?= $description ?>">
                                </i>
<?php endif; ?>
                            </label><br>
<?php
}
?>
                    </div>
                </div>
                <div class="d-grid gap-1 d-md-flex justify-content-md-end">
                    <button id="save" type="button" class="btn btn-sm btn-primary me-1" data-id="0"><?= $labels['btnSave'] ?></button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <?= $labels['btnCancel'] ?>
                    </button>
                </div>
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
<script src="/lib/select2/select2.min.js"></script>
<script>
    var localeSN = chrLocale;
</script>
<script>
    var localeSN = chrLang;
</script>
<script>
    var locTree = <?= json_encode($locTree); ?>;
    var labels = <?= json_encode($labels); ?>;
    var loggedUserAccesses = <?= json_encode($_SESSION['access']) ?>;
</script>
<script src="/lib/datatables/Buttons-2.3.4/js/dataTables.buttons.js"></script>
<script src="/lib/datatables/JSZip-2.5.0/jszip.min.js"></script>
<script src="/lib/datatables/Buttons-2.3.4/js/buttons.html5.min.js"></script>
<script src="/lib/datatables/Buttons-2.3.4/js/buttons.colVis.min.js"></script>
<script src="/lib/xlsx/xlsx.full.min.js"></script>
<script src="/lib/jquery-ui/jquery-ui.min.js"></script>
<script src="<?= autoVer('/adm/users/assets/js/users.js'); ?>"></script>
<script src="<?= autoVer('/adm/users/assets/js/access.js'); ?>"></script>

<!-- END PAGE SPECIFIC SCRIPTS -->
</body>

</html>
