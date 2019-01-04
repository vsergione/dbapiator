<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 12/8/17
 * Time: 2:42 PM
 */
require_once __DIR__."/../../third_party/OAuth2-Server/src/OAuth2/Autoloader.php";
OAuth2\Autoloader::register();

require_once __DIR__."/../../libraries/OAuth2/MyToken.php";


/**
 * Class Token
 * @property CI_Loader load
 * @property MyStorage mystorage
 * @property CI_Config config
 */
class Token extends CI_Controller
{
    /**
     * @var MyStorage
     */
    private $oauthStorage;

    /**
     * @var MyToken
     */
    private $token;


    public function __construct ()
    {
        parent::__construct();
        $this->init_token();
    }

    function init_token()
    {
        $this->load->config("oauth2",true);
        $config = $this->config->item("oauth2");
        $dbDrv = $this->load->database($config["authDbConn"],true);
        if(!$dbDrv)
            die();

        $this->token = new MyToken($dbDrv,$config["tables"],$config["oauth2Paras"],$config["grantParas"]);
    }

    public function password()
    {
        $this->token->password();
    }

    public function refresh()
    {
        $this->token->refresh();
    }

    public function info()
    {
        header("Content-type: application/json");
        $tokenInfo = $this->token->info();
        if($tokenInfo)
            echo json_encode($tokenInfo);
        else
            http_response_code(404);
    }

    public function revoke()
    {
        $this->token->revoke();
    }

    public function index()
    {
        header("Content-type: application/json");
        echo json_encode(["error_message"=>"Invalid call"]);
    }
}