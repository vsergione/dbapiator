<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/10/18
 * Time: 1:51 PM
 */
namespace Apiator\Config;

class ApiConfig
{
    /**
     * @var
     */
    private $structure;
    /**
     * @var
     */
    private $connection;
    /**
     * @var
     */
    private $settings;

    /**
     * name of config the way it is identified by the enumerator
     * @var string
     */
    private $name;

    /**
     * @var EnumeratorInterface
     */
    private $enum;

    /**
     * @var bool
     */
    private $validConfig = true;
    private $defaultSettings = [];

    /**
     * ApiConfig constructor.
     * @param string $name //TODO implement an API definition Class
     * @param string|array $data
     * @param EnumeratorInterface $enum
     * @param bool $decodeAsArray
     */
    function __construct ($name,$data,&$enum,$decodeAsArray)
    {

        $this->name = $name;
        $this->enum = $enum;

        if(is_string($data))
            $data = json_decode($data,$decodeAsArray);


        // settings validation
        if(isset($data["settings"]))
            $this->settings = $data["settings"];
        else
            $this->settings = $this->defaultSettings;

        // connection validation
        if(isset($data["connection"]))
            $this->connection = $data["connection"];
        else
            // TODO log print_r("empty conn") |
            $this->validConfig = false;

        // structure validation
        if(isset($data["structure"]))
            $this->structure = $data["structure"];
        else
            // TODO log invalid structure
            $this->validConfig = false;

    }


    /**
     * @param $name
     * @param $contents
     * @param $enum
     * @param bool $decodeAsArray
     * @return ApiConfig|null
     */
    public static function init($name,$contents,&$enum,$decodeAsArray=true) {

        if(!$name)
            return null;

        if(!is_string($name) && !is_array($contents))
            return null;

        $item = new ApiConfig($name,$contents,$enum,$decodeAsArray);

        if($item->validConfig)
            return $item;

        return null;

    }


    /**
     * @return mixed
     */
    function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return mixed
     */
    function getStructure()
    {
        return $this->structure;
    }

    /**
     * @param $value
     */
    function setSettings($value)
    {
        $this->settings = $value;
        $this->enum->update($this->name,$this->to_json());
    }

    function setConnection($value)
    {
        $this->connection = $value;
        $this->enum->update($this->name,$this->to_json());
    }

    function setStructure($value)
    {
        $this->structure = $value;
        $this->enum->update($this->name,$this->to_json());
    }


    /**
     * @return false|string
     */
    function to_json()
    {
        return json_encode([
            "connection"=>$this->connection,
            "settings"=>$this->settings,
            "structure"=>$this->structure
        ],JSON_PRETTY_PRINT);
    }

}