<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';

$objLabels = new labels($_MYSQLI_);
switch ($action) {
    case 'C':
        switch ($part) {
            case 'L':
                $objLabels->setId($id);
                $objLabels->setLabelName($laName);
                $objLabels->setLabelDescriptions(array_filter($laDescription));
                if ($id) {
                    $json = modGeneralFunction::toJson($objLabels->updateLabel());
                } else {
                    $json = modGeneralFunction::toJson($objLabels->insertLabel());
                }
                break;
        }
        break;

    case 'R':
        switch ($part) {
            case 'A':
                $json = modGeneralFunction::toJson($objLabels->selectAll());
                break;
            case 'L':
                $objLabels->setId($id);
                $json = modGeneralFunction::toJson($objLabels->selectAll());
                break;
        }
        break;

    case 'D':
        switch ($part) {
            case 'L':
                $objLabels->setId($id);
                $json = modGeneralFunction::toJson($objLabels->deleteLabel());
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo $json;
