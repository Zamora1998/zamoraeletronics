<?php

/**
 * webhook_wa.php
 * Webhook para integrarse con Meta Cloud API (WhatsApp).
 * Recibe mensajes y gestiona un chatbot para levantar órdenes de reparación de TV.
 */

require_once __DIR__ . '/autoconf.php';
require_once __DIR__ . '/assets/php/libLocale.php';
require_once __DIR__ . '/adm/settings/modSettings.php';
require_once __DIR__ . '/assets/php/generalFunctions.php';
require_once __DIR__ . '/usr/screens/model/modScreens.php';

// ==========================================
// CONFIGURACIÓN META API
// ==========================================

//define('VERIFY_TOKEN', '');
//define('WA_TOKEN', '');
//define('WA_PHONE_ID', '');
//define('APP_SECRET', '');


define('SESSION_TIMEOUT', 600); // 10 minutos

// 1. Verificación de webhook (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === VERIFY_TOKEN) {
        echo $_GET['hub_challenge'];
    }
    exit;
}

// ==========================================
// FIX: Responder 200 a Meta INMEDIATAMENTE
// para evitar reintentos que causan mensajes duplicados
// ==========================================
http_response_code(200);
if (ob_get_level()) ob_end_flush();
flush();

// 2. Recibir mensajes entrantes
$raw_payload = file_get_contents('php://input');

if ($raw_payload) {
    @file_put_contents(__DIR__ . '/webhook_debug.txt', date('Y-m-d H:i:s') . " - PAYLOAD RECIBIDO:\n" . $raw_payload . "\n\n", FILE_APPEND);
}

// VERIFICACIÓN DE SEGURIDAD: Solo Meta puede enviar datos
if (APP_SECRET !== '' && APP_SECRET !== 'TU_APP_SECRET_DE_META_AQUI') {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    $expected_signature = 'sha256=' . hash_hmac('sha256', $raw_payload, APP_SECRET);
    if (!hash_equals($expected_signature, $signature)) {
        @file_put_contents(__DIR__ . '/webhook_debug.txt', date('Y-m-d H:i:s') . " - FIRMA INVALIDA. Expected: $expected_signature, Got: $signature\n", FILE_APPEND);
        exit;
    }
}

$input = json_decode($raw_payload, true);
$msg   = $input['entry'][0]['changes'][0]['value']['messages'][0] ?? null;

if (!$msg) {
    exit;
}

// ── DEDUPLICACIÓN DE MENSAJES (Evita reintentos de Meta) ──
$msgId = $msg['id'] ?? '';
if ($msgId) {
    if (!is_dir(__DIR__ . '/sessions')) @mkdir(__DIR__ . '/sessions', 0755, true);
    $processedFile = __DIR__ . '/sessions/processed_msgs.json';
    $processed = file_exists($processedFile) ? json_decode(file_get_contents($processedFile), true) : [];
    if (!is_array($processed)) $processed = [];

    if (in_array($msgId, $processed)) {
        @file_put_contents(__DIR__ . '/webhook_debug.txt', date('Y-m-d H:i:s') . " - MSG DUPLICADO IGNORADO: $msgId\n", FILE_APPEND);
        exit; // Ya procesamos este mensaje
    }

    $processed[] = $msgId;
    if (count($processed) > 500) array_shift($processed); // Mantener límite mayor
    file_put_contents($processedFile, json_encode($processed));
}

$from = $msg['from'];
$text = strtolower(trim(
    $msg['text']['body'] ??
        $msg['interactive']['list_reply']['id'] ??
        $msg['interactive']['button_reply']['id'] ?? ''
));

// Cargar sesión del cliente
$session = getSession($from);
$step = $session['step'] ?? 'inicio';

// ── VERIFICAR INACTIVIDAD ──────────────────────────────────────────
if (!empty($session) && $step !== 'inicio' && $step !== 'finalizado') {
    $ultimaActividad = $session['last_activity'] ?? 0;
    $segundosTranscurridos = time() - $ultimaActividad;

    if ($segundosTranscurridos > SESSION_TIMEOUT) {
        saveSession($from, ['step' => 'finalizado', 'last_activity' => time()]);
        sendText(
            $from,
            "⏱️ *Conversación finalizada por inactividad.*\n\n" .
                "Tu sesión expiró después de " . (SESSION_TIMEOUT / 60) . " minutos sin respuesta.\n" .
                "Escribe *hola* cuando quieras empezar de nuevo. 😊"
        );
        exit;
    }
}

// Actualizar timestamp de última actividad en cada mensaje
$session['last_activity'] = time();
saveSession($from, $session);

// Verificar si el mensaje está vacío (audios, imágenes sin texto)
if ($text === '' && $step !== 'inicio') {
    $msgVacio = "⚠️ Lo siento, no recibí ningún texto.\n\n";
    switch ($step) {
        case 'nombre':
            sendText($from, $msgVacio . "Por favor digita tu *Nombre y Apellido*:");
            break;
        case 'telefono':
            sendText($from, $msgVacio . "Por favor ingresa tu *número de teléfono* de contacto:");
            break;
        case 'ubicacion':
            sendText($from, $msgVacio . "¿Cuál es tu *ubicación o dirección*?");
            break;
        case 'marca':
            sendText($from, $msgVacio . "Por favor selecciona la marca en el menú anterior o escribe la *marca* de tu televisor:");
            break;
        case 'modelo':
            sendText($from, $msgVacio . "Ingresa el *modelo o tamaño* de tu TV:");
            break;
        case 'problema':
            sendText($from, $msgVacio . "Describe brevemente el *problema o falla* que presenta:");
            break;
        case 'confirmar':
            sendText($from, $msgVacio . "Responde *SI* para confirmar o *NO* para cancelar.");
            break;
        case 'finalizado':
            sendText($from, "✅ Esta conversación ya ha sido finalizada. Si deseas crear una nueva orden, escribe *hola*.");
            break;
    }
    exit;
}

// Máquina de estados
switch ($step) {
    case 'inicio':
        sendText($from, "¡Hola! Bienvenido a *Reparaciones Electrónicas Zamora* 🛠️\nPara comenzar a crear tu orden, por favor digita tu *Nombre y Apellido*:\n\n_(Nota: Por favor responde solo con texto, no audios. El costo por la revisión del equipo es de 6000 Colones dentro del GAM.)_");
        saveSession($from, ['step' => 'nombre', 'last_activity' => time()]);
        break;

    case 'nombre':
        saveSession($from, ['step' => 'telefono', 'nombre' => $text, 'last_activity' => time()]);
        sendText($from, "Gracias. Ahora, por favor ingresa tu *número de teléfono* de contacto:");
        break;

    case 'telefono':
        $phone_clean = preg_replace('/[^0-9]/', '', $text);
        if (str_starts_with($phone_clean, '506')) {
            $phone_clean = substr($phone_clean, 3);
        }

        if (strlen($phone_clean) !== 8) {
            sendText($from, "⚠️ El número debe tener exactamente 8 dígitos (sin espacios ni guiones, ej: 88888888). Por favor, ingresa tu *número de teléfono* nuevamente:");
            break;
        }

        saveSession($from, array_merge($session, ['step' => 'ubicacion', 'telefono' => $phone_clean, 'last_activity' => time()]));
        sendText($from, "Perfecto. ¿Cuál es tu *ubicación o dirección*?");
        break;

    case 'ubicacion':
        saveSession($from, array_merge($session, ['step' => 'marca', 'ubicacion' => $text, 'last_activity' => time()]));
        sendList($from, "¡Anotado! Ahora sí, ¿cuál es la *marca* de tu televisor?\n\n_(Si tu marca no está en la lista, simplemente escríbela aquí abajo)_", 'Ver Marcas', [
            [
                'title' => 'Marcas de TV',
                'rows' => [
                    ['id' => 'marca_sony',         'title' => 'Sony'],
                    ['id' => 'marca_samsung',      'title' => 'Samsung'],
                    ['id' => 'marca_telstar',      'title' => 'Telstar'],
                    ['id' => 'marca_sankey',       'title' => 'Sankey'],
                    ['id' => 'marca_durabrand',    'title' => 'Durabrand'],
                    ['id' => 'marca_westinghouse', 'title' => 'Westinghouse'],
                    ['id' => 'marca_rca',          'title' => 'RCA']
                ]
            ]
        ]);
        break;

    case 'marca':
        $marca = str_replace('marca_', '', $text);
        saveSession($from, array_merge($session, ['step' => 'modelo', 'marca' => $marca, 'last_activity' => time()]));
        sendText($from, "Excelente, ingresa el *modelo o tamaño* de tu TV " . ucwords($marca) . ".\n\n_(Nota: Normalmente se encuentra detrás del televisor en un sticker y dice 'Model')_:");
        break;

    case 'modelo':
        saveSession($from, array_merge($session, ['step' => 'problema', 'modelo' => $text, 'last_activity' => time()]));
        sendText($from, 'Describe brevemente el *problema o falla* que presenta:');
        break;

    case 'problema':
        $data = array_merge($session, ['problema' => $text]);
        saveSession($from, array_merge($data, ['step' => 'confirmar', 'last_activity' => time()]));
        sendText(
            $from,
            "✅ *Resumen de tu orden:*\n" .
                "👤 Cliente: " . ucwords($data['nombre']) . "\n" .
                "📱 Teléfono: {$data['telefono']}\n" .
                "📍 Ubicación: {$data['ubicacion']}\n" .
                "📺 Marca: " . ucwords($data['marca']) . "\n" .
                "📋 Modelo/Tamaño: {$data['modelo']}\n" .
                "🔧 Falla: $text\n\n" .
                "Responde *SI* para confirmar o *NO* para cancelar."
        );
        break;

    case 'confirmar':
        if (str_contains($text, 'si')) {
            $result = crearOrdenLocal($session, $from);
            if ($result['result']) {
                sendText($from, "🎉 ¡Orden creada exitosamente! Tu número de orden es *#{$result['data']['orderId']}*. En breve lo contactaremos.");
            } else {
                sendText($from, "⚠️ Hubo un error al crear tu orden. Por favor intenta más tarde o contáctanos directamente.");
            }
            saveSession($from, ['step' => 'finalizado', 'last_activity' => time()]);
        } else {
            saveSession($from, ['step' => 'finalizado', 'last_activity' => time()]);
            sendText($from, '❌ Orden cancelada. Escribe *hola* para empezar de nuevo.');
        }
        break;

    case 'finalizado':
        if (in_array($text, ['hola', 'inicio', 'menu', 'nueva', 'orden', 'buenas', 'buenos'])) {
            saveSession($from, ['step' => 'nombre', 'last_activity' => time()]);
            sendText($from, "¡Hola de nuevo! Bienvenido a *Reparaciones Electrónicas Zamora* 🛠️\nPara comenzar a crear tu orden, por favor digita tu *Nombre y Apellido*:\n\n_(Nota: Por favor responde solo con texto, no audios. El costo por la revisión del equipo es de 6000 Colones dentro del GAM.)_");
        } else {
            sendText($from, "✅ Esta conversación ya ha sido finalizada. Si deseas crear una nueva orden, por favor escribe *hola*.");
        }
        break;
}

// ── Funciones de sesión ────────────────────────────────────────────
function getSession($phone)
{
    $file = __DIR__ . "/sessions/$phone.json";
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveSession($phone, $data)
{
    if (!is_dir(__DIR__ . '/sessions')) {
        @mkdir(__DIR__ . '/sessions', 0755, true);
    }
    file_put_contents(__DIR__ . "/sessions/$phone.json", json_encode($data));
}

function deleteSession($phone)
{
    @unlink(__DIR__ . "/sessions/$phone.json");
}

// ── Enviar mensajes a Meta API ─────────────────────────────────────
function sendText($to, $body)
{
    callMeta(['type' => 'text', 'text' => ['body' => $body]], $to);
}

function sendButtons($to, $body, $buttons)
{
    callMeta([
        'type' => 'interactive',
        'interactive' => [
            'type' => 'button',
            'body' => ['text' => $body],
            'action' => ['buttons' => array_map(function ($b) {
                return [
                    'type'  => 'reply',
                    'reply' => ['id' => $b['id'], 'title' => $b['title']]
                ];
            }, $buttons)]
        ]
    ], $to);
}

function sendList($to, $body, $buttonText, $sections)
{
    callMeta([
        'type' => 'interactive',
        'interactive' => [
            'type' => 'list',
            'body' => ['text' => $body],
            'action' => [
                'button' => $buttonText,
                'sections' => $sections
            ]
        ]
    ], $to);
}

// ── Guardar en Base de Datos Local ─────────────────────────────────
function crearOrdenLocal($session, $phone)
{
    global $_MYSQLI_;

    $objS = new tvScreens($_MYSQLI_);

    $nombreCliente    = "WH " . ucwords(strtolower($session['nombre']));
    $telefonoCliente  = $session['telefono'];
    $ubicacionCliente = $session['ubicacion'];

    $clientId = 0;
    $db = new dbConn();
    $sqlBusqueda = "SELECT id FROM tv_clients WHERE telefono = '{$phone}' OR telefono = '{$telefonoCliente}' LIMIT 1";
    $res = $db->processQuery($sqlBusqueda);

    if ($res['result'] && !empty($res['data'])) {
        $clientId = $res['data'][0]['id'];
    } else {
        $objS->setNombre($nombreCliente);
        $objS->setTelefono($telefonoCliente);
        $objS->setUbicacion($ubicacionCliente);
        $resCli = $objS->saveClient();
        if ($resCli['result']) {
            $clientId = $resCli['data']['clientId'];
        }
    }

    if (!$clientId) {
        return ['result' => false, 'error' => 'No se pudo generar el cliente.'];
    }

    $objS = new tvScreens($_MYSQLI_);
    $objS->setClientId($clientId);
    $modeloInfo = ucwords($session['marca']) . " " . $session['modelo'];
    $objS->setModeloLibre($modeloInfo);
    $objS->setFallaReportada($session['problema']);
    $objS->setCostoEstimado(6000);
    $objS->setEstado('pendiente');
    $objS->setTipoPago('pendiente');

    return $objS->saveOrder();
}

function callMeta($payload, $to)
{
    $payload['messaging_product'] = 'whatsapp';
    $payload['to'] = $to;

    if (WA_TOKEN === '' || WA_TOKEN === 'TU_TOKEN_DE_META_AQUI' || WA_PHONE_ID === 'TU_PHONE_ID_AQUI') {
        error_log("WhatsApp Webhook: Falta configurar WA_TOKEN o WA_PHONE_ID");
        return;
    }

    $ch = curl_init('https://graph.facebook.com/v25.0/' . WA_PHONE_ID . '/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . WA_TOKEN],
        CURLOPT_POSTFIELDS     => json_encode($payload)
    ]);
    curl_exec($ch);
}
