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
     * @var Relationships
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
     * @param string $type
     * @param int $id
     * @param Attributes|null $attributes
     * @param Relationships|null $relationships
     * @param Links|null $links
     * @param Meta|null $meta
     * @return Resource
     */
    static function factory($type,$id,Attributes $attributes=null,Relationships $relationships=null,Links $links=null,Meta $meta=null)
    {
        $res = new self($type,$id,$attributes);
        if($relationships)
            $res->setRelationships($relationships);
        if($links)
            $res->setLinks($links);
        if($meta)
            $res->setMeta($meta);

        return $res;

    }

    private function __construct ($type,$id,$attributes)
    {
        $this->id = $id;
        $this->type = $type;
        $this->attributes = $attributes;
        $rels = $this->attributes->extract_includes();
        $doc = Document::singleton();

        foreach ($rels as $relationName=>$relationData) {
            // add to includes
            $includedResource = $doc->addInclude(Resource::factory($relationData->type,$relationData->id,Attributes::factory($relationData->attributes)));
            if(!$this->relationships)
                $this->relationships = Relationships::factory();

            $relation = Relationship::factory($includedResource);
            $this->relationships->addRelation($relationName,$relation);
        }
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
     * @param mixed $attributes
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
     * @param mixed $relationships
     * @return Resource
     */
    public function &setRelationships ($relationships)
    {
        $this->relationships = $relationships;
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