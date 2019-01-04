<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 8/30/18
 * Time: 11:07 AM
 */
require_once(__DIR__."/../libraries/Response.php");


/**
 * sets correct header content type for JSON
 */
function ctype_json() {
    header("Content-type: application/json");
}

/**
 * shortcut to output JSON
 * @param $data
 */
function json_out($data) {
    ctype_json();
    echo json_encode($data);
}

/**
 * sets correct header content type for HTML
 */
function ctype_html() {
    header("Content-type: text/html");
}

/**
 * sets correct header content type for PLAIN TEXT
 */
function ctype_text() {
    header("Content-type: text/plain");
}

/**
 * sets correct header content type for XML
 */
function ctype_xml() {
    header("Content-type: application/xml");
}

/**
 * @param array $errs array of error objects
 * @param int $httpCode http response code to set
 */
function errors_response($errs=null,$httpCode=400) {
    ctype_json();
    http_response_code($httpCode);

    if(!$errs)
        $errs = [["code"=>400,"title"=>"Invalid request"]];

    die(json_encode(["errors"=>$errs]));
}

/**
 * validate if config dir exists and can be accessed for RW
 * if invalid it will reply and error JSON to client with a 500 HTTP CODE
 * @param string $dir directory to check
 * @return bool
 */
function check_valid_dir($dir)
{
    // TODO: check if directory is RW
    if(!is_dir($dir)) {
        http_response_code(500);
        $err = [
            "status" => "500",
            "title" => "Invalid config directory",
            "detail" => "Applications configuration directory is invalid"
        ];
        errors_response([$err]);
    }
    return true;
}


/**
 * Connect to DB and return the DB driver
 * In case of failure it will return an array of errors
 *
 * @param CI_Controller $ci
 * @param array $dbConfig
 * @return Response
 */
function get_db_conn($ci,$dbConfig) {


    /*
     $dsn = sprintf($dbConfig["dbengine"]."://".$dbConfig["username"].":".$dbConfig["password"]
                ."@".$dbConfig["host"]."/".$dbConfig["database"]."?db_debug=false ");
    */


    $dbConfig = array(
        'dsn'	=> '',
        'hostname' => $dbConfig["host"],
        'username' => $dbConfig["username"],
        'password' => $dbConfig["password"],
        'database' => $dbConfig["database"],
        'dbdriver' => $dbConfig["type"],
        'dbprefix' => '',
        'pconnect' => FALSE,
        'db_debug' => false,
        'cache_on' => FALSE,
        'cachedir' => '',
        'char_set' => 'utf8',
        'dbcollat' => 'utf8_general_ci',
        'swap_pre' => '',
        'encrypt' => FALSE,
        'compress' => FALSE,
        'stricton' => FALSE,
        'failover' => array(),
        'save_queries' => TRUE
    );

    /** @var CI_DB_driver $db */
    $db = @$ci->load->database($dbConfig,TRUE);
    $connErr = $db->error();

    if($connErr["code"]!==0)
        return Response::make(false,500,[["code" => $connErr["code"], "title" => $connErr["message"]]]);

    return Response::make(true,200,$db);
}

/**
 * wrapper for generating DB structure
 * @param string $type
 * @param string $dbName
 * @param CI_DB_pdo_driver $dbDriver
 * @return Response
 */
function generate_config($type,$dbName,&$dbDriver) {
    switch ($type) {
        case "mysqli":
            return Response::make(true,200,generate_mysql_config($dbName,$dbDriver));
        default:
            return Response::make(false,200,[["title"=>"Invalid DB engine",
                "details"=>"Selected DB Engine '$type' is not supported"]]);
    }

}

/**
 * generates configuration structure of a MySQL Database
 * @param $dbName
 * @param CI_DB_pdo_driver $db
 * @return array
 */
function generate_mysql_config($dbName,&$db) {

    // load DB config template
    $tables = [];

    // fetch table fields
    $sql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE TABLE_SCHEMA='$dbName'";
    $res = $db->query($sql)->result();

    foreach($res as $item) {
        // create table entry when not yet defined
        if(!array_key_exists($item->TABLE_NAME,$tables))
            $tables[$item->TABLE_NAME] = [
                "fields"=>[],
                "type"=>"table",
                "update"=>true,
                "delete"=>true,
                "create"=>true,
                "read"=>true,
                "keyFld"=>null
            ];

        // populate fields
        $tables[$item->TABLE_NAME]["fields"][$item->COLUMN_NAME] = [
            "type" => parseType($item->COLUMN_TYPE),
            "insert" => $item->EXTRA=="auto_increment"?false:true,
            "update" => $item->EXTRA=="auto_increment"?false:true,
            "select" => true,
            "iskey" => in_array($item->COLUMN_KEY, ["PRI","UNI"]),
            "required" => !($item->IS_NULLABLE=="YES" ||  $item->EXTRA=="auto_increment" || $item->COLUMN_DEFAULT),
            "default" => $item->COLUMN_DEFAULT,
        ];
        if($item->COLUMN_KEY==="PRI")
            $tables[$item->TABLE_NAME]["keyFld"] = $item->COLUMN_NAME;
        if($item->COLUMN_KEY==="UNI" && !$tables[$item->TABLE_NAME]["keyFld"])
            $tables[$item->TABLE_NAME]["keyFld"] = $item->COLUMN_NAME;
    }

    // fetch foreign keys
    $sql = "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                            FROM information_schema.KEY_COLUMN_USAGE
                            WHERE TABLE_SCHEMA='$dbName' and REFERENCED_TABLE_SCHEMA='$dbName';";
    $fKeys = $db->query($sql)->result();
    foreach ($fKeys as $fk) {
        $srcTable = $fk->TABLE_NAME;
        $srcFld = $fk->COLUMN_NAME;
        $tgtTable = $fk->REFERENCED_TABLE_NAME;
        $tgtFld = $fk->REFERENCED_COLUMN_NAME;
        $tables[$srcTable]["fields"][$srcFld]["foreignKey"] = [
            "table" => $tgtTable,
            "field" => $tgtFld
        ];
        if(!in_array("referencedBy",$tables[$tgtTable]["fields"][$tgtFld]))
            $tables[$tgtTable]["fields"][$tgtFld]["referencedBy"] = [];
        $tables[$tgtTable]["fields"][$tgtFld]["referencedBy"][] =  [
            "table" => $srcTable,
            "field" => $srcFld
        ];
    }
    //echo json_encode($tables)."\n\n\n\n";

    foreach($tables as $tableName=>$table) {
        $fkFlds = array();
        foreach($table["fields"] as $fldName=>$fldSpec) {
            if(array_key_exists("foreignKey",$fldSpec)) {
                $fKey = $fldSpec["foreignKey"];
                if(($fKey["table"]."_".$fKey["field"])==$fldName)
                    $fkFlds[] = [$fldName,$fKey];
            }
        }
        if(count($fkFlds)<2)
            continue;

        for($i=0;$i<count($fkFlds);$i++) {
            for($j=0;$j<count($fkFlds);$j++) {
                $sTbl = $fkFlds[$i][1]["table"];
                if($sTbl==$fkFlds[$j][1]["table"])
                    continue;
                $tTbl = $fkFlds[$j][1]["table"];
                if(!array_key_exists("relations",$tables[$sTbl])) {
                    $tables[$sTbl]["relations"] = [];
                }

                $relType = $tables[$tableName]["fields"][$sTbl."_id"]["iskey"]?"1:1":"1:n";
                $tables[$sTbl]["relations"][$tTbl] = [
                    "table"=>$tTbl,
                    "lnkTable"=>$tableName,
                    "relType"=>$relType,
                    "sourceIdMapFld"=>$sTbl."_id",
                    "targetIdMapFld"=>$tTbl."_id"
                ];

            }
        }
    }

    // fetch views
    $sql = "SHOW FULL TABLES IN `$dbName` WHERE TABLE_TYPE LIKE 'VIEW'";
    $views = [];
    $query = $db->query($sql);

    $viewsRecs = $query->result();
    foreach ($viewsRecs as $rec) {
        $propName = "Tables_in_$dbName";
        array_push($views,$rec->$propName);
        $tables[$rec->$propName] = ["fields"=>[],"type"=>"view"];
        $res = $db->query("SHOW COLUMNS FROM ".$rec->$propName);
        //echo $db->last_query();
        $fields = $res->result();
        foreach ($fields as $fld) {
            $tables[$rec->$propName]["fields"][$fld->Field] = [
                "type" => parseType($fld->Type),
                "hidden"=>false,
                "allowinsert" => false,
                "allowupdate" => false,
                "iskey" => in_array($fld->Key, ["PRI","UNI"]),
                "required" => ((in_array($fld->Key, ["PRI","UNI"]) || $fld->Null=="NO") && $fld->Extra!="auto_increment")?true:false,
                "default" => $fld->Default,
            ];
        }
    }

    return $tables;
}

function strip_quotes($str) {
    return str_replace(array('"', "'"), '', $str);
}

function parseType($str) {
    preg_match("/([a-z]+)(\(([a-z0-9\*\, \']+)\))?/i",$str,$m);
    //echo $str."\n";
    //print_r($m);
    if($m[1]=="set") {
        $quotes = explode(",",$m[3]);
        return array("proto"=>$m[1],"vals"=>array_map("strip_quotes",$quotes));
    }
    return count($m)>2?array("proto"=>$m[1],"length"=>$m[3]):array("proto"=>$m[1]);
}

