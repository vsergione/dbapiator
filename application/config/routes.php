<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = 'errors/not_found';
$route['translate_uri_dashes'] = false;

/**
 *  nodes / controllers / cells / groups / parameters
 *
 */

//$route['api/v(\d+)'] = "api_v$1/index";
/*
// /api/v1/databases/dbName/tables/tblName/records/recId/relationships/relName/recId?query
$route['api\/v(\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/relationships\/([\w\-]+)\/([\w\-]+)\/?(?=\\?\?(.*))'] = "MyApp/index/db/$2/$3/$4/relationships/$5/$6?$7";
// /api/v1/databases/dbName/tables/tblName/records/recId/relationships/relName?query
$route['api\/v(\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/relationships\/([\w\-]+)\/?(?=\\?\?(.*))'] = "MyApp/index/db/$2/$3/$4/relationships/$5?$6";
// /api/v1/databases/dbName/tables/tblName/records/recId/relationships?query
$route['api\/v(\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/([\w\-]+)\/relationships\/?(?=\\?\?(.*))'] = "MyApp/index/db/$2/$3/$4/relationships?$5";
// /api/v1/databases/dbName/tables/tblName/records/recId?query
$route['api\/v(\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/?(?=\\?\?(.*))'] = "MyApp/index/db/$2/$3/$4?$5";
// /api/v1/databases/dbName/tables/tblName/records?query
$route['api\/v(\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/?(?=\\?\?(.*))'] = "MyApp/index/db/$2/$3/$4?$5";
*/

// database controller
// GET fetch DB list
// POST create DB
//apis/user_name/api_name/
$route['api\/(v\d+)\/databases'] = "databases/index";
$route['api\/(v\d+)\/sessions'] = "databases/index";
// GET fetch DB
// DELETE drop db
// PUT rename DB
$route['api\/(v\d+)\/databases\/([\w\-]+)'] = "databases/index/$2";
$route['api\/(v\d+)\/sessions\/([\w\-]+)'] = "databases/index/$2";

$route['api\/(v\d+)\/databases\/([\w\-]+)/config'] = "databases/index/$2/config";
$route['api\/(v\d+)\/sessions\/([\w\-]+)/config'] = "databases/index/$2/config";


// tables controller
// GET fetch tables list
// POST create table
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables'] = "records/index/$2";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables'] = "records/index/$2";
// GET fetch table
// DELETE drop table
// PUT rename table
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)'] = "records/index/$2/$3";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)'] = "records/index/$2/$3";

// records controller./fi
// GET fetch records list
// POST create records (single or bulk)
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records'] = "records/index/$2/$3";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)\/records'] = "records/index/$2/$3";
// GET fetch Record
// DELETE drop Record/ PUT edit Record
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)'] = "records/index/$2/$3/$4";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)'] = "records/index/$2/$3/$4";
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/_link'] = "records/index/$2/$3/$4/link";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/_link'] = "records/index/$2/$3/$4/link";

$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/_link\/([\w\-]+)'] = "records/index/$2/$3/$4/link/$5";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/_link\/([\w\-]+)'] = "records/index/$2/$3/$4/link/$5";

// api/v1/databases/db_name/tables/tbl_name/records/recId
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/([\w\-]+)'] = "records/index/$2/$3/$4/fk/$5";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/([\w\-]+)'] = "records/index/$2/$3/$4/fk/$5";
$route['api\/(v\d+)\/databases\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/([\w\-]+)\/([\w\-]+)'] = "records/index/$2/$3/$4/fk/$5/$6";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/tables\/([\w\-]+)\/records\/([\w\-]+)\/([\w\-]+)\/([\w\-]+)'] = "records/index/$2/$3/$4/fk/$5/$6";

$route['api\/(v\d+)\/databases\/([\w\-]+)\/views'] = "views/index/$2";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/views'] = "views/index/$2";
$route['api\/(v\d+)\/databases\/([\w\-]+)\/views\/([\w\-]+)'] = "views/index/$2/$3";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/views\/([\w\-]+)'] = "views/index/$2/$3";
$route['api\/(v\d+)\/databases\/([\w\-]+)\/views\/([\w\-]+)\/records'] = "views/index/$2/$3";
$route['api\/(v\d+)\/sessions\/([\w\-]+)\/views\/([\w\-]+)\/records'] = "views/index/$2/$3";



//------------------------------DBAPI ----------------------------------------------------------------------
$pfx = "v1";


// record($username,$api,$table,$recId)
// valid reqs
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records/([\w\-\_]+)"]["get"] = "arecords/getRecordFromTable/$1/$2/$3/$4";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records/([\w\-\_]+)"]["put"] = "arecords/updateRecord/$1/$2/$3/$4";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records/([\w\-\_]+)"]["delete"] = "arecords/deleteRecords/$1/$2/$3/$4";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records/([\w\-\_]+)"]["options"] = "arecords/options/record";
// invalid reqs
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records/([\w\-\_]+)"]["post"] = "errors/invalid_req/record/post";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records/([\w\-\_]+)"]["patch"] = "errors/invalid_req/record/patch";

// tableRecords($username,$api,$table)
// valid reqs
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records"]["get"] = "arecords/listRecordsFromTable/$1/$2/$3";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records"]["post"] = "arecords/addRecord/$1/$2/$3";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records"]["delete"] = "arecords/deleteRecords/$1/$2/$3";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records"]["options"] = "arecords/options/tableRecords";
// invalid reqs
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records"]["put"] = "errors/invalid_req/records/put";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/tables/([\w\-\_]+)/records"]["patch"] = "errors/invalid_req/records/patch";

// viewRecords($username,$api,$table)
// valid reqs
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/views/([\w\-\_]+)/records"]["get"] = "arecords/listRecordsFromView/$1/$2/$3";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/views/([\w\-\_]+)/records"]["options"] = "arecords/options/viewRecords";
// invalid reqs
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/views/([\w\-\_]+)/records"]["put"] = "errors/invalid_req/views/put";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/views/([\w\-\_]+)/records"]["delete"] = "errors/invalid_req/views/delete";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/views/([\w\-\_]+)/records"]["post"] = "errors/invalid_req/views/post";
$route["^$pfx/([\w\-\_]+)/dbapi/([\w\-\_]+)/views/([\w\-\_]+)/records"]["patch"] = "errors/invalid_req/views/patch";


//------------------------------API ADMIN ----------------------------------------------------------------------
$adminController = "manager";
// API
$route["^admin/test_connection$"] = "$adminController/test_connection";
$route["^admin/apis$"] = "$adminController/apps";

$route["^admin/apis/([\w\-\_]+)$"]["get"] = "$adminController/getApp/$1";
$route["^admin/apis/([\w\-\_]+)$"]["delete"] = "$adminController/deleteApp/$1";
$route["^admin/apis/([\w\-\_]+)$"]["put"] = "$adminController/putApp/$1";
$route["^admin/apis/([\w\-\_]+)$"]["patch"] = "$adminController/patchApp/$1";
//$route["^admin/apis/([\w\-\_]+)$"] = "$adminController/app/$1";
$route["^admin/apis/([\w\-\_]+)/settings$"] = "$adminController/getSetConfig/$1/settings";
$route["^admin/apis/([\w\-\_]+)/connection$"] = "$adminController/getSetConfig/$1/connection";
$route["^admin/apis/([\w\-\_]+)/structure$"] = "$adminController/getSetConfig/$1/structure";
$route["^admin/apis/([\w\-\_]+)/structure/regenerate$"] = "$adminController/regen/$1";

// UX
$route["^launchpad$"] = "launchpad/dashboard";
$route["^launchpad/dbapiator$"] = "launchpad/list_dbapis";
$route["^launchpad/dbapiator/([\w\-\_]+)$"] = "launchpad/api_admin/$1";




