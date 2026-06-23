<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/screens/model/modScreens.php';

$json = '';
$objS = new tvScreens($_MYSQLI_);

switch ($action ?? '') {

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
                $json = json_encode($objS->saveOrder());
                break;

            case 'ST': // Cambio de estado/pago
                $objS->setOrderId($orderId ?? 0);
                $objS->setEstado($estado ?? 'pendiente');
                $objS->setTipoPago($tipoPago ?? 'pendiente');
                $json = json_encode($objS->updateOrderStatus());
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
                            $result['firmaRuta'] = $rutaRelativa;
                        } else {
                            $result['error'] = 'No se pudo guardar el archivo de firma.';
                        }
                    } else {
                        $result['error'] = 'Datos base64 inválidos.';
                    }
                }
                $json = json_encode($result);
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
                $json = json_encode($objS->saveOrderPart());
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
                $json = json_encode($objS->deleteClient());
                break;

            case 'OR': // Orden
                $objS->setOrderId($orderId ?? 0);
                $json = json_encode($objS->deleteOrder());
                break;

            case 'OP': // Order Part
                $objS->setOrderId($orderId ?? 0);
                $objS->setOrderPartId($orderPartId ?? 0);
                $json = json_encode($objS->deleteOrderPart());
                break;
        }
        break;

    // =========================================================
    // SELECT / GET
    // =========================================================
    default:
        switch ($part ?? '') {
            case 'CL': // Lista de clientes
                $json = json_encode($objS->selectClients());
                break;

            case 'OR': // Lista de órdenes
                $objS->setClientId($clientId ?? 0);
                if (!empty($estado ?? '')) $objS->setEstado($estado);
                $json = json_encode($objS->selectOrders());
                break;

            case 'ORD': // Detalle de una orden
                $objS->setOrderId($orderId ?? 0);
                $json = json_encode($objS->selectOrder());
                break;

            case 'BR': // Marcas
                $json = json_encode($objS->selectBrands());
                break;

            case 'MD': // Modelos (filtrados por marca si viene brandId)
                $objS->setBrandId($brandId ?? 0);
                $json = json_encode($objS->selectModels());
                break;

            case 'PT': // Partes
                $objS->setBrandId($brandId ?? 0);
                $json = json_encode($objS->selectParts());
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
