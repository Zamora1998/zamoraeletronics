<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/main/modMain.php';

$json = [];
$objMain = new main($_MYSQLI_);

switch ($action) {
    case 'R':
        switch ($part) {
            case 'D':
                $json = $objMain->select();
                break;
        }
        break;
}


header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
