<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['user_id']=1;
$_POST['canteen_id']=2;
$_POST['food_id']=1;

if(isset($_GET['for'])){
	$db=Db::getInstance();
	$cache=new Cache();
	$sql='';
	$cachename='';
	if($_GET['for']=='canteen'){
		if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&is_int($_POST['user_id'])&&is_int($_POST['canteen_id'])){
			$cachename=basename(__FILE__)."for=canteen".md5("&user_id={$_POST['user_id']}&canteen_id={$_POST['canteen_id']}").".txt";
			if(($val=$cache->get($cachename))!==false){
				echo $val;
				exit();
			}else{
				$sql="select grade from canteen_grade where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']};";
				
			}
		}else{
			echo getJsonResponse(3,"post参数没有设置或错误",null);
			exit();
		}
	}else if($_GET['for']=="food"){
		if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&isset($_POST['food_id'])&&is_int($_POST['user_id'])&&is_int($_POST['canteen_id'])&&is_int($_POST['food_id'])){
			$cachename=basename(__FILE__)."for=food".md5("&user_id={$_POST['user_id']}&canteen_id={$_POST['canteen_id']}&food_id={$_POST['food_id']}").".txt";
			if(($val=$cache->get($cachename))!==false){
				echo $val;
				echo "cache";
				exit();
			}else{
				$sql="select grade from food_grade where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']} and food_id={$_POST['food_id']};";
			}
		}else{
			echo getJsonResponse(3,"post参数没有设置或错误",null);
			exit();
		}
	}else{
		echo getJsonResponse(3,"url for参数设置错误",null);
		exit();
	}
	//读取数据库
	try {
		$db->connect();
		$res=$db->query($sql);
		if($res===false){
			echo getJsonResponse(2,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		}else{
			if(empty($res)){
				echo getJsonResponse(0,"成功",null);
				$db->close();
				exit();
			}
			$json=getJsonResponse(0,"成功",$res[0]['grade']);
			$cache->set($cachename,$json,1200);
			echo $json;
		}
		$db->close();
	} catch (Exception $e) {
		echo getJsonResponse(2,"数据库连接失败",null);
		Log::error_log("数据库连接错误");
	}	
}else{
	echo getJsonResponse(3,"url for参数没有设置",null);
}