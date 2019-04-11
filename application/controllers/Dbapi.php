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

        // load permissions
        // todo: depending on the API client, load the appropriate permissions file
        $permissions = require($apiConfigDir."/profiles/default.php");
        if(!isset($permissions)) {
            HttpResp::server_error("Invalid API permissions");
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

    /**
     * @param string $apiName
     * @param string $tblName
     * @param string $recId
     */
    function update($apiName, $tblName, $recId)
    {
        echo "updateRecord";
        print_r(func_get_args());
    }

    /**
     * @param string $apiName
     * @param string $tblName
     * @param string $recId
     */
    function delete($apiName, $tblName, $recId=null)
    {
        if(!$recId)
            echo "deleteRecords";
        else
            echo "deleteRecord";
        print_r(func_get_args());
    }


    /**
     * retrieves data from the database according with the provided parameters and outputs it to the client as JSON
     * processes a GET requests for /api/$apiId/$resName
     * @param string $apiId
     * @param string $resName
     * @param bool $singleRec
     * @return null
     * TODO add limitation for absolute maximum records to return at a time
     */
    function fetch_multiple_records($apiId, $resName,$singleRec=false)
    {
        $this->_init($apiId);

        // fetch records
        try {
            list($recordSet,$totalRecords) = $this->_get($resName);

            die();
        }
        catch (Exception $exception) {
            HttpResp::exception_out($exception);
            die();
        }



        $null = null;

        if(!$singleRec) {
            $json = new JSONApiResponse($recordSet,$null,
                new JSONApiMeta($recordSet->offset, $recordSet->total),
                new JSONApiLinks(current_url())
            );
        }
        else {
            $null = null;
            $data = count($recordSet->records) ? $recordSet->records[0] : null;
            $json = new JSONApiResponse($data,$null);
        }

        $json->cleanUp();
        $cleaned = cleanUpArray((array)$json);
        HttpResp::json_out(200, $cleaned);
    }

    function to_jsonapi($recordSet,$total)
    {
        $doc = \JSONApi\Document::singleton($recordSet);
    }


    /**
     * processes a GET requests for /api/$apiId/$resName/$recId
     * - initializes API Config & DB Connection
     * - depending on the parameters redirects the
     * @param $apiId
     * @param $resName
     * @param $recId
     * @param bool $internalCall
     * @return RecordSet
     */
    function fetch_record_by_id($apiId, $resName, $recId,$internalCall=false)
    {
        $this->_init($apiId);

        // check if table exists
        if (!$this->apiDm->is_valid_resource($resName)) {
            HttpResp::error_out_json("Resource $resName not found",404);
        }

        // retrieves name of field used as key field
        $keyFld = $this->apiDm->get_key_fld($resName);
        if(is_null($keyFld)) {
            HttpResp::error_out_json("Resource $resName cannot be retrieved by ID",422);
        }

        // id as filter
        if($recId)
            $_GET["filter"] = "$keyFld=$recId";

        // fetch data from DB
        $recordSet = $this->_get($resName);

        if($internalCall)
            return $recordSet;

        if($recordSet->total==0) {
            HttpResp::error_out_json("Resource ID $recId of type $resName not found",404);
        }

        $null = null;
        $json = new JSONApiResponse($recordSet,$null, null,
            new JSONApiLinks(current_url())
        );
        $json->cleanUp();
        if (count($json->data))
            $json->data = $json->data[0];
        else
            $json->data = null;
        $cleaned = cleanUpArray((array) $json);

        HttpResp::json_out(200, $cleaned);
    }



    /**
     * @param $apiId
     * @param $resName
     * @param $recId
     * @param $fkName
     */
    function fetch_fk_record($apiId, $resName, $recId,$fkName) {



        $this->_init($apiId);

        try {
            $rel = $this->apiDm->get_fk_relation($resName, $fkName);

            // prepare include
            $incl = $this->input->get("include");
            $newIncl = [$fkName];
            foreach (explode(",",$incl) as $inc) {
                $newIncl[] .= $rel["table"].".".$incl;
            }
            $_GET["include"] = implode(",",$newIncl);

            // prepare fields
            $flds = $this->input->get("fields");

            $rs = $this->fetch_record_by_id($apiId,$resName,$recId,true);
            print_r($rs);
            //echo (json_encode($rs->records[0]->attributes->$fkName,JSON_PRETTY_PRINT));
        }
        catch (Exception $exception) {
            \HttpResp::exception_out($exception);
        }

        //$res = $this->_get($rel["table"]);
        //$selectedFields = get_fields($this->input,$tblName);

    }


    /**
     * @param string $tblName
     * @return array
     * @throws Exception
     */
    private function _get($tblName)
    {
        // get query parameters
        //$includes = get_include($this->input);
        //$selectedFields = get_fields($this->input,$tblName);
        $filters = get_filters($this->input,$tblName);
        $offset = get_offset($this->input);
        $pageSize = get_limit($this->input,$this->myConfig["default_result_set_limit"]);
        $sortBy = get_sort($this->input,$tblName);
        $relations = get_relations($this->input);



        /*
        $options = [
            "includes"=>get_include($this->input),
            "fields"=>get_fields($this->input,$tblName),
            "filter"=>get_filters($this->input,$tblName),
            "offset"=>get_offset($this->input),
            "pageSize"=>get_limit($this->input,$this->myConfig["default_result_set_limit"]),
            "sort"=>get_sort($this->input,$tblName),
            "relations"=>get_relations($this->input)
        ];
        */
        //print_r($options);

        try {
            list($records,$totalRecords) = $this->recs->get_records($tblName,
                $this->input->get("include"),
                $this->input->get("fields"),
                $filters,
                $offset,
                $pageSize,
                $sortBy
            );

        }
        catch (Exception $exception) {
            throw $exception;
        }


        $metaData = new stdClass();
        $metaData->offset = $offset;
        $metaData->total = $totalRecords;
        $doc = \JSONApi\Document::singleton($records,\JSONApi\Meta::factory($metaData));
        print_r($doc->json_data());
        return [$records,$totalRecords];
        //print_r($records);
        //exit();
/*
        $relationsConfig = [];
        $initialFilter = @$_GET["filter"];
*/
        /**
         * @var Record $rec
         */
//        foreach ($records->records as $rec) {
//            foreach ($relations as $relationName) {
//                if(!isset($relationsConfig[$relationName])) {
//                    $relationsConfig[$relationName] = $this->apiDm->get_relation_config($tblName, $relationName);
//                }
//
//                $rel = $relationsConfig[$relationName];
//                if($rel) {
//                    $_GET["include"] = $this->input->get("include") . "," . $rel->targetIdMapFld;
//                    $_GET["filter"] = $initialFilter.",".$rel->lnkTable.".".$rel->sourceIdMapFld."=".$rec->id;
//
//                    $relRecs = $this->recs->get_records(
//                        $rel->lnkTable,
//                        get_include($this->input),
//                        $this->input->get("fields"),
//                        get_filters($this->input,$tblName),
//                        0,
//                        3,
//                        //$this->config->item("default_result_set_limit"),
//                        $sortBy
//                    );
//
//
//                    //$rec->add_relations($relationName,$relRecs->records,$rel->table,$relRecs->offset,$relRecs->total);
//
//                    //$rec->add_relation($relationName,$relRec->attributes->$lnkAttrName);
//                    $relationsRecs = [];
//                    if(count($relRecs->records)) {
//                        foreach ($relRecs->records as $relRec) {
//                            $lnkAttrName =  $rel->targetIdMapFld;
//                            $rec->add_relation($relationName,$relRec->attributes->$lnkAttrName);
//                            $relationsRecs[] = $relRec->attributes->$lnkAttrName;
//                            //print_r($relRec);
//                        }
//                    }
//                    $rec->add_relations($relationName,$relationsRecs,$rel->table,$relRecs->offset,$relRecs->total);
//                    //exit();
//
//                }
//            }
//        }
        //$stop = microtime();
        /** @var TYPE_NAME $records */
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

        // check if table exists
        if (!$this->apiDm->is_valid_resource($tableName)) {
            HttpResp::not_found();
        }

        // POST data validation
        $postData = json_decode($this->input->raw_input_stream);
        try{
            is_valid_post_data($postData);
        }
        catch (Exception $e) {
            // TODO: log validation data, eventualy provide extra validation info....
            HttpResp::json_out($e->getCode(),["errors"=>[["message"=>$e->getMessage()]]]);
        }

        // configure onDuplicate behaviour
        $onDuplicate = $this->input->get("onduplicate");
        if(!in_array($onDuplicate,["update","ignore","error"]))
            $onDuplicate = "error";

        // configure fields to be updated when onduplicate is set to "update"
        $updateFields = [];
        if($onDuplicate=="update") {
            $updateFields = get_fields_to_update($this->input,$tableName);
            // if no update fields provided exit with bad request 400
            if(!count($updateFields))
                HttpResp::bad_request("No fields to be updated have been specified");
        }


        // configure if inserts should be enclosed in a transaction
        $transaction = $this->input->get("transaction") && true;

        // call internal method to update
        try {
            $data = $this->_insert($tableName, $postData, true, $transaction, $onDuplicate, $updateFields);
        }
        catch (Exception $e) {
            HttpResp::json_out($e->getCode(),["errors"=>[["message"=>$e->getMessage()]]]);
        }

        $meta = null;
        if(get_class($data)=="RecordSet")
            $meta =  new JSONApiMeta($data->offset,$data->total);

        $null = null;
        $response = new JSONApiResponse($data,$null,$meta);
        //$response->cleanUp();
        HttpResp::json_out(200, $response->toJSON());
    }

    /**
     * @param $apiId
     * @param $tableName
     * @param $recId
     */
    function single_delete($apiId, $tableName, $recId)
    {
        $this->_init($apiId);

        // check if table exists
        if (!$this->apiDm->is_valid_resource($tableName)) {
            HttpResp::not_found();
        }

        try {
            $this->recs->delete($tableName, $recId);
            HttpResp::no_content(204);
        }
        catch (Exception $e) {
            HttpResp::json_out($e->getCode(),["errors"=>[["message"=>$e->getMessage()]]]);
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
        if (!$this->apiDm->is_valid_resource($tableName)) {
            HttpResp::not_found();
            exit();
        }

    }


    /**
     * inserts new records. Always included in a transaction
     * @param string $tblName
     * @param mixed $data
     * @param $recursive
     * @param $transaction
     * @param $onDuplicate
     * @param $updateFields
     * @return RecordSet
     * @throws Exception
     */
    private function _insert($tblName, $data, $recursive, $transaction, $onDuplicate, $updateFields) {
        // initializes an empty RecordSet
        $recordSet = new RecordSet([],$tblName,0,0);

        // starts transaction
        if($transaction)
            $this->apiDb->trans_strict(FALSE);

        // determine if is a single or bulk insert
        $singleInsert = !is_array($data->data);

        // prepare data
        $entries = is_array($data->data)?$data->data:[$data->data];

        // iterate through data and insert records one by one
        // - no bulk insert supported
        foreach($entries as $entry) {
            try {
                // todo: what happens when the records are not uniquely identifyable?? think about adding an extra behaviuor
                $keyFldVal = $this->recs->insert($entry->type, $entry->attributes, $recursive, $onDuplicate, $updateFields);
                $idFld = $this->apiDm->get_key_fld($entry->type);
                $_GET["filter"] = "$idFld=$keyFldVal";
                $records = $this->_get($tblName);
                if(count($records->records)) {
                    $recordSet->add_record($tblName,$records->records[0],$this->apiDm->get_key_fld($tblName));
                }
            }
            catch (Exception $e)
            {
                if($transaction){
                    $this->apiDb->trans_rollback();
                }
                throw $e;
            }

        }


        $this->apiDb->trans_commit();
        if($singleInsert)
            return $recordSet->records[0];

        return $recordSet;
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

