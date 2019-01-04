<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/13/18
 * Time: 12:20 PM
 */

namespace Apiator\DataTypes;

class Connection
{

    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $database;
    /**
     * @var array
     */
    private $settings;

    /**
     * @return mixed
     */
    public function getHost ()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost ($host)
    {
        $this->host = $host;
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
     */
    public function setType ($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getUsername ()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername ($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword ($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getDatabase ()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase ($database)
    {
        $this->database = $database;
    }

    /**
     * @return mixed
     */
    public function getSettings ()
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings ($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return bool
     */
    public function isValid ()
    {
        return $this->valid;
    }


    private $valid=false;

    public function __construct ($array)
    {
        if(!is_array($array))
            return;

        $this->database = $array["database"];
        $this->username = $array["username"];
        $this->password = $array["password"];
        $this->host = $array["host"];
        $this->type= $array["type"];
        $this->settings= $array["settings"];
        $this->valid = true;
    }

}