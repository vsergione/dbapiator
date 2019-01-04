<?php
namespace Apiator\DBApi;

require_once(__DIR__."/../../../libraries/Response.php");

use Response;

/**
 * Class Data_model
 */
class Datamodel {
    private $valid = false;
    private $dataModel = null;

    function is_valid_model() {
        return $this->valid;
    }

    function get_idfld($table) {
        return $this->dataModel[$table]["keyFld"];
    }

    function __construct($dataModel) {
        $this->dataModel = $dataModel;
    }

    /**
     * check if $database exists and initializes the DB
     * @param array $structure
     * @return Datamodel|null
     */
    static function init($structure) {
        if(is_array($structure))
            return new Datamodel($structure);
        return null;
    }

    /**
     * get table fields
     * @param string $tableName table nam
     * @return array|null
     */
    function get_fields($tableName) {
        return $this->dataModel[$tableName]["fields"];
    }

    /**
     * gets configuration of table or full model when no tableName is provided
     *
     * @param string $tableName table name
     * @return Object config model
     *
     */
    function get_config($tableName=null) {
        if(is_null($tableName))
            return $this->dataModel;

        return $this->dataModel[$tableName];
    }


    /**
     * get relation target table name
     * @param string $tableName source table name
     * @param string $relationName relation name
     * @return string target table name or null when relation name not found
     */
    function get_rel_target_tbl($tableName,$relationName) {
        if($this->is_valid_relation($tableName,$relationName))
            return $this->dataModel[$tableName]["relations"][$relationName]["table"];
        return null;
    }

    /**
     * @alias get_rel_target_tbl()
     * @param string $tableName
     * @param string $relationName
     * @return string
     */
    function get_relation_target_table($tableName,$relationName)  {
        return $this->get_rel_target_tbl($tableName,$relationName);
    }


    /**
     * get relation link table name
     * @param string $tableName source table name
     * @param string $relationName relation name
     * @return string|null
     */
    function get_rel_link_tlb($tableName,$relationName) {
        if($this->is_valid_relation($tableName,$relationName))
            return $this->dataModel[$tableName]["relations"][$relationName]["lnkTable"];
        return null;
    }

    /**
     * @alias Data_model::get_rel_link_tlb()
     * @param string $tableName
     * @param string $relationName
     * @return null|string
     */
    function get_relation_link_table($tableName,$relationName)  {
        return $this->get_rel_link_tlb($tableName,$relationName);
    }


    /**
     * get relation descriptor object
     * @param string $tableName source table name
     * @param string $relationName relation name
     * @return object|null
     */
    function get_relation_config($tableName,$relationName) {
        if($this->is_valid_relation($tableName,$relationName))
            return $this->dataModel[$tableName]["relations"][$relationName];
        return null;
    }

    /**
     * get relation type (1:1 or 1:n)
     * @param string $tableName source table name
     * @param string $relationName relation name
     * @return string relation type or null when relation name not found
     */
    function get_rel_type($tableName,$relationName) {
        if($this->is_valid_relation($tableName,$relationName))
            return $this->dataModel[$tableName]["relations"][$relationName]["relType"];
        return null;
    }
    /**
     * @alias get_rel_type()
     */
    function get_relation_type($tableName,$relationName) {
        return $this->get_rel_type($tableName,$relationName);
    }

    /**
     * checks if table exists
     * @param string $name
     * @return boolean true if exists, false otherwise
     */
    function is_valid_table($name) {
        return array_key_exists($name,$this->dataModel) && $this->dataModel[$name]["type"]=="table";
    }

    /**
     * checks if table exists
     * @param string $name
     * @return boolean true if exists, false otherwise
     */
    function is_valid_view($name) {
        return array_key_exists($name,$this->dataModel) && $this->dataModel[$name]["type"]=="view";
    }


    /**
     * checks if relation is valid
     * @param string $tableName source table name
     * @param string $relationName relation nam
     * @return boolean true if exists, false otherwise
     */
    function is_valid_relation($tableName,$relationName) {
        return array_key_exists("relations",$this->dataModel[$tableName]) &&
            array_key_exists($relationName,$this->dataModel[$tableName]["relations"]);

    }

    /**
     * @param $table
     * @param $relationName
     * @param $relatedTable
     * @return bool
     */
    function is_valid_related_table($table,$relationName,$relatedTable) {
        if(!$this->is_valid_relation($table,$relationName))
            return false;
        return $this->dataModel[$table]["relations"][$relationName]["table"]==$relatedTable;
    }

    /**
     * validate field name against datamodel
     *
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    function is_valid_field($tableName, $fieldName) {
        return $this->is_valid_table($tableName) && array_key_exists($fieldName,$this->dataModel[$tableName]["fields"]);
    }

    /**
     * @param $tableName
     * @param $fieldName
     * @return bool
     */
    function is_field_updateable($tableName,$fieldName) {
        return $this->is_valid_field($tableName,$fieldName) && $this->dataModel[$tableName]["fields"][$fieldName]["update"];
    }

    /**
     * @param $tableName
     * @return Response
     */
    function get_key_flds($tableName) {
        $keys = array();
        foreach($this->dataModel[$tableName]["fields"] as $fldName=> $fldSpec)
            if($fldSpec->iskey)
                $keys[] = $fldName;
        return Response::make(true,200,$keys);
    }

    /**
     * @param $table
     * @param $field
     * @return Response
     */
    function get_fk_relation($table,$field) {
        if(!$this->is_valid_field($table,$field))
            return Response::make(false,500,"Invalid field");
        if(!property_exists($this->dataModel->$table->fields->$field,"foreignKey"))
            return Response::make(false,500,"Invalid attribute");

        return Response::make(true,null,$this->dataModel[$table]["fields"][$field]["foreignKey"]);
    }

    /**
     * type validation & type casting of proposed value against field type
     *
     * @param string $tableName table name
     * @param string $fieldName field name
     * @param string $value value to be validated
     * @return Response
     */
    function is_valid_value($tableName,$fieldName,$value) {
        $mysqlTypes = [
            "numeric"=>[
                "int","tinyint","smallint","mediumint","int","bigint","decimal","float","double","real","bit","boolean","serial"
            ],
            "date"=>[
                "date","datetime","timestamp","time","year"
            ],
            "string"=>[
                "char","varchar","tinytext","text","mediumtext","longtext",
                "binary","varbinary","tinyblob","mediumblob","blob","longblob","enum","set"
            ],
            "spatial"=>[
                "geometry","point","linestring","polygon","multipoint","multilinestring","multipolygon","geometrycollection"
            ],
            "json"=>["json"]
        ];
        // $boolValid = array("1"=>true,"0"=>false,1=>true,0=>false,true=>true,false=>false,"true"=>true,"false"=>false);

        if(!$this->is_valid_field($tableName,$fieldName))
            return Response::make(false,400,"Invalid field $fieldName");

        $fields = $this->get_fields($tableName);

        if($value==="") {
            if(in_array($fields[$fieldName]["type"]["proto"],$mysqlTypes["numeric"]))
                $value = null;
            elseif (in_array($fields[$fieldName]["type"]["proto"],$mysqlTypes["date"]))
                $value = null;
        }

        // ToDO: implement length check
        // $length = property_exists($fields->$fieldName->type,"length") ? $fields->$fieldName->type->length : null;

        if(!$fields[$fieldName]["required"] && is_null($value))
            return Response::make(true, 200, null);

        if(is_object($value)) {
            if (array_key_exists("foreignKey", $fields[$fieldName])
                && $fields[$fieldName]["foreignKey"]["table"] == $value->data->type) {
                return Response::make(true, 200, $value);
            }
            else
                return Response::make(false, 400, "Invalid object as field value");

        }

        switch($fields[$fieldName]["type"]["proto"]) {
            // numeric types
            case "tinyint":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "smallint":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "mediumint":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "int":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "bigint":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "decimal":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "float":
                if(is_numeric($value))
                    $value *= 1;
                if(in_array(gettype($value), ["float","double","integer"]))
                    return Response::make(true,200,$value);
                break;
            case "double":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "real":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "bit":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "boolean":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            case "serial":
                if(is_numeric($value)) {
                    return Response::make(true, 200, $value);
                }
                break;
            // DATE & TIME
            case "datetime":
                if(preg_match("/^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$/",$value))
                    return Response::make(true,200,$value);
                break;
            case "date":
                if(preg_match("/^\d{4}\-\d{2}\-\d{2}$/",$value))
                    return Response::make(true,200,$value);
                break;
            case "timestamp":
                if(preg_match("/^\d{4}\-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/i",$value))
                    return Response::make(true,200,$value);
                break;
            case "time":
                if(preg_match("/^\-?\d{2,3}:\d{2}:\d{2}$/i",$value))
                    return Response::make(true,200,$value);
                break;
            case "year":
                if(is_numeric($value) && ($value*1)<9999)
                    return Response::make(true,200,$value);
                break;
            // TEXT
            case "char":
                return Response::make(true,200,$value."");
            case "varchar":
                return Response::make(true,200,$value."");
            case "tinytext":
                return Response::make(true,200,$value."");
            case "text":
                return Response::make(true,200,$value."");
            case "mediumtext":
                return Response::make(true,200,$value."");
            case "longtext":
                return Response::make(true,200,$value."");
            case "binary":
                return Response::make(true,200,$value."");
            case "varbinary":
                return Response::make(true,200,$value."");
            case "tinyblob":
                return Response::make(true,200,$value."");
            case "mediumblob":
                return Response::make(true,200,$value."");
            case "blob":
                return Response::make(true,200,$value."");
            case "longblob":
                return Response::make(true,200,$value."");
            // SET
            case "set":
                if(in_array($value,$fields[$fieldName]["type"]["vals"]))
                    return Response::make(true,200,$value);
                break;
            case "enum":
                if(in_array($value,$fields[$fieldName]["type"]["vals"]))
                    return Response::make(true,200,$value);
                break;
            default:
                return Response::make(false,500,"Invalid type in configuration file for $fieldName");
        }

        // check if field is required but value is null and set error message accordingly
        if($fields[$fieldName]["required"] && is_null($value)) {
            $msg = "Field $fieldName is required";
        }
        else {
            $msg = "Invalid type of value for field $fieldName";
        }

        return Response::make(false, 400, $msg);
    }

    /**
     * checks if field can be used in where condition;
     * defaults to yes when no searchable fld present in the field config
     * @param $table
     * @param $field
     * @return bool
     */
    function is_searchable_field($table,$field) {
        if(!$this->is_valid_field($table,$field))
            return false;
        return array_key_exists("searchable",$this->dataModel[$table]["fields"][$field])?
            $this->dataModel[$table]["fields"][$field]["searchable"]:true;
    }


    /**
     * checks if
     * @param $table
     * @param $rels
     * @return array
     */
    function validate_object_relationships($table,$rels) {
        if(!is_object($rels))
            return array(false,"rels is not an object");

        // iterate through object properties
        foreach($rels as $relName=>$relData) {

            // is it a valid relation?
            if($this->is_valid_relation($table,$relName)) {

                // validate $relData & $relData->data to be objects
                if(!is_object($relData) || !property_exists($relData,"data")) {
                    unset($rels->$relName);
                    break;
                }

                $relData = is_object($relData->data)?array($relData->data):(is_array($relData->data)?$relData->data:false);
                if($relData===false) {
                    unset($rels->$relName);
                    break;
                }
                foreach($relData as $idx=>$rel) {
                    if(property_exists($rel,"type") && property_exists($rel,"id")) {
                        if($this->is_valid_related_table($table,$relName,$rel->type)) {
                            break;
                        }
                    }
                    unset($rels->$relName->data[$idx]);
                }
            }
        }

        return array(true,$rels);
    }


    /**
     * Validates if a field is foreignKey
     * @param string $table
     * @param string $field
     * @return bool
     */
    function is_fk_field($table,$field) {
        if(!$this->is_valid_field($table,$field))
            return false;
        return array_key_exists("foreignKey",$this->dataModel[$table]["fields"][$field]);
    }


    /**
     * @param $table
     * @param $attrs
     * @param string $operation
     * @return Response
     */
    function validate_object_attributes($table,$attrs,$operation="ins") {
        if(!$this->is_valid_table($table))
            return Response::make(false,404,"table '$table' not found");
        $attrFlds = array_keys(get_object_vars($attrs));

        foreach($this->dataModel[$table]["fields"] as $fldName=>$fldSpec) {
            if($fldSpec["required"] && is_null($fldSpec["default"]) && !in_array($fldName,$attrFlds) && $operation=="ins")
                return Response::make(false,400,"required attribute '$fldName' not provided");

            // field not allowed to insert
            if(in_array($fldName,$attrFlds) && $fldSpec["insert"]==false && $operation=="ins")
                return Response::make(false,400,"attribute '$fldName' not allowed to be inserted");

            // field not allowed to update
            if(in_array($fldName,$attrFlds) && $fldSpec["update"]==false && $operation=="upd")
                return Response::make(false,400,"attribute '$fldName' not allowed to be updated");
        }

        foreach($attrs as $attrName=>$attrVal) {
            $response = $this->is_valid_value($table,$attrName,$attrVal);

            /**
             * TODO: instead of just checking if value is an object as exception when value type validation fails implement a proper mechanism inside the is_valid_value method
             */
            if(!$response->success && !is_object($attrVal))
                return $response;

            if(!is_object($attrVal))
                $attrs->$attrName = $response->data;
        }
        return Response::make(true,200,$attrs);
    }



    /**
     * validate if field is key field
     * @param $table
     * @param $fieldName
     * @return bool
     */
    function is_key_field($table,$fieldName) {
        $res = $this->is_valid_field($table,$fieldName);
        if(!$res)
            return false;

        return $this->dataModel->$table->fields->$fieldName->iskey;
    }
}

function array_key_exists_and_has_value($array,$key,$value) {
    return array_key_exists($key,$array) && $array[$key] = $value;
}