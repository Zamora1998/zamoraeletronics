<?
require_once __ROOT__ . '/model/modNav.php';
require_once __ROOT__ . '/usr/companies/modcompanies.php';
$objLoc = new locales($_MYSQLI_);
$locales = $objLoc->select()['data'];

$objNav = new nav($_MYSQLI_);
$objNav->setLanguageId($chrLang);
$objNav->setLocaleId($chrLocale);
$objNav->setPrivate(1);
$objNav->setType(1);
$objNav->setUserId($selUser);
$navs = $objNav->select()['data'] ?? [];
$drops = $objNav->selectDrop()['data'] ?? [];

$objEvents = new Companies($_MYSQLI_);
$objEvents->setUserId($selUser);
$events = $objEvents->selectCompanies()['data'];

$request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
$request_url = rtrim($request_url, '#');
$request_url = rtrim($request_url, '/');
$request_url = strtok($request_url, '?');
$brequest_url = str_replace('/' . $chrLocale, '', $request_url);
$linkClass = '';
$pageTitle = '';
$pageIcon = '';
?>
                    <ul class="nav col-md-10 col-lg-auto my-2 justify-content-center my-md-0 text-small ms-auto">
<?
foreach ($navs as $nav) {
    if ($nav['url'] == $request_url || $nav['url'] == '/'. $chrLocale . $request_url) {
        $nav['url'] = '#';
        $pageTitle = $nav['name'];
        $pageIcon = $nav['icon'];
        $linkClass = 'link-secondary';
    } else {
        $linkClass = 'link-body-emphasis';
    }

    if(in_array($nav['id'], array_column($drops, 'parent_route_id'))){
?>
                        <li class="nav-item dropdown">
                            <a href="#" class="dropdown-toggle nav-link px-2 <?= $linkClass ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-block mx-auto text-center">
                                    <i class="<?= $nav['icon'] ?>"></i>
                                </div>
                                <?= $nav['name'] ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
<?
        foreach ($drops as $drop) {
            if($drop['parent_route_id'] == $nav['id']){
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
<?
            }
        }
?>
                            </ul>
                        </li>
<?
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
<?
    }
}
?>
                        <li class="nav-link"></li>
                        <li class="nav-item dropdown pt-2">
                            <a class="nav-link dropdown-toggle link-body-emphasis" type="button" id="locales" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?= $arrLocale['locale_name'] ?>"><?= $chrLocale ?></a>
                            <div class="locales dropdown-menu" aria-labelledby="locales">
<?
$openDrop = false;
$currLang='';
foreach ($locales as $key => $locale) {
    if($openDrop && $locale['language_id'] != $currLang){
?>
                                </div>
                            </div>
<?
        $openDrop=false;
    }
    if($openDrop && $locale['language_id'] == $currLang){
?>
                                    <a class="dropdown-item" href="/<?= $locale['locale_id'] . $brequest_url ?>"><?= $locale['locale_name'] ?></a>
<?
    }else{

        if(!$locale['locale_id']){
?>
                            <a class="dropdown-item" href="#"><?= $locale['language_name'] ?></a>
<?
        }else{
            $openDrop = true;
?>
                            <div class="dropdown dropend">
                                <a class="dropdown-item dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $locale['language_name'] ?></a>
                                <div class="locales-sub dropdown-menu" aria-labelledby="dropdown-layouts">
                                    <a class="dropdown-item" href="/<?= $locale['locale_id'] . $brequest_url ?>"><?= $locale['locale_name'] ?></a>
<?
        }
    }
    $currLang = $locale['language_id'];
}
$initials = substr($_SESSION['first'], 0, 1) . substr($_SESSION['last'], 0, 1);
?>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <?
    // Ensure variables exist to avoid undefined index warnings
    $selEvent = $selEvent ?? ['id' => 0, 'name' => ''];
    $labels = $labels ?? [];
    $events = $events ?? [];
    if (!isset($labels['drpNoEvent'])) {
        $labels['drpNoEvent'] = 'No event';
    }
    if($selEvent['id'] == 0){
        $selEvent['name'] = $labels['drpNoEvent'];
    }
?>
                        <li class="nav-link"></li>
                        <li class="nav-item dropdown pt-1">
                            <a class="nav-link dropdown-toggle link-dark" href="#" id="dropdownEvents" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-id="<?= $selEvent['id'] ?>">
                                <span class="lead">
                                    <?= $selEvent['event_name'] ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownEvents">
<?
    foreach ($events as $key => $event) {
?>
                                <li><a class="dropdown-item event-item" href="#" data-id="<?= $event['id'] ?>" data-name="<?= $event['event_name'] ?>"><?= $event['event_name'] ?></a></li>
<?
    }
?>
                            </ul>
                        </li>

                        <li class="nav-item dropdown pt-2">
                            <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="avatar">
                                    <img data-name="<?= $initials ?>" data-char-count="2" data-font-size="45" data-seed="3" class="profileContact img-fluid rounded-circle" alt="<?= $initials ?>" src="">
                                </span>
                            </a>
                            <ul class="dropdown-menu text-small">
<?
$objNav->setType(2);
$navs = $objNav->select()['data'];
foreach ($navs as $nav) {
    if ($nav['url'] == $request_url || $nav['url'] == '/'. $chrLocale . $request_url) {
        $nav['url'] = '#';
        $pageTitle = $nav['name'];
        $pageIcon = $nav['icon'];
        $linkClass = 'link-secondary';
        $linkClass = ' disabled';
    } else {
        $linkClass = '';
    }
?>
                                <li><a class="dropdown-item<?= $linkClass ?>" href="<?= $nav['url'] ?>"><?= $nav['name'] ?></a></li>
<?
}
?>
                            </ul>
                        </li>
                    </ul>
