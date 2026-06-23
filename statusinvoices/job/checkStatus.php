<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/statusinvoices/model/modgetToken.php';
require_once __ROOT__ . '/statusinvoices/functions.php';

$objSettings = new settings($_MYSQLI_);
$objToken = new GetToken();

// Obtener token
$responseToken = $objToken->GenerateToken();
if (!empty($responseToken['error'])) {
    echo json_encode([
        'result' => false,
        'error' => $responseToken['error'],
        'data' => ''
    ]);
    exit;
}

$token = $responseToken['data'];
$objSettings = $objSettings->getSettings(['MT_api_key_url']);
$urltokenCE = trim($objSettings['MT_api_key_url']);

// ------------------ DATOS DE PRUEBA ------------------
$clave = '50622072400310123456700100001010000000001123456789';
$fecha = date('c');

$datosFactura = [
    'clave' => $clave,
    'actividad' => '721001', // Servicios de desarrollo de software
    'consecutivo' => '00100001010000000001',
    'fecha' => $fecha,
    'emisor' => [
        'nombre' => 'EMPRESA DE PRUEBA S.A.',
        'tipo' => '02', // Jurídico
        'numero' => '3101234567',
        'provincia' => '1',
        'canton' => '01',
        'distrito' => '01',
        'barrio' => '01',
        'senas' => '100 metros norte del parque central',
        'telefono' => '22223333',
        'correo' => 'factura@empresa.com'
    ],
    'receptor' => [
        'nombre' => 'CLIENTE DE PRUEBA',
        'tipo' => '01', // Físico
        'numero' => '114520478',
        'correo' => 'cliente@correo.com'
    ],
    'condicionVenta' => '01',
    'medioPago' => '01',
    'detalle' => [
        [
            'codigo' => 'A-001',
            'cantidad' => 1,
            'unidad' => 'Unid',
            'descripcion' => 'Servicio de consultoría',
            'precio' => 10000.00,
            'impuesto' => 13.00
        ]
    ],
    'totales' => [
        'servicios_gravados' => 10000.00,
        'servicios_exentos' => 0.00,
        'mercancias_gravadas' => 0.00,
        'mercancias_exentas' => 0.00,
        'gravado' => 10000.00,
        'exento' => 0.00,
        'venta' => 10000.00,
        'descuentos' => 0.00,
        'venta_neta' => 10000.00,
        'impuestos' => 1300.00,
        'total' => 11300.00
    ]
];


// ------------------ PROCESO DE ENVÍO ------------------
try {
    // 1. Generar XML
    $xml = generarFacturaXML($datosFactura);

    // 2. Firmar XML
    $rutaCert = __ROOT__ . '/statusinvoices/nuevo.p12'; // Ajusta esta ruta

    $claveCert = '2254'; // Reemplaza por la clave real del certificado
        validarCertificadoP12($rutaCert, $claveCert);
    $xmlFirmado = firmarXML($xml, $rutaCert, $claveCert);

    // 3. Enviar a Hacienda
        $respuesta = enviarComprobante($xmlFirmado, [
            'clave' => $datosFactura['clave'],
            'fecha' => $datosFactura['fecha'],
            'emisor' => $datosFactura['emisor']['numero'],
            'receptor' => $datosFactura['receptor']['numero']
        ], $token, true); // true = sandbox



    // 4. Procesar respuesta
    $resultado = procesarRespuesta($respuesta);

    echo json_encode([
        'result' => true,
        'data' => $resultado
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'result' => false,
        'error' => $e->getMessage()
    ]);
}


// Validar que el certificado P12 se pueda leer antes de firmar
function validarCertificadoP12(string $rutaCert, string $claveCert): void {
    if (!file_exists($rutaCert)) {
        throw new Exception("El certificado no se encuentra en: $rutaCert");
    }
    $contenidoCert = file_get_contents($rutaCert);
    if ($contenidoCert === false) {
        throw new Exception("No se pudo leer el archivo del certificado.");
    }
    if (!openssl_pkcs12_read($contenidoCert, $certs, $claveCert)) {
        throw new Exception("Error al leer el certificado P12. Verifica la contraseña.");
    }
}
