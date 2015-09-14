<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

$_POST['comment_id']=1;
$_POST['recommend_id']=1;
$_POST['user_id']=1;
$_POST['favor']='true';

$sql1=$sql2='';
if(isset($_GET['which'])){
	if($_GET['which']=='comment'){
		if(isset($_POST['comment_id'])&&isset($_POST['favor'])&&isset($_POST['user_id'])){
			if($_POST['favor']=='true'){
				$sql="update food_comments set favor=favor+1
				where comment_id={$_POST['comment_id']};";
				$sql2="insert into food_comments_favor values({$_POST['comment_id']},{$_POST['user_id']});";
			}else{
				$sql="update food_comments set favor=favor-1
				where comment_id={$_POST['comment_id']};";
				$sql2="delete from food_comments_favor values({$_POST['comment_id']},{$_POST['user_id']});";
			}			
		}else{
			echo getJsonResponse(2,"post参数错误",null);
			exit();
		}
	}elseif ($_GET['which']=='recommendedfood'){
		if(isset($_POST['user_id'])&&isset($_POST['food_id'])&&isset($_POST['favor'])){
			if($_POST['favor']=='true'){
				$sql="update recommendedfood set favor=favor+1
				where user_id={$_POST['user_id']} and food_id={$_POST['food_id']};";
				//$sql2="update into recommendedfood_favor values({$_POST['comment_id']},{$_POST['user_id']});";
			}else{
				$sql="update recommendedfood set favor=favor-1
				where user_id={$_POST['user_id']} and food_id={$_POST['food_id']};";
				//$sql2="delete from recommendedfood_favor values({$_POST['comment_id']},{$_POST['user_id']});";
			}
		}else{
			echo getJsonResponse(2,"post参数错误",null);
			exit();
		}
	}else{
		echo getJsonResponse(2,"get参数错误",null);
		exit();
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
	exit();
}

$db=Db::getInstance();
try {
	$db->connect();
	if($_GET['which']=='comment'){
		if($db->execute($sql2)!==false){
			if($db->numRows==0)
				echo getJsonResponse(2,"post id错误",null);
			//else
				//echo getJsonResponse(0,"success",null);
		}else{
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			exit();
		}
	}
	if($db->execute($sql)!==false){
		if($db->numRows==0)
			echo getJsonResponse(2,"post id错误",null);
		else 
			echo getJsonResponse(0,"success",null);
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
	}
} catch (Exception $e) {
	echo getJsonResponse(1,"数据库连接错误",null);
	Log::error_log("数据库连接错误");
	exit();
}

?>