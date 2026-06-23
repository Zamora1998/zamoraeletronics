<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/systemslogs/modSystemlogs.php';

$json = [];
$part;

$objsyslogs = new syslogs($_MYSQLI_);
$hasProtected = in_array('dev', $authParams ?? []) || in_array('adm', $authParams ?? []);

switch ($action) {
    case 'R':

        if (!$hasProtected) {
            $json = [
                'result' => true,
                'data' => [],
                'message' => 'Access Denied'
            ];
            break;
        }

        switch ($part) {
            case 'A':
                $json = $objsyslogs->selectAll();
                break;
            case 'T':
                $json = $objsyslogs->selectLogTypes();
                break;
            case 'LT':
                $json = $objsyslogs->selectLogTypes();
                break;
            case 'LTT':
                $json = $objsyslogs->selectLogTypeTables();
                break;
        }
        break;

    case 'C':
        if (!$hasProtected) {
            $json = ['result' => false, 'data' => [], 'message' => 'Access Denied'];
            break;
        }
        switch ($part) {
            case 'LT': // Log Type Insert
                $objsyslogs->setName($name ?? '');
                $json = $objsyslogs->insertLogType();
                break;
            case 'LTT': // Log Type Table Insert
                $objsyslogs->setId($logtype_id ?? 0);
                $tables = $_POST['table'] ?? [];
                if (!is_array($tables)) {
                    $tables = [trim($tables)];
                }
                $json = ['result' => true, 'data' => []];
                foreach ($tables as $t) {
                    $t = trim($t);
                    if ($t === '') continue;
                    $objsyslogs->setTableName($t);
                    $res = $objsyslogs->insertLogTypeTable();
                    if (!$res['result']) {
                        $json = $res;
                        break;
                    }
                }
                break;
        }
        break;

    case 'U':
        if (!$hasProtected) {
            $json = ['result' => false, 'data' => [], 'message' => 'Access Denied'];
            break;
        }
        switch ($part) {
            case 'LT': // Log Type Update
                $objsyslogs->setId($id ?? 0);
                $objsyslogs->setName($name ?? '');
                $json = $objsyslogs->updateLogType();
                break;
        }
        break;

    case 'D':
        if (!$hasProtected) {
            $json = ['result' => false, 'data' => [], 'message' => 'Access Denied'];
            break;
        }
        switch ($part) {
            case 'LT': // Log Type Delete
                $objsyslogs->setId($id ?? 0);
                $json = $objsyslogs->deleteLogType();
                break;
            case 'LTT': // Log Type Table Delete
                $objsyslogs->setId($logtype_id ?? 0);
                $objsyslogs->setTableName($table ?? '');
                $json = $objsyslogs->deleteLogTypeTable();
                break;
        }
        break;
}
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
