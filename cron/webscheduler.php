<?php

require_once __DIR__ . '/../autoconf.php';
include_once __ROOT__ . '/vendor/autoload.php';
require_once __ROOT__ . '/adm/cronjobs/modCronjobs.php';

$objCron = new cronjobs($_MYSQLI_);
$jobs = $objCron->select()['data'];

foreach ($jobs as $job) {

    $url = 'https://edminew.discoveryadventurecr.com/' . ltrim($job['script'], '/');

    echo "Ejecutando: $url\n";

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Error cURL: " . curl_error($ch) . "\n";
    } else {
        echo "Respuesta:\n$response\n";
    }

}

echo "done\n";
