<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/routes/modRoutes.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnEdit',
    'btnNew',
    'btnNo',
    'btnSave',
    'btnAccess',
    'btnYes',
    'navRoutes',
    'navConfiguration',
    'nteCreateError',
    'nteCreateSuccess',
    'nteDeleteError',
    'nteDeleteSuccess',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'nteCheckboxSuccess',
    'lblRequired',
    'lblEnabled',
    'tblName',
    'tblParentCategory',
    'lblNewRoute',
    'lblEditRoute',
    'lblCategory',
    'lblMethod',
    'lblSelect',
    'lblPublic',
    'lblAllusers',
    'lblNumber',
    'tblActions',
    'tblLabelname',
    'tblChildCategory',
    'tblIcon',
    'tblUrl',
    'tblType',
    'tblPosition',
    'tblFile',
    'tblMethod',
    'tblIspublic',
    'tblIsalluser',
    'tblCategory',
    'lblEditAccess',
    'lblNewAccess',
    'lblNewCategory',
    'lblName',
    'lblNewSubCategory',
    'lblEditSubCategory',
    'tblParentCategory',
    'lblEditCategory',
    'lblTypeAccess',
    'lblSubCategory',
    'lblParameter',
    'lblNewAcess',
    'lblEditAccess'

);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$langs = $objLoc->selectLanguages()['data'];

$objRoutes = new route($_MYSQLI_);
$objRoutes->setId(0);
$objRoutes->setLanguageId($chrLang);
$routes = $objRoutes->selectAccessDetails()['data'];
$routesNames = $objRoutes->selectDetailsRoutes()['data'];
?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/jquery-nestable/jquery.nestable.min.css" />
<link rel="stylesheet" type="text/css" href="<?= autoVer('/adm/routes/routes.css') ?>" />

<div class="" id="table">
    <div class="p-4 pb-0 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-routes-tab" data-bs-toggle="tab" data-bs-target="#nav-routes"
                    type="button" role="tab" aria-controls="nav-routes"
                    aria-selected="true"><?= $labels['navRoutes'] ?></button>
                <button class="nav-link" id="nav-category-tab" data-bs-toggle="tab" data-bs-target="#nav-category"
                    type="button" role="tab" aria-controls="nav-category"
                    aria-selected="true"><?= $labels['lblCategory'] ?></button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active pt-3" id="nav-routes" role="tabpanel" aria-labelledby="nav-routes-tab"
                tabindex="0">
                <table id="tableRoutes" class="table table-striped nowrap"></table>
            </div>
            <div class="tab-pane fade pt-3" id="nav-category" role="tabpanel" aria-labelledby="nav-category-tab"
                tabindex="0">
                <div class="row">
                    <div class="input-group"><button id="categoryCreate" type="button" data-id="0"
                            class="btn btn-sm btn-success me-1" data-id="0"><?= $labels['btnNew'] ?></button></div>
                    <div id="category" class="col-6">
                        <div id="categories" class="dd"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Category -->
        <div class="modal fade" id="modalCategories" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticAddCategory"><?= $labels['lblNewCategory'] ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <input type="text" class="form-control form-control-sm" id="idCategoryName"
                                    name="elCategoryName" minlength="3" maxlength="50" required
                                    placeholder="<?= $labels['lblName'] ?>" title="<?= $labels['lblNewCategory'] ?>">
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
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="categorySave"
                            class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                        <button type="button" class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalRoutes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblNewRoute'] ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <input type="text" class="form-control form-control-sm" id="idLabelname"
                                    name="elLabelname" minlength="3" maxlength="50" required
                                    placeholder="<?= $labels['tblLabelname'] ?>" title="<?= $labels['tblLabelname'] ?>">
                                <div id="laNameFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <input type="text" class="form-control form-control-sm" id="idType" name="elType"
                                    minlength="3" maxlength="50" required placeholder="<?= $labels['tblType'] ?>"
                                    title="<?= $labels['tblType'] ?>">
                                <div id="laNameFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <input type="text" class="form-control form-control-sm" id="idIcon" name="elIcon"
                                    minlength="3" maxlength="100" required placeholder="<?= $labels['tblIcon'] ?>"
                                    title="<?= $labels['tblIcon'] ?>">
                                <div id="laNameFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div id="additionalNumbers">
                                    <div class="mb-2">
                                        <input id='numberInput' name="elPosition" type="number" class="form-control form-control-sm" min="0" max="250" placeholder="<?= $labels['tblPosition'] ?>" required value="1">
                                        <div id="laNameFeedback" class="invalid-feedback">
                                            <?= $labels['lblRequired'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <input type="text" class="form-control form-control-sm" id="idUrl" name="elUrl"
                                    minlength="3" maxlength="100" required placeholder="<?= $labels['tblUrl'] ?>"
                                    title="<?= $labels['tblUrl'] ?>">
                                <div id="laNameFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <input type="text" class="form-control form-control-sm" id="idFile" name="elFile"
                                    minlength="3" maxlength="100" required placeholder="<?= $labels['tblFile'] ?>"
                                    title="<?= $labels['tblFile'] ?>">
                                <div id="laNameFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <select class="form-control form-control-sm" id="idMethod" name="elMethod" required>
                                    <option value="" disabled selected><?= $labels['lblMethod'] ?></option>
                                </select>
                                <div id="dropdownFeedback" class="invalid-feedback">
                                    <?= $labels['lblRequired'] ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div id="dropdownCat" class="dropdown dropend">
                                </div>
                            </div>
                            <div class="col-12 mb-1">
                                <div class="row">
                                    <div id='containerSelect2Childs' class="col-5 mb-1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="idAllusers" name="elAllusers">
                                    <label class="form-check-label" for="idAllusers">
                                        <?= $labels['lblAllusers'] ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="idPublic" name="elPublic">
                                    <label class="form-check-label" for="idPublic">
                                        <?= $labels['lblPublic'] ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="routeSave" class="btn btn-sm btn-primary">
                            <?= $labels['btnSave'] ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <?= $labels['btnCancel'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal  2-->
        <div class="modal fade" id="modalAccess" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $labels['lblAccess'] ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        foreach ($routes as $key => $route) {
                            $stripe = '';
                            if ($key % 2) {
                                $stripe = ' bg-light';
                            }
                        ?>
                            <div class="row py-1<?= $stripe ?>">
                                <div class="col">
                                    <label class="mt-1" for="idParameter"><?= $route['name'] ?></label>
                                </div>
                                <div class="col">
                                    <input id="acId_<?= $route['id'] ?>" name="accessIds" type="hidden"
                                        value="<?= $route['id'] ?>">
                                    <input id="acParam_<?= $route['id'] ?>" name="param" type="text"
                                        class="form-control form-control-sm" maxlength="50" required
                                        placeholder="<?= $labels['lblParameter'] ?>">
                                </div>
                                <div class="col-1">
                                    <input id="acOptional_<?= $route['id'] ?>" name="optional" type="checkbox"
                                        class="form-check-input mt-2" data-id="<?= $route['id'] ?>">
                                </div>
                                <div class="col-1">
                                    <input id="acRequired_<?= $route['id'] ?>" name="required" type="checkbox"
                                        class="form-check-input form-check-input-success mt-2"
                                        data-id="<?= $route['id'] ?>">
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="AccessSave"
                            class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                        <button type="button" class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
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
        <script src="/lib/jquery-nestable/jquery.nestable.min.js"></script>
        <script>
            var labels = <?= json_encode($labels); ?>;
            var routesNames = <?= json_encode($routesNames); ?>;
        </script>
        <script src="<?= autoVer('/adm/routes/routes.js'); ?>"></script>
        <script src="<?= autoVer('/adm/routes/category.js'); ?>"></script>
        </body>

        </html>
