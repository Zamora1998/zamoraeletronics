<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/screens/model/modScreens.php';

$json = '';
$objS = new tvScreens($_MYSQLI_);

// Soportar parámetros por URL (GET) para la API
$action = $_REQUEST['action'] ?? $action ?? '';
$part   = $_REQUEST['part']   ?? $part   ?? '';

switch ($action) {

    case 'U':
        switch ($part ?? '') {
            case 'CL': // Cliente
                $objS->setClientId($clientId ?? 0);
                $objS->setNombre($nombre ?? '');
                $objS->setTelefono($telefono ?? '');
                $objS->setUbicacion($ubicacion ?? '');
                $objS->setLatitud($latitud ?? null);
                $objS->setLongitud($longitud ?? null);
                $json = $objS->saveClient();
                break;

            // =========================================================
            // ÓRDENES DE TRABAJO
            // =========================================================
            case 'OR': // Orden
                $objS->setOrderId($orderId ?? 0);
                $objS->setClientId($clientId ?? 0);
                $objS->setBrandId($brandId ?? 0);
                $objS->setModelId($modelId ?? 0);
                $objS->setModeloLibre($modeloLibre ?? '');
                $objS->setPantallaLibre($pantallaLibre ?? '');
                $objS->setFallaReportada($fallaReportada ?? '');
                $objS->setCostoEstimado($costoEstimado ?? 0);
                $objS->setAbonoInicial($abonoInicial ?? 0);
                $objS->setEstado($estado ?? 'pendiente');
                $objS->setTipoPago($tipoPago ?? 'pendiente');
                $objS->setNotas($notas ?? '');
                $json = $objS->saveOrder();
                break;

            case 'ST': // Cambio de estado/pago
                $objS->setOrderId($orderId ?? 0);
                $objS->setEstado($estado ?? 'pendiente');
                $objS->setTipoPago($tipoPago ?? 'pendiente');
                $json = $objS->updateOrderStatus();
                break;

            case 'FI': // Firma del cliente (base64 → guardar PNG)
                $objS->setOrderId($orderId ?? 0);
                $firmaBase64 = $firmaBase64 ?? '';
                $result = ['result' => false, 'error' => 'Sin datos de firma.', 'orderId' => 0];

                if ($firmaBase64 && ($orderId ?? 0)) {
                    // Limpiar cabecera base64 si la trae
                    $firmaData = preg_replace('/^data:image\/\w+;base64,/', '', $firmaBase64);
                    $firmaData = base64_decode($firmaData);

                    if ($firmaData !== false) {
                        $sigDir = __ROOT__ . '/uploadDir/signatures/';
                        if (!is_dir($sigDir)) {
                            mkdir($sigDir, 0755, true);
                        }
                        $fileName  = 'firma_orden_' . intval($orderId) . '_' . time() . '.png';
                        $filePath  = $sigDir . $fileName;
                        $rutaRelativa = 'uploadDir/signatures/' . $fileName;

                        if (file_put_contents($filePath, $firmaData) !== false) {
                            $objS->setFirmaRuta($rutaRelativa);
                            $result = $objS->saveFirmaRuta();
                            $result['data']['firmaRuta'] = $rutaRelativa;
                        } else {
                            $result['error'] = 'No se pudo guardar el archivo de firma.';
                        }
                    } else {
                        $result['error'] = 'Datos base64 inválidos.';
                    }
                }
                $json = $result;
                break;

            case 'FRC': // Foto de recepcion (base64 → guardar PNG o JPG)
                $objS->setOrderId($orderId ?? 0);
                $fotoBase64 = $fotoBase64 ?? '';
                $result = ['result' => false, 'error' => 'Sin datos de foto.', 'orderId' => 0];

                if ($fotoBase64 && ($orderId ?? 0)) {
                    // Limpiar cabecera base64 si la trae
                    $fotoData = preg_replace('/^data:image\/\w+;base64,/', '', $fotoBase64);
                    $fotoData = base64_decode($fotoData);

                    if ($fotoData !== false) {
                        $fotoDir = __ROOT__ . '/uploadDir/recepcion/';
                        if (!is_dir($fotoDir)) {
                            mkdir($fotoDir, 0755, true);
                        }
                        $fileName  = 'foto_recepcion_' . intval($orderId) . '_' . time() . '.png';
                        $filePath  = $fotoDir . $fileName;
                        $rutaRelativa = 'uploadDir/recepcion/' . $fileName;

                        if (file_put_contents($filePath, $fotoData) !== false) {
                            $objS->setFotoRecepcionRuta($rutaRelativa);
                            $result = $objS->saveFotoRecepcionRuta();
                            $result['data']['fotoRecepcionRuta'] = $rutaRelativa;
                        } else {
                            $result['error'] = 'No se pudo guardar el archivo de foto.';
                        }
                    } else {
                        $result['error'] = 'Datos base64 inválidos.';
                    }
                }
                $json = $result;
                break;

            // =========================================================
            // PARTES DE ORDEN
            // =========================================================
            case 'OP': // Order Part
                $objS->setOrderId($orderId ?? 0);
                $objS->setOrderPartId($orderPartId ?? 0);
                $objS->setPartId($partId ?? 0);
                $objS->setCantidad($cantidad ?? 1);
                $objS->setPrecioUnit($precioUnit ?? 0);
                $json = $objS->saveOrderPart();
                break;
        }
        break;

    // =========================================================
    // ELIMINAR
    // =========================================================
    case 'D':
        switch ($part ?? '') {
            case 'CL': // Cliente
                $objS->setClientId($clientId ?? 0);
                $json = $objS->deleteClient();
                break;

            case 'OR': // Orden
                $objS->setOrderId($orderId ?? 0);
                $json = $objS->deleteOrder();
                break;

            case 'OP': // Order Part
                $objS->setOrderId($orderId ?? 0);
                $objS->setOrderPartId($orderPartId ?? 0);
                $json = $objS->deleteOrderPart();
                break;
        }
        break;

    // =========================================================
    // SELECT / GET
    // =========================================================
    default:
        $currentPart = $part ?: 'OR'; // 'OR' por defecto si no se envía part
        switch ($currentPart) {
            case 'CL': // Lista de clientes
                $json = $objS->selectClients();
                break;

            case 'OR': // Lista de órdenes
                $objS->setClientId($_REQUEST['clientId'] ?? $clientId ?? 0);
                $estadoQuery = $_REQUEST['estado'] ?? $estado ?? '';
                if ($estadoQuery !== '') {
                    $objS->setEstado($estadoQuery);
                } else {
                    $objS->setEstado(''); // Permite buscar todos si viene vacío
                }
                $json = $objS->selectOrders();
                break;

            case 'ORD': // Detalle de una orden
                $objS->setOrderId($_REQUEST['orderId'] ?? $orderId ?? 0);
                $json = $objS->selectOrder();
                break;

            case 'BR': // Marcas
                $json = $objS->selectBrands();
                break;

            case 'MD': // Modelos (filtrados por marca si viene brandId)
                $objS->setBrandId($brandId ?? 0);
                $json = $objS->selectModels();
                break;

            case 'PT': // Partes
                $objS->setBrandId($brandId ?? 0);
                $json = $objS->selectParts();
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
