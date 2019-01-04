<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 7/25/18
 * Time: 2:57 PM
 */

require_once(APPPATH."/core/MY_AdminPagesController.php");


/**
 * Class Launchpad
 * @property CI_Input $input
 * @property CI_Loader $load
 */
class Dashboard extends MY_AdminPagesController
{
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
            "currentPage"=>"dashboard",
            "title"=>"DBApiator - APIs list",
            "apiRoot"=>$this->baseUrl."/dbapiator/"
        ];
        $this->load->view("admin/launchpad",$data);
        return;
    }

}

//{
//    /**
//     * @var MyToken
//     */
//    private $token;
//
//    /**
//     * BASE URL relative to DOCUMENT_ROOT
//     * @var string
//     */
//    private $basePath;
//    private $authorizationToken=null;
//    private $data = [
//        "baseUrl" => "/proteus",
//        "apiRoot"=> "https://develhost/proteus/api/v1/databases",
//
//    ];
//    /**
//     * @var bool
//     */
//    private $isAuth;
//
//    function __construct ()
//    {
//
//        parent::__construct();
//        $this->basePath = "/proteus";
//        $this->baseUrl = "https://develhost/proteus";
//
//        $this->init_token();
//        if(!$this->token->info()) {
//            HttpResp::not_authorized() && die();
//        }
//    }
//
//    /**
//     *
//     */
//    function init_token()
//    {
//        $this->load->config("oauth2",true);
//        $config = $this->config->item("oauth2");
//        $dbDrv = $this->load->database($config["authDbConn"],true);
//        if(!$dbDrv)
//            die("Invalid DB connection");
//
//        $this->token = new MyToken($dbDrv,$config["tables"],$config["oauth2Paras"],$config["grantParas"]);
//    }
//
//    /**
//     *
//     */
//    function index()
//    {
//        if($this->isAuth)
//            HttpResp::redirect($this->data["baseUrl"]."/admin/launchpad/dashboard") || die();
//    }
//
//
//    /**
//     *
//     */
//    function dashboard()
//    {
//        //header("Location: /proteus/launchpad/dashboard");
//        $this->load->view("admin/launchpad",[
//            "title"=>"Dashboard",
//            "cPage"=>"dashboard",
//            "basePath"=>$this->basePath,
//            "token"=>$this->authorizationToken?"?_token=".$this->authorizationToken:""]);
//    }
//
////
////    /**
////     * entry point for dbapiator pages
////     * when no API name provided it just displays the list of defined APIs
////     * @return mixed
////     */
////    function dbapiator($name=null)
////    {
////        if($name!==null) {
////            $this->load->view("launchpad/template",array_merge($this->data,[
////                "content"=>$this->load->view("launchpad/dbapiator/all",["apiRoot"=>$this->data["apiRoot"],"appName"=>$name],true),
////                "scripts"=>$this->load->view("launchpad/dbapiator/all.js.html",["apiRoot"=>$this->data["apiRoot"]],true),
////                "cPage"=>"dbapiator",
////            ]));
////            return;
////        }
////
////        $content = $this->load->view("launchpad/dbapiator/dbs_list",["apiRoot"=>$this->data["apiRoot"]],true);
////        $scripts = $this->load->view("launchpad/dbapiator/dbs_list.js.html",[],true);
////        $this->load->view("launchpad/template",array_merge($this->data,[
////            "scripts"=>$scripts,
////            "content"=>$content,
////            "cPage"=>"dbapiator",
////        ]));
////        return;
////    }
//
//
//
//}
