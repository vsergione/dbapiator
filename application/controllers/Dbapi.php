<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/18/18
 * Time: 12:05 PM
 */
require_once(APPPATH."libraries/HttpResp.php");
require_once(APPPATH."third_party/Apiator/Autoloader.php");
\Apiator\Autoloader::register();

/**
 * Class Dbapi controller for processing the API calls for manipulating data in the database
 * @property CI_Config config
 * @property CI_Loader load
 * @property CI_Input input
 */
class Dbapi extends CI_Controller
{
    private $apisDir;
    private $myConfig;
    public $baseDomain;
    public $apiId;

    /**
     * @var CI_DB_pdo_driver
     */
    private $apiDb;

    /**
     * @var \Apiator\DBApi\Datamodel
     */
    private $apiDm;

    /**
     * @var array
     */
    private $apiSettings;

    /**
     * @var \Apiator\DBApi\Records
     */
    private $recs;

    function __construct ()
    {
        parent::__construct();

        $this->config->load("apiator",true);
        $this->myConfig = $this->config->item("apiator");
        $this->apisDir = $this->config->item("allApisDir");
        $this->baseDomain = $this->config->item("base_domain");

        $this->load->helper("my_utils");
        header("Access-Control-Allow-Origin: *");
    }

    /**
     * reads API configuration file, connects to the database and initializes the API DataModel (structure)
     * initializes internal objects:
     * - apiDm: DataModel
     * - apiDb: database connection
     */
    private function _init()
    {
        $arr = (explode($this->baseDomain,$_SERVER["SERVER_NAME"]));
        if(count($arr)!==2)
            HttpResp::redirect("https://launchpad.apiator");
            //HttpResp::text_out(400,"Invalid request");
        $this->apiId = $arr[0];

        $apiConfigDir = $this->apisDir."/$this->apiId";

        if(!is_dir($apiConfigDir)) {
            // API Not found
            // TODO: log to admin log that API not found
            HttpResp::text_out(404,"API $this->apiId not found");
        }

        // load structure
        $structure = require($apiConfigDir."/structure.php");
        if(!isset($structure)) {
            // Invalid API config
            // TODO: log error: wrong api config
            HttpResp::server_error("Invalid API configuration");
        }

        // load connection
        $connection = require($apiConfigDir."/connection.php");
        if(!isset($connection)) {
            HttpResp::server_error("Invalid database config");
        }

        // load permissions
        // todo: depending on the API client, load the appropriate permissions file
        $apiKey = $this->input->get("api_key")?$this->input->get("api_key"):$this->input->server("HTTP_X_API_KEY");
        if(empty($apiKey)) {
            $profileFIle = "/profiles/default.php";
        }
        else {
            $profileFIle = "/clients/$apiKey.php";
        }

        /** @noinspection PhpIncludeInspection */
        $permissions = require($apiConfigDir.$profileFIle);
        if(!isset($permissions)) {
            HttpResp::server_error("Invalid API permissions");
        }

        //
        $settings = require($apiConfigDir."/settings.php");
        if(!isset($settings)) {
            HttpResp::server_error("Invalid API settings");
        }

        $apiCfg = array_merge_recursive($permissions,$structure);

        // connects to DB server
        $dbConf = array(
            'dsn'	=> '',
            'hostname' => $connection["db_host"],
            'username' => $connection["db_user"],
            'password' => $connection["db_pass"],
            'database' => $connection["db_schema"],
            'dbdriver' => $connection["db_type"],
            'dbprefix' => '',
            'pconnect' => FALSE,
            'db_debug' => false,
            'cache_on' => FALSE,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            'encrypt' => FALSE,
            'compress' => FALSE,
            'stricton' => FALSE,
            'failover' => array(),
            'save_queries' => TRUE
        );

        /**
         * @var CI_DB_pdo_driver db
         */
        $db = $this->load->database($dbConf,TRUE);
        if(!$db) {
            // TODO log DB connection failed
            HttpResp::service_unavailable("Failed to connect to database");
        }

        // initializes DM with structure fetched from $apiCfg
        $dm = Apiator\DBApi\Datamodel::init($apiCfg);
        if(!$dm) {
            // TODO log wrong config file
            HttpResp::server_error("Invalid API datamodel");
        }

        $this->apiDb = $db;
        $this->apiDm = $dm;
        $this->apiSettings = $settings;

        // initialize recs
        $this->recs = \Apiator\DBApi\Records::init($this->apiDb,$this->apiDm);
        if(!$this->recs) {
            // TODO log unable to initialize records navigator class
            HttpResp::server_error("Invalid API config");
        }
    }

    function dm()
    {
        $this->_init();
        HttpResp::json_out(200,$this->apiDm->get_dataModel());
    }

    function swagger()
    {
        $this->_init();
        $dm = $this->apiDm->get_dataModel();
        $oas =  [
            "swagger" => "2.0",
            "info" => [
                "description" => "Demoblog DB API",
                "version" => "1.0.0",
                "title" => "Demoblog",
                "contact" => [
                    "name" => "Sergiu Voicu",
                    "email" => "svoicu@softaccel.net"
                ],
                "license" => [
                    "name" => "GPL"
                ]
            ],
            "host" => "5cbaed2eb9a51.api.apiator",
            "basePath" => "/v2",
            "schemes" => [ "https" ],
            "consumes" => [ "application/vnd.api+json" ],
            "produces" => [ "application/vnd.api+json" ],
            "paths"=>[]
        ];

        foreach ($dm as $resName=>$resDef) {
            // /resname
            $oas["paths"]["/$resName"] = [];
            $oas["paths"]["/$resName"]["get"] = [
                "summary" => "Get $resName records list",
                "parameters" => []
            ];
            $oas["paths"]["/$resName"]["get"]["parameters"][] = [
                "name" => "fields[$resName]",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "descriptions" => "Comma separated list of '$resName' field names",
                "x-example" => join(",",
                    array_merge(
                        array_keys($resDef["fields"]),
                        array_keys(
                            array_filter($resDef["relations"],function ($var){
                                return $var["type"]=="inbound";
                            })
                        )
                    )
                )
            ];
            $includes = [];
            foreach ($resDef["relations"] as $relName=>$relSpec) {
                $relFields = [
                    "name" => "fields[{$relSpec["table"]}]",
                    "in" => "query",
                    "required" => false,
                    "type" => "string",
                    "descriptions" => "Comma separated list of '{$relSpec["table"]}' field names. Should be used only when '{$relSpec["table"]}' is included (see parameter 'includes')",
                    "x-example" =>  join(",",
                        array_merge(
                            array_keys($dm[$relSpec["table"]]["fields"]),
                            array_keys(
                                array_filter($dm[$relSpec["table"]]["relations"],function ($var){
                                    return $var["type"]=="inbound";
                                })
                            )
                        )
                    )
                ];
                $oas["paths"]["/$resName"]["get"]["parameters"][] = $relFields;
                $includes[] = $relName;
            }

            $oas["paths"]["/$resName"]["get"]["parameters"][] = [
                "name" => "includes",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "descriptions" => "Comma separated list of relationships to include. See example for list of valid values",
                "x-example" => implode(",",$includes)
            ];


            // /resname/id
            $oas["paths"]["/$resName/{{$resDef["keyFld"]}}"] = [
                "parameters"=>[
                    "name" => $resDef["keyFld"],
                    "in" => "path",
                    "required" => true,
                    "type" => "string",
                    "description" => $resDef["keyFld"]." field"
                ]
            ];
        }

        HttpResp::json_out(200,$oas);
    }



    function update_bulk($resourceName)
    {
        $this->_init();
        $postData = json_decode($this->input->raw_input_stream);
        try{
            //validate_post_data($postData,["id"=>null,"type"=>["string"],"attributes"=>["object"]]);
            validate_post_data($postData);
        }
        catch (Exception $exception) {
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }

        if(gettype($postData->data)!=="array") {
            $exception = new Exception("Invalid data attribute type: must be an array",400);
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }
        $maxBulkUpdateRecords = $this->config->item("bulk_update_limit");

        $ids = [];
        $exceptions = [];
        foreach ($postData->data as $idx=>$item) {
            if(!isset($item->id)) {
                $exceptions[] = new Exception("Failed to update record number $idx: no id attribute provided", 400);
                continue;
            }

            try {
                $ids[] = $this->update($resourceName, $item->id, $item);
            }
            catch (Exception $e) {
                $exceptions[] = new Exception("Failed to update record number $idx: ".$e->getMessage(),$e->getCode());
            }

            $maxBulkUpdateRecords--;
            if($maxBulkUpdateRecords==0) {
                $exceptions[] = new Exception("Maximum number of records to bulk update reached: "
                    .$this->config->item("bulk_update_limit"), 400);
            }

        }
        $doc = \JSONApi\Document::singleton([]);
        if(count($ids)) {
            $filterStr = $this->apiDm->get_key_fld($resourceName)."><".implode(";",$ids);
            $filter = get_filter($filterStr,$resourceName);

            $recs = $this->recs->get_records($resourceName,[
                "filter"=>$filter
            ]);
            $doc = \JSONApi\Document::singleton($recs[0]);
        }
        if(count($exceptions)) {
            foreach ($exceptions as $exception) {
                $doc->addError(\JSONApi\Error::from_exception($exception));
            }
        }
        //print_r($doc);
        HttpResp::jsonapi_out(200,$doc);

    }

    /**
     * update one record
     * @param string $resourceName
     * @param string $recId
     * @param null $updateData
     * @return Exception|string
     * @throws Exception
     */
    function update($resourceName, $recId, $updateData=null)
    {
        $this->_init();
        $internal = true;

        // POST data validation
        if(is_null($updateData)) {
            $postData = json_decode($this->input->raw_input_stream);

            try {
                validate_post_data($postData);
            } catch (Exception $exception) {
                HttpResp::jsonapi_out($exception->getCode(), \JSONApi\Document::from_exception($exception));

            }
            $updateData = $postData->data;
            $internal = false;
        }

        if(gettype($updateData)!=="object") {
            $exception = new Exception("Invalid data attribute type: must be an object",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }

        // validate if data type is same as the end point type
        if($resourceName!==$updateData->type) {
            $exception = new Exception("Object type mismatch; '$updateData->type' instead of '$resourceName' ",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }

        // validate if record ID from input matches the one from URL
        if($recId!==$updateData->id) {
            $exception = new Exception("Record ID mismatch",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }

        // check if resource has primary key
        $resKeyFld = $this->apiDm->get_key_fld($resourceName);
        if(!$resKeyFld) {
            $exception = new Exception("Cannot update by id: resource is not configured with a primary key",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }

        // check if record exists
        $filter = get_filter("$resKeyFld=$recId",$resourceName);
        list($recs,$total) = $this->recs->get_records($resourceName,[
            "filter"=>$filter
        ]);
        if(!$total) {
            $exception = new Exception("Record not $recId of type '$resourceName' not found",404);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }

        // perform update
        try {
            $recId = $this->recs->update_by_id($resourceName, $recId, $updateData);
            if($internal)
                return $recId;
            $this->fetch_record_by_id($resourceName,$recId);
        }
        catch (Exception $exception) {
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));
        }


    }


    /**
     * retrieves data from the database according with the provided parameters and outputs it to the client as JSON
     * processes a GET requests for /api/$apiId/$resName
     * @param string $resName
     * @param bool $singleRec
     * TODO add limitation for absolute maximum records to return at a time
     * @throws Exception
     */
    function fetch_multiple_records($resName,$singleRec=false)
    {
        $this->_init();
        $opts = [];

        // get include
        if($this->input->get("include"))
            $opts["includeStr"] = $this->input->get("include");

        // get sparse fieldset fields
        if($flds = $this->input->get("fields")) {
            if(is_array($flds))
                $opts["fields"] = $flds;
        }

        // get paging parameters
        $opts["offset"] = 0;
        if($page = $this->input->get("page")) {
            // get offset
            if(isset($page["offset"]))
                $opts["offset"] = intval($page["offset"]);

            // get limit
            if(isset($page["limit"]) && intval($page["limit"]))
                $opts["limit"] = intval($page["limit"]);
        }

        // get filter
        if($filterStr=$this->input->get("filter"))
            $opts["filter"] = get_filter($filterStr,$resName);

        // get sort
        if($sortQry=$this->input->get("sort"))
            $opts["order"] = get_sort($sortQry,$resName);

        try {
            list($records,$totalRecords) = $this->recs->get_records($resName,$opts);
            $doc = \JSONApi\Document::singleton($records);
            $doc->setMeta(\JSONApi\Meta::factory(["offset"=>$opts["offset"],"totalRecords"=>$totalRecords]));


            HttpResp::json_out(200, $doc->json_data());
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
        }
    }



    /**
     * @param $resName
     * @param $recId
     */
    function fetch_record_by_id($resName,$recId)
    {
        $this->_init();

        $keyFld = $this->apiDm->get_key_fld($resName);
        if(is_null($keyFld))
            HttpResp::json_out(400,"Request not supported. Resource does not have a primary key defined");


        // get include
        if($this->input->get("include"))
            $opts["includeStr"] = $this->input->get("include");

        // get sparse fieldset fields
        if($flds = $this->input->get("fields")) {
            if(is_array($flds))
                $opts["fields"] = $flds;
        }

        // get filter
        $opts["filter"] = get_filter("$keyFld=$recId",$resName);

        // fetch records
        try {

            list($records,$totalRecords) = $this->recs->get_records($resName,$opts);

            if(!$totalRecords) {
                $doc = \JSONApi\Document::not_found("Not found",404);
                HttpResp::json_out(200, $doc->json_data());
            }
            //print_r($records);
            //$resource = \JSONApi\Resource::factory()
            $doc = \JSONApi\Document::singleton($records[0])->json_data();
            HttpResp::json_out(200,$doc);
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
        }
    }


    /**
     * @param $resourceName
     * @param $recId
     * @param $relationName
     * @throws Exception
     */
    function fetch_relationships( $resourceName, $recId, $relationName)
    {
        $this->_init();

        // detect relation type
        $relationType = null;
        if($rel = $this->apiDm->get_inbound_relation($resourceName, $relationName)) {
            $relationType = "inbound";
        }
        else {
            try {
                $rel = $this->apiDm->get_outbound_relation($resourceName, $relationName);
                $relationType = "outbound";
            }
            catch (Exception $exception) {
                $doc = \JSONApi\Document::singleton()
                    ->addError(\JSONApi\Error::factory(["title" => "Invalid relation $relationName of $resourceName"]));
                HttpResp::json_out(400, $doc->json_data());
            }
        }


        // prepare filter for matching the parent records
        $filterStr = $this->apiDm->get_key_fld($resourceName)."=$recId";
        $filter = get_filter($filterStr,$resourceName);
        $parent = null;
        // fetch parent record
        try {
            list($records, $count) = $this->recs->get_records($resourceName, [
                "filter"=> $filter
            ]);

            if(!$count) {
                HttpResp::not_found("RecordID $recId of $resourceName not found");
            }
            $parent = $records[0];
            if($relationType=="outbound")
                $fkId = $parent->attributes->$relationName;
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
        }

        if($relationType=="inbound") {
            $_GET["filter"] = @$_GET["filter"] . "," . $rel["field"] . "=" . $recId;
            $this->fetch_multiple_records($rel["table"]);
        }
        if($relationType=="outbound") {
            $_GET["filter"] = $rel["field"]."=".$fkId;
            $this->fetch_record_by_id($rel["table"],$fkId);
        }

    }


    /**
     * method for inserting records in one table at a time. Supports single to multiple.
     * @param $tableName
     * @return null
     * TODO: add some limitation for maximum records to insert at a time
     * @throws Exception
     */
    public function simple_insert($tableName)
    {
        $this->_init();

        // POST data validation
        $postData = json_decode($this->input->raw_input_stream);
        try{
            validate_post_data($postData);
        }
        catch (Exception $exception) {
            // TODO: log validation data, eventualy provide extra validation info....
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($exception));

        }

        // configure onDuplicate behaviour
        $onDuplicate = $this->input->get("onduplicate");
        if(!in_array($onDuplicate,["update","ignore","error"]))
            $onDuplicate = "error";

        // configure fields to be updated when onduplicate is set to "update"
        $updateFields = [];
        if($onDuplicate=="update") {
            $updateFields = get_fields_to_update($this->input,$tableName);
            if(!count($updateFields))
                $onDuplicate = null;
        }

        // starts transaction
        $this->apiDb->trans_strict(FALSE);

        // prepare data
        $entries = is_array($postData->data)?$postData->data:[$postData->data];

        // iterate through data and insert records one by one
        $insertedRecords = [];
        $totalRecords = 0;
        foreach($entries as $entry) {
            try {
                // todo: what happens when the records are not uniquely identifiable? think about adding an extra behavior
                if(!isset($entry->type) || !isset($entry->attributes))
                    continue;

                $includes = [];
                $recId = $this->recs->insert($tableName,$entry, true, $onDuplicate, $updateFields,null,$includes);

                $recIdFld = $this->apiDm->get_key_fld($entry->type);
                $filterStr = "$recIdFld=$recId";
                $filter = get_filter($filterStr,$tableName);

                list($records,$noRecs) = $this->recs->get_records($tableName,[
                        "includeStr" => implode(",",$includes),
                        "filter"=>$filter
                    ]);
                $totalRecords += $noRecs;
                $insertedRecords = array_merge_recursive($insertedRecords,$records);
                //die();

            }
            catch (Exception $exception)
            {
                $this->apiDb->trans_rollback();
                HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
            }
        }

        $this->apiDb->trans_commit();
        //return [$insertedRecords,$totalRecords];

        if($totalRecords) {
            if (is_object($postData->data))
                $doc = \JSONApi\Document::singleton($insertedRecords[0])->json_data();
            else
                $doc = \JSONApi\Document::singleton($insertedRecords)->json_data();
            HttpResp::json_out(200, $doc);
        }
        $err = \JSONApi\Error::factory(["code"=>400,"title"=>"No records inserted due to invalid input data"]);
        HttpResp::jsonapi_out(400,\JSONApi\Document::error_doc($err));
    }

    /**
     * @param $tableName
     * @param $recId
     */
    function single_delete($tableName, $recId)
    {
        $this->_init();

        try {
            $this->recs->delete($tableName, $recId);
            HttpResp::no_content(204);
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
        }
    }

    /**
     * @param $tableName
     * TODO: finish it
     */
    function bulk_delete($tableName)
    {
        HttpResp::method_not_allowed();
        $this->_init();

        // check if table exists
        if (!$this->apiDm->resource_exists($tableName)) {
            HttpResp::not_found();
            exit();
        }

    }



    function index()
    {
        $this->_init();
        HttpResp::json_out(200,[
            "meta"=>[
                "apiId"=>$this->apiId,
                "baseUrl"=>"https://".$this->apiId.$this->baseDomain."/v2"
            ],
            "jsonapi"=>[
                "version"=>"1.1"
            ]
        ]);
    }

    /**
     * TODO: properly implement this method
     * returns appropriate headers according to respective request and security settings
     */
    function options()
    {
        //echo "options";
        HttpResp::init()
            ->header("Access-Control-Allow-Origin: *")
            ->header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE")
            ->header("Access-Control-Allow-Headers: *")
            ->output();
    }

}

