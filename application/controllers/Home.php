<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 11/2/18
 * Time: 8:40 AM
 */

/**
 * Class Home
 * @property CI_Loader $load
 */
class Home extends CI_Controller
{
    function index()
    {
        $this->load->view("welcome_message");
    }
}