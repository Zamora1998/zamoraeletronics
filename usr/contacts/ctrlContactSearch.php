<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/contacts/modContacts.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'lblCancel',
    'lblCompany',
    'lblEmail',
    'lblName',
    'lblPhone',
    'lblSelect',
    'lblSearch'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);

$objContact = new contacts($_MYSQLI_);
$objContact->setLanguageId($chrLang);
$contacts = $objContact->selectContacts()['data'];
//print_r($contacts);
$header = '';
$body = '';
$footer = '';

$header = <<<EOH
<div class="modal-title row mt-20" style="width: 91%;">
    <div class="col-11 pe-0">
        <div class="form-group mb-0">
            <div class="input-group">
                <!--<i class="input-group-icon fal fa-search" aria-hidden="true"></i>-->
                <span class="input-group-text" id="basic-addon1">
                <i class="fal fa-search"></i>
                </span>
                <input id="ctctsearch" type="text" class="form-control" name="" placeholder="{$labels['lblSearch']} {$labels['lblName']}, {$labels['lblCompany']}, {$labels['lblPhone']}, {$labels['lblEmail']}">
                <!--<button type="button" class="input-search-close icon wb-close" aria-label="Close"></button>-->
                <span id="ctctsearchclear" class="input-group-text ps-1">
                    <i class="btn-close"></i>
                </span>
            </div>
        </div>
    </div>
EOH;
if ($addNew) {
    $header .= <<<EOH
    <div class="col-1 ps-1">
        <button id="ctctcontactadd" type="button" class="btn btn-outline-secondary btn-icon btn-round">
            <i class="fas fa-user-plus"></i>
        </button>
    </div>
EOH;
}
$header .= <<<EOH
</div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
<!--<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
</button>-->
EOH;

$body = <<<EOB
<ul class="list-group ui-selectable" id="ctctList">
EOB;
foreach ($contacts as $contact) {
    $name = ($contact['firstname'] || $contact['lastname']) ? $contact['firstname'] . " " . $contact['lastname'] :  $contact['company'];
    $company = ($contact['firstname'] || $contact['lastname']) ? $contact['company'] : '&nbsp;';
    $search = strtolower("{$contact['firstname']}|{$contact['lastname']}|{$contact['company']}|{$contact['phonenumbers']}|{$contact['emails']}");
    $body .= <<<EOB
    <li id="ctctlistitem_{$contact['id']}" class="list-group-item py-1" data-id="{$contact['id']}"  data-search="{$search}">
        <div class="d-flex">
            <div class="flex-shrink-0">
EOB;
    if ($contact['initials']) {
        $body .= <<<EOB
                <a class="avatar" href="javascript:void(0)">
                    <img data-name="{$contact['initials']}" data-char-count='2' data-font-size='45' data-seed='2' class="profileContact img-fluid rounded-circle" alt="{$contact['initials']}">
                </a>
EOB;
    } else {
        $body .= <<<EOB
                <div style="width:40px;height:40px" alt="Avatar" class="rounded-circle d-flex justify-content-center align-items-center bg-info">
                    <i class="fal fa-building font-size-16"></i>
                </div>
EOB;
    }
    $body .= <<<EOB
            <!--<img class="img-fluid" src="../../../global/portraits/1.jpg" alt="{$contact['initials']}" data-mediaId="{$contact['mediaId']}">-->
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="mb-0">{$name}</h6>
                <small>{$company}</small>
            </div>
        </div>
    </li>
EOB;
}
$body .= <<<EOB
</ul>
EOB;
if ($multiple) {
    $footer = <<<EOF
    <button id="ctctSelectSearch" class="btn btn-sm btn-success">{$labels['lblSelect']}</button>
    <button id="ctctCancel" class="btn btn-sm btn-danger" data-dismiss="modal">{$labels['lblCancel']}</button>
EOF;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson(array('contactId' => $contactId, 'header' => $header, 'body' => $body, 'footer' => $footer));

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
