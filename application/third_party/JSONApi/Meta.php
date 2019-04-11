<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 4:46 PM
 */

namespace JSONApi;


class Meta extends json_ready
{
    static function factory()
    {
        return new self();
    }
    private function __construct ()
    {
    }

}