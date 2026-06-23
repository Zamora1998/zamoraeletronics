<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/model/modLocales.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'accAdmin',
    'accDev',
    'btnCancel',
    'btnDarkMode',
    'btnSendPasswordResetEmail',
    'btnUpdate',
    'btnResetPassword',
    'frmChangePassword',
    'frmConfirmPassword',
    'frmCurrentPassword',
    'frmEmail',
    'frmFirstname',
    'frmLastname',
    'frmNewPassword',
    'frmSignature',
    'lblLoading',
    'lblResetPassword',
    'lblSignIn',
    'lblSignUp',
    'nteCoudNotBeUpdated',
    'nteCurrentPasswordIncorre',
    'nteDuplicateEntry',
    'nteErrorSave',
    'nteFields',
    'nteFieldReqired',
    'nteInsertAnEmail',
    'nteLanguages',
    'nteSaveSuccess',
    'nteSecurePassword',
    'ntePWNotSame',
    'ntePWNotStrongEnough',
    'ntePWResetError',
    'ntePWResetSoccess',
    'ntePWSame',
    'ntePWStrongEnough',
    'pwrFail',
    'pwrInternalError',
    'pwrSuccess',
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$locTree = $objLoc->selectTree()['data'];

?>
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/password-strength-meter/password.min.css" />

<div class="container-fluid" id="table">
    <div class="p-4 align-items-start rounded-3 border shadow-lg">
        <div class="col-12">
            <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        </div>
        <div class="row">
            <div class="col-md-6" id="Perfil">
                <div class="form-group py-2">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-user"></i>
                        </span>
                        <input id="First" name="first" class="form-control form-control-sm" type="text" placeholder="<?= $labels['frmFirstname'] ?>" required>
                        <div class="invalid-feedback"><?= $labels['nteFieldReqired'] ?></div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-user"></i>
                        </span>
                        <input id="Last" name="last" class="form-control form-control-sm" type="text" placeholder="<?= $labels['frmLastname'] ?>" required>
                        <div class="invalid-feedback"><?= $labels['nteFieldReqired'] ?></div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-at"></i>
                        </span>
                        <input id="Email" name="email" class="form-control form-control-sm" type="text" placeholder="<?= $labels['frmEmail'] ?>" required>
                        <div class="invalid-feedback"><?= $labels['nteFieldReqired'] ?></div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-globe"></i>
                        </span>
                        <select id="langId" class="form-control form-control-sm selectLang" name="lang_id" data-placeholder="<?= $labels['nteLanguages'] ?>">
                            <option value="" disabled selected><?= $labels['nteLanguages'] ?></option>
                        </select>
                    </div>
                </div>
                <div class="d-grid gap-1 d-md-flex justify-content-md-end mt-3">
                    <button id="updateProfile" type="button" class="btn btn-sm btn-primary me-1" disabled><?= $labels['btnUpdate'] ?></button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fal fa-sun"></i>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
                    </div>
                    <i class="fal fa-moon"></i>
                </div>
            </div>
            <div class="col-md-6" id="Password">
                <h5><b><?= $labels['frmChangePassword'] ?></b></h5>
                <div class="form-group py-2 mt-4">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-lock"></i>
                        </span>
                        <input id="repass" name="currentPassword" class="form-control form-control-sm" type="password" placeholder="<?= $labels['frmCurrentPassword'] ?>" required>
                        <button id="review" class="input-group-text ps-2">
                            <i class="fal fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-lock"></i>
                        </span>
                        <input id="repass1" name="newPassword" class="form-control form-control-sm" type="password" placeholder="<?= $labels['frmNewPassword'] ?>" required>
                        <button id="review1" class="input-group-text ps-2">
                            <i class="fal fa-eye"></i>
                        </button>
                        <div class="repass1 invalid-feedback mb-1"><?= $labels['ntePWNotStrongEnough'] ?></div>
                        <div class="repass1 valid-feedback mb-1"><?= $labels['ntePWStrongEnough'] ?></div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div class="input-group has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-lock"></i>
                        </span>
                        <input id="repass2" name="newPassword2" class="form-control form-control-sm" type="password" placeholder="<?= $labels['frmConfirmPassword'] ?>" required>
                        <button id="review2" class="input-group-text ps-2">
                            <i class="fal fa-eye"></i>
                        </button>
                        <div class="repass2 invalid-feedback mb-1"><?= $labels['ntePWNotSame'] ?></div>
                        <div class="repass2 valid-feedback mb-1"><?= $labels['ntePWSame'] ?></div>
                    </div>
                </div>
                <div class="d-grid gap-1 d-md-flex justify-content-md-end mt-3">
                    <button id="updateChPass" type="button" class="btn btn-sm btn-primary me-1"><?= $labels['btnUpdate'] ?></button>
                    <button id="cancelChPass" type="button" class="btn btn-sm btn-secondary"><?= $labels['btnCancel'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/password-strength-meter/password.min.js"></script>

<script>
    var locTree = <?= json_encode($locTree); ?>;
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/usr/profile/profile.js'); ?>"></script>

<!-- END PAGE SPECIFIC SCRIPTS -->
</body>

</html>
