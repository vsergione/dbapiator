<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 12/7/17
 * Time: 3:37 PM
 */
require_once __DIR__."/../third_party/OAuth2-Server/src/OAuth2/Autoloader.php";
require_once __DIR__."/../libraries/OAuth2/Storage/MyStorage.php";
require_once __DIR__."/../libraries/OAuth2/ProtectedResource.php";


OAuth2\Autoloader::register();

/**
 * Class Resource
 * @property CI_Loader load
 * @property CI_Config config
 * @param \OAuth2\Storage\MyStorage mystorage
 */
class Resource extends CI_Controller
{
    private $token;
    private $oauthServer;

    private $isAuth = false;
    /**
     * Resource constructor.
     */
    function __construct ()
    {
        parent::__construct();
        $this->check_auth();
        if(!$this->isAuth)
            die();
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


    function check_auth() {
        $this->load->config("oauth2",true);
        $config = $this->config->item("oauth2");
        $dbDrv = $this->load->database($config["authDbConn"],true);
        if(!$dbDrv)
            die("Invalid OAuth2DB connection");

        $this->isAuth = ProtectedResource::init($dbDrv,$config["tables"],$config["oauth2Paras"])->is_auth();
        return $this->isAuth;
    }




    function index()
    {
        echo "It works";

    }

    function userId()
    {
        header("Content-type: application/json");
        $this->token = $this->oauthServer->getAccessTokenData(OAuth2\Request::createFromGlobals());
        echo json_encode($this->token);
    }

    function method_call($para1)
    {
        echo "Method call with para: $para1";
    }
}

class ACL {
    private $rules;
    /**
     * ACL constructor.
     * @param $userName
     * @param $db
     */
    function __construct ($userName,$db)
    {
        $sql = "SELECT r.* FROM `users_roles` AS ur
            LEFT JOIN roles_rules AS rr
            ON rr.role_id=ur.role_id
            LEFT JOIN rules AS r
            ON r.id=rr.rule_id
            WHERE ur.username='$userName'
            ORDER BY rr.prio DESC";
        echo  $sql;
        $this->rules = $db->query($sql)->result();
    }

    function is_allowed($resource,$op) {
        foreach ($this->rules as $rule) {
            if(preg_match("/$rule->resource/i",$resource) && in_array($rule->method,["*",$op])) {
                echo "/$rule->resource/i";
                return $rule->access == "allow";
            }
        }
        return null;
    }
}