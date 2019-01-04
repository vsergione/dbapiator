<?php
/**
 * Creates and sends a HTTP response. Singleton implementation
 * User: vsergiu
 * Date: 10/3/18
 * Time: 11:59 AM
 */
class HttpResp{
    private static $self=null;

    private $header = [
        "Content-Type"=>"text/html",
        "Access-Control-Allow-Credentials"=>"true",
        "Pragma"=>"no-cache"
    ];

    private $responseCode = 200;
    private $body;

    private function __construct ()
    {

    }

    /**
     * set header
     * @param string $name header name or complete header
     * @param string|int $value header value or null when $name contains complete header
     * @return $this
     */
    public function &header($name, $value=null)
    {
        if(is_null($value) && strpos(":",$name)!=-1) {
            list($name,$value) = explode(":",$name);
        }
        $this->header[$name] = $value;
        return $this;
    }

    /**
     * set body
     * @param string $value
     * @return HttpResp $this
     */
    public function &body($value)
    {
        $this->body = $value;
        return $this;
    }

    /**
     * set response code
     * @param string|int $code HTTP response code
     * @return HttpResp $this
     */
    public function &response_code($code)
    {
        $this->responseCode = $code;
        return $this;
    }

    /**
     * output the response
     * @return bool
     */
    public function output()
    {
        http_response_code($this->responseCode);
        foreach ($this->header as $header=>$value)
        {
            if(!is_null($value)) {
                header("$header: $value");
            }
        }
        echo $this->body;
        return true;
    }

    /**
     * set no caching
     * @return HttpResp
     */
    public function &no_cache()
    {
        return $this
            ->header("Cache-Control","no-cache, no-store, must-revalidate")
            ->header("Expires","0");
    }

    /**
     * set allowed headers
     * @param $headers
     * @return mixed
     */
    public function &allow_headers($headers)
    {
        return $this->header("Access-Control-Allow-Headers",$headers);
    }

    /**
     * shortcut for allowing all headers
     * @param $headers
     * @return mixed
     */
    public function &allow_all_headers($headers)
    {
        return $this->allow_headers($headers);
    }

    /**
     * set allowed origins
     * @param $origin
     * @return HttpResp
     */
    public function &allow_origin($origin)
    {
        return $this->header("Access-Control-Allow-Origin",$origin);
    }

    /**
     * set content type
     * @param $type
     * @return HttpResp
     */
    public function &content_type($type)
    {
        return $this->header("Content-Type",$type);
    }

    /**
     * shorthand for allowing all origins
     * @return HttpResp
     */
    public function &allow_all_origin()
    {
        return $this->allow_origin("*");
    }

    /**
     * set allowed methods
     * @param $methods
     * @return HttpResp
     */
    public function &allow_methods($methods)
    {
        return $this->header("Access-Control-Allow-Methods",$methods);
    }

    /**
     * shorthand for allowing all methods
     * @return HttpResp
     */
    public function &allow_all_methods()
    {
        return $this->allow_methods("*");
    }

    /**
     * singleton method for getting the response instance
     * @return HttpResp
     */
    static function init()
    {
        if(isset(HttpResp::$self))
            return HttpResp::$self;

        return new HttpResp();
    }

    /**
     * shorthand method for making a quick response
     * @param string|int $code HTTP response code
     * @param $contentType HTTP content type
     * @param $body HTTP response body
     * @return bool
     */
    static function quick($code,$contentType,$body=null)
    {
        $resp = HttpResp::init();
        $resp
            ->response_code($code)
            ->content_type($contentType)
            ->body($body)
            ->output();
        return true;
    }


    /**
     * @param $code
     * @param $contentType
     * @param null $body
     * @param null $headers
     * @return bool
     */
    static private function ctype_out($code,$contentType,$body=null,$headers=null)
    {
        if(is_null($headers)) {
            HttpResp::quick($code, $contentType,  $body);
            return true;
        }

        $resp = HttpResp::init();
        if(is_array($headers))
            foreach ($headers as $header=>$value)
                $resp->header($header,$value);
        else
            $resp->header($headers);
        $resp->content_type($contentType)->body($body)->output();
        return true;
    }

    static function not_authorized($body=null)
    {
        HttpResp::init()->response_code(401)->body($body)->output();
        return true;
    }

    /**
     * helper method for creating a response for json cType. JSON encodes the body when not encoded already
     * @param string|int $code HTTP response code
     * @param string|array|object $body
     * @param array|string $headers assoc array of key->value or string containing header string
     * @return bool
     */
    static function json_out($code,$body=null,$headers=null)
    {
        if(is_array($body) || is_object($body))
            $body = json_encode($body,JSON_PRETTY_PRINT);

        HttpResp::ctype_out($code,"application/json",$body,$headers);
        return true;
    }

    /**
     * shorthand method for generating & sending an XML response
     * @param string|int $code HTTP response code
     * @param string|array|object $body
     * @param string|array|null $headers
     * @return bool
     */
    static function xml_out($code,$body=null,$headers=null)
    {
        // TODO: to implement
        return true;
    }

    /**
     * shorthand method for generating & sending a test response
     * @param string|int $code HTTP response code
     * @param string $body
     * @return bool
     */
    static function text_out($code,$body=null)
    {
        HttpResp::quick($code,"text/plain",$body);
        return true;
    }

    /**
     * shorthand method for generating & sending a html response
     * @param string|int $code HTTP response code
     * @param string $body
     * @return bool
     */
    static function html_out($code,$body=null)
    {
        HttpResp::quick($code,"text/html",$body);
        return true;
    }

    /**
     * shorthand method for performing a redirect
     * @param string $location
     * @param string|int $code HTTP response code; defaults to 301 Moved permanently
     * @return bool
     */
    static function redirect($location,$code=301)
    {
        HttpResp::init()
            ->response_code($code)
            ->header("Location",$location)
            ->output();
        return true;
    }
}

