<?
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/mailaccounts/modMailAccounts.php';

$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnEdit',
    'btnNew',
    'btnNo',
    'btnTestMail',
    'btnSave',
    'btnYes',
    'btnActions',
    'btnDuplicate',
    'lblCopy',
    'lblEditTemplate',
    'lblLabel',
    'lblNone',
    'lblTLS',
    'lblSSL',
    'lblSendMail',
    'lblQMAIL',
    'lblSMTP',
    'lblRequired',
    'lblPortNumbers',
    'lblMAIL',
    'lblNewMailAccount',
    'lblEditMailAccount',
    'lblNewTemplate',
    'lblTemplate',
    'lblMsgSuccess',
    'lblPort',
    'navMailAccounts',
    'lblVariable',
    'lblPreview',
    'btnRefresh',
    'navMailTemplates',
    'tblEmailAccount',
    'tblActions',
    'tblAltBody',
    'tblBody',
    'tblName',
    'tblSmtpauth',
    'tblPort',
    'tblSmtpsecure',
    'tblUsername',
    'tblPassword',
    'tblHost',
    'tblDebug',
    'tblProtected',
    'tblEnabled',
    'tblReplyto',
    'tblSubject',
    'tblProtocol',
    'nteCreateSuccess',
    'nteCreateError',
    'nteDeleteWarn',
    'nteTestSuccess',
    'nteTestError',
    'nteUpdateSuccess',
    'nteDeleteSuccess',
    'nteDuplicateSuccess',
    'nteDuplicateError',
    'nteDeleteError',
    'nteError',
    'nteFillAll',
    'nteSuccess',
    'ntePlaceHolderSizeLimit',
    'tblOauth',
    'tblOauthClientId',
    'tblOauthClientSecret',
    'tblOauthRefreshToken',
    'tblOauthType',
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$objMailAcc = new mailAccounts($_MYSQLI_);
$mailAccounts = $objMailAcc->selectAll()['data'];
$objCompanies = $objMailAcc->selectCompanies()['data'];
?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="/lib/jquery-ui/jquery-ui.min.css" type="text/css" />
<link rel="stylesheet" href="/lib/summernote/summernote-bs5.min.css">

<div class="" id="table">
    <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-mailtemplate-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-mailtemplate" type="button" role="tab" aria-controls="nav-mailtemplate"
                    aria-selected="true"><?= $labels['navMailTemplates'] ?></button>

                <button class="nav-link" id="nav-mailAccount-tab" data-bs-toggle="tab" data-bs-target="#nav-mailAccount"
                    type="button" role="tab" aria-controls="nav-mailAccount"
                    aria-selected="true"><?= $labels['navMailAccounts'] ?></button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active pt-3" id="nav-mailtemplate" role="tabpanel"
                aria-labelledby="nav-mailtemplate-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tableTemplates" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade pt-3" id="nav-mailAccount" role="tabpanel" aria-labelledby="nav-mailAccount-tab"
                tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tableMailAccounts" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalTemplate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblEditTemplate'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="liTemplates" data-bs-toggle="tab"
                            data-bs-target="#tabTemplates" type="button" role="tab" aria-controls="home"
                            aria-selected="true"><?= $labels['lblTemplate'] ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="liVariables" data-bs-toggle="tab" data-bs-target="#tabVariables"
                            type="button" role="tab" aria-controls="profile"
                            aria-selected="false"><?= $labels['lblVariable'] ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="liPreview" data-bs-toggle="tab" data-bs-target="#tabPreview"
                            type="button" role="tab" aria-controls="profile"
                            aria-selected="false"><?= $labels['lblPreview'] ?></button>
                    </li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="tabTemplates" role="tabpanel"
                        aria-labelledby="liTemplates">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input id="mtName" name="mtName" type="text" class="form-control form-control-sm"
                                    placeholder="<?= $labels['tblName'] ?>" title="<?= $labels['tblName'] ?>"
                                    maxlength="50"></input>
                            </div>
                            <div class="col-md-2">
                                <input id="mtSubject" name="mtSubject" type="text" class="form-control form-control-sm"
                                    placeholder="<?= $labels['tblSubject'] ?>" title="<?= $labels['tblSubject'] ?>"
                                    maxlength="50"></input>
                            </div>
                            <div class="col-md-3">
                                <select name="mtCompany" id="mtCompany" class="form-control form-control-sm" style="width: 100%;" placeholder="Empresa" title="Empresa">
                                    <option value="0">Seleccione una cuenta</option> <!-- Opción predeterminada con id 0 -->
                                    <?php
                                    foreach ($objCompanies as $key => $company) {
                                    ?>
                                        <option value="<?= $company['id'] ?>"><?= $company['name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="mtAccount" id="mtAccount" class="form-control form-control-sm"
                                    style="width: 100%;" placeholder="<?= $mailAccount['username'] ?>"
                                    title="<?= $mailAccount['username'] ?>">
                                    <option></option>
                                    <?
                                    foreach ($mailAccounts as $key => $mailAccount) {
                                    ?>
                                        <option value="<?= $mailAccount['id'] ?>"><?= $mailAccount['username'] ?></option>
                                    <?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label><?= $labels['tblBody'] ?>:</label>
                                <textarea class="form-control emailBody" aria-label="With textarea" id="mtBody"
                                    name="mtBody" placeholder="<?= $labels['tblBody'] ?>" maxlength="65535"></textarea>
                            </div>
                            <div class="col-12">
                                <label><?= $labels['tblAltBody'] ?>:</label>
                                <textarea class="form-control altBody" aria-label="With textarea" id="mtAltBody"
                                    name="mtAltBody" placeholder="<?= $labels['tblAltBody'] ?>"
                                    maxlength="65535"></textarea>
                            </div>
                            <div class="d-grid gap-1 d-md-flex justify-content-md-end">
                                <button id="templateSave" type="button" class="btn btn-sm btn-primary me-1"
                                    data-id="0"><?= $labels['btnSave'] ?></button>
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabVariables" role="tabpanel" aria-labelledby="liVariables">
                        <div class="row g-3" id="templateVariables">
                        </div>
                        <div class="d-grid gap-1 d-md-flex justify-content-md-end">
                            <button id="variableSave" type="button" class="btn btn-sm btn-primary me-1"
                                data-id="0"><?= $labels['btnSave'] ?></button>
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabPreview" role="tabpanel" aria-labelledby="liPreview">
                        <div class="row">
                            <div class="col-12 .overflow-auto" id="preview"></div>
                            <div class="col-12">
                                <button id="btnPreview" class="btn btn-sm btn-primary float-end"><?= $labels['btnRefresh'] ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mailAccount" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblNewMailAccount'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="form-group col-md-6">
                        <div id="sectionUser" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-regular fa-user"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idUser" name="elUser"
                                minlength="3" maxlength="50" required placeholder="<?= $labels['tblUsername'] ?>"
                                title="<?= $labels['tblUsername'] ?>">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div id="sectionPassword" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-lock"></i>
                            </span>
                            <input id="idPass" name="elPass" class="form-control form-control-sm" type="password"
                                placeholder="<?= $labels['tblPassword'] ?>" required="">
                            <button id="review" class="input-group-text ps-2">
                                <i class="fal fa-eye"></i>
                            </button>
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="form-group col-md-6">
                        <div id="sectionHost" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-thin fa-server"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idHost" name="elHost"
                                minlength="3" maxlength="50" required placeholder="<?= $labels['tblHost'] ?>"
                                title="<?= $labels['tblHost'] ?>">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div id="sectionHost" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-thin fa-globe"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idPort" name="elPort"
                                minlength="3" maxlength="5" required placeholder="<?= $labels['tblPort'] ?>"
                                title="<?= $labels['tblPort'] ?>">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblPortNumbers'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="form-group col-md-6">
                        <div id="sectionSmtp" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-shield-alt"></i>
                            </span>
                            <select class="form-control form-control-sm" id="idSmtpsecure" name="elSmtpsecure" required>
                                <option value="none"> <?= $labels['lblNone'] ?></option>
                                <option value="tls" selected><?= $labels['lblTLS'] ?></option>
                                <option value="ssl"><?= $labels['lblSSL'] ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div id="sectionSmtp" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-envelope"></i>
                            </span>
                            <select class="form-control form-control-sm" id="idProtocol" name="elProtocol" required>
                                <option value="smtp" selected><?= $labels['lblSMTP'] ?></option>
                                <option value="sendmail" disabled><?= $labels['lblSendMail'] ?></option>
                                <option value="qmail" disabled><?= $labels['lblQMAIL'] ?></option>
                                <option value="mail" disabled><?= $labels['lblMAIL'] ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="form-group col-md-6">
                        <div id="sectionReplyto" class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-envelope"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idReplyto" name="elReplyto"
                                required placeholder="<?= $labels['tblReplyto'] ?>"
                                title="<?= $labels['tblReplyto'] ?>">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div id="sectionOauthClientId" class="form-group col-md-6 d-none">
                        <div class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-hashtag"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idOauthClientId" name="elOauthClientId"
                                required placeholder="<?= $labels['tblOauthClientId'] ?>"
                                title="<?= $labels['tblOauthClientId'] ?>">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div id="sectionOauthClientSecret" class="form-group col-md-6 mt-3 d-none">
                        <div class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-key"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idOauthClientSecret" name="elOauthClientSecret"
                                required placeholder="<?= $labels['tblOauthClientSecret'] ?>"
                                title="<?= $labels['tblOauthClientSecret'] ?>">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div id="sectionOauthRefreshToken" class="form-group col-md-6 mt-3 d-none">
                        <div class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-sync"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idOauthRefreshToken" name="elOauthRefreshToken"
                                placeholder="<?= $labels['tblOauthRefreshToken'] ?>"
                                title="<?= $labels['tblOauthRefreshToken'] ?>">
                            <button type="button" id="btnGetOauthToken"
                                class="btn btn-sm btn-outline-secondary input-group-text"
                                title="Obtener Refresh Token via OAuth">
                                <i class="fal fa-key"></i>
                            </button>
                            <div class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div id="sectionOauthType" class="form-group col-md-6 mt-3 d-none">
                        <div class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-list-ul"></i>
                            </span>
                            <select class="form-control form-control-sm" id="idOauthType" name="elOauthType">
                                <option value="">-- Provider --</option>
                                <option value="oauth2-google">Google</option>
                                <option value="oauth2-yahoo">Yahoo</option>
                                <option value="oauth2-microsoft">Microsoft</option>
                                <option value="oauth2-azure">Azure</option>
                            </select>
                            <div class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                    <div id="sectionOauthTenantId" class="form-group col-md-6 mt-3 d-none">
                        <div class="input-group input-group-sm has-validation">
                            <span class="input-group-text">
                                <i class="fal fa-building"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="idOauthTenantId" name="elOauthTenantId"
                                placeholder="Tenant ID (common)" title="Tenant ID">
                            <div id="laNameFeedback" class="invalid-feedback">
                                <?= $labels['lblRequired'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container p-md-3">
                    <div class="row row-cols-4">
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="idSmtpauth" name="elSmtpauth">
                                <label class="form-check-label" for="idSmtpauth">
                                    <?= $labels['tblSmtpauth'] ?>
                                </label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="idOauth" name="elOauth">
                                <label class="form-check-label" for="idOauth">
                                    <?= $labels['tblOauth'] ?>
                                </label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="idProtected" name="elProtected">
                                <label class="form-check-label" for="idProtected">
                                    <?= $labels['tblProtected'] ?>
                                </label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="idDebug" name="elDebug">
                                <label class="form-check-label" for="idDebug">
                                    <?= $labels['tblDebug'] ?>
                                </label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="idEnabled" name="elEnabled">
                                <label class="form-check-label" for="idEnabled">
                                    <?= $labels['tblEnabled'] ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="accountSave" class="btn btn-sm btn-success">
                    <?= $labels['btnSave'] ?>
                </button>
                <button type="button" id="accountTestMail" class="btn btn-sm btn-primary">
                    <?= $labels['btnTestMail'] ?>
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">
                    <?= $labels['btnCancel'] ?>
                </button>
            </div>
        </div>
    </div>
</div>

<? require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/jquery-ui/jquery-ui.min.js"></script>
<script src="/lib/summernote/summernote-bs5.min.js"></script>
<?
if (file_exists(__ROOT__ . '/lib/datatables/i18n/' . str_replace('_', '-', $chrLocale) . '.min.js')) {
?>
    <script src="/lib/summernote/lang/summernote-<?= str_replace('_', '-', $chrLocale) ?>.min.js"></script>
    <script>
        var localeSN = chrLocale;
    </script>
<?
} else {
?>
    <script src="/lib/summernote/lang/summernote-<?= $chrLang ?>.min.js"></script>
    <script>
        var localeSN = chrLang;
    </script>
<?
}
?>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/adm/mailtemplates/mailTemplates.js'); ?>"></script>
<script src="<?= autoVer('/adm/mailaccounts/mailAccounts.js'); ?>"></script>

<!-- END PAGE SPECIFIC SCRIPTS -->
</body>

</html>