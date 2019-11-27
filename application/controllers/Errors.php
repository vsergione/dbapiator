<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/18/18
 * Time: 12:19 PM
 */

class Errors extends CI_Controller
{
    function __construct ()
    {
        parent::__construct();
    }

    function index()
    {
        echo "404";
    }

    function not_found()
    {
        http_response_code(404);
        echo "Resource not found";
    }

    function invalid_req($resource,$verb)
    {
        echo "invalid req $verb on $resource";
    }

}