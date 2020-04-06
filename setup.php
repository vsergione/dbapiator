#!/usr/bin/php
<?php
$urlRegex = "/^\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))$/";
//echo preg_match($urlRegex,"https://localhs/asd");
//die();
define("BASEPATH",getcwd());
if(count($argv)===1) {
    echo "Syntax: php setup.php [command] [OPTIONS]\n\n";
    echo "Description: Command line DB Apiator administration utility\n\n";
    echo "Commands:\n";
    echo "    install\n";
    echo "         Installs a DB Apiator on server\n";
    echo "    setupdb\n";
    echo "         Creates a REST API for a given database\n";
    echo "Setupdb Options:\n";
    echo "-c /project_name/db_engine/db_host/db_user/db_pass/db_name\n";
    echo "All configuration parameters are provided directly in the command line\n";
    echo "   - project_name alfanumeric string with no spaces. Used to uniquely identify the database within the current installation\n";
    echo "   - db_engine database engine. Currently only mysqli is supported\n";
    echo "   - db_host IP or hostname of database server. It can also contain the port number in case it differs from default value (eg. localhost:3688)\n" ;
    echo "   - db_user database username\n";
    echo "   - db_pass database password\n";
    echo "   - db_name database name\n\n";
    echo "-i\nInteractive mode.\n\n";
    die();
}

switch($argv[1]) {
    case "install":
        install();
        break;
    case "setupdb":
        array_shift($argv);
        setupdb($argv);
        break;
    default:
        die("invalid command\n\n");
}

function install() {
    echo "DB Apiator install wizzard. Please fill requested info\n";
    $p = "Install path";
    $is_base_dir = is_dir("application");
    if($is_base_dir)
        $p .= " [".getcwd()."]";
    $instPath = readline($p.": ");
    $instPath = $instPath==""?"./":$instPath;
    if(!is_dir($instPath))
        die("Invalid directory: does not exist.\n");

    if(!is_dir($instPath."/application"))
        die("Invalid DB API base directory: should cotain a directory named 'application'\n");
    $instPath = realpath($instPath."/");

    // DB config directory
    $read = readline("Directory path to store project files: ");
    if($read=="")
        die("Invalid directory path: empty not allowed\n");
    $configDir = substr($read,0,1)==="/"?$read:($instPath."/".$read);

    if(!is_dir($configDir)) {
        $read = readline("Directory $configDir does not exist. Do you want to create it? ([Yes]\\Y\\No\\N)");
        $conf = $read===""?"yes":$read;
        if(in_array(strtolower($conf),["yes","y"]))
            if(!mkdir($configDir))
                die("Could not create $configDir");
    }

    $apiatorCfgTpl = $instPath."/application/config/apiator.single.php";
    $apiatorCfgDst = $instPath."/application/config/apiator.php";
    $apiatorCfg = file_get_contents($apiatorCfgTpl);
    if($apiatorCfg===false)
        die("DB Apiator config template not found");
    $apiatorCfg = str_replace("%%conn_dir_path%%",$configDir,$apiatorCfg);
    if(file_put_contents($apiatorCfgDst,$apiatorCfg)===false)
        die("Could not save $apiatorCfgDst config file");



    // base URL
    $ciConfigTpl = $instPath."/application/config/config.tpl.php";
    $ciConfigDst = $instPath."/application/config/config.php";
    $read = readline("Base URL to access DB Apiator: ");
    global $urlRegex;
    if(!preg_match($urlRegex,$read))
        die("Invalid URL.\n");

    $ciConfig = file_get_contents($ciConfigTpl);
    if($ciConfig===false)
        die("CI config template not found");
    $ciConfig = str_replace("%%base_url%%",$read,$ciConfig);

    if(file_put_contents($ciConfigDst,$ciConfig)===false)
        die("Could not save $ciConfigDst config file");

    // next
    $read = readline("Do you want to create REST API? You can always call the script again with command 'setupdb' ([Yes]\\Y\\No\\N)");
    $conf = $read===""?"yes":$read;
    if(in_array(strtolower($conf),["yes","y"]))
        setupdb([null,"-i"]);
}

function setupdb($params) {
    $cmd = null;
    if($params[1]==="-c") {
        $path = @$params[2];
        if(!preg_match("/\/([a-z0-9]+)\/(mysqli)\/([0-9a-z\.\-\_]+(\:[0-9]{2,5})?)\/([a-z][a-z0-9\.\_\-]+)\/(.*)\/([a-z][a-z0-9\.\_\-]+)/",$path,$matches))
            die("Invalid configuration parameters. See help.\n\n");
        $cmd = sprintf("dbapi/ConfigGen/cli/%s/%s/%s/%s/%s",$matches[2],$matches[3],$matches[5],$matches[6],$matches[7]);
        $projName = $matches[1];
    }

    if($params[1]==="-i") {
        echo "Just press enter to select default values\n";
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
    }

    if(!$cmd)
        die("No options provided. Nothing to do.\n\n");

    require "application/config/apiator.php";
    $projPath = CFG_DIR_BASEPATH."/".$projName;

    $isDir = is_dir($projPath);
    if($isDir) {
        $read = readline("Project $projName already exists. Do you want to overwrite? ([Yes]\\Y\\No\\N):");
        $conf = $read===""?"yes":$read;
        if(!in_array(strtolower($conf),["yes","y"]))
            die("Setup canceled\n");
    }
    else {
        if(!mkdir($projPath))
            die("Could not create project directory '$projPath'");
    }
    $cmd .= "/".urlencode(realpath($projPath));
    $cmd = "php public/index.php $cmd";
    exec( $cmd,$output,$ret);

    if($ret===0) {

        require "application/config/config.php";
        //print_r($config);
        echo "API Succefully created and available at:\n\n{$config["base_url"]}/v2/$projName\n\n";
    }
    else {
        if($isDir)
            rmdir(realpath($projPath));
        print_r($output);
    }
}
/*
echo readline("aaa: ")."\n";
echo readline("bbb: ")."\n";
*/