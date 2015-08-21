<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

$_POST['user_id']=1;
$_POST['canteen_id']=1;
$_POST['food_id']=2;
$_POST['favor']=1;

if(isset($_GET['type'])){
	$db=Db::getInstance();
	try {
		$db->connect();
	} catch (Exception $e) {
		echo getJsonResponse(2,"数据库连接失败",null);
		Log::error_log("数据库连接错误");
	}
	$sql='';
	if($_GET['type']=='get'){
		if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&isset($_POST['food_id'])&&is_int($_POST['user_id'])&&is_int($_POST['canteen_id'])
				&&is_int($_POST['food_id'])){
			$sql="select favor from food_grade where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']} and food_id={$_POST['food_id']};";
			$res=$db->query($sql);
			if($res===false){
				echo getJsonResponse(2,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			}else{
				if(empty($res))
					$res[0]['grade']=null;
				echo getJsonResponse(0,"成功",$res[0]['grade']);
			}
			$db->close();
		}else{
			echo getJsonResponse(3,"post参数没有设置或错误",null);
			exit();
		}
	}else if($_GET['type']=="set"){
		if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&isset($_POST['food_id'])&&is_int($_POST['user_id'])&&is_int($_POST['canteen_id'])&&is_int($_POST['food_id'])
				&&isset($_POST['favor'])&&in_array($_POST['favor'],array(0,1,2))){
			if($_POST['favor']==2)
				$_POST['favor']='null';
			if(checkExist($db, 'food'))
				$sql="update food_grade set favor={$_POST['favor']} where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']} and food_id={$_POST['food_id']};";
			else
				$sql="insert into food_grade values({$_POST['user_id']},{$_POST['canteen_id']},{$_POST['food_id']},null,{$_POST['favor']});";
			$res=$db->execute($sql);
			if($res===false){
				echo getJsonResponse(2,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			}else{
				echo getJsonResponse(0,"成功",null);
			}
			$db->close();
		}else{
			echo getJsonResponse(3,"post参数没有设置或错误",null);
			exit();
		}
	}else{
		echo getJsonResponse(3,"url type参数设置错误",null);
		exit();
	}
}else{
	echo getJsonResponse(3,"url for参数没有设置",null);
}

function checkExist(&$db,$which){
	$sql='';
	if ($which=='canteen'){
		$sql="select count(*) from canteen_grade where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']};";	
	}else 
		$sql="select count(*) from food_grade where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']} and food_id={$_POST['food_id']};";
	$res=$db->query($sql);
	if($res===false){
		echo getJsonResponse(2,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}else{
		if($res[0]['count(*)']==1)
			return true;
		else return false;
	}
}

?>