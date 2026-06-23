<?php
require_once __ROOT__ . '/components/usr/usrhead.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnEdit',
    'btnNew',
    'btnNo',
    'btnSave',
    'btnYes',
    'navCronJobs',
    'nteCreateError',
    'nteSyncSuccess',
    'nteSyncError',
    'nteCreateSuccess',
    'nteDeleteError',
    'lblSync',
    'nteDeleteSuccess',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'nteSuccess',
    'lblNewLabel',
    'lblRequired',
    'lblErrorsFound',
    'lblSyncStatus',
    'lblNewCronJob',
    'lblSyncStatus',
    'lblNote',
    'lblEditCronJob',
    'lblEnabled',
    'tblActions',
    'tblProtected',
    'tblmessage',
    'tblEnabled',
    'tblSchedule',
    'tblScript',
    'nteCheckboxSuccess'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$langs = $objLoc->selectLanguages()['data'];
?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />

<div class="" id="table">
    <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <table id="tableCronjobs" class="table table-striped nowrap"></table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCronjob" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblNewCronJob'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <input type="text" class="form-control form-control-sm" id="idScript" name="elScript" minlength="3"
                            maxlength="255" required placeholder="<?= $labels['tblScript'] ?>"
                            title="<?= $labels['tblScript'] ?>">
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <input type="text" class="form-control form-control-sm" id="idSchedule" name="elSchedule" minlength="3"
                            maxlength="255" required placeholder="<?= $labels['tblSchedule'] ?>"
                            title="<?= $labels['tblSchedule'] ?>">
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="idEnabled" name="elEnabled">
                            <label class="form-check-label" for="idEnabled"><?= $labels['lblEnabled'] ?></label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="idProtected" name="elProtected">
                            <label class="form-check-label" for="idProtected"><?= $labels['tblProtected'] ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cronjobSave" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary"
                    data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Status -->
<div class="modal fade" id="modalSync" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticStatus"><?= $labels['lblSyncStatus'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <!--<label><?= $labels['tblMessegeResult'] ?>:</label>-->
                        <textarea type="text" class="form-control form-control-sm" id="idMessegeResult" readonly
                            name="elMessegeResult" minlength="3" maxlength="50" required
                            placeholder="<?= $labels['tblmessage'] ?>" title="<?= $labels['tblmessage'] ?>"></textarea>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-8 col-lg" id="idSuccess">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="sync"
                    class="btn btn-sm btn-primary"> <i class="fas fa-sync-alt"></i>&nbsp;<?= $labels['lblSync'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary"
                    data-bs-dismiss="modal"><?= $labels['btnCancel'] ?>
                </button>
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
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/adm/cronjobs/cronjobs.js'); ?>"></script>
</body>

</html>
