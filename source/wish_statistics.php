<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['school_id']=1;

if (isset($_POST['school_id'])){
	$cache=new Cache();
	$cachename=basename(__FILE__)."school_id={$_POST['school_id']}".'.txt';
	if(($val=$cache->get($cachename))!==false){
		echo $val;
		exit();
	}
	$db=Db::getInstance();
	try {
		$db->connect();
		$res=array();
		//count
		$res=getCountRecFood($db);
		$json=getJsonResponse(0,"success",$res);
		$cache->set($cachename, $json,1200);
		echo $json;
		$db->close();
	} catch (Exception $e) {
		echo getJsonResponse(1,"数据库连接错误",null);
		Log::error_log("数据库连接错误");
		exit();
	}
}else {
	echo getJsonResponse(2,"post参数错误",null);
	exit();
}

/**
 * count
 * @param Db $db
 * @return array
 */
function getCountRecFood(&$db){
	$sql="select favor,cometrue from recommendedfood,users 
			where recommendedfood.user_id=users.user_id
			and users.school_id={$_POST['school_id']};";
	$res=$db->query($sql);
	if($res!==false){
		$return=array();
		$return['count']=0;
		$return['wish_satisfied_rate']=0.0;
		$return['lineover_rate']=0.0;
		$return['line']=500;      //心愿线数量
		$return['count']=sizeof($res);
		if($return['count']===0){
			return $return;
		}
		$linecount=0;
		$truecount=0;
		foreach ($res as $value){
			if($value['cometrue'])
				$truecount++;
			if($value['favor']>=500)
				$linecount++;
		}
		$return['lineover_rate']=$linecount/$return['count'];
		$return['wish_satisfied_rate']=$truecount/$return['count'];
		return $return;
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}
}

?>