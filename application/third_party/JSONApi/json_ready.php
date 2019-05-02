<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 4/10/19
 * Time: 5:39 PM
 */

namespace JSONApi;


abstract class json_ready
{
    function json_data()
    {
        $ret = [];
        foreach ($this as $lbl=>$val) {
            switch (gettype($val)) {
                case "object":
                    if(method_exists($val,"json_data")) {

                        $ret[$lbl] = $val->json_data();
                    }
                    else
                        $ret[$lbl] = $val;
                    break;
                case "array":
                    $ret[$lbl] = [];
                    foreach ($val as $key=>$item) {
                        if (method_exists($item, "json_data")) {
                            $ret[$lbl][] = $item->json_data();
                        }
                        else {
                            $ret[$lbl][] = $item;
                        }
                    }


                    break;
                default:
                    if(!is_null($val))
                        $ret[$lbl] = $val;
            }
        }
        return $ret;
    }

}