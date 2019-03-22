<?php

require_once(__DIR__."/../libraries/Response.php");

/**
 * @param int $httpCode
 * @param string $payload
 * @param string $location
 * @param string $encoding
 * @return null
 */
function http_respond($httpCode=200,$payload=null,$location=null,$encoding="application/json") {
    http_response_code($httpCode);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: origin, content-type, accept");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    if($location)
        header("Location: $location");

    if($httpCode!=array("204"))
        header("Content-type: $encoding");

    echo $payload;
    return null;
}



/**
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}



function generate_where_str($where) {
    //print_r($where);
    // if element is not an object or left property of OBJ is not a field -> ignore -> return TRUE
    if(!is_object($where) || !property_exists($where,"left")){
        log_message("debug","invalid filter entry");
        return "TRUE";
    }

    if($where->right=="") return "TRUE";

    $validOps = ["!=","=","<","<=",">",">=","><","~=","!~=","=~","!=~","<>","!><"];

    $where->right = $where->right=="NULL"?null:$where->right;

    switch($where->op) {
        case "><":
            $str = sprintf("%s.%s IN ('%s')",$where->left->alias,$where->left->field,str_replace(";","','",$where->right));
            break;
        case "!><":
            $str = sprintf("%s.%s NOT IN ('%s')",$where->left->alias,$where->left->field,str_replace(";","','",$where->right));
            break;
        case "~=":
            $str = sprintf("%s.%s LIKE ('%%%s')",$where->left->alias,$where->left->field,$where->right);
            break;
        case "!~=":
            $str = sprintf("%s.%s NOT LIKE ('%%%s')",$where->left->alias,$where->left->field,$where->right);
            break;
        case "=~":
            $str = sprintf("%s.%s LIKE ('%s%%')",$where->left->alias,$where->left->field,$where->right);
            break;
        case "!=~":
            $str = sprintf("%s.%s NOT LIKE ('%s%%')",$where->left->alias,$where->left->field,$where->right);
            break;
        case "~=~":
            $str = sprintf("%s.%s LIKE ('%%%s%%')",$where->left->alias,$where->left->field,$where->right);
            break;
        case "!~=~":
            $str = sprintf("%s.%s NOT LIKE ('%%%s%%')",$where->left->alias,$where->left->field,$where->right);
            break;
        default:
            if(in_array($where->op,$validOps))
                $str = sprintf("%s.%s %s %s",$where->left->alias,$where->left->field,$where->op,($where->right!==""?"'".$where->right."'":"NULL"));
            else
                $str = "TRUE";
    }

    return $str;
}

function render_select_query($from, $joins, $whereArr=null, $orderByArr=null, $offset=null, $limit=null,$groupBy=null) {
    // prepare selected fields statement
    $fieldsArr = [];
    $fieldsArr[] = $from["table"].".".implode(",".$from["table"].".",$from["fields"]);


    // prepare JOIN statement
    $joinArr = [];
    foreach ($joins as $fld=>$join) {
        $fieldsArr[] = $join["alias"].".".implode(",".$join["alias"].".",$join["fields"]);
        $joinArr[] = sprintf("LEFT JOIN %s AS %s ON %s=%s",$join["table"], $join["alias"], $join["left"], $join["right"]);
    }

    // prepare where statement
    $sqlWhere = 1;
    if(!empty($whereArr))
        $sqlWhere = implode(" AND ",$whereArr);

    // prepare order statement
    $sqlOrder = "1";
    if(!empty($orderByArr))
        $sqlOrder = implode(", ",$orderByArr);

    // prepare limit statement
    $sqlLimit = "";
    if(is_numeric($offset) && is_numeric($limit))
        $sqlLimit = "LIMIT $offset,$limit";

    $sqlGroup = ($groupBy)?"GROUP BY $groupBy":"";

    $countSql = sprintf("SELECT count(*) as cnt FROM %s %s WHERE %s %s",
        $from["table"],
        implode(" ", $joinArr),
        $sqlWhere,
        $sqlGroup);
    $mainSql = sprintf("SELECT %s FROM %s %s WHERE %s %s ORDER BY %s %s",
        implode(",", $fieldsArr),
        $from["table"],
        implode(" ", $joinArr),
        $sqlWhere,
        $sqlGroup,
        $sqlOrder,
        $sqlLimit
    );

    return array($countSql,$mainSql);
}

/**
 * extracts include from GET
 * @param CI_Input $input
 * @return array
 */
function get_include($input)
{
    if(empty($input->get("include")))
        return [];
    return explode(",",$input->get("include"));
}

/**
 * extracts fields from GET
 * @param CI_Input $input
 * @param string $defaultTable
 * @return array
 */
function get_fields($input,$defaultTable)
{
    $fields = [];
    if(empty($input->get("fields")))
        return $fields;

    $tmp = explode(",",$input->get("fields"));
    for($i=0;$i<count($tmp);$i++) {
        $t = explode(".",$tmp[$i]);
        if(count($t)==1) {
            $fields[$defaultTable][] = $tmp[$i];
        }
        elseif(count($t)==2) {
            $fields[$t[0]][] = $t[1];
        }
    }

    return $fields;
}

/**
 * @param CI_Input $input
 * @param $defaultTable
 * @return array|null
 */
function get_updatefields($input,$defaultTable) {
    if(empty($input->get("update")))
       return null;
    
    $update = [];
    
    foreach(explode(",",$input->get("update")) as $fld) {
        $pair = explode(".",$fld);
        $tbl = count($pair)==1?$defaultTable:$pair[0];
        $fld = count($pair)==1?$pair[0]:$pair[1];
        if(!isset($update[$tbl]))
            $update[$tbl] = [];
        if(!in_array($fld,$update[$tbl]))
            $update[$tbl][] = $fld;
    }
    return $update;
}

/**
 * extracts filters from GET
 * @param CI_Input $input
 * @param string $defaultTable
 * @return array
 */
function get_filters($input,$defaultTable)
{
    $filters = [];
    if(empty($input->get("filter")))
        return $filters;

    // split string by comma and process each segment
    foreach(explode(",",$input->get("filter")) as $idx=>$item) {
        $where = null;
        // regexp search to identify
        preg_match("/([\w\-\$]+)(\.([\w\-\$]+))?(\!?[\=\<\>\~]+)(.*)/",$item,$m);

        if(!empty($m)) {
            $alias = empty($m[3])?$defaultTable:$m[1];
            $filters[] = (object) [
                "left"=>(object) [
                    "alias"=>$alias,
                    "field"=>empty($m[3])?$m[1]:$m[3]
                ],
                "op"=>$m[4],
                "right"=>$m[5]
            ];
        }
    }
    return $filters;
}

/**
 * extracts offset from GET
 * @param CI_Input $input
 * @return int
 */
function get_offset($input,$defaultValue =0)
{
    return empty($input->get("offset"))?$defaultValue:$input->get("offset");
}

/**
 * @param CI_Input $input
 * @param int $defaultValue
 * @return mixed
 */
function get_limit($input,$defaultValue)
{
    return empty($input->get("limit"))?$defaultValue:$input->get("limit");

}

/**
 * @param CI_Input $input
 * @param null $defaultValue
 * @return null
 */
function get_groupby($input,$defaultValue=null) {
    return empty($input->get("groupby"))?$defaultValue:$input->get("groupby");
}

/**
 * @param CI_Input $input
 * @param string $defaultTable
 * @return array
 */
function get_sort($input,$defaultTable)
{

    $sort = [];
    if(empty($input->get("sort")))
        return $sort;

    // generate sort array
    $arr = explode(",",$input->get("sort"));
    foreach($arr as $item) {
        $dir = substr($item,0,1)=="-"?"DESC":"ASC";
        $fld = $dir=="DESC"?substr($item,1):$item;
        $tmp = explode(".",$fld);
        $sort[] = (object) [
            "alias" => count($tmp)>1?$tmp[0]:$defaultTable,
            "fld" => count($tmp)>1?$tmp[1]:$fld,
            "dir" => $dir
        ];
    }
    return $sort;
}

/**
 * @param CI_Input $input
 * @return array
 */
function get_relations($input)
{
    return empty($input->get("relations"))?[]:explode(",",$input->get("relations"));
}


function get_fields_to_update($input,$defaultTableName)
{
    $updateFields = [];

    $fldsFromQuery = explode(",",$input->get("update"));
    for($i=0;$i<count($fldsFromQuery);$i++) {
        $tmp = explode(".", $fldsFromQuery[$i]);
        if(count($tmp)==1) {
            $tbl = $defaultTableName;
            $fld = $tmp[0];
        }
        else
            list($tbl,$fld) = [$tmp];

        if(!$fld)
            continue;

        if(!isset($updateFields[$tbl]))
            $updateFields[$tbl] = [];
        $updateFields[$tbl][] = $fld;
    }
    return $updateFields;
}


/**
 * @param $arr
 * @return mixed
 */
function cleanUpArray($arr) {
    if(is_object($arr))
        $arr = (array) $arr;
    foreach ($arr as $key=>$val) {
        if(is_object($val))
            $val = (array) $val;
        if(is_array($val))
            if(count($val))
                $arr[$key] = cleanUpArray($val);
            else
                unset($arr[$key]);


    }
    return $arr;
}


/**
 * validates $data as a JSON API document
 * @param $data
 * @return Response
 * @throws Exception
 */
function is_valid_post_data($data,$def=null) {
    if(!is_object($data))
        throw new Exception("Input data must be an object: ".json_encode($data),400);
    if(!property_exists($data,"data"))
        throw new Exception("data property missing",400);


    $entries = !is_array($data->data)?[$data->data]:$data->data;
    
    foreach($entries as $entry) {
        $res = is_valid_post_data_entry($entry,$def);
        if($res->code!==200)
            return $res;
    }
    
    return Response::make(true,200,$data);
}

/**
 * validates if $entry is a valid entry structure inside an JSONApi object
 * does not valid contents against the prototype
 * @param object $entry
 * @param object $def
 * @return Response
 */
function is_valid_post_data_entry($entry,$def) {
    if(!is_object($entry))
        return Response::make(false,400,"data entry not an object");
    if(!property_exists($entry,"type"))
        return Response::make(false,400,"entry object missing type property");
        
    if(!property_exists($entry,"attributes")) 
        $entry->attributes = new stdClass();
    if(!is_object($entry->attributes))
        return Response::make(false,400,"entry object attributes property must be an object");
    return Response::make(true,200,$entry);
}

/**
 * generates a cryptographical strong random string
 * @param int $len bytes length
 * @return string
 */
function unique_id($len)
{
    return bin2hex(openssl_random_pseudo_bytes($len));
}