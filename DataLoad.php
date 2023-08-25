<script
src="https://code.jquery.com/jquery-3.6.4.js"
integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
crossorigin="anonymous">
</script>
<?php


set_time_limit(900);
$startTime = microtime(true);
$txt = file_get_contents('http://localhost/proxy.txt');
$items = explode(';', $txt);
$parameters = [
    'proxy_host'     => $items[0],
    'proxy_port'     => $items[1],
     'stream_context' => stream_context_create(
        array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            )
        )
    )
];
$RowHunt = 0;
$CloseParcel = 0;
$RowInsert=0;
 
    //connection setting
    $PPL = file_get_contents('http://localhost/ppl.txt');
    $items = explode(';', $PPL);
    $clientID = $items[0];
    $clientSecret = base64_decode($items[1]);
    $proxy = $items[2];
    
    $tokenUrl = "https://myapi.ppl.cz/v1/IMyApi2/Login";
    $grantType = "client_credentials";
    $scope = "myapi2";
    
    $loginParams = array(
        'UserName' => $clientID,
        'Password' => $clientSecret,
    );
    

    $client = new SoapClient("https://myapi.ppl.cz/MyApi.svc?singleWsdl",$parameters);
try 
{      
    $pw = file_get_contents('http://localhost/packeta.txt');

    $response = $client->Login($loginParams);

    $loginResult = $response->LoginResult;

    $token = $loginResult->AuthToken;

    echo "Auth Token: " . $token . "\n";
  
}
catch (PDOException $exception) 
{
    echo "Db connect error: " . $e->getMessage() . "\n";
} 
catch (Exception $e) 
{
    echo "Error: " . $e->getMessage() . "\n";
}

?>