<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:38 PM
 */

namespace JSONApi;


class Relationships extends json_ready
{
    protected  $rels = [];

    static function factory()
    {
        return new Relationships();
    }

    private function __construct ()
    {
    }

    function addRelation($name,Relationship $relation)
    {
        $this->rels[$name] = $relation;

    }

    function getRelation($name)
    {
        if(isset($this->rels[$name]))
            return $this->rels[$name];
        throw new \Exception("Invalid relation $name");
    }

    function json_data ()
    {
        if(empty($this->rels))
            return null;
        $ret = [];
        foreach ($this->rels as $key=>$val) {
            //echo get_class($val)." - ".$key."\n";
            //print_r($val);

            $ret[$key] = $val->json_data();
        }
        return $ret;
    }

}