<?php

$config = require_once 'config/config.php';

function C($key=null){
	global $config;
	if($key==null)
		return $config;
	else
	{
		if(array_key_exists($key,$config))
			return $config["{$key}"];
		else return null;
	}
}