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
 * Class Arecords
 * @property CI_Config config
 * @property CI_Loader load
 * @property CI_Input input
 */
class Arecords extends CI_Controller
{
    /**
     * @var \Apiator\Config\ApisEnumerator
     */
    private $apiConfigsEnumerator;
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

        $this->load->helper("my_utils");

    }

    /**
     * @param string $owner
     * @param string $apiName
     */
    function init($owner,$apiName)
    {
        $this->apiConfigsEnumerator =
            \Apiator\Config\ApisEnumerator::init(
                $this->myConfig["configStorageType"]
                ,$this->myConfig["configStorageSettings"],
                $owner);

        if(!$this->apiConfigsEnumerator) {
            die(
                HttpResp::json_out(500, ["errors" => [
                    ["title" => "Server error [Records_model/Init]: could not initialize config enum"]
                ]])
            );
        }

        $apiCfg = $this->apiConfigsEnumerator->get($apiName,true);
        if(!$apiCfg) {
            die(
                HttpResp::json_out(500, ["errors" => [
                    ["title" => "Server error [Records_model/Init]: could not load configuration object"]
                ]])
            );
        }

        $conn = $apiCfg->getConnection();
        $dbConf = array(
            'dsn'	=> '',
            'hostname' => $conn["host"],
            'username' => $conn["username"],
            'password' => $conn["password"],
            'database' => $conn["database"],
            'dbdriver' => $conn["type"],
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
            die(
                HttpResp::json_out(500, ["errors" => [
                    ["title" => $db->error()]
                ]])
            );
        }

        // initializes DM with structure fetched from $apiCfg
        $dm = Apiator\DBApi\Datamodel::init($apiCfg->getStructure());
        if(!$dm) {
            die(
                HttpResp::json_out(500, ["errors" => [
                    ["title" => "Could not initialize data model. Check API config"]
                ]])
            );
        }

        $this->apiDb = $db;
        $this->apiDm = $dm;
        $this->apiSettings = $apiCfg->getSettings();
    }


    /**
     * looks ok
     * @param $owner
     * @param $apiName
     * @param $tblName
     * @param $recId
     * @return null
     */
    function getRecordFromTable($owner,$apiName,$tblName,$recId)
    {
        $this->init($owner,$apiName);

        // check if table exists
        if(!$this->apiDm->is_valid_table($tblName))
            die(HttpResp::json_out(404,["errors"=>[
                ["title"=>"Table $tblName not found"]
            ]]));

        // initialize recs
        $this->recs = \Apiator\DBApi\Records::init($this->apiDb,$this->apiDm);
        if(!$this->recs)
            die(HttpResp::json_out(500,["errors"=>[
                ["title"=>"Could not initialize records navigator class."]
            ]]));

        // add id as filter
        $_GET["filter"] = "id=$recId";
        $response = $this->get_records($tblName);
        if(!$response->success)
            return http_respond($response->code,$response->data);
        $recordSet = $response->data;

        if($recordSet->total==0)
            return http_respond(404,'{"error":"Record not found"}');
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

        return http_respond(200, json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    function updateRecord($owner,$apiName,$tblName,$recId)
    {
        echo "updateRecord";
        print_r(func_get_args());
    }

    function deleteRecords($owner,$apiName,$tblName,$recId=null)
    {
        if(!$recId)
            echo "deleteRecords";
        else
            echo "deleteRecord";
        print_r(func_get_args());
    }

    function addRecord($owner,$apiName,$tblName)
    {
        $this->init($owner,$apiName);

        // check if table exists
        if(!$this->apiDm->is_valid_table($tblName))
            die(HttpResp::json_out(404,["errors"=>[
                    ["title"=>"Table $tblName not found"]
                ]]));

        // initialize recs
        $this->recs = \Apiator\DBApi\Records::init($this->apiDb,$this->apiDm);
        if(!$this->recs)
            die(HttpResp::json_out(500,["errors"=>[
                    ["title"=>"Could not initialize records navigator class."]
                ]]));


        // TODO: parse input depending on Content-type header
        $postData = json_decode($this->input->raw_input_stream);
        $validation = is_valid_data($postData);
        if(!$validation->success)
            die(
                HttpResp::json_out($validation->code,["errors"=>[
                    $validation->data
                ]])
            );

        $options = isset($_GET["options"])?explode(",",$_GET["options"]):[];

        $response = $this->insert_records($tblName,$postData,$options);
        if(!$response->success)
            return http_respond($response->code,'{"error":"'.$response->data.'"}');

        $meta = null;
        if(get_class($response->data)=="RecordSet")
            $meta =  new JSONApiMeta($response->data->offset,$response->data->total);

        $response = new JSONApiResponse($response->data,$meta);
        return http_respond(200, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    }

    private function insert_records($tblName,$data,$options) {
        $recordSet = new RecordSet([],$tblName,$this->apiDm->get_idfld($tblName),0,0);
        $fieldsToUpdate = get_updatefields($this->input,$tblName);

        $this->apiDb->trans_strict(FALSE);
        $singleInsert = !is_array($data->data);
        $entries = is_array($data->data)?$data->data:[$data->data];

        foreach($entries as $entry) {
            $res = $this->recs->create($entry->type,$entry->attributes,$fieldsToUpdate,$options);

            if($res->success) {
                $_GET["filter"] = $this->apiDm->get_idfld($tblName)."=$res->data";

                $response = $this->get_records($tblName);
                if(count($response->data->records))
                    $recordSet->add_record($tblName,$response->data->records[0],$this->apiDm->get_idfld($tblName));
            }
            // in case it returns fail and is singleInsert then return
            elseif($singleInsert){
                return $res;
            }
        }
        if($singleInsert)
            return Response::make(true,200,$recordSet->records[0]);

        return Response::make(true,200,$recordSet);
    }

    /**
     * looks ok
     * @param string $owner
     * @param string $apiName
     * @param string $tblName
     * @return null
     */
    function listRecordsFromTable($owner,$apiName,$tblName)
    {
        $this->init($owner,$apiName);

        // initialize recs
        $this->recs = \Apiator\DBApi\Records::init($this->apiDb,$this->apiDm);
        if(!$this->recs)
            die(
                HttpResp::json_out(500,["errors"=>[
                    ["title"=>"Could not initialize records navigator class."]
                ]])
            );

        if(!$this->apiDm->is_valid_table($tblName))
            die(
                HttpResp::json_out(404,["errors"=>[
                    ["title"=>"Table $tblName not found"]
                ]])
            );

        // fetch records
        $response = $this->get_records($tblName);
        if(!$response->success)
            return http_respond($response->code,$response->data);
        $recordSet = $response->data;

        $json = new JSONApiResponse($recordSet,
            new JSONApiMeta($recordSet->offset,$recordSet->total),
            new JSONApiLinks(current_url())
        );
        $cleaned = cleanUpArray((array) $json);
        return http_respond(200, json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    }

    /**
     * @param string $tblName
     * @return Response
     */
    private function get_records($tblName)
    {
        // get query parameters
        $includes = get_include($this->input);
        $selectedFields = get_fields($this->input,$tblName);
        $filters = get_filters($this->input,$tblName);
        $offset = get_offset($this->input);
        $pageSize = get_limit($this->input,$this->myConfig["default_result_set_limit"]);
        $sortBy = get_sort($this->input,$tblName);
        $relations = get_relations($this->input);

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

    function listRecordsFromView($owner,$apiName,$tblName)
    {
        echo "listRecordsFromViews";
        print_r(func_get_args());
    }


    function options($reqType)
    {
        //print_r($_SERVER);
        $resp = HttpResp::init();
        switch ($reqType) {
            case "record":
                if(isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
                    $resp->allow_methods("GET, PUT, DELETE, OPTIONS");
                break;
            case "tableRecords";
                if(isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
                    $resp->allow_methods("GET, POST, DELETE, OPTIONS");
                break;
            case "viewRecords";
                if(isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
                    $resp->allow_methods("GET, OPTIONS");
                break;
        }
        $resp->output();
    }



}