<?php
namespace Apiator\DBApi;



//require_once(__DIR__."/../../../libraries/Response.php");


require_once(__DIR__."/../../../libraries/Errors.php");
require_once(__DIR__.'/../../../libraries/RecordSet.php');

require_once (APPPATH."/third_party/JSONApi/Autoloader.php");

use http\Exception;
use JSONApi\Autoloader;
Autoloader::register();

use JSONApi\Document;

//require_once(__DIR__."/../../../libraries/HttpResp.php");

/**
 * Class Records
 * core class for manipulating the records in the Database
 */
class Records {

    private $debug = true;
    /**
     * @var Datamodel $dm
     */
    private $dm;

    /**
     * @var \CI_DB_query_builder $dbdrv
     */
    private $dbdrv;
    private $maxNoRels=10;
    private $configDir;

    /**
     * Records constructor.
     * @param \CI_DB_query_builder $dbDriver
     * @param Datamodel $dataModel
     */
    function __construct($dbDriver,$dataModel) {
        $this->dm = $dataModel;
        $this->dbdrv = $dbDriver;
        $instance = get_instance();
        $this->maxNoRels = $instance->config->item("inbound_relationships_page_size");
        $this->configDir = $instance->config->item("api_config_dir");
    }

    static function init($dbDriver,$dataModel) {
        return new Records($dbDriver,$dataModel);
    }


    /**
     * it parses the includes into a relations tree, where each node contains:
     * - name: name of the table
     * - alias: alias for the table
     * - lnkFld (optional): field indicated by the parent FK. To be used in JOIN definition
     * - fields: table fields (retrieved from DB data model which is !important! to be accurate)
     * - select: fields to be included in the SELECT part (defaults to *)
     * - includes: hash array of tables to be joined, where key is an FK field pointing to the related table
     * - join: FK field of parent to be used in LEFT JOIN...ON expression, in tableAlias.fieldName format
     * - keyFld: primary key field
     * - noFlds: number of fields to be included in the result (count(select) when select!=* or count(fields) otherwise
     * - start: column position in result row where current table begins
     * -
     *
     * @param string $top
     * @param string $includeStr string of comma separated includes
     * @param array $fields
     * @return array
     * @throws \Exception
     */
    private function generateSqlParts($top, &$includes, array $fields)
    {
        sort($includes);
        for($i=0;$i<count($includes);$i++) {
            if(is_string($includes[$i]))
                $includes[$i] = explode(".",$includes[$i]);
        }


        // init relationTree with top element
        try {
            $relationTree = [
                $top => [
                    "name" => $top,
                    "alias" => $top,
                    "fields" => $this->dm->get_selectable_fields($top),
                    "fks"=>$this->dm->get_fk_fields($top),
                    "select" => [],
                    "includes" => [],
                    "inbound" => $this->dm->get_inbound_relations($top)
                ]
            ];

            if(isset($fields[$top])) {
                $relationTree[$top]["select"] = $fields[$top];

                foreach (array_keys($relationTree[$top]["inbound"]) as $relName) {
                    if(!in_array($relName,$fields[$top]))
                        unset($relationTree[$top]["inbound"][$relName]);
                }
            }
        }
        catch (\Exception $exception) {
            throw new \Exception("Base table $top is invalid",400,$exception);
        }


        // populate relationTree tree by processing includes
        $newInclude = [];
        foreach($includes  as $include) {
            if(!count($include))
                continue;
            $this->processIncludes($relationTree[$top],$include,$fields);
            if(count($include))
                $newInclude[] = $include;
        }
        $includes = $newInclude;



        $select = [];
        $join = [];
        $this->prepareQuery($relationTree[$top],0,$select,$join);


        $select = implode(", ",array_map(function($sel){
            return implode(", ",$sel);
        },$select));
        $join = implode(" \n",$join);
        //echo  $join;

        return [$select,$join,$relationTree];
    }


    /**
     * generate include tree to be used for generating the SELECT clause
     *
     * @param array $parent
     * @param array $include include path as array, where lower indices are parents of higher ones
     * @param $fields
     * @throws \Exception
     */
    private function processIncludes(&$parent, &$include, $fields)
    {
        $parent_table = $parent["name"];
        $parent_alias = $parent["alias"];

        // extract current level (first element of the array)
        $relation_name = array_shift($include);

        // generate alias
        $alias = $parent_alias."_".$relation_name;

        // retrieves relation between parent and current level
        // throws exception. caught lower and ignored
        try {
            $fkRel = $this->dm->get_outbound_relation($parent_table, $relation_name);
            //echo $relation_name;
            //print_r($fkRel);
            $parent_fk_field = $fkRel["fkfield"];
            $related_table = $fkRel["table"];
            $related_table_field = $fkRel["field"];

            // check if joined resource already there and if not create it
            if(!array_key_exists($relation_name,$parent["includes"])) {
                $parent["includes"][$relation_name] = [
                    "name" => $related_table,
                    "alias" => $alias,
                    "lnkFld" => $related_table_field,
                    "fields" => $this->dm->get_selectable_fields($related_table),
                    "fks" => $this->dm->get_fk_fields($related_table),
                    "inbound" => $this->dm->get_inbound_relations($related_table),
                    "select" => [],
                    "includes" => [],
                    "join" => $parent_alias . "." . $parent_fk_field,
                    "type" => "1:1",
                    "parent" => &$parent
                ];

                //print_r($parent["includes"][$relation_name]);

                // if there is a fields selection for current node set it for select
                if(isset($fields[$related_table])) {
                    $parent["includes"][$relation_name]["select"] = $fields[$related_table];
                    foreach (array_keys($parent["includes"][$relation_name]["inbound"]) as $relation_name) {
                        if(!in_array($relation_name,$fields[$related_table]))
                            unset($parent["includes"][$relation_name]["inbound"][$relation_name]);
                    }
                }
            }

            if(!empty($parent["select"]) && !in_array($relation_name,$parent["select"])) {
                $parent["select"][] = $relation_name;
            }

            if(count($include))
                $this->processIncludes($parent["includes"][$relation_name],$include,$fields);
        }
        catch (\Exception $exception) {
            //print_r($exception);
        }

        if($inboundRel = $this->dm->get_inbound_relation($parent_table, $relation_name)) {
            //print_r($inboundRel);
            if(!array_key_exists($relation_name,$parent["includes"])) {
                $parent["includes"][$relation_name] = [
                        "type"=>"1:n",
                        "table"=>$inboundRel["table"],
                        "field"=>$inboundRel["field"],
                        "parent"=>&$parent
                    ];
            }
        }

    }

    /**
     * parses
     * @param $node
     * @param $start
     * @param $select
     * @param $join
     * @return int
     */
    private function prepareQuery(&$node, $start, &$select, &$join)
    {
        $id = $this->dm->getPrimaryKey($node["name"]);

        $node["keyFld"] = $id;
        $node["offset"] = $start;

        if(empty($node["select"])) {
            $node["select"] = $node["fields"];
        }

        if($id)
            $node["select"][]=$id;


        $node["select"] = array_unique($node["select"]);
        $node["noFlds"] = count($node["select"]);

        // extract ID field position in the fields array
        $node["keyFldPos"] = $node["keyFld"]?array_search($node["keyFld"],$node["select"]):null;
        $node["keyFldPos"] = $node["keyFldPos"]===false?null:$node["keyFldPos"];


        // define currAlias global to be able to refer to it from inside the callback function
        global $currAlias;
        $currAlias = $node["alias"];

        // add alias prefix to field selection using an array_map and an anonymous function as callback
        $select[] = array_map(function($item){
            global $currAlias;
            return $currAlias.".$item";
        },$node["select"]);

        // generate LEFT JOIN for joined tables
        if(isset($node["join"]))
            $join[] = sprintf("\nLEFT JOIN %s AS %s ON %s=%s.%s",
                $node["name"],$node["alias"],$node["join"],$node["alias"],$node["lnkFld"]);

        $start += $node["noFlds"];
        foreach (array_keys($node["includes"]) as $key) {
            if($node["includes"][$key]["type"]=="1:1")
                $start = $this->prepareQuery($node["includes"][$key],$start,$select,$join);
        }


        return $start;
    }

    /**
     * @param $node
     * @param $row
     * @param $allRecs
     * @return null|\stdClass
     * @throws \Exception
     */
    private function parseResultRow($node, $row, &$allRecs,$options=[])
    {
//        print_r($row);
        $rec = null;
        $recId = null;
        if(!empty($node["keyFld"])) {
            // extract record ID from row
            $idFldPosition = $node["keyFldPos"]+$node["offset"];
            $recId = $row[$idFldPosition];

            // retrieve object from allRecs array (if already there)
            $objIdx = $node["name"] . "_" . $recId;
            if(isset($allRecs[$objIdx])) {
                $rec = &$allRecs[$objIdx];
            }
        }


        if(is_null($rec)){
            // record not yet saved in allRecs -> need to extract it

            $attributes = new \stdClass();

            $relationships = [];

            // parse record and populate attributes or relationship
            for ($i = 0; $i < $node["noFlds"]; $i++) {
                $fieldName = $node["select"][$i];
                if(isset($node["fks"][$fieldName])) {
                    $relationships[$fieldName] = (object)[
                        "data"=>null,
                        "type"=>"object"
                    ];
                    $fkId = $row[$node["offset"] + $i];
                    if(!is_null($fkId))
                        $relationships[$fieldName]->data = (object) [
                            "id"=>$fkId,
                            "type"=>$node["fks"][$fieldName]["table"]
                        ];

                }
                else {
                    $attributes->$fieldName = $row[$node["offset"] + $i];
                }
            }

            $rec = new \stdClass();
            $rec->id = $recId;
            $rec->type = $node["name"];
            $rec->attributes = $attributes;

            if(count($relationships))
                $rec->relationships = (object) $relationships;
            elseif(count($node["inbound"]))
                $rec->relationships = new \stdClass();

            // add inbound
            foreach ($node["inbound"] as $label=>$spec) {
                $rec->relationships->$label = (object)[
                    "type"=>"array"
                ];
            }


            if(isset($objIdx))
                $allRecs[$objIdx] = $rec;
        }

        if(!isset($node["includes"]) || count($node["includes"])==0)
            return $rec;

        foreach($node["includes"] as $fk=>$incNode) {
            if(!isset($rec->relationships->$fk))
                continue;
            if(is_null($rec->relationships->$fk))
                continue;
            if($incNode["type"]=="1:1") {
                $inboundRelation = $this->parseResultRow($incNode, $row, $allRecs);
                $rec->relationships->$fk = (object) [
                        "data"=>$inboundRelation,
                        "type"=>"object"
                    ];
            }

            if($incNode["type"]=="1:n") {
                $filterStr = $incNode["field"]."=".$rec->id;
                $filter = get_filter($filterStr,$incNode["table"]);

                $rec->relationships->$fk = (object)[
                    "data"=>[],
                    "total"=>0,
                    "type"=>"array"

                ];
                $options = (array)$options;
                $options["filter"] = $filter;
                $options["limit"] = $this->maxNoRels;
                list($rec->relationships->$fk->data,$rec->relationships->$fk->total) = $this->getRecords($incNode["table"],$options);
            }
        }
        return $rec;
    }

    function getRecordById($table,$idFld,$recId,$includes=null) {
        $opts = [
            "filter"=>[
                "$table.$idFld"=>
                    (object)[
                        "left"=>(object) [
                            "alias"=>"$table",
                            "field"=>"$idFld"
                        ],
                        "op"=>"=",
                        "right"=>$recId
                    ]
            ]
        ];
        if($includes) {
            $opts["includeStr"] = $includes;
        }
        list($recs,$total) = $this->getRecords($table,$opts);
        if(count($recs))
            return $recs[0];

        return null;
    }
    /**
     * @param $filters
     * @param $resName
     * @return int|string
     * @throws \Exception
     */
    private function generateWhereSQL($filters, $resName)
    {
        $whereArr = [];
        foreach ($filters as $filter) {
            if ($filter->left->alias == $resName
                && $this->dm->field_is_searchable($resName, $filter->left->field)) {
                $whereArr[] = generate_where_str($filter);
            }
        }
        return count($whereArr) ? implode(" AND ", $whereArr) : 1;
    }

    /**
     * @param $order
     * @param $resName
     * @return string
     * @throws \Exception
     */
    private function generateSortSQL($order, $resName)
    {
        $orderByArr = [];
        foreach ($order as $item) {
            if($item->alias!==$resName)
                continue;
            if(!$this->dm->field_is_sortable($item->alias,$item->fld))
                continue;

            $orderByArr[] = sprintf("%s.%s %s",$resName,$item->fld,$item->dir);
        }
        return count($orderByArr)?implode(", ",$orderByArr):1;
    }

    /**
     * Retrieves records from a database.
     *
     * Inner workings:
     * 1. basic checks (resource exists & is readable)
     * 2. prepare query parameters
     * 3. find out total number of matched records
     * 4. prepare fields selection
     * 5. generate SQL parts
     * 6. create ORDER BY
     * 7. compile SELECT statement
     * 8. run query
     * 9. parse result
     *
     * @param string $resourceName
     * @param array $opts [
            * "includeStr" => "",
            * "fields" => [],
            * "filters"=>[],
            * "offset"=>0,
            * "limit"=>0,
            * "order"=>[]
        * ]
     * @return array
     * @throws \Exception
     * @todo: check filtering
     *
     */
    function getRecords($resourceName, $opts=[])
    {

        // check if resource exists
        if(!$this->dm->resource_exists($resourceName))
            throw new \Exception("Resource '$resourceName' not found",404);

        // check if client is authorized
        if(!$this->dm->resource_allow_read($resourceName))
            throw new \Exception("Not authorized to read from '$resourceName'",403);

        $cfg = $this->dm->get_config($resourceName);
        $tableName = isset($cfg["name"])?$cfg["name"]:$resourceName;

        $cfgLimit = get_instance()->config->item("default_page_size_limit");
        if(!intval($cfgLimit)) {
            log_message('error','Invalid default_page_size_limit');
            $cfgLimit = 10;
        }

        // prepare parameters
        $defaultOpts = [
            "includeStr" => [],
            "fields" => [],
            "filter"=>[],
            "offset"=>0,
            "limit"=>null,
            "order"=>[]
        ];

        $opts = array_merge($defaultOpts,$opts);
        $opts['limit'] = $opts['limit']?$opts['limit']:$cfgLimit;

        if(!array_key_exists("custom_where",$opts)) {
            $whereStr = $this->generateWhereSQL($opts['filter'],$tableName);
        }
        else {
            $whereStr = $opts['custom_where'];
        }


        // extract total number of records matched by the query
        $countSql = "SELECT count(*) as `cnt` FROM `$tableName` WHERE $whereStr";
        $totalRecs = $this->dbdrv->query($countSql)->row()->cnt;
//        echo $countSql;
        // return if no records matched
        if($totalRecs==0) return [[],0];

        // prepare field selection (validate and ....
        foreach ($opts['fields'] as $res=>$fldsStr) {
            $opts['fields'][$res] = [];
            $tmp = explode(",",$fldsStr);
            foreach ($tmp as $fld) {
                if($this->dm->is_valid_field($res,$fld))
                    $opts->fields[$res][] = $fld;
                else
                    throw new \Exception("Invalid field name $res.$fld",401);
            }
            if(empty($opts['fields'][$res]))
                unset($opts['fields'][$res]);
        }

        // generate SQL parts & relation tree
        if(is_string($opts['includeStr'])) {
            $includeStr = trim($opts['includeStr']);
            $opts['includeStr'] = $includeStr===""?[]:explode(",",$includeStr);
        }

        $ttt = $this->generateSqlParts($tableName,$opts['includeStr'],$opts['fields']);
        list($select,$join,$relTree) = $ttt;
//        echo "\n\n$tableName reltree\n";
//        print_r($relTree);
        // prepare ORDER BY part
        $orderStr = $this->generateSortSQL($opts['order'],$tableName);

        // compile SELECT
        $mainSql = "SELECT $select FROM `{$relTree[$tableName]["name"]}` AS `{$relTree[$tableName]["alias"]}` "
            .($join!==""?$join:"")
            ." WHERE $whereStr"
            ." ORDER BY $orderStr"
            ." LIMIT {$opts['offset']}, {$opts['limit']}";
         //echo $mainSql."\n";


        // run query
        /** @var \CI_DB_result $res */
        $res = $this->dbdrv->query($mainSql);
        //get_instance()->debug_log($mainSql);

        $rows = $res->result_array_num();

        $recordSet = [];
        // parse result
        $allRecs = [];
        foreach ($rows as $row) {
            $newRec = $this->parseResultRow($relTree[$resourceName],$row,$allRecs,$opts);
            $recordSet[] = $newRec;
        }


        return [$recordSet,$totalRecs];
    }

     /**
     * @param $resName
     * @param $attributes
     * @param string $operation
     * @return mixed
     * TODO: review this method
     * @throws \Exception
     */
    private function validateInsertData($resName, $attributes) {
        $attributesNames = array_keys($attributes);

        foreach($this->dm->getResourceFields($resName) as $fldName=> $fldSpec) {
            if($fldSpec["required"] && is_null($fldSpec["default"]) && !in_array($fldName,$attributesNames))
                throw new \Exception("Required attribute '$fldName' of '$resName' not provided",400);

            // field not allowed to insert
            if(!$this->dm->field_is_insertable($resName,$fldName) && in_array($fldName,$attributesNames))
                throw new \Exception("Attribute '$resName/$fldName' not allowed to be inserted",400);
        }

        foreach($attributes as $attrName=> $attrVal) {
            $attrVal = $this->dm->is_valid_value($resName,$attrName,$attrVal);

            /**
             * TODO: instead of just checking if value is an object as exception when value type validation fails
             *       implement a proper mechanism inside the is_valid_value method
             */
            if(!is_object($attrVal))
                $attributes[$attrName] = $attrVal;
        }
        return $attributes;
    }

    /**
     * create new Record
     * TODO: clarify best approach about what to return after inserting OK....
     *
     * @param $table
     * @param object $data data to be inserted
     * @param $watchDog
     * @param string $onDuplicate behaviour flags
     * @param String[] $fieldsToUpdate
     * @param $path
     * @param $includes
     * @return \Response
     * @throws \Exception
     */
    function insert($table, $data, $watchDog, $onDuplicate, $fieldsToUpdate, $path, &$includes) {
        if($watchDog==0)
            throw new \Exception("Maximum recursion level has been reached. Aborting. Please review your nested data.",400);

        //$table = $data->type;
        if($data->type!=$table)
            throw new \Exception("Invalid data type '$data->type' '$table'",400);

        // check if resource exists
        if(!$this->dm->resource_exists($table))
            throw new \Exception("Resource '$table'' not found",404);

        // check if client is authorized to insert into resource
        if(!$this->dm->resource_allow_insert($table))
            throw new \Exception("Not authorized to insert into resource '$table'",401);



        // validate attributes
        $attributes = $data->attributes;
        $relations = isset($data->relationships)?$data->relationships:[];
        $idFld = $this->dm->getPrimaryKey($table);

        $insertData = [];
        if(isset($data->id)) {
            $insertData[$idFld] = $data->id;
        }

        // collect attributes
        foreach($attributes as $fldName=>$value) {
            if(!$this->dm->field_is_insertable($table,$fldName))
                throw new \Exception("Not allowed to insert data in field '$fldName' of table '$table'",400);
            $insertData[$fldName] = $value;
        }

        $one2nRelations = [];
        // iterate relations and insert recursive if it is the case
        foreach ($relations as $relName=>$relData) {
            // gets relationship config. Throws an error when relation is not valid
            $relSpec = $this->dm->get_relationship($table, $relName);

            // todo: implement full validation in input_validator and remove the code bellow
            if(!is_object($relData))
                throw new \Exception("Invalid relationship '$relName' data: invalid format ",400);

            if(!isset($relData->data))
                throw new \Exception("Invalid relationship '$relName' data: invalid format",400);

            // relation type vs data type: object for outbound relations; array for inbound relations
            if ($relSpec["type"]=="inbound" && !is_array($relData->data))
                throw new \Exception("Invalid 1:n relation '$relName' for '$table'",400);

            if ($relSpec["type"]=="outbound" && !is_object($relData->data) )
                throw new \Exception("Invalid 1:1 relation '$relName' for '$table'",400);


            // inbound relation (1:n) add to stack for later insert
            if(is_array($relData->data) && $relSpec["type"]=="inbound") {
                $one2nRelations[$relName] = [
                    "data"=>$relData->data,
                    "spec"=>$relSpec
                ];
                continue;
            }

            //////////////////////////////////////////////////
            // continue with outbound relation (1:1) processing
            // validate object structure
            ///////////////////////////////////////////////////

            if(!$this->dm->is_valid_field($table,$relName))
                throw new \Exception("Invalid 1:1 relation '$relName' for '$table'",400);

            if(!isset($relData->data->type))
                throw new \Exception("Invalid relationship data: missing '$relName' type",400);

            $fk = (object)$this->dm->get_outbound_relation($table,$relName);

            // todo: data obfuscation ...........
            if($fk->table!==$relData->data->type)
                throw new \Exception("Invalid relationship data: invalid type for relationship '$relName'",400);

            $newPath = $path==null?$relName:$path.".$relName";
            if(isset($relData->data->id)) {
                // related record exists already; just set the id and continue
                // still... it does not check if it actually exists... but on insert it will throw an error if ID is fake
                if(!in_array($newPath,$includes))
                    $includes[] = $newPath;
                $insertData[$relName] = $relData->data->id;
                continue;
            }
            // create 1:1 related record
            if(isset($relData->data->attributes)) {

                $insertData[$relName] = $this->insert($fk->table,$relData->data,$watchDog-1,$onDuplicate,$fieldsToUpdate,$newPath,$includes);
                if(!in_array($newPath,$includes))
                    $includes[] = $newPath;
            }
        }

        $insertData = $this->validateInsertData($table,$insertData);

        // call oninsert hook
        $tableConfig = $this->dm->get_config($table);
        if(isset($tableConfig["oninsert"]) && is_callable($tableConfig["oninsert"])) {
            $insertData = $tableConfig["oninsert"]($insertData,$tableConfig);
        }


        // before insert hook
        $beforeInsert = @include($this->configDir."/hooks/".$table."/before.insert.php");
        if(is_callable($beforeInsert))
            $insertData = $beforeInsert($this,$insertData);


        // check insert data for non-scalar values and throw error in case found
        foreach ($insertData as $key=>$value) {
            if($value!==null && !is_scalar($value)) {
                throw new \Exception("Invalid value for $key: ".json_encode($value));
            }
            $this->dbdrv->set($key,$value);
        }

        $insSql = $this->dbdrv->get_compiled_insert($table);

        // todo: should put this in an external file: configure behaviour to update fields (database specific)
        switch ($onDuplicate) {
            case "update":
                if (empty($fieldsToUpdate[$table]))
                    break;

                $updStr = [];
                foreach ($fieldsToUpdate[$table] as $fld) {
                    if (!$this->dm->field_is_updateable($table, $fld)) {
                        throw new \Exception("ON DUPLICATE UPDATE failure: not allowed to update field '$fld'", 400);
                    }
                    $updStr[] = "$fld=VALUES($fld)";
                }

                if(count($updStr))
                    $insSql .= " ON DUPLICATE KEY UPDATE " . implode(",", $updStr);
                break;
            case "ignore":
                if($idFld)
                    $insSql .= " ON DUPLICATE KEY UPDATE $idFld=$idFld";
                break;
            case "error":
                break;
            default:
                throw new \Exception("Invalid 'onduplicate' parameter value.");
        }

        // insert data in DB

        $this->dbdrv->db_debug = false;

        $res = $this->dbdrv->query($insSql);

//        /**
//         * @var \Dbapi $ci
//         *
//         */
//        $ci = get_instance();

        if(!$res) {
            // todo: log message to the app log file
            $sqlErr = $this->dbdrv->error();
            log_message("error",$sqlErr["message"]." > $insSql");
            throw new \Exception($sqlErr["message"], 500);
        }

        // retrieve resource ID (mysql specific)



        // todo: evaluate impact for other DB engines and implement
        $newRecId = $this->dbdrv->insert_id();
        if(!$newRecId && $this->dbdrv->affected_rows() && is_scalar($insertData[$idFld]))
            $newRecId = $insertData[$idFld];

        if(!$newRecId) {
            $selSql = $this->dbdrv
                ->where($insertData)
                ->get_compiled_select($table);
            //print_r($selSql);

            $q = $this->dbdrv->query($selSql);
            get_instance()->debug_log($selSql);
            $cnt = $q->num_rows();

            if($cnt > 1) {
                log_message("error", "More then one records returned on Insert new record: $insSql / $selSql");
                return null;
            }

            $newRecId = $q->row()->$idFld;
        }

        $afterInsert = @include($this->configDir."/hooks/".$table."/after.insert.php");
        if(is_callable($afterInsert))
            $afterInsert($this,$newRecId,$insertData);

        // create outbound relations
        if($newRecId && $one2nRelations) {
            foreach ($one2nRelations as $relName=>$relData) {
                $relSpec = $relData["spec"];
                $rels = $relData["data"];

                $newPath = $path==null ? $relName : "$path.$relName";

                // iterate through data
                foreach ($rels as $rel){
                    // check relation data type
                    $objType = $this->get_object_type($rel);
                    $fkFld = $relSpec["field"];
                    switch ($objType) {
                        // data is a resource indicator object = related record exist already
                        // => perform an update with the id of the newly created object for the FK field
                        case "ResourceIndicatorObject":
                            // update related record
                            if($this->dm->resource_allow_update($relSpec["table"]))
                                throw new \Exception("Not allowed to update relationship of type $relName 1",403);
                            $rel->attributes = (object) [
                                $relSpec["field"]=>$newRecId
                            ];
                            if(!in_array($newPath,$includes))
                                $includes[] = $newPath;
                            $this->updateRecord($relSpec["table"],$rel);
                            break;
                            // data is of newResourceObject type => new related record must be created
                        case "newResourceObject":
                            // insert new related record
                            if(!$this->dm->resource_allow_insert($relSpec["table"]))
                                throw new \Exception("Not allowed to update relationship of type $relName 2",403);
                            if(!in_array($newPath,$includes))
                                $includes[] = $newPath;
                            $rel->attributes->$fkFld = $newRecId;

                            $this->insert($relSpec["table"],$rel,$watchDog-1,$onDuplicate,$fieldsToUpdate,$newPath,$includes);
                            break;
                        default:
                            throw new \Exception("Invalid '$relName' relationship data type ($objType) : ".json_encode($rel),403);
                    }
                }
            }
        }

        return $newRecId;
    }

    /**
     * @param $table
     * @param $resource
     * @return string
     * @throws \Exception
     */
    function updateRecord($table, $resource) {
        return $this->updateById($table,$resource->id,$resource);
    }

    function updateByFilter($table,$data,$filter) {

    }

    /**
     * @param string $table
     * @param string $id
     * @param array $attributes
     * @return string mixed
     * @throws \Exception
     */
    private function updateAttributes($table, $id, $attributes)
    {
        $priKey = $this->dm->getPrimaryKey($table);

        $keyFields = $this->dm->get_key_flds($table);

        $whereArr = array();

        // check for duplicates on key fields
        // @todo: contemplate if this code is really needed. Maybe a simple capture of the mysql error should do the job
        // build where part with key fields
        foreach($attributes as $name=>$value) {
            if(in_array($name,$keyFields)) {
                $whereArr[] = "$name='$value'";
            }
        }
        // run the query
        if(count($whereArr)) {
            $sql = "SELECT * FROM $table WHERE $priKey!='$id' AND (".implode(" OR ",$whereArr).")";

            if($this->dbdrv->query($sql)->num_rows()) {
                throw new \Exception("Duplicate key fields",409);
            }
        }

        // before insert hook
        $beforeUpdate = @include($this->configDir."/hooks/".$table."/before.update.php");
        if(is_callable($beforeUpdate))
            $attributes = $beforeUpdate($this,$id,$attributes);

        // configure query
        $sql = $this->dbdrv
            ->where($this->dm->getPrimaryKey($table),$id)
            ->set($attributes)
            ->get_compiled_update($table);

        // perform update
        $this->dbdrv->query($sql);

        // before insert hook
        $afterUpdate = @include($this->configDir."/hooks/".$table."/after.update.php");
        if(is_callable($afterUpdate))
            $afterUpdate($this,$id,$attributes);

        if(isset($attributes[$priKey]))
            return $attributes[$priKey];
        return $id;

    }

    /**
     * @param string $table
     * @param string $id
     * @param array $relationships
     */
    function updateRelations($table, $id, $relationships) {

    }

    /**
     * update Record
     * @param $table
     * @param $id
     * @param $resource
     * @return string
     * @throws \Exception
     */
    function updateById($table, $id, $resource) {
        if(!$this->dm->getPrimaryKey($table))
            throw new \Exception("Update by ID not allowed: table '$table' does not have primary key/unique field.",500);

        // extract 1:1 relation data and insert
        if(isset($resource->relationships)) {
            foreach ($resource->relationships as $relName => $relData) {
                $relSpec = $this->dm->get_relation_config($table, $relName);

                if ($relSpec["type"] !== "outbound")
                    continue;

                if (!isset($resource->attributes))
                    $resource->attributes = new \stdClass();

                if(isset($relData->id) && $relData->id!==null) {
                    $resource->attributes->$relName = $relData->id;
                    continue;
                }

                if(!isset($relData->type))
                    throw new \Exception("Invalid empty data type for relation '$relName' of record ID $id of type $table");

                if($relData->type!==$relSpec["table"])
                    throw new \Exception("Invalid data type for relation '$relName' of record ID $id of type $table");

                $includes = [];
//                echo "inserting";
//                print_r($relData);
                $resource->attributes[$relName] = $this->insert($relData->type,$relData,get_instance()->get_max_insert_recursions(),
                    "",[],null,$includes);
            }
        }

        if(isset($resource->attributes) && count(get_object_vars($resource->attributes))) {
            $resource->attributes = $this->dm->validate_object_attributes($table, $resource->attributes, "upd");
            return $this->updateAttributes($table,$id,$resource->attributes);
        }

        // todo: update 1:n relationships

        // get key flds of table


        return  $id;
    }


    /**
     * delete Record id $id from $database/$table
     *
     * @param string $tableName
     * @param string $recId
     *
     * @return bool
     * @throws \Exception
     */
    function deleteById($tableName, $recId) {
        // check if resource exists
        if(!$this->dm->resource_exists($tableName))
            throw new \Exception("Resource '$tableName' not found",404);

        if(!$this->dm->resource_allow_delete($tableName))
            throw new \Exception("Not authorized to delete from $tableName",401);

        $idFld = $this->dm->getPrimaryKey($tableName);

        $this->dbdrv->where("$idFld in ('$recId')");
        $this->dbdrv->delete($tableName);
        if($this->dbdrv->affected_rows()) {
            return true;
        }
        throw new \Exception("Record not found",404);
    }

    /**
     * @param string $tableName
     * @param array $where
     * @return bool
     * @throws \Exception
     */
    function deleteByWhere($tableName,$where) {
        // check if resource exists
        if(!$this->dm->resource_exists($tableName))
            throw new \Exception("Resource '$tableName' not found",404);

        if(!$this->dm->resource_allow_delete($tableName))
            throw new \Exception("Not authorized to delete from $tableName",401);

        $this->dbdrv->where($where);
        $this->dbdrv->delete($tableName);
        if($this->dbdrv->affected_rows())
            return true;

        throw new \Exception("Records not found",404);
    }

    /**
     * @param $obj
     * @return string
     * @throws \Exception
     */
    private function get_object_type($obj)
    {
        if(!is_object($obj))
            return "NoObject";

        if(property_exists($obj,"data"))
            return "DocumentObject";

        if(!property_exists($obj,"type"))
            return "UnknownObject";

        if(property_exists($obj,"id"))
            return property_exists($obj,"attributes")?"ResourceObject":"ResourceIndicatorObject";
        else
            return property_exists($obj,"attributes")?"newResourceObject":"InvalidObject";
    }

}
