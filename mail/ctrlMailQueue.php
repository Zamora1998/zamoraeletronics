<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/mail/model/modMailQueue.php';

$objMQ = new mailQueue;

$json = [];

switch ($action) {
    case 'R':
        switch ($part) {
            case 'M':
                $objMQ->set_MailId($id);
                $json=$objMQ->selectMail();
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
