<?php

require_once(APPPATH."third_party/Softaccel/Autoloader.php");
\Softaccel\Autoloader::register();

/**
 * Class ConfigGen
 * @property CI_Loader load
 */
class ConfigGen extends CI_Controller
{
    private static $conn = [
        "dsn"=> "",
        "hostname"=> null,
        "username"=> null,
        "password"=> null,
        "database"=> null,
        "dbdriver"=> null,
        "dbprefix"=> "",
        "pconnect"=> false,
        "db_debug"=> true,
        "cache_on"=> false,
        "cachedir"=> "",
        "char_set"=> "utf8",
        "dbcollat"=> "utf8_general_ci",
        "swap_pre"=> "",
        "encrypt"=> false,
        "compress"=> false,
        "stricton"=> false,
        "failover"=> [],
        "save_queries"=> true
    ];

    function cli($hostname,$username,$password,$database){
        $conn = self::$conn;
        $conn["dbdriver"] = 'mysqli';
        $conn["hostname"] = $hostname;
        $conn["username"] = $username;
        $conn["password"] = $password;
        $conn["database"] = $database;

        $db = $this->load->database($conn,true);
        $structure = \Softaccel\Apiator\DBApi\DBWalk::parse_mysql($db,$conn['database']);
        $structure = "<?php\nreturn ".preg_replace(["/\{/","/\}/","/\:/"],["[","]","=>"],json_encode($structure,JSON_PRETTY_PRINT)).";";
        $connection = "<?php\nreturn ".preg_replace(["/\{/","/\}/","/\:/"],["[","]","=>"],json_encode($conn,JSON_PRETTY_PRINT)).";";
        file_put_contents("structure.php",$structure);
        file_put_contents("connection.php",$connection);
    }



    function mysql() {
        $conn = array_merge(self::$conn,$_POST);

        $conn["dbdriver"] = 'mysqli';
        $db = $this->load->database($conn,true);

        $structure = \Softaccel\Apiator\DBApi\DBWalk::parse_mysql($db,$_POST['database']);
        $data['structure'] = "<?php\nreturn ".preg_replace(["/\{/","/\}/","/\:/"],["[","]","=>"],json_encode($structure,JSON_PRETTY_PRINT)).";";
        $data['connection'] = "<?php\nreturn ".preg_replace(["/\{/","/\}/","/\:/"],["[","]","=>"],json_encode($conn,JSON_PRETTY_PRINT)).";";
        $this->load->view("conf_output",$data);
    }

    function index() {
        $this->load->view("dbgen");
    }
}