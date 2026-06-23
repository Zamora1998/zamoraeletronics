<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/assets/php/libCrypt.php';

$objCrypt = new crypt;

$json = [];

if ($action == 'C')
    switch ($part) {
        case 'D':
            $json['plain'] = $objCrypt->decrypt($key, $hash);
            break;
        case 'E':
            $json['hash'] = $objCrypt->encrypt($key, $plain);
            break;
        case 'P':
            $json['hash'] = password_hash(trim($plain), PASSWORD_BCRYPT);
            break;
    }

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
