<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 7/25/18
 * Time: 2:57 PM
 */

require_once(APPPATH."/core/MY_AdminPagesController.php");

class Login extends MY_AdminPagesController
{

    function __construct ()
    {
        parent::__construct();
    }

    function index()
    {
        $this->load->view("admin/login",[
            "basePath"=>$this->basePath,
        ]);
    }
}