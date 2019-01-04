<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Table {
	private $_ci;
	private $_config;
	private $_self;
	
	function __construct($name,$config,$ci) {
		$this->_self = $name;
		$this->_big_conf = $config;
		try {
			$this->_config = $config->$name;
		}
		catch(Exception $e) {
			print_r($e);
		}
		
		$this->_ci = $ci;
	}

    /**
     * @param $data
     * @return array|bool
     */
	function insert($data) {
		//$this->_ci->db->db_debug = FALSE;
		if(!is_array($data))
			$data = array($data);
		$ids = array();
		$this->db->trans_start();
		foreach($data as $rec) {
			$this->_ci->db->insert_batch($this->_self,$data);
			$ids[] = $this->_ci->db->insert_id();
		}
		$this->db->trans_complete();
		$this->_ci->db->db_debug = TRUE;
		
		return empty($this->_ci->db->error())?$ids:false;
	}

    /**
     * @param $where
     * @return mixed
     * @throws Exception
     */
	function delete($where) {
		$this->_ci->db->where($where);
		$this->_ci->db->db_debug = FALSE;
		$this->_ci->db->delete($this->_self);
		$this->_ci->db->db_debug = TRUE;
		$err = $this->_ci->db->error();
		if($err)
			throw(new Exception(json_encode($err)));
		return $this->_ci->db->affected_rows();
	}

    /**
     * @param $data
     * @param $where
     * @return mixed
     * @throws Exception
     */
	function update($data,$where) {
		$this->_ci->db->where($where);
		$this->_ci->db->db_debug = FALSE;
		$this->_ci->db->update($this->_self,$where);
		$this->_ci->db->db_debug = TRUE;
		$err = $this->_ci->db->error();
		if($err)
			throw(new Exception(json_encode($err)));
		return $this->_ci->db->affected_rows();
	}

    /**
     * @param null $select
     * @param null $include
     * @param string $where
     * @param null $order
     * @param null $limit
     * @return mixed
     */
	function select($select=null,$include=null,$where="1",$order=null,$limit=null) {
		//$query = "SELET $select FROM ".$this->_self;
		$select = is_array($select)?$select:array(is_null($select)?$this->_self.".*":$select);
		try {
			$leftJoinTables = array();
			$leftJoin = array();
			if($include=="*")
				foreach($this->_config->fields as $fldName=>$fldDef)
					if(property_exists($fldDef,"foreignKey"))
						array_push($leftJoinTables,$fldName);
			else
				$includes = $include;
			
			foreach($leftJoinTables as $fld) {
				$fkTbl = $this->_config->fields->$fld->foreignKey->table;
				echo $fkTbl;
				$fkFld = $this->_config->fields->$fld->foreignKey->field;
				
				$this->_ci->db->join($fkTbl,sprintf("%s.%s=%s.%s",$this->_self,$fld,$fkTbl,$fkFld));
				print_r(array_keys(get_object_vars($this->_big_conf->$fkTbl->fields)));
				
				foreach(array_keys(get_object_vars($this->_big_conf->$fkTbl->fields)) as $fkTblFld) {
					$select[] = "$fkTbl.$fkTblFld as $fkTbl"."_$fkTblFld";
				}
			}
			
			$this->_ci->db
					->select(implode($select,","))
					->from($this->_self)
					
					->order_by($order)
					->limit($limit);
			//echo $this->_ci->db->get_compiled_select();
			$this->_ci->db->db_debug = FALSE;
			$q = $this->_ci->db->get();
			$this->_ci->db->db_debug = TRUE;
			$err = $this->_ci->db->error();
			if($err) {
				echo "myerr";
				throw(new Exception(json_encode($err)));
			}
			//foreach
			print_r( $q);
			echo $this->_ci->db->last_query();
			return $q->result();
			
		}
		catch(Exception $e) {
			print_r($e);
		}
	}
}

