<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

/* $_POST['comment_id']=1;
$_POST['user_id']=1;
$_POST['recommend_id']=3;
$_POST['favor']='true'; */


if(isset($_GET['which'])){
	if($_GET['which']=='comment'){
		if(isset($_POST['comment_id'])&&isset($_POST['user_id'])&&isset($_POST['favor'])){
			if($_POST['favor']=='true'){
				$sql="update food_comments set favor=favor+1 
					where comment_id={$_POST['comment_id']};";
				$sql2="insert into food_comments_favor values({$_POST['comment_id']},{$_POST['user_id']});";
			}else {
				$sql="update food_comments set favor=favor-1
				where comment_id={$_POST['comment_id']};";
				$sql2="delete from food_comments_favor where comment_id={$_POST['comment_id']} and user_id={$_POST['user_id']};";
			}
		}else{
			echo getJsonResponse(2,"post参数错误",null);
			exit();
		}
	}elseif ($_GET['which']=='recommendedfood'){
		if(isset($_POST['user_id'])&&isset($_POST['recommend_id'])&&isset($_POST['favor'])){
			if($_POST['favor']=='true'){
				$sql="update recommendedfood set favor=favor+1
				where recommend_id={$_POST['recommend_id']};";
				$sql2="insert into recommendedfood_favor values({$_POST['recommend_id']},{$_POST['user_id']});";
			}else{
				$sql="update recommendedfood set favor=favor-1
				where recommend_id={$_POST['recommend_id']};";
				$sql2="delete from recommendedfood_favor where recommend_id={$_POST['recommend_id']} and user_id={$_POST['user_id']};";
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
	if($db->execute($sql)!==false){
		if($db->numRows==0)
			echo getJsonResponse(2,"post id错误",null);
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}
	if ($_GET['which']=='comment')
	if($db->execute($sql2)!==false){
		if($db->numRows==0)
			echo getJsonResponse(2,"post id错误",null);
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}
	echo getJsonResponse(0,"success",null);
	$db->close();
} catch (Exception $e) {
	echo getJsonResponse(1,"数据库连接错误",null);
	Log::error_log("数据库连接错误");
	exit();
}

?>