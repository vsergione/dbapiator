#!/usr/bin/php
<?php
define("BASEPATH",getcwd());

$apiatorCfgTpl = __DIR__ . "/application/config/apiator.single.php.tpl";
$apiatorCfgDst = __DIR__ . "/application/config/apiator.php";
$ciConfigTpl = __DIR__ . "/application/config/config.php.tpl";
$ciConfigDst = __DIR__."/application/config/config.php";


if(count($argv)===1) {
    echo "Syntax: php setup.php [command] [OPTIONS]\n\n";
    echo "Description: Command line DB Apiator administration utility\n\n";
    echo "Commands:\n";
    echo "    install [/project_name/db_engine/db_host/db_user/db_pass/db_name]: Installs DbAPI on server by creating config files and\n";
    echo "    setupdb /project_name/db_engine/db_host/db_user/db_pass/db_name: Creates a REST API for a given database\n\n";
    echo "    regen: Regenerates an existing API\n\n";
    echo "Setupdb Options:\n";
    echo "-c /project_name/db_engine/db_host/db_user/db_pass/db_name\n";
    echo "All configuration parameters are provided directly in the command line\n";
    echo "   - project_name alfanumeric string with no spaces. Used to uniquely identify the database within the current installation\n";
    echo "   - db_engine database engine. Currently only mysqli is supported\n";
    echo "   - db_host IP or hostname of database server. It can also contain the port number in case it differs from default value (eg. localhost:3688)\n" ;
    echo "   - db_user database username\n";
    echo "   - db_pass database password\n";
    echo "   - db_name database name\n\n";
    echo "regen Options:\n";
    die();
}

if(!is_dir(__DIR__."/application"))
    die("Invalid setup script.... Cannot find application directory");
@include_once $apiatorCfgDst;
$apisConfigDir = defined("CFG_DIR_BASEPATH")?CFG_DIR_BASEPATH:getcwd();
//echo CFG_DIR_BASEPATH;
array_shift($argv);
$cmd = array_shift($argv);
switch($cmd) {
    case "install":
        install($apisConfigDir,$argv);
        break;
    case "setupdb":
        setupdb($apisConfigDir,$argv);
        break;
    case "regen":
        regen($apisConfigDir,array_shift($argv));
        break;
    default:
        die("invalid command\n\n");
}

/**
 * - check app path
 * - read existing config
 */
function install($configDir,$argv) {
    global $apiatorCfgTpl,$apiatorCfgDst,$ciConfigTpl,$ciConfigDst;

    echo "DbAPI install wizard. Please fill requested info.\n\n";

    // Projects base dir
    $readConfigDir = readline("Configurations base directory [".$configDir."]: ");
    if($readConfigDir!=="")
        $configDir = $readConfigDir;

    if(!is_dir($configDir)) {
        $confirmCreateProjeDir = readline("Directory $configDir does not exist. Do you want to create it? ([Yes]\\Y\\No\\N)");
        $conf = $confirmCreateProjeDir===""?"yes":$confirmCreateProjeDir;
        if(in_array(strtolower($conf),["yes","y"]))
            if(!mkdir($configDir))
                die("Could not create $configDir");
    }


    $apiatorCfg = file_get_contents($apiatorCfgTpl);
    if($apiatorCfg===false)
        die("DB Apiator config template not found");
    $apiatorCfg = str_replace("%%conn_dir_path%%",$configDir,$apiatorCfg);
    if(file_put_contents($apiatorCfgDst,$apiatorCfg)===false)
        die("Could not save $apiatorCfgDst config file");



    $ciConfig = file_get_contents($ciConfigTpl);
    if($ciConfig===false)
        die("CI config template not found");

    $cfg = include $ciConfigDst;
    $prompt = "Base URL for DBAPI (optional)";
    if(isset($cfg["base_url"])) {
        $baseUrl = $cfg["base_url"];
        $prompt = "Base URL for DBAPI [".$cfg["base_url"]."]";
    }
    $readBaseUrl = readline($prompt);
    $readBaseUrl = (empty($readBaseUrl) && $baseUrl)?$baseUrl:$readBaseUrl;

    $urlRegex = "/^\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))$/";
    if(preg_match($urlRegex,$readBaseUrl))
        $ciConfig = str_replace("%%base_url%%","'$readBaseUrl'",$ciConfig);
    else
        $ciConfig = str_replace("%%base_url%%","null",$ciConfig);

    if(file_put_contents($ciConfigDst,$ciConfig)===false)
        die("Could not save $ciConfigDst config file");

    echo "\n";
    $setupConfirm = readline("Do you want to configure a project ([Y]/N)?");
    if(in_array(strtoupper($setupConfirm),["","Y"]))
        setupdb($configDir,$argv);
}

function regen($configDir,$projName)
{
    if(!$projName)
        die("Please specify a project name\n");
    $projPath = "$configDir/$projName";
    if(!is_dir($projPath))
        die("Project $projName not found\n");
    $conn = require_once "$projPath/connection.php";
    $args = [
            sprintf("/%s/%s/%s/%s/%s/%s",$projName,$conn["dbdriver"],$conn["hostname"],$conn["username"],$conn["password"],$conn["database"])
    ];
    if(is_file($projPath."/parse_helper.php"))
        $args[] = $projPath."/parse_helper.php";
    setupdb($configDir,$args);
}

/**
 * @param $params
 * @return array
 */
function get_parameters($params)
{
    $pattern = "/\/([a-z0-9]+)\/(mysqli)\/([0-9a-z\.\-\_]+(\:[0-9]{2,5})?)\/([a-z][a-z0-9\.\_\-]+)\/(.*)\/([a-z][a-z0-9\.\_\-]+)/";
    if(isset($params[0])) {
        if(!preg_match($pattern,$params[0],$matches))
            die("Invalid configuration parameters. See help.\n\n");
        $projName = $matches[1];
        $cmd = sprintf("dbapi/ConfigGen/cli/%s/%s/%s/%s/%s",$matches[2],$matches[3],$matches[5],$matches[6],$matches[7]);
        return [$projName,$cmd];
    }

    // interactive mode
    $read = readline("Project name (alphanumeric): ");
    $projName = $read===""?"localhost":$read;

    $read = readline("Database engine [mysqli]: ");
    $engine = $read===""?"mysqli":$read;
    if($engine!=="mysqli")
        die("\nError: Database engine '$engine' not supported\n");

    $read = readline("Database host (eg: localhost:3306) [localhost]: ");
    $host = $read===""?"localhost":$read;

    $user = readline("Database username: ");
    $pass = readline("Database password: ");
    $dbname = readline("Database name: ");

    $cmd = sprintf("dbapi/ConfigGen/cli/%s/%s/%s/%s/%s",$engine,$host,$user,$pass,$dbname);

    return [$projName,$cmd];
}

/**
 * @param $configDir
 * @param $cli_args
 */
function setupdb($configDir,$cli_args) {
    global $apiatorCfgTpl,$apiatorCfgDst,$ciConfigTpl,$ciConfigDst;

    list($projName,$cmd) = get_parameters($cli_args);


    if(!$cmd)
        die("No options provided. Nothing to do.\n\n");

    $projPath = $configDir."/".$projName;

    if(!is_dir($projPath) && !mkdir($projPath))
        die("Could not create project directory '$projPath'");

    $read = readline("Project $projName already exists. Do you want to overwrite? ([Y]/N):");
    if(!in_array(strtolower($read),["y",""]))
        die("Setup canceled\n");


    $cmd .= "/".urlencode(realpath($projPath));
    if(isset($cli_args[1]) && is_file($cli_args[1])) {
        $cmd .= "/".urlencode(realpath($cli_args[1]));
    }
    elseif (is_file(getcwd()."/helper.php")) {
        $cmd .= "/".urlencode(realpath(getcwd()."/helper.php"));
    }
    $cmd = "php ".__DIR__."/public/index.php $cmd";
//    echo  $cmd;
    exec( $cmd,$output,$ret);
//    print_r($output);
    if($ret===0) {
        $cfg = include __DIR__."/application/config/config.php";
        echo "\nAPI successfully created and available at:\n\t {$cfg["base_url"]}/v2/$projName\n\n";
        echo "Config files available at: \n\t$projPath\n\n";
        return;
    }

//    recursive_delete(realpath($projPath));

}


function recursive_delete($path) {
    if(is_dir($path)) {
        $dp = opendir($path);
        while ($fe=readdir($dp)){
            if(in_array($fe,[".",".."]))
                continue;
            recursive_delete($path."/$fe");
        }
        rmdir($path);
        return;
    }
    unlink($path);
}
