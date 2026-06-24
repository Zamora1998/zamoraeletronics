<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/model/modAuth.php';
require_once __ROOT__ . '/assets/php/captcha/modCaptcha.php';

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
$hasCaptcha = $ini_array['general']['captcha'] ?? true;

$json = array('auth' => false, 'captcha' => false, 'reroute' => '', 'reqCaptcha' => true);

if ($hasCaptcha && isset($_SESSION['loginFail']) && $_SESSION['loginFail']) {
    $objCaptcha = new Captcha;
    $isValidCaptcha = $objCaptcha->validateCaptcha('captcha_code', $captcha);
} else {
    $isValidCaptcha = true;
}
if ($isValidCaptcha) {
    $json['captcha'] = true;
    $objAuth = new auth($_MYSQLI_);
    $remember = isset($remember) && $remember == '1';
    $data = $objAuth->authenticate($email, $password, $remember);
    $json['auth'] = $data['auth'];
    $json['result'] = $data['result'];
    $json['errors'] = $data['errors'];
    if ($data['auth']) {
        // IMPORTANTE: Limpiar sesiones anteriores del usuario para evitar conflictos
        // Esto invalida cualquier sesión activa anterior del mismo usuario
        $objAuth->cleanupPreviousSessions((int)$data['data']['id']);
        
        $_SESSION['id'] = $data['data']['id'];
        $_SESSION['locale_id'] = $data['data']['locale_id'];
        $_SESSION['first'] = $data['data']['first'];
        $_SESSION['last'] = $data['data']['last'];
        $_SESSION['email'] = $data['data']['email'];
        $_SESSION['token'] = $data['token'];
        $_SESSION['access'] = $data['data']['access'];
        
        // Check for intended URL stored in cookie (from redirect after login attempt)
        $intendedUrl = $_COOKIE['intended_url'] ?? '';
        if (!empty($intendedUrl)) {
            // Clear the cookie
            setcookie('intended_url', '', time() - 3600, '/', '', false, false);
            $json['reroute'] = $intendedUrl;
        } else {
            $json['reroute'] = "/" . str_replace('_', '-', strtolower($_SESSION['locale_id'])) . "/main";
        }
        $_SESSION['loginFail'] = false;
    } else {
        $_SESSION['loginFail'] = true;
        $json['reqCaptcha'] = true;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json);
