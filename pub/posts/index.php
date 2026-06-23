<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/pub/posts/modPosts.php';
$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'ntyAproved',
    'ntyDenied',
    'lblMirror'
);
$headLabels = $objLabel->getLabels($labelSels, $chrLang);

$objPost = new posts($_MYSQLI_);
$objPost->setUUID($uuid);
$post = $objPost->select()['data'];
if (empty($post)) {
    header("Location: /");
}
if (isset($post[0])) {
    $post = $post[0];
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Wolf Pack Labs">
    <meta name="generator" content="">
    <title>EventOs</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/EventOs_icon.svg">
    <link href="/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= autoVer('/assets/css/custom.css'); ?>" rel="stylesheet">
    <link href="/lib/fontawesome/css/all.min.css" rel="stylesheet">
</head>

<body class="d-flex h-100 text-center text-bg-dark">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="p-3 mb-3 border-bottom">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                    <a href="/" class="d-flex align-items-center col-md-3 mb-2 mb-md-0 text-dark text-decoration-none">
                        <img src="/assets/images/LogoD.svg" alt="EventOs" class="me-2" width="271" height="32" role="img" aria-label="EventOs">
                    </a>
                </div>
            </div>
        </header>
        <div id="alertcontainer" class="container">
        </div>
        <main id="main" class="px-3" data-event_id="<?= $post['event_id'] ?>" data-post_id="<?= $post['post_id'] ?>" data-uuid="<?= $uuid ?>">
            <h1><?= $post['event_name'] ?></h1>
            <p class="lead"><?= $post['post_name'] ?></p>
            <div class="col-md-12">
                <div id="reader"></div>
            </div>
            <div class="container py-4" id="nfcScanner">
                <div class="row p-4 align-items-center rounded-3 border shadow-lg">
                    <h2 class="display-6 text-center mb-4 fw-normal text-eventos">Escáner NFC</h2>
                    <p class="text-center">Presiona el botón y acerca el chip NFC al lector de tu dispositivo.</p>
                    <div class="text-center mb-3">
                        <button id="startNFC" class="btn btn-primary">Iniciar Escaneo NFC</button>
                    </div>
                    <div id="nfcResult" class="text-center mt-3 fw-bold text-success"></div>
                </div>
            </div>
        </main>
        <footer class="mt-auto text-white-50">
            <p>© 2025 by Discovery Adventure CR. All rights reserved.</p>
        </footer>
    </div>
    <script src="/lib/jquery/jquery-3.6.3.min.js"></script>
    <script src="/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= autoVer('/assets/js/general.js') ?>"></script>
    <script src="<?= autoVer('/pub/posts/posts.js') ?>" type="module"></script>
    <script src="<?= autoVer('/pub/posts/sound.js') ?>"></script>
    <script>
        var chrLang = '<?= $chrLang ?>';
        var labels = {
            <? foreach ($headLabels as $key => $value) {echo $key . ": '" . addslashes($value) . "',";} ?>};
    </script>
</body>

</html>