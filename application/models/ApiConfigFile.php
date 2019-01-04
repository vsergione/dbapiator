<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/2/18
 * Time: 1:14 PM
 */
require_once APPPATH."/libraries/Response.php";

/**
 * Class Config_model
 */
class ApiConfigFile extends CI_Model {

    /**
     * parsed configuration data
     * @var array|object
     */
    private $configData;

    /**
     * current configuration file path
     * @var null
     */
    private $cfgFilePath = null;

    function __construct ()
    {
        parent::__construct();
    }

    /**
     * @param string $dirPath
     */
    function init($dirPath)
    {
        $this->cfgRootDir = $dirPath;
    }


    /**
     * loads a configuration file
     * @param string $rootDir
     * @param string $user
     * @param string $apiName
     * @return Response
     */
    function loadr($rootDir, $user, $apiName)
    {
        if(!is_file("$rootDir/$user/$apiName.json"))
            // TODO: make proper response
            return Response::make(false,404,"File not found");

        $this->cfgFilePath = "$rootDir/$user/$apiName.json";

        $contents = file_get_contents( $this->cfgFilePath);
        if(!$contents)
            // TODO: make proper response
            return Response::make(false,500,"Could not open config file");

        if(!$this->data2var(json_decode($contents)))
            // TODO: make proper response
            return Response::make(false,500,"Invalid config file structure");

        return Response::make(true,200);
    }

    /**
     * @param $data
     * @return bool
     */
    private function data2var($data)
    {
        if(!is_object($data))
            return false;

        $tmp = new stdClass();
        if(property_exists($data,"settings"))
            $tmp->settings = $data->settings;
        if(property_exists($data,"connection"))
            $tmp->settings = $data->connection;
        if(property_exists($data,"structure"))
            $tmp->settings = $data->structure;
        return true;
    }

    function delete()
    {
        if(!isset($this->cfgFilePath))
            return false;
        //return unlink($this->cfgFilePath);
        return true;
    }


/*
    function get_settings()
    {
        if(is_null($this->configData)) return Response::make(false);
        return $this->configData->settings;
    }

    function save_settings()
    {
        if(is_null($this->configData)) return Response::make(false);
    }

    function get_connection()
    {
        if(is_null($this->configData)) return falseResponse::make(false);
    }

    function set_connection()
    {
        if(is_null($this->configData)) return Response::make(false);
    }

    function get_structure()
    {
        if(is_null($this->configData)) return Response::make(false);
    }

    function set_structure()
    {
        if(is_null($this->configData)) return Response::make(false);
    }
*/
    private function persist()
    {
        $this->load->helper("file_helper");
        if(!write_file($this->cfgFilePath,json_encode($this->configData)))
            errors_response([
                [
                    "status"=>500,
                    "title"=>"Error writing configuration file"
                ]
            ],500);

        http_response_code(201);
        die();
    }

    /**
     * @param $fn
     * @return Response
     */
    public static function load($fn) {
        if(is_file($fn))
            return Response::make(false,404,"File not found");
        $data = file_get_contents($fn);
        if(!$data)
            return Response::make(false, 500, "Could not read file");

        $data = json_decode($data);
    }

    /**
     * @param $fn
     * @param $data
     */
    public static function create($fn,$data) {

    }
}