<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/19/18
 * Time: 12:01 PM
 */
require_once __DIR__."/Storage/MyStorage.php";
/**
 * Class Token_model
 * @property CI_DB_query_builder $db
 * @property MyStorage $oauthStorage
 */
class MyToken {
    /**
     * @var \OAuth2\Server
     */
    private $oauthServer;

    private $grantParas=[];



    function __construct ($dbDriver,$tables,$oauth2Paras,$grantParas)
    {
        $this->grantParas = $grantParas;
        $this->oauthStorage = MyStorage::init($dbDriver,$tables);
        $this->oauthServer = new \OAuth2\Server($this->oauthStorage,$oauth2Paras);
    }

    public function password()
    {
        $userCredentials = new OAuth2\GrantType\UserCredentials($this->oauthStorage);
        $this->oauthServer->addGrantType($userCredentials);
        $this->oauthServer->handleTokenRequest(OAuth2\Request::createFromGlobals(),new OAuth2\Response())->send();
    }

    public function refresh()
    {
        $this->oauthServer->addGrantType(new \OAuth2\GrantType\RefreshToken($this->oauthStorage,$this->grantParas));
        $this->oauthServer->handleTokenRequest(OAuth2\Request::createFromGlobals(),new OAuth2\Response())->send();
    }

    /**
     * @return \OAuth2\ResponseType\AccessToken
     */
    public function info()
    {
        //header("Content-type: application/json");
        return $this->oauthServer->getAccessTokenData(OAuth2\Request::createFromGlobals());
        //echo json_encode($this->token);
    }

    function is_auth()
    {
        $req = OAuth2\Request::createFromGlobals();
        $resp = $this->oauthServer->verifyResourceRequest($req);
        return $resp;
    }

    function revoke()
    {
        $req = OAuth2\Request::createFromGlobals();
        $resp = $this->oauthServer->handleRevokeRequest($req);
        print_r($resp);
        return;
    }
}