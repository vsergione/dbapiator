<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 11/1/18
 * Time: 4:34 PM
 */

abstract class ap {
    protected $v;

    function __construct ()
    {
        $this->v = __CLASS__;
    }
}

class cc extends ap {
    function __construct ()
    {
        parent::__construct();
    }

    function test() {
        echo $this->v;
    }
}

$c = new cc();
$c->test();