<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['province']='广东省';
//$_POST['city']='广州市';

$sql='';
$cachename=basename(__FILE__);
if(isset($_GET['get'])){
	if($_GET['get']=='province'){
		$sql="select distinct province from schools;";
		$cachename.="get=province.txt";
	}else if($_GET['get']=='city'){
		if(isset($_POST['province'])&&is_string($_POST['province'])){
			$sql="select distinct city from schools where province='{$_POST['province']}';";
			$cachename.="get=city&province={$_POST['province']}";
			$cachename=basename(__FILE__).md5($cachename).'.txt';       //有中文
		}else{
			echo getJsonResponse(3,'post参数没有设置',null);
			exit();
		}
	}else if($_GET['get']=='school'){
		if(isset($_POST['province'])&&is_string($_POST['province'])){
			if(!isset($_POST['city'])){
				$sql="select school_id,schoolname,schoolarea from schools where province='{$_POST['province']}';";
				$cachename.="get=city&province={$_POST['province']}&city=null";
				$cachename=basename(__FILE__).md5($cachename).'.txt';       //有中文
			}
			else{
				$sql="select school_id,schoolname,schoolarea from schools where province='{$_POST['province']}' and city='{$_POST['city']}';";
				$cachename.="get=city&province={$_POST['province']}&city={$_POST['city']}";
				$cachename=basename(__FILE__).md5($cachename).'.txt';       //有中文
			}
		}else{
			echo getJsonResponse(3,'post参数没有设置',null);
			exit();
		}
	}else{
		echo getJsonResponse(3,'get参数错误',null);
		exit();
	}
}else{
	echo getJsonResponse(3,'get参数没有设置',null);
	exit();
}
$db=Db::getInstance();
$cache=new Cache();
try {
	if(($val=$cache->get($cachename))!==false){
		echo $val;
		exit();
	}else{
		$db->connect();
		$res=$db->query($sql);
		if($res===false){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		}else{
			if(empty($res)){
				echo getJsonResponse(0,"成功",null);
				$db->close();
				exit();
			}
			$json=getJsonResponse(0,'成功',$res);
			if($cache->set($cachename,$json,1200)===false)
				Log::error_log($cachename.'  '.$cache->error);
			echo $json;
		}
		$db->close();
	}
	
} catch (Exception $e) {
	echo getJsonResponse(1,'数据库连接失败',null);
	Log::error_log("数据库连接错误");
}
?>