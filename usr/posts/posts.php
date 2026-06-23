<?
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/usr/posts/modPosts.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnCancel',
    'btnCopyLink',
    'btnDelete',
    'btnEdit',
    'btnNew',
    'btnNo',
    'btnSave',
    'btnYes',
    'nteCreateError',
    'nteCreateSuccess',
    'nteLinkCopied',
    'nteDeleteError',
    'nteDeleteSuccess',
    'btnAssignRoutes',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'lblEditPost',
    'lblNewPost',
    'lblPostName',
    'tblActions',
    'tblLink',
    'tblName',
    'tblEnabled',
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$objdistances = new posts();
$distances = $objdistances->selectDistances($chrLang)['data'];

//$objPosts = new posts($_MYSQLI_);
//$objPosts->setLanguageId($chrLang);
//$postStatuses = $objPosts->selectPostStatuses()['data'];

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

<div class="" id="table">
    <div class="row p-4 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <table id="tablePosts" class="table table-striped nowrap"></table>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modalPost" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblEditPost'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <input type="text" class="form-control form-control-sm" id="grName" name="grName" minlength="3" maxlength="25" placeholder="<?= $labels['tblName'] ?>" title="<?= $labels['tblName'] ?>">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="grEnabled" name="grEnabled">
                    <label class="form-check-label" for="flexCheckDefault"><?= $labels['tblEnabled'] ?></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="postSave" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssignRoutes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="h1confIRFS" aria-hidden="true" data-id="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="h1modalConRegions"><?= $labels['btnNew'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Nombre anterior -->
   
                </div>
                <div class="row">
                    <!-- Nombre anterior -->
                    <div id='containerDistances' class="col-12 mb-3">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveAssignRoutes" class="btn btn-sm btn-primary">&nbsp;<?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>

<? require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.min.js"></script>
<script src="/lib/datatables/Select-1.6.0/js/dataTables.select.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/datatables/Buttons-2.3.4/js/dataTables.buttons.min.js"></script>
<script src="/lib/datatables/Buttons-2.3.4/js/buttons.dataTables.min.js"></script>
<script src="/lib/datatables/JSZip-2.5.0/jszip.min.js"></script>
<script src="/lib/datatables/Buttons-2.3.4/js/buttons.html5.min.js"></script>
<script src="/lib/datatables/Buttons-2.3.4/js/buttons.colVis.min.js"></script>


        <script>
            var labels = <?= json_encode($labels); ?>;
            var distances = <?= json_encode($distances); ?>;
        </script>
        <script src="<?= autoVer('/usr/posts/posts.js'); ?>"></script>
    </body>

</html>