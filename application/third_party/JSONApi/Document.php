<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:25 PM
 */
namespace JSONApi;

/**
 * Class Document
 * @package JSONApi
 */
class Document extends  json_ready
{

    protected static $doc;

    protected $data;
    /**
     * @var array
     */
    protected $errors;
    /**
     * @var Meta
     */
    protected $meta;
    /**
     * @var string
     */
    protected $jsonapi;
    /**
     * @var Links
     */
    protected $links;

    /**
     * @var array
     */
    protected $includes;

    /**
     * @param null $data
     * @param Meta|null $meta
     * @param array|null $errors
     * @param Links|null $links
     * @param array|null $includes
     * @return Document
     */
    static function singleton($data=null, Meta $meta=null, array $errors=null, Links $links=null, array $includes=null)
    {
        if(isset(self::$doc))
            return self::$doc;

        self::$doc = new self();
        self::$doc->setData($data);
        if($meta)
            self::$doc->setMeta($meta);
        if($errors)
            self::$doc->setErrors($errors);
        if($links)
            self::$doc->setLinks($links);
        if($includes)
            self::$doc->setIncludes($includes);

        return self::$doc;
    }

    /**
     * @param $errors
     * @return Document
     */
    static function error_doc($errors)
    {
        if(!isset(self::$doc))
            self::$doc = new self();
        self::$doc->setErrors($errors);
        return self::$doc;
    }

    /**
     * @param \Exception $exception
     * @return Document
     */
    static function from_exception(\Exception $exception)
    {

        /**
         * @param $errors
         * @param \Exception $exception
         */
        function parseRecursive(&$errors,$exception) {
            $errors[] = Error::factory(
                    [
                    "message"=>$exception->getMessage(),
                    "code"=>$exception->getCode()
                ]);
            if($lnk=$exception->getPrevious())
                parseRecursive($errors,$lnk);
        }

        if(!isset(self::$doc))
            self::$doc = new self();
        $errors = [];
        parseRecursive($errors,$exception);
        self::$doc->setErrors($errors);

        return self::$doc;
    }


    private function __construct ()
    {

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
     * @return Document
     */
    public function &setData ($data)
    {

        if(is_null($data)) {
            $this->data = null;
            return $this;
        }

        switch(gettype($data)) {
            case "object":
                $attrs = Attributes::factory($data->attributes);
                $this->data = Resource::factory($data->type, $data->id, $attrs);
                break;
            case "array":

                $this->data = [];
                foreach ($data as $item) {
                    if(is_object($item)) {
                        $attrs = Attributes::factory($item->attributes);
                        $this->data[] = Resource::factory($item->type, $item->id, $attrs);
                    }

                }
                break;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrors ()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     * @return Document
     */
    public function &setErrors ($errors)
    {
        $this->errors = $errors;
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
     * @param Meta|null $meta
     * @return Document
     */
    public function &setMeta ($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJsonapi ()
    {
        return $this->jsonapi;
    }

    /**
     * @param mixed $jsonapi
     * @return Document
     */
    public function &setJsonapi ($jsonapi)
    {
        $this->jsonapi = $jsonapi;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncludes ()
    {
        return array_values($this->includes);
    }

    /**
     * @param mixed $includes
     * @return Document
     */
    public function &setIncluded (array $includes)
    {
        /**
         * @var Resource $resource
         */
        foreach ($includes as $resource) {
            $this->includes[$resource->getType()."_".$resource->getId()] = $resource;
        }
        return $this;
    }

    /**
     * @param Resource $resource
     * @return Resource|Resource
     */
    function &addInclude(Resource $resource)
    {
        $this->includes[$resource->getType()."_".$resource->getId()] = $resource;
        return $resource;
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
     * @return Document
     */
    public function &setLinks ($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @return array
     */
    public function json_data ()
    {

        $data = parent::json_data(); // TODO: Change the autogenerated stub
        if(!isset($data["errors"]) && !isset($data["data"]))
            return ["data"=>null];
        return $data;
    }

    /**
     * @param Error $error
     * @return Document
     */
    public function addError(Error $error)
    {
        if(!is_array($this->errors))
            $this->errors = [];
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @param $title
     * @param $code
     * @return Document
     */
    static function not_found($title,$code)
    {
        return self::singleton(null,null, [])
            ->addError(Error::factory(
            [
                "title" => $title,
                "code" => $code
            ]
        ));
    }

    private function setIncludes (array $includes)
    {
    }


}