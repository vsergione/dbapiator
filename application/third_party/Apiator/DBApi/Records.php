<?php
namespace Apiator\DBApi;



//require_once(__DIR__."/../../../libraries/Response.php");


require_once(__DIR__."/../../../libraries/Errors.php");
require_once(__DIR__.'/../../../libraries/RecordSet.php');

$req = require_once (APPPATH."/third_party/JSONApi/Autoloader.php");
use JSONApi\Autoloader;
Autoloader::register();

use JSONApi\Document;

//require_once(__DIR__."/../../../libraries/HttpResp.php");

/**
 * Class Records
 * core class for manipulating the records in the Database
 */
class Records {
    /**
     * @var \Apiator\DBApi\Datamodel
     */
    private $dm;

    /**
     * @var \CI_DB_query_builder
     */
    private $dbdrv;
    private $maxNoRels=10;

    /**
     * Records constructor.
     * @param \CI_DB_query_builder $dbDriver
     * @param Datamodel $dataModel
     */
    function __construct($dbDriver,$dataModel) {
        $this->dm = $dataModel;
        $this->dbdrv = $dbDriver;
        $this->maxNoRels = get_instance()->config->item("inbound_relationships_page_size");
    }

    static function init($dbDriver,$dataModel) {
        return new Records($dbDriver,$dataModel);
    }


    /**
     * it parses the includes into a relations tree, where each node contains:
     * - name: name of the table
     * - alias: alias for the table
     * - lnkFld: field which indicated by the parent FK field. To be used in JOIN definition
     * - fields: table fields (retrieved from DataBase data model which is important to be accurate)
     * - select: fields to be included in the SELECT part (defaults to *)
     * - includes: associative array of tables to be joined, where key is an FK field pointing to the related table
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
    private function generate_sql_parts($top, $includeStr, array $fields)
    {
        // parse & prepare includes
        $includeStr = trim($includeStr);
        $includes = $includeStr===""?[]:explode(",",$includeStr);

        sort($includes);
        for($i=0;$i<count($includes);$i++) {
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
        foreach ($includes as $include) {
            $this->process_includes($relationTree[$top],$include,$fields);
        }

        $select = [];
        $join = [];
        $this->prepare_query($relationTree[$top],0,$select,$join);


        $select = implode(", ",array_map(function($sel){
            return implode(", ",$sel);
        },$select));
        $join = implode(" \n",$join);

        return [$select,$join,$relationTree];
    }


    /**
     * generate include tree to be used for generating the SELECT clause
     *
     * @param array $parent
     * @param array $include include path as array, where lower indices are parents of higher ones
     * @throws \Exception
     */
    private function process_includes(&$parent, $include,$fields)
    {
        // if empty includes return
        if(!count($include)) {
            return;
        }

        // extract current level (first element of the array)
        $relName = array_shift($include);

        // generate alias
        $alias = $parent["alias"]."_".$relName;

        // retrieves relation between parent and current level
        // throws exception. caught lower and ignored
        try {
            $fkRel = $this->dm->get_outbound_relation($parent["name"], $relName);

            // check if joined resource already there and if not create it
            if(!array_key_exists($relName,$parent["includes"])) {
                $parent["includes"][$relName] = [
                    "name" => $fkRel["table"],
                    "alias" => $alias,
                    "lnkFld" => $fkRel["field"],
                    "fields" => $this->dm->get_selectable_fields($fkRel["table"]),
                    "fks" => $this->dm->get_fk_fields($fkRel["table"]),
                    "inbound" => $this->dm->get_inbound_relations($fkRel["table"]),
                    "select" => [],
                    "includes" => [],
                    "join" => $parent["alias"] . "." . $relName,
                    "type" => "1:1",
                    "parent" => &$parent
                ];

                // print_r($parent["includes"][$relName]);

                // if there is a fields selection for current node set it for select
                if(isset($fields[$fkRel["table"]])) {
                    $parent["includes"][$relName]["select"] = $fields[$fkRel["table"]];
                    foreach (array_keys($parent["includes"][$relName]["inbound"]) as $relName) {
                        if(!in_array($relName,$fields[$fkRel["table"]]))
                            unset($parent["includes"][$relName]["inbound"][$relName]);
                    }
                }
            }
            if(!empty($parent["select"]) && !in_array($relName,$parent["select"])) {
                $parent["select"][] = $relName;
            }

            $this->process_includes($parent["includes"][$relName],$include,$fields);
        }
        catch (\Exception $exception) {
            //print_r($exception);
        }

        if($inboundRel = $this->dm->get_inbound_relation($parent["name"], $relName)) {
            if(!array_key_exists($relName,$parent["includes"])) {
                $parent["includes"][$relName] = [
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
    private function prepare_query(&$node, $start, &$select, &$join)
    {
        $id = $this->dm->get_key_fld($node["name"]);

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
                $start = $this->prepare_query($node["includes"][$key],$start,$select,$join);
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
    private function parse_result_row($node, $row,&$allRecs)
    {
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
                $inboundRelation = $this->parse_result_row($incNode, $row, $allRecs);
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

                list($rec->relationships->$fk->data,$rec->relationships->$fk->total) = $this->get_records($incNode["table"],[
                    "filter"=>$filter,
                    "limit"=>$this->maxNoRels
                ]);
            }
        }
        return $rec;
    }

    /**
     * @param $filters
     * @param $resName
     * @return int|string
     * @throws \Exception
     */
    private function get_where_str($filters,$resName)
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
    private function get_sort_str($order,$resName)
    {
        $orderByArr = [];
        foreach ($order as $item) {
            if($item->alias!==$resName)
                continue;
            if(!$this->dm->field_is_sortable($item->alias))
                continue;

            $orderByArr[] = sprintf("%s.%s %s",$resName,$item->fld,$item->dir);
        }
        return count($orderByArr)?implode(", ",$orderByArr):1;
    }

    /**
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
     * @param string $tableName
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
     */
    function get_records($tableName, $opts=[])
    {
        // check if resource exists
        if(!$this->dm->resource_exists($tableName))
            throw new \Exception("Resource '$tableName' not found",404);

        // check if client is authorized
        if(!$this->dm->resource_allow_read($tableName))
            throw new \Exception("Not authorized to read from '$tableName''",403);

        // prepare parameters
        $defaultOpts = [
            "includeStr" => "",
            "fields" => [],
            "filter"=>[],
            "offset"=>0,
            "limit"=>get_instance()->config->item("default_page_size_limit"),
            "order"=>[]
        ];
        $opts = (object) array_merge($defaultOpts,$opts);

        $whereStr = $this->get_where_str($opts->filter,$tableName);

        // extract total number of records matched by the query
        $countSql = "SELECT count(*) as cnt FROM $tableName WHERE $whereStr";
        $totalRecs = $this->dbdrv->query($countSql)->row()->cnt;

        // return if no records matched
        if($totalRecs==0) return [[],0];

        $recordSet = [];
        // prepare field selection (validate and ....
        foreach ($opts->fields as $res=>$fldsStr) {
            $opts->fields[$res] = [];
            $tmp = explode(",",$fldsStr);
            foreach ($tmp as $fld) {
                if($this->dm->is_valid_field($res,$fld))
                    $opts->fields[$res][] = $fld;
                else
                    throw new \Exception("Invalid field name $res.$fld",401);
            }
            if(empty($opts->fields[$res]))
                unset($opts->fields[$res]);
        }

        // generate SQL parts & relation tree
        list($select,$join,$relTree) = $this->generate_sql_parts($tableName,$opts->includeStr,$opts->fields);
        // prepare ORDER BY part
        $orderStr = $this->get_sort_str($opts->order,$tableName);

        // compile SELECT
        $mainSql = "SELECT $select FROM {$relTree[$tableName]["name"]} AS {$relTree[$tableName]["alias"]} "
            .($join!==""?$join:"")
            ." WHERE $whereStr"
            ." ORDER BY $orderStr"
            ." LIMIT $opts->offset, $opts->limit";
        //echo $mainSql."\n";


        // run query
        /** @var \CI_DB_result $res */
        $res = $this->dbdrv->query($mainSql);
        $rows = $res->result_array_num();

        // parse result
        $allRecs = [];
        foreach ($rows as $row) {
            $newRec = $this->parse_result_row($relTree[$tableName],$row,$allRecs);
            $recordSet[] = $newRec;
        }

        return [$recordSet,$totalRecs];

    }

    /**
     * @param string $resName
     * @param array $includes
     * @param array $fields
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @return \RecordSet
     * @throws \Exception
     */
    function get_records_old($resName, $includes, $fields, $filters, $offset=0, $limit=10, $order=[]) {
        //echo "get records";
        // validate main inputs

        //$queryFromArr = [$table];
        $includedFields = [];
        $includedFields[$resName] = [];

        $from = [
            "table"=>$resName,
            "fields"=> [],
            "order"=> [],
            "where"=> []
        ];


        // generate join & partially the fields Arr
        $joinArr = [];
        foreach ($includes as $include) {
            // get relation
            try {
                $rel = $this->dm->get_outbound_relation($resName, $include);
            }
            catch (\Exception $exception) {
                $rel = null;
            }

            if($rel) {
                $alias = sprintf("%s_%s",$include,$rel["table"]);
                $joinArr[$include] = [
                    "table" => $rel["table"],
                    "alias"=> $alias,
                    "left"=>$alias.".".$rel["field"],
                    "right"=>$resName.".".$include,
                    "fields"=> [],
                    "order"=> [],
                    "where"=> [],
                    "fkFLd"=>$rel["field"]
                ];

                // add selected fields of included resources for the SELECT part
                if(isset($fields[$include])) {
                    foreach ($fields[$include] as $fld) {
                        if ($this->dm->is_valid_field($rel["table"], $fld)) {
                            array_push($joinArr[$include]["fields"], $fld);
                        }
                    }
                }

                // when no fields specified, make sure to add *
                if(empty($joinArr[$include]["fields"]))
                    array_push($joinArr[$include]["fields"],"*");
                // if there are fields specified make sure that the fkField is included
                elseif (!in_array($joinArr[$include]["fkFLd"],$joinArr[$include]["fields"]))
                    array_push($joinArr[$include]["fields"],$joinArr[$include]["fkFLd"]);
            }

        }

        // take care  of the FROM part
        if(isset($fields[$resName])) {
            foreach ($fields[$resName] as $fld)
                if ($this->dm->is_valid_field($resName, $fld))
                    array_push($from["fields"], $fld);
        }

        if(empty($from["fields"])) {
            array_push($from["fields"], "*");
        }
        else {
            $primaryKey = $this->dm->get_key_fld($resName);
            if($primaryKey && !in_array($primaryKey,$from["fields"]))
                array_push($from["fields"],$primaryKey );
            $from["fields"] = array_merge($from["fields"],array_keys($joinArr));
            $from["fields"] = array_unique($from["fields"]);
        }


        // generate ORDER BY array
        $orderByArr = [];
        foreach ($order as $item) {
            if($item->alias==$resName && $this->dm->is_valid_field($resName,$item->fld))
                $orderByArr[] = sprintf("%s.%s %s",$resName,$item->fld,$item->dir);
            if(isset($joinArr[$item->alias]) && $this->dm->is_valid_field($joinArr[$item->alias]["table"],$item->fld))
                $orderByArr[] = sprintf("%s.%s %s",$joinArr[$item->alias]["alias"],$item->fld,$item->dir);
        }


        // generate $whereArr
        $whereArr = [];
        foreach ($filters as $filter) {
            if($filter->left->alias==$resName
                && $this->dm->field_is_searchable($resName,$filter->left->field)) {
                $whereArr[] = generate_where_str($filter);

            }
            elseif(isset($joinArr[$filter->left->alias])
                && $this->dm->field_is_searchable($joinArr[$filter->left->alias]["table"],$filter->left->field)) {
                $filter->left->alias = $joinArr[$filter->left->alias]["alias"];
                $whereArr[] = generate_where_str($filter);
            }
        }

        //list($totalRecs,$res) = $this->select_query($from,$joinArr,$whereArr,$orderByArr,$offset,$limit);
        list($countSql,$mainSql) = render_select_query($from,$joinArr,$whereArr,$orderByArr,$offset,$limit);

        $res = $this->dbdrv->query($countSql);

        //echo $this->dbdrv->last_query();
        $totalRecs = $res->row()->cnt;
        if($totalRecs===0)
            $mainSql = "SELECT 1 from DUAL where false";

        $res = $this->dbdrv->query($mainSql);
        //echo $this->dbdrv->last_query();

        $recordSet = new \RecordSet([],$resName,$this->dm->get_idfld($resName), $offset,$totalRecs);

        if($from["fields"][0]=="*") {
            $from["fields"] = array_keys($this->dm->get_fields($from["table"]));
        }

        foreach ($joinArr as $fld=>$join) {
            if($join["fields"][0]=="*") {
                $joinArr[$fld]["fields"] = array_keys((array) $this->dm->get_fields($join["table"]));
            }
        }

        $resArray = $res->result_array_num();
        //print_r($joinArr);

        foreach ($resArray as $row) {
            // separate the main table fields from the fields of the joined  tables
            $mainTableFlds = [];
            for($i=0;$i<count($from["fields"]);$i++) {
                $mainTableFlds[$from["fields"][$i]] = $row[$i];
            }
            $newRec = $recordSet->add_record($resName,$mainTableFlds,$this->dm->get_idfld($resName));

            $recOffset = count($from["fields"]) ;
            foreach ($joinArr as $fld=>$join) {
                //print_r($join);
                // extract fields of the joined array
                $joinTableFlds = [];
                for($i=0;$i<count($join["fields"]);$i++) {
                    $joinTableFlds[$join["fields"][$i]] = $row[$i+$recOffset];
                }

                if(!empty($joinTableFlds[$join["fkFLd"]])) {
                    $relRecId = $joinTableFlds[$join["fkFLd"]];
                    $relRecType = $join["table"];
                    unset($joinTableFlds[$join["fkFLd"]]);
                    $newRec->attributes->$fld = new \Record($relRecType,$relRecId,$joinTableFlds);
                }
                //$recordSet->add_related_record($join["table"],$tmp);
                $recOffset += count($join["fields"]);
            }
            reset($joinArr);
        }

        return $recordSet;
    }


    /**
     * create new Record
     * TODO: clarify best approach about what to return after inserting OK....
     *
     *
     * @param $table
     * @param object $data data to be inserted
     * @param bool $recursive allow recursive insert
     * @param string $onDuplicate behaviour flags
     * @param String[] $fieldsToUpdate
     * @param $path
     * @param $includes
     * @return \Response
     * @throws \Exception
     */
    function insert($table, $data, $recursive, $onDuplicate, $fieldsToUpdate, $path, &$includes) {
        //$table = $data->type;
        if($data->type!=$table)
            throw new \Exception("Invalid data type '$table'",400);

        // check if resource exists
        if(!$this->dm->resource_exists($table))
            throw new \Exception("Resource '$table'' not found",404);

        // check if client is authorized to insert into resource
        if(!$this->dm->resource_allow_insert($table))
            throw new \Exception("Not authorized to insert into resource '$table'",401);



        // validate attributes
        $attributes = $data->attributes;
        $relations = isset($data->relationships)?$data->relationships:[];
        $idFld = $this->dm->get_key_fld($table);

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
            if(!isset($relData->data)) {
                throw new \Exception("Invalid relationship '$relName' data: invalid format",400);
            }
            if(!is_object($relData)) {
                throw new \Exception("Invalid relationship '$relName' data: invalid format ",400);
            }
            // inbound relation (1:n) add to stack for later insert
            if(is_array($relData->data)) {
                $one2nRelations[$relName] = $relData->data;
                continue;
            }

            if(!$this->dm->is_valid_field($table,$relName))
                throw new \Exception("Invalid 1:1 relation '$relName' for '$table'",400);

            if(!isset($relData->data->type))
                throw new \Exception("Invalid relationship data: missing '$relName' type",400);

            $fk = (object)$this->dm->get_outbound_relation($table,$relName);
            //$relData = (object)$relData;
            if($fk->table!==$relData->data->type)
                throw new \Exception("Invalid relationship data: invalid type for relationship '$relName'",400);
            $newPath = $path==null?$relName:$path.".$relName";
            if(isset($relData->data->id)) {
                if(!in_array($newPath,$includes))
                    $includes[] = $newPath;
                $insertData[$relName] = $relData->data->id;
                continue;
            }
            if(isset($relData->data->attributes)) {
                $insertData[$relName] = $this->insert($fk->table,$relData->data,$recursive,$onDuplicate,$fieldsToUpdate,$newPath,$includes);
                if(!in_array($newPath,$includes))
                    $includes[] = $newPath;
            }
        }

        $insertData = $this->dm->validate_object_attributes($table,$insertData,"ins");

        // print_r($attributes);
        $insSql = $this->dbdrv->insert_string($table,$insertData);

        // configure behaviour to update fields when
        // valid for MySQL
        // todo: should put this in an external file
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
        }


        // insert data in DB
        if(!$this->dbdrv->query($insSql)) {
            log_message("error","$insSql");
            throw new \Exception($this->dbdrv->error()["message"], 500);
        }

        // retrieve resource ID (mysql specific)
        // todo: evaluate impact for other DB engines and implement
        $insId = $this->dbdrv->insert_id();
        if(!$insId && $this->dbdrv->affected_rows() && $idFld)
            $insId = $insertData[$idFld];

        if($insId)
            return $insId;

        $selSql = $this->dbdrv
                    ->where($insertData)
                    ->get_compiled_select($table);

        /**
         * @var \CI_DB_result
         */
        $q = $this->dbdrv->query($selSql);
        $cnt = $q->num_rows();

        if($cnt>1) {
            log_message("error","More then one records returned on Insert new record: $insSql / $selSql");
            throw new \Exception("More then one records returned",500);
        }

        if($cnt==0) {
            log_message("error","Insert did not failed, but no records where returned...: $insSql / $selSql");
            throw new \Exception("Server error. Contact administrator.",500);
        }

        $newRecId = $q->row()->$idFld;
        return $newRecId;
    }


    /**
     * update Record
     * @param $table
     * @param $id
     * @param $resource
     * @return string
     * @throws \Exception
     */
    function update_by_id($table, $id, $resource) {
        try {
            $resource->attributes = $this->dm->validate_object_attributes($table, $resource->attributes, "upd");
        }
        catch (\Exception $exception) {
            throw new \Exception("Could not update record due to data validation failure.",400,$exception);
        }
        $priKey = $this->dm->get_key_fld($table);
        if(!$priKey)
            throw new \Exception("Update by ID not allowed: table '$table' does not have primary key.",500);

        // get key flds of table
        $keyFlds = $this->dm->get_key_flds($table);


        // validate uniq recs
        $whereArr = array();
        foreach($resource->attributes as $name=>$value) {
            if(in_array($name,$keyFlds)) {
                $whereArr[] = "$name='$value'";
            }
        }

        if(count($whereArr)) {
            $sql = "SELECT * FROM $table WHERE $priKey!='$id' AND (".implode(" OR ",$whereArr).")";

            $q = $this->dbdrv->query($sql);
            if($q->num_rows()) {
                throw new \Exception("Duplicate key fields",409);
            }
        }


        $this->dbdrv->where($this->dm->get_key_fld($table),$id);
        $this->dbdrv->update($table,$resource->attributes);
        if(isset($resource->attributes[$priKey]))
            return $resource->attributes[$priKey];
        return $resource->id;

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
    function delete($tableName, $recId) {
        // check if resource exists
        if(!$this->dm->resource_exists($tableName))
            throw new \Exception("Resource '$tableName' not found",404);

        if(!$this->dm->resource_allow_delete($tableName))
            throw new \Exception("Not authorized to delete from $tableName",401);

        $idFld = $this->dm->get_key_fld($tableName);

        $this->dbdrv->where("$idFld in ('$recId')");
        $this->dbdrv->delete($tableName);
        if($this->dbdrv->affected_rows()) {
            return true;
        }
        throw new \Exception("Record not found",404);
    }

}
