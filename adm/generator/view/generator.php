<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/components/usr/labels/cLabels.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnDetailBooking',
    'btnDetails',
    'btnEdit',
    'btnExpected',
    'btnExport',
    'btnNew',
    'btnNews',
    'btnNo',
    'btnSave',
    'btnYes',
    'lblAcceses',
    'lblAdministration',
    'lblAssets',
    'lblClean',
    'lblController',
    'lblControllers',
    'lblCustomAlert',
    'lblCustomRoute',
    'lblCustomRouteC',
    'lblDataModels',
    'lblExample',
    'lblGenerateStructure',
    'lblGeneratedfiles',
    'lblModel',
    'lblModuleName',
    'lblNameModuleInput',
    'lblProjectOrigin',
    'lblRefence',
    'lblRelativeRoute',
    'lblRequieredFields',
    'lblRequired',
    'lblRequiredFields',
    'lblRevenue',
    'lblSearch',
    'lblSelect',
    'lblSelectRoute',
    'lblStructurePreview',
    'lblStructuregenerated',
    'lblSuccessfullOperation',
    'lblView',
    'lblViewfiles',
    'lblWarning',
    'tblIspublic',
    'tblUser'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$setLabels = $objLabel->getPrefixedLabels('set', $chrLang);
$hasProtected = in_array('dev', $authParams);
$basePathOptions = [
    ''       => '-- ' . $labels['lblAdministration'] . ' --',
    'adm'    => 'adm/ (' . $labels['lblAdministration'] . ')',
    'usr'    => 'usr/ (' . $labels['tblUser'] . ')',
    'pub'    => 'pub/ (' . $labels['tblIspublic'] . ')',
    '.'      => $labels['lblProjectOrigin'],
    'custom' => '🔧 ' . $labels['lblCustomRouteC'] . '...',
];
?>
<link rel="stylesheet" type="text/css" href="<?= autoVer('/adm/generator/assets/css/generator.css') ?>" />
<?php if ($hasProtected): ?>
    <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <div class="container-fluid mt-4 pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Main Content -->
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4">
                            <form id="generatorForm" class="container">
                                <div class="row">
                                    <div class="col-md-6">

                                        <!-- Module Name -->
                                        <div class="mb-4">
                                            <label for="moduleName" class="form-label">
                                                <i class="fas fa-cube text-primary"></i>
                                                <?= $labels['lblModuleName'] ?>
                                            </label>
                                            <input type="text" id="moduleName" name="moduleName" class="form-control" placeholder="<?= $labels['lblExample'] ?>: products, invoices, reports..." autocomplete="off">
                                            <div id="laNameFeedback" class="invalid-feedback">
                                                <?= $labels['lblRequired'] ?>
                                            </div>
                                            <small class="form-text text-muted">
                                                <?= $labels['lblNameModuleInput'] ?>
                                            </small>
                                        </div>

                                        <!-- Base Path -->
                                        <div class="mb-4">
                                            <label for="basePath" class="form-label">
                                                <i class="fas fa-folder-tree text-primary"></i>
                                                <?= $labels['lblRelativeRoute'] ?>
                                            </label>
                                            <select id="basePath" name="basePath" class="form-select">
                                                <?php foreach ($basePathOptions as $value => $label): ?>
                                                    <option value="<?= $value ?>"><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div id="laNameFeedback" class="invalid-feedback">
                                                <?= $labels['lblRequired'] ?>
                                            </div>
                                        </div>

                                        <!-- Custom Path Input -->
                                        <div class="mb-4" id="customPathGroup" style="display: none;">
                                            <label for="customPath" class="form-label">
                                                <i class="fas fa-pen text-primary"></i>
                                                <?= $labels['lblCustomRouteC'] ?>
                                            </label>
                                            <input
                                                type="text"
                                                id="customPath"
                                                name="customPath"
                                                class="form-control"
                                                placeholder="<?= $labels['lblExample'] ?>: adm/subdir o usr/modules">
                                            <div id="laNameFeedback" class="invalid-feedback">
                                                <?= $labels['lblRequired'] ?>
                                            </div>
                                            <small class="form-text text-muted"><?= $labels['lblExample'] ?>: adm/modules, usr/dashboard, etc.</small>
                                        </div>

                                        <!-- Structure Options -->
                                        <div class="mb-4">
                                            <label class="form-label section-label">
                                                <i class="fas fa-layer-group text-primary"></i>
                                                <?= $labels['lblStructuregenerated'] ?>
                                            </label>
                                            <div class="checkbox-group">
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" class="form-check-input" name="createView" id="createView" checked>
                                                    <label class="form-check-label" for="createView">
                                                        <strong><?= $labels['lblView'] ?></strong> - <?= $labels['lblViewfiles'] ?> (PHP)
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" class="form-check-input" name="createModel" id="createModel" checked>
                                                    <label class="form-check-label" for="createModel">
                                                        <strong><?= $labels['lblModel'] ?></strong> - <?= $labels['lblDataModels'] ?> (PHP)
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" class="form-check-input" name="createController" id="createController" checked>
                                                    <label class="form-check-label" for="createController">
                                                        <strong><?= $labels['lblController'] ?></strong> - <?= $labels['lblControllers'] ?> (PHP)
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" class="form-check-input" name="createAssets" id="createAssets" checked>
                                                    <label class="form-check-label" for="createAssets">
                                                        <strong><?= $labels['lblAssets'] ?></strong> - CSS & JavaScript
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">

                                        <!-- Preview Section -->
                                        <div class="preview-section mb-4">
                                            <h5 class="mb-3 text-center">
                                                <i class="fas fa-eye text-primary"></i> <?= $labels['lblStructurePreview'] ?>
                                            </h5>

                                            <div id="structurePreview" class="structure-tree p-3 bg-light rounded">
                                                <div class="empty-state text-center text-muted">
                                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                    <p><?= $labels['lblRequieredFields'] ?></p>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="d-flex gap-2 justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary" id="resetBtn">
                                        <i class="fas fa-undo"></i>
                                        <?= $labels['lblClean'] ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="generateBtn">
                                        <i class="fas fa-magic"></i>
                                        <?= $labels['lblGenerateStructure'] ?>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>

                    <!-- Result Section -->
                    <div id="resultSection" class="mt-4" style="display: none;">
                        <div class="card shadow-lg border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 id="resultTitle" class="mb-0"></h5>
                                    <button type="button" class="btn-close" id="closeResult"></button>
                                </div>
                                <div id="resultContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="resultModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" data-id="0">
        <div class="modal-dialog">
            <div class="modal-content">
                <div id="modalHeader" class="modal-header">
                    <h5 class="modal-title">
                        <i id="modalIcon"></i>
                        <span id="modalTitle"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- <p id="resultMessage" class="font-weight-bold"></p> -->
                    <h6><b><?= $labels['lblGeneratedfiles']  ?>:</b></h6>
                    <ul id="resultFiles" class="list-group mb-3"></ul>
                    <h6><b><?= $labels['lblAcceses'] ?>:</b></h6>
                    <ul id="accessStatus" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary"
                        data-bs-dismiss="modal"><?= $labels['bntClose'] ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/adm/generator/assets/js/generator.js'); ?>"></script>
</body>

</html>