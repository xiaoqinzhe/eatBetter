<?php

$Config = require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';

function C($key=null){
	global $Config;
	if($key==null)
		return $Config;
	else
	{
		if(array_key_exists($key,$Config))
			return $Config["{$key}"];
		else return null;
	}
}