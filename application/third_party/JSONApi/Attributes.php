<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:33 PM
 */

namespace JSONApi;

/**
 * Class Attributes
 * @package JSONApi
 */
class Attributes
{
    protected  $attributes = [];

    static function factory($attributes)
    {
        return new Attributes($attributes);
    }

    function setAttributes(Array $array)
    {
        $this->attributes = $array;
    }

    function getAttributes()
    {
        return $this->attributes;
    }


    private function __construct ($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return \stdClass
     */
    public function extract_includes()
    {
        $includes = new \stdClass();
        foreach ($this->attributes as $name=>$value) {
            if(is_object($value)) {
                $includes->$name = $value;
                unset($this->attributes->$name);
            }
        }
        return $includes;
    }

    public function __set ($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __get ($name)
    {
        if(!isset($this->attributes->$name))
            throw new \Exception("Attribute $name does not exist");
    }

    function json_data()
    {
        return $this->attributes;
    }
}