<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

$_POST['username']="qiujiaman";
$_POST['password']='123456';
$_POST['sex']='女';
$_POST['phone']='18819451314';
$_POST['school_id']='1';

if(isset($_GET['type'])){
	if($_GET['type']!='phone'&&$_GET['type']!='checkusername'){
		echo getJsonResponse(2,"get参数错误",null);
		exit();
	}
	$db=Db::getInstance(array('autocommit'=>false));
	try {
		$db->connect();
	} catch (Exception $e) {
		echo getJsonResponse(1,'数据库连接错误',null);
		Log::error_log("数据库连接错误");
		exit();
	}
	$db->commit();
	if($_GET['type']=='phone'){
		if(isset($_POST['username'])&&isset($_POST['password'])&&isset($_POST['sex'])&&isset($_POST['phone'])&&isset($_POST['school_id'])){
			if(checkPhone($db,$_POST['phone'])){
				echo getJsonResponse(2,'phone已经注册过',null);
				$db->close();
				exit();
			}
			$_POST['username']=trim($_POST['username']);
			if(strlen($_POST['username'])<5||strlen($_POST['username'])>15){
				echo getJsonResponse(2,'username长度错误',null);
				$db->close();
				exit();
			}
			if(checkUserName($db,$_POST['username'])){
				echo getJsonResponse(2,'username已经注册过',null);
				$db->close();
				exit();
			}
			//检查性别
			if($_POST['sex']!='男'&&$_POST['sex']!='女'){
				echo getJsonResponse(2,'性别有误',null);
				$db->close();
				exit();
			}
			$password=md5($_POST['password']);
			$schoolid=(int)$_POST['school_id'];
			$token=md5(uniqid());
			$res=$db->execute("insert into users values(null,'{$token}','{$_POST['username']}','{$password}','{$_POST['sex']}',0,'{$_POST['phone']}',current_date(),'http://localhost/images/users/default.jpg',{$schoolid},null,null);");
			if($res===false){
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
				$db->close();
				exit();
			}
			$res2=$db->query("select user_id from users where phone='{$_POST['phone']}';");
			if($res2===false){
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
				$db->rollback();
				$db->close();
				exit();
			}
			$return=array(
					'user_id'=>$res2[0]['user_id'],
					'access_token'=>$token
			);
			echo getJsonResponse(0,'success',$token);
			$db->commit();
			$db->close();
		}else{
			echo getJsonResponse(2,'post数据有误',null);
			$db->close();
			exit();
		}
	}else{
		if(isset($_POST['username'])){
			if(checkUserName($db, $_POST['username'])){
				echo getJsonResponse(3,'用户名存在',null);
				$db->close();
				exit();
			}else{
				echo getJsonResponse(0,"success",null);
				$db->close();
				exit();
			}
		}else{
			echo getJsonResponse(2,'post数据有误',null);
			$db->close();
			exit();
		}
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
	exit();
}

?>