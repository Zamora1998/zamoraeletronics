<?

require_once __ROOT__ . '/lib/xmlseclibs/src/XMLSecurityDSig.php';
require_once __ROOT__ . '/lib/xmlseclibs/src/XMLSecurityKey.php';


use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

// Generar XML para factura electrónica (versión 4.4)
function generarFacturaXML(array $d): string {
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');

    $xml->startElementNS(null, 'FacturaElectronica', 'https://tribunet.hacienda.go.cr/docs/esquemas/2023/v4.4/facturaElectronica');
    $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $xml->writeAttribute(
        'xsi:schemaLocation',
        'https://tribunet.hacienda.go.cr/docs/esquemas/2023/v4.4/facturaElectronica https://www.hacienda.go.cr/ATV/Esquemas/2023/v4.4/FacturaElectronica.xsd'
    );
    $xml->writeAttribute('version', '4.4');

    // Encabezado
    $xml->writeElement('Clave', $d['clave']);
    $xml->writeElement('CodigoActividad', $d['actividad']);
    $xml->writeElement('NumeroConsecutivo', $d['consecutivo']);
    $xml->writeElement('FechaEmision', $d['fecha']);

    // Emisor
    $xml->startElement('Emisor');
        $xml->writeElement('Nombre', $d['emisor']['nombre']);
        $xml->startElement('Identificacion');
            $xml->writeElement('Tipo', $d['emisor']['tipo']);
            $xml->writeElement('Numero', $d['emisor']['numero']);
        $xml->endElement();
        $xml->startElement('Ubicacion');
            $xml->writeElement('Provincia', $d['emisor']['provincia']);
            $xml->writeElement('Canton', $d['emisor']['canton']);
            $xml->writeElement('Distrito', $d['emisor']['distrito']);
            $xml->writeElement('Barrio', $d['emisor']['barrio']);
            $xml->writeElement('OtrasSenas', $d['emisor']['senas']);
        $xml->endElement();
        $xml->startElement('Telefono');
            $xml->writeElement('CodigoPais', '506');
            $xml->writeElement('NumTelefono', $d['emisor']['telefono']);
        $xml->endElement();
        $xml->writeElement('CorreoElectronico', $d['emisor']['correo']);
    $xml->endElement();

    // Receptor
    $xml->startElement('Receptor');
        $xml->writeElement('Nombre', $d['receptor']['nombre']);
        $xml->startElement('Identificacion');
            $xml->writeElement('Tipo', $d['receptor']['tipo']);
            $xml->writeElement('Numero', $d['receptor']['numero']);
        $xml->endElement();
        $xml->writeElement('CorreoElectronico', $d['receptor']['correo']);
    $xml->endElement();

    // Condición de venta y medio de pago
    $xml->writeElement('CondicionVenta', $d['condicionVenta'] ?? '01'); // Contado por defecto
    $xml->writeElement('PlazoCredito', $d['plazoCredito'] ?? '');
    $xml->writeElement('MedioPago', $d['medioPago'] ?? '01'); // Efectivo por defecto

    // Detalle de productos
    $linea = 1;
    foreach ($d['detalle'] as $item) {
        $xml->startElement('LineaDetalle');
            $xml->writeElement('NumeroLinea', $linea++);
            $xml->writeElement('CodigoComercial', $item['codigo']);
            $xml->writeElement('Cantidad', $item['cantidad']);
            $xml->writeElement('UnidadMedida', $item['unidad']);
            $xml->writeElement('Detalle', $item['descripcion']);
            $xml->writeElement('PrecioUnitario', $item['precio']);
            $xml->writeElement('MontoTotal', round($item['precio'] * $item['cantidad'], 5));
            $xml->writeElement('SubTotal', round($item['precio'] * $item['cantidad'], 5));

            if (!empty($item['impuesto']) && $item['impuesto'] > 0) {
                $xml->startElement('Impuesto');
                    $xml->writeElement('Codigo', '01'); // IVA
                    $xml->writeElement('Tarifa', $item['impuesto']);
                    $xml->writeElement('Monto', round(($item['precio'] * $item['cantidad']) * ($item['impuesto']/100), 5));
                $xml->endElement();
            }
            $montoTotalLinea = ($item['precio'] * $item['cantidad']) * (1 + ($item['impuesto'] ?? 0)/100);
            $xml->writeElement('MontoTotalLinea', round($montoTotalLinea, 5));
        $xml->endElement();
    }

    // Resumen
    $xml->startElement('ResumenFactura');
        $xml->startElement('CodigoTipoMoneda');
            $xml->writeElement('CodigoMoneda', $d['moneda'] ?? 'CRC');
            $xml->writeElement('TipoCambio', $d['tipoCambio'] ?? '1.00');
        $xml->endElement();

        $xml->writeElement('TotalServGravados', $d['totales']['servicios_gravados'] ?? '0');
        $xml->writeElement('TotalServExentos', $d['totales']['servicios_exentos'] ?? '0');
        $xml->writeElement('TotalMercanciasGravadas', $d['totales']['mercancias_gravadas'] ?? '0');
        $xml->writeElement('TotalMercanciasExentas', $d['totales']['mercancias_exentas'] ?? '0');
        $xml->writeElement('TotalGravado', $d['totales']['gravado'] ?? '0');
        $xml->writeElement('TotalExento', $d['totales']['exento'] ?? '0');
        $xml->writeElement('TotalVenta', $d['totales']['venta'] ?? '0');
        $xml->writeElement('TotalDescuentos', $d['totales']['descuentos'] ?? '0');
        $xml->writeElement('TotalVentaNeta', $d['totales']['venta_neta'] ?? ($d['totales']['venta'] ?? '0'));
        $xml->writeElement('TotalImpuesto', $d['totales']['impuestos'] ?? '0');
        $xml->writeElement('TotalComprobante', $d['totales']['total'] ?? '0');
    $xml->endElement();

    $xml->endElement(); // FacturaElectronica
    $xml->endDocument();

    return $xml->outputMemory();
}

// Generar XML para Nota de Crédito (versión 4.4)
function generarNotaCreditoXML(array $d): string {
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');

    $xml->startElementNS(null, 'NotaCreditoElectronica', 'https://tribunet.hacienda.go.cr/docs/esquemas/2023/v4.4/notaCreditoElectronica');
    $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $xml->writeAttribute(
        'xsi:schemaLocation',
        'https://tribunet.hacienda.go.cr/docs/esquemas/2023/v4.4/notaCreditoElectronica https://www.hacienda.go.cr/ATV/Esquemas/2023/v4.4/NotaCreditoElectronica.xsd'
    );
    $xml->writeAttribute('version', '4.4');

    // Encabezado
    $xml->writeElement('Clave', $d['clave']);
    $xml->writeElement('CodigoActividad', $d['actividad']);
    $xml->writeElement('NumeroConsecutivo', $d['consecutivo']);
    $xml->writeElement('FechaEmision', $d['fecha']);
    $xml->writeElement('CodigoTipoNota', $d['tipoNota']); // 01=Anulación, 02=Devolución, etc
    $xml->writeElement('NumeroFacturaModificada', $d['numFacturaModificada']);
    $xml->writeElement('FechaFacturaModificada', $d['fechaFacturaModificada']);

    // Emisor
    $xml->startElement('Emisor');
        $xml->writeElement('Nombre', $d['emisor']['nombre']);
        $xml->startElement('Identificacion');
            $xml->writeElement('Tipo', $d['emisor']['tipo']);
            $xml->writeElement('Numero', $d['emisor']['numero']);
        $xml->endElement();
        $xml->startElement('Ubicacion');
            $xml->writeElement('Provincia', $d['emisor']['provincia']);
            $xml->writeElement('Canton', $d['emisor']['canton']);
            $xml->writeElement('Distrito', $d['emisor']['distrito']);
            $xml->writeElement('Barrio', $d['emisor']['barrio']);
            $xml->writeElement('OtrasSenas', $d['emisor']['senas']);
        $xml->endElement();
        $xml->startElement('Telefono');
            $xml->writeElement('CodigoPais', '506');
            $xml->writeElement('NumTelefono', $d['emisor']['telefono']);
        $xml->endElement();
        $xml->writeElement('CorreoElectronico', $d['emisor']['correo']);
    $xml->endElement();

    // Receptor
    $xml->startElement('Receptor');
        $xml->writeElement('Nombre', $d['receptor']['nombre']);
        $xml->startElement('Identificacion');
            $xml->writeElement('Tipo', $d['receptor']['tipo']);
            $xml->writeElement('Numero', $d['receptor']['numero']);
        $xml->endElement();
        $xml->writeElement('CorreoElectronico', $d['receptor']['correo']);
    $xml->endElement();

    // Condición de venta y medio de pago
    $xml->writeElement('CondicionVenta', $d['condicionVenta'] ?? '01'); // Contado
    $xml->writeElement('PlazoCredito', $d['plazoCredito'] ?? '');
    $xml->writeElement('MedioPago', $d['medioPago'] ?? '01'); // Efectivo

    // Detalle de productos (igual que factura)
    $linea = 1;
    foreach ($d['detalle'] as $item) {
        $xml->startElement('LineaDetalle');
            $xml->writeElement('NumeroLinea', $linea++);
            $xml->writeElement('CodigoComercial', $item['codigo']);
            $xml->writeElement('Cantidad', $item['cantidad']);
            $xml->writeElement('UnidadMedida', $item['unidad']);
            $xml->writeElement('Detalle', $item['descripcion']);
            $xml->writeElement('PrecioUnitario', $item['precio']);
            $xml->writeElement('MontoTotal', round($item['precio'] * $item['cantidad'], 5));
            $xml->writeElement('SubTotal', round($item['precio'] * $item['cantidad'], 5));

            if (!empty($item['impuesto']) && $item['impuesto'] > 0) {
                $xml->startElement('Impuesto');
                    $xml->writeElement('Codigo', '01');
                    $xml->writeElement('Tarifa', $item['impuesto']);
                    $xml->writeElement('Monto', round(($item['precio'] * $item['cantidad']) * ($item['impuesto']/100), 5));
                $xml->endElement();
            }
            $montoTotalLinea = ($item['precio'] * $item['cantidad']) * (1 + ($item['impuesto'] ?? 0)/100);
            $xml->writeElement('MontoTotalLinea', round($montoTotalLinea, 5));
        $xml->endElement();
    }

    // Resumen
    $xml->startElement('ResumenFactura');
        $xml->startElement('CodigoTipoMoneda');
            $xml->writeElement('CodigoMoneda', $d['moneda'] ?? 'CRC');
            $xml->writeElement('TipoCambio', $d['tipoCambio'] ?? '1.00');
        $xml->endElement();

        $xml->writeElement('TotalServGravados', $d['totales']['servicios_gravados'] ?? '0');
        $xml->writeElement('TotalServExentos', $d['totales']['servicios_exentos'] ?? '0');
        $xml->writeElement('TotalMercanciasGravadas', $d['totales']['mercancias_gravadas'] ?? '0');
        $xml->writeElement('TotalMercanciasExentas', $d['totales']['mercancias_exentas'] ?? '0');
        $xml->writeElement('TotalGravado', $d['totales']['gravado'] ?? '0');
        $xml->writeElement('TotalExento', $d['totales']['exento'] ?? '0');
        $xml->writeElement('TotalVenta', $d['totales']['venta'] ?? '0');
        $xml->writeElement('TotalDescuentos', $d['totales']['descuentos'] ?? '0');
        $xml->writeElement('TotalVentaNeta', $d['totales']['venta_neta'] ?? ($d['totales']['venta'] ?? '0'));
        $xml->writeElement('TotalImpuesto', $d['totales']['impuestos'] ?? '0');
        $xml->writeElement('TotalComprobante', $d['totales']['total'] ?? '0');
    $xml->endElement();

    $xml->endElement(); // NotaCreditoElectronica
    $xml->endDocument();

    return $xml->outputMemory();
}

// Generar XML para Mensaje Receptor (aceptación o rechazo del comprobante)
function generarMensajeReceptor(array $d, int $tipo): string {
    // $tipo: 1=Aceptación, 2=Aceptación Parcial, 3=Rechazo
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');

    $xml->startElementNS(null, 'MensajeReceptor', 'https://tribunet.hacienda.go.cr/docs/esquemas/2023/v4.4/mensajeReceptor');
    $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $xml->writeAttribute(
        'xsi:schemaLocation',
        'https://tribunet.hacienda.go.cr/docs/esquemas/2023/v4.4/mensajeReceptor https://www.hacienda.go.cr/ATV/Esquemas/2023/v4.4/MensajeReceptor.xsd'
    );
    $xml->writeAttribute('version', '4.4');

    $xml->writeElement('Clave', $d['clave']);
    $xml->writeElement('NumeroCedulaEmisor', $d['emisor']);
    $xml->writeElement('NumeroCedulaReceptor', $d['receptor']);
    $xml->writeElement('FechaEmisionDoc', $d['fechaDoc']);
    $xml->writeElement('Mensaje', $d['mensaje']);
    $xml->writeElement('Estado', $tipo);

    if ($tipo === 2) {
        // Si aceptación parcial, se puede agregar detalle adicional
        if (isset($d['detalle'])) {
            $xml->startElement('DetalleMensaje');
            foreach ($d['detalle'] as $item) {
                $xml->startElement('LineaDetalle');
                $xml->writeElement('NumeroLinea', $item['linea']);
                $xml->writeElement('EstadoLinea', $item['estado']); // 1=aceptado, 2=rechazado
                $xml->writeElement('MensajeLinea', $item['mensaje']);
                $xml->endElement();
            }
            $xml->endElement();
        }
    }

    $xml->endElement(); // MensajeReceptor
    $xml->endDocument();

    return $xml->outputMemory();
}


function firmarXML(string $xml, string $pfxPath, string $pfxPass): string {
    if (!file_exists($pfxPath)) {
        throw new Exception("Certificado P12 no encontrado en: $pfxPath");
    }

    $pkcs12 = file_get_contents($pfxPath);
    if (!openssl_pkcs12_read($pkcs12, $certs, $pfxPass)) {
        throw new Exception("Error al leer el certificado P12.");
    }

    if (!isset($certs['pkey'], $certs['cert'])) {
        throw new Exception("Certificado P12 inválido o sin clave/certificado.");
    }

    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = false;
    $doc->loadXML($xml);

    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
    $objDSig->addReference(
        $doc,
        XMLSecurityDSig::SHA256,
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
        ['force_uri' => true]
    );

    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
    $objKey->loadKey($certs['pkey'], false);

    $objDSig->sign($objKey);
    $objDSig->add509Cert($certs['cert'], true, false, ['subjectName' => true]);

    $objDSig->appendSignature($doc->documentElement);

    return $doc->saveXML();
}
    function enviarComprobante(string $xmlFirmado, array $encabezado, string $token, bool $sandbox = true): array {
    $url = $sandbox
        ? 'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion'
        : 'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion';

$headers = [
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: key=' . trim($token)
];



    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlFirmado);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Error CURL al enviar comprobante: $error");
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Error HTTP al enviar comprobante: código $httpCode, respuesta: $response");
    }

    $json = json_decode($response, true);
    if ($json === null) {
        throw new Exception("Respuesta no es JSON válido: $response");
    }

    return $json;
}

// Procesar respuesta (puedes adaptarla según tus necesidades)
function procesarRespuesta(array $respuesta): array {
    // Ejemplo simple: verificar estado y mensaje
    $resultado = [];
    $resultado['ind-estado'] = $respuesta['ind-estado'] ?? null;
    $resultado['mensaje'] = $respuesta['mensaje'] ?? 'No disponible';

    if (isset($respuesta['comprobanteXml'])) {
        $resultado['comprobanteXml'] = base64_decode($respuesta['comprobanteXml']);
    }

    if (isset($respuesta['clave'])) {
        $resultado['clave'] = $respuesta['clave'];
    }

    return $resultado;
}
