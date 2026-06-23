<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/generator/model/modGenerator.php';

$json = [];
$objGenerator = new GeneratorModel();
$hasProtected = in_array('dev', $authParams);

switch ($action) {
    case 'C':
        switch ($part) {
            case 'G':
                if ($hasProtected) {
                    if (is_string($options)) {
                        $optionsArray = json_decode($options, true);
                    } else {
                        $optionsArray = $options;
                    }

                    $objGenerator->setModuleName($moduleName);
                    if (preg_match('/^[a-zA-Z\/_]+$/', $basePath)) {
                        $objGenerator->setBasePath($basePath);
                    } else {
                        $json = array('result' => false, 'error' => 'The value of basePath can only contain letters, _, or /', 'data' => []);
                    }
                    $objGenerator->setOptions($optionsArray);

                    $result = $objGenerator->generateStructure();

                    $json = [
                        'result' => !empty($result['result']) && $result['result'] == 1,
                        'error' => '',
                        'data' => [],
                        'restview' => null,
                        'restctrl' => null
                    ];

                    if (!empty($result['result']) && $result['result'] == 1) {
                        $json['data'] = $result['data'] ?? [];

                        $files = $result['data']['files'] ?? [];
                        $viewFile = array_values(array_filter($files, fn($f) => str_contains($f, '/view/')))[0] ?? null;
                        $ctrlFile = array_values(array_filter($files, fn($f) => str_contains($f, '/controller/')))[0] ?? null;

                        if ($viewFile) {
                            $objGenerator->setViewFile($viewFile);
                            $restview = $objGenerator->insertRoutesAccess();
                            $json['restview'] = $restview;
                        }

                        if ($ctrlFile) {
                            $objGenerator->setCtrlFile($ctrlFile);
                            $restctrl = $objGenerator->insertCtrlAccess();
                            $json['restctrl'] = $restctrl;
                        }
                    } else {
                        $json['error'] = $result['error'];
                    }
                } else {
                    $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                }
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
