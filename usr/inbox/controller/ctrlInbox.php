<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/adm/mailaccounts/modMailAccounts.php';
require_once __ROOT__ . '/assets/php/libCrypt.php';
require_once __ROOT__ . '/usr/inbox/model/modInbox.php';

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
define('KEY', $ini_array["general"]["key"]);

$objCrypt        = new Crypt;
$objMailAccounts = new mailAccounts($_MYSQLI_);
$objInbox        = new InboxModel($_MYSQLI_);

// ── Obtener configuración de la cuenta (hardcoded ID=10 por ahora) ──
$ACCOUNT_ID = 10;
$objMailAccounts->setId($ACCOUNT_ID);
$accountData = $objMailAccounts->selectAccount();

if (!$accountData['result'] || empty($accountData['data'])) {
    $json = ['result' => false, 'error' => 'Mail account not found', 'data' => []];
    header('Content-Type: application/json; charset=utf-8');
    echo modGeneralFunction::toJson($json, null);
    exit;
}

$account = $accountData['data'][0];
// ── Desencriptar credenciales ───────────────────────────────────────
$password     = trim($account['password']              ?? '');
$clientSecret = trim($account['oauth_client_secret']   ?? '');
$refreshToken = trim($account['oauth_refresh_token']   ?? '');

switch ($action) {

    // ── SYNC: leer correos del servidor y guardar en BD ─────────────
    case 'C':
        switch ($part) {
            case 'SYNC':
                session_write_close();
                $json = syncInbox($account, $password, $clientSecret, $refreshToken, $objInbox);
                break;
        }
        break;

    // ── READ ────────────────────────────────────────────────────────
    case 'R':
        switch ($part) {
            // Traer todos los correos guardados en BD
            case 'INBOX':
                session_write_close();
                $syncResult = syncInbox($account, $password, $clientSecret, $refreshToken, $objInbox);
                    $json = $objInbox->selectAll();
       
                break;

            // Traer un correo específico
            case 'S':
                $objInbox->setId($id);
                $json = $objInbox->selectOne();
                break;
        }
        break;

    // ── UPDATE ──────────────────────────────────────────────────────
    case 'U':
        switch ($part) {
            // Marcar como leído
            case 'READ':
                $objInbox->setId($id);
                $json = $objInbox->markRead();
                break;
        }
        break;

    // ── REPLY ───────────────────────────────────────────────────────
    case 'M':
        switch ($part) {
            case 'REPLY':
                $objInbox->setId($id);
                $original = $objInbox->selectOne();

                if (!$original['result']) {
                    $json = ['result' => false, 'error' => 'Mail not found', 'data' => []];
                    break;
                }

                $json = sendReply($original['data'], $elBody, $account, $password, $clientSecret, $refreshToken);
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
exit;


// ════════════════════════════════════════════════════════════════════
// FUNCIÓN: Sincronizar correos del servidor → BD
// ════════════════════════════════════════════════════════════════════


function syncInbox($account, $password, $clientSecret, $refreshToken, InboxModel $objInbox)
{
    try {
        $useOauth = !empty($account['oauth'])
            && !empty($account['oauth_client_id'])
            && !empty($refreshToken);

        $imapHost = resolveImapHost($account['host']);

        // ── Si es OAuth2, obtener access_token fresco ────────────
        $accessToken = null;
        if ($useOauth) {
            $accessToken = getAccessToken($account, $clientSecret, $refreshToken);
            if (!$accessToken) {
                return ['result' => false, 'error' => 'No se pudo obtener access_token OAuth2', 'data' => []];
            }
        }

        $config = [
            'host'          => $imapHost,
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => $account['username'],
            'password'      => $useOauth ? $accessToken : $password,
            'protocol'      => 'imap',
        ];

        if ($useOauth) {
            $config['authentication'] = 'oauth';
        }

        $cm     = new \Webklex\PHPIMAP\ClientManager();
        $client = $cm->make($config);
        $client->connect();

        $folder   = $client->getFolder('INBOX');
        $messages = $folder->messages()->all()->limit(100)->setFetchOrder('desc')->get(); //enviar el from del correo de base de datos y lueg el limit

        // ── Reconectar MySQL + cargar todos los clientes de una vez ──
        $objInbox->reconnect();
        $clienteMap = $objInbox->getAllClientEmails();

        if (empty($clienteMap)) {
            $client->disconnect();
            return ['result' => false, 'error' => 'No hay clientes con email registrado', 'data' => []];
        }

        $synced = 0;
        foreach ($messages as $message) {
            $fromAddr  = $message->getFrom()[0] ?? null;
            $fromEmail = strtolower(trim($fromAddr ? $fromAddr->mail : ''));

            // ── Solo procesar si el remitente es un cliente conocido ──
            if (!isset($clienteMap[$fromEmail])) {
                continue;
            }

            $cliente   = $clienteMap[$fromEmail];
            $messageId = $message->getMessageId() ?? '';

            if ($objInbox->existsByMessageId($messageId)) {
                continue;
            }

            $fromName = $fromAddr ? $fromAddr->personal : '';

            $objInbox->setMessageId($messageId);
            $objInbox->setMailaccountId($account['id']);
            $objInbox->setFromEmail($fromEmail);
            $objInbox->setFromName($fromName);
            $objInbox->setSubject($message->getSubject() ?? '(Sin asunto)');
            $objInbox->setBody($message->getHTMLBody() ?: $message->getTextBody() ?: '');

            $dateAttr = $message->getDate()->first();
            $objInbox->setReceivedAt(
                $dateAttr ? date('Y-m-d H:i:s', strtotime((string)$dateAttr)) : date('Y-m-d H:i:s')
            );

            $objInbox->setRead((int)$message->getFlags()->contains('Seen'));
            $objInbox->setClienteId($cliente['id']);

            $insertResult = $objInbox->insert();
            if (!$insertResult['result']) {
                file_put_contents(
                    __ROOT__ . '/inbox_insert_debug.txt',
                    "ERROR INSERT correo {$synced}\n" .
                        "MESSAGE_ID: {$messageId}\n" .
                        "FROM: {$fromEmail}\n" .
                        "ERROR: " . ($insertResult['error'] ?? 'desconocido') . "\n---\n",
                    FILE_APPEND
                );
                continue;
            }

            $synced++;
        }

        $client->disconnect();

        return ['result' => true, 'error' => '', 'data' => ['synced' => $synced]];
    } catch (Exception $e) {
        return ['result' => false, 'error' => $e->getMessage(), 'data' => []];
    }
}


// ════════════════════════════════════════════════════════════════════
// HELPER: canjear refresh_token → access_token
// ════════════════════════════════════════════════════════════════════
function getAccessToken($account, $clientSecret, $refreshToken): ?string
{
    $type = $account['oauth_type'] ?? '';
    
    switch ($type) {
        case 'Google':
        case 'oauth2-google':
            $url    = 'https://oauth2.googleapis.com/token';
            $params = [
                'client_id'     => $account['oauth_client_id'],
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ];
            break;

        case 'Microsoft':
        case 'oauth2-microsoft':
        case 'Azure':
        case 'oauth2-azure':
            $tenantId = $account['oauth_tenant_id'] ?? 'common';
            $url      = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
            $params   = [
                'client_id'     => $account['oauth_client_id'],
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
                'scope'         => 'https://outlook.office365.com/IMAP.AccessAsUser.All offline_access',
            ];
            break;

        default:
            // ── Debug: qué tipo llegó ────────────────────────────
            file_put_contents(
                __ROOT__ . '/inbox_oauth_debug.txt',
                "TIPO DESCONOCIDO: " . var_export($type, true) . "\n" .
                    "ACCOUNT: " . var_export($account, true) . "\n"
            );
            return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);

    if (!$response) return null;

    $data = json_decode($response, true);

    return $data['access_token'] ?? null;
}

// ════════════════════════════════════════════════════════════════════
// FUNCIÓN: Enviar reply usando PHPMailer (reutiliza tu cola)
// ════════════════════════════════════════════════════════════════════
function sendReply($original, $replyBody, $account, $password, $clientSecret, $refreshToken)
{
    require_once __ROOT__ . '/vendor/autoload.php';



    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host     = $account['host'];
        $mail->Port     = $account['port'];
        $mail->SMTPAuth = $account['smtpauth'];

        if ($account['smtpsecure'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($account['smtpsecure'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $useOauth = !empty($account['oauth'])
            && !empty($account['oauth_client_id'])
            && !empty($refreshToken);

        if ($useOauth) {
            $mail->AuthType = 'XOAUTH2';
            $provider = null;
            switch ($account['oauth_type']) {
                case 'Google':
                case 'oauth2-google':
                    $provider = new Google([
                        'clientId'     => $account['oauth_client_id'],
                        'clientSecret' => $clientSecret,
                    ]);
                    break;
                case 'Microsoft':
                case 'oauth2-microsoft':
                    $provider = new Microsoft([
                        'clientId'     => $account['oauth_client_id'],
                        'clientSecret' => $clientSecret,
                    ]);
                    break;
                case 'Azure':
                case 'oauth2-azure':
                    $provider = new Azure([
                        'clientId'     => $account['oauth_client_id'],
                        'clientSecret' => $clientSecret,
                        'tenantId'     => $account['oauth_tenant_id'] ?? 'common',
                    ]);
                    break;
            }
            $mail->setOAuth(new OAuth([
                'provider'     => $provider,
                'clientId'     => $account['oauth_client_id'],
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName'     => $account['username'],
            ]));
        } else {
            $mail->Username = $account['username'];
            $mail->Password = $password;
        }

        $mail->setFrom($account['username'], $account['fromname'] ?? '');
        $mail->addAddress($original['from_email'], $original['from_name'] ?? '');

        // ── Headers para que sea hilo/reply correcto ─────────────
        if (!empty($original['message_id'])) {
            $mail->addCustomHeader('In-Reply-To', $original['message_id']);
            $mail->addCustomHeader('References',  $original['message_id']);
        }

        $mail->Subject = 'Re: ' . ltrim(preg_replace('/^(Re:\s*)+/i', '', $original['subject']));
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Body    = nl2br(htmlspecialchars($replyBody));
        $mail->AltBody = $replyBody;

        $mail->send();

        return ['result' => true, 'error' => '', 'data' => []];

    } catch (Exception $e) {
        return ['result' => false, 'error' => $mail->ErrorInfo, 'data' => []];
    }
}


// ════════════════════════════════════════════════════════════════════
// HELPER: resolver host IMAP desde el host SMTP
// ════════════════════════════════════════════════════════════════════
function resolveImapHost($smtpHost)
{
    $map = [
        'smtp.gmail.com'         => 'imap.gmail.com',
        'smtp.office365.com'     => 'outlook.office365.com',
        'smtp-mail.outlook.com'  => 'outlook.office365.com',
        'smtp.live.com'          => 'imap-mail.outlook.com',
    ];

    return $map[$smtpHost] ?? str_replace('smtp.', 'imap.', $smtpHost);
}