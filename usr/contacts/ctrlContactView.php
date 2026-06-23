<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/contacts/modContacts.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'lblCancel',
    'lblSelect'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);

$objContact = new contacts($_MYSQLI_);
$objContact->setContactId($contactId);
$objContact->setLanguageId($chrLang);
$contact = $objContact->selectContact()['data'];

$header = '';
$body = '';
$footer = '';

if ($contact['id']) {
    $header = <<<EOH
<div class="modal-title card border-0">
    <div class="card-body p-0">
        <div class="d-flex text-black">
            <div class="flex-shrink-0">
                <img data-name="{$contact['initials']}" data-char-count='2' data-font-size='45' data-seed='2' class="profileContact img-fluid rounded-circle" alt="{$contact['initials']}">
            </div>
            <div class="flex-grow-1 ms-3 pt-4">
                <h5 class="mb-1">{$contact['title']} {$contact['firstname']} {$contact['lastname']}</h5>
                <p class="mb-2 pb-1" style="color: #2b2a2a;">
                    {$contact['companyname']}
                </p>
            </div>
        </div>
    </div>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
EOH;

    $body = "
<ul class=\"list-group\">";

    foreach ($contact['phones'] as $phone) {
        $body .= "
    <li class=\"list-group-item py-1\" data-default=\"{$phone['default']}\" data-id=\"{$phone['id']}\">
        <div class=\"font-size-12\"><i class=\"fal fa-phone pe-5\"></i>{$phone['type']}</div>
        <div class=\"font-size-14 blue-grey-600\">
            " . formatPhoneNumber($phone['number']);
        if ($select) {
            $body .= "
            <input type=\"checkbox\" id=\"phone_{$phone['id']}\" class=\"float-right\" data-id=\"{$phone['id']}\">";
        }
        $body .= "
        </div>
    </li>";
    }

    if (count($contact['phones'])) {
        $body .= "
    <li class=\"list-group-item py-1\"></li>";
    }

    foreach ($contact['emails'] as $email) {
        $body .= "
    <li class=\"list-group-item py-1\" data-default=\"{$email['default']}\" data-id=\"{$email['id']}\">
        <div class=\"font-size-12\"><i class=\"fal fa-envelope pe-5\"></i>{$email['type']}</div>
        <div class=\"font-size-14 blue-grey-600\">
            {$email['address']}";
        if ($select) {
            $body .= "
            <input type=\"checkbox\" id=\"email_{$email['id']}\" class=\"float-right\" data-id=\"{$email['id']}\">";
        }
        $body .= "
        </div>
    </li>";
    }
    if (count($contact['emails'])) {
        $body .= "
    <li class=\"list-group-item py-1\"></li>";
    }

    foreach ($contact['socials'] as $social) {
        $body .= "
    <li class=\"list-group-item py-1\" data-id=\"{$social['id']}\">
        <div class=\"font-size-12\"><i class=\"{$social['icon']} pe-5\"></i>{$social['type']}</div>
        <div class=\"font-size-14 blue-grey-600\">{$social['name']}</div>
    </li>";
    }
    if (count($contact['socials'])) {
        $body .= "
        <li class=\"list-group-item py-1\"></li>";
    }

    foreach ($contact['addresses'] as $address) {
        $body .= "
    <li class=\"list-group-item py-1\" data-id=\"{$address['id']}\">
        <div class=\"font-size-12\"><i class=\"fal fa-map-marker-alt pe-5\"></i>{$address['type']}</div>
        <div class=\"font-size-14 blue-grey-600\">{$address['line1']}</div>
        <div class=\"font-size-14 blue-grey-600\">{$address['line2']}</div>
        <div class=\"font-size-14 blue-grey-600\">{$address['zip']} {$address['city']}, {$address['country']}.</div>
    </li>";
    }



    $body .= "
</ul>
";
    if ($select) {
        $footer = "
        <button id=\"ctctSelect\" class=\"btn btn-sm btn-success\">{$labels['lblSelect']}</button>
        <button id=\"ctctCancel\" class=\"btn btn-sm btn-danger\" data-dismiss=\"modal\">{$labels['lblCancel']}</button></div>";
    }
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson(array('contactId' => $contact['id'], 'header' => $header, 'body' => $body, 'footer' => $footer));

function formatPhoneNumber($numberString) {
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
        $NumberProto = $phoneUtil->parse($numberString);
        //code...
    } catch (\Throwable $th) {

        return $numberString;
    }

    return $phoneUtil->format($NumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
}
