<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
$hasNotifications = $ini_array['general']['notifications'] ?? false;
$hasAiPanel = $ini_array['general']['aipanel'] ?? false;

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'drpNoEvent',
    'lblLoading',
    'lblNotifications',
    'lblAiPanel',
    'nteSessionExpired',
);
$headLabels = $objLabel->getLabels($labelSels, $chrLang);

$settingSels = [
    'favicon',
    'menubgclass',
];
$objSettings = new settings;
$settings = $objSettings->getSettings($settingSels);
$favicon = $settings['favicon'] != 'favicon N/A' ? $settings['favicon'] : '/assets/images/icon.png';
$menubgclass = $settings['menubgclass'] != 'menubgclass N/A' ? $settings['menubgclass'] : '';
?>
<!doctype html>
<html lang="<?= $chrLang ?>" data-bs-theme="<?= $selDark ? 'dark' : 'light'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="EDMI">
    <meta name="generator" content="">
    <meta name="csrf-token" content="<?= $_SESSION['csrf'] ?? '' ?>">
    <title>Reparaciones Zamora</title>
    <link rel="icon" type="image/x-icon" href="<?= $favicon ?>">
    <link href="<?= autoVer('/lib/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?= autoVer('/lib/bootstrap-5-multi-level-dropdown/bootstrap5-dropdown-ml-hack-hover.css'); ?>" rel="stylesheet">
    <link href="<?= autoVer('/assets/css/custom.css'); ?>" rel="stylesheet">
    <link href="<?= autoVer('/assets/css/toast.css'); ?>" rel="stylesheet">
    <link href="<?= autoVer('/assets/css/datatables.css'); ?>" rel="stylesheet">
    <link href="/lib/fontawesome/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php
    if ($hasNotifications) {
        require_once __ROOT__ . '/components/usr/notifications/notifications.php';
    }
    if ($hasAiPanel) {
        require_once __ROOT__ . '/components/usr/aipanel/aipanel.php';
    }
    ?>
    <main>
        <header class="menu-bg border-bottom bg-body-tertiary">
            <div class="<?= $menubgclass ?>">
                <div class="pe-3 d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                    <a href="/<?= $chrLocale ?>/main" class="p-2 d-flex align-items-center mb-2 mb-md-0 text-dark text-decoration-none">
                        <img src="/assets/images/logo.svg" alt="Logo" class="me-2" width="100" height="58" role="img" aria-label="Reparaciones Zamora">
                    </a>

                    <?php require_once __ROOT__ . '/components/usr/usrnav.php'; ?>

                </div>
            </div>
        </header>
        <div id="spinner-div" class="">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?= $headLabels['lblLoading'] ?>...</span>
            </div>
        </div>
        <div id="alertcontainer" class="toast-container position-fixed end-0 pe-3"></div>