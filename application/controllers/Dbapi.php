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

        $this->load->helper("my_utils");
        header("Access-Control-Allow-Origin: *");
    }

    /**
     * reads API configuration file, connects to the database and initializes the API DataModel (structure)
     * initializes internal objects:
     * - apiDm: DataModel
     * - apiDb: database connection
     * @param string $apiId
     */
    private function _init($apiId)
    {
        $apiConfigDir = $this->apisDir."/$apiId";

        if(!is_dir($apiConfigDir)) {
            // API Not found
            // TODO: log to admin log that API not found
            HttpResp::not_found("API $apiId not found");
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

    function update_bulk($apiId,$resourceName)
    {
        $this->_init($apiId);
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
                $ids[] = $this->update($apiId, $resourceName, $item->id, $item);
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
            $filter = get_filters($filterStr,$resourceName);
            $recs = $this->recs->get_records($resourceName,null,null, $filter);
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
     * @param string $apiId
     * @param string $resourceName
     * @param string $recId
     * @param null $updateData
     * @return Exception|string
     * @throws Exception
     */
    function update($apiId, $resourceName, $recId, $updateData=null)
    {
        $this->_init($apiId);
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
        $filters = get_filters("$resKeyFld=$recId",$resourceName);
        list($recs,$total) = $this->recs->get_records($resourceName,null,null,$filters);
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
            $this->fetch_record_by_id($apiId,$resourceName,$recId);
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
     * @param string $apiId
     * @param string $resName
     * @param bool $singleRec
     * TODO add limitation for absolute maximum records to return at a time
     * @throws Exception
     */
    function fetch_multiple_records($apiId, $resName,$singleRec=false)
    {
        $this->_init($apiId);

        // get query parameters
        $include = $this->input->get("include");
        $fields = $this->input->get("fields");
        $filters = get_filters($this->input->get("filter"),$resName);
        $offset = get_offset($this->input);
        $pageSize = get_limit($this->input,$this->myConfig["default_result_set_limit"]);
        $sortBy = get_sort($this->input,$resName);
        //$relations = get_relations($this->input);

        try {
            //list($records,$totalRecords);
            list($records,$totalRecords) = $this->recs->get_records($resName,$include,$fields,$filters,$offset,$pageSize,$sortBy);
            //print_r($records);

            $metaData = new stdClass();
            $metaData->offset = $offset;
            $metaData->total = $totalRecords;
            $doc = \JSONApi\Document::singleton($records,\JSONApi\Meta::factory($metaData));

            HttpResp::json_out(200, $doc->json_data());
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
        }
    }



    /**
     * @param $apiId
     * @param $resName
     * @param $recId
     */
    function fetch_record_by_id($apiId,$resName,$recId)
    {
        $this->_init($apiId);

        $keyFld = $this->apiDm->get_key_fld($resName);
        if(is_null($keyFld))
            HttpResp::json_out(400,"Request not supported. Resource does not have a primary key defined");

        $include = $this->input->get("include");
        $fields = $this->input->get("fields");
        $_GET["filter"]= "$keyFld=$recId";
        $filters = get_filters($this->input->get("filter"),$resName);

        // fetch records
        try {
            list($records,$totalRecords) = $this->recs->get_records($resName,$include,$fields,$filters);
            print_r($records);

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
     * @param $apiId
     * @param $resourceName
     * @param $recId
     * @param $relationName
     * @throws Exception
     */
    function fetch_relationships($apiId, $resourceName, $recId, $relationName)
    {
        $this->_init($apiId);

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
        $filters = get_filters($filterStr,$resourceName);
        $parent = null;
        // fetch parent record
        try {
            list($records, $count) = $this->recs->get_records($resourceName, "", "", $filters);
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
            $this->fetch_multiple_records($apiId, $rel["table"]);
        }
        if($relationType=="outbound") {
            $_GET["filter"] = $rel["field"]."=".$fkId;
            $this->fetch_record_by_id($apiId,$rel["table"],$fkId);
        }

    }


    /**
     * method for inserting records in one table at a time. Supports single to multiple.
     * @param $apiId
     * @param $tableName
     * @return null
     * TODO: add some limitation for maximum records to insert at a time
     */
    public function simple_insert($apiId, $tableName)
    {
        $this->_init($apiId);

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
                $filters = get_filters($filterStr,$tableName);


                list($records,$noRecs) = $this->recs->get_records($tableName,implode(",",$includes),"",$filters);
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
     * @param $apiId
     * @param $tableName
     * @param $recId
     */
    function single_delete($apiId, $tableName, $recId)
    {
        $this->_init($apiId);

        try {
            $this->recs->delete($tableName, $recId);
            HttpResp::no_content(204);
        }
        catch (Exception $exception) {
            HttpResp::json_out($exception->getCode(),\JSONApi\Document::from_exception($exception)->json_data());
        }
    }

    /**
     * @param $apiId
     * @param $tableName
     * TODO: finish it
     */
    function bulk_delete($apiId, $tableName)
    {
        HttpResp::method_not_allowed();
        $this->_init($apiId);

        // check if table exists
        if (!$this->apiDm->resource_exists($tableName)) {
            HttpResp::not_found();
            exit();
        }

    }



    function index()
    {
        echo "DBAPI IDX";
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

