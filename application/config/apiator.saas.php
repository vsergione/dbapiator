<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 6/19/18
 * Time: 11:51 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

define("BASE_DOMAIN",".dbapi.apiator");
define("APIS_DIR","/var/www/domains/apiator/apiator_data/___apis");
define("CFG_DIR_REL_PATH","/dbapi");
$config["default_page_size_limit"] = 10;


$config["api_config_dir"] = (function() {
    $tmp = explode(BASE_DOMAIN,$_SERVER["SERVER_NAME"]);
    if(count($tmp)<2)
        die("Invalid URL");
    $apiId = $tmp[0];

    return APIS_DIR."/$apiId".CFG_DIR_REL_PATH;
})();

