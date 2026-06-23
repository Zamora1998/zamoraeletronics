<?
$ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
define('COUNTRY', (isset($ini_array["general"]["country"]) ? $ini_array["general"]["country"] : null));

$select = false;
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
    'lblCompany',
    'lblCountry',
    'lblDelete',
    'lblEmail',
    'lblFirstname',
    'lblLastname',
    'lblPhone',
    'lblSelect',
    'lblSocialNerwork',
    'lblZip'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);

$objContact = new contacts($_MYSQLI_);
$objContact->setContactId($contactId);
$objContact->setLanguageId($chrLang);
$contact = $objContact->selectContact()['data'];
$header = '';
$body = '';
$footer = '';

if (!empty($contact)) {
    //Name-company section
    $header = <<<EOH
<div class="modal-title card border-0 col-11">
    <div class="card-body p-0">
        <div class="d-flex text-black">
            <div class="flex-shrink-0">
                <img data-name="{$contact['initials']}" data-char-count='2' data-font-size='45' data-seed='2' class="profileContact img-fluid rounded-circle" alt="{$contact['initials']}">
            </div>
            <div class="flex-grow-1 ms-3">
                <input id="ctctfirstname" type="text" value="{$contact['firstname']}" class="form-control form-control-sm mt-1" size="120" placeholder="{$labels['lblFirstname']}">
                <input id="ctctlastname" type="text" value="{$contact['lastname']}" class="form-control form-control-sm mt-1" size="45"  placeholder="{$labels['lblLastname']}">
                <!-- {$contact['title']} -->
                <input id="ctctcompanyname" type="text" value="{$contact['companyname']}" class="form-control form-control-sm mt-1" size="255"  placeholder="{$labels['lblCompany']}">
            </div>
        </div>
    </div>
</div>
<button id="ctcteditclose" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
EOH;

    $body = <<<EOB
<!-- Phone section -->
<ul id="ctctphones" class="list-group mb-10">
EOB;
    foreach ($contact['phones'] as $phone) {
        $isChecked = '';
        if ($phone['default']) {
            $isChecked = ' checked';
        }
        $body .= <<<EOB
    <li class="list-group-item py-1 border-0" data-id="{$phone['id']}">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdelphone_{$phone['id']}" data-id="{$phone['id']}" type="button" class="mt-1 btn btn-contact-del btn-danger btn-sm" title="{$labels['lblDelete']} {$labels['lblPhone']}">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="ctctphonetype_{$phone['id']}" data-id="{$phone['id']}" class="select2 form-control form-control-sm" style="width: 100%;">
EOB;
        foreach ($contact['contacttypes'] as $contacttype) {
            $selected = '';
            if ($contacttype['id'] == $phone['contacttype_id']) {
                $selected = ' selected';
            }
            $body .= <<<EOB
                    <option value="{$contacttype['id']}"{$selected}>{$contacttype['name']}</option>
EOB;
        }
        $phoneNumber = formatPhoneNumber($phone['number']);
        $body .= <<<EOB
                </select>
            </div>
            <div class="col-7 ps-0">
                <input id="ctctphonenumber_{$phone['id']}" data-id="{$phone['id']}" type="text" data-id="{$phone['id']}" value="{$phoneNumber}" class="form-control form-control-sm" size="255" placeholder="{$labels['lblPhone']}">
            </div>
            <div class="col-1 ps-0">
                <input class="mt-2" name="ctctdefphone[]" id="ctctdefphone_{$phone['id']}" data-id="{$phone['id']}" value="{$phone['id']}" type="radio"{$isChecked}>
            </div>
        </div>
    </li>
EOB;
    }

    $body .= <<<EOB
    <li id="ctctaddphonesect" class="list-group-item py-1 border-0" data-default="0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctaddphone" type="button" class="btn btn-contact-add btn-success btn-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="col-11">
                <label class="font-size-12 mb-2">{$labels['lblAdd']} {$labels['lblPhone']}</label>
            </div>
        </div>
    </li>
</ul>

<!-- Email section -->
<ul id="ctctemails" class="list-group mb-10">
EOB;
    foreach ($contact['emails'] as $email) {
        $isChecked = '';
        if ($email['default']) {
            $isChecked = ' checked';
        }
        $body .= <<<EOB
    <li class="list-group-item py-1 border-0" data-id="{$email['id']}">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdelemail_{$email['id']}" data-id="{$email['id']}" type="button" class="mt-1 btn btn-contact-del btn-danger btn-sm" title="{$labels['lblDelete']} {$labels['lblEmail']}">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="ctctemailtype_{$email['id']}" data-id="{$email['id']}" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
        foreach ($contact['contacttypes'] as $contacttype) {
            $selected = '';
            if ($contacttype['id'] == $email['contacttype_id']) {
                $selected = ' selected';
            }
            $body .= <<<EOB
                    <option value="{$contacttype['id']}"{$selected}>{$contacttype['name']}</option>
EOB;
        }
        $body .= <<<EOB
                </select>
            </div>
            <div class="col-7 ps-0">
                <input id="ctctemailaddress_{$email['id']}" type="text" data-id="{$email['id']}" value="{$email['address']}" class="form-control form-control-sm" size="255" placeholder="{$labels['lblEmail']}">
            </div>
            <div class="col-1 ps-0">
                <input class="mt-2" name="ctctdefemail[]" id="ctctdefemail_{$email['id']}" data-id="{$email['id']}" value="{$email['id']}" type="radio"{$isChecked}>
            </div>
        </div>
    </li>
EOB;
    }
    $body .= <<<EOB
    <li id="ctctaddemailsect" class="list-group-item py-1 border-0" data-default="0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctaddemail" type="button" class="btn btn-contact-add btn-success btn-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="col-11">
                <label class="font-size-12 mb-2">{$labels['lblAdd']} {$labels['lblEmail']}</label>
            </div>
        </div>
    </li>
</ul>

<!-- Social network section -->
<ul id="ctctsocials" class="list-group mb-10">
EOB;
    foreach ($contact['socials'] as $social) {
        $body .= <<<EOB
    <li class="list-group-item py-1 border-0" data-id="{$social['id']}">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdelsocial_{$social['id']}" data-id="{$social['id']}" type="button" class="mt-1 btn btn-contact-del btn-danger btn-sm"  title="{$labels['lblDelete']} {$labels['lblSocialNerwork']}">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="socialmediatype_{$social['id']}" data-id="{$social['id']}" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
        foreach ($contact['socialmediatypes'] as $socialmediatype) {
            $selected = '';
            if ($socialmediatype['id'] == $social['socialmediatype_id']) {
                $selected = ' selected';
            }
            $body .= <<<EOB
                    <option value="{$socialmediatype['id']}"{$selected}>{$socialmediatype['name']}</option>
EOB;
        }
        $body .= <<<EOB
                </select>
            </div>
            <div class="col-8 ps-0">
                <input id="ctctsocialname_{$social['id']}" type="text" data-id="{$social['id']}" value="{$social['name']}" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblSocialNerwork']}">
            </div>
        </div>
    </li>
EOB;
    }
    $body .= <<<EOB
    <li id="ctctaddsocialsect" class="list-group-item py-1 border-0" data-default="0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctaddsocial" type="button" class="btn btn-contact-add btn-success btn-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="col-11">
                <label class="font-size-12 mb-2">{$labels['lblAdd']} {$labels['lblSocialNerwork']}</label>
            </div>
        </div>
    </li>
</ul>

<!-- Address section -->
<ul id="ctctaddresses" class="list-group mb-10">
EOB;
    foreach ($contact['addresses'] as $address) {
        $body .= <<<EOB
    <li class="list-group-item py-1 border-0" data-id="{$address['id']}">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdeladdress_{$address['id']}" data-id="{$address['id']}" type="button" class="mt-1 btn btn-contact-del btn-danger btn-sm" title="{$labels['lblDelete']} {$labels['lblAddress']}">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="ctctaddresstype_{$address['id']}" data-id="{$address['id']}" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
        foreach ($contact['contacttypes'] as $contacttype) {
            $selected = '';
            if ($contacttype['id'] == $address['contacttype_id']) {
                $selected = ' selected';
            }
            $body .= <<<EOB
                    <option value="{$contacttype['id']}"{$selected}>{$contacttype['name']}</option>
EOB;
        }
        $body .= <<<EOB
                </select>
            </div>
            <div class="col-8 ps-0">
                <div class="form-row">
                    <div class="col-12 form-group mb-1">
                        <input id="ctctaddressline1_{$address['id']}" type="text" data-id="{$address['id']}" value="{$address['line1']}" class=" form-control-sm" size="255"  placeholder="{$labels['lblAddress']}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-12 form-group mb-1">
                        <input id="ctctaddressline2_{$address['id']}" type="text" data-id="{$address['id']}" value="{$address['line2']}" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblAddress']}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-4 form-group mb-1">
                        <input id="ctctaddresszip_{$address['id']}" type="text" data-id="{$address['id']}" value="{$address['zip']}" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblZip']}">
                    </div>
                    <div class="col-8 form-group mb-1">
                        <input id="ctctaddresscity_{$address['id']}" type="text" data-id="{$address['id']}" value="{$address['city']}" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblCity']}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-12 form-group mb-1">
                        <select id="ctctaddresscountry_{$address['id']}" data-id="{$address['id']}" class="select2 form-select form-select-sm" style="width: 100%;" placeholder="{$labels['lblCountry']}">
EOB;
        foreach ($contact['countries'] as $country) {
            $selected = '';
            if ($country['id'] == $address['country_id']) {
                $selected = ' selected';
            }
            $body .= <<<EOB
                            <option value="{$country['id']}"{$selected}>{$country['name']}</option>
EOB;
        }
        $body .= <<<EOB
                        </select>
                    </div>
                </div>
            </div>
        </dev>
    </li>
EOB;
    }
    $body .= <<<EOB
    <li id="ctctaddaddresssect" class="list-group-item py-1 border-0" data-default="0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctaddaddress" type="button" class="btn btn-contact-add btn-success btn-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="col-11">
                <label class="font-size-12 mb-2">{$labels['lblAdd']} {$labels['lblAddress']}</label>
            </div>
        </div>
    </li>
</ul>

<!--Delete Contact -->
<ul class="list-group mb-0">
    <li class="list-group-item py-0 border-0">
        <div class="row form-group mb-0">
            <div class="col-12 d-grid">
                    <button id="ctctdelcontact" data-id="{$contact['id']}" type="button" class="btn btn-outline-danger">
                        {$labels['lblDelete']}
                    </button>
            </div>
        </div>
    </li>
</ul>

<!-- New phone item -->
<div id="ctctnewphone" class="d-none">
    <li class="list-group-item py-1 border-0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdelphone_0" data-id="0" type="button" class="mt-1 btn btn-contact-del btn-danger btn-sm">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="ctctphonetype_0" data-id="0" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
    foreach ($contact['contacttypes'] as $contacttype) {
        $body .= <<<EOB
                    <option value="{$contacttype['id']}">{$contacttype['name']}</option>
EOB;
    }
    $body .= <<<EOB
                </select>
            </div>
            <div class="col-7 ps-0">
                <input id="ctctphonenumber_0" data-id="0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblPhone']}">
            </div>
            <div class="col-1 ps-0">
                <input class="mt-2" name="ctctdefphone[]" id="ctctdefphone_0" data-id="0" value="0" type="radio">
            </div>
        </div>
    </li>
</div>

<!-- New email item -->
<div id="ctctnewemail" class="d-none">
    <li class="list-group-item py-1 border-0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdelemail_0" data-id="0" type="button" class="mt-2 btn btn-contact-del btn-danger btn-sm" data-id="0">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="ctctemailtype_0" data-id="0" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
    foreach ($contact['contacttypes'] as $contacttype) {
        $body .= <<<EOB
                    <option value="{$contacttype['id']}">{$contacttype['name']}</option>
EOB;
    }
    $body .= <<<EOB
                </select>
            </div>
            <div class="col-7 ps-0">
                <input id="ctctemailaddress_0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblEmail']}">
            </div>
            <div class="col-1 ps-0">
                <input class="mt-2" name="ctctdefemail[]" id="ctctdefemail_0" data-id="0" value="0" type="radio">
            </div>
        </div>
    </li>
</div>

<!-- New social network item -->
<div id="ctctnewsocial" class="d-none">
    <li class="list-group-item py-1 border-0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdelsocial_0" data-id="0" type="button" class="mt-2 btn btn-contact-del btn-danger btn-sm">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="socialmediatype_0" data-id="0" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
    foreach ($contact['socialmediatypes'] as $socialmediatype) {
        $body .= <<<EOB
                    <option value="{$socialmediatype['id']}">{$socialmediatype['name']}</option>
EOB;
    }
    $body .= <<<EOB
                </select>
            </div>
            <div class="col-8 ps-0">
                <input id="ctctsocialname_0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblSocialNerwork']}">
            </div>
        </div>
    </li>
</div>

<!-- New address item -->
<div id="ctctnewaddress" class="d-none">
    <li class="list-group-item py-1 border-0" data-id="0">
        <div class="row form-group mb-0">
            <div class="col-1">
                <button id="ctctdeladdress_0" data-id="0" type="button" class="mt-2 btn btn-contact-del btn-danger btn-sm">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="col-3">
                <select id="ctctaddresstype_0" data-id="0" class="select2 form-select form-select-sm" style="width: 100%;">
EOB;
    foreach ($contact['contacttypes'] as $contacttype) {
        $body .= <<<EOB
                    <option value="{$contacttype['id']}">{$contacttype['name']}</option>
EOB;
    }
    $body .= <<<EOB
                </select>
            </div>
            <div class="col-8 ps-0">
                <div class="form-row">
                    <div class="col-12 form-group mb-1">
                        <input id="ctctaddressline1_0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblAddress']}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-12 form-group mb-1">
                        <input id="ctctaddressline2_0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblAddress']}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-4 form-group mb-1">
                        <input id="ctctaddresszip_0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblZip']}">
                    </div>
                    <div class="col-8 form-group mb-1">
                        <input id="ctctaddresscity_0" type="text" data-id="0" value="" class="form-control form-control-sm" size="255"  placeholder="{$labels['lblCity']}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-12 form-group mb-1">
                        <select id="ctctaddresscountry_0" data-id="0" class="select2 form-select form-select-sm" style="width: 100%;" placeholder="{$labels['lblCountry']}">
EOB;
    foreach ($contact['countries'] as $country) {
        $body .= <<<EOB
                            <option value="{$country['id']}">{$country['name']}</option>
EOB;
    }
    $body .= <<<EOB
                        </select>
                    </div>
                </div>
            </div>
        </dev>
    </li>
</div>
EOB;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson(array('contactId' => $contact['id'], 'header' => $header, 'body' => $body, 'footer' => $footer));

function formatPhoneNumber($numberString) {
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
        $NumberProto = $phoneUtil->parse($numberString, COUNTRY);
        //code...
    } catch (\Throwable $th) {

        return $numberString;
    }

    return $phoneUtil->format($NumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
}
