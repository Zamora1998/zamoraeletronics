<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/assets/php/libInputValidator.php';
require_once __ROOT__ . '/assets/php/libBitCtrl.php';
require_once __ROOT__ . '/adm/users/model/modUsers.php';
require_once __ROOT__ . '/model/modPassReset.php';
require_once __ROOT__ . '/model/modLocales.php';

$objBitCtrl = new BitControl;
$objUsers = new users($_MYSQLI_);
$objPassReset = new passReset($_MYSQLI_);
$objLoc = new locales($_MYSQLI_);
$json = [];

$objUsers->setLanguageId($chrLang); // Pass the language to the model

switch ($action) {
    case "R":
        switch ($part) {
            case 'A':
                // Include the user model and select all users
                $json = $objUsers->selectAll();
                break;
            case 'U':
                $objUsers->setId($id);
                $json = $objUsers->select();
                break;
            case 'L':
                $json = $objLoc->selectTree();
                break;
            case 'T':
                $json = $objUsers->selectTable();
                break;
        }
        break;
    case 'U':
        // Process access bits received to update user permissions
        $accessbit = 0;
        $objUsers->setId($id);
        if (!in_array('adm', $authParams)) {
            $objUsers->setModUserId($selUser);
            $accessbit = $objUsers->selectStaticAccesses()['data']['access'];
        }
        foreach ($accesses ?? [] as $access) {
            $accessbit = $objBitCtrl->set_bit($accessbit, $access);
        }
        // Validate and sanitize inputs
        $first = InputValidator::sanitizeString($first ?? '');
        $last = InputValidator::sanitizeString($last ?? '');
        $email = InputValidator::sanitizeEmail($email ?? '');

        // Validate required fields
        if (empty($first) || empty($last) || empty($email)) {
            $json = ['result' => false, 'error' => 'First name, last name, and email are required'];
        } else {
            $objUsers->setId($id);
            $objUsers->setFirst($first);
            $objUsers->setLast($last);
            $objUsers->setEmail($email);
            $objUsers->setLocaleId($lang_id ?? 'en_US');
            $objUsers->setEnabled($enabled ?? 0);
            $objUsers->setAccess($accessbit);
            $json = $objUsers->update();

            // If the user was successfully created (no id provided)
            if ($json['result'] && !$id) {
                $objPassReset->setUser($email); // Set the new user's email address
                $user = $objPassReset->selectUser(); // Search for the user by email

                if (!empty($user['data'])) {
                    $result = $objPassReset->insertNewReset(); // Insert new password reset
                    if (!empty($result['key'])) { // Check if a "key" has been generated
                        $objPassReset->setLanguageId($localeId ?? $chrLang);
                        $json['mail'] = $objPassReset->sendEmail(3); // Send the reset email
                    } else {
                        $json['mail'] = 0; // Failed to generate key
                    }
                } else {
                    $json['mail'] = 0; // User not found
                }
            } else {
                $json['mail'] = 0; // User not found
            }
        }
        break;
};

// Configure the response header as JSON and send the response
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
