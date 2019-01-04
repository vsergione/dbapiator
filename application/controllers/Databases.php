<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.'core/MY_RestController.php');

/**
 * @property CI_Config config
 * @property CI_Loader load
 * @property CI_Input input
 * @property Records_model resources
 * @property CI_Output output
 * @property  Data_model dm
 */
class Databases extends MY_RestController
{
    /**
     * Api_v1 constructor.
     */
    public function __construct ()
    {
        parent::__construct();

        if(array_key_exists("debug",$_GET)) echo "Controller: ".__CLASS__."\n";
        $this->load->helper('url');
    }

    /**
     * @param $pathComponents
     * @return int
     */
    public function _get($pathComponents) {
        header("Content-type: application/json");
        if(empty($pathComponents[0]))
            return print(json_encode($this->list_databases(),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if(empty($pathComponents[1]))
            return print(json_encode($this->get_session($pathComponents[0]),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if($pathComponents[1]=="config")
            return print(json_encode($this->get_config($pathComponents[0]),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    }

    private function get_config($session) {
        require_once(__DIR__."/../config/database.php");
        if(!isset($db[$session]))
            return http_response_code("404")?["error"=>"Session not found"]:null;

        $cfgFile = __DIR__."/../poliapi/config/$session.json";
        if(!is_file($cfgFile))
            return http_response_code("404")?["error"=>"Config file not found"]:null;

        return json_decode(implode("",file($cfgFile)));
    }
    /**
     * @param $name
     * @return array
     */
    private function get_session($name) {
        require_once(__DIR__."/../config/database.php");
        $dbs = [];
        if(!isset($db[$name]))
            return http_response_code("404")?["error"=>"Not found"]:null;

        return array(
            "data"=>[
                "type"=>"schema",
                "id"=>$name,
                "attributes"=>[
                    "schema"=>$db[$name]["database"]
                ]
            ],
            "meta"=> array(
                "total" => 4,
                "offset"=>0
            ),
            "links"=>array(
                "self"=>site_url()."api/v1/databases/$name/"
            )
        );
    }


    /**
     * @return array
     */
    private function list_databases() {
        require_once(__DIR__."/../config/database.php");
        $dbs = [];
        foreach ($db as $lbl=>$entry) {
            $dbs[] = [
                "id"=>$lbl,
                "type"=>"sessions",
                "attributes"=>[
                    "schema"=>$entry["database"]
                ],
                "links"=>[
                    "self"=>site_url()."api/v1/databases/$lbl/"
                ]
            ];
        }
        return array(
            "data"=>$dbs,
            "meta"=> array(
                "total" => 4,
                "offset"=>0
            ),
            "links"=>array(
                "self"=>site_url()."api/v1/databases"
            )
        );
    }
}
