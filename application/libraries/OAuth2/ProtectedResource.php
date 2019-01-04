<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/19/18
 * Time: 3:35 PM
 */
require_once __DIR__."/Storage/MyStorage.php";

class ProtectedResource
{
    /**
     * @var MyStorage
     */
    private $oauthStorage;

    /**
     * @var \OAuth2\Server
     */
    private $oauthServer;


    private function __construct ($dbDriver,$tables,$oauth2Paras)
    {
        $this->oauthStorage = MyStorage::init($dbDriver,$tables);
        $this->oauthServer = new \OAuth2\Server($this->oauthStorage,$oauth2Paras);
    }

    function is_auth()
    {
        $req = OAuth2\Request::createFromGlobals();
        $resp = $this->oauthServer->verifyResourceRequest($req);
        return $resp;
    }

    static function init($dbDriver,$tables,$oauth2Paras)
    {
        return new ProtectedResource($dbDriver,$tables,$oauth2Paras);
    }
}