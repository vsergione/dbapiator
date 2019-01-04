<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 11/2/18
 * Time: 8:40 AM
 */

class Home extends CI_Controller
{
    function index()
    {
        http_response_code(301);
        header("Location: /proteus/admin/dashboard");
    }
}