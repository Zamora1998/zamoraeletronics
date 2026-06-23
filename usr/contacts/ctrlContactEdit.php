<?
$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
define('COUNTRY', (isset($ini_array["general"]["country"]) ? $ini_array["general"]["country"] : null));

$json = '';
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/contacts/modContacts.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'lblAdd',
    'lblAddress',
    'lblCancel',
    'lblCity',
    'lblCountry',
    'lblEmail',
    'lblPhone',
    'lblSelect',
    'lblSocialNerwork',
    'lblZip'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);

$objContact = new contacts($_MYSQLI_);
$objContact->setContactId($contactId);

switch ($action) {
    case 'U':
        switch ($part) {
            case 'P': //Person
                $objContact->setFirstname($firstname);
                $objContact->setLastname($lastname);
                $json = modGeneralFunction::toJson($objContact->updatePerson());
                break;
            case 'C': //Company
                $objContact->setCompanyname($companyname);
                $json = modGeneralFunction::toJson($objContact->updateCompany());
                break;
            case 'T': //Phone
                $phone = parsePhoneNumber($number);
                $objContact->setPhoneId($phoneId);
                $objContact->setContactTypeId($contacttypeId);
                $objContact->setDefault($def);
                $objContact->setCountryCode((string)$phone['countryCode']);
                $objContact->setPhoneNumber($phone['nationalNumber']);
                $objContact->setPhoneExtension($phone['extension']);
                $result = $objContact->updatePhone();
                $result['formatedPhoneNumber'] = formatPhoneNumber($number);
                $json = modGeneralFunction::toJson($result);
                break;
            case 'E': //Email
                $objContact->setEmailId($emailId);
                $objContact->setContactTypeId($contacttypeId);
                $objContact->setEmailAddress($email);
                $objContact->setDefault($def);
                $json = modGeneralFunction::toJson($objContact->updateEmail());
                break;
            case 'S': //Social
                $objContact->setSocialId($socialId);
                $objContact->setSocialName($socialName);
                $objContact->setSocialTypeId($socialtypeId);
                $json = modGeneralFunction::toJson($objContact->updateSocial());
                break;
            case 'A': //Address
                $objContact->setEmailId($addressId);
                $objContact->setContactTypeId($contacttypeId);
                $objContact->setAddressId($addressId);
                $objContact->setAddressLine1($addressLine1);
                $objContact->setAddressLine2($addressLine2);
                $objContact->setAddressZip($addressZip);
                $objContact->setAddressCity($addressCity);
                $objContact->setAddressCountry($addressCountry);
                $json = modGeneralFunction::toJson($objContact->updateAddress());
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'T': //Phone
                $objContact->setPhoneId($phoneId);
                $json = modGeneralFunction::toJson($objContact->deletePhone());
                break;
            case 'E': //Email
                $objContact->setEmailId($emailId);
                $json = modGeneralFunction::toJson($objContact->deleteEmail());
                break;
            case 'S': //Social
                $objContact->setSocialId($socialId);
                $json = modGeneralFunction::toJson($objContact->deleteSocial());
                break;
            case 'A': //Address
                $objContact->setAddressId($addressId);
                $json = modGeneralFunction::toJson($objContact->deleteAddress());
                break;
            case 'C': //Full contact
                $json = modGeneralFunction::toJson($objContact->deleteContact());
                break;
            default:
                break;
        }
        break;
}
header('Content-Type: application/json; charset=utf-8');
echo $json;

function parsePhoneNumber($numberString) {
    $return = array();
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
        $numberProto = $phoneUtil->parse($numberString, COUNTRY);
    } catch (\Throwable $th) {
        $return['countryCode'] = '';
        $return['nationalNumber'] = $numberString;
        $return['extension'] = '';

        return $return;
    }

    $return['countryCode'] = $numberProto->getCountryCode();
    $return['nationalNumber'] = $numberProto->getNationalNumber();
    $return['extension'] = $numberProto->getExtension();

    return $return;
}

function formatPhoneNumber($numberString) {
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
        $numberProto = $phoneUtil->parse($numberString, COUNTRY);
    } catch (\Throwable $th) {

        return $numberString;
    }

    return $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
}
