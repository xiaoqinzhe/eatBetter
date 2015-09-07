<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['user_id']=1;
$_POST['access_token']='9e5cd477f4ff4a04d915b3892e58c033';

if(isset($_POST['user_id']))
{
	//用户是否合法
	if(!isset($_POST['access_token'])){
		echo getJsonResponse(2,"token参数没有设置",null);
		exit();
	}
	$db=Db::getInstance();
	try {
		$db->connect();
	} catch (Exception $e) {
		echo getJsonResponse(1,'数据库连接错误',null);
		Log::error_log("数据库连接错误");
		exit();
	}
	if(!checkUserToken($db, $_POST['user_id'], $_POST['access_token'])){
		echo getJsonResponse(2,'用户token错误',null);
		exit();
	}
	$cache=new Cache();
	$cachename=basename(__FILE__)."user_id={$_POST['user_id']}.txt";  //缓存名
	if(($val=$cache->get($cachename))!==false){      //读取缓存
		echo $val;
		exit();
	}else{
			$sql="select * from users where user_id={$_POST['user_id']};";		
			if(($result=$db->query($sql))!==false){
				unset($result[0]['password']);
				//stripcslashes($result[0]['imageurl']);
				if(!empty($result))
				{
					$json=getJsonResponse(0,'sucess',$result[0]);
					$ret=$cache->set($cachename,$json,1200);     //缓存失效时间为20分钟
					if($ret===false)
						Log::error_log($cachename.'  '.$cache->error);     //错误日志
				}else
					$json=getJsonResponse(2,'没有这个id',null);				
				echo $json;
			}else{
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			}
			$db->close();
	}
}else{
	echo getJsonResponse(2,'post参数没有设置或错误',null);
}

?>