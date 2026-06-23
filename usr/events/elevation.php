<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['locations'])) {
    echo json_encode(["error" => "Missing 'locations' parameter"]);
    exit;
}

$locations = urlencode($_GET['locations']);
$apiUrl = "https://api.opentopodata.org/v1/srtm90m?locations=$locations";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

echo $response;
