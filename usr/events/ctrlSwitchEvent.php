<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/companies/modcompanies.php';

$id = $_POST['id'] ?? 0;
$result = false;
$error = '';

if ($id > 0) {
    try {
        $objEvent = new Companies($_MYSQLI_);
        $objEvent->setId($id);
        $eventData = $objEvent->selectCompany(); // Fetch specific event details

        if ($eventData && isset($eventData['data'][0])) {
            $_SESSION['event'] = $eventData['data'][0];
            $result = true;
        } else {
            $error = 'Event not found.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} else {
    $error = 'Invalid Event ID.';
}

$json = array('result' => $result, 'error' => $error);
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json);
