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

    /**
     * @param $name
     * @param Relationship|null $relation
     */
    function addRelation($name,$relation)
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
            if(is_null($val))
                $ret[$key] = $val;
            else
                $ret[$key] = $val->json_data();

        }
        return $ret;
    }

}