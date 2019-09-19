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
 * Class Dbapi controller: translates API calls into SQL statements
 * @property CI_Config config
 * @property CI_Loader load
 * @property CI_Input input
 */
class Dbapi extends CI_Controller
{
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
    private $baseUrl = "https://dbapi.apiator/api/5cbaed2eb9a51";
    private $nolinks = true;
    private $JsonApiDocOptions = ["nolinks"=>true];

    function __construct ()
    {
        parent::__construct();

        $this->config->load("apiator");
        $this->deployment_type = $this->config->item("deployment_type");
        if(empty( $this->deployment_type))
            HttpResp::server_error("Invalid config. Deployment type unknown.");

        $this->load->helper("my_utils");
        header("Access-Control-Allow-Origin: *");
        $this->_init();
        $this->inputData = json_decode($this->input->raw_input_stream);
    }



    /**
     * reads API configuration file, connects to the database and initializes the API DataModel (structure)
     * initializes internal objects:
     * - apiDm: DataModel
     * - apiDb: database connection
     */
    private function _init()
    {
        switch ($this->deployment_type) {
            case "saas":
                // API ID is retrieved by a function provided in the config file by the name "api_id"
                $apiId = $this->config->item("api_id")();
                if(is_null($apiId))
                    HttpResp::error_out_json("Invalid call",400);

                $apiConfigDir = $this->config->item("apisDir")."/$apiId".$this->config->item("configdir_rel_path");
                break;
            case "single":
                $apiConfigDir = $this->config->item("api_config_dir");
                break;
            default:
                HttpResp::server_error("Invalid deployment type");

        }

        $this->baseUrl = "https://".$_SERVER["SERVER_NAME"]."/v2";
        $this->JsonApiDocOptions["baseUrl"] = $this->baseUrl;


        if(!is_dir($apiConfigDir)) {
            // API Not found
            // TODO: log to admin log that API not found
            HttpResp::text_out(404,"API configuration not found");
        }

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

        /** @noinspection PhpIncludeInspection */
        $permissions = require($apiConfigDir.$profileFIle);
        if(!isset($permissions)) {
            HttpResp::server_error("Invalid API permissions");
        }

        // todo configure settings
        $settings = [];
        // $settings = require($apiConfigDir."/settings.php");
        //if(!isset($settings)) HttpResp::server_error("Invalid API settings");

        $apiCfg = array_merge_recursive($permissions,$structure);


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
        $openApiSpec = generate_swagger($_SERVER["SERVER_NAME"],$this->apiDm->get_dataModel(),"/v2");
        HttpResp::json_out(200,$openApiSpec);
    }


    /**
     * Creates multiple records with a single call
     * @todo to be implemented
     */
    function create_multiple_records()
    {

        print_r($this->inputData);
    }

    /**
     * Update multiple records of different types with a single call
     * @param $resourceName
     * @throws Exception
     * @todo to be implemented
     */
    function update_multiple_records($resourceName)
    {
        // todo: finish it
        // extract data from RequestBody

        // & validate it
        try{
            validate_post_data_array($this->inputData);
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
            if(!isset($item->id)) {
                continue;
            }

            try {
                $ids[] = $this->update_single_record($item->type, $item->id, $item);
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
        $doc = \JSONApi\Document::singleton($options,[]);

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
    function delete_multiple_records($tableName)
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
    function update_single_record($resourceName, $recId, $updateData=null)
    {
        $internal = true;

        // input data validation
        if(is_null($updateData)) {
            $postData = $this->inputData;

            try {
                validate_post_data($postData);
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
        if($recId!==$updateData->id) {
            $exception = new Exception("Record ID mismatch",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        // check if resource has primary key
        $resKeyFld = $this->apiDm->get_key_fld($resourceName);
        if(!$resKeyFld) {
            $exception = new Exception("Cannot update by id: resource is not configured with a primary key",400);
            if($internal)
                throw $exception;
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
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
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));
        }

        // perform update
        try {
            $recId = $this->recs->update_by_id($resourceName, $recId, $updateData);
            if($internal)
                return $recId;
            $this->fetch_single($resourceName,$recId);
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
    private function get_query_paras($resName)
    {
        $queryParas = [];

        // get include
        if($this->input->get("include")) {
            $queryParas["includeStr"] = $this->input->get("include");
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
            $queryParas["order"] = get_sort($sortQry,$resName);


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
     * @param string $resName
     */
    function get_multiple_records($resName)
    {
        $queryParas = $this->get_query_paras($resName);

        try {
            list($records,$totalRecords) = $this->recs->get_records($resName,$queryParas);
            //print_r($records);

            $options = ["nolinks"=>$this->nolinks,"baseUrl"=>$this->baseUrl];
            $doc = \JSONApi\Document::singleton($options,$records);
            $doc->setMeta(\JSONApi\Meta::factory(["offset"=>$queryParas["offset"],"totalRecords"=>$totalRecords]));
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
     */
    function get_single_record($resName, $recId)
    {
        $keyFld = $this->apiDm->get_key_fld($resName);
        if(is_null($keyFld))
            HttpResp::json_out(400,"Request not supported. Resource does not have a primary key defined");

        $opts = $this->get_query_paras($resName);

        // get filter
        $opts["filter"] = get_filter("$keyFld=$recId",$resName);

        // fetch records
        try {

            list($records,$totalRecords) = $this->recs->get_records($resName,$opts);

            $options = ["nolinks"=>$this->nolinks,"baseUrl"=>$this->baseUrl];
            if(!$totalRecords) {
                $doc = \JSONApi\Document::not_found($this->JsonApiDocOptions,"Not found",404);
                HttpResp::json_out(200, $doc->json_data());
            }

            //$resource = \JSONApi\Resource::factory()
            $doc = \JSONApi\Document::singleton($this->JsonApiDocOptions,$records[0])->json_data();
            HttpResp::json_out(200,$doc);
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }
    }

    function test($type=null,$resId=null)
    {
        print_r($_SERVER);

    }


    /**
     * @param $resourceName
     * @param $recId
     * @param $relationName
     */
    function update_relationships($resourceName, $recId, $relationName)
    {
        print_r(func_get_args());
    }


    /**
     * @param $resourceName
     * @param $recId
     * @param $relationName
     * @throws Exception
     */
    function fetch_relationships( $resourceName, $recId, $relationName)
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
                $fkId = $parent->relationships->$relationName->id;
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }

        if($relationType=="inbound") {
            $_GET["filter"] = @$_GET["filter"] . "," . $relSpec["field"] . "=" . $recId;
            $this->fetch_multiple($relSpec["table"]);
        }
        if($relationType=="outbound") {
            $_GET["filter"] = $relSpec["field"]."=".$fkId;
            $this->fetch_single($relSpec["table"],$fkId);
        }

    }

    /**
     * fetch related resource(s)
     * @param string $resourceName parent record resource type
     * @param string $recId parent record ID
     * @param string $relationName related resource name
     */
    function fetch_related($resourceName,$recId,$relationName)
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
                $fkId = $parent->relationships->$relationName->id;
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception)->json_data());
        }

        if($relationType=="inbound") {
            $_GET["filter"] = @$_GET["filter"] . "," . $relSpec["field"] . "=" . $recId;
            $this->fetch_multiple($relSpec["table"]);
        }
        if($relationType=="outbound") {
            $_GET["filter"] = $relSpec["field"]."=".$fkId;
            $this->fetch_single($relSpec["table"],$fkId);
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
        // POST data validation
        $postData = json_decode($this->input->raw_input_stream);
        try{
            validate_post_data($postData);
        }
        catch (Exception $exception) {
            // TODO: log validation data, eventualy provide extra validation info....
            HttpResp::jsonapi_out($exception->getCode(),\JSONApi\Document::from_exception($this->JsonApiDocOptions,$exception));

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
            if (is_object($postData->data))
                $doc = \JSONApi\Document::singleton($options,$insertedRecords[0])->json_data();
            else
                $doc = \JSONApi\Document::singleton($options,$insertedRecords)->json_data();
            HttpResp::json_out(200, $doc);
        }
        $err = \JSONApi\Error::factory(["code"=>400,"title"=>"No records inserted due to invalid input data"]);
        HttpResp::jsonapi_out(400,\JSONApi\Document::error_doc($this->JsonApiDocOptions,$err));
    }

    /**
     * @param $tableName
     * @param $recId
     */
    function single_delete($tableName, $recId)
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
            ->header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE")
            ->header("Access-Control-Allow-Headers: *")
            ->output();
    }

}

