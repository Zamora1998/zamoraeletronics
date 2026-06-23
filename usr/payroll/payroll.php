<?
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/usr/payroll/modpayroll.php';

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
    'lblColumns',
    'nteNotData',
    'lblMonthadded',
    'tblPhone',
    'navMonthsData',
    'lblIdateofentry',
    'lblName',
    'lblSelectMonths',
    'lblEmail',
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
    'tblActions'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$setLabels = $objLabel->getPrefixedLabels('set', $chrLang);
$pageTitle = $labels['navPayroll'];
$objpayroll = new payroll();
$templates = $objpayroll->selectTemplateCompany()['data'];
$objSet = new settings($Data);
$sett = $objSet->getSettings(['uploadDir', 'rutaPlanilla']);

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
    <div class="p-4 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-Payroll-tab" data-bs-toggle="tab" data-bs-target="#nav-Payroll" type="button" role="tab" aria-controls="nav-Payroll" aria-selected="true"><?= $labels['navPayroll'] ?></button>
                <button class="nav-link" id="nav-MonthsData-tab" data-bs-toggle="tab" data-bs-target="#nav-MonthsData" type="button" role="tab" aria-controls="nav-MonthsData" aria-selected="true"><?= $labels['navMonthsData'] ?></button>

            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane  fade show active" id="nav-Payroll" role="tabpanel" aria-labelledby="nav-Payroll-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tablePayroll" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
                    </div>
                </div>
            </div>
            <div class="tab-pane  fade pt-3" id="nav-MonthsData" role="tabpanel" aria-labelledby="nav-MonthsData-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tableMonthsData" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal AK Numbers -->
<div class="modal fade" id="modalPayroll" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="h1confIRFS" aria-hidden="true" data-id="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="h1confIRFS"><?= $labels['btnNew'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Nombre anterior -->
                    <div id='containerFields' class="col-12 mb-3">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveCompany" class="btn btn-sm btn-primary">&nbsp;<?= $labels['btnSave'] ?></button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?></button>
            </div>
        </div>
    </div>
</div>

<? require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/dataTables.responsive.min.js"></script>
<script src="/lib/datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>
<script src="/lib/datatables/Select-1.6.0/js/dataTables.select.min.js"></script>
<script src="/lib/colorpicker/spectrum.js"></script>
<script src="/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/vendor/enyo/dropzone/dist/min/dropzone.min.js"></script>
<script src="/lib/xlsx/xlsx.full.min.js"></script>
<?
if (file_exists(__ROOT__ . '/lib/bootstrap-datepicker/locales/bootstrap-datepicker.' . str_replace('_', '-', $chrLocale) . '.min.js')) {
?>
    <script src="/lib/bootstrap-datepicker/locales/bootstrap-datepicker.<?= str_replace('_', '-', $chrLocale) ?>.min.js"></script>
<?
} elseif (file_exists(__ROOT__ . '/lib/bootstrap-datepicker/locales/bootstrap-datepicker.' . $chrLang . '.min.js')) {
?>
    <script src="/lib/bootstrap-datepicker/locales/bootstrap-datepicker.<?= $chrLang ?>.min.js"></script>
<?
} elseif (file_exists(__ROOT__ . '/lib/colorpicker/i18n/jquery.spectrum-' . $chrLang . '.js')) {
?>
    <script src="/lib/colorpicker/i18n/jquery.spectrum-<?= $chrLang ?>.min.js"></script>
<?
}
if (file_exists(__ROOT__ . '/lib/colorpicker/i18n/jquery.spectrum-' . $chrLang . '.js')) {
?>
    <script src="/lib/colorpicker/i18n/jquery.spectrum-<?= $chrLang; ?>.js"></script>
<?
}
?>
<script>
    var labels = <?= json_encode($labels); ?>;
    var templates = <?= json_encode($templates); ?>;
    var sett = <?= json_encode($sett); ?>;
</script>
<script src="<?= autoVer('/usr/payroll/payroll.js'); ?>"></script>

</body>

</html>