<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/mail/model/modMailComposer.php';

$objMComp = new mailComposer($_MYSQLI_);
$id = 0;
$json = [];
$params = [];

foreach ($_GET as $key => $value) {
    $$key = $value;
}
foreach ($_POST as $key => $value) {
    $$key = $value;
}

if ($id) {
    $objMComp->setId($id);
    $objMComp->setUserId($selUser ?? 0);
    $objMComp->setLanguageId($languageId ?? 'en');
    $objMComp->setParameters($params ?? []);
    $json = $objMComp->select();
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
