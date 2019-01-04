<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.'core/MY_RestController.php');


/**
 * Class Views
 * @property CI_Loader $load
 * @property Data_model $dm
 * @property Tables_model $tbls
 * @property CI_Input $input
 *
 */
class Views extends MY_RestController
{
    /**
     * Api_v1 constructor.
     */
    public function __construct ()
    {
        parent::__construct();

        $this->load->helper("my_utils");
        $this->load->model("Data_model","dm");

        if(array_key_exists("debug",$_GET)) echo "Controller: ".__CLASS__."\n";
    }

    /**
     * @param $pathComponents
     * @return null
     */
    public function _get ($pathComponents)
    {

        @list($dbName, $viewName) = $pathComponents;

        // test if DB name provided and respond 400 when not and return
        if (empty($dbName))
            return http_respond(400, "Database name not provided");

        // test if Table name provided and respond 400 when not and return
        if (empty($viewName))
            return http_respond(400, "View not provided");

        $this->load->model("Tables_model","tbls");
        if(!$this->tbls->init($dbName))
            return http_respond(404, "Database not found");

        // test if Table exists and respond 404 when not and return
        if (!$this->dm->is_valid_view($viewName))
            return http_respond(404, "Table not found");


        //print_r(get_sort($this->input,$viewName));
        $recordSet = $this->tbls->get_view_records(
            $viewName,
            get_fields($this->input,$viewName),
            get_filters($this->input,$viewName),
            get_sort($this->input,$viewName),
            get_offset($this->input),
            get_limit($this->input,$this->config->item("default_result_set_limit")),
            get_groupby($this->input)
        );

        $meta =  new JSONApiMeta($recordSet->offset,$recordSet->total);
        return http_respond(200, json_encode(new JSONApiResponse($recordSet,$meta), JSON_PRETTY_PRINT));
    }

    /**
     * @param $pathComponents
     * @param $postData
     * @return null
     */
    public function _post ($pathComponents, $postData)
    {
        return http_respond(400,"Invalid request");
    }

    /**
     * @param $pathComponents
     * @param $postData
     * @return null
     */
    public function _put ($pathComponents, $postData)
    {
        return http_respond(400,"Invalid request");
    }

    /**
     * @param $pathComponents
     * @param $postData
     * @return null
     */
    public function _patch ($pathComponents, $postData)
    {
        return http_respond(400,"Invalid request");
    }

    /**
     * @param $pathComponents
     * @param $postData
     * @return null
     */
    public function _delete ($pathComponents, $postData)
    {
        return http_respond(400,"Invalid request");
    }


}
