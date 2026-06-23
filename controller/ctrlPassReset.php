<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/assets/php/libInputValidator.php';
require_once __ROOT__ . '/model/modPassReset.php';

$json = [];

// Validate and sanitize inputs
$key = InputValidator::sanitizeString($key ?? '');
$password = InputValidator::sanitizeString($password ?? '');

// Validate required fields
if (empty($key) || empty($password)) {
    $json = ['result' => false, 'error' => 'Invalid request parameters'];
} else {
    $objPassReset = new passReset($_MYSQLI_);
    $objPassReset->setKey($key);
    $objPassReset->setPassword($password);
    $json = $objPassReset->updatePass();
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
