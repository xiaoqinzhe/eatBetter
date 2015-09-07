<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['school_id']=1;

if(isset($_POST['school_id'])){
	$cache=new Cache();
	$cachename=basename(__FILE__)."school_id={$_POST['school_id']}.txt";
	if(($val=$cache->get($cachename))!==false){
		echo $val;
		exit();
	}
	$sql="select school_canteen.canteen_id,canteenname,imageurl from school_canteen,canteens 
			where school_id={$_POST['school_id']} and 
			school_canteen.canteen_id=canteens.canteen_id;";
	$db=Db::getInstance();
	try {
		$db->connect();
		$res=$db->query($sql);
		if($res===false){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		}else{
			if(!empty($res))
				$json=getJsonResponse(0,"成功",$res);
			else
				$json=getJsonResponse(0,"成功",null);
			$cache->set($cachename,$json,1200);
			echo $json;
		}
		$db->close();
	} catch (Exception $e) {
		echo getJsonResponse(1,'数据库连接失败',null);
		Log::error_log("数据库连接错误");
		exit();
	}
}else{
	echo getJsonResponse(2,'post参数没有设置',null);
	exit();
}