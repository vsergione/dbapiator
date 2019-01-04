<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/2/18
 * Time: 4:19 PM
 */


require APPPATH."/libraries/Response.php";
class sss
{
    public $var;
    function __construct ()
    {
        $this->var = 1;
    }

    function &get_ref()
    {
        return $this;
    }
}
/**
 * Class Mytest
 * @property CI_Loader load
 * @property CI_Config config
 * @property CI_Session session
 */
class Mytest extends CI_Controller

{
    function __construct ()
    {
        parent::__construct();
        $this->load->library("session");
        if($this->session->has_userdata("counter"))
            $this->session->counter++;
        else
            $this->session->set_userdata("counter",0);
        session_write_close();
    }

    function respond_something()
    {
        return Response::make(true);
    }

    function ajx_resp()
    {
        echo $this->session->counter;
    }

    function index()
    {
        $this->load->view("test",[]);
    }

}

