<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');

$_POST['user_id']=1;

if(isset($_POST['user_id'])&&is_int($_POST['user_id']))
{
	$db=Db::getInstance();
	try {
		$db->connect();
		$sql="select * from users where user_id={$_POST['user_id']};";		
		if($result=$db->query($sql)){
			unset($result[0]['password']);
			//stripcslashes($result[0]['imageurl']);
			echo getJsonResponse(0,'sucess',$result[0]);
		}else{			
			echo getJsonResponse(1,$db->error,null);
		}
		$db->close();
	} catch (Exception $e) {
		echo getJsonResponse(1,'数据库连接错误',null);
	}
}else{
	echo getJsonResponse(1,'post参数没有设置或错误',null);
}

?>