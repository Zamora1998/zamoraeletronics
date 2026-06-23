<?
require_once __ROOT__ . '/components/usr/usrhead.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = [
    'btnEncrypt',
    'btnDecrypt',
    'btnPassword',
    'lblHash',
    'lblRequired',
    'lblKey',
    'lblPlainText',
];
$labels = $objLabel->getLabels($labelSels, $chrLang);
?>
<div class="" id="page">
    <div class="row p-4 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <form id="crypt">
            <div class="mb-3">
                <label class="form-label" for="crPlain"><?= $labels['lblPlainText'] ?></label>
                <input class="form-control form-control-sm" id="crPlain" name="plain" type="text" placeholder="<?= $labels['lblPlainText'] ?>" data-sb-validations="required" />
                <div class="invalid-feedback" data-sb-feedback="lblPlainText:required"><?= $labels['lblPlainText'] ?> <?= $labels['lblRequired'] ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="crKey"><?= $labels['lblKey'] ?></label>
                <input class="form-control form-control-sm" id="crKey" name="key" type="text" placeholder="<?= $labels['lblKey'] ?>" data-sb-validations="required" />
                <div class="invalid-feedback" data-sb-feedback="lblKey:required"><?= $labels['lblKey'] ?> <?= $labels['lblRequired'] ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="crHash"><?= $labels['lblHash'] ?></label>
                <textarea class="form-control form-control-sm" id="crHash" name="hash" type="text" placeholder="<?= $labels['lblHash'] ?>" style="height: 10rem;" data-sb-validations="required"></textarea>
                <div class="invalid-feedback" data-sb-feedback="lblHash:required"><?= $labels['lblHash'] ?> <?= $labels['lblRequired'] ?></div>
            </div>
            <div class="d-flex justify-content-end">
                <button id="crencrypt" class="btn btn-sm btn-success me-1"><?= $labels['btnEncrypt'] ?></button>
                <button id="crdecrypt" class="btn btn-sm btn-success me-1"><?= $labels['btnDecrypt'] ?></button>
                <button id="crpwcrypt" class="btn btn-sm btn-success me-1"><?= $labels['btnPassword'] ?></button>
            </div>
        </form>
    </div>
</div>
<? require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/adm/crypt/crypt.js'); ?>"></script>
</body>

</html>