<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 6/19/18
 * Time: 11:51 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$config["default_result_set_limit"] = 10;
$config["force_ssl"] = true;
$config["apisConfigRootDir"] = BASEPATH."../apiator_data";
$config["configStorageType"] = "file";
$config["configStorageSettings"] = [
    "path" => BASEPATH."../apiator_data",
    "fileExt" => "new.json",
    "fields"=>[
        "conn"=>"connection",
        "struct"=>"structure",
        "settings"=>"settings"
    ]
];
$config["adminBaseUrl"] = "/proteus/admin";
