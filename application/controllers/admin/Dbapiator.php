<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 11/1/18
 * Time: 4:30 PM
 */

require_once(APPPATH."/core/MY_AdminPagesController.php");

/**
 * Class Dbapiator
 * @property CI_Loader $load
*/
class Dbapiator extends MY_AdminPagesController
{
    protected $notAuth401Body = "Not authorized";

    function __construct ()
    {
        parent::__construct();
        $this->not_auth_exit($this->load->view("admin/not_auth",["basePath"=>$this->basePath],true));
    }

    function index()
    {
        $data = [
            "baseUrl"=>$this->baseUrl,
            "basePath"=>$this->basePath,
            "accessToken"=>$this->oauth2AccessToken,
            "userId"=>$this->oauth2UserId,
            "currentPage"=>"dbapiator/list",
            "title"=>"DBApiator - APIs list",
            "apiRoot"=>$this->baseUrl."/dbapiator/"
        ];
        $this->load->view("admin/launchpad",$data);
        return;
    }

    function api($name,$section=null) {
        if(!$section)
            $section = "summary";
        $data = [
            "baseUrl"=>$this->baseUrl,
            "basePath"=>$this->basePath,
            "accessToken"=>$this->oauth2AccessToken,
            "userId"=>$this->oauth2UserId,
            "currentPage"=>"dbapiator/details",
            "apiName"=>$name,
            "title"=>"DBApiator - APIs list",
            "apiRoot"=>$this->baseUrl."/dbapiator/"
        ];
        $this->load->view("admin/launchpad",$data);
        return;
    }
}