<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/assets/php/libInputValidator.php';
require_once __ROOT__ . '/assets/php/captcha/modCaptcha.php';
require_once __ROOT__ . '/model/modPassReset.php';

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
$hasCaptcha = $ini_array['general']['captcha'] ?? true;

$json = array('captcha' => false, 'mail' => 0, 'user' => false);

// Sanitize inputs
$username = InputValidator::sanitizeEmail($_POST['username'] ?? '');

if ($hasCaptcha) {
    $objCaptcha = new Captcha;
    $isValidCaptcha = $objCaptcha->validateCaptcha('captcha_code', $captcha ?? '');
} else {
    $isValidCaptcha = true;
}

if ($isValidCaptcha) {
    $json['captcha'] = true;

    // Validate email format
    if (empty($username)) {
        $json['error'] = 'Valid email is required';
    } else {
        $objPass = new passReset($_MYSQLI_);
        $objPass->setUser($username);
        $user = $objPass->selectUser();
        if ($user['result'] && !empty($user['data'])) {
            $json['user'] = true;
            $objPass->insertNewReset();
            $objPass->setLanguageId($user['data']['language_id'] ?? $chrLang);
            $json['mail'] = $objPass->sendEmail(1);
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
