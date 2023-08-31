<?php
namespace Sawjan\OcisSdk;

require __DIR__ . '/../vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;
use Sawjan\OcisSdk\Lib\Files;


class Ocis {
    public $oidc;
    private $codeVerifier;
    private $request;

    // lib
    public $files;

    function __construct($optons){
        $this->oidc = $this->getOidcClient($optons);
        $this->request = new Request([
            "ocis_url" => $optons["ocis_url"]
        ]);
        $this->files = new Files($this->request);
    }

    private function getOidcClient($optons){
        $oidc = new OpenIDConnectClient(
            $optons['ocis_url'],
            $optons['client_id'],
            $optons['client_secret']
        );

        // insecure for development environment
        // TODO: remove for production
        $oidc->setVerifyHost(false);
        $oidc->setVerifyPeer(false);

        $oidc->setRedirectURL($optons['redirect_uri']);

        // oidc scopes
        $oidc->addScope('profile');
        $oidc->addScope('email');
        $oidc->addScope('offline_access');

        // auth params
        $oidc->addAuthParam(['response_mode' => 'query']);

        // ocis "web" client doesn't work out of the box
        // need to create a new oidc client that works without secret (with PKCE)
        // $this->oidc->setCodeChallengeMethod('S256');
        return $oidc;
    }

    function login(){
        $this->oidc->authenticate();
    }

    function exchangeTokens(){
        $this->oidc->authenticate();
        $tokens["id_token"] = $this->oidc->getIdToken();
        $tokens["access_token"] = $this->oidc->getAccessToken();
        $tokens["refresh_token"] = $this->oidc->getRefreshToken();
        return $tokens;
    }

    function initWithAuth($token){
        $this->request->createClientWithAuth($token);
    }

    function refreshTokens($refreshToken){
        $this->oidc->refreshToken($refreshToken);
        $tokens["id_token"] = $this->oidc->getIdToken();
        $tokens["access_token"] = $this->oidc->getAccessToken();
        $tokens["refresh_token"] = $this->oidc->getRefreshToken();

        $this->initWithAuth($tokens["access_token"]);

        return $tokens;
    }
}
?>