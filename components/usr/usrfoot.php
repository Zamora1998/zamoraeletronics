        <!-- Back to top button -->
        <button type="button" class="btn btn-secondary btn-floating btn-lg" id="btn-back-to-top">
            <i class="fal fa-chevron-up"></i>
        </button>
        </main>
        <!-- Contact Modal -->
        <div class="modal fade" id="ctctDialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <!--<div class="modal fade" id="ctctDialog" data-keyboard="false" tabindex="-1" data-backdrop="static" aria-labelledby="termsCondLabel" aria-hidden="true">-->
            <div class="modal-dialog modal-md">
                <div class="modal-content" id="ctctContent">
                    <div class="modal-header" id="ctctHeader">
                    </div>
                    <div class="modal-body modal-dialog-scrollbar p-0" id="ctctBody">
                    </div>
                    <div class="modal-footer" id="ctctFooter">
                    </div>
                </div>
            </div>
        </div>

        <script src="/lib/jquery/jquery-3.6.3.min.js"></script>
        <script src="<?= autoVer('/lib/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
        <script src="<?= autoVer('/lib/bootstrap-5-multi-level-dropdown/bootstrap5-dropdown-ml-hack.js') ?>"></script>
        <script src="<?= autoVer('/lib/luxon/luxon.min.js'); ?>"></script>
<?php
if ($hasNotifications) {
?>
        <script src="<?= autoVer('/lib/jquery-timeago/jquery.timeago.js'); ?>"></script>        
<?php
    if (file_exists(__ROOT__ . '/lib/jquery-timeago/locales/jquery.timeago.' . $chrLang . '.js')){
?>
        <script src="<?= autoVer('/lib/jquery-timeago/locales/jquery.timeago.' . $chrLang . '.js'); ?>"></script>
<?php
    }
}
?>
        <script src="<?= autoVer('/lib/initialjs/initial.min.js'); ?>"></script>
        <script src="<?= autoVer('/assets/js/general.js') ?>"></script>
        <script src="<?= autoVer('/usr/contacts/contact.js'); ?>"></script>
        <script>
            var chrLang = '<?= $chrLang ?>';
            var chrLocale = '<?= str_replace('_', '-', $chrLocale) ?>';
            var headLabels = <?= json_encode($headLabels); ?>;
<?php
if (file_exists(__ROOT__ . '/lib/datatables/2.3.2/plugins/i18n/' . str_replace('_', '-', $chrLocale) . '.json')) {
    $tlbLang = '/lib/datatables/2.3.2/plugins/i18n/' . str_replace('_', '-', $chrLocale) . '.json';
} else {
    $tlbLang = '/lib/datatables/2.3.2/plugins/i18n/' . $chrLang . '.json';
}
?>
            var TABLELANG = '<?= $tlbLang ?>';
        </script>
<?php
if ($hasNotifications) {
?>
        <script src="<?= autoVer('/components/usr/notifications/notifications.js'); ?>"></script>
<?php
}
