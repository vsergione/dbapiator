<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/16/18
 * Time: 11:03 AM
 */

namespace Apiator\Config;

/**
 * Class ApisEnumerator
 * wrapper class to interface with various storage drivers for config files
 * @package Apiator\Config
 */
class ApisEnumerator implements  EnumeratorInterface
{
    /**
     * @var EnumeratorInterface
     */
    private $driver;

    private function __construct ($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $type storage type  [file,sqldb]
     * @param array $settings parameters for initializing the storage
     * @param string $currentUser
     * @return ApisEnumerator
     */
    public static function init($type,$settings,$currentUser) {

        switch ($type) {
            case "file":
                if(array_key_exists("path",$settings) && array_key_exists("fileExt",$settings))
                    return new ApisEnumerator(FilesEnumerator::init($settings["path"],$settings["fileExt"],$currentUser));
                break;
            case "sqldb":
                return new ApisEnumerator(SQLDbEnumerator::init($settings["db"],$settings));
        }

        return null;
    }

    function exists ($name)
    {
        return $this->driver->exists($name);
    }

    /**
     * @param string|null $filter
     * @param int $pageSize
     * @param int $offset
     * @return array
     */
    function enumerate ($filter=null,$pageSize=10, $offset=0)
    {
        return $this->driver->enumerate($filter,$pageSize,$offset);
    }

    /**
     * @param string $name
     * @return bool
     */
    function delete ($name)
    {
        return $this->driver->delete($name);
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    function rename($oldName, $newName)
    {
        return $this->driver->rename($oldName,$newName);
    }

    /**
     * creates a new item
     * @param string $name
     * @param mixed $content
     * @return ApiConfig
     */
    function add($name,$content)
    {
        return $this->driver->add($name,$content);
    }

    /**
     * get an item by name
     * @param string $name
     * @param bool $asArray
     * @return ApiConfig
     */
    function get ($name, $asArray=true)
    {
        return $this->driver->get($name,$asArray);
    }

    /**
     * @param string $name
     * @param mixed $content
     * @return bool
     */
    function update ($name, $content)
    {
        return $this->driver->update($name,$content);
    }
}