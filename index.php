<?php
require 'fileWork.php';

$PPL = file_get_contents('http://localhost/ppl.txt');
$items = explode(';', $PPL);
$clientID = $items[0];
$clientSecret = base64_decode($items[1]);
$proxy = $items[2];

$tokenUrl = "https://api-dev.dhl.com/ecs/ppl/myapi2/login/getAccessToken";
$grantType = "client_credentials";
$scope = "myapi2";


$data = array(
    'grant_type' => $grantType,
    'client_id' => $clientID,
    'client_secret' => $clientSecret,
    'scope' => $scope
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'proxy' => $proxy,
        'request_fulluri' => true, 
    ),
);

$context = stream_context_create($options);
$response = file_get_contents($tokenUrl, false, $context);

if ($response === false) {
    die("Chyba při získávání tokenu.");
}

$tokenData = json_decode($response, true);

if (isset($tokenData['access_token'])) {
    $accessToken = $tokenData['access_token'];
} else {
    die("Chyba při získávání tokenu.");
}

$data = array(
    'ShipmentNumbers' => ''
);

$options = array(
    'http' => array(
        'header' => "Content-type: application/json\r\n" .
                    "Authorization: Bearer $accessToken\r\n".
                    "content-length: 500 \r\n", 
        'method' => 'GET',
        'content' => json_encode($data),
        'proxy' => $proxy,
        'request_fulluri' => true
    )
);
$apiUrl ="https://api.dhl.com/ecs/ppl/myapi2/shipment/";
$File = new KN_file_get_contents($apiUrl,$accessToken);
$response =$File->json(); 

if ($response === false) {

    die("Chyba při volání služby.");
}

?>
