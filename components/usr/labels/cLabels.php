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
    'navLabels',
    'nteCreateError',
    'nteCreateSuccess',
    'nteDeleteError',
    'nteDeleteSuccess',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'lblEditLabel',
    'lblNewLabel',
    'lblRequired',
    'tblActions',
    'tblName',
    'tblDescription',
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$langs = $objLoc->selectLanguages()['data'];
?>
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/flag-icons/css/flag-icons.min.css" />

<!-- Modal -->
<div class="modal fade" id="modallabelsComponent" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-id="0">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="h1modalLabelCom"><?= $labels['lblNewLabel'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <input type="text" class="form-control form-control-sm" id="laNameLabel" name="laName" minlength="3" maxlength="25" required placeholder="<?= $labels['tblName'] ?>" title="<?= $labels['tblName'] ?>">
                        <div id="laNameFeedback" class="invalid-feedback">
                            <?= $labels['lblRequired'] ?>
                        </div>
                    </div>
                    <div>
                        <ul class="nav nav-tabs" id="langTab" role="tablist">
                            <?php
                            foreach ($langs as $key => $lang) {
                                $active = '';
                                if (!$key) {
                                    $active = ' active';
                                }
                            ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link<?= $active ?>" id="tab_<?= $lang['id'] ?>" data-bs-toggle="tab" data-bs-target="#laTab_<?= $lang['id'] ?>" type="button" role="tab" aria-controls="home" aria-selected="true">
                                        <span class="fi <?= $lang['flag'] ?>"></span>
                                    </button>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <?php
                            foreach ($langs as $key => $lang) {
                                $active = '';
                                if (!$key) {
                                    $active = ' show active';
                                }
                            ?>
                                <div class="tab-pane fade<?= $active ?>" id="laTab_<?= $lang['id'] ?>" role="tabpanel" aria-labelledby="tab_<?= $lang['id'] ?>">
                                    <div class="form-floating mt-2">
                                        <textarea class="form-control" placeholder="<?= $labels['tblDescription'] ?>" id="Compdescription_<?= $lang['id'] ?>" name="laDescription[<?= $lang['id'] ?>]"></textarea>
                                        <label for="floatingTextarea"><?= $labels['tblDescription'] ?></label>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="labelSaveComponent" class="btn btn-sm btn-primary"><?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>
<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/components/usr/labels/cLabels.js'); ?>"></script>
</body>

</html>