<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/assets/php/libInputValidator.php';
require_once __ROOT__ . '/usr/profile/modProfile.php';

$objProfile = new profile($_MYSQLI_);
$objLocj = new locales($_MYSQLI_);
$json = [];

switch ($action) {
    case 'R':
        switch ($part) {
            case 'P':
                $objProfile->setId($selUser);
                $json = $objProfile->select();
                break;
            case 'L':
                $json = $objLoc->selectTree();
                break;
        }
        break;
    case 'U':
        // Validate and sanitize inputs
        $first = InputValidator::sanitizeString($first ?? '');
        $last = InputValidator::sanitizeString($last ?? '');
        $email = InputValidator::sanitizeEmail($email ?? '');
        $localeId = InputValidator::sanitizeString($localeId ?? '');
        
        // Validate required fields
        if (empty($first) || empty($last) || empty($email)) {
            $json = ['result' => false, 'error' => 'First name, last name, and email are required'];
        } else {
            $objProfile->setId($selUser);
            $objProfile->setFirst($first);
            $objProfile->setLast($last);
            $objProfile->setEmail($email);
            $objProfile->setLocaleId($localeId);
            $json = $objProfile->update();
        }
        break;
    case 'UP':
        // Password update
        $objProfile->setId($selUser);
        // Verify if the password is present in the request
        if (isset($newPassword) && !empty($newPassword)) {
            $newPassword = InputValidator::sanitizeString($newPassword);
            $currentPassword = InputValidator::sanitizeString($currentPassword ?? '');
            
            if (empty($currentPassword)) {
                $json = ['result' => false, 'error' => 'Current password is required'];
            } else {
                $objProfile->setPass($newPassword);
                $objProfile->setCurrentPass($currentPassword);
                $json = $objProfile->updatePass();
            }
        } else {
            $json = ['result' => false, 'error' => 'New password is required'];
        }
        break;
    case 'T':  // Nueva acción para cambiar el tema
        $objProfile->setId($selUser);
        $dark = InputValidator::validateInt($_POST['dark'] ?? 0, 0, 1);
        $objProfile->setDark($dark ?? 0);
        $json = $objProfile->updateDarkMode();
        break;
}

// Configure the response header as JSON and send the response
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
