<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
$hasCaptcha = $ini_array['general']['captcha'] ?? true;
$hasSignup = $ini_array['general']['signup'] ?? true;

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'btnSendPasswordResetEmail',
    'btnResetPassword',
    'frmConfirmPassword',
    'frmEmail',
    'frmFirstname',
    'frmLastname',
    'frmPassword',
    'lblAlreadyHaveAccount',
    'lblBackToSignIn',
    'lblDontHaveAccount',
    'lblForgotPassword',
    'lblLoading',
    'lblResetPassword',
    'lblSignIn',
    'lblSignUp',
    'nteFieldReqired',
    'nteSecurePassword',
    'ntePWNotSame',
    'ntePWNotStrongEnough',
    'ntePWResetError',
    'ntePWResetSoccess',
    'ntePWSame',
    'ntePWStrongEnough',
    'pwrFail',
    'pwrInternalError',
    'pwrSuccess',
);
$headLabels = $objLabel->getLabels($labelSels, $chrLang);

$modal = '';
if (in_array('password_reset', explode('/', $_SERVER["REQUEST_URI"])) && $pkey) {
    require_once __ROOT__ . '/model/modPassReset.php';
    $objPassReset = new passReset($_MYSQLI_);
    $objPassReset->setKey($pkey ?? '');
    if ($objPassReset->validateKey()['result']) {
        $modal = 'reset';
    } else {
        $modal = 'forgot';
    }
}
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
    <title>EDMI - Soluciones Empresariales</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/icon.png">
    <link href="<?= autoVer('/lib/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?= autoVer('/lib/bootstrap-5-multi-level-dropdown/bootstrap5-dropdown-ml-hack-hover.css'); ?>" rel="stylesheet">
    <link href="<?= autoVer('/assets/css/custom.css'); ?>" rel="stylesheet">
    <link href="/lib/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="<?= autoVer('/lib/password-strength-meter/password.min.css'); ?>" rel="stylesheet">
    <style>
        .captcha-input {
            background: #FFF url(/captcha/image?<?= time() ?>) repeat-y left center;
            width: 72px;
        }
    </style>
</head>

<body>
    <main>
        <header class="border-bottom bg-body-tertiary">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                    <a href="/<?= $chrLocale ?>" class="d-flex align-items-center col-md-3 mb-2 mb-md-0 text-dark text-decoration-none">
                        <img src="/assets/images/logo.svg" alt="Logo" class="me-2" width="100" height="58" role="img" aria-label="Edmi Costa Rica">
                    </a>

                    <?php require_once __ROOT__ . '/components/pub/pubnav.php'; ?>

                </div>
            </div>
        </header>
        <div id="spinner-div" class="">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?= $headLabels['lblLoading'] ?>...</span>
            </div>
        </div>
        <div id="alertcontainer" class="toast-container position-fixed end-0 pe-3"></div>