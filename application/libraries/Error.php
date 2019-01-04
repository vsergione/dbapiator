<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RestError {
	public $id, $status,$code, $title,$detail,$source;
	function __construct($id,$status=null,$code=null,$title=null,$detail=null,$source=false) {
		$this->id = $id;
		$this->status = $status;
		$this->code = $code;
		$this->title = $title;
		$this->detail = $detail;
		if($source) {
			$trc = debug_backtrace();
			$this->source = json_decode(sprintf(
										"{'file':'%s','file':'%s','file':'%s'}",
										$trc[0]["file"],
										$trc[0]["file"],
										$trc[0]["file"]));
		}
	}
}

$myErrors = array(
	1 => new RestError(1,204)
);