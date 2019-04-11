<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:46 PM
 */

namespace JSONApi;


class Error extends json_ready
{
    /**
     * @var string
     */
    private $title;
    /**
     * @var Links
     */
    private $links;
    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $detail;
    /**
     * @var Meta
     */
    private $meta;

    static function factory()
    {
        return new self();
    }
    private function __construct ()
    {
    }


    /**
     * @return mixed
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Error
     */
    public function &setTitle ($title)
    {
        $this->title = $title;
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
     * @return Error
     */
    public function &setLinks (Links $links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus ()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Error
     */
    public function &setStatus ($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode ()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return Error
     */
    public function &setCode ($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDetail ()
    {
        return $this->detail;
    }

    /**
     * @param mixed $detail
     * @return Error
     */
    public function &setDetail ($detail)
    {
        $this->detail = $detail;
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
     * @return Error
     */
    public function &setMeta (Meta $meta)
    {
        $this->meta = $meta;
        return $this;
    }

}