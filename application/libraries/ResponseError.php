<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class ResponseError {
	public $id;
	public $links;
	public $status;
	public $code;
	public $title;
	public $detail;
	
	private function __construct($id,$code,$title,$status,$detail){
		$this->code = $code;
		$this->detail = $detail;
		$this->id = $id;
		$this->status = $status;
		$this->title = $title;
	}
	public static function mk($id,$code,$title,$status,$detail){
		return new self($id,$code,$title,$status,$detail);
	}
	
	function __get($name) {
		if(property_exists($this,$name))
			return $this->$name;
	}
}