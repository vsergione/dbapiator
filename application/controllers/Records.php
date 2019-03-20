<?php
/** @noinspection PhpIncludeInspection */
require_once(APPPATH.'core/MY_RestController.php');
/** @noinspection PhpIncludeInspection */
require_once(APPPATH.'libraries/RecordSet.php');



/**
 * @property CI_Config config
 * @property CI_Loader load
 * @property CI_Input input
 * @property Records_model recs
 * @property Records_model resources
 * @property CI_Output output
 * @property Data_model dm
 * @property CI_DB_driver db
 */

class Records extends MY_RestController
{
    private $baseUrl;
    /**
     * Api_v1 constructor.
     */
    public function __construct ()
    {
        parent::__construct();

        /// load some stuff
        $this->load->helper("my_utils");
        if(array_key_exists("debug",$_GET)) {
            echo "Controller: ".__CLASS__."\n";
            echo base_url();
        }

        $this->baseUrl = base_url()."api/v1/databases/%s/tables/%s/records/%s";
    }


    /**
     * @return array
     */
    private function get_relations()
    {
        if(empty($this->input->get("relations")))
            return [];
         return explode(",",$this->input->get("relations"));
    }


    /**
     * retrieves records
     * @param $pathComponents
     * @return null
     * @todo Populate links object
     */
    function _get ($pathComponents)
    {
        
        $this->load->model("data_model", "dm");
        @list($dbName, $tblName, $recId, $rel, $relName, $relId) = $pathComponents;

        if (empty($dbName))
            return http_respond(400, '{"error":"Database name not provided"}');

        if (empty($tblName))
            return http_respond(400, '{"error":"Table name not provided"}');

        if (!$this->dm->init($dbName))
            return http_respond(404, '{"error":"Database not found"}');

        if (!$this->dm->is_valid_table($tblName))
            return http_respond(404, '{"error":"Table not found"}');

        if (empty($recId)) {
            /**
             * FETCH RECORDS
             */
            $response = $this->get_records($dbName, $tblName);
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
         * FETCH RECORD BY id
         */
        if (empty($rel)) {
            $_GET["filter"] = "id=$recId";
            $response = $this->get_records($dbName, $tblName);
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

        if (!in_array($rel, ["fk", "link"])) {
            return http_respond(400, '{"error":"Invalid request: relations qualifier is invalid"}');
        }

        // relation name not provided => respond 400 and exit
        if (empty($relName))
            return http_respond(400, '{"error":"Invalid request: relation name expected but not provided"}');

        // test if relation exists and respond 404 when not and return
        if ($rel == "link" && !$this->dm->is_valid_relation($tblName, $relName))
            return http_respond(404, '{"error":"Invalid relation name"}');

        // test if relation exists and respond 404 when not and return
        if ($rel == "fk" && !$this->dm->is_fk_field($tblName, $relName))
            return http_respond(404, '{"error":"Invalid field name 1"}');

        /**
         * FETCH INCLUDE
         */
        if ($rel == "fk") {
            $_GET["filter"] = "id=$recId";
            $_GET["include"] = $relName;

            $response = $this->get_records($dbName, $tblName);
            if(!$response->success)
                return http_respond($response->code,$response->data);
            $recordSet = $response->data;

            if (count($recordSet->records)) {
                $record = $recordSet->records[0]->attributes->$relName;
                $recordSet = new RecordSet([], $record->type, $this->dm->get,0, 1);
                $recordSet->records[] = $record;
            }
            $response = new JSONApiResponse($recordSet,
                (object) [],
                new JSONApiLinks(current_url()));
            if ($response->data)
                $response->data = $response->data[0];
            else
                $response->data = null;
            $response = cleanUpArray($response);

            return http_respond(200, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        /**
         * FETCH RELATIONS
         */

        if ($rel == "link"){
            $relConf = $this->dm->get_relation_config($tblName,$relName);
            $_GET["filter"] = $relConf->sourceIdMapFld."=$recId";
            $_GET["include"] = $relConf->targetIdMapFld;
            $fldName = ($relConf->targetIdMapFld);

            $response = $this->get_records($dbName, $relConf->lnkTable);
            if(!$response->success)
                return http_respond($response->code,$response->data);
            $recordSet = $response->data;

            if (count($recordSet->records)) {
                $newRecordSet = new RecordSet([], $relConf->table, $recordSet->offset, $recordSet->total);
                foreach ($recordSet->records as $record) {
                    $newRecordSet->records[] = $record->attributes->$fldName;
                }
                $recordSet = $newRecordSet;
            }

            $response = new JSONApiResponse($recordSet,
                new JSONApiMeta($recordSet->offset,$recordSet->total),
                new JSONApiLinks(current_url()));

            return http_respond(200, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return http_respond(200, json_encode(array("data"=>null),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    }


    /**
     * @param $dbName
     * @param $tblName
     * @return Response
     */
    private function get_records($dbName,$tblName)
    {
        if(!isset($this->recs)) {
            $this->load->model("Records_model", "recs");

            if (!$this->recs->init($dbName))
                return Response::make(false, 500, "Server error");
        }


        $includes = get_include($this->input);
        $selectedFields = get_fields($this->input,$tblName);
        $filters = get_filters($this->input,$tblName);
        $offset = get_offset($this->input);
        $pageSize = get_limit($this->input,$this->config->item("default_result_set_limit"));
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
                    $relationsConfig[$relationName] = $this->dm->get_relation_config($tblName, $relationName);
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

    /*
    Possible responses
    201 Created
    202 Accepted
    204 No Content
    403 Forbidden unsupported request to create a resource.
    404 Not Found
    409 Conflict - duplicate and for incompatible type
    */

    /**
     * creates a new resource on  indicated endpoint
     * @param array $pathComponents URL req path compoments
     * @param Object/Array $postData POST data JSON decoded
     * @return null
     */
    public function _post($pathComponents,$postData){
        //return http_respond(500,null,"Some error");
        @list($dbName, $tblName, $recId, $rel, $relName) = $pathComponents;

        if (empty($dbName))
            return http_respond(400, '{"error":"Database name not provided"}');

        if (empty($tblName))
            return http_respond(400, '{"error":"Table name not provided"}');

        try{
            is_valid_post_data($postData);
        }
        catch (Exception $e) {
            // TODO: log validation data, eventualy provide extra validation info....
            HttpResp::json_out($e->getCode(),["errors"=>[["message"=>$e->getMessage()]]]);
        }

        $this->load->model("data_model", "dm");
        if (!$this->dm->init($dbName))
            return http_respond(404, '{"error":"Database not found"}');

        if (!$this->dm->is_valid_table($tblName))
            return http_respond(404, '{"error":"Table not found"}');

        $options = isset($_GET["options"])?explode(",",$_GET["options"]):[];

        if(!empty($id)) {
            return http_respond(201, "");
        }

        $response = $this->insert_records($dbName,$tblName,$postData,$options);
        if(!$response->success)
            return http_respond($response->code,'{"error":"'.$response->data.'"}');

        $meta = null;
        if(get_class($response->data)=="RecordSet")
            $meta =  new JSONApiMeta($response->data->offset,$response->data->total);

        $response = new JSONApiResponse($response->data,$meta);
        return http_respond(200, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    }

    /**
     * inserts new records
     * @param string $dbName
     * @param string $tblName
     * @param mixed $data
     * @param array $options: [createRecursive,duplicateUpdate]
     * @return Response
     */
    private function insert_records($dbName,$tblName,$data,$options) {
        $this->load->model("Records_model","recs");
        if(!$this->recs->init($dbName))
            return Response::make(false,404, '{"error":"Database config \'$dbName\' not found"}');

        $recordSet = new RecordSet([],$tblName,0,0);
        $updateFlds = get_updatefields($this->input,$tblName);
        
        $this->db->trans_strict(FALSE);
        $singleInsert = !is_array($data->data);
        $entries = is_array($data->data)?$data->data:[$data->data];

        foreach($entries as $entry) {
            $res = $this->recs->create($entry->type,$entry->attributes,$updateFlds,$options);

            if($res->success) {
                $_GET["filter"] = "id=$res->data";

                $response = $this->get_records($dbName,$tblName);
                if(count($response->data->records))
                    $recordSet->add_record($tblName,$response->data->records[0]);
            }
            elseif($singleInsert){
                return $res;
            }


        }
        if($singleInsert)
            return Response::make(true,200,$recordSet->records[0]);

        return Response::make(true,200,$recordSet);
    }


    /**
     * deletes one or more records
     * @param $pathComponents
     * @param $postData
     * @return null|void
     */
    public function _delete($pathComponents, $postData)
    {

        @list($dbName, $tblName, $recId, $rel, $relName, $relId) = $pathComponents;

        if (empty($dbName))
            return http_respond(400, '{"error":"Database name not provided"}');

        $this->load->model("data_model", "dm");
        if (!$this->dm->init($dbName))
            return http_respond(404, '{"error":"Database not found"}');

        if (empty($tblName))
            return http_respond(400, '{"error":"Table name not provided"}');

        if (!$this->dm->is_valid_table($tblName))
            return http_respond(404, '{"error":"Table not found"}');

        if($recId==null)
            return http_respond(400, '{"error":"No record ID provided"}');

        $this->load->model("Records_model","resources");
        if(!$this->resources->init($dbName))
            return http_respond(404, '{"error":"Database config \'$dbName\' not found"}');

        $response = null;
        switch($rel) {
            case "rel":
                if($relName=="")
                    return http_respond(400, '{"error":"Relation name not provided"}');
                else
                    if(is_object($postData))
                        if(property_exists($postData,"data"))
                            $response = $this->resources->delete_relationship($tblName,$recId,$relName,$postData->data);
                        else
                            return http_respond(400, '{"error":"Invalid post data: invalid structure"}');
                    else
                        return http_respond(400, '{"error":"Invalid post data: not an object"}');

                return http_respond($response->code, $response->code==204?null:($response->data));
                break;
            case "fk":
                break;
            case "":
                $response = $this->resources->delete($tblName,$recId);
                return http_respond($response->code, $response->code==204?null:($response->data));
                break;
            default:
                return http_respond(404, '{"error":"Invalid request"}');
        }
    }


    /**
     * updates one or more records
     * @param $pathComponents
     * @param $postData
     * @return null|void
     */
    public function _put ($pathComponents, $postData)
    {

        @list($dbName, $tblName, $recId, $rel, $relName) = $pathComponents;

        if (empty($dbName))
            return http_respond(400, '{"error":"Database name not provided"}');

        if (empty($tblName))
            return http_respond(400, '{"error":"Table name not provided"}');

        try{
            is_valid_post_data($postData);
        }
        catch (Exception $e) {
            // TODO: log validation data, eventualy provide extra validation info....
            HttpResp::json_out($e->getCode(),["errors"=>[["message"=>$e->getMessage()]]]);
        }

        $this->load->model("data_model", "dm");
        if (!$this->dm->init($dbName))
            return http_respond(404, '{"error":"Database not found"}');

        if (!$this->dm->is_valid_table($tblName))
            return http_respond(404, '{"error":"Table not found"}');

        $options = isset($_GET["options"])?explode(",",$_GET["options"]):[];
        if($recId==null && !in_array("bulkUpdate",$options))
            return http_respond(400, '{"error":"No record ID provided"}');

        if(!empty($recId)) {
            $response = $this->update_record($dbName,$tblName,$recId,$postData->data);

            if(!$response->success)
                return http_respond($response->code,'{"error":"'.$response->data.'"}');
            //print_r($response);
            $response = new JSONApiResponse($response->data);

            return http_respond(200, $response->toJSON());
        }

        return http_respond(200, "Bulk insert");


    }

    /**
     * @param $dbName
     * @param $tblName
     * @param $id
     * @param $data
     * @return Response
     */
    private function update_record($dbName,$tblName,$id,$data) {

        $this->load->model("Records_model","resources");

        if(!$this->resources->init($dbName))
            return Response::make(false,404, '{"error":"Database config \'$dbName\' not found"}');

        $res = $this->resources->update($tblName,$id,$data->attributes);
        if(!$res->success)
            return $res;

        $_GET["filter"] = "id=$id";
        $res = $this->get_records($dbName,$tblName);
        if(!$res->success)
            return $res;

        return Response::make(true,200,$res->data->records[0]);
    }

    /**
     * @param $pathComponents
     * @param $postData
     * @return null|void
     */
    public function _patch($pathComponents,$postData) {
        $this->_put($pathComponents,$postData);

    }
}
