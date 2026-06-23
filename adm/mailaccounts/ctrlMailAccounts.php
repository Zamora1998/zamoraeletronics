<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/adm/mailaccounts/modMailAccounts.php';
require_once __ROOT__ . '/mail/model/modMailComposer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;


$objMailAccounts = new mailAccounts($_MYSQLI_);
$hasProtected = in_array('dev', $authParams);

switch ($action) {
    case 'C':
        switch ($part) {
            case 'C':
                $objMailAccounts->setId($id);
                $objMailAccounts->setUser($elUser);
                $objMailAccounts->setPassword($elPass);
                $objMailAccounts->setHost($elHost);
                $objMailAccounts->setPort($elPort);
                $objMailAccounts->setSMTP($elSmtpsecure);
                $objMailAccounts->setProtocol($elProtocol);
                $objMailAccounts->setAuth($elSmtpauth);
                $objMailAccounts->setProtected($elProtected);
                $objMailAccounts->setReplyto($elReplyto);
                $objMailAccounts->setDebug($elDebug);
                $objMailAccounts->setEnabled($elEnabled);
                $objMailAccounts->setOauth($elOauth);
                $objMailAccounts->set_oauth_client_id($elOauthClientId);
                $objMailAccounts->set_oauth_client_secret($elOauthClientSecret);
                $objMailAccounts->set_oauth_refresh_token($elOauthRefreshToken);
                $objMailAccounts->set_oauth_type($elOauthType);
                $objMailAccounts->set_oauth_tenant_id($elOauthTenantId ?? '');
                $json = $objMailAccounts->insertAccountDetails();
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'S':
                $objMailAccounts->setId($id);
                $json = $objMailAccounts->deleteAccount();
                break;
        }
        break;
    case 'U':
        switch ($part) {
        }
        break;
    case 'R': //Read
        switch ($part) {
            case 'A':
                $objMailAccounts->setHasProtected($hasProtected ? 1 : 0);
                $json = $objMailAccounts->selectMailAccounts();
                break;
            case 'S':
                if ($hasProtected) {
                    $objMailAccounts->setId($id);
                    $json = $objMailAccounts->selectAccount();
                } else {
                    $json = array('result' => false, 'error' => 'Access denied', 'data' => []);
                }
                break;
            case 'T':
                $oauthParams = [
                    'enabled'       => (bool)($elOauth ?? 0),
                    'type'          => $elOauthType ?? '',
                    'clientId'      => $elOauthClientId ?? '',
                    'clientSecret'  => $elOauthClientSecret ?? '',
                    'refreshToken'  => $elOauthRefreshToken ?? '',
                    'tenantId'      => $elOauthTenantId ?? '',
                ];
                $json = testEmail($elHost, $elUser, $elPass, $elPort, $elSmtpauth, $elSmtpsecure, $elReplyto, $selUser, $elDebug, $chrLang, $oauthParams);
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
function testEmail($host, $user, $password, $port, $auth, $smtpsecure, $replyto, $selUser, $debug, $chrLang = 'en', $oauthParams = [])
{
    $settingSels = [
        'debug_email',
        'debug_name'
    ];
    $objSettings = new settings;
    $settings = $objSettings->getSettings($settingSels);

    $result = false;
    $error = '';
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->getSMTPInstance()->Timelimit = 30;
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = $auth;

        if ($smtpsecure == 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($smtpsecure == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = '';
        }

        // OAuth2 o autenticación estándar
        $useOauth = !empty($oauthParams['enabled']) && !empty($oauthParams['clientId']) && !empty($oauthParams['refreshToken']);

        if ($useOauth) {
            $mail->AuthType = 'XOAUTH2';
            $provider = null;
            switch ($oauthParams['type']) {
                case 'Google':
                case 'oauth2-google':
                    $provider = new Google([
                        'clientId'     => $oauthParams['clientId'],
                        'clientSecret' => $oauthParams['clientSecret'],
                    ]);
                    break;
                case 'Microsoft':
                case 'oauth2-microsoft':
                    $provider = new Microsoft([
                        'clientId'     => $oauthParams['clientId'],
                        'clientSecret' => $oauthParams['clientSecret'],
                    ]);
                    break;
                case 'Azure':
                case 'oauth2-azure':
                    $provider = new Azure([
                        'clientId'     => $oauthParams['clientId'],
                        'clientSecret' => $oauthParams['clientSecret'],
                        'tenantId'     => $oauthParams['tenantId'],
                    ]);
                    break;
            }

            $mail->setOAuth(new OAuth([
                'provider'     => $provider,
                'clientId'     => $oauthParams['clientId'],
                'clientSecret' => $oauthParams['clientSecret'],
                'refreshToken' => $oauthParams['refreshToken'],
                'userName'     => $user,
            ]));
        } else {
            $mail->Username = $user;
            $mail->Password = $password;
        }

        if (empty($replyto)) {
            $replyto = $user;
        }

        $objMail = new mailComposer($_MYSQLI_);
        $objMail->setId(3);
        $objMail->setUserId($selUser);
        $objMail->setLanguageId($chrLang);
        $maildata = $objMail->select();

        if ($maildata['result']) {
            $from = $user;
            $name = 'EDMI';
            $to = $replyto;
            $subject = $maildata['subject'];
            $body = $maildata['body'];
            $altbody = $maildata['altbody'];

            // Recipients
            $mail->setFrom($from, $name);
            $mail->addAddress($to);
            $mail->addReplyTo($replyto);
            if ($debug) {
                $mail->addBCC($settings['debug_email'], $settings['debug_name']);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altbody;
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str, $level) {
                error_log("SMTP DEBUG: $str");
            };
            // Send email
            $mail->send();
            $result = true;
        } else {
            $error = 'The mail template could not be obtained or not exist.';
        }
    } catch (\Throwable $e) {
        $errorMsg = $e->getMessage();

        if (!empty($mail->ErrorInfo)) {
            $errorMsg .= ' | ' . $mail->ErrorInfo;
        }

        $errorLower = strtolower($errorMsg);

        if (strpos($errorLower, 'invalid_grant') !== false) {
            $error = 'OAUTH_ERROR: Refresh token expired or invalid.';
            $_SESSION['oauth_error'] = 'INVALID_GRANT';
        } elseif (strpos($errorLower, 'authenticate') !== false) {
            $error = 'AUTHENTICATION_ERROR: Invalid credentials or OAuth issue.';
        } elseif (strpos($errorLower, 'invalid address') !== false) {
            $error = 'INVALID_EMAIL: The email address format is invalid.';
        } elseif (strpos($errorLower, 'connect') !== false) {
            $error = 'CONNECTION_ERROR: Unable to connect to SMTP server.';
        } elseif (strpos($errorLower, 'tls') !== false || strpos($errorLower, 'ssl') !== false) {
            $error = 'SSL/TLS_ERROR: Security protocol error.';
        } elseif (strpos($errorLower, 'timeout') !== false) {
            $error = 'TIMEOUT_ERROR: Connection timed out.';
        } else {
            $error = 'SMTP_ERROR: ' . $errorMsg;
        }

        return ['result' => false, 'error' => $error];
    }

    return array('result' => $result, 'error' => $error);
}
