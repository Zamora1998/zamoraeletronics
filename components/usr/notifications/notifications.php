<link href="<?= autoVer('/components/usr/notifications/notifications.css'); ?>" rel="stylesheet">
<div class="offcanvas offcanvas-end" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="ocNotifications" aria-labelledby="ocNotificationsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="ocNotificationsLabel">
            <?= $headLabels['lblNotifications'] ?>
        </h5>&nbsp;
        <span class="badge rounded-pill text-bg-danger notNew"></span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="ocNotificationsBody">
    </div>
</div>