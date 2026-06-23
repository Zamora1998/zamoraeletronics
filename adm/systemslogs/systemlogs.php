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
    'btnExport',
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
    'nteNotDataToExport',
    'nteUpdateInformation',
    'tblMethod',
    'tblActions',
    'lblNote',
    'lblIncompleteInfo',
    'lblDoubleClicJson',
    'lblSync',
    'lblEditCronJob',
    'lblGenerate',
    'lblEnabled',
    'lblAnyChangesRegistrered',
    'tblActions',
    'tblProtected',
    'tblmessage',
    'tblIp',
    'tblDate',
    'tblJsonData',
    'lblWriteTableName',
    'tblTables',
    'tblUsers',
    'tblUrl',
    'nteExportSuccess',
    'nteError',
    'tblSchedule',
    'lblDoubleAdd',
    'tblTable',
    'nteSystemLog',
    'tblScript',
    'nteCheckboxSuccess',
    'tblName'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$langs = $objLoc->selectLanguages()['data'];
$hasProtected = in_array('dev', $authParams);
?>

<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/2.3.2/datatables.min.css" />
<link rel="stylesheet" href="/lib/flatpickr/themes/<?= $selDark ? 'dark' : 'light'; ?>.css">
<?= $selDark ? '<link rel="stylesheet" href="/lib/flatpickr/themes/dark.css">' : ''; ?>

<div class="" id="table">
    <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-logs-tab" data-bs-toggle="tab" data-bs-target="#nav-logs" type="button" role="tab" aria-controls="nav-logs" aria-selected="true">Registros</button>
                <?php if ($hasProtected): ?>
                    <button class="nav-link" id="nav-logtypetables-tab" data-bs-toggle="tab" data-bs-target="#nav-logtypetables" type="button" role="tab" aria-controls="nav-logtypetables" aria-selected="false">Log Type Tables</button>
                    <button class="nav-link" id="nav-logtypes-tab" data-bs-toggle="tab" data-bs-target="#nav-logtypes" type="button" role="tab" aria-controls="nav-logtypes" aria-selected="false">Log Types</button>
                <?php endif; ?>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <!-- Tab 1: Registros -->
            <div class="tab-pane fade show active" id="nav-logs" role="tabpanel" aria-labelledby="nav-logs-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-md-3 mb-2">
                        <select id="filterLogType" class="form-select" style="width: 100%;">
                            <option value=""><?= $labels['tblActions'] ?? 'All' ?></option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table id="tableSystemLogs" class="table table-striped nowrap"></table>
                    </div>
                </div>
            </div>
            <?php if ($hasProtected): ?>
                <div class="tab-pane fade" id="nav-logtypetables" role="tabpanel" aria-labelledby="nav-logtypetables-tab" tabindex="0">
                    <div class="row mt-2">
                        <div class="col-12">
                            <table id="tableLogTypeTables" class="table table-striped nowrap"></table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-logtypes" role="tabpanel" aria-labelledby="nav-logtypes-tab" tabindex="0">
                    <div class="row mt-2">
                        <div class="col-12">
                            <table id="tableLogTypes" class="table table-striped nowrap"></table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal LogType -->
<div class="modal fade" id="modalLogType" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalLogTypeLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLogTypeLabel"><?= $labels['btnNew'] ?? 'Log Type' ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="logTypeName" class="form-label"><?= $labels['tblName'] ?></label>
                        <input type="text" id="logTypeName" class="form-control" placeholder="Log Type Name">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="logTypeSave" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?? 'Guardar' ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?? 'Cancelar' ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal LogTypeTables -->
<div class="modal fade" id="modalLogTypeTables" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalLogTypeTablesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLogTypeTablesLabel"><?= $labels['btnNew'] ?? 'Añadir Tablas a Log Type' ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="selectLogTypeToAdd" class="form-label">Log Type</label>
                        <select id="selectLogTypeToAdd" class="form-select" style="width: 100%;"></select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="inputMultipleTables" class="form-label"><?= $labels['tblTables'] ?></label>
                        <select id="inputMultipleTables" class="form-select w-100" multiple="multiple"></select>
                        <small class="form-text text-muted"><?= $labels['lblWriteTableName'] ?></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="logTypeTablesSave" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?? 'Guardar' ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?? 'Cancelar' ?></button>
            </div>
        </div>
    </div>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/datatables/2.3.2/datatables.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/flatpickr/flatpickr.min.js"></script>
<script src="/lib/exceljs/exceljs.min.js"></script>
<?php if ($chrLang != 'en'): ?>
    <script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
<?php endif; ?>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/adm/systemslogs/systemslogs.js'); ?>"></script>
</body>

</html>