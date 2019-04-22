<?php
namespace Apiator\DBApi;

//require_once(__DIR__."/../../../libraries/Response.php");

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
     * @param $resName
     * @param $fldName
     * @return bool
     * @throws \Exception
     */
    function field_is_selectable($resName, $fldName)
    {
        if(!isset($this->dataModel[$resName]["fields"][$fldName]))
            throw new \Exception("Invalid field $fldName (is_selectable)",400);
        return isset($this->dataModel[$resName]["fields"][$fldName]["select"])?
            $this->dataModel[$resName]["fields"][$fldName]["select"]:false;
    }

    /**
     * @param $resName
     * @param $fldName
     * @return bool
     * @throws \Exception
     */
    function field_is_insertable($resName, $fldName)
    {
        //print_r($this->dataModel[$resName]);
        if(!isset($this->dataModel[$resName]["fields"][$fldName]))
            throw new \Exception("Invalid field $fldName (is_insertable)",400);
        return isset($this->dataModel[$resName]["fields"][$fldName]["insert"])?
            $this->dataModel[$resName]["fields"][$fldName]["insert"]:false;
    }

    /**
     * @param $resName
     * @param $fldName
     * @return bool
     * @throws \Exception
     */
    function field_is_updateable($resName, $fldName)
    {
        if(!isset($this->dataModel[$resName]["fields"][$fldName]))
            throw new \Exception("Invalid field $fldName (is_updateable)",400);
        //print_r($this->dataModel[$resName]["fields"][$fldName]["update"]);
        return isset($this->dataModel[$resName]["fields"][$fldName]["update"])?
            $this->dataModel[$resName]["fields"][$fldName]["update"]:false;
    }

    /**
     * @param $resName
     * @param $fldName
     * @return bool
     * @throws \Exception
     */
    function field_is_sortable($resName, $fldName)
    {
        if(!isset($this->dataModel[$resName]["fields"][$fldName]))
            throw new \Exception("Invalid field $fldName (is_sortable)",400);
        return isset($this->dataModel[$resName]["fields"][$fldName]["sortable"])?
            $this->dataModel[$resName]["fields"][$fldName]["sortable"]:false;
    }

    /**
     * @param $resName
     * @param $fldName
     * @return bool
     * @throws \Exception
     */
    function field_is_searchable($resName, $fldName)
    {
        if(!isset($this->dataModel[$resName]["fields"][$fldName]))
            throw new \Exception("Invalid field $fldName (is_searchable)",400);
        return isset($this->dataModel[$resName]["fields"][$fldName]["sortable"])?
            $this->dataModel[$resName]["fields"][$fldName]["searchable"]:false;
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
     * return name of the field used as primary key
     * @param $resName
     * @return mixed
     */
    function get_key_fld($resName)
    {
        return $this->dataModel[$resName]["keyFld"];
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
    function get_relation_type($tableName, $relationName) {
        if($this->is_valid_relation($tableName,$relationName))
            return $this->dataModel[$tableName]["relations"][$relationName]["relType"];
        return null;
    }


    /**
     * checks if resource exists
     * @param string $name
     * @return boolean true if exists, false otherwise
     */
    function resource_exists($name) {
        return array_key_exists($name,$this->dataModel);
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
        return $this->resource_exists($tableName) && array_key_exists($fieldName,$this->dataModel[$tableName]["fields"]);
    }

    /**
     * @param $tableName
     * @return array
     */
    function get_key_flds($tableName) {
        $keys = [];
        foreach($this->dataModel[$tableName]["fields"] as $fldName=> $fldSpec)
            if($fldSpec["iskey"])
                $keys[] = $fldName;

        return $keys;
    }

    /**
     * @param $resName
     * @param $field
     * @return Response
     * @throws \Exception
     */
    function get_outbound_relation($resName, $field) {

        if(!$this->resource_exists($resName))
            throw new \Exception("Invalid resource $resName",400);

        if(!$this->is_valid_field($resName,$field))
            throw new \Exception("Invalid field $field",400);

        if(!isset($this->dataModel[$resName]["fields"][$field]["foreignKey"]))
            throw new \Exception("Field $field is not a foreign key",400);

        return $this->dataModel[$resName]["fields"][$field]["foreignKey"];
    }

    /**
     * @param $table
     * @param $relName
     * @return mixed
     * @throws \Exception
     */
    function get_relationship($table,$relName)
    {
        if (!$this->resource_exists($table))
            throw new \Exception("Invalid resource $table", 400);
        if (!isset($this->dataModel[$table]["referencedBy"])
            || !isset($this->dataModel[$table]["referencedBy"][$relName]))
            throw new \Exception("Invalid relationship $relName of $table");

        return $this->dataModel[$table]["referencedBy"][$relName];
    }

    /**
     * type validation & type casting of proposed value against field type
     *
     * @param string $tableName table name
     * @param string $fieldName field name
     * @param mixed $value value to be validated
     * @return mixed
     * @throws \Exception
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
            throw new \Exception("Invalid field $fieldName",400);

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
            return null;

        //print_r($value);

        if(is_object($value)) {
            if (array_key_exists("foreignKey", $fields[$fieldName])
                && $fields[$fieldName]["foreignKey"]["table"] == $value->data->type) {
                return $value;
            }
            else
                throw new \Exception("Invalid object as field value",400);

        }

        switch($fields[$fieldName]["type"]["proto"]) {
            // numeric types
            case "tinyint":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "smallint":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "mediumint":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "int":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "bigint":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "decimal":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "float":
                if(is_numeric($value))
                    $value *= 1;
                if(in_array(gettype($value), ["float","double","integer"]))
                    return $value;
                break;
            case "double":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "real":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "bit":
                if(is_numeric($value)) {
                    return $value*1;
                }
                break;
            case "boolean":
                if(is_bool($value)) {
                    return $value;
                }
                break;
            case "serial":
                if(is_numeric($value)) {
                    return $value;
                }
                break;
            // DATE & TIME
            case "datetime":
                if(preg_match("/^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$/",$value))
                    return $value;
                break;
            case "date":
                if(preg_match("/^\d{4}\-\d{2}\-\d{2}$/",$value))
                    return $value;
                break;
            case "timestamp":
                if(preg_match("/^\d{4}\-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/i",$value))
                    return $value;
                break;
            case "time":
                if(preg_match("/^\-?\d{2,3}:\d{2}:\d{2}$/i",$value))
                    return $value;
                break;
            case "year":
                if(is_numeric($value) && ($value*1)<9999)
                    return $value;
                break;
            // TEXT
            case "char":
                return $value;
            case "varchar":
                return $value;
            case "tinytext":
                return $value;
            case "text":
                return $value;
            case "mediumtext":
                return $value;
            case "longtext":
                return $value;
            case "binary":
                return $value;
            case "varbinary":
                return $value;
            case "tinyblob":
                return $value;
            case "mediumblob":
                return $value;
            case "blob":
                return $value;
            case "longblob":
                return $value;
            // SET
            case "set":
                if(in_array($value,$fields[$fieldName]["type"]["vals"]))
                    return $value;
                break;
            case "enum":
                if(in_array($value,$fields[$fieldName]["type"]["vals"]))
                    return $value;
                break;
            default:
                throw new \Exception("Invalid type in configuration file for $fieldName",500);

        }

        // check if field is required but value is null and set error message accordingly
        if($fields[$fieldName]["required"] && is_null($value)) {
            $msg = "Field $fieldName is required";
        }
        else {
            $msg = "Invalid type of value for field $fieldName";
        }

        throw new \Exception($msg,400);
    }


    /**
     * checks if
     * @param string $resName
     * @param array $relations
     * @return array
     */
    function validate_object_relationships($resName, $relations) {

        // iterate through object properties
        foreach($relations as $relName=> $relData) {

            // is it a valid relation?
            if($this->is_valid_relation($resName,$relName)) {

                // validate $relData & $relData->data to be objects
                if(!is_object($relData) || !property_exists($relData,"data")) {
                    unset($relations->$relName);
                    break;
                }

                $relData = is_object($relData->data)?array($relData->data):(is_array($relData->data)?$relData->data:false);
                if($relData===false) {
                    unset($relations->$relName);
                    break;
                }
                foreach($relData as $idx=>$rel) {
                    if(property_exists($rel,"type") && property_exists($rel,"id")) {
                        if($this->is_valid_related_table($resName,$relName,$rel->type)) {
                            break;
                        }
                    }
                    unset($relations->$relName->data[$idx]);
                }
            }
        }

        return array(true,$relations);
    }


    /**
     * Validates if a field is foreignKey
     * @param string $resName
     * @param string $field
     * @return bool
     */
    function is_fk_field($resName, $field) {
        return array_key_exists("foreignKey",$this->dataModel[$resName]["fields"][$field]);
    }


    /**
     * @param $resName
     * @param $attributes
     * @param string $operation
     * @return mixed
     * TODO: review this method
     * @throws \Exception
     */
    function validate_object_attributes($resName, $attributes, $operation="ins") {

        if(!$this->resource_exists($resName))
            throw new \Exception("table '$resName' not found",400);

        if(is_object($attributes))
            $attributes = (array) $attributes;
        //if(!isset($data->attributes)) throw new

        $attributesNames = array_keys($attributes);

        foreach($this->dataModel[$resName]["fields"] as $fldName=> $fldSpec) {
            if($fldSpec["required"] && is_null($fldSpec["default"]) && !in_array($fldName,$attributesNames) && $operation=="ins")
                throw new \Exception("Required attribute '$fldName' not provided",400);

            // field not allowed to insert
            if(in_array($fldName,$attributesNames) && $fldSpec["insert"]==false && $operation=="ins")
                throw new \Exception("Attribute '$fldName' not allowed to be inserted",400);

            // field not allowed to update
            if(in_array($fldName,$attributesNames) && $fldSpec["update"]==false && $operation=="upd")
                throw new \Exception("Attribute '$fldName' not allowed to be updated",400);

        }

        foreach($attributes as $attrName=> $attrVal) {
            $attrVal = $this->is_valid_value($resName,$attrName,$attrVal);

            /**
             * TODO: instead of just checking if value is an object as exception when value type validation fails
             *       implement a proper mechanism inside the is_valid_value method
             */


            if(!is_object($attrVal))
                $attributes[$attrName] = $attrVal;
        }
        return $attributes;
    }



    /**
     * validate if field is key field
     * @param string $resName resource name
     * @param string $fieldName field name
     * @return bool
     */
    function is_key_field($resName, $fieldName) {
        $res = $this->is_valid_field($resName,$fieldName);
        if(!$res)
            return false;

        return $this->dataModel->$resName->fields->$fieldName->iskey;
    }

    /**
     * @param $tableName
     * @return array
     * @throws \Exception
     */
    public function get_selectable_fields ($tableName)
    {
        if(!isset($this->dataModel[$tableName]))
            throw new \Exception("Invalid table $tableName",404);

        $fields = [];
        foreach ($this->dataModel[$tableName]["fields"] as $fldName=>$fldSpec) {
            if(isset($fldSpec["select"]) && $fldSpec["select"]) {
                $fields[] = $fldName;
            }
        }
        return $fields;
    }

    /**
     * @param $tableName
     * @param $relName
     * @return mixed
     * @throws \Exception
     */
    public function get_inbound_relation ($tableName,$relName)
    {
        //echo "$tableName $relName";
        if(!isset($this->dataModel[$tableName]["referencedBy"]) || !isset($this->dataModel[$tableName]["referencedBy"][$relName]))
            return null;

        return $this->dataModel[$tableName]["referencedBy"][$relName];
    }

    /**
     * @param $resName
     * @return bool
     */
    public function resource_allow_read ($resName)
    {
        return isset($this->dataModel[$resName]["read"])?$this->dataModel[$resName]["read"]:false;
    }

    /**
     * @param $resName
     * @return bool
     */
    public function resource_allow_insert ($resName)
    {
        return isset($this->dataModel[$resName]["create"])?$this->dataModel[$resName]["create"]:false;

    }

    /**
     * @param $resName
     * @return bool
     */
    public function resource_allow_update ($resName)
    {
        return isset($this->dataModel[$resName]["update"])?$this->dataModel[$resName]["update"]:false;
    }

    /**
     * @param $resName
     * @return bool
     */
    public function resource_allow_delete ($resName)
    {
        return isset($this->dataModel[$resName]["delete"])?$this->dataModel[$resName]["delete"]:false;
    }

    /**
     * @param $resName
     * @return array
     */
    public function get_fk_fields ($resName)
    {
        $fks = [];
        foreach ($this->dataModel[$resName]["fields"] as $fieldName=>$fieldSpec) {
            if(isset($fieldSpec["foreignKey"]))
                $fks[$fieldName] = $fieldSpec["foreignKey"];
        }

        return $fks;
    }

    /**
     * @param $tableName
     * @return null
     */
    public function get_inbound_relations ($tableName)
    {
        if(!isset($this->dataModel[$tableName]["referencedBy"])) {
            return [];
        }
        return $this->dataModel[$tableName]["referencedBy"];
    }


}

function array_key_exists_and_has_value($array,$key,$value) {
    return array_key_exists($key,$array) && $array[$key] = $value;
}