<?php
require_once __DIR__ . '/../../autoconf.php';
include_once __ROOT__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;

require_once __ROOT__ . '/vendor/autoload.php';
require_once __ROOT__ . '/assets/php/libCrypt.php';
require_once __ROOT__ . '/mail/model/modMailQueue.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
define('KEY', $ini_array["general"]["key"]);

// ============================================================
// CONFIGURACIÓN DE THROTTLING
// Ajusta estos valores según tu proveedor SMTP:
//   Gmail/Google Workspace : BATCH=20, DELAY=3
//   Microsoft 365/Outlook  : BATCH=30, DELAY=2
//   SendGrid/Mailgun/SES   : BATCH=100, DELAY=0
// ============================================================
define('BATCH_SIZE',      50);   // Correos máximos por ejecución del job
define('DELAY_PER_MAIL',   2);   // Segundos de pausa entre cada correo
define('DELAY_ON_FAIL',    5);   // Segundos de pausa extra tras un fallo
define('MAX_ATTEMPTS',     3);   // Reintentos máximos antes de abandonar

$objCrypt = new Crypt;
$objQueue = new mailQueue($_MYSQLI_);

// Se pasa BATCH_SIZE al select para que la query traiga solo ese límite
// Tu modMailQueue->select() debe aceptar $limit y $maxAttempts como parámetros
// Ver nota al pie del archivo
$queue = $objQueue->select(BATCH_SIZE, MAX_ATTEMPTS)['data'];

if (empty($queue)) {
    $json = [
        'result' => true,
        'error'  => 'Queue Empty',
        'data'   => ''
    ];
    header('Content-Type: application/json; charset=utf-8');
    echo modGeneralFunction::toJson($json, null);
    exit;
}

$sentCount  = 0;
$failCount  = 0;
$queueTotal = count($queue);

foreach ($queue as $idx => $current) {
    $mail = new PHPMailer(true);

    try {
        // ── Debug ────────────────────────────────────────────────
        $mail->SMTPDebug = $current['debug']
            ? SMTP::DEBUG_SERVER
            : SMTP::DEBUG_OFF;

        // ── Servidor SMTP ────────────────────────────────────────
        $mail->isSMTP();
        $mail->Host     = $current['host'];
        $mail->SMTPAuth = $current['smtpauth'];
        $mail->Port     = $current['port'];

        if ($current['smtpsecure'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($current['smtpsecure'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        // ── Autenticación: OAuth2 o estándar ─────────────────────
        $useOauth = !empty($current['oauth'])
            && !empty($current['oauth_client_id'])
            && !empty($current['oauth_refresh_token']);

        if ($useOauth) {
            $mail->AuthType   = 'XOAUTH2';
            $clientSecret     = trim($objCrypt->decrypt(KEY, $current['oauth_client_secret']));
            $refreshToken     = trim($objCrypt->decrypt(KEY, $current['oauth_refresh_token']));

            $provider = null;
            switch ($current['oauth_type']) {
                case 'Google':
                case 'oauth2-google':
                    $provider = new Google([
                        'clientId'     => $current['oauth_client_id'],
                        'clientSecret' => $clientSecret,
                    ]);
                    break;

                case 'Microsoft':
                case 'oauth2-microsoft':
                    $provider = new Microsoft([
                        'clientId'     => $current['oauth_client_id'],
                        'clientSecret' => $clientSecret,
                    ]);
                    break;

                case 'Azure':
                case 'oauth2-azure':
                    $provider = new Azure([
                        'clientId'     => $current['oauth_client_id'],
                        'clientSecret' => $clientSecret,
                        'tenantId'     => $current['oauth_tenant_id'] ?? 'common',
                    ]);
                    break;
            }

            if ($provider) {
                $mail->setOAuth(new OAuth([
                    'provider'     => $provider,
                    'clientId'     => $current['oauth_client_id'],
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName'     => $current['username'],
                ]));
            }
        } else {
            $mail->Username = $current['username'];
            $mail->Password = trim($objCrypt->decrypt(KEY, $current['password']));
        }

        // ── Remitente ────────────────────────────────────────────
        $mail->setFrom($current['username'], $current['fromname']);

        // ── Destinatarios ────────────────────────────────────────
        $typeIds   = explode('|', $current['addresstype_ids']);
        $addresses = explode('|', $current['addresses']);
        $names     = explode('|', $current['names']);

        foreach ($typeIds as $k => $typeId) {
            switch ((int)$typeId) {
                case 1: // reply-to (descomentá si lo necesitás)
                    // $mail->addReplyTo($addresses[$k], html_entity_decode($names[$k]));
                    break;
                case 2: // to
                    $mail->addAddress($addresses[$k], $names[$k]);
                    break;
                case 3: // cc
                    $mail->addCC($addresses[$k], $names[$k]);
                    break;
                case 4: // bcc
                    $mail->addBCC($addresses[$k], $names[$k]);
                    break;
            }
        }

        // ── Adjuntos ─────────────────────────────────────────────
        $attFiles = explode('|', $current['attFiles'] ?? '');
        $attNames = explode('|', $current['attNames'] ?? '');

        if (!empty($attFiles) && $attFiles[0] !== '') {
            foreach ($attFiles as $k => $attFile) {
                $fullPath = __ROOT__ . $attFile;
                if (!empty($attFile) && file_exists($fullPath)) {
                    $mail->addAttachment($fullPath, $attNames[$k] ?? '');
                }
            }
        }

        // ── Contenido ────────────────────────────────────────────
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $current['subject'];
        $mail->Body    = $current['body'];
        $mail->AltBody = $current['altbody'];

        // ── Envío ────────────────────────────────────────────────
        $mail->send();
        $objQueue->updateSent($current['id']);
        $sentCount++;

        // ── Throttle: pausa entre correos ────────────────────────
        // No pausar después del último correo del lote
        $isLastInBatch = ($idx === array_key_last($queue));
        if (!$isLastInBatch && DELAY_PER_MAIL > 0) {
            sleep(DELAY_PER_MAIL);
        }
    } catch (IdentityProviderException $e) {
        // Capturar errores OAuth específicos (invalid_grant, etc.)
        $errorMsg = $e->getMessage();
        if (method_exists($e, 'getResponseBody')) {
            $body = $e->getResponseBody();
            if (is_array($body) && isset($body['error'])) {
                $errorMsg = $body['error'] . ': ' . ($body['error_description'] ?? $errorMsg);
            }
        }
        
        $classifiedError = classifyEmailError($errorMsg);
        $objQueue->updateAttempt($current['id'], $classifiedError);
        $failCount++;

        // Pausa mayor tras un fallo para no saturar el servidor
        if (DELAY_ON_FAIL > 0) {
            sleep(DELAY_ON_FAIL);
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();

        if (!empty($mail->ErrorInfo)) {
            $errorMsg .= ' | ' . $mail->ErrorInfo;
        }
        $classifiedError = classifyEmailError($errorMsg);
        $objQueue->updateAttempt($current['id'], $classifiedError);
        $failCount++;

        // Pausa mayor tras un fallo para no saturar el servidor
        if (DELAY_ON_FAIL > 0) {
            sleep(DELAY_ON_FAIL);
        }
    }
}
function classifyEmailError($errorMsg)
{
    $errorLower = strtolower($errorMsg);

    if (strpos($errorLower, 'invalid_grant') !== false) {
        return 'OAUTH_ERROR: Refresh token expired, revoked or invalid. Action: Reauthenticate the account.';
    }

    if (strpos($errorLower, 'invalid_client') !== false) {
        return 'OAUTH_ERROR: Invalid client credentials. Check OAuth client ID and secret.';
    }

    if (strpos($errorLower, 'access_denied') !== false) {
        return 'OAUTH_ERROR: Access denied. Check user permissions or account restrictions.';
    }

    if (strpos($errorLower, 'oauth') !== false || strpos($errorLower, 'xoauth') !== false) {
        return 'OAUTH_ERROR: OAuth authentication failed. ' . substr($errorMsg, 0, 100);
    }

    if (strpos($errorLower, 'authenticate') !== false || strpos($errorLower, '535') !== false) {
        return 'AUTHENTICATION_ERROR: Invalid username or password.';
    }

    if (strpos($errorLower, '530') !== false) {
        return 'AUTH_REQUIRED: Authentication required.';
    }

    if (strpos($errorLower, 'unable to connect') !== false || strpos($errorLower, 'could not connect') !== false) {
        return 'CONNECTION_ERROR: Unable to connect to SMTP server.';
    }

    if (strpos($errorLower, 'timeout') !== false) {
        return 'TIMEOUT_ERROR: Server timeout.';
    }

    if (strpos($errorLower, 'ssl') !== false || strpos($errorLower, 'tls') !== false) {
        return 'SSL/TLS_ERROR: Security protocol issue.';
    }

    if (strpos($errorLower, 'invalid address') !== false) {
        return 'INVALID_EMAIL: Invalid email format.';
    }

    if (strpos($errorLower, 'recipient') !== false) {
        return 'RECIPIENT_ERROR: Delivery rejected.';
    }

    if (strpos($errorLower, 'relaying') !== false) {
        return 'RELAY_ERROR: Relaying denied.';
    }

    return 'SMTP_ERROR: ' . substr($errorMsg, 0, 150);
}
// ── Respuesta JSON ───────────────────────────────────────────
$json = [
    'result' => true,
    'error'  => $failCount > 0 ? "Failed to send {$failCount} messages" : '',
    'data'   => [
        'sent'   => $sentCount,
        'failed' => $failCount,
        'total'  => $queueTotal,
    ]
];

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
exit;
