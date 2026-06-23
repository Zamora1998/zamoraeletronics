<?
/*
uuid: data.uuid,
eventId: data.event_id,
postId: data.post_id,
deliverableId,
decodedText,
decodedResult
*/
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/pub/posts/modDeliveries.php';
require_once __ROOT__ . '/model/modLabels.php';
require_once __ROOT__ . '/pub/posts/modPosts.php';
$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'ntyAproved',
    'ntyRejected',
    'ntyQRNotFound'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);
$json = [];
$decodedText = str_replace('\n',  chr(10), $decodedText);
$objDeli = new deliveries($_MYSQLI_);

$objDeli->setEventId($eventId);
$objDeli->setPostId($postId);
$objDeli->setUUID($uuid);
$objDeli->setvCard($decodedText);
$objDeli->setDeliverableId($deliverableId);
$json = $objDeli->selectContact();
if (count($json['data'])) {
    $return = $objDeli->insert();
} else {
    $json['error'] = $labels['ntyQRNotFound'];
    $json['result'] = false;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json);
