<?php 

require_once('../lib/Db.class.php');

$db=Db::getInstance();
try {
	$db->connect();
	echo "success";
} catch (Exception $e) {
	echo $e->getMessage();
}
var_dump(file_put_contents("aa.txt", "asdfas"));
var_dump(file_exists($_SERVER['DOCUMENT_ROOT']."lib/Cache.class.php"));
