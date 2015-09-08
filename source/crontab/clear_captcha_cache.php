<?php 

require_once('../../lib/Db.class.php');
require_once('../../lib/Log.class.php');

$db=Db::getInstance();
try {
	$db->connect();
} catch (Exception $e) {
	Log::error_log("clear_captcha_cache.php 连接数据库失败");
	exit();
}

$res=$db->execute("delete from captcha_cache where now()-time>200;");
if($res===false){
	Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
}

?>