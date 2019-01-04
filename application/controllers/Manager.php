<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/3/18
 * Time: 10:08 AM
 */

//echo APPPATH;
require_once(__DIR__."/../libraries/HttpResp.php");
require_once(__DIR__."/../helpers/manager_helper.php");
require_once(__DIR__."/../third_party/Apiator/Autoloader.php");
\Apiator\Autoloader::register();

/**
 * Class Manager
 * TODO: implement auth
 * @property CI_Loader load
 * @property CI_Config config
 * @property ApiConfigFile apiConfig
 * @property ApiConfigCollection apisCollection
 * @property CI_Input input
 */
class Manager extends CI_Controller
{
    private $activeUser = "apiator";
    private $baseUrl = "/proteus/admin/";
    private $supportedDbEngines = ["mysqli"];

    /**
     * @var \Apiator\Config\ApisEnumerator
     */
    private $apiConfigsEnumerator = null;

    function index()
    {

    }

    function __construct ()
    {
        parent::__construct();

        // load configuration
        $this->config->load("apiator",true);
        $ctrlCfg = $this->config->item("apiator");

        $this->baseUrl = $ctrlCfg["adminBaseUrl"];

        // initializes ApisEnumerator
        $this->apiConfigsEnumerator = \Apiator\Config\ApisEnumerator::init(
            $ctrlCfg["configStorageType"]
            ,$ctrlCfg["configStorageSettings"],
            $this->activeUser);

        if(is_null($this->apiConfigsEnumerator))
            HttpResp::json_out(500,["errors"=>[
                ["title"=>"Invalid server configuration. Could not initialize configs enumerator"]
            ]]) && die();

    }

    /**
     *
     */
    function apps()
    {
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                $this->get_apis_list();
                break;
            case "POST":
                $this->create_api();
                break;
            default:
                $this->invalid_req_method();
        }
        return;
    }

    function getApp($name) {
        $this->get_api($name);
        return true;
    }

    function deleteApp($name) {
        $this->delete_api($name);
        return true;
    }

    function putApp($name) {
        http_response_code(409);
        echo "put";
        return true;
    }

    function patchApp($name) {
        echo "patch";
        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    function app($name=null)
    {
        if(!$name)
            HttpResp::json_out(400,"Invalid request: missing app name") || die();

        if(!$this->apiConfigsEnumerator->get($name))
            return HttpResp::json_out(404,["errors"=>["title"=>"Api not found"]]);

        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                $this->get_api($name);
                break;
            case "DELETE":
                $this->delete_api($name);
                break;
            default:
                // TODO: proper response
                $this->invalid_req_method();
        }
        return true;
    }

    /**
     * shorthand method to send and HTTP method not supported
     */
    private function invalid_req_method()
    {
        HttpResp::json_out(405,[
            "errors"=>[
                [
                    "title"=>"Invalid request: HTTP Method not allowed"
                ]
            ]
        ]);
    }

    /**
     * delete API
     * @param string $name API name
     * @return bool
     */
    private function delete_api($name)
    {
        if($this->apiConfigsEnumerator->delete($name))
            HttpResp::json_out(204);
        else
            HttpResp::json_out(500,["errors"=>[
                [
                    "title"=>"Could not remove API. Please contact admin"
                ]
            ]]);
        return true;
    }

    /**
     * get API
     * @param string $name
     * @return bool
     */
    private function get_api($name)
    {
        $apiConfig = $this->apiConfigsEnumerator->get($name);
        if($apiConfig) {
            $conn = $apiConfig->getConnection();

            HttpResp::json_out(200, [
                "data" => [
                    "id" => $name,
                    "type" => "apis",
                    "attributes" => [
                        "type" => $conn["type"],    //$conn->getType(),
                        "name" => $name,
                        "host" => $conn["host"],    // $conn->getHost(),
                        "database" => $conn["database"] // $conn->getDatabase()
                    ],
                    "links" => [
                        "self" => $this->baseUrl . "apis/$name"
                    ]
                ]]);
        }
        else
            HttpResp::json_out(500,["errors"=>[["title"=>"Server error; could not read config"]]]);

        return true;
    }


    /**
     * @param $name
     * @param $section
     * @return bool
     */
    function getSetConfig($name,$section) {
        $allowedMethods = ["GET","PUT","OPTIONS"];
        if(!in_array($_SERVER["REQUEST_METHOD"],$allowedMethods))
            return $this->invalid_req_method();

        /**
         * respond to preflight request
         */
        if ($_SERVER["REQUEST_METHOD"]=="OPTIONS" && array_key_exists("HTTP_ACCESS_CONTROL_REQUEST_METHOD",$_SERVER))
            return HttpResp::init()->allow_methods(implode(", ",$allowedMethods))->output();

        $conf = $this->apiConfigsEnumerator->get($name);
        if(!$conf)
            return HttpResp::json_out(404,["errors"=>[
                [
                    "title"=>"Api $name not found"
                ]
            ]]);

        if ($_SERVER["REQUEST_METHOD"]=="GET") {
            switch ($section) {
                case "structure":
                    return HttpResp::json_out(200,$conf->getStructure());
                case "connection":
                    $conn = $conf->getConnection();
                    if(!$conn)
                        return HttpResp::json_out(500,["errors"=>[
                            [
                                "title"=>"Empty connection parameters"
                            ]
                        ]]);

                    HttpResp::json_out(200,[
                        "data"=>[
                            "id"=>$name,
                            "type"=>"connection",
                            "attributes"=>$conn
                        ]
                    ]);
                    return true;
                case "settings":
                    return HttpResp::json_out(200,$conf->getSettings());

            }
        }

        if ($_SERVER["REQUEST_METHOD"]=="PUT") {
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData);
            if(!is_object($jsonData))
                return HttpResp::json_out(400, ["errors"=>[[
                    "title"=>"Invalid structure submited"
                ]]]);

            switch ($section) {
                case "structure":
                    $conf->setStructure($jsonData);
                    return HttpResp::json_out(200,$conf->getStructure());
                case "connection":
                    $conf->setConnection($jsonData);
                    return HttpResp::json_out(200,$conf->getConnection());
                case "settings":
                    $conf->setSettings($jsonData);
                    return HttpResp::json_out(200,$conf->getSettings());

            }
        }
        return true;
    }


    /**
     * @param $name
     * @return bool|int
     */
    function regen($name)
    {
        $allowedMethods = ["GET","OPTIONS"];

        if(!in_array($_SERVER["REQUEST_METHOD"],$allowedMethods))
            return $this->invalid_req_method();

        $conf = $this->apiConfigsEnumerator->get($name);
        if(!$conf)
            return HttpResp::json_out(404,["errors"=>[
                [
                    "title"=>"Api $name not found"
                ]
            ]]);

        $conn = $conf->getConnection();
        $res = $this->test_connection(true,$conn);
        if(!$res->success)
            return HttpResp::json_out(500,$res->data);


        $resp = (generate_config($conn["type"],$conn["database"],$res->data));
        HttpResp::json_out(200,$resp->data);
        return true;
    }


    /**
     * @return bool
     * receives connection details + label
     */
    function create_api() {

        // check if label provided
        if(empty($this->input->post("label")))
            return HttpResp::json_out(400,["errors"=>[["title"=>"Missing label"]]]);

        // check for duplicate label
        $apiName =  $this->input->post("label");
        if($this->apiConfigsEnumerator->get($apiName))
            return HttpResp::json_out(409,["errors"=>[["title"=>"Duplicate label"]]]);

        // test connection
        $res = $this->test_connection(true);
        if(!$res->success)
            return HttpResp::json_out($res->code,["errors"=>$res->data]);

        // set db driver
        $dbConn = $res->data;

        switch ($_POST["type"]) {
            case "mysqli":
                $config = generate_mysql_config($_POST["database"],$dbConn);
                break;
            default:
                $config = null;
        }

        $confData = [
            "structure"=>$config,
            "connection"=>[
                "host"=>$_POST["host"],
                "type"=>$_POST["type"],
                "username"=>$_POST["username"],
                "password"=>$_POST["password"],
                "database"=>$_POST["database"]
            ],
            "settings"=>[]
        ];


        $apiConfig = $this->apiConfigsEnumerator->add($apiName,$confData);
        if($apiConfig) {
            $conn = $apiConfig->getConnection();
            $dataOut = [
                "data"=> [
                        "id" => $apiName,
                        "type" => "apis",
                        "attributes" => [
                            "type" => $conn["type"],    //$conn->getType(),
                            "name" => $apiName,
                            "host" => $conn["host"],    // $conn->getHost(),
                            "database" => $conn["database"] // $conn->getDatabase()
                        ],
                        "links" => [
                            "self" => $this->baseUrl . "apis/$apiName"
                        ]
                    ]];
            HttpResp::json_out(200,$dataOut);
        }

        return true;
    }

    /**
     * test connection
     * @param bool $internalCall
     * @return Response|bool|null
     */
    function test_connection($internalCall=false,$dbConf=null)
    {
        if($internalCall && $dbConf)
            $confArr = $dbConf;
        else
            $confArr = $_POST;

        if(!$internalCall && !$dbConf && $_SERVER["REQUEST_METHOD"]!=="POST")
            HttpResp::json_out(400,["errors"=>[
                [
                    "code"=>405,
                    "title"=>"Invalid request: method not allowed"
                ]
            ]]) || die();

        $errors = [];
        if(!isset($confArr["host"])) {
            $errors[] = [
                "code" => 400,
                "title"=> "Missing host"
            ];
        }
        if(!isset($confArr["type"])) {
            $errors[] = [
                "code" => 400,
                "title"=> "Missing type"
            ];
        }
        elseif(!in_array($confArr["type"],$this->supportedDbEngines)) {
            $errors[] = [
                "code" => 400,
                "title"=> "Invalid type"
            ];
        }
        if(!isset($confArr["username"])) {
            $errors[] = [
                "code" => 400,
                "title"=> "Missing username"
            ];
        }
        if(!isset($confArr["password"])) {
            $_POST["password"]  = null;
        }
        if(!isset($confArr["database"])) {
            $errors[] = [
                "code" => 400,
                "title"=> "Missing database name"
            ];
        }
        if(count($errors))
            return $internalCall?
                Response::make(false,400,$errors):
                HttpResp::json_out(400, ["errors" => $errors]);


        $res = get_db_conn($this,$confArr);
        if($internalCall)
            return $res;

        if($res->success)
            HttpResp::json_out(204);
        else
            HttpResp::json_out(503, ["errors" => $res->data]);

        return null;
    }


    /**
     * list APIs
     */
    private function get_apis_list()
    {
        $jsonApiResponse = [
            "data"=>[],
            "links"=>[
                "self" => $this->baseUrl . "/apis"
            ]
        ];
        $pageSize = $this->input->get("pageSize")?$this->input->get("pageSize"):20;
        $offset = $this->input->get("offset")?$this->input->get("offset"):0;
        $filter = $this->input->get("filter")?$this->input->get("filter"):null;

        $configs = $this->apiConfigsEnumerator->enumerate($filter,$pageSize,$offset);
        foreach ($configs as $configName) {
            $apiCfg = $this->apiConfigsEnumerator->get($configName);

            if(!$apiCfg)
                continue;
            $conn = $apiCfg->getConnection();
            $jsonApiResponse["data"][] = [
                "id" => $configName,
                "type" => "apis",
                "attributes" => [
                    "type" => $conn["type"],    //$conn->getType(),
                    "name" => $configName,
                    "host" => $conn["host"],    // $conn->getHost(),
                    "database" => $conn["database"] // $conn->getDatabase()
                ],
                "links" => [
                    "self" => $this->baseUrl . "/apis/$configName"
                ]
            ];
        }
        HttpResp::json_out(200, $jsonApiResponse);

    }


}