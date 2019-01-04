<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 11/1/18
 * Time: 4:31 PM
 */

require_once(APPPATH."/libraries/HttpResp.php");
require_once(APPPATH."/libraries/OAuth2/ProtectedResource.php");
require_once(APPPATH."/libraries/OAuth2/MyToken.php");

/**
 * Class MY_AdminPagesController
 * @property CI_Loader $load
 * @property CI_Config $config
 */
abstract class MY_AdminPagesController extends CI_Controller
{
    /**
     * @var bool indicates if the script should halt when not authenticated
     */
    protected $dieWhenNotAuth = true;
    /**
     * @var MyToken
     */
    protected $token;


    /**
     * BASE URL relative to DOCUMENT_ROOT
     * @var string
     */
    protected $basePath;

    protected $oauth2AccessToken=null;
    protected $oauth2UserId=null;
    protected $oauth2ClientId=null;
    protected $oauth2Scope=null;

    protected $data = [
        "baseUrl" => "/proteus",
        "apiRoot"=> "https://develhost/proteus/api/v1/databases",
    ];


    function __construct ()
    {
        parent::__construct();
        $this->basePath = "/proteus";
        $this->baseUrl = "https://develhost/proteus";

        // load oauth2 config
        $this->load->config("oauth2",true);
        $config = $this->config->item("oauth2");

        // init DB driver
        $dbDrv = $this->load->database($config["authDbConn"],true);
        if(!$dbDrv)
            HttpResp::init()->response_code(500)->body("Invalid Oauth2 DB configuration");

        // init token
        $this->token = new MyToken($dbDrv,$config["tables"],$config["oauth2Paras"],$config["grantParas"]);

        $tokenInfo = $this->token->info();
        if(!$tokenInfo) return;

        $this->oauth2AccessToken = $tokenInfo["access_token"];
        $this->oauth2ClientId = $tokenInfo["client_id"];
        $this->oauth2UserId = $tokenInfo["user_id"];
        $this->oauth2Scope = $tokenInfo["scope"];
    }

    protected function not_auth_exit($body)
    {
        if(!$this->oauth2AccessToken)
            HttpResp::not_authorized($body) && die();
    }
}