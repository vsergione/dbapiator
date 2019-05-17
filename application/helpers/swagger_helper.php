<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 5/3/19
 * Time: 9:44 AM
 */

/**
 * @param $name
 * @param $def
 * @param $dm
 * @return array
 */
function get_collection($name, $def, $dm)
{
    if(!$def["read"])
        return [];

    $data = [
        "summary" => "Get '$name' records list",
        "parameters" => [],
        "responses"=> [
            "200"=> [
                "description"=> "Status 200",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "data"=> [
                            "type"=> "array",
                            "items"=> [
                                "\$ref"=> "#/definitions/ResourceObject"
                            ]
                        ],
                        "meta"=> [
                            "required"=> [
                                "offset",
                                "total"
                            ],
                            "type"=> "object",
                            "properties"=> [
                                "offset"=> [
                                    "type"=> "integer"
                                ],
                                "total"=> [
                                    "type"=> "integer"
                                ]
                            ]
                        ],
                        "includes"=> [
                            "type"=> "array",
                            "items"=> [
                                "type"=> "object"
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $data["parameters"][] = [
        "name" => "filter",
        "in" => "query",
        "required" => false,
        "type" => "string",
        "description" => "Comma separated list of filter criteria. \n\nA filtering criteria is expressed as follows: fieldName [operator] value, where operator can be:\n- ! negates the operator which follows. Not to be used alone\n- = equals\n- ~= begins with\n- =~ ends with\n- ~=~ contains\n- &lt; smaller then\n- &lt;= equal or smaller then\n- &gt; bigger then\n- &gt;= equal or bigger then\n- &gt;&lt; in list. In this case the value should be a list of semicolon separated values\n\nPossible values for fieldName: id, title, content, creation_date, author, category, public",
        "x-example" => ""
    ];

    $data["parameters"][] = [
        "name" => "offset",
        "in" => "query",
        "required" => false,
        "type" => "string",
        "description" => "Recordset offset",
        "x-example" => ""
    ];
    $data["parameters"][] = [
        "name" => "limit",
        "in" => "query",
        "required" => false,
        "type" => "string",
        "description" => "Maximum number of record to include in the result set",
        "x-example" => ""
    ];

    $fields =[];
    $includes = [];
    search_recursive($name,$def,[],$fields,$includes,$dm,100);
    $fields = array_map(function ($item){
        return array_unique($item);
    },$fields);

    if(count($fields)) {
        foreach ($fields as $fld=>$values) {
            $data["parameters"][] = [
                "name" => "fields[$fld]",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "description" => "Comma separated list of '$name' field names when 'includes' parameter contains a relation of type '$name'",
                "x-example" => join(",",$values)
            ];
        }
    }
    if(count($includes)) {
        $data["parameters"][] = [
            "name" => "includes",
            "in" => "query",
            "required" => false,
            "type" => "string",
            "description" => "Comma separated list of relationships to include. See example for list of valid values",
            "x-example" => implode(", ",$includes)
        ];
    }
    return $data;
}



/**
 * @param $name
 * @param $def
 * @param $dm
 * @return array
 */
function get_record($name, $def, $dm)
{
    if(!$def["read"])
        return [];

    $data = [
        "summary" => "Get '$name' record by ID",
        "parameters" => [],
        "responses"=> [
            "200"=> [
                "description"=> "Status 200",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "data"=> [
                            "\$ref"=> "#/definitions/ResourceObject"
                        ],
                        "includes"=> [
                            "type"=> "array",
                            "items"=> [
                                "\$ref"=> "#/definitions/ResourceObject"
                            ]
                        ]
                    ]
                ]
            ],
            "404"=>[
                "description"=> "Record not found",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "errors"=> [
                            "\$ref"=> "#/definitions/ErrorObject"
                        ]
                    ]
                ]
            ]
        ]
    ];


    $fields =[];
    $includes = [];
    search_recursive($name,$def,[],$fields,$includes,$dm,100);
    $fields = array_map(function ($item){
        return array_unique($item);
    },$fields);

    if(count($fields)) {
        foreach ($fields as $fld=>$values) {
            $data["parameters"][] = [
                "name" => "fields[$fld]",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "description" => "Comma separated list of '$name' field names when 'includes' parameter contains a relation of type '$name'",
                "x-example" => join(",",$values)
            ];
        }
    }
    if(count($includes)) {
        $data["parameters"][] = [
            "name" => "includes",
            "in" => "query",
            "required" => false,
            "type" => "string",
            "description" => "Comma separated list of relationships to include. See example for list of valid values",
            "x-example" => implode(", ",$includes)
        ];
    }
    return $data;
}

/**
 * @param $name
 * @param $def
 * @param $dm
 * @return array
 */
function get_relation($name, $def, $dm)
{
    if(!$def["read"])
        return [];

    $data = [
        "summary" => "Get '$name' record by ID",
        "parameters" => [],
        "responses"=> [
            "200"=> [
                "description"=> "Status 200",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "data"=> [
                            "type"=>"object"
                        ],
                        "includes"=> [
                            "type"=> "array",
                            "items"=> [
                                "type"=> "object"
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];


    $fields =[];
    $includes = [];
    search_recursive($name,$def,[],$fields,$includes,$dm,100);
    $fields = array_map(function ($item){
        return array_unique($item);
    },$fields);

    if(count($fields)) {
        foreach ($fields as $fld=>$values) {
            $data["parameters"][] = [
                "name" => "fields[$fld]",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "description" => "Comma separated list of '$name' field names when 'includes' parameter contains a relation of type '$name'",
                "x-example" => join(",",$values)
            ];
        }
    }
    if(count($includes)) {
        $data["parameters"][] = [
            "name" => "includes",
            "in" => "query",
            "required" => false,
            "type" => "string",
            "description" => "Comma separated list of relationships to include. See example for list of valid values",
            "x-example" => implode(", ",$includes)
        ];
    }
    return $data;
}



/**
 * @param string $resName
 * @param array $resSpec
 * @param array $path
 * @param array $fields
 * @param array $includes
 * @param array $dm
 * @param int $recursionLevel
 */
function search_recursive($resName,$resSpec,$path,&$fields,&$includes,&$dm,$recursionLevel)
{
    if($recursionLevel--<0)
        return;

    if(isset($fields[$resName]))
        return;


    foreach ($resSpec["fields"] as $fldName => $fldSpec) {
        if ($fldSpec["select"])
            $fields[$resName][] = $fldName;
    }

    if(!isset($resSpec["relations"]))
        return;

    foreach ($resSpec["relations"] as $relName => $relSpec) {
        if ($relSpec["select"])
            $fields[$resName][] = $relName;
    }
    foreach ($resSpec["relations"] as $relName => $relSpec) {
        if ($relSpec["select"]) {
            $newPath = array_merge($path,[$relName]);
            $newPathStr = implode(".",$newPath);
            if(!in_array($newPathStr,$includes)) {
                $includes[] = $newPathStr;
                search_recursive($relSpec["table"],$dm[$relSpec["table"]],$newPath,$fields,$includes,$dm,$recursionLevel);
            }
        }
    }

}

/**
 * @param $resName
 * @param $def
 * @return array|void
 */
function patch_single_record($resName,$def)
{
    if(!$def["update"])
        return;

    $data = [
        "summary"=>"Update record of type $resName",
        "responses"=> [
            "200"=> [
                "description"=> "Returns updated record",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "data"=> [
                            "\$ref"=> "#/definitions/ResourceObject"
                        ],
                        "includes"=> [
                            "type"=> "array",
                            "items"=> [
                                "\$ref"=> "#/definitions/ResourceObject"
                            ]
                        ]
                    ]
                ]
            ],
            "404"=>[
                "description"=> "Record not found",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "errors"=> [
                            "\$ref"=> "#/definitions/ErrorObject"
                        ]
                    ]
                ]
            ]
        ]
    ];

    return $data;
}

/**
 * @param $def
 * @return array
 */
function post_single_record($resName,$def)
{
    if(!$def["insert"])
        return [];

    $fields = [];
    foreach ($def["fields"] as $fldName => $fldSpec) {
        if($fldSpec["update"])
            $fields[] = $fldName;
    }

    foreach ($def["relations"] as $relName => $relSpec) {
        if($relSpec["update"])
            $fields[] = $relName;
    }
    $fields = array_unique($fields);

    $data = [
        "summary"=>"Add new record of type $resName",
        "parameters"=>[
            [
                "name" => "onduplicate",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "enum"=>[
                    "update",
                    "ignore",
                    "error"
                ],
                "default"=>[
                    "error"
                ],
                "description" => "Comma separated list of relationships to include. See example for list of valid values",
                "x-example" => "onduplicate=update"
            ],
            [
                "name" => "update",
                "in" => "query",
                "required" => false,
                "type" => "string",
                "description" => "Comma separated list of relationships to include. See example for list of valid values",
                "x-example" => "update=".implode(",",$fields)
            ]
        ],
        "responses"=>[
            "200"=> [
                "description"=> "Status 200",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "data"=> [
                            "\$ref"=> "#/definitions/ResourceObject"
                        ],
                        "includes"=> [
                            "type"=> "array",
                            "items"=> [
                                "\$ref"=> "#/definitions/ResourceObject"
                            ]
                        ]
                    ]
                ]
            ],
            "404"=>[
                "description"=> "Status 200",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "errors"=> [
                            "\$ref"=> "#/definitions/ErrorObject"
                        ]
                    ]
                ]
            ]
        ]
    ];

    return $data;
}

/**
 * @param $name
 * @param $def
 * @return array
 */
function delete_single_record($name, $def)
{
    if(!$def["delete"])
        return [];

    $data = [
        "summary" => "Delete record by ID from '$name'",
        "responses"=> [
            "204"=> [
                "description"=> "Record successfully deleted"
            ],
            "404"=>[
                "description"=> "Record not found",
                "schema"=> [
                    "type"=> "object",
                    "properties"=> [
                        "errors"=> [
                            "type"=> "array",
                            "items"=> [
                                "\$ref"=> "#/definitions/ErrorObject"
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $data;
}


/**
 * @param $apiId
 * @param $dataModel
 * @return array
 */
function generate_swagger($apiId,$dataModel)
{
    $openApiSpec =  [
        "swagger" => "2.0",
        "info" => [
            "description" => "Demoblog DB API",
            "version" => "1.0.0",
            "title" => "Demoblog",
            "contact" => [
                "name" => "Sergiu Voicu",
                "email" => "svoicu@softaccel.net"
            ],
            "license" => [
                "name" => "GPL"
            ]
        ],
        "host" => "$apiId.api.apiator",
        "basePath" => "/v2",
        "schemes" => [ "https" ],
        "consumes" => [ "application/vnd.api+json" ],
        "produces" => [ "application/vnd.api+json" ],
        "paths"=>[],
        "definitions"=> [
            "ResourceObject"=> [
                "type"=> "object",
                "required"=> [
                    "id",
                    "type"
                ],
                "properties"=> [
                    "id"=> [
                        "type"=> "string"
                    ],
                    "type"=> [
                        "type"=> "string"
                    ],
                    "attributes"=> [
                        "type"=> "array",
                        "items"=> [
                            "type"=> "string"
                        ]
                    ],
                    "relationships"=> [
                        "type"=> "object"
                    ],
                    "meta"=> [
                        "type"=> "object"
                    ],
                    "links"=> [
                        "type"=> "string"
                    ]
                ]
            ],
            "ErrorObject"=> [
                "type"=> "object",
                "properties"=> [
                    "id"=> [
                        "type"=> "string"
                    ],
                    "status"=> [
                        "type"=> "string"
                    ],
                    "code"=> [
                        "type"=> "string"
                    ],
                    "title"=> [
                        "type"=> "string"
                    ],
                    "detail"=> [
                        "type"=> "string"
                    ],
                    "source"=> [
                        "type"=> "string"
                    ],
                    "meta"=> [
                        "type"=> "object"
                    ]
                ]
            ]
        ]
    ];


    /************************************************
     * /b/resource/ID/relationships
     ***********************************************/
    $openApiSpec["paths"]["/"] = [
        "post"=>[
            "summary" => "Bulk create records. Returns the records which have been created. Duplicates will be ignored",
            "responses"=>[
                "200"=>[
                    "description"=> "Record successfully deleted",
                    "schema"=>[
                        "type"=>"object",
                        "properties"=>[
                            "data"=>[
                                "type"=>"array",
                                "\$ref"=> "#/definitions/ResourceObject"
                            ],
                            "includes"=>[
                                "type"=>"array",
                                "\$ref"=> "#/definitions/ResourceObject"
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "patch"=>[
            "summary" => "Bulk update records. Returns the records which have been updated",
            "responses"=>[
                "200"=>[
                    "description"=> "Record successfully deleted",
                    "schema"=>[
                        "type"=>"object",
                        "properties"=>[
                            "data"=>[
                                "type"=>"array",
                                "\$ref"=> "#/definitions/ResourceObject"
                            ],
                            "includes"=>[
                                "type"=>"array",
                                "\$ref"=> "#/definitions/ResourceObject"
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    foreach ($dataModel as $resName=>$resSpec) {
        /************************************************
         * /s/resource
         ***********************************************/

        $resPath = "/$resName";
        $openApiSpec["paths"][$resPath] = [
            //"summary"=>"Retrieve and manipulate '$resName' collections"
        ];
        // add retrieve
        if($data=get_collection($resName,$resSpec,$dataModel)) {
            $openApiSpec["paths"][$resPath]["get"] = $data;
        }
        // add create
        if($data=post_single_record($resName,$resSpec)) {
            $openApiSpec["paths"][$resPath]["post"] = $data;
        }




        /************************************************
         * /s/resource/ID
         ***********************************************/
        $resPath = "$resPath/{".$resSpec["keyFld"]."}";
        $openApiSpec["paths"][$resPath] = [
            //"summary"=>"Retrieve and manipulate '$resName' records"
        ];
        if($data=get_record($resName,$resSpec,$dataModel)) {
            $openApiSpec["paths"][$resPath]["get"] = $data;
        }
        if($data=delete_single_record($resName,$resSpec,$dataModel)) {
            $openApiSpec["paths"][$resPath]["delete"] = $data;
        }
        // add update
        if($data=patch_single_record($resName,$resSpec)) {
            $openApiSpec["paths"][$resPath]["patch"] = $data;
        }

        if(count($openApiSpec["paths"][$resPath]))
            $openApiSpec["paths"][$resPath]["parameters"][] = [
                "name" => $resSpec["keyFld"],
                "in" => "path",
                "required" => true,
                "type" => "string"
            ];
        else
            unset($openApiSpec["paths"][$resPath]);



        /************************************************
         * /s/resource/ID/relationships
         ***********************************************/
        $resPath = "$resPath/relationships/";
        foreach ($resSpec["relations"] as $relName=>$relSpec) {
            $openApiSpec["paths"][$resPath.$relName] = [];
            if($get=get_relation($relSpec["table"],$dataModel[$relSpec["table"]],$dataModel)) {
                $openApiSpec["paths"][$resPath . $relName]["get"] = $get;
            }
        }
    }

    return $openApiSpec;
}
