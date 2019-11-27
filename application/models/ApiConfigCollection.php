<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/2/18
 * Time: 3:00 PM
 */
require_once APPPATH."libraries/Response.php";

/**
 * Class ConfigCollection_model
 */
class ApiConfigCollection extends CI_Model
{
    private $cfgRootDir = null;
    private $apiConfigFileNamePattern = "/^(.*)\.new\.json$/i";

    function __construct ()
    {
        parent::__construct();
        $this->config->load("apiator");
    }

    /**
     * @param $path
     */
    function init($path)
    {
        $this->cfgRootDir = $path;
    }

    /**
     * @param $user
     * @return bool
     */
    private function user_dir_exists($user)
    {
        return is_dir($this->cfgRootDir."/".$user);
    }

    private function config_exists($user,$name)
    {
        return is_file("$this->cfgRootDir/$user/$name.json");

    }

    /**
     * @param $user
     * @param bool $includeDetails
     * @param int $offset
     * @param int $pageSize
     * @return array|Response
     */
    function get_list($user,$includeDetails=true,$offset=0,$pageSize=20)
    {
        if(!$this->user_dir_exists($user))
            return Response::make(false,404,"Invalid user");

        $dir = opendir($this->cfgRootDir."/".$user);
        $cfgDir = $this->cfgRootDir."/".$user;

        $entries = [];
        $idx = 0;
        while(($fileName=readdir($dir)) && ($idx<$offset+$pageSize)) {
            if($idx<$offset) continue;
            if(!preg_match($this->apiConfigFileNamePattern,$fileName,$matches))
                continue;
            $idx++;
            $entry = [
                "id"=>$matches[1],
                "type"=>"apps"
            ];
            if($includeDetails) {
                $contents = file_get_contents( "$cfgDir/$fileName");
                if($contents) {
                    $data = json_decode($contents);

                    $entry["attributes"] = [
                        "name" => $matches[1],
                        "type" => $data->connConfig->type,
                        "host" => $data->connConfig->host,
                        "database" => $data->connConfig->database,
                    ];
                }
            }
            $entries[] = $entry;

        }

        return Response::make(true,200,$entries);
    }

}