<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 6/19/18
 * Time: 11:51 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$config["deployment_type"] = "saas"; // saas or single
//$config["deployment_type"] = "single"; // saas or single
// directory to store links to all APIs of all users
$config["apisDir"] = "/var/www/domains/apiator/apiator_data/___apis";
$config["api_config_dir"] = "/var/www/domains/apiator/apiator_data/___apis/5ccc3e7865d2f";

$config["base_domain"] = ".dbapi.apiator";
$config["base_domain_path"] = ".dbapi.apiator/v2";

// apiId is first part of the host_name
$config["api_id"] = function() {
    $arr = explode(".dbapi.apiator",$_SERVER["SERVER_NAME"]);
    if(count($arr)!==2)
        return false;

    return $arr[0];
};

// apiId is the first path segment
$config["api_id"] = function() {
    $tmp = explode("/",$_SERVER["REQUEST_URI"]);
    if(count($tmp)<2)
        return false;

    return $tmp[1];
};





// default recordset page size
$config["default_page_limit"] = 10;
