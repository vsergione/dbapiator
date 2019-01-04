<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 6/26/18
 * Time: 5:31 PM
 */

define("MAIN_CONFIG_DIR","../application/config/");
define("MAIN_CONFIG_FILE","config.php");
function respond($result,$message) {
    header("Content-type: application/json");
    echo json_encode(["result"=>$result,"message"=>$message]);
    return true;
}

switch (@$_GET["action"]) {
    case "version":
        respond(true,"Version: 1.1.0");
        break;
    case "general_config":
        header("Content-type: application/json");
        if(!is_file(MAIN_CONFIG_DIR.MAIN_CONFIG_FILE)) {
            if(!is_writable(MAIN_CONFIG_DIR)) {
                respond(false, "File does not exist and the folder is not writable");
                break;
            }
            if(!is_file(MAIN_CONFIG_DIR.MAIN_CONFIG_FILE.".default")) {
                respond(false, "Broken package. No config.php.default");
                break;
            }
            //if(copy(MAIN_CONFIG_DIR.MAIN_CONFIG_FILE.".tpl",MAIN_CONFIG_DIR.MAIN_CONFIG_FILE))

        }
        //if(is_file("../application/config/config.php") && )

        respond(true, "Everything is OK");

        break;
    case "database_config":
        header("Content-type: application/json");
        echo json_encode(["result"=>false,"message"=>"Version: 1.1.0"]);
        break;
    default:
        require_once "setup.html";
}