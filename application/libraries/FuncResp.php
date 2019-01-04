<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/2/18
 * Time: 4:26 PM
 */

class FuncResp
{
    private $result;
    private $details;

    public function __get ($name)
    {
        // TODO: Implement __get() method.
        switch ($name) {
            case "result":
                return $this->result;
            case "details":
                return $this->details;
            default:
                return null;
        }
    }

    private function __construct ($result,$details)
    {
        $this->result = $result;
        $this->details = $details;
    }

    static function issue($result=true,$details=null)
    {
        return new FuncResp($result,$details);
    }
}