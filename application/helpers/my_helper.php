<?php
// Api_v1


// generic
function split_arr_items(&$item,$key,$sep) {
	$item = explode($sep,$item);
}

function prefix_arr_items(&$item,$key,$prefix) {
	$item = explode($sep,$item);
}

function sufix_arr_items(&$item,$key,$prefix) {
	$item = $item.$sufix;
}


