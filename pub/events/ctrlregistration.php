<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/pub/events/modregistration.php';

$json = [];

$objRegistration = new modRegistration($_MYSQLI_);

$objRegistration->setUUID($uuid);
$objRegistration->setCedula($idcard);
$objRegistration->setAge($age);
$objRegistration->setBeneficiaryId($beneficiaryId);
$objRegistration->setBeneficiaryName($beneficiaryName);
$objRegistration->setEmail($email);
$objRegistration->setFirstName($firstName);
$objRegistration->setGender($gender);
$objRegistration->setIdcard($idcard);
$objRegistration->setLastName($lastName);
$objRegistration->setPhone($phone);
$objRegistration->setSecondLastName($secondLastName);
$objRegistration->setShirtSize($shirtSize);


$return = $objRegistration->createEntryCUC();

// Si necesitas manejar el error de manera específica, puedes hacerlo aquí
// Si el método createEntryCUC() devuelve un error, puedes asignar el error al objeto json
if (!$return['result']) {
    $json['error'] = $return['error'];
    $json['result'] = false;
} else {
    // Puedes manejar el caso exitoso aquí si es necesario
    $json['result'] = true;
}



header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json);
