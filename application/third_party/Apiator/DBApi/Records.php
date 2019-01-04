<?php
namespace Apiator\DBApi;



require_once(__DIR__."/../../../libraries/Response.php");
require_once(__DIR__."/../../../libraries/Errors.php");
require_once(__DIR__.'/../../../libraries/RecordSet.php');


require_once(__DIR__."/../../../libraries/HttpResp.php");
require_once(__DIR__."/../../../helpers/manager_helper.php");

/**
 * Class Records
 */
class Records {
    /**
     * @var \Apiator\DBApi\Datamodel
     */
    private $dm;

    /**
     * @var \CI_DB_pdo_driver
     */
    private $dbdrv;

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
            if(!$this->dm->is_valid_table($tbl)) {
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
     * @param string $table
     * @param array $includes
     * @param array $fields
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @return \RecordSet
     */
    function get_records($table, $includes, $fields, $filters, $offset=0, $limit=10, $order=[]) {
        //echo "get records";
        // validate main inputs

        //$queryFromArr = [$table];
        $includedFields = [];
        $includedFields[$table] = [];

        $from = [
            "table"=>$table,
            "fields"=> [],
            "order"=> [],
            "where"=> []
        ];


        // generate join & part of the fields Arr
        $joinArr = [];
        foreach ($includes as $include) {
            $fk = $this->dm->get_fk_relation($table,$include);

            if($fk->success) {
                $rel = $fk->data;
                $alias = sprintf("%s_%s",$include,$rel->table);
                $joinArr[$include] = [
                    "table" => $rel->table,
                    "alias"=> $alias,
                    "left"=>$alias.".".$rel->field,
                    "right"=>$table.".".$include,
                    "fields"=> [],
                    "order"=> [],
                    "where"=> []
                ];

                if(isset($fields[$include])) {
                    foreach ($fields[$include] as $fld)
                        if($this->dm->is_valid_field($rel->table,$fld))
                            array_push($joinArr[$include]["fields"],$fld);
                }

                if(empty($joinArr[$include]["fields"]))
                    array_push($joinArr[$include]["fields"],"*");
                elseif (!in_array("id",$joinArr[$include]["fields"]))
                    array_push($joinArr[$include]["fields"],"id");
            }
        }

        // take care  of the from part
        if(isset($fields[$table])) {
            foreach ($fields[$table] as $fld)
                if ($this->dm->is_valid_field($table, $fld))
                    array_push($from["fields"], $fld);
        }

        if(empty($from["fields"]))
            array_push($from["fields"], "*");
        else {
            array_push($from["fields"], "id");
            $from["fields"] = array_merge($from["fields"],array_keys($joinArr));
            $from["fields"] = array_unique($from["fields"]);
        }


        // ORDER BY array generation
        $orderByArr = [];
        foreach ($order as $item) {
            if($item->alias==$table && $this->dm->is_valid_field($table,$item->fld))
                $orderByArr[] = sprintf("%s.%s %s",$table,$item->fld,$item->dir);
            if(isset($joinArr[$item->alias]) && $this->dm->is_valid_field($joinArr[$item->alias]["table"],$item->fld))
                $orderByArr[] = sprintf("%s.%s %s",$joinArr[$item->alias]["alias"],$item->fld,$item->dir);
        }


        // generate $whereArr
        $whereArr = [];
        foreach ($filters as $filter) {
            if($filter->left->alias==$table
                && $this->dm->is_searchable_field($table,$filter->left->field)) {
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
        $recordSet = new \RecordSet([],$table,$this->dm->get_idfld($table), $offset,$totalRecs);

        if($from["fields"][0]=="*") {
            $from["fields"] = array_keys($this->dm->get_fields($from["table"]));
        }

        foreach ($joinArr as $fld=>$join) {
            if($join["fields"][0]=="*") {
                $joinArr[$fld]["fields"] = array_keys((array) $this->dm->get_fields($join["table"]));
            }
        }

        $resArray = $res->result_array_num();

        foreach ($resArray as $row) {
            $tmp = [];
            for($i=0;$i<count($from["fields"]);$i++) {
                $tmp[$from["fields"][$i]] = $row[$i];
            }
            $newRec = $recordSet->add_record($table,$tmp,$this->dm->get_idfld($table));
            $recOffset = count($from["fields"]) ;
            foreach ($joinArr as $fld=>$join) {
                $tmp = [];
                for($i=0;$i<count($join["fields"]);$i++) {
                    $tmp[$join["fields"][$i]] = $row[$i+$recOffset];
                }

                if(!empty($tmp["id"])) {
                    $relRecId = $tmp["id"];
                    $relRecType = $join["table"];
                    unset($tmp["id"]);
                    $newRec->attributes->$fld = new \Record($relRecType,$relRecId,$tmp);
                }
                //$RecordSet->add_related_record($join["table"],$tmp);
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
     * @param string $table
     * @param object $data
     * @param null|array $fieldsToUpdate fields to update in case of duplicate
     * @param  array $options
     * @return \Response
     */
    function create($table, $data, $fieldsToUpdate, $options) {
        // validate attributes
        //print_r($data);
        $resp = $this->dm->validate_object_attributes($table,$data,"ins");
        if(!$resp->success)
            return $resp;
        $attributes = $resp->data;

        $idFld = $this->dm->get_idfld($table);


        foreach($attributes as $name=>$value) {
            if(is_object($value)) {
                // insert first embeded objects
                $resp = is_valid_data($value);
                if(!$resp->success)
                    return $resp;

                $value = $value->data;

                $resp = $this->create($value->type,$value->attributes,$fieldsToUpdate,$options);

                if(!$resp->success)
                    return $resp;
                $attributes->$name = $resp->data;
            }
        }


        $insSql = $this->dbdrv->insert_string($table,$attributes);

        $updStr = in_array("duplicateUpdate",$options)?["$idFld=$idFld"]:[];
        if(!empty($fieldsToUpdate[$table])) {
            foreach($fieldsToUpdate[$table] as $fld) {
                if($this->dm->is_field_updateable($table,$fld) && isset($attributes->$fld)) {
                    $updStr[] = "$fld=VALUES($fld)";
                }
            }
        }
        $insSql .= (in_array("duplicateUpdate",$options)?" ON DUPLICATE KEY UPDATE ":" ").implode(",",$updStr);

        if(!$this->dbdrv->query($insSql))
            return \Response::make(false, 500, $this->dbdrv->error()["message"]);


        $insId = $this->dbdrv->insert_id();
        if($insId)
            return \Response::make(true,201,$insId);

        $this->dbdrv->where((array) $attributes);
        $selSql = $this->dbdrv->get_compiled_select($table);
        /**
         * @var \CI_DB_result
         */
        $q = $this->dbdrv->query($selSql);

        if($q->num_rows()>1) {
            print_r($q->result_array());
            log_message("debug","More then one records returned on Insert new record: $insSql / $selSql");
            return \Response::make(false,500,"More then one records returned");
        }

        if($q->num_rows()==0) {
            return \Response::make(false, 500, "Server error. Contact administrator.");
        }

        $newRecId = $q->row()->$idFld;
        return \Response::make(true,201,$newRecId);

    }



    /**
     * update Record
     * @param $table
     * @param $id
     * @param $attributes
     * @return \Response
     */
    function update($table, $id, $attributes) {
        $validation = $this->dm->validate_object_attributes($table,$attributes,"upd");
        if(!$validation->success)
            return $validation;
        $attributes = $validation->data;

        // get key flds of table
        $keyFlds = $this->dm->get_key_flds($table);
        if($keyFlds->success)
            $keyFlds = $keyFlds->data;
        else
            return $keyFlds;

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
                return \Response::make(false,409,"Duplicate key fields");
            }
        }


        $this->dbdrv->where("id",$id);
        $this->dbdrv->update($table,$attributes);
        return $this->dbdrv->affected_rows()>0?\Response::make(true,200):\Response::make(true,204);
    }


    /**
     * delete Record id $id from $database/$table
     *
     * @param string $tableName
     * @param string $recId
     *
     * @return \Response
     */
    function delete($tableName, $recId) {
        if(!$this->dm->is_valid_table($tableName))
            return \Response::make(false,404,"Invalid table $tableName");

        $this->dbdrv->where("id in ('$recId')");
        $this->dbdrv->delete($tableName);
        if($this->dbdrv->affected_rows()) {
            return \Response::make(true,204,null);
        }
        return \Response::make(false,404,"Not found");
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
     * @return \Response
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
                return \Response::make(false,404,"Insert failed");
            }
            else {
                $this->dbdrv->trans_commit();
                return \Response::make(true,204);
            }

        }
        catch(\Exception $e) {
            print_r($e);
        }
        return \Response::make(false,null,"Could not create relationship.");
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
        if(!$this->dm->is_valid_table($srcTbl))
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

        if(!$this->dm->is_valid_table($srcTbl))
            return \Response::make(false,404,"Invalid table $srcTbl");

        if(!$this->dm->is_valid_relation($srcTbl,$relation))
            return \Response::make(false,404,"Invalid relation $relation");

        $lnkTbl = $this->dm->get_rel_link_tlb($srcTbl,$relation);
        $tgtTbl = $this->dm->get_rel_target_tbl($srcTbl,$relation);

        switch($this->dm->get_rel_type($srcTbl,$relation)) {
            case "1:1":
                if(is_object($data))
                    if(property_exists($data,"type") && property_exists($data,$id))
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
