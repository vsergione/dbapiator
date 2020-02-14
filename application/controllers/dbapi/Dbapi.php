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
 * create_multiple_records
 * update_multiple_records
 * delete_multiple_records
 *
 * get_multiple_records
 * get_single_record
 *
 * create_single_record
 * update_single_record
 * delete_single_record
 * get_relationship
 * create_relationship
 * update_relationship
 * delete_relationship
 */

/**
 * Class Dbapi controller: translates API calls into SQL statements
 * @property CI_Config config
 * @property CI_Loader load
 * @property CI_Input input
 */
class Dbapi extends CI_Controller
{
    private $default_options = [
        "page[offset]"=>"0"
    ];
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
    private $deployment_type;
    /**
     * @var mixed
     */
    private $inputData;
    private $baseUrl;

    private $noLinksInOutput = false;
    /**
     * @var array
     */
    private $JsonApiDocOptions = [
        /**
         * @var bool when true do not include links in output
         */
        "nolinks"=>true,
        /**
         * @var bool
         */
        "minify"=>false
    ];

    /**
     * @var int set recursion level for creating new records
     * set to 0 to disable it
     */
    protected $insertMaxRecursionLevel=3;
    private $debug=false;
    /**
     * @var string
     */
    private $apiConfigDir;
    /**
     * @var array
     */
    private $errors;

    function get_max_insert_recursions()
    {
        return $this->insertMaxRecursionLevel;
    }

    function dummy()
    {
        try {
            $this->createMultipleRecords();
            $this->updateMultipleRecords();
            $this->deleteMultipleRecords();
            $this->getMultipleRecords();
            $this->getSingleRecord();
            $this->createSingleRecord();
            $this->updateSingleRecord();
            $this->deleteSingleRecord();
            $this->getRelationship();
            $this->getRelated();
            $this->create_relationship();
            $this->update_relationship();
            $this->delete_relationship();
        }
        catch (Exception $e) {

        }
    }



    function __construct ()
    {
        parent::__construct();
        $this->config->load("apiator");

        $this->deployment_type = $this->config->item("deployment_type");

        $this->load->helper("my_utils");

        $this->load->config("errorscatalog");
        $this->errors = $this->config->item("errors");

        // TODO: implement CORS control
        header("Access-Control-Allow-Origin: *");

        $this->_init();

        $this->inputData = json_decode($this->input->raw_input_stream);
    }



    /**
     * reads API configuration file, connects to the database and initializes the DataModel (structure)
     * initializes internal objects:
     * - apiDm: DataModel
     * - apiDb: database connection
     */
    private function _init()
    {
//        switch ($this->deployment_type) {
//            case "saas":
//                // API ID is retrieved by a function provided in the config file by the name "api_id"
//                $apiId = $this->config->item("api_id")();
//                if(is_null($apiId)) {
//                    HttpResp::json_out(404);
//                }
//                $apiConfigDir = $this->config->item("apisDir")."/$apiId".$this->config->item("configdir_rel_path");
//                break;
//            case "single":
//                $apiConfigDir = $this->config->item("api_config_dir");
//                break;
//            default:
//                HttpResp::server_error("Invalid deployment type");
//
//        }

        $apiConfigDir = $this->config->item("api_config_dir");

        $this->baseUrl = "https://".$_SERVER["SERVER_NAME"]."/v2";
        $this->JsonApiDocOptions["baseUrl"] = $this->baseUrl;

        if(!is_dir($apiConfigDir)) {
            // API Not found
            // TODO: log to applog (API not found)
            HttpResp::json_out(500,"Invalid API config dir $apiConfigDir");
        }
        $this->apiConfigDir = $apiConfigDir;

        // load structure
        $structure = require($apiConfigDir."/structure.php");
        if(!isset($structure)) {
            // Invalid API config
            // TODO: log error: wrong api config
            HttpResp::server_error("Invalid API configuration");
        }

        // load connection
        $dbConf = require($apiConfigDir."/connection.php");
        if(!isset($dbConf)) {
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

        $permissions = [];
        /** @noinspection PhpIncludeInspection */
//        $permissions = require($apiConfigDir.$profileFIle);
//        if(!isset($permissions)) {
//            HttpResp::server_error("Invalid API permissions");
//        }

        // todo configure settings
        $settings = [];
        // $settings = require($apiConfigDir."/settings.php");
        //if(!isset($settings)) HttpResp::server_error("Invalid API settings");

        $apiCfg = array_merge_recursive($permissions,$structure);

//        print_r($apiCfg);

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

    /**
     * debug function: shows datamodel
     * final
     */
    function dm()
    {
        HttpResp::json_out(200,$this->apiDm->get_dataModel());
    }

    /**
     * generates OpenAPI swagger file in JSON format
     * final
     */
    function swagger()
    {
        $this->load->helper("swagger");
        $openApiSpec = generate_swagger($_SERVER["SERVER_NAME"],$this->apiDm->get_dataModel(),"/v2","To update","To update","Test User","test@user.com");
        HttpResp::json_out(200,$openApiSpec);
    }

    /**
     * Parses input data depending on the Content-Type header and returns it. When invalid content type returns null
     * @return mixed|null
     * @throws Exception
     */
    private function getInputData()
    {
        if(!isset($_SERVER["CONTENT_TYPE"]))
            throw new Exception("Missing Content-Type",400);

        $cType = explode(";",$_SERVER["CONTENT_TYPE"]);

        if(in_array("application/x-www-form-urlencoded",$cType))
            return $this->inputData = json_decode(json_encode($this->input->post()));
        if(in_array("application/vnd.api+json",$cType))
            return $this->inputData = json_decode($this->input->raw_input_stream);

        //return $this->inputData = json_decode($this->input->raw_input_stream);

        throw new Exception("Invalid Content-Type",400);

    }
    /**
     * Creates multiple records with a single call
     * @todo to be implemented
     */


    /**
     * Update multiple records of different types with a single call
     * @param $resourceName
     * @throws Exception
     * @todo to be implemented
     */
    function updateMultipleRecords($resourceName)
    {
        // todo: finish it
        // extract data from RequestBody

        // & validate it
        try{
            validatePostDataArray($this->inputData);
        }
        catch (Exception $exception) {
            $errors = JSONApi\Error::from_exception($exception);
            HttpResp::json_out(400,
                JSONApi\Document::error_doc($this->JsonApiDocOptions,$errors)->json_data()
            );
        }

        $maxBulkUpdateRecords = $this->config->item("bulk_update_limit");
        $newRecords = $this->inputData->data;

        $ids = [];
        $exceptions = [];
        foreach ($newRecords as $idx=>$item) {
            if(!isset($item->id))
                continue;

            try {
                $ids[] = $this->updateSingleRecord($item->type, $item->id, $item);
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

        $options = [];
        $doc = \JSONApi\Document::create($this->JsonApiDocOptions,[]);

        if(count($exceptions)) {
            foreach ($exceptions as $exception) {
                $doc->addError(\JSONApi\Error::from_exception($exception));
            }
        }
        //print_r($doc);
        HttpResp::jsonapi_out(200,$doc);

    }


    /**
     * @param $tableName
     * @todo to be implemented
     */
    function deleteMultipleRecords($tableName)
    {
        HttpResp::method_not_allowed();

        // check if table exists
        if (!$this->apiDm->resource_exists($tableName)) {
            HttpResp::not_found();
            exit();
        }

    }


    /**
     * update one record
     * @param string $resourceName
     * @param string $recId
     * @param null $updateData
     * @return Exception|string
     * @throws Exception
     * @todo validate it
     */
    function updateSingleRecord($resourceName, $recId, $updateData=null)
    {
        $internal = true;

        // input data validation
        if(is_null($updateData)) {
            $postData = $this->inputData;

            try {
                validatePostData($postData);
            } catch (Exception $exception) {
                HttpResp::jsonapi_out($exception->getCode(), \JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));

            }
            $updateData = $postData->data;
            $internal = false;
        }

        if(gettype($updateData)!=="object") {
            $exception = new Exception("Invalid data attribute type: must be an object",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        // validate if data type is same as the end point type
        if($resourceName!==$updateData->type) {
            $exception = new Exception("Object type mismatch; '$updateData->type' instead of '$resourceName' ",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        // validate if record ID from input matches the one from URL
        if($recId!==@$updateData->id) {
            $exception = new Exception("Record ID mismatch",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        // check if resource has primary key
        $resKeyFld = $this->apiDm->getPrimaryKey($resourceName);
        if(!$resKeyFld) {
            $exception = new Exception("Cannot update by id: resource is not configured with a primary key",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        // perform update
        try {
            $recId = $this->recs->updateById($resourceName, $recId, $updateData);
            if($internal)
                return $recId;
            $qp = $this->getQueryParameters($resourceName);
//            print_r($qp);
            unset($qp["offset"]);
            unset($qp["limit"]);
            $this->getRecords($resourceName,$recId,$qp);
        }
        catch (Exception $exception) {
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }


    }


    /**
     * extracts query parameters and returns them as an array:
     * - include: comma separated list of related resources to include
     * - fields[resourceName]: comma separated list of fields to include from the specified resourceName
     * - filter: filtering criteria @todo write more details
     * - page[offset]: page offset
     * - page[limit]: page size
     * - sort: comma separated list of sorting conditions
     * - onduplicate: parameter describing the behaviour when a duplicate key occurs when inserting (or updating); possible values: ignore, update, error
     * - update: comma separated list of fields to update when onduplicate=update.
     * - _jwt
     * - api_key
     * @return array
     *
     * @
     */
    private function getQueryParameters($resName)
    {
        $queryParas = [];

        // get include
        if($this->input->get("include")) {
            $queryParas["includeStr"] = $this->input->get("include");
        }

        if($this->input->get("where")) {
            $this->load->helper("where");
            $queryParas["custom_where"] = parseStrAsWhere($this->input->get("where"));
        }


        // get sparse fieldset fields
        if($flds = $this->input->get("fields")) {
            if(is_array($flds))
                $queryParas["fields"] = $flds;
        }

        // extract paging parameters
        $queryParas["offset"] = 0;
        if($page = $this->input->get("page")) {
            // get offset
            if(isset($page["offset"]) && preg_match("/^\d+$/",$page["offset"]))
                $queryParas["offset"] = intval($page["offset"]);
            else
                $queryParas["offset"] = 0;

            // get limit
            if(isset($page["limit"])  && preg_match("/^\d+$/",$page["limit"]))
                $queryParas["limit"] = intval($page["limit"]);
        }

        // get filter
        if($filterStr=$this->input->get("filter")) {
            $queryParas["filter"] = get_filter($filterStr, $resName);
        }

        // get sort
        if($sortQry=$this->input->get("sort"))
            $queryParas["order"] = getSort($sortQry,$resName);

        // get onduplicate behaviour and fields to update
        if($ondupe=$this->input->get("onduplicate")) {
            if(!in_array($ondupe,["update","ignore","error"]))
                $ondupe = "error";
            $queryParas["onduplicate"] = $ondupe;

            $updateFields=$this->input->get("update");
            if($ondupe=="update" && $updateFields && is_array($updateFields)) {
                $queryParas["update"] = $updateFields;
            }
        }

        return $queryParas;
    }



    /**
     * retrieves data from the database according with the provided parameters and outputs it to the client as JSON
     * processes a GET requests for /api/$apiId/$resName
     * @param $resourceName
     * @param null $queryParameters
     */
    function getMultipleRecords($resourceName, $queryParameters=null)
    {
        if(is_null($queryParameters))
            $queryParameters = $this->getQueryParameters($resourceName);

        try {
            list($records,$totalRecords) = $this->recs->getRecords($resourceName,$queryParameters);
            //print_r($records);

            $doc = \JSONApi\Document::create($this->JsonApiDocOptions,$records);
            $doc->setMeta(\JSONApi\Meta::factory(["offset"=>$queryParameters["offset"],"totalRecords"=>$totalRecords]));
            //print_r($doc);


            HttpResp::json_out(200, $doc->json_data());
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }
    }

    /**
     * @param $resName
     * @param $recId
     * @param null $queryParameters
     * @deprecated
     */
    function getSingleRecord($resName, $recId, $queryParameters=null)
    {
        $keyFld = $this->apiDm->getPrimaryKey($resName);
        if(is_null($keyFld))
            HttpResp::json_out(400,"Request not supported. Resource does not have a primary key defined");

        if(is_null($queryParameters))
            $queryParameters = $this->getQueryParameters($resName);

        // get filter
        $queryParameters["filter"] = get_filter("$keyFld=$recId",$resName);

        // fetch records
        try {
            list($records,$totalRecords) = $this->recs->getRecords($resName,$queryParameters);


            if(!$totalRecords) {
                $doc = \JSONApi\Document::not_found($this->JsonApiDocOptions,"Not found",404);
                HttpResp::json_out(404, $doc->json_data());
            }

            //$resource = \JSONApi\Resource::factory()
            $doc = \JSONApi\Document::create($this->JsonApiDocOptions,$records[0])->json_data();
            HttpResp::json_out(200,$doc);
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }
    }


    /**
     * get records from table or from view identified by $resourceName
     * @param $resourceName
     * @param string|null $recId
     * @param array|null $queryParameters
     * @throws Exception
     */
    function getRecords($resourceName, $recId=null, $queryParameters=null)
    {

        $doc = \JSONApi\Document::create($this->JsonApiDocOptions);

        if(is_null($queryParameters))
            $queryParameters = $this->getQueryParameters($resourceName);

        if(!is_null($recId)) {
            $keyFld = $this->apiDm->getPrimaryKey($resourceName);
            if(is_null($keyFld))
                HttpResp::json_out(400,"Request not supported. Resource does not have a primary key defined");

            $queryParameters["filter"] = get_filter("$keyFld=$recId",$resourceName);
        }



        // fetch records
        try {
            list($records,$totalRecords) = $this->recs->getRecords($resourceName,$queryParameters);
//            print_r($records);

            // single record retrieval
            if(!is_null($recId)) {
                if (!$totalRecords) {
                    $doc = \JSONApi\Document::not_found($this->JsonApiDocOptions, "Not found", 404);
                    HttpResp::json_out(404, $doc->json_data());
                }

                $doc->setData($records[0]);
            }
            // multiple records retrieval
            else {
                $doc->setData($records);
                $doc->setMeta(\JSONApi\Meta::factory(["offset"=>$queryParameters["offset"],"totalRecords"=>$totalRecords]));

            }

            HttpResp::json_out(200, $doc->json_data());

        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }
    }


    /**
     * @param $procedureName
     */
    function callStoredProcedure($procedureName=null)
    {
        if(is_null($procedureName))
            HttpResp::bad_request("Invalid routine name");

        if($_SERVER["REQUEST_METHOD"]!=="POST") {
            http_response_code("403");
            HttpResp::method_not_allowed();
        }

        /**
         * @var \Apiator\DBApi\
         */
        $procedures = \Apiator\DBApi\Procedures::init($this->apiDb,$this->apiDm);
        print_r($this->input->post("args"));
        $procedures->call($procedureName,$this->input->post("args"));


        $this->input->post("paras");

    }



    function test($type=null,$resId=null)
    {
        switch ($type) {
            case "dbins":
                /**
                 * @var CI_DB_driver $db
                 */
                $db = $this->load->database([
                    "dsn"=> "",
                    "hostname"=> "localhost",
                    "username"=> "root",
                    "password"=> "parola123",
                    "database"=> "realy_simple_db",
                    "dbdriver"=> "mysqli",
                    "dbprefix"=> "",
                    "pconnect"=> false,
                    "db_debug"=> true,
                    "cache_on"=> false,
                    "cachedir"=> "",
                    "char_set"=> "utf8",
                    "dbcollat"=> "utf8_general_ci",
                    "swap_pre"=> "",
                    "encrypt"=> false,
                    "compress"=> false,
                    "stricton"=> false,
                    "failover"=> [],
                    "save_queries"=> true
                ],true);

                $db->query("INSERT INTO test values(2,2,2) ON DUPLICATE KEY UPDATE dd=dd");
                echo $db->affected_rows();
                break;
            default:
                $this->load->view("test");
        }

    }

    function debug_log($module=0,$message=0) {
        if(!$this->debug)
            return false;
        //print_r(debug_backtrace());
        //error_log(printf("[%s][%s][%s][%s][%d] %s\n",date("h:m:s.u"),__FILE__,__CLASS__,__FUNCTION__,__LINE__,$countSql), 3,$this->apiId);
    }


    /**
     * @param $resourceName
     * @param $recId
     * @param $relationName
     */
    function updateRelationships($resourceName, $recId, $relationName)
    {
        print_r(func_get_args());
    }


    /**
     * @param $resourceName
     * @param $recId
     * @param $relationName
     * @throws Exception
     */
    function getRelationship($resourceName, $recId, $relationName)
    {

        // detect relation type
        try {
            $relSpec = $this->apiDm->get_relationship($resourceName, $relationName);
            $relationType = $relSpec["type"];
            $relRes = $relSpec["table"];
        }
        catch (Exception $exception) {
            $doc = \JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception);
            HttpResp::json_out($exception->getCode(), $doc->json_data());
        }


        // prepare filter for matching the parent records
        $filterStr = $this->apiDm->getPrimaryKey($resourceName)."=$recId";
        $filter = get_filter($filterStr,$resourceName);
        $parent = null;
        // fetch parent record
        try {
            list($records, $count) = $this->recs->getRecords($resourceName, [
                "filter"=> $filter
            ]);

            if(!$count) {
                HttpResp::not_found("RecordID $recId of $resourceName not found");
            }
            $parent = $records[0];
            if($relationType=="outbound")
                $fkId = $parent->relationships->$relationName->id;
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }

        if($relationType=="inbound") {
            $_GET["filter"] = @$_GET["filter"] . "," . $relSpec["field"] . "=" . $recId;
            //$this->get_multiple_records()
//            $this->getMultipleRecords($relSpec["table"],["offset"=>0,"fields"=>[$relSpec["table"]=>"id"]]);
            $this->getRecords($relSpec["table"],null,["offset"=>0,"fields"=>[$relSpec["table"]=>"id"]]);
        }
        if($relationType=="outbound") {
            $_GET["filter"] = $relSpec["field"]."=".$fkId;
            $this->getRecords($relSpec["table"],$fkId);
        }

    }

    /**
     * fetch related resource(s)
     * @param string $resourceName parent record resource type
     * @param string $recId parent record ID
     * @param string $relationName related resource name
     * @throws Exception
     */
    function getRelated($resourceName, $recId, $relationName)
    {
        // detect relation type
        try {
            $relSpec = $this->apiDm->get_relationship($resourceName, $relationName);
            $relationType = $relSpec["type"];
            $relRes = $relSpec["table"];
        }
        catch (Exception $exception) {
            $doc = \JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception);
            HttpResp::json_out($exception->getCode(), $doc->json_data());
        }


        // prepare filter for matching the parent records
        $filterStr = $this->apiDm->getPrimaryKey($resourceName)."=$recId";
        $filter = get_filter($filterStr,$resourceName);
        $parent = null;
        // fetch parent record
        try {
            list($records, $count) = $this->recs->getRecords($resourceName, [
                "filter"=> $filter
            ]);

            if(!$count) {
                HttpResp::not_found("RecordID $recId of $resourceName not found");
            }
            $parent = $records[0];
//            print_r($parent->relationships->$relationName);
            if($relationType=="outbound")
                $fkId = $parent->relationships->$relationName->data->id;
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }

        if($relationType=="inbound") {
            $_GET["filter"] = @$_GET["filter"] . "," . $relSpec["field"] . "=" . $recId;
//            $this->getMultipleRecords($relSpec["table"]);
            $this->getRecords($relSpec["table"]);
        }
        if($relationType=="outbound") {
            $_GET["filter"] = $relSpec["field"]."=".$fkId;
            $this->getRecords($relSpec["table"],$fkId);
        }
    }


    /**
     * Insert records recursively
     * @param $tableName
     * @return null
     * TODO: add some limitation for maximum records to insert at a time
     * @throws Exception
     */
    public function createSingleRecord($tableName, $input=null)
    {
        // get input data
        try {
            if(is_null($input))
                $input = $this->getInputData();
        }
        catch (Exception $exception) {
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        if(is_null($input))
            HttpResp::json_out(400,
                \JSONApi\Document::error_doc($this->JsonApiDocOptions,[
                    \JSONApi\Error::factory(["title"=>"Empty input data not allowed","code"=>400])
                ])->json_data()
            );

        // validate POST data
        try{
            validatePostData($input);
        }
        catch (Exception $exception) {
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        $opts = $this->getQueryParameters($tableName);

        // configure onDuplicate behaviour
        $onDuplicate = $this->input->get("onduplicate");
        if(!in_array($onDuplicate,["update","ignore","error"]))
            $onDuplicate = "error";

        // configure fields to be updated when onduplicate is set to "update"
        $updateFields = [];
        if($onDuplicate=="update") {
            $updateFields = getFieldsToUpdate($this->input,$tableName);
            if(!count($updateFields))
                $onDuplicate = null;
        }

        // starts transaction
        $this->apiDb->trans_strict();

        // prepare data
        $entries = is_array($input->data)?$input->data:[$input->data];

        // iterate through data and insert records one by one
        $insertedRecords = [];
        $totalRecords = 0;
        foreach($entries as $entry) {
            try {
                // todo: what happens when the records are not uniquely identifiable? think about adding an extra behavior
                if(!isset($entry->type) || !isset($entry->attributes))
                    continue;

                $includes = [];
                $recId = $this->recs->insert($tableName, $entry, $this->insertMaxRecursionLevel,
                                                    $onDuplicate, $updateFields,null,$includes);

                $recIdFld = $this->apiDm->getPrimaryKey($entry->type);
                $filterStr = "$recIdFld=$recId";
                $filter = get_filter($filterStr,$tableName);

                list($records,$noRecs) = $this->recs->getRecords($tableName,[
                        "includeStr" => implode(",",$includes),
                        "filter"=>$filter
                    ]);
                $totalRecords += $noRecs;
                $insertedRecords = array_merge_recursive($insertedRecords,$records);

            }
            catch (Exception $exception)
            {
                $this->apiDb->trans_rollback();
                HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
            }
        }

        $this->apiDb->trans_commit();
        //return [$insertedRecords,$totalRecords];

        if($totalRecords) {
            $options = [];
            if (is_object($input->data))
                $doc = \JSONApi\Document::create($this->JsonApiDocOptions,$insertedRecords[0])->json_data();
            else
                $doc = \JSONApi\Document::create($this->JsonApiDocOptions,$insertedRecords)->json_data();
            HttpResp::json_out(200, $doc);
        }
        $err = \JSONApi\Error::factory(["code"=>400,"title"=>"No records inserted due to invalid input data"]);
        HttpResp::jsonapi_out(400,\JSONApi\Document::error_doc($this->JsonApiDocOptions,$err));
    }

    /**
     * @param $tableName
     * @param $recId
     */
    function deleteSingleRecord($tableName, $recId)
    {

        try {
            $this->recs->delete($tableName, $recId);
            HttpResp::no_content(204);
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }
    }






    function index()
    {
        HttpResp::json_out(200,[
            "meta"=>[
                "baseUrl"=>"https://".$_SERVER["SERVER_NAME"]."/v2"
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
            ->header("Access-Control-Allow-Methods: PUT, PATCH, POST, GET, OPTIONS, DELETE")
            ->header("Access-Control-Allow-Headers: *")
            ->output();
    }



}

function custom_where($str) {
    $expr = [];
    $start = 0;
    for($i=0;$i<strlen($str);$i++) {
        if(in_array(substr($str,$i,2),["&&","||"])){
            $expr[] = [
                "type"=>"expr",
                "expr"=>substr($expr,$start,$i-$start)
            ];
        }
    }
    $str = urldecode($str);
    $str = str_replace("&&", "' AND ",$str);
    $str = str_replace("||", "' OR ",$str);
    $str = str_replace("~=", " OR ",$str);
    return $str;
}