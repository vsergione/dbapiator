<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class generic_model_lib {
	function __construct() {
	}
	
    function recAsResObject($rec,$type) {
        $resObj = array(
            "type"  => $type,
            "id"    => $rec->id
        );
        foreach($rec as $fld=>$val) {
            if($fld!="id") {
                $resObj["attributes"][$fld] = $val;
            }
        }
        return (object) $resObj;
    }


    /**
     * @param $tbl
     * @param $id
     * @param $lnkdTbl
     * @return array|bool

    private function get_tgtTbl_from_and_where($tbl,$id,$lnkdTbl) {
        $dm = self::$dataModel;
        $where = array();
        
        // check if linked data is requested
        if(!is_null($lnkdTbl)) {
            // check if linked data is requested
            if(!is_null($id) && property_exists($dm->$tbl,"linkedTables")
                && property_exists($dm,$lnkdTbl)
                && property_exists($dm->$tbl->linkedTables,$lnkdTbl)) {
            
                $tgtTbl = $lnkdTbl;
                $lnkTbl = self::$dataModel->$tbl->linkedTables->$lnkdTbl->lnkTable;
                $from = "$lnkdTbl LEFT JOIN $lnkTbl ON $lnkTbl.{$lnkdTbl}_id=$lnkdTbl.id ";
                array_push($where,(object) array("left"=>"$lnkTbl.{$tbl}_id","op"=>"=","right"=>$id));
                
            }
            else {
                // cannot provide linked data info when no ID is provided
                log_message("debug","invalid request");
                return false;
            }
        }
        // no linked data requested => prepare normal query
        else{
            $tgtTbl = $tbl;
            $from = $tbl;
            if(!is_null($id)) {
                array_push($where,(object) array("left"=>"$tbl.id","op"=>"=","right"=>$id));
            }
        }
        
        return array($tgtTbl,$from,$where);
    }


    
    
    
   **
     * helper func to prepend table name to field name

    private function add_tablename_to_filter_flds($obj) {
        if(!is_object($obj))
            return null;
        $obj->left = $this->tgtTbl.".".$obj->left;
        return $obj;
    }
     */
}