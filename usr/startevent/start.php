<?
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';


$objLabel = new labels($_MYSQLI_);


$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnEdit',
    'btnDetails',
    'btnNew',
    'btnUpdate',
    'btnSave',
    'btnYes',
    'btnNo',
    'nteCreateError',
    'nteCreateSuccess',
    'nteDeleteError',
    'nteDeleteSuccess',
    'nteDeleteWarn',
    'nteError',
    'nteUpdateError',
    'nteUpdateSuccess',
    'navConfiguration',
    'lblNewLabel',
    'lblYear',
    'lblidentificationcard',
    'lblOrganizationName',
    'lblidentificationcard',
    'lblTypeCompany',
    'lblAddress',
    'tblPhone',
    'tblEmail',
    'socWebPage',
    'lblIdateofentry',
    'lblNewSettings',
    'lblRequired',
    'lblEnabled',
    'lblFrom',
    'lblEveryMonth',
    'lblMonths',
    'lblType',
    'btnNew',
    'lblColumns',
    'nteNotData',
    'lblMonthadded',
    'tblPhone',
    'navMonthsData',
    'navOverview',
    'nteStartTimeRunners',
    'lblIdateofentry',
    'lblName',
    'lblSelectMonths',
    'lblEditEvent',
    'lblEmail',
    'lblNewEvent',
    'btnExport',
    'lblRefence',
    'lblInputsRequired',
    'lblAplyReference',
    'lblVoucher',
    'lblSelect',
    'lblBooking',
    'lblAmount',
    'lblFilter',
    'lblChart',
    'lblCurrency',
    'navPayroll',
    'lblSearch',
    'lblSendMailsSuccess',
    'lblSendMailsError',
    'lblEvents',
    'lblCreaEvent',
    'navRegistrations',
    'lblCreaRoutes',
    'tblActions'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$setLabels = $objLabel->getPrefixedLabels('set', $chrLang);
?>
<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/2.3.2/datatables.min.css" />
<link rel="stylesheet" href="/lib/flatpickr/themes/<?= $selDark ? 'dark' : 'light'; ?>.css">
<?= $selDark ? '<link rel="stylesheet" href="/lib/flatpickr/themes/dark.css">' : ''; ?>
<link rel="stylesheet" type="text/css" href="/vendor/enyo/dropzone/dist/min/dropzone.min.css" />
<link rel="stylesheet" type="text/css" href="<?= autoVer('/usr/events/event.css'); ?>" />

<!-- Leaflet core -->

<link rel="stylesheet" href="/lib/flatpickr/flatpickr.min.css">

<div class="" id="table">
    <div class="p-4 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-StartEvent-tab" data-bs-toggle="tab" data-bs-target="#nav-StartEvent" type="button" role="tab" aria-controls="nav-StartEvent" aria-selected="true"><?= $labels['navOverview'] ?></button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane  fade show active" id="nav-StartEvent" role="tabpanel" aria-labelledby="nav-StartEvent-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tableStartEvent" class="table table-hover dataTable table-striped w-full no-footer"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<? require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/jquery-ui/jquery-ui.min.js"></script>
<script src="/lib/datatables/2.3.2/datatables.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/xlsx/xlsx.full.min.js"></script>
<script src="/lib/flatpickr/flatpickr.min.js"></script>
<?php if ($chrLang != 'en'): ?>
    <script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
<?php endif; ?>
<script src="/vendor/enyo/dropzone/dist/min/dropzone.min.js"></script>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer(url: '/usr/startevent/start.js'); ?>"></script>


</body>

</html>