<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';

$labelSels = array(
    'btnCancel',
    'btnNew',
    'btnNo',
    'btnSave',
    'btnYes',
    'btnDelete',
    'btnEdit',
    'nteCreateError',
    'nteCreateSuccess',
    'nteDeleteError',
    'nteDeleteSuccess',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'nteCheckboxSuccess',
    'lblContentText',
    'lblDate',
    'lblDelete',
    'lblDescription',
    'lblDropFilesHere',
    'lblEdit',
    'lblEditLogo',
    'lblError',
    'lblErrorSave',
    'lblfileUploadFailed',
    'lblNoIcon',
    'lblText',
    'lblOrClick',
    'lblSaveConfirm',
    'lblSelect',
    'lblSelectCategorie',
    'lblfileUploadFailed',
    'lblSettings',
    'lblPassword',
    'lblEnabled',
    'lblNewSettings',
    'lblSaveComplete',
    'lblRequired',
    'lblSettings',
    'lblNewSetting',
    'lblEditSetting',
    'nteFieldReqired',
    'navSettings',
    'tblActions',
    'tblKey',
    'tblEnd',
    'tblStart',
    'tblName',
    'tblOwner',
    'tblType',
    'tblValue',
);

$labels = $objLabel->getLabels($labelSels, $chrLang);
$setLabels = $objLabel->getPrefixedLabels('set', $chrLang);

?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/colorpicker/spectrum.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/jquery-nestable/jquery.nestable.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/bootstrap-datepicker/css/bootstrap-datepicker.standalone.min.css" />
<link rel="stylesheet" type="text/css" href="<?= autoVer('/adm/settings/css/settings.css'); ?>" />
<link rel="stylesheet" type="text/css" href="/vendor/enyo/dropzone/dist/min/dropzone.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />


<div class="" id="table">
    <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <table id="tableSettings" class="table table-striped nowrap"></table>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="modalSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $labels['lblNewSettings'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="seModal">
                <div class="form-group py-2">
                    <div id="sectionKey" class="input-group input-group-sm has-validation">
                        <span class="input-group-text">
                            <?= $labels['tblKey'] ?>
                        </span>
                        <input type="text" maxlength="20" class="form-control form-white" id="settingsKey" name="elkey" value="" />
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div id="sectionNew" class="input-group input-group-sm has-validation">
                        <span class="input-group-text">
                            <?= $labels['tblType'] ?>
                        </span>
                        <select title="" id="selectSettingsType" name="elselectSettingsType" class="form-control form-white" data-search="true" value="" data-placeholder=<?= $labels['lblSelect'] ?>>
                        </select>
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div id="sectionText" class="input-group input-group-sm has-validation">
                        <span class="input-group-text">
                            <?= $labels['tblValue'] ?>
                        </span>
                        <input type="text" maxlength="255" class="form-control form-white" id="settingsText" name="elvalue" placeholder="<?= $labels['lblText'] ?>" value="" />
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div id="sectionCheck" class="input-group input-group-sm">
                        <span class="input-group-text">
                            <?= $labels['tblValue'] ?>
                        </span>
                        <span class="input-group-text">
                            <input type="checkbox" class="form-check-input form-white mt-0" id="settingCheck" name="elvalue" style="margin-top: .75rem;" />
                        </span>
                    </div>
                </div>

                <div class="form-group py-2">
                    <div id="sectionPassword" class="input-group input-group-sm has-validation">
                        <span class="input-group-text">
                            <i class="fal fa-lock"></i>
                        </span>
                        <input id="repass" name="elvalue" class="form-control form-control-sm" type="password" placeholder="<?= $labels['lblPassword'] ?>" required="">
                        <button id="review" class="input-group-text ps-2">
                            <i class="fal fa-eye"></i>
                        </button>
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div id="sectionColor" class="input-group input-group-sm has-validation">
                        <span class="input-group-text">
                            <?= $labels['tblValue'] ?>
                        </span>
                        <input type="text" class="color-picker form-control" id="settingsColor" value="">
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                </div>
                <div class="form-group py-2">
                    <div id="sectionDate" class="input-group date">
                        <div class="col mb-3">
                            <div class="input-group date" data-provide="datepicker">
                                <input type="text" class="form-control form-control-sm" id="calenStart" name="elvalue" placeholder="<?= $labels['lblSelect'] ?>" title="<?= $labels['tblStart'] ?>">
                                    <span class="input-group-text input-group-addon">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                <div id="laNameFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="sectionFile" class="form-group row">
                    <label class="col-2 form-control-label" for="dropSettings" id="addLogo">
                        <?= $labels['tblValue'] ?>
                    </label>
                    <div class="form-group col-10 mb-0" id="divDropSettings">
                        <form class="dropzone dz-clickable" id="dropSettings">
                        </form>
                    </div>
                    <div id="template-container" class="d-none">
                        <div class="dz-preview dz-complete dz-image-preview">
                            <div class="dz-image">
                                <img data-dz-thumbnail>
                            </div>
                            <div class="dz-details">
                                <div class="dz-size" data-dz-size></div>
                                <div class="dz-filename">
                                    <span data-dz-name></span>
                                </div>
                            </div>
                            <div class="dz-progress">
                                <span class="dz-upload" data-dz-uploadprogress></span>
                            </div>
                            <div class="dz-error-message">
                                <span data-dz-errormessage></span>
                            </div>
                            <div class="dz-success-mark">
                                <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                                    <title>Check</title>
                                    <defs></defs>
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                        <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>
                                    </g>
                                </svg>
                            </div>
                            <div class="dz-error-mark">
                                <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                                    <title><?= $labels['lblError'] ?></title>
                                    <defs></defs>
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                        <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475">
                                            <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup"></path>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <a class="dz-remove bg-danger" href="javascript:undefined;" data-dz-remove>
                                <i class="fa fa-trash icon-white"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="settingsSave" type="button" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                <button id="cancelSettings" type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
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
<script src="/lib/colorpicker/spectrum.js"></script>
<script src="/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/vendor/enyo/dropzone/dist/min/dropzone.min.js"></script>

<?php
if (file_exists(__ROOT__ . '/lib/bootstrap-datepicker/locales/bootstrap-datepicker.' . str_replace('_', '-', $chrLocale) . '.min.js')) {
?>
<script src="/lib/bootstrap-datepicker/locales/bootstrap-datepicker.<?= str_replace('_', '-', $chrLocale) ?>.min.js"></script>
<?php
} elseif (file_exists(__ROOT__ . '/lib/bootstrap-datepicker/locales/bootstrap-datepicker.' . $chrLang . '.min.js')) {
?>
<script src="/lib/bootstrap-datepicker/locales/bootstrap-datepicker.<?= $chrLang ?>.min.js"></script>
<?php
} elseif (file_exists(__ROOT__ . '/lib/colorpicker/i18n/jquery.spectrum-' . $chrLang . '.js')) {
?>
<script src="/lib/colorpicker/i18n/jquery.spectrum-<?= $chrLang ?>.min.js"></script>
<?php
}
if (file_exists(__ROOT__ . '/lib/colorpicker/i18n/jquery.spectrum-' . $chrLang . '.js')) {
?>
<script src="/lib/colorpicker/i18n/jquery.spectrum-<?= $chrLang; ?>.js"></script>
<?php
}
?>

<script>
    var labels = <?= json_encode($labels); ?>;
    var setLabels = <?= json_encode($setLabels); ?>;
</script>
<script src="<?= autoVer('/adm/settings/settings.js'); ?>"></script>



</body>

</html>
