<?php
/**
 * Created by PhpStorm.
 * User: contvsergiu
 * Date: 10/10/18
 * Time: 2:28 PM
 */
namespace Apiator\Config;

use Apiator\DataTypes\Connection;

interface ConfigItemInterface {
    /**
     * @param string $id storage ID (can be filePath or DSN
     * @param string|\stdClass|array $data data to be saved
     * @return \Response|ApiConfig
     */
    static function create($id,$data);

    /**
     * @param string $id
     * @return \Response
     */
    static function load($id);


    /**
     * persists the configuration
     * @return bool
     */
    function persist($data);

    function get_contents();

    function getStructure();
    function getSettings();

    /**
     * @return Connection
     */
    function getConnection();

}