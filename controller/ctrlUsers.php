<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/assets/php/libInputValidator.php';
require_once __ROOT__ . '/model/modUsers.php';
require_once __ROOT__ . '/assets/php/captcha/modCaptcha.php';

$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
$hasCaptcha = $ini_array['general']['captcha'] ?? true;
$hasSignup = $ini_array['general']['signup'] ?? true;

$json = [];
$objUsers = new users($_MYSQLI_);
$objUsers->setLocaleId($chrLocale);

if ($hasSignup) {
    $json['enabled'] = true;
    switch ($action) {
        case 'C':
            // Validate and sanitize inputs
            $sanitized = InputValidator::sanitizeInputs([
                'firstname' => 'name',
                'lastname' => 'name',
                'username' => 'email',
                'password' => 'string'
            ]);
            
            $firstname = $sanitized['firstname'];
            $lastname = $sanitized['lastname'];
            $username = $sanitized['username'];
            $password = $sanitized['password'];
            $passwordc = InputValidator::sanitizeString($_POST['passwordc'] ?? '');
            $captcha = InputValidator::sanitizeString($_POST['captcha'] ?? '');
            
            // Validate required fields
            $validationErrors = [];
            if (empty($firstname)) {
                $validationErrors[] = 'First name is required';
            }
            if (empty($lastname)) {
                $validationErrors[] = 'Last name is required';
            }
            if (empty($username)) {
                $validationErrors[] = 'Valid email is required';
            }
            if (empty($password)) {
                $validationErrors[] = 'Password is required';
            }
            if ($password !== $passwordc) {
                $validationErrors[] = 'Passwords do not match';
            }
            
            // Check password strength (minimum 8 characters)
            if (!empty($password) && strlen($password) < 8) {
                $validationErrors[] = 'Password must be at least 8 characters';
            }
            
            if (!empty($validationErrors)) {
                $json = modGeneralFunction::toJson(['validation' => false, 'errors' => $validationErrors]);
                break;
            }
            
            if ($hasCaptcha) {
                $objCaptcha = new Captcha();
                $isValidCaptcha = $objCaptcha->validateCaptcha('captcha_code', $captcha);
            } else {
                $isValidCaptcha = true;
            }
            if ($isValidCaptcha) {
                $objUsers->setFirstname($firstname);
                $objUsers->setLastname($lastname);
                $objUsers->setEmail($username);
                $objUsers->setPass($password);
                $result = $objUsers->insert();
                $result['validation'] = true;
                $result['captcha'] = true;
                $json = modGeneralFunction::toJson($result, null);
            } else {
                $json = modGeneralFunction::toJson(['validation' => true, 'captcha' => false], null);
            }
            break;
    }
} else {
    $json['enabled'] = false;
}

header('Content-Type: application/json; charset=utf-8');
echo $json;
