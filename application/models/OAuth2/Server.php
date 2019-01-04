<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 12/4/17
 * Time: 4:43 PM
 */

class Server extends CI_Model
{
    private $handler;
    function __construct ($config)
    {
        $storage = $config["storage"];
        $this->handler = new \OAuth2\Server($storage);
        $this->handler->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
        $this->handler->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
    }
}