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
     * reads API configuration file, connectes to database and initializes the API DataModel (structure)
     * initializes internal objects:
     * - apiDm: DataModel
     * - apiDb: database connection
     * @param string $apiId
     */
    private function _init($apiId)
    {
        $apiConfigDir = $this->apisDir."/$apiId";
        //echo $apiConfigDir;
        if(!is_dir($apiConfigDir)) {
            // API Not found
            // TODO: log to admin log that API not found
            HttpResp::not_found("API $apiId not found");
            die();
        }


        $structure = require($apiConfigDir."/structure.php");
        if(!isset($structure)) {
            // Invalid API config
            // TODO: log error: wrong api config
            HttpResp::server_error("Invalid API configuration");
            die();
        }

        $connection = require($apiConfigDir."/connection.php");
        if(!isset($connection)) {
            HttpResp::server_error("Invalid API configuration");
            die();
        }

        $permissions = require($apiConfigDir."/profiles/default.php");
        if(!isset($permissions)) {
            HttpResp::server_error("Invalid API configuration");
            die();
        }

        $settings = require($apiConfigDir."/settings.php");
        if(!isset($settings)) {
            HttpResp::server_error("Invalid API configuration");
            die();
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
            //
            // TODO log DB connection failed
            HttpResp::service_unavailable("Failed to connect to database");
            die();
        }

        // initializes DM with structure fetched from $apiCfg
        $dm = Apiator\DBApi\Datamodel::init($apiCfg);
        if(!$dm) {
            // TODO log wrong config file
            HttpResp::server_error("Invalid API config");
            die();
        }

        $this->apiDb = $db;
        $this->apiDm = $dm;
        $this->apiSettings = $settings;

        // initialize recs
        $this->recs = \Apiator\DBApi\Records::init($this->apiDb,$this->apiDm);
        if(!$this->recs) {
            // TODO log unable to initialize records navigator class
            HttpResp::server_error("Invalid API config");
            die();
        }
    }




    function update($apiName, $tblName, $recId)
    {
        echo "updateRecord";
        print_r(func_get_args());
    }

    function delete($apiName, $tblName, $recId=null)
    {
        if(!$recId)
            echo "deleteRecords";
        else
            echo "deleteRecord";
        print_r(func_get_args());
    }



    /**
     * processes a GET requests for /api/$apiId/$resName
     * - initializez API Config & DB Connection
     * - depending on the parameteres redirects the
     * @param string $apiId
     * @param string $resName
     * @return null
     * TODO add limitation for absolute maximum records to return at a time
     */
    function fetch_multiple_records($apiId, $resName)
    {
        $this->_init($apiId);

        // check if table exists
        if (!$this->apiDm->is_valid_resource($resName)) {
            // TODO log resource $resName not found
            HttpResp::not_found("Resource $resName not found");
            exit();
        }

        // fetch records
        $response = $this->_get($resName);
        if (!$response->success)
            return http_respond($response->code, $response->data);
        $recordSet = $response->data;

        $json = new JSONApiResponse($recordSet,
            new JSONApiMeta($recordSet->offset, $recordSet->total),
            new JSONApiLinks(current_url())
        );
        $cleaned = cleanUpArray((array)$json);
        http_respond(200, json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }


    /**
     * processes a GET requests for /api/$apiId/$resName/$recId
     * - initializez API Config & DB Connection
     * - depending on the parameteres redirects the
     * @param $apiId
     * @param $resName
     * @param $recId
     * @return null
     */
    function fetch_record_by_id($apiId, $resName, $recId)
    {
        $this->_init($apiId);

        // check if table exists
        if (!$this->apiDm->is_valid_resource($resName)) {
            HttpResp::not_found();
            exit();
        }

        // retrieves name of field used as key field
        $keyFld = $this->apiDm->get_key_fld($resName);
        if(is_null($keyFld)) {
            HttpResp::not_found();
            exit();
        }

        // add id as filter
        if($recId)
            $_GET["filter"] = "$keyFld=$recId";

        // fetch data from DB
        $response = $this->_get($resName);
        if(!$response->success)
            return http_respond($response->code,$response->data);
        $recordSet = $response->data;

        if($recordSet->total==0) {
            HttpResp::not_found();
            exit();
        }
        //print_r($recordSet);
        $json = new JSONApiResponse($recordSet,
            null,
            new JSONApiLinks(current_url())
        );
        if (count($json->data))
            $json->data = $json->data[0];
        else
            $json->data = null;
        $cleaned = cleanUpArray((array) $json);
        //print_r($cleaned);

        HttpResp::json_out(200, json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        exit();


    }


    /**
     * @param string $tblName
     * @return Response
     */
    private function _get($tblName)
    {
        // get query parameters
        $includes = get_include($this->input);
        $selectedFields = get_fields($this->input,$tblName);
        $filters = get_filters($this->input,$tblName);
        $offset = get_offset($this->input);
        $pageSize = get_limit($this->input,$this->myConfig["default_result_set_limit"]);
        $sortBy = get_sort($this->input,$tblName);
        $relations = get_relations($this->input);
        $options = [
            "includes"=>get_include($this->input),
            "fields"=>get_fields($this->input,$tblName),
            "filter"=>get_filters($this->input,$tblName),
            "offset"=>get_offset($this->input),
            "pageSize"=>get_limit($this->input,$this->myConfig["default_result_set_limit"]),
            "sort"=>get_sort($this->input,$tblName),
            "relations"=>get_relations($this->input)
        ];
        //print_r($options);

        $records = $this->recs->get_records($tblName,
            $includes,
            $selectedFields,
            $filters,
            $offset,
            $pageSize,
            $sortBy
        );

        $relationsConfig = [];
        $initialFilter = @$_GET["filter"];

        /**
         * @var Record $rec
         */
        foreach ($records->records as $rec) {

            foreach ($relations as $relationName) {
                if(!isset($relationsConfig[$relationName])) {
                    $relationsConfig[$relationName] = $this->apiDm->get_relation_config($tblName, $relationName);
                }

                $rel = $relationsConfig[$relationName];
                if($rel) {
                    $_GET["include"] = $this->input->get("include") . "," . $rel->targetIdMapFld;
                    $_GET["filter"] = $initialFilter.",".$rel->lnkTable.".".$rel->sourceIdMapFld."=".$rec->id;

                    $relRecs = $this->recs->get_records(
                        $rel->lnkTable,
                        get_include($this->input),
                        $selectedFields,
                        get_filters($this->input,$tblName),
                        0,
                        3,
                        //$this->config->item("default_result_set_limit"),
                        $sortBy
                    );


                    //$rec->add_relations($relationName,$relRecs->records,$rel->table,$relRecs->offset,$relRecs->total);

                    //$rec->add_relation($relationName,$relRec->attributes->$lnkAttrName);
                    $relationsRecs = [];
                    if(count($relRecs->records)) {
                        foreach ($relRecs->records as $relRec) {
                            $lnkAttrName =  $rel->targetIdMapFld;
                            $rec->add_relation($relationName,$relRec->attributes->$lnkAttrName);
                            $relationsRecs[] = $relRec->attributes->$lnkAttrName;
                            //print_r($relRec);
                        }
                    }
                    $rec->add_relations($relationName,$relationsRecs,$rel->table,$relRecs->offset,$relRecs->total);
                    //exit();

                }
            }
        }
        $stop = microtime();
        return Response::make(true, 200, $records);

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
            exit();
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


        // configure onDuplicate parameter
        $onDuplicate = $this->input->get("onduplicate");
        if(!in_array($onDuplicate,["update","ignore","error"]))
            $onDuplicate = "error";



        // configure fields to be updated when onduplicate is set to "update"
        $updateFields = [];
        if($onDuplicate=="update") {
            $updateFields = get_fields_to_update($this->input,$tableName);

        }

        // if flag is set update when duplicate and no update fields provided
        // exit with bad request 400
        if($onDuplicate=="update" && !count($updateFields)) {
            HttpResp::bad_request("No fields to be updated have been specified");
            die();
        }

        // configure if inerts should be enclosed in a transaction
        $_get = $this->input->get();
        $transaction = isset($_get["transaction"]);



        // call internal method to update
        $response = $this->_insert($tableName,$postData,true,$transaction,$onDuplicate,$updateFields);
        if(!$response->success)
            return http_respond($response->code,'{"error":"'.$response->data.'"}');

        $meta = null;
        if(get_class($response->data)=="RecordSet")
            $meta =  new JSONApiMeta($response->data->offset,$response->data->total);

        $response = new JSONApiResponse($response->data,$meta);
        HttpResp::json_out(200, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
            exit();
        }

        $res = $this->recs->delete($tableName,$recId);
        $resp = HttpResp::init()->response_code($res->code);
        if($resp->data)
            $resp->body($resp->data);
        $resp->output();
    }

    /**
     * @param $apiId
     * @param $tableName
     * TODO: finish it
     */
    function bulk_delete($apiId, $tableName)
    {
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
     * @return Response
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

        // iterate through data and insert records
        foreach($entries as $entry) {

            $res = $this->recs->insert($entry->type,$entry->attributes,$recursive,$onDuplicate,$updateFields);

            if($res->success) {
                $_GET["filter"] = "id=$res->data";
                $response = $this->_get($tblName);
                if(count($response->data->records))
                    $recordSet->add_record($tblName,$response->data->records[0],$this->apiDm->get_key_fld($tblName));
            }
            elseif($transaction){
                $this->apiDb->trans_rollback();
                return $res;
            }
        }

        $this->apiDb->trans_commit();
        if($singleInsert)
            return Response::make(true,200,$recordSet->records[0]);

        return Response::make(true,200,$recordSet);
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