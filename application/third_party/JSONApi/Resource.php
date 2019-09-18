<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:28 PM
 */

namespace JSONApi;


class Resource extends json_ready
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var Attributes
     */
    protected $attributes;
    /**
     * @var Relationship
     */
    protected $relationships;
    /**
     * @var Links
     */
    protected $links;
    /**
     * @var Meta
     */
    protected $meta;

    /**
     * @param object $data
     * @return Resource
     * @throws \Exception
     */
    static function factory($data)
    {
        if(!is_object($data))
            throw new \Exception("Invalid data parameter when creating a new Resource: not an object");
        if(!isset($data->type))
            throw new \Exception("Invalid data parameter when creating a new Resource: type property missing");
//        if(!isset($data->id))
//            throw new \Exception("Invalid data parameter when creating a new Resource: id property missing");
        if(!isset($data->attributes))
            throw new \Exception("Invalid data parameter when creating a new Resource: attributes property missing");
        if(!is_object($data->attributes))
            throw new \Exception("Invalid data parameter when creating a new Resource: attributes property not an object");

        $res = new self($data->type,$data->id);
        $res->setAttributes(Attributes::factory($data->attributes));

        if(!isset($data->relationships) || !is_object($data->relationships))
            return $res;

        foreach($data->relationships as $relName=>$relData) {
            $res->addRelationship($relName,$relData);
        }

        return $res;
    }

    /**
     * Resource constructor.
     * @param $type
     * @param $id
     */
    private function __construct ($type,$id)
    {
        $this->id = $id;
        $this->type = $type;
    }



    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Resource
     */
    public function &setId ($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType ()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return Resource
     */
    public function &setType ($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes ()
    {
        return $this->attributes;
    }

    /**
     * @param Attributes $attributes
     * @return Resource
     */
    public function &setAttributes ($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelationships ()
    {
        return $this->relationships;
    }

    /**
     * @param $name
     * @param mixed $data
     * @return Resource
     * @throws \Exception
     */
    public function &addRelationship($name, $data)
    {

        if(empty($name))
            throw new \Exception("Not allowed: empty relation name.");

        // initializes relationships
        if(!isset($this->relationships)) {
            $this->relationships = new Relationships();
        }

        if(!isset($this->relationships->$name)) {
            $this->relationships->$name = Relationship::factory($data,Links::factory([
                "self"=>Document::singleton()->get_baseUrl()."/".$this->type."/".$this->id."/relationships/".$name,
                "related"=>Document::singleton()->get_baseUrl()."/".$this->type."/".$this->id."/".$name
            ]));
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinks ()
    {
        return $this->links;
    }

    /**
     * @param mixed $links
     * @return Resource
     */
    public function &setLinks ($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeta ()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     * @return Resource
     */
    public function &setMeta ($meta)
    {
        $this->meta = $meta;
        return $this;
    }


}