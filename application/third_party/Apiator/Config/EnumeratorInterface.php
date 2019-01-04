<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/10/18
 * Time: 5:55 PM
 */
namespace Apiator\Config;

interface  EnumeratorInterface
{
    /**
     * @param mixed $para1
     * @param mixed $para2
     * @param mixed $para3
     * @return EnumeratorInterface
     */
    static function init($para1,$para2,$para3);

    /**
     * @param mixed $filter
     * @param int $pageSize
     * @param int $offset
     * @return array
     */
    function enumerate($filter,$pageSize,$offset);

    /**
     * @param string $name
     * @return bool
     */
    function delete($name);

    /**
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    function rename($oldName,$newName);

    /**
     * @param string $name
     * @return bool
     */
    function exists($name);

    /**
     * @param string $name
     * @param mixed $content
     * @return ApiConfig
     */
    function add($name, $content);

    /**
     * @param string $name
     * @param bool $asArray
     * @return ApiConfig
     */
    function get($name,$asArray);

    /**
     * @param $name
     * @param $content
     * @return bool
     */
    function update($name,$content);

}