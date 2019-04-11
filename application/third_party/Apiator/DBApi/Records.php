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

    /**
     * Records constructor.
     * @param \CI_DB_query_builder $dbDriver
     * @param Datamodel $dataModel
     */
    function __construct($dbDriver,$dataModel) {
        $this->dm = $dataModel;
        $this->dbdrv = $dbDriver;
    }

    static function init($dbDriver,$dataModel) {
        return new Records($dbDriver,$dataModel);
    }



    /**
     * @codeCoverageIgnore
     * @param $totalRecs
     * @param $data
     * @param int $offset
     * @param null $includeArr
     * @return object
     */
    private function build_return_obj($totalRecs, $data, $offset=0, $includeArr=null) {
        $data = is_array($data)?$data: [];
        $return = (object) ["data"=>array_values($data)];
        $return->meta = (object) ["total"=>$totalRecs,"offset"=>$offset];
        if(count($includeArr)) {
            $return->included = $includeArr;
        }

        return $return;
    }


    /**
     * @codeCoverageIgnore
     * @param array $order order array
     * @param array $queryFromArr array of tables to query from
     * @return array Array of strings  to be used in ORDER BY statement
     */
    private function generate_order_arr($order,$queryFromArr) {
        $orderByArr = [];
        foreach($order as $item) {
            $item = (object) $item;
            if(in_array($item->alias,$queryFromArr)) {
                if($this->dm->is_valid_field($item->table,$item->fld))
                    $orderByArr[] = sprintf("%s.%s %s",$item->table,$item->fld,$item->dir);
            }
        }
        return $orderByArr;
    }

    /**
     * prepare fields for SELECT clause
     *
     * @param array $fields reference of fields array
     * @return array
     */
    private function clean_up_fields($fields) {
        // cleanup fields and add id field when not existing
        foreach($fields as $tbl=>$flds) {
            if(!$this->dm->is_valid_resource($tbl)) {
                unset($fields[$tbl]);
            }
            else {
                if(!empty($flds)) {
                    $fields[$tbl] = array();
                    foreach($flds as $fld) {
                        if($this->dm->is_valid_field($tbl,$fld)) {
                            $fields[$tbl][] = $fld;
                        }
                    }
                    if(count($fields[$tbl]) && !in_array("id",$flds)) {
                        array_push($fields[$tbl],"id");
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * extract ids from records in RecordSet
     * @param mixed $data
     * @return array List of extracted Record ids
     */
    private function get_ids($data)
    {
        $tmp = is_array($data)?$data: [$data];
        $ids = [];
        foreach($tmp as $rec) {
            if(property_exists($rec,"id"))
                array_push($ids,$rec->id);
        }
        return $ids;
    }


    /**
     * @param $resName
     * @param $recId
     * @param $fkName
     * @param $includes
     * @param $fields
     * @param $filters
     * @param $offset
     * @param $limit
     * @param $order
     * @return \RecordSet
     * @throws \Exception
     */
//    function get_fk_record($resName,$recId,$fkName,$includes,$fields,$filters,$offset,$limit,$order)
//    {
//        // todo: remove??
//
//        return $this->get_records($rel["table"],$includes,$fields,$filters,$offset,$limit,$order);
//    }


    /**
     * @param $resName
     * @param $includes
     * @param $fields
     * @param $filters
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @return array
     * @throws \Exception
     */
    function get_records($resName, $includes, $fields, $filters, $offset=0, $limit=10, $order=[])
    {
        if(!$this->dm->is_valid_resource($resName))
            throw new \Exception("Invalid resource $resName");

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
         * @param Datamodel $dm
         * @param $top
         * @param string $includeStr string of comma separated includes
         * @param string $fieldsStr string of comma separated field names to include
         * @return array
         */
        function generate_sql_parts(&$dm,$top, $includeStr, $fieldsStr)
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
                        "fields" => $dm->get_selectable_fields($top),
                        "select" => [],
                        "includes" => []
                    ]
                ];
            }
            catch (Exception $exception) {
                throw new Exception("Base table is invalid",400);
            }


            // populate relationTree tree by processing includes
            foreach ($includes as $include) {
                process_includes($dm,$relationTree[$top],$include);
            }


            // parse & prepare fields as in format path + field name
            // where path is the way to navigate within the object
            $fields = explode(",",$fieldsStr);
            for($i=0;$i<count($fields);$i++) {
                $fields[$i] = explode(".",trim($fields[$i]));
                $fld = array_pop($fields[$i]);
                array_unshift($fields[$i],$top);
                $tmp = implode(".includes.",$fields[$i]);
                $fields[$i] = [
                    "path"=>explode(".",$tmp),
                    "field"=>$fld
                ];
            }

            // add selected fields
            foreach ($fields as $field) {
                try {
                    // identify node indicated by path
                    $table =& find_in_tree($relationTree, $field["path"]);
                    if(in_array($field["field"],$table["fields"])){
                        $table["select"][] = $field["field"];
                    }
                }
                catch (Exception $exception) {
                    //echo "wrong path";
                }
                //print_r($res["fields"][$field[""]]);
            }

            $select = [];
            $join = [];
            prepare_query($dm,$relationTree[$top],0,$select,$join);

            //print_r($join);
            $select = implode(", ",array_map(function($sel){
                return implode(", ",$sel);
            },$select));
            $join = implode(" \n",$join);
            return [$select,$join,$relationTree];
        }

        /**
         * parses
         * @param Datamodel $dm
         * @param $obj
         * @param $start
         * @param $select
         * @param $join
         * @return int
         */
        function prepare_query(&$dm,&$obj, $start,&$select,&$join)
        {
            try {
                $id = $dm->get_key_fld($obj["name"]);
            }
            catch (Exception $exception) {
                $id = null;
            }

            $obj["keyFld"] = $id;
            $obj["start"] = $start;

            if(count($obj["select"])===0) {
                $obj["select"] = $obj["fields"];
            }

            $obj["select"][]=$id;
            $obj["select"] = array_unique($obj["select"]);
            $obj["noFlds"] = count($obj["select"]);

            // extract ID field position in the fields array
            $obj["keyFldPos"] = $obj["keyFld"]?array_search($obj["keyFld"],$obj["fields"]):null;
            $obj["keyFldPos"] = $obj["keyFldPos"]===false?null:$obj["keyFldPos"];


            // define currAlias global to be able to refer to it from inside the callback function
            global $currAlias;
            $currAlias = $obj["alias"];

            // add alias prefix to field selection using an array_map and an anonymous function as callback
            $select[] = array_map(function($item){
                global $currAlias;
                return $currAlias.".$item";
            },$obj["select"]);

            // generate LEFT JOIN for joined tables
            if(isset($obj["join"]))
                $join[] = sprintf("\nLEFT JOIN %s AS %s ON %s=%s.%s",
                    $obj["name"],$obj["alias"],$obj["join"],$obj["alias"],$obj["lnkFld"]);

            $start += $obj["noFlds"];
            foreach (array_keys($obj["includes"]) as $key) {
                $start = prepare_query($dm,$obj["includes"][$key],$start,$select,$join);
            }

            return $start;
        }

        /**
         * generate include tree to be used for generating the SELECT clause
         *
         * @param Datamodel $dm
         * @param array $parent
         * @param array $include include path as array, where lower indices are parents of higher ones
         */
        function process_includes(&$dm,&$parent, $include)
        {
            // if empty includes return
            if(!count($include)) {
                return;
            }

            // extract current level (first element of the array)
            $attr = array_shift($include);

            // generate alias
            $alias = $parent["alias"]."_".$attr;

            try {
                // retrieves relation between parent and current level
                // throws exception. caught lower and ignored
                $rel = $dm->get_fk_relation($parent["name"], $attr);

                // check if joined resource already there and if not create it
                if(!array_key_exists($attr,$parent["includes"]))
                    $parent["includes"][$attr] = [
                        "name"=>$rel["table"],
                        "alias"=>$alias,
                        "lnkFld"=>$rel["field"],
                        "fields"=>$dm->get_selectable_fields($rel["table"]),
                        "select"=>[],
                        "includes"=>[],
                        "join"=>$parent["alias"].".".$attr
                    ];
                process_includes($dm,$parent["includes"][$attr],$include);
            }
            catch (Exception $exception) {
                echo $exception->getMessage()."\n";
            }
        }

        /**
         * @param array $tree reference to array
         * @param $path
         * @return null|array
         * @throws Exception
         */
        function &find_in_tree(&$tree, $path)
        {
            if(count($path)==0)
                return $tree;

            $c = array_shift($path);
            if(!isset($tree[$c])) {
                throw new Exception("Wrong path");
            }
            return find_in_tree($tree[$c],$path);
        }

        /**
         * @param $node
         * @param $row
         * @param $allRecs
         * @return null|\stdClass
         */
        function parse_result_row($node, $row,&$allRecs)
        {
            $id = $node["keyFld"]?$row[$node["keyFldPos"]]:null;
            $rec = null;

            if($id) {
                $objIdx = $node["name"] . "_" . $id;
                if(isset($allRecs[$objIdx]))
                    $rec &= $allRecs[$objIdx];
            }

            if(is_null($rec)){
                $attributes = new \stdClass();
                for ($i = 0; $i < $node["noFlds"]; $i++) {
                    $fieldName = $node["fields"][$i];
                    $attributes->$fieldName = $row[$node["start"] + $i];
                }
                $rec = new \stdClass();
                $rec->id = $id;
                $rec->type = $node["name"];
                $rec->attributes = $attributes;

                if(isset($objIdx))
                    $allRecs[$objIdx] = $rec;
            }

            if(!isset($node["includes"]) || count($node["includes"])==0)
                return $rec;

            foreach($node["includes"] as $fk=>$incNode) {
                if(!is_null($rec->attributes->$fk))
                    $rec->attributes->$fk = parse_result_row($incNode,$row,$allRecs);
            }
            return $rec;
        }

        $where = 1;

        $countSql = "SELECT count(*) as cnt FROM $resName WHERE $where";
        $totalRecs = $this->dbdrv->query($countSql)->row()->cnt;

        $recordSet = [];
        if($totalRecs==0)
            return [$recordSet,0];

        list($select,$join,$relTree) = generate_sql_parts($this->dm,$resName,$includes,$fields);
        //print_r($relTree);


        $mainSql = "SELECT $select FROM {$relTree[$resName]["name"]} AS {$relTree[$resName]["alias"]} "
            .($join!==""?$join:"")
            ." WHERE $where"
            ." LIMIT $offset,$limit";


        /** @var \CI_DB_result $res */
        $res = $this->dbdrv->query($mainSql);
        $rows = $res->result_array_num();
        //print_r($relTree);
        $allRecs = [];
        foreach ($rows as $row) {
            $newRec = parse_result_row($relTree[$resName],$row,$allRecs);

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
                $rel = $this->dm->get_fk_relation($resName, $include);
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
                && $this->dm->is_searchable_field($resName,$filter->left->field)) {
                $whereArr[] = generate_where_str($filter);

            }
            elseif(isset($joinArr[$filter->left->alias])
                && $this->dm->is_searchable_field($joinArr[$filter->left->alias]["table"],$filter->left->field)) {
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
     * @param string $table name of table where data will be inserted
     * @param object $data data to be inserted
     * @param bool $recursive allow recursive insert
     * @param string $onDuplicate behaviour flags
     * @param String[] $toUpdateFields
     * @return \Response
     * @throws \Exception
     */
    function insert($table, $data, $recursive, $onDuplicate, $toUpdateFields) {
        // validate attributes
        $attributes = $this->dm->validate_object_attributes($table,$data,"ins");

        $idFld = $this->dm->get_key_fld($table);

        // call recursive create for embedded records
        foreach($attributes as $fldName=>$value) {
            if(is_object($value) && $recursive) {
                // insert first embeded objects
                try{
                    is_valid_post_data($value);
                }
                catch (\Exception $e) {
                    throw $e;
                }

                $value = $value->data;
                try {
                    $recId = $this->insert($value->type, $value->attributes, $recursive, $onDuplicate, $toUpdateFields);
                    $attributes->$fldName = $recId;
                }
                catch (\Exception $e)  {
                    throw $e;
                }
            }
        }


        // print_r($attributes);
        $insSql = $this->dbdrv->insert_string($table,$attributes);

        // configure behaviour to update fields when
        if($onDuplicate=="update") {
            $updStr = [];
            if (!empty($toUpdateFields[$table])) {
                foreach ($toUpdateFields[$table] as $fld) {
                    if ($this->dm->is_field_updateable($table, $fld) && isset($attributes->$fld)) {
                        $updStr[] = "$fld=VALUES($fld)";
                    }
                }
            }
            if(count($updStr))
                $insSql .= " ON DUPLICATE KEY UPDATE " . implode(",", $updStr);
            else
                throw new \Exception("Invalid fields to be updated",400);
        }

        if($onDuplicate=="ignore") {
            if($idFld)
                $insSql .= " ON DUPLICATE KEY UPDATE $idFld=$idFld";
        }


        if(!$this->dbdrv->query($insSql))
            throw new \Exception($this->dbdrv->error()["message"],500);


        $insId = $this->dbdrv->insert_id();
        if($insId)
            return $insId;

        $this->dbdrv->where((array) $attributes);
        $selSql = $this->dbdrv->get_compiled_select($table);
        /**
         * @var \CI_DB_result
         */
        $q = $this->dbdrv->query($selSql);

        if($q->num_rows()>1) {
            log_message("debug","More then one records returned on Insert new record: $insSql / $selSql");
            throw new \Exception("More then one records returned",500);
        }

        if($q->num_rows()==0) {
            throw new \Exception("Server error. Contact administrator.",500);
        }

        $newRecId = $q->row()->$idFld;
        return $newRecId;

    }


    /**
     * update Record
     * @param $table
     * @param $id
     * @param $attributes
     * @return \Response
     * @throws \Exception
     */
    function update($table, $id, $attributes) {
        $validation = $this->dm->validate_object_attributes($table,$attributes,"upd");
        if(!$validation->success)
            return $validation;
        $attributes = $validation->data;

        // get key flds of table
        $keyFlds = $this->dm->get_key_flds($table);

        // validate uniq recs
        $whereArr = array();
        foreach($attributes as $name=>$value) {
            if(in_array($name,$keyFlds)) {
                $whereArr[] = "$name='$value'";
            }
        }

        if(count($whereArr)) {
            $sql = "SELECT * FROM $table WHERE id!='$id' AND (".implode(" OR ",$whereArr).")";

            $q = $this->dbdrv->query($sql);
            if($q->num_rows()) {
                throw new \Exception("Duplicate key fields",409);
            }
        }


        $this->dbdrv->where($this->dm->get_key_fld($table),$id);
        $this->dbdrv->update($table,$attributes);
        return $this->dbdrv->affected_rows();
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
        /**
         * TODO same check should be used in OPTIONS response
         */
        if(!$this->dm->delete_allowed($tableName))
            throw new \Exception("Delete not allowed on table $tableName",400);

        $idFld = $this->dm->get_key_fld($tableName);

        $this->dbdrv->where("$idFld in ('$recId')");
        $this->dbdrv->delete($tableName);
        if($this->dbdrv->affected_rows()) {
            return true;
        }
        throw new \Exception("Not found",404);
    }

    /**
     * validates if object is valid
     * @param $data
     * @return bool
     * @todo to implemet check on controller side => delete from here
     */
    private function is_valid_resource($data) {
        return property_exists($data,"type") && property_exists($data,"id");
    }

    /**
     * create relationship (new)
     * @param $srcTbl
     * @param $srcId
     * @param $relation
     * @param $data
     * @return bool
     */
    function create_relationships($srcTbl,$srcId,$relation,$data) {
        if(!is_array($data))
            $data = array($data);
        try {
            $lnkTbl = $this->dm->get_rel_link_tlb($srcTbl,$relation);

            $this->dm->get_rel_target_tbl($srcTbl,$relation);
            $insData = array();
            foreach($data as $item) {
                $insData[] = array($srcTbl."_id"=>$srcId,$item->type."_id"=>$item->id);
            }

            //$this->db->db_debug = FALSE;
            $this->dbdrv->trans_start();
            $this->dbdrv->insert_batch($lnkTbl,$insData);
            $this->dbdrv->trans_complete();
            $this->dbdrv->db_debug = TRUE;
            if ($this->dbdrv->trans_status() === FALSE) {
                $this->dbdrv->trans_rollback();
                throw new \Exception("Insert failed",500);
            }
            else {
                $this->dbdrv->trans_commit();
                return true;
            }

        }
        catch(\Exception $e) {
            throw new $e;
        }
    }


    /**
     * updates relationships
     *
     * @param string $srcTbl source table name
     * @param string $srcId id of source Record
     * @param string $relation relationship name
     * @param mixed $data relation data
     * @return \Response
     *
     * @todo Review code
     */
    function update_relationships($srcTbl,$srcId,$relation,$data) {
        if(!$this->dm->is_valid_resource($srcTbl))
            return \Response::make(false,404,"Invalid table $srcTbl");

        if(!$this->dm->is_valid_relation($srcTbl,$relation))
            return \Response::make(false,404,"Invalid relation $relation");

        $lnkTbl = $this->dm->get_rel_link_tlb($srcTbl,$relation);
        $tgtTbl = $this->dm->get_rel_target_tbl($srcTbl,$relation);
        if($this->dm->get_rel_type($srcTbl,$relation)=="1:1")
            if(is_object($data))
                if($data==null)
                    $this->dbdrv->delete($lnkTbl,array($srcTbl."_id"=>$srcId));
                else
                    if($this->is_valid_resource($data))
                        if($data->type==$tgtTbl) {
                            $this->dbdrv->delete($lnkTbl,array($srcTbl."_id"=>$srcId));
                            $this->dbdrv->insert($lnkTbl,array($srcTbl."_id"=>$srcId,$tgtTbl."_id"=>$data->id));
                            return \Response::make(true,204);
                        }
                        else
                            return \Response::make(false,400,"Invalid relation data type");
                    else
                        return \Response::make(false,404,"Invalid relation data");
            else
                return \Response::make(false,400,"Invalid relation data");
        elseif($this->dm->get_rel_type($srcTbl,$relation)=="1:n")
            if(is_array($data))
                if(count($data)==0)
                    $this->dbdrv->delete($lnkTbl,array($srcTbl."_id"=>$srcId));
                else {
                    $insData = array();
                    $dataSize = count($data);
                    foreach($data as $idx=>$item)
                        if($this->is_valid_resource($item))
                            if($item->type==$tgtTbl)
                                $insData[] = array($srcTbl."_id"=>$srcId,$tgtTbl."_id"=>$item->id);
                            else
                                unset($data[$idx]);
                        else
                            unset($data[$idx]);
                    if(count($insData)) {
                        $this->dbdrv->delete($lnkTbl,array($srcTbl."_id"=>$srcId));
                        $this->dbdrv->insert($lnkTbl,$insData);
                        if(count($data)!=$dataSize)
                            return \Response::make(true,200,$data);
                        else
                            return \Response::make(true,204);
                    }
                }
            else
                return \Response::make(false,400,"Invalid relation data");
        else
            return \Response::make(false,500,"Invalid relation config on server side");

        return \Response::make(false,500,"Invalid relation config on server side");
    }

    /**
     * delete relationships
     *
     * @param string $srcTbl source table name
     * @param string $srcId id of source Record
     * @param string $relation relationship name
     * @param array|object $data relation data
     * @return \Response
     */
    function delete_relationship($srcTbl,$srcId,$relation,$data) {

        if(!$this->dm->is_valid_resource($srcTbl))
            return \Response::make(false,404,"Invalid table $srcTbl");

        if(!$this->dm->is_valid_relation($srcTbl,$relation))
            return \Response::make(false,404,"Invalid relation $relation");

        $lnkTbl = $this->dm->get_rel_link_tlb($srcTbl,$relation);
        $tgtTbl = $this->dm->get_rel_target_tbl($srcTbl,$relation);

        switch($this->dm->get_rel_type($srcTbl,$relation)) {
            case "1:1":
                if(is_object($data))
                    if(property_exists($data,"type") && property_exists($data,"id"))
                        if($data->type==$tgtTbl)
                            $deleteWhere = "{$srcTbl}_id='$srcId' AND {$tgtTbl}_id='$data->id'";
                        else
                            return \Response::make(false,400,"Invalid input data");
                    else
                        return \Response::make(false,400,"Invalid input data");
                else
                    return \Response::make(false,400,"Invalid input data");
                $this->dbdrv->where($deleteWhere)->delete($lnkTbl);
                break;
            case "1:n":
                $deleteWhere = array();
                if(is_array($data))
                    foreach($data as $idx=>$item)
                        if(property_exists($item,"type") && property_exists($item,"id"))
                            if($item->type==$tgtTbl)
                                $deleteWhere[] = "({$srcTbl}_id='$srcId' AND {$tgtTbl}_id='$item->id')";
                            else
                                return \Response::make(false,400,"Invalid input data");
                        else
                            return \Response::make(false,400,"Invalid input data");
                else
                    return \Response::make(false,400,"Invalid input data");

                $this->dbdrv->where(implode(" OR ",$deleteWhere))->delete($lnkTbl);
                break;
            default:
                return \Response::make(false,500,"Invalid config");
        }

        return \Response::make(true,204);
    }
}
