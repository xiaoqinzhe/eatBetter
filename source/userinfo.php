<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['user_id']=1;

if(isset($_POST['user_id'])&&is_int($_POST['user_id']))
{
	$cache=new Cache();
	$cachename=basename(__FILE__)."user_id={$_POST['user_id']}.txt";  //缓存名
	if(($val=$cache->get($cachename))!==false){      //读取缓存
		echo $val;
		exit();
	}else{
		$db=Db::getInstance();
		try {
			$db->connect();
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
		} catch (Exception $e) {
			echo getJsonResponse(1,'数据库连接错误',null);
			Log::error_log("数据库连接错误");
		}
	}
}else{
	echo getJsonResponse(2,'post参数没有设置或错误',null);
}

?>