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
$route['default_controller'] = 'dbapi';
//$route['404_override'] = 'errors/not_found';
$route['translate_uri_dashes'] = false;


// /api/apiName/entryPoint
$controller = "dbapi";
$basePath = "v2";

// ApiId part of the domain name
$stdOpsPath = $basePath;


// ApiId part of the server path
//$stdOpsPath = "[0-9a-z]+\/$basePath";
//$blkOpsPath =  "[0-9a-z]+\/".$basePath."\/b";



// API
$route["^api/([\w\-\_]+)"] = "$controller/$1";

// APIID/EP
//$route["^api/([\w\-\_]+)/([\w\-\_]+)"] = "$controller/api/$1";


/**
/
- POST: bulk_create_records
- PATCH: bulk_update_records
- DELETE: bulk_delete_records

/resourceName**
- GET: get_multiple_records
- POST: create_single_record

/resourceName/$id**
- GET: get_single_record
- PATCH: update_single_records
- DELETE: update_single_records

/resourceName/$id/__relationships/$relation**
- GET: get_relationship
- POST: create_relationship
- PATCH: update_relationship
- DELETE: delete_relationship

/resourceName/$id/$relation**
- GET: get_related_records
*/

$route["^swagger"] ="$controller/swagger";
$route["^dm"] ="$controller/dm";
$route["^test"] ="$controller/test";
$route["^test\/(.*)"] ="$controller/test/$1";



// first family: - bulk operations /
// #1
$route["^$stdOpsPath"]["post"] ="$controller/create_multiple_records";
// #2
$route["^$stdOpsPath"]["patch"] ="$controller/update_multiple_records";
// #3
$route["^$stdOpsPath"]["delete"] ="$controller/delete_multiple_records";


// second family: /resourceŃame
// #4 OK
$route["^$stdOpsPath\/([\w\-\_\%]+)"]["get"] = "$controller/get_multiple_records/$1";
// #5
$route["^$stdOpsPath\/([\w\-\_\%]+)"]["post"] = "$controller/create_single_record/$1";


// third family: /resourceName/id
// #6 OK
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)"]["get"] = "$controller/get_single_record/$1/$2";
// #7
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)"]["patch"] = "$controller/update_single_record/$1/$2";
// #8
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)"]["delete"] = "$controller/delete_single_record/$1/$2";


// third family: /resourceName/id/_relationships/relation
// #9
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/_relationships\/([\w\-\_\%]+)"]["get"] = "$controller/get_relationship/$1/$2/$3";
// #10
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/_relationships\/([\w\-\_\%]+)"]["post"] = "$controller/create_relationship/$1/$2/$3";
// #11
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/_relationships\/([\w\-\_\%]+)"]["patch"] = "$controller/update_relationship/$1/$2/$3";
// #12
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/_relationships\/([\w\-\_\%]+)"]["delete"] = "$controller/delete_relationship/$1/$2/$3";


// fourth family: /resourceName/id/relation
// #13
$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/([\w\-\_\%]+)"]["get"] = "$controller/get_related/$1/$2/$3";


$route["^$stdOpsPath\/.*"]["options"] = "$controller/options";


//
//
//$route["^$stdOpsPath\/([\w\-\_\%]+)"]["get"] = "$controller/fetch_multiple/$1/$2";
//$route["^$stdOpsPath\/([\w\-\_\%]+)"]["post"] = "$controller/simple_insert/$1/$2";
//$route["^$stdOpsPath\/([\w\-\_\%]+)"]["delete"] = "$controller/bulk_delete/$1/$2";
//$route["^$stdOpsPath\/([\w\-\_\%]+)"]["patch"] = "$controller/update_bulk/$1/$2";
//$route["^$stdOpsPath\/([\w\-\_\%]+)"]["options"] = "$controller/options/$1/$2";
//
//$route["^$blkOpsPath\/([\w\-\_\%]+)"]["post"] = "$controller/bulk_insert/$1/$2";
//$route["^$blkOpsPath\/([\w\-\_\%]+)"]["patch"] = "$controller/bulk_update/$1/$2";
//$route["^$blkOpsPath\/([\w\-\_\%]+)"]["delete"] = "$controller/bulk_delete/$1/$2";
//$route["^$blkOpsPath\/([\w\-\_\%]+)"]["options"] = "$controller/options/$1/$2";
//
//$route["^swagger"]["get"] = "$controller/swagger";
//
//// api/$apiId/$resName/$resId
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)$"]["get"] = "$controller/fetch_single/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)$"]["delete"] = "$controller/single_delete/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)$"]["patch"] = "$controller/update/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)$"]["post"] = "$controller/create/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)$"]["options"] = "$controller/options/$1/$2/$3";
//
//
//// APIID/RESOURCE/ID/relationships/RELNAME
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/_relationships\/([\w\-\%_]+)"]["get"] = "$controller/fetch_relationships/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/([\w\-\%_]+)"]["get"] = "$controller/fetch_relationships/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/relationships\/([\w\-\%_]+)"]["patch"] = "$controller/update_relationships/$1/$2/$3";
//$route["^$stdOpsPath\/([\w\-\_\%]+)\/([\w\-\_\%]+)\/relationships\/([\w\-\%_]+)"]["options"] = "$controller/options/$1/$2/$3";
//
//

