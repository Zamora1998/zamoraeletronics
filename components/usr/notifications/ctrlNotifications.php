<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/components/usr/notifications/modNotifications.php';

$objNot = new notifications($_MYSQLI_);
$objNot->setUserId($selUser);
$objNot->setLanguageId($chrLang);
switch ($action) {
    case 'R':
        $json = $objNot->select();
        break;
    case 'U':
        switch ($part) {
            case 'D':
                $objNot->setId($id);
                $json = $objNot->updateDeleted();
                break;
            case 'N':
                $objNot->setId($id);
                $json = $objNot->updateUnread();
                break;
            case 'R':
                $objNot->setId($id);
                $json = $objNot->updateRead();
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
