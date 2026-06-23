<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/screens/model/modScreens.php';

$json = '';
$objS = new tvScreens($_MYSQLI_);

// --- Manejo de upload de PDF para modelos ---
$pdfRutaGuardada = '';
if (
    isset($_FILES['pdf_archivo']) &&
    $_FILES['pdf_archivo']['error'] === UPLOAD_ERR_OK
) {
    $pdfDir = __ROOT__ . '/uploadDir/tv_models/';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }
    $ext      = strtolower(pathinfo($_FILES['pdf_archivo']['name'], PATHINFO_EXTENSION));
    $fileName = 'tvmodel_' . time() . '_' . uniqid() . '.' . $ext;
    $destino  = $pdfDir . $fileName;

    if ($ext === 'pdf' && move_uploaded_file($_FILES['pdf_archivo']['tmp_name'], $destino)) {
        $pdfRutaGuardada = 'uploadDir/tv_models/' . $fileName;
    }
}

switch ($action ?? '') {

    // =========================================================
    // MARCAS
    // =========================================================
    case 'C':
    case 'U':
        switch ($part ?? '') {
            case 'BR':
                $objS->setBrandId($brandId ?? 0);
                $objS->setBrandNombre($brandNombre ?? '');
                $json = json_encode($objS->saveBrand());
                break;

            case 'MD':
                $objS->setModelId($modelId ?? 0);
                $objS->setBrandId($brandId ?? 0);
                $objS->setModelNombre($modelNombre ?? '');
                $objS->setPantalla($pantalla ?? '');
                if ($pdfRutaGuardada) {
                    $objS->setPdfRuta($pdfRutaGuardada);
                } elseif (!empty($pdfRuta ?? '')) {
                    $objS->setPdfRuta($pdfRuta);
                }
                $json = json_encode($objS->saveModel());
                break;

            case 'PT':
                $objS->setPartId($partId ?? 0);
                $objS->setBrandId($brandId ?? 0);
                $objS->setPartNombre($partNombre ?? '');
                $objS->setPartDesc($partDesc ?? '');
                $objS->setPrecioCrc($precioCrc ?? 0);
                $objS->setStock($stock ?? 0);
                $json = json_encode($objS->savePart());
                break;
        }
        break;

    case 'D':
        switch ($part ?? '') {
            case 'BR':
                $objS->setBrandId($brandId ?? 0);
                $json = json_encode($objS->deleteBrand());
                break;

            case 'MD':
                // Si el modelo tiene PDF, eliminarlo del disco
                $objS->setModelId($modelId ?? 0);
                $modelData = $objS->selectModel();
                if (!empty($modelData['data']['pdf_ruta'])) {
                    $filePath = __ROOT__ . '/' . $modelData['data']['pdf_ruta'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                $json = json_encode($objS->deleteModel());
                break;

            case 'PT':
                $objS->setPartId($partId ?? 0);
                $json = json_encode($objS->deletePart());
                break;
        }
        break;

    // GET — carga de listas para la pantalla de catálogos
    default:
        switch ($part ?? '') {
            case 'BR':
                $json = json_encode($objS->selectBrands());
                break;

            case 'MD':
                $objS->setBrandId($brandId ?? 0);
                $json = json_encode($objS->selectModels());
                break;

            case 'PT':
                $objS->setBrandId($brandId ?? 0);
                $json = json_encode($objS->selectParts());
                break;

            default:
                // Cargar vista HTML del catálogo
                $brands = $objS->selectBrands()['data'];
                $models = $objS->selectModels()['data'];
                $parts  = $objS->selectParts()['data'];
                include __ROOT__ . '/usr/screens/viewCatalogs.php';
                exit;
        }
        break;
}


header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
