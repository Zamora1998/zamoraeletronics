<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/startevent/modstart.php';


$objStarEvents = new modStarEvents();

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
                $registrations = $objStarEvents->selectRegistrations();
                $postsByEvent = $objStarEvents->selectPostsByEventGrouped();
                $scans = $objStarEvents->selectPostScansByEvent();
                $json = [
                    'registrations' => $registrations,
                    'posts' => $postsByEvent,
                    'scans' => $scans
                ];
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
                $data = json_decode($data, true);
                $objStarEvents->setParams($data);
                $json = $objStarEvents->InsertMassiveClose();
                break;
        }
        break;
}


/* ======= salida ======= */
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
