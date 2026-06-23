<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/components/usr/labels/cmodLabels.php';

$json = [];
$part;
$objComLabels = new componentLabels($_MYSQLI_);

switch ($action) {
    case 'C':
        switch ($part) {
            case 'L':
                $objComLabels->setId($id);
                $objComLabels->setLabelName($laName);
                $objComLabels->setLabelDescriptions(array_filter($laDescription));
                if ($id) {
                    $json = $objComLabels->updateLabel();
                } else {
                    $json = $objComLabels->insertLabel();
                }
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'C':
                break;
        }
        break;
    case 'R':
        switch ($part) {
            case 'L':
                if (!isset($term)) {
                    $term = '';
                }
                $objComLabels->setTerm($term);
                $json = $objComLabels->selectLabels();
                break;
            case 'D';
                $objComLabels->setId($id);
                $json = $objComLabels->selectDescriptions();
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
