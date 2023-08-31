<?php
namespace Sawjan\TestClient;
require __DIR__ . '/vendor/autoload.php';

session_start();

use Sawjan\OcisSdk\Ocis;


function getOcisSdk(){
    $options = [
        "ocis_url" => "https://localhost:9200",
        // ocis "web" client doesn't work out of the box
        // need to create a new oidc client that works without secret (with PKCE)
        // Using Desktop client id for now
        "client_id" => "sdk",
        "client_secret" => "UBntmLjC2yYCeHwsyj73Uwo9TAaecAetRwMw0xYcvNL9yRdLSUi0hUAHfvCHFeFh",
        "redirect_uri" => "http://localhost:9000/silent_oidc_callback"
    ];

    return new Ocis($options);
}

// Handler
// GET '/'
function connect() {
    $ocis = getOcisSdk();
    $ocis->login();
}

// Handler
// GET '/silent_oidc_callback'
function exchangeTokens(){
    // different instance of OcisSdk
    $ocis = getOcisSdk();
    $tokens = $ocis->exchangeTokens();
    // TODO: store tokens
    $_SESSION['tokens'] = $tokens;
    header('Location: http://localhost:9000/post_connection');
}

// Handler
// GET '/post_connection'
function postConnection(){
    $tokens = $_SESSION['tokens'];
    $idToken = $tokens['id_token'];
    $accessToken = $tokens['access_token'];
    $refreshToken = $tokens['refresh_token'];

    // different instance of OcisSdk
    $ocis = getOcisSdk();
    $ocis->initWithAuth($accessToken);

    $res = $ocis->files->list("/");

    $html = "<h1>Connected to oCIS</h1>";

    if ($res['statuscode'] >= 400) {
        $html .= "<h3>Cannot list files: $res[statuscode]</h3>";
        echo $html;

        if ($res['statuscode'] === 401) {
            echo "Session expired. Refreshing...";
            sleep(2);
            refreshTokenInterval($ocis, $refreshToken);
            postConnection();
        }
        return;
    }

    $html .= "<h3>Files:</h3>";
    $data = $res['body']->xpath('//d:multistatus/d:response/d:href');
    $rootPath = (string)$data[0];
    foreach ($data as $item) {
        $name = str_replace($rootPath, "", (string)$item);
        $name = trim($name, "/");
        if ($name !== "" && $name !== "Shares") {
            $html .= "<p>$name</p>";
        }
    }
    echo $html;
}

function refreshTokenInterval($ocis, $refreshToken){
    $tokens = $ocis->refreshTokens($refreshToken);
    $_SESSION['tokens'] = $tokens;
}

$urlPath = $_SERVER['REQUEST_URI'];

// router
switch (true) {
    case preg_match('/^\/$/',$urlPath):
        connect();
        break;
    case preg_match('/^\/silent_oidc_callback\?(.*)/', $urlPath):
        exchangeTokens();
        break;
    case preg_match('/^\/post_connection$/', $urlPath):
        postConnection();
        break;
    default:
        echo "<h1>404 Not Found</h1>";
        break;
}
?>