<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Custom Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/general/controllers.html
 */
interface RestIface
{
	public function _get($pathComponents);
	public function _post($pathComponents, $postData);
	public function _put($pathComponents,$postData);
	public function _patch($pathComponents,$postData);
	public function _delete($pathComponents,$postData);
	public function _options($pathComponents,$postData);
}

/**
 * Class MY_RestController
 * @property CI_Loader load
 * @property CI_Config config
 * @property MyStorage mystorage
 * @property OAuth2\Server oauthServer
 * @property CI_Input input
 */
abstract class MY_RestController extends CI_Controller implements RestIface{
    public function __construct()
    {
		parent::__construct();
		$this->load->config("apiator",true);
		$config =$this->config->item("apiator");
        if($config["force_ssl"] && !isset($_SERVER["HTTPS"])) {
            header("HTTP/1.1 403 Forbidden");
            header("Content-type: application/vnd.ap0i-json");
            die(json_encode(["error"=>"Insecure requests are forbidden. Please use SSL"]));
        }

        //if(!isset($_GET["noauth"])) $this->doAuth();
	}

	private function doAuth() {
        $this->load->config("oauth2", TRUE);
        $config = $this->config->item("oauth2");

        $paras = [
            "db" => $this->load->database($config["dsn"], true),
            "tables" => $config["tables"]
        ];
        $this->load->library("OAuth2/Storage/MyStorage", $paras);

        $this->oauthServer = new \OAuth2\Server($this->mystorage);
        if (!$this->oauthServer->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            http_response_code(401);
            header("Content-type: application/json");
            die("{'error':'Not authorized'}");
        }
    }

	/**
	 * default method to be called
	 *
	 * @return void
	 */
    public function index() {

        $args = func_get_args();
		$httpMethodOverride = $this->input->get_request_header("X-HTTP-Method-Override");
		$reqMethod = is_null($httpMethodOverride)?$this->input->method():strtolower($httpMethodOverride);
	
        if(array_key_exists("debug",$_GET)) echo "Method: $reqMethod\n";
		switch($reqMethod) {
			// get
			case "get":
				$this->_get($args);
				break;
			// create
			case "post":
				$this->_post($args,json_decode($this->input->raw_input_stream));
				break;
			// update
			case "put":
				$this->_put($args,json_decode($this->input->raw_input_stream));
				break;
			// update
			case "patch":
				$this->_patch($args,json_decode($this->input->raw_input_stream));
				break;
			// delete
			case "delete":
				$this->_delete($args,json_decode($this->input->raw_input_stream));
				break;
			case "options":
				$this->_options($args,json_decode($this->input->raw_input_stream));
				break;
		}
	}

    public function _get($pathComponents) {
	    header("Content-type: text/plain");
        print_r($pathComponents);
        print_r($this->input->get());
    }

    public function _post($pathComponents, $postData) {
        print_r($pathComponents);
        print_r($this->input->get());
        print_r($postData);
    }

    public function _put($pathComponents,$postData) {
        print_r($pathComponents);
        print_r($this->input->get());
		print_r($postData);
    }

    public function _patch($pathComponents,$postData) {
        print_r($pathComponents);
        print_r($this->input->get());
		print_r($postData);
    }
    
    public function _delete($pathComponents,$postData) {
        print_r($pathComponents);
        print_r($this->input->get());
        print_r($postData);
    }
	
	public function _options($pathComponents,$postData) {
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, PATCH");
        /*
        else {

            print_r($pathComponents);
            print_r($this->input->get());
            print_r($postData);

        }
        */
    }

}
