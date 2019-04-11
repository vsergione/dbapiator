<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:32 PM
 */

namespace JSONApi;

class Relationship extends json_ready
{
    protected  $data;
    protected  $links;
    protected  $meta;
    private $type;

    /**
     * @param txt|array $data
     * @param null $links
     * @param null $meta
     * @return Relationship
     * @throws \Exception
     */
    static function factory($data, $links=null, $meta=null)
    {
        if(is_object($data)) {
            $type = "1:1";
        }
        if(is_array($data)) {
            $type = "1:n";
        }
        if(!isset($type))
            throw new \Exception("Invalid data type");

        return new self($type,$data,$links,$meta);
    }

    private function __construct ($type,$data,$links,$meta)
    {
        $this->setType($type)
            ->setData($data)
            ->setLinks($links)
            ->setMeta($meta);
    }

    public function &setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData ()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Relationship
     * @throws \Exception
     */
    public function &setData ($data)
    {
        if(!in_array(gettype($data),["object","array"]))
            throw new \Exception("Invalid Resource used as relationship");

        switch ($this->type) {
            case "1:1":
                //print_r($data);
                $this->data = new \stdClass();
                $this->data->id = $data->getId();
                $this->data->type = $data->getType();
                break;
            case "1:n":
                $this->data = [];
                foreach ($data as $item) {
                    $newObj = new \stdClass();
                    $newObj->id = $data->getId();
                    $newObj->type = $data->getType();
                    $this->data[] = $newObj;
                }
                break;
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
     * @param null|Links $links
     * @return Relationship
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
     * @return Relationship
     */
    public function &setMeta ($meta)
    {
        $this->meta = $meta;
        return $this;
    }




}