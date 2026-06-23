<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/registrations/modregistrations.php';
require_once __ROOT__ . '/mail/model/modMailComposer.php';
require_once __ROOT__ . '/vendor/autoload.php';               //  FPDF via Composer
require_once __ROOT__ . '/model/modFileProcessor.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$objRegistrations = new modRegistrations();
$objSettings = new settings($_MYSQLI_);
$settings = $objSettings->getSettings(['routeimages']);

$json  = [];
$part;

switch ($action) {
    case 'C':
        switch ($part) {
            case 'E':
                break;
            case 'R':
                break;
        }
        break;
    case 'R':
        switch ($part) {
            case 'A':
                $json = $objRegistrations->selectRegistrations();
                break;
            case 'R':
                break;
            case 'E':
                break;
            case 'S':
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'R':
                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'E':
                $objRegistrations->setStatusPay($value);
                $objRegistrations->setId($participant);
                $json = $objRegistrations->updateState();
                break;
        }
        break;
}


/* ======= salida ======= */
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
