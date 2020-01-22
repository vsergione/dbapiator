<?php

function parse_where($str) {
    $expr = [];
    $start = 0;
    for($i=0;$i<strlen($str);$i++)
    {
        $op = substr($str,$i,2);
        if(!in_array($op,["&&","||"]))
            continue;

        $expr[] = parseExpr(substr($str,$start,$i-$start));
        $expr[] = parseLogicalOp($op);
        $start = $i+2;
    }
    $expr[] = parseExpr(substr($str,$start));


    return implode("",$expr);
}

function parseExpr($str) {
    if(!preg_match("/([\w\d\.]+)([\=\<\>~\!]+)([\w\d\.]+)/i",$str,$m))
        return null;
    $left = $m[1];
    switch($m[2]){
        case "~=" :
            $right = "%".$m[3];
            break;
        case "!~=":
            $right = "%".$m[3];
            break;
        case "=~":
            $right = $m[3]."%";
            break;
        case "!=~":
            $right = $m[3]."%";
            break;
        case "~=~":
            $right = "%".$m[3]."%";
            break;
        case "!~=~":
            $right = "%".$m[3]."%";
            break;
        default:
            $right = $m[3];
    }
    $right = "'$right'";
    $op = parseCompOp($m[2]);
    return $left.$op.$right;
}

function parseCompOp($opStr) {
    $opMap = [
        "==" => "=",
        "!=" => "!=",
        "=>" => "=>",
        "<=" => "<=",
        "<" => "<",
        ">" => ">",
        "><" => "IN",
        "~=" => " LIKE ",
        "!~=" => "NOT LIKE ",
        "=~" => " LIKE ",
        "!=~" => " NOT LIKE ",
        "~=~" => " LIKE ",
        "!~=~" => " NOT LIKE ",
    ];
    if(isset($opMap[$opStr]))
        return $opMap[$opStr];
    return null;
}
function parseLogicalOp($str) {

    return [
        "&&" => " AND ",
        "||" => " OR ",
    ][$str];
}
