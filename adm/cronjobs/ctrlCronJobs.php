<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/cronjobs/modCronjob.php';

$json = [];
$part;

$objCronJobs = new cronjobs($_MYSQLI_);
$hasProtected = in_array('dev', $authParams);

switch ($action) {
    case 'C':
        switch ($part) {
            case 'C':
                $objCronJobs->setId($id);
                $objCronJobs->setScript($elScript);
                $objCronJobs->setSchedule($elSchedule);
                $objCronJobs->setStatus($elEnabled);
                $objCronJobs->setProtected($elProtected);
                if ($id) {
                    $json = $objCronJobs->updateCronjob();
                } else {
                    $json = $objCronJobs->insertCronjob();
                }
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'L':
                $objCronJobs->setId($id);
                $cronjobData = $objCronJobs->selectCronjob();

                if ($cronjobData['result'] && isset($cronjobData['data'][0]['protected'])) {
                    if ($cronjobData['data'][0]['protected'] == 1 && !$hasProtected) {
                        $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                    } else {
                        $json = $objCronJobs->deleteCronjob();
                    }
                } else {
                    $json = array('result' => false, 'error' => 'Invalid cronjob or no data found', 'data' => []);
                }
                break;
        }
        break;
    case 'R': // Agregar manejo de lectura
        switch ($part) {
            case 'A':
                $cronjobs = $objCronJobs->selectAll();
                if ($cronjobs['result']) {
                    foreach ($cronjobs['data'] as &$row) {
                        $row['disabled'] = ($row['protected'] == 1 && !$hasProtected) ? 1 : 0;
                    }
                }
                $json = $cronjobs;
                break;
            case 'L':
                $objCronJobs->setId($id);
                $cronjobData = $objCronJobs->selectCronjob();
                if ($cronjobData['result'] && isset($cronjobData['data'][0]['protected'])) {
                    if ($cronjobData['data'][0]['protected'] == 1 && !$hasProtected) {
                        $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                    } else {
                        $json = $cronjobData;
                    }
                } else {
                    $json = array('result' => false, 'error' => 'Invalid cronjob or no data found', 'data' => []);
                }
                break;
            case 'S': // Acción para ejecutar la sincronización
                if ($id) {
                    $objCronJobs->setId($id);
                    $syncData = $objCronJobs->selectCronjob();

                    if ($syncData['result'] && isset($syncData['data'][0]['protected'])) {
                        if ($syncData['data'][0]['protected'] == 1 && !$hasProtected) {
                            $json = ['result' => false, 'error' => 'Access denied', 'data' => []];
                        } else {
                            if (!empty($syncData['data'][0]['script'])) {
                                $localUrl = modGeneralFunction::baseUrl() . '/' . ltrim($syncData['data'][0]['script'], '/');
                                $ch = curl_init($localUrl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $json = curl_exec($ch) ?: 'Error: ' . curl_error($ch);
                                $json = json_decode($json, true);
                            } else {
                                $json = ['result' => false, 'error' => $syncData['error'] ?? ''];
                            }
                        }
                    } else {
                        $json = ['result' => false, 'error' => 'Invalid cronjob or no data found', 'data' => []];
                    }
                }
                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'C':
                if ($id) {
                    $objCronJobs->setId($id);
                    $cronjobData = $objCronJobs->selectCronjob();

                    if ($cronjobData['result'] && isset($cronjobData['data'][0]['protected'])) {
                        if ($cronjobData['data'][0]['protected'] == 1 && !$hasProtected) {
                            $json = ['result' => false, 'error' => 'Access denied', 'data' => []];
                        } else {
                            $json = $objCronJobs->updateCronjobStatus($enabled);
                        }
                    } else {
                        $json = ['result' => false, 'error' => 'Invalid cronjob or no data found', 'data' => []];
                    }
                }
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
