<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/model/modFileProcessor.php';

$objSettings = new settings($_MYSQLI_);
$settings = $objSettings->getSettings(['uploadDir']);
$json = [];

switch ($action) {
    case 'C': //Create
        switch ($part) {
            case 'C':
                $objSettings->setKey($elkey);
                $objSettings->setType($elselectSettingsType);
                $objSettings->setValue($elvalue);
                $json = $objSettings->insert();
                break;
            case 'U':
                if (isset($_FILES) && !empty($_FILES['file']['name'])) {
                    $foldername = __ROOT__ . '/' . $settings['uploadDir'] . "/";
                    $filePrcObj = new FileProcessor($foldername);
                    $fileSaved = $filePrcObj->save();
                    $objSettings->setKey($elkey);
                    $objSettings->setType($elselectSettingsType);
                    $objSettings->setValue($settings['uploadDir'] . '/' . $fileSaved['name'] . '.' . $fileSaved['ext']);
                    $result = $objSettings->insert();
                    $json['result'] = $result['result'] && !empty($fileSaved['name']);
                } else {
                    $objSettings->setKey($elkey);
                    $objSettings->setType($elselectSettingsType);
                    $objSettings->setValue('');
                    $result = $objSettings->insert();
                    $json['result'] = $result['result'];
                }
                break;
        }
        break;
    case 'R': //Read
        switch ($part) {
            case 'A':
                $json = $objSettings->selectAll();
                break;
            case 'S':
                $objSettings->setKey($sKey);
                $json = $objSettings->select();
                break;
        }
        break;
    case 'U': //Update
        switch ($part) {
            case 'K':
                $objSettings->setKey($key);
                $objSettings->setValue($value);
                $json = $objSettings->insert();
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'F':
                $json['result'] = false;
                $objSettings->setKey($sKey);
                $docData = $objSettings->select();
                if (!empty($docData)) {
                    $fileName = $docData['data'][0]['value'];
                    $filePrcObj = new FileProcessor();
                    $result = $filePrcObj->remove(__ROOT__ . $fileName);
                    if ($result) {
                        $objSettings->setKey($sKey);
                        $json = $objSettings->updateValue();
                    }
                }
                break;
            case 'S':
                $json['result'] = false;
                $objSettings->setKey($sKey);
                $objSettings->setType($sType);
                $docData = $objSettings->select();

                if ($sType != 'U' || empty($docData['data'][0]['value'])) {
                    $json = $objSettings->delete();
                } else {
                    $fileName = $docData['data'][0]['value'];
                    $filePrcObj = new FileProcessor();
                    $result = $filePrcObj->remove(__ROOT__ . $fileName);
                    if ($result) {
                        $json = $objSettings->delete();
                    }
                }
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
