<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['canteen_id']=1;

if(isset($_POST['canteen_id'])){
	$cache=new Cache();
	$cachename=basename(__FILE__)."canteen_id={$_POST['canteen_id']}.txt";
	if(($val=$cache->get($cachename))!==false){
		echo $val;
		exit();
	}else{
		$sql="select canteenname,imageurl,grade from school_canteen,canteens 
				where school_canteen.canteen_id={$_POST['canteen_id']} and 
				school_canteen.canteen_id=canteens.canteen_id;";
		$db=Db::getInstance();
		try {
			$db->connect();
			$res=$db->query($sql);
			if($res===false){
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
				exit();
			}else{
				if(empty($res)){
					echo getJsonResponse(0,"成功",null);
					$db->close();
					exit();
				}
				//算饭堂平均分
				//$res[0]['grade']=getAvgOfCanteen($db);
				//食堂食物总数	
				$res[0]['count']=getCountFood($db);
				//心愿满足率
				$res[0]['wish_satisfied_rate']=getWishRate($db);
				//食堂投诉接受率
				$res[0]['complaint_aceepted_rate']=getAcceptRate($db);
				$json=getJsonResponse(0,"成功",$res[0]);
				if($cache->set($cachename,$json,60)===false)
					Log::error_log($cachename.'  '.$cache->error);     //错误日志
				echo $json;
			}
				
			$db->close();
		} catch (Exception $e) {
			echo getJsonResponse(1,'数据库连接失败',null);
			Log::error_log("数据库连接错误");
			exit();
		}
	}
}else{
	echo getJsonResponse(2,'post参数没有设置',null);
	exit();
}

//算食堂平均分
function getAvgOfCanteen(&$db){
	$res=$db->query("select avg(grade) from canteen_grade where canteen_id={$_POST['canteen_id']};");
	if($res===false)
	{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}else
		return $res[0]['avg(grade)'];
}

//食堂食物总数
function getCountFood(&$db){
	$res=$db->query("select count(*) from canteen_food where canteen_id={$_POST['canteen_id']};");
	if($res===false)
	{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}else
		return $res[0]['count(*)'];
}

//心愿满足率
function getWishRate(&$db){
	$sql="select count(*) from recommendedfood where cometrue=false and user_id in
				(select user_id from users where school_id in
					(select school_id from school_canteen where canteen_id={$_POST['canteen_id']})
					);";
	$res=$db->query($sql);   //学校学生推荐的菜数量
	if($res===false)
	{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}else{
		if($res[0]['count(*)']==0){
			return 100;
		}
		$sql2="select count(*) from cometruefood where canteen_id={$_POST['canteen_id']};";
		$res2=$db->query($sql2);   //食堂实现的菜数量
		if($res2===false)
		{
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			exit();
		}else{
			if($res2[0]['count(*)']==0)
				return 0;
			return $res2[0]['count(*)']/$res[0]['count(*)'];
		}
	}
}

//食堂投诉接受率
function getAcceptRate(&$db){
	$sql="select count(*) from canteen_complaints where canteen_id={$_POST['canteen_id']};";
	$res=$db->query($sql);   //食堂投诉数量
	if($res===false)
	{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}else{
		if($res[0]['count(*)']==0){
			return 100;
		}
		$sql2="select count(*) from canteen_complaints,complaint_replies where 
		 canteen_id={$_POST['canteen_id']} and canteen_complaints.complaint_id=complaint_replies.complaint_id;";
		$res2=$db->query($sql2);   //食堂回复数
		if($res2===false)
		{
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			exit();
		}else{
			if($res2[0]['count(*)']==0)
				return 0;
			return $res2[0]['count(*)']/$res[0]['count(*)'];
		}
	}
}