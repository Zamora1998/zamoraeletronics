<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = [
    'lblDownload',
    'lblDownloadError',
    'lblDownloadSuccess',
    'lblDownloading',
    'lblGeneratedownload'
];
$labelsDownload = $objLabel->getLabels($labelSels, $chrLang);
$langs = $objLoc->selectLanguages()['data'];
?>

<!-- CSS -->
<link rel="stylesheet" type="text/css" href="<?= '/components/usr/download/download.css'; ?>" />

<!-- Modal of the download -->
<div class="modal fade" id="modaldownload" tabindex="-1" aria-labelledby="modaldownload" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content  custom-download-size text-center p-4">
            <div id="icondownload" class="spinner-download"></div>
            <p id="messagedownload"></p>
        </div>
    </div>
</div>

<!-- JS -->
<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script>
    var labelsDownload = <?= json_encode($labelsDownload); ?>;
</script>
<script src="<?= autoVer('/components/usr/download/download.js'); ?>"></script>