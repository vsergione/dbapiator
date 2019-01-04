<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 2/5/18
 * Time: 3:33 PM
 */

namespace JSONApi;

class Document{
    /**
     * @var Resource
     */
    public $data;
    /**
     * @var Meta
     */
    public $meta;
    /**
     * @var Link
     */
    public $links;
    /**
     * @var Errors[]
     */
    public $errors;
    /**
     * @var array
     */
    public $included;

    /**
     * @var JSONApi
     */
    public $jsonApi;

    /**
     * Document constructor.
     * @param array $options
     */
    public function __construct($options)
    {
        foreach ($options as $option) {
            switch (get_class($option)) {
                case "JSONApi\Errors":
                    break;
                case "JSONApi\ResourceLinkage":
                    break;
                case "JSONApi\Links":
                    break;
                case "JSONApi\Meta":
                    break;
                case "JSONApi\Included":
                    break;
                case "JSONApi\JSONApi":
                    break;
            }
        }
    }
}

/**
 * Class ResourceObject
 * “Resource objects” appear in a JSON API document to represent resources.
 * @package JSONApi
 */
class ResourceObject {
    /**
     * @var string|int
     */
    public $id;
    /**
     * @var string
     */
    public $type;
    /**
     * @var object
     */
    public $attributes;
    /**
     * @var object
     */
    public $relationships;
    /**
     * @var
     */
    public $links;
    /**
     * @var Meta
     */
    public $meta;

    public function __construct ($id,$type,$attributes=null,$relationships=null,$links=null,$meta=null)
    {
        if(empty($type)) {
            throw new Exception('Null type not allowed');
        }
        $this->id = $id;
        $this->type = $type;
    }
}

class Attributes implements \Iterator {
    /**
     * @var array
     */
    private $collection;

    private $cursor;
    private $keys;


    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current ()
    {
        return $this->collection[$this->keys[$this->cursor]];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next ()
    {
        $this->cursor++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key ()
    {
        return $this->keys[$this->cursor];
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid ()
    {
        return $this->cursor<count($this->keys) && $this->cursor>=0;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind ()
    {
        $this->cursor = 0;
    }

    function __get ($name)
    {
        return array_key_exists($name,$this->collection)?$this->collection[$name]:null;
    }

    function __set ($name, $value)
    {
        // TODO: Implement __set() method.
        $this->collection[$name] = $value;
        $this->keys = array_keys($this->collection);
        $this->rewind();
    }

    /**
     * Attributes constructor.
     * @param $properties array
     */
    function __construct ($properties)
    {
        foreach ($properties as $name=>$value) {
            if(gettype($value)=="object" && get_class($value)!=="Document") {
            }
        }
    }
}

/**
 * Class Links
 * @package JSONApi
 */
class Links {

}
/**
 * Class JSONApi
 * an object describing the server’s implementation
 * @package JSONApi
 */
class JSONApi{
    /**
     * @var string
     */
    public $version;

    public function __construct ($version)
    {
        if($version)
            $this->version = $version;
    }
}

/**
 * Class Errors
 * @package JSONApi
 * Error objects provide additional information about problems encountered while performing an operation.
 * Error objects MUST be returned as an array keyed by errors in the top level of a JSON API document.
 */
class Errors {
    /**
     * @var int
     * a unique identifier for this particular occurrence of the problem.
     */
    public $id;
    /**
     * @var string
     * the HTTP status code applicable to this problem, expressed as a string value.
     */
    public $status;
    /**
     * @var string
     * an application-specific error code, expressed as a string value.
     */
    public $code;
    /**
     * @var string
     * a short, human-readable summary of the problem that SHOULD NOT change
     * from occurrence to occurrence of the problem, except for purposes of localization.
     */
    public $title;
    /**
     * @var string
     * a human-readable explanation specific to this occurrence of the problem. Like title, this field’s value can be localized.
     */
    public $detail;
    /**
     * @var string
     * an object containing references to the source of the error, optionally including any of the following members:
     *  -    pointer: a JSON Pointer [RFC6901] to the associated entity in the request document
     *       [e.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute].
     *  -    parameter: a string indicating which URI query parameter caused the error.
     */
    public $source;
    /**
     * @var string
     * a meta object containing non-standard meta-information about the error.
     */
    public $meta;

}

/**
 * Class Meta
 * @package JSONApi
 * Where specified, a meta member can be used to include non-standard meta-information.
 * The value of each meta member MUST be an object (a “meta object”).
 *
 * Eg.:
 * {
 *        "meta": {
 *            "copyright": "Copyright 2015 Example Corp.",
 *            "authors": [
 *                "Yehuda Katz",
 *                "Steve Klabnik",
 *                "Dan Gebhardt",
 *                "Tyler Kellen"
 *            ]
 *      },
 *      "data": {
 *             // ...
 *      }
 * }
 */
class Meta{

}

/**
 * Class Link
 * @package JSONApi
 * can have any number of members. Any of the members can be either a string representing an URL
 * or a LinkObject
 */
class Link {
    /**
     * @var string|LinkObject
     */
    private $self;
    /**
     * @var string|LinkObject
     */
    private $related;
}

/**
 * Class LinkObject
 * @package JSONApi
 */
class LinkObject {
    /**
     * @var string
     * a string containing the link’s URL.
     */
    public $href;
    /**
     * @var Meta
     * a meta object containing non-standard meta-information about the link.
     */
    public $meta;
}
