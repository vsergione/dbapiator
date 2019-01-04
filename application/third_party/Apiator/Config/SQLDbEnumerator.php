<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/16/18
 * Time: 11:22 AM
 */

namespace Apiator\Config;

/**
 * Class MysqlEnumerator
 * TODO: to finish implementing
 * @package Apiator\Config
 */
class SQLDbEnumerator implements EnumeratorInterface
{

    private $defaultFldMapping = [
        "name" => "name"
    ];
    /**
     * @var \CI_DB_pdo_driver
     */
    private $dbDriver;
    private $tbl;

    private function __construct ($dbDriver,$tbl,$mapping)
    {
        $this->dbDriver = $dbDriver;
        $this->tbl = $tbl;
    }

    /**
     * @param mixed $filter
     * @param int $pageSize
     * @param int $offset
     * @return array|void
     */
    function enumerate ($filter, $pageSize, $offset)
    {
        // TODO: Implement enumerate() method.
    }

    /**
     * @param string $name
     * @return bool|void
     */
    function delete ($name)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @return bool|void
     */
    function rename ($oldName, $newName)
    {
        // TODO: Implement rename() method.
    }

    /**
     * @param string $name
     * @return bool|void
     */
    function exists ($name)
    {
        // TODO: Implement exists() method.
    }

    /**
     * @param mixed $dbDriver
     * @param mixed $settings
     * @param mixed $somePara
     * @return EnumeratorInterface|SQLDbEnumerator|null
     */
    static function init ($dbDriver,$settings,$somePara)
    {
        // TODO: Implement init() method.
        if($dbDriver && in_array("table",$settings))
            return new SQLDbEnumerator($dbDriver,$settings["table"],
                in_array("mapping",$settings)?$settings["mapping"]:null);
        return null;
    }

    /**
     * @param string $name
     * @param mixed $content
     * @return ApiConfig|void
     */
    function add ($name, $content)
    {
        // TODO: Implement add() method.
    }

    /**
     * @param $name
     * @return ApiConfig|void
     */
    function get ($name)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param $name
     * @param $content
     * @return bool|void
     */
    function update ($name, $content)
    {
        // TODO: Implement update() method.
    }
}