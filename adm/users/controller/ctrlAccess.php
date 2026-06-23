<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/users/model/modAccess.php';
require_once __ROOT__ . '/components/usr/labels/cmodLabels.php';

$json = [];
$part;
$objAccess = new Access($_MYSQLI_);
$objCompAcess = new componentLabels($_MYSQLI_);
$hasProtected = in_array('dev', $authParams);

switch ($action) {
    case 'C':
        switch ($part) {
            case 'A':
                $objAccess->setId($id ?? 0);
                $objAccess->setAccessName($Select2Name);
                $objAccess->setDescription($Select2Description);
                if ($hasProtected) {
                    if ($id ?? 0) {
                        $json = $objAccess->updateAccess();
                    } else {
                        $json = $objAccess->insertAccess();
                    }
                } else {
                    $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                }
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'A':
                $objAccess->setId($id);
                $json = $objAccess->consultAccess();
                break;
            case 'D':
                $objAccess->setId($id);
                if ($hasProtected) {
                    $json = $objAccess->deleteAccess();
                } else {
                    $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                }
                break;
        }
        break;
    case 'R':
        switch ($part) {
            case 'A':
                $objAccess->setLanguageId($chrLang);
                $json = $objAccess->selectAll();
                break;
            case 'S':
                $objAccess->setId($num ?? 0);
                if (empty($num)) {
                    echo json_encode(['data' => []]);
                    return;
                }
                if ($hasProtected) {
                    $objAccess->setLanguageId($chrLang);
                    $json = $objAccess->selectAccess();
                } else {
                    $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                }
                break;
            case 'L':
                $objCompAcess->setLanguageId($chrLang);
                if (!isset($term)) {
                    $term = '';
                }
                $objCompAcess->setTerm($term);
                $json = $objCompAcess->selectLabels();
                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'U':
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
