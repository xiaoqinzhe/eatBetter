<?php 

require_once('../lib/Db.class.php');

$db=Db::getInstance();
try {
	$db->connect();
	echo "success";
} catch (Exception $e) {
	echo $e->getMessage();
}

var_dump(file_exists("./lib/Cache.class.php"));