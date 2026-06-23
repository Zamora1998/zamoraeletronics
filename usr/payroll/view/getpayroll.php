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

?>
<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/2.3.2/datatables.min.css" />
<link rel="stylesheet" href="/lib/flatpickr/themes/<?= $selDark ? 'dark' : 'light'; ?>.css">
<?= $selDark ? '<link rel="stylesheet" href="/lib/flatpickr/themes/dark.css">' : ''; ?>

<div class="" id="table">
    <div class="p-4 align-items-center rounded-3 border shadow-lg">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-MonthsData-tab" data-bs-toggle="tab" data-bs-target="#nav-MonthsData" type="button" role="tab" aria-controls="nav-MonthsData" aria-selected="true"><?= $labels['navMonthsData'] ?></button>

            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane  show active fade pt-3" id="nav-MonthsData" role="tabpanel" aria-labelledby="nav-MonthsData-tab" tabindex="0">
                <div class="row mt-2">
                    <div class="col-12">
                        <table id="tableMonthsData" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal Deconstruction -->
<div id="modalDeconstruction" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h4 id="deconstructionModalTitle" class="modal-title largetitle">Detalles de Periodos</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tableDeconstruction">
                        <thead>
                            <tr id="deconstructionHeaders">
                                <th>Concepto</th>
                                <!-- Dynamic headers will go here -->
                            </tr>
                        </thead>
                        <tbody id="deconstructionBody">
                            <!-- Dynamic rows will go here -->
                        </tbody>
                        <tfoot id="deconstructionFooter" style="font-weight: bold;">
                            <!-- Dynamic footer will go here -->
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['btnCancel'] ?? 'Cerrar' ?></button>
            </div>
        </div>
    </div>
</div>

<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>

<script src="/lib/jquery-ui/jquery-ui.min.js"></script>
<script src="/lib/datatables/2.3.2/datatables.min.js"></script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/xlsx/xlsx.full.min.js"></script>
<script src="/lib/exceljs/exceljs.min.js"></script>
<script src="/lib/flatpickr/flatpickr.min.js"></script>
<?php if ($chrLang != 'en'): ?>
    <script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
<?php endif; ?>
<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="<?= autoVer('/usr/payroll/assets/js/getpayroll.js'); ?>"></script>
</body>

</html>