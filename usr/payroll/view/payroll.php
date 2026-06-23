<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/components/usr/labels/cLabels.php';
require_once __ROOT__ . '/usr/payroll/model/modPayroll.php';
$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnCancel',
    'btnDelete',
    'btnNews',
    'btnNew',
    'btnEdit',
    'btnDetailBooking',
    'btnDetails',
    'btnExpected',
    'btnExport',
    'btnSave',
    'btnNo',
    'btnYes',
    'lblRequired',
    'lblRevenue',
    'lblSearch',
    'lblRefence',
    'lblSelect',
    'lblSendEmail',
    'lblMailerTemplates',
    'lblSelectAll',
    'lblReplyto',
    'lblSend',
    'lblClose',
    'lblUpdate',
    'lblAttachments',
    'lblPreview',
    'lblSave',
    'lblView',
    'btnPreview',
    'lblSending',
    'brlAraTours',
    'nteErrorPDF',
    'lblActions',
    'lblMonth',
    'lblName',
    'lblEmail',
    'lblSalary',
    'lblGrossSalary',
    'lblNetOverall',
    'lblAdvances',
    'lblAssociation',
    'lblOthers',
    'lblLoans',
    'lblTotalDeductions',
    'lblAnotherText',
    'lblTotal',
    'lblGrossInColones',
    'lblSuccess'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$objPayroll = new modPayroll($_MYSQLI_);
$companyId = $selEvent['id'];
$imageRoute = $selEvent['image'];
$namecompany = $selEvent['event_name'];
$mtps = $objPayroll->selectMailTemplates($companyId)['data'] ?? [];
$replytos = $objPayroll->selecreplytos()['data'] ?? [];

?>
<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Select-1.6.0/css/select.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/SearchPanes-2.1.1/css/searchPanes.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/bootstrap-datepicker/css/bootstrap-datepicker.standalone.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Buttons-2.3.4/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Buttons-2.3.4/css/buttons.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/Responsive-2.4.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" href="/lib/flatpickr/themes/<?= $selDark ? 'dark' : 'light'; ?>.css">
<?= $selDark ? '<link rel="stylesheet" href="/lib/flatpickr/themes/dark.css">' : ''; ?>
<link rel="stylesheet" type="text/css" href="<?= autoVer('/usr/payroll/assets/css/payroll.css') ?>" />

<div class="" id="">
    <div class="p-4 pb-0 align-items-center rounded-3 border">
        <h5 class="pb-1 border-bottom"><i class="<?= $pageIcon ?>">&nbsp;</i><?= $pageTitle ?></h5>
        <div class="row">
            <div class="col-12">
                <table id="tablePayroll" class="table table-hover dataTable table-striped w-full no-footer table-responsive"></table>
            </div>
        </div>
    </div>
</div>

<div id="modalBulkMails" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-seriesgroupdetail_ids="[]" data-newstatus="">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h4 id="modaltitle" class="modal-title largetitle"><?= $labels['lblSendEmail'] ?></h4>
            </div>
            <div class="modal-body">
                <div class="col-4 d-flex flex-column" id="mailtemplates">
                    <label for=" filterSerie" class=""><?= $labels['lblMailerTemplates'] ?></label>
                    <select name="mailTemplate" id="mailTemplate" class="form-control form-control-sm" data-id="0"></select>
                </div>
                <div class="row mt-3" id="testTableRow">
                    <div class="col-12">
                        <div class="table-container" style="line-height: normal !important;">
                            <table class="table table-hover table-striped" id="tableMails" style="width: 100%;"></table>
                        </div>
                    </div>
                </div>
                <div class=" row" id="">
                    <div class="col-6">
                        <label for="replyto" class=""><?= $labels['lblReplyto'] ?></label>
                        <select class="form-control form-control-sm" name="mailReplyto" id="replyto" data-id="0"></select>
                    </div>
                    <div class="col-1 text-center">
                        <button id="copycc" class="btn btn-primary px-15 mt-4"><i class="fal fa-caret-right"></i></button>
                    </div>
                    <div class="col-5 pl-0">
                        <label class="">CC</label>
                        <input class="form-control form-control-sm" name="mailCc" id="mailCc">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="sendMailPayroll" class="btn btn-sm btn-primary"><?= $labels['lblSend'] ?></button>
                <button id="closeMailPayroll" type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['lblClose'] ?></button>
            </div>
        </div>
    </div>
</div>

<div id="previewMailModal" class="modal fade nest" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" tabindex="-1" data-supplier_id="0" data-seriesgroupdetail_ids="[]" data-mailtemplate_id="0" data-attachments="[]">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $labels['lblPreview'] ?></h5>
                <h5 class="modal-title" id="customername"></h5>
                <h5 class="modal-title"> - </h5>
                <h5 class="modal-title" id="subject"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class=" row" id="mailPreview">
                    <div class="col-12 p-2">
                        <div class="table-container" id="smpreview" style="line-height: normal !important; max-height: calc( 100vh - 375px ); overflow-y: auto;">
                        </div>
                        <div id="smAtt" class="attachment-section mt-2">
                            <label class="form-label"><?= $labels['lblAttachments'] ?>:</label>
                            <button type="button" id="ViewAttachments" class="btn btn-sm btn-primary" title="<?= $labels['lblView'] ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <!--<div class="col-10 d-flex mt-2" id="previewOptions">
                    <textarea type="text" class="form-control form-control-sm col-10" rows="2" id="mailComment"></textarea>
                    <div class="col-2 d-flex justify-content-end">
                        <button id="refreshView" class="btn btn-sm btn-primary ml-2 mr-2" style="max-height: 50px;"> <?= $labels['lblUpdate'] ?></button>
                    </div>
                </div>-->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['lblClose'] ?></button>
                <!--<button id="previewsave" type="button" class="btn btn-sm btn-primary"><?= $labels['lblSave'] ?></button>-->
            </div>
        </div>
    </div>
    <div id="apAttachmentPreview" class="modal fade nest" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" tabindex="-1" data-supplier_id="0" data-seriesgroupdetail_ids="[]" data-mailtemplate_id="0" data-attachments="[]">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $labels['lblPreview'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-container" id="apAttachmentPreviewBody" style="line-height: normal !important; max-height: calc( 100vh - 250px ); overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= $labels['lblClose'] ?></button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
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
    <script src="/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="/lib/xlsx/xlsx.full.min.js"></script>
    <script src="/lib/exceljs/exceljs.min.js"></script>
    <script src="/lib/flatpickr/flatpickr.min.js"></script>
    <script src="/lib/jquery-ui/jquery-ui.min.js"></script>
    <script src="/lib/pdfmake/pdfmake.min.js"></script>
    <script src="/lib/pdfmake/vfs_fonts.min.js"></script>
    <?php if ($chrLang != 'en'): ?>
        <script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
    <?php endif; ?>
    <script>
        var labels = <?= json_encode($labels); ?>;
        var replytos = <?= json_encode($replytos); ?>;
        var mtps = <?= json_encode($mtps); ?>;
        var imageRoute = "<?= $imageRoute ?>";
        var namecompany = "<?= $namecompany ?>";
    </script>
    <script src="<?= autoVer('/usr/payroll/assets/js/payroll.js'); ?>"></script>
    </body>

    </html>