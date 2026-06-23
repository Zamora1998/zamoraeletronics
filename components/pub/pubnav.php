<?php
require_once __ROOT__ . '/model/modNav.php';

$objLoc = new locales($_MYSQLI_);
$locales = $objLoc->select()['data'];

$objNav = new nav($_MYSQLI_);
$objNav->setLanguageId($chrLang);
$objNav->setLocaleId($chrLocale);
$objNav->setType(1);
$navs = $objNav->select()['data'] ?? [];
$drops = $objNav->selectPublicDrop()['data'] ?? [];

$request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
$request_url = rtrim($request_url, '#');
$request_url = rtrim($request_url, '/');
$request_url = strtok($request_url, '?');
$brequest_url = str_replace('/' . $chrLocale, '', $request_url);
$linkClass = '';
?>
                    <ul class="nav col-md-11 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
<?php
foreach ($navs as $nav) {
    if ($nav['url'] == $request_url) {
        $nav['url'] = '#';
        $linkClass = 'link-secondary';
    } else {
        $linkClass = 'link-body-emphasis';
    }

    if (in_array($nav['id'], array_column($drops, 'parent_route_id'))) {
?>
                        <li class="nav-item dropdown">
                            <a href="#" class="dropdown-toggle nav-link px-2 text-uppercase <?= $linkClass ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-block mx-auto text-center">
                                    <i class="<?= $nav['icon'] ?>"></i>
                                </div>
                                <?= $nav['name'] ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
<?php
        foreach ($drops as $drop) {
            if ($drop['parent_route_id'] == $nav['id']) {
                if ($drop['url'] == $request_url || $drop['url'] == '/' . $chrLocale . $request_url) {
                    $drop['url'] = '#';
                    $pageTitle = $drop['name'];
                    $pageIcon = $drop['icon'];
                    $linkClass = 'link-secondary';
                } else {
                    $linkClass = 'link-body-emphasis';
                }
?>
                                <li>
                                    <a class="dropdown-item <?= $linkClass ?>" href="<?= $drop['url'] ?>">
                                        <i class="<?= $drop['icon'] ?>"></i>
                                        <?= $drop['name'] ?>
                                    </a>
                                </li>
<?php
            }
        }
?>
                            </ul>
                        </li>
<?php
    } else {
?>
                        <li>
                            <a href="<?= $nav['url'] ?>" class="nav-link px-2 <?= $linkClass ?>">
                                <div class="d-block mx-auto text-center">
                                    <i class="<?= $nav['icon'] ?>"></i>
                                </div>
                                <?= $nav['name'] ?>
                            </a>
                        </li>
<?php
    }
}
?>
                    </ul>
<?php
if (isset($_SESSION['id']) && isset($_SESSION['token'])) {
    $initials = substr($_SESSION['first'], 0, 1) . substr($_SESSION['last'], 0, 1);
?>
                    <div class="d-none d-md-block col-1 dropdown text-end">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">
                                <img data-name="<?= $initials ?>" data-char-count="2" data-font-size="45" data-seed="3" class="profileContact img-fluid rounded-circle" alt="<?= $initials ?>" src="">
                            </span>
                        </a>
                        <ul class="dropdown-menu text-small">
<?php
    $objNav->setType(2);
    $navs = $objNav->select()['data'];
    foreach ($navs as $nav) {
        if ($nav['url'] == $request_url || $nav['url'] == '/'. $chrLocale . $request_url) {
            $nav['url'] = '#';
            $pageTitle = $nav['name'];
            $pageIcon = $nav['icon'];
            $linkClass = ' disabled';
        } else {
            $linkClass = '';
        }
?>
                            <li><a class="dropdown-item<?= $linkClass ?>" href="<?= $nav['url'] ?>"><?= $nav['name'] ?></a></li>
<?php
    }
?>
                        </ul>
                    </div>
<?php
} else {
?>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#login"><?= $headLabels['lblSignIn'] ?></button>
<?php
    if ($hasSignup) {
?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#signup"><?= $headLabels['lblSignUp'] ?></button>
<?php
    }
?>
                    </div>
<?php
}
?>
                    <div class="d-none d-md-block col-lg-1 me-lg-0 dropdown text-end">
                        <a class="nav-link dropdown-toggle" type="button" id="locales" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?= $arrLocale['locale_name'] ?>"><?= $arrLocale['language_name'] ?></a>
                        <div class="locales dropdown-menu" aria-labelledby="locales">
<?php
$openDrop = false;
$currLang='';
foreach ($locales as $key => $locale) {
    if($openDrop && $locale['language_id'] != $currLang){
?>
                                </div>
                            </div>
<?php
        $openDrop=false;
    }
    if($openDrop && $locale['language_id'] == $currLang){
?>
                                    <a class="dropdown-item" href="/<?= $locale['locale_id'] . $brequest_url ?>"><?= $locale['locale_name'] ?></a>
<?php
    }else{

        if(!$locale['locale_id']){
?>
                            <a class="dropdown-item" href="#"><?= $locale['language_name'] ?></a>
<?php
        }else{
            $openDrop = true;
?>
                            <div class="dropdown dropend">
                                <a class="dropdown-item dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $locale['language_name'] ?></a>
                                <div class="locales-sub dropdown-menu" aria-labelledby="dropdown-layouts">
                                    <a class="dropdown-item" href="/<?= $locale['locale_id'] . $brequest_url ?>"><?= $locale['locale_name'] ?></a>
<?php
        }
    }
    $currLang = $locale['language_id'];
}
?>
                        </div>
                    </div>
