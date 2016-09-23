<?php
if (isset($_GET["error"]))
{
    echo("<pre>OAuth Error: " . $_GET["error"]."\n");
    echo('<a href="index.php">Retry</a></pre>');
    die;
    
    //?state=SomeUnguessableValue&code=ki4tr-_EAXNXVrhWMcQ5a5pLm0o
}

$authorizeUrl = 'https://ssl.reddit.com/api/v1/authorize';
$accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
$clientId = 'jJgLD5ebMOT9sw';
$clientSecret = 'muldwiysWI2ok2KWNmoiDK6FMKw';
$userAgent = 'ChangeMeClient/0.1 by YourUsername';

$redirectUrl = "http://brittanyannkos.com/reddit";

require("OAuth2/Client.php");
require("OAuth2/GrantType/IGrantType.php");
require("OAuth2/GrantType/AuthorizationCode.php");

$client = new OAuth2\Client($clientId, $clientSecret, OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
$client->setCurlOption(CURLOPT_USERAGENT,$userAgent);

$_GET["code"] = "ki4tr-_EAXNXVrhWMcQ5a5pLm0o";

if (!isset($_GET["code"]))
{
    $authUrl = $client->getAuthenticationUrl($authorizeUrl, $redirectUrl, array(
    		"scope" => "identity", 
    		"state" => "SomeUnguessableValue",
    		"duration" => "permanent")
    );
    header("Location: ".$authUrl);
    die("Redirect");
}
else
{
    $params = array("code" => $_GET["code"], "redirect_uri" => $redirectUrl);
    $response = $client->getAccessToken($accessTokenUrl, "authorization_code", $params);

    $accessTokenResult = $response["result"];
    print_r($accessTokenResult);
    $client->setAccessToken($accessTokenResult["access_token"]);
    $client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);

    $response = $client->fetch("https://oauth.reddit.com/api/v1/me.json");

    echo('<strong>Response for fetch me.json:</strong><pre>');
    print_r($response);
    echo('</pre>');
}
?>