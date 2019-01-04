<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/19/18
 * Time: 10:29 AM
 */
require_once(APPPATH."libraries/HttpResp.php");
require_once APPPATH."libraries/OAuth2/MyToken.php";

/**
 * Class Register
 * @property CI_Loader $load
 * @property CI_Config $config
 * @property CI_Input $input
 */
class Register extends CI_Controller
{
    /**
     * @var MyToken
     */
    private $token;
    /**
     * @var CI_DB_query_builder|bool $db
     */
    private $db;

    /**
     * @var string
     */
    private $usersTable;


    function __construct ()
    {
        parent::__construct();
        $this->load->config("oauth2",true);
        $config = $this->config->item("oauth2");
        $this->db = $this->load->database($config["authDbConn"],true);
        if(!$this->db) {
            show_error("Invalid Db connection");
            die();
        }

        $this->usersTable = $config["tables"]["users"];
    }

    /**
     * perform the registration
     */
    function index()
    {
        $errs = new push();

        // validate req method
        if($_SERVER["REQUEST_METHOD"]!=="POST") {
            HttpResp::json_out(405);
            die();
        }


        // validate input fields
        if(!$this->input->post("username")) {
            $errs->add("username","Username not provided");
        }
        if(!$this->input->post("email")) {
            $errs->add("email","Email not provided");
        }
        if(strlen($this->input->post("password"))<2) {
            $errs->add("password","Password too short");
        }

        $data = $errs->get();
        if(count($data)) {
            HttpResp::json_out(400, [
                "errors" => $data
            ]);
            return;
        }

        // validate uniq records
        $res = $this->db->get_where($this->usersTable,["username"=>$this->input->post("username")]);
        if($res->num_rows())
            $errs->add("username","Duplicate username");
        $res = $this->db->get_where($this->usersTable,["email"=>$this->input->post("email")]);
        if($res->num_rows())
            $errs->add("email","Duplicate email");

        $data = $errs->get();
        if(count($data)) {
            HttpResp::json_out(400, [
                "errors" => $data
            ]);
            return;
        }

        $this->load->helper("my_utils_helper");

        // insert data
        $this->db->insert($this->usersTable,[
            "username"=>$this->input->post("username"),
            "email"=>$this->input->post("email"),
            "passwordhash"=>password_hash($this->input->post("password"),PASSWORD_BCRYPT),
            "activation_code"=>generateRandomString(50)
        ]);

        // check insert results and if OK get authorisation token
        if($this->db->affected_rows()) {
            $this->init_token();
            $_POST = [
                "username"=>$this->input->post("username"),
                "password"=>$this->input->post("password"),
                "grant_type"=> "password",
                "client_id"=>"demo_client",
                "client_secret"=>""
            ];
            $this->token->password();
        }
        else {
            HttpResp::json_out(500, ["errors" => [
                "title" => "Could not create user. Unknown server error"
            ]]);
        }
        return;
    }

    /**
     * initialize token
     */
    function init_token()
    {
        // load config
        $this->load->config("oauth2",true);
        $config = $this->config->item("oauth2");

        // init DB Drv
        $dbDrv = $this->load->database($config["authDbConn"],true);
        if(!$dbDrv)
            die();

        // init token
        $this->token = new MyToken($dbDrv,$config["tables"],$config["oauth2Paras"],$config["grantParas"]);
    }

}


class push {
    private $data=[];

    function  add()
    {
        $args = func_get_args();
        if(count($args)<2)
            return false;
        $cdata = &$this->data;
        for($i=0;$i<count($args)-1;$i++) {
            if(!array_key_exists($args[$i],$cdata))
                $cdata[$args[$i]] = [];
            $cdata = &$cdata[$args[$i]];
        }
        $cdata[] = $args[$i];
    }

    function get()
    {
        return $this->data;
    }
}
