<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

$_POST['user_id']=1;
$_POST['canteen_id']=1;
$_POST['food_id']=1;
$_POST['grade']=3.5;

if(isset($_GET['for'])){
	$db=Db::getInstance();
	try {
		$db->connect();
	} catch (Exception $e) {
		echo getJsonResponse(2,"数据库连接失败",null);
		Log::error_log("数据库连接错误");
	}
	$sql='';
	if($_GET['for']=='canteen'){
		if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&is_int($_POST['user_id'])&&is_int($_POST['canteen_id'])
				&&isset($_POST['grade'])&&is_float($_POST['grade'])){
			if($_POST['grade']>=0&&$_POST['grade']<=10){
				if(checkExist($db, 'canteen'))
					$sql="update canteen_grade set grade={$_POST['grade']} where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']};";
				else 
					$sql="insert into canteen_grade values({$_POST['user_id']},{$_POST['canteen_id']},{$_POST['grade']});";
			}
			else 
			{
				echo getJsonResponse(3,"grade参数错误",null);
				exit();
			}
		}else{
			echo getJsonResponse(3,"post参数没有设置或错误",null);
			exit();
		}
	}else if($_GET['for']=="food"){
		if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&isset($_POST['food_id'])&&is_int($_POST['user_id'])&&is_int($_POST['canteen_id'])&&is_int($_POST['food_id'])
				&&isset($_POST['grade'])&&is_float($_POST['grade'])){
			if($_POST['grade']>=0&&$_POST['grade']<=5)
				if(checkExist($db, 'food'))
					$sql="update food_grade set grade={$_POST['grade']} where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']} and food_id={$_POST['food_id']};";
				else 
					$sql="insert into food_grade values({$_POST['user_id']},{$_POST['canteen_id']},{$_POST['food_id']},{$_POST['grade']},null);";
			else
			{
				echo getJsonResponse(3,"grade参数错误",null);
				exit();
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
	$db->connect();
	$res=$db->execute($sql);
	if($res===false){
		echo getJsonResponse(2,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
	}else{
		echo getJsonResponse(0,"成功",null);
	}
	$db->close();
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