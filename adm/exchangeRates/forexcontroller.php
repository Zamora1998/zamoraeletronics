<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/adm/exchangeRates/modForExRate.php';

$json = ['result' => true];
$objSettings = new settings($_MYSQLI_);
$settings = $objSettings->selectAll()['data'];

$objForEx = new forExchange();
$dates = $objForEx->selectLastDate()['data'][0] ?? [];
$startDate = $settings['BCCR_StartDate'];
print_r($startDate);
if ($dates['lastDate'] !== null) {
    $startDate = $dates['lastDate'];
}
$endDate = $dates['currDate'];

$page = $settings['BCCR_WebService'];
$name = $settings['BCCR_Name'];
$email = $settings['BCCR_Email'];
$token = $settings['BCCR_Token'];
$indicators = [317, 318];

foreach ($indicators as $indicator) {
    $fields = array(
        'Indicador' => $indicator,
        'FechaInicio' => $startDate,
        'FechaFinal' => $endDate,
        'Nombre' => $name,
        'SubNiveles' => 'N',
        'CorreoElectronico' => $email,
        'Token' => $token
    );
    $query = http_build_query($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $page . '?' . $query);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CURL via PHP');
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    $result = str_replace(['&lt;', '&gt;'], ['<', '>'], $result);
    $xml_res = json_decode(json_encode(new SimpleXMLElement(trim($result))), true);
    if (array_key_exists('Datos_de_INGC011_CAT_INDICADORECONOMIC', $xml_res)) {
        $data = $xml_res['Datos_de_INGC011_CAT_INDICADORECONOMIC']['INGC011_CAT_INDICADORECONOMIC'];
        $objForEx->setForExData($data);
        if ($indicator == 317) {
            $result = $objForEx->insertPurchase();
        } else {
            $result = $objForEx->insertSales();
        }
        $json['result'] = $json['result'] && $result['result'];
        if (!$result['result']) {
            $json['errors'][] = $result['error'];
        }
    } else {
        $json['result'] = false;
        $json['errors'][] = $xml_res;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);