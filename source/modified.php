<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');

/* $_POST['user_id']=1;        //测试数据
$_POST['access_token']='9e5cd477f4ff4a04d915b3892e58c033';
$_POST['new_username']="xiao";
$_POST['old_password']='123456';
$_POST['new_password']='123456';
$_POST['force']=true;
$_POST['new_sex']='男'; */

if(!isset($_GET['content']))
	echo getJsonResponse(2,'参数没有设置',null);
else{
	//用户是否合法
	if(!isset($_POST['access_token'])){
		echo getJsonResponse(2,"token参数没有设置",null);
		exit();
	}
	$db=Db::getInstance();
	try {
		$db->connect();
	} catch (Exception $e) {
		echo getJsonResponse(1,'数据库连接错误',null);
		Log::error_log("数据库连接错误");
		exit();
	}
	if(!checkUserToken($db, $_POST['user_id'], $_POST['access_token'])){
		echo getJsonResponse(2,'用户token错误',null);
		exit();
	}
	if($_GET['content']=='username'){
		if(isset($_POST['user_id'])&&isset($_POST['new_username'])){
			$username=stringToDb($_POST['new_username']);
			$sql="select user_id from users where username='{$username}';";
			$sql1="update users set username='{$username}' where user_id={$_POST['user_id']};";
				$res=$db->query($sql);
				if($res===false){
					echo getJsonResponse(1,$db->error,null);
				}else{
					if(!empty($res)){
						echo getJsonResponse(3,"用户名已存在",null);
					}else{
						if($db->execute($sql1)==false){
							echo getJsonResponse(1,$db->error,null);
						}else{    //修改成功
							echo getJsonResponse(0,"修改成功",null);
						}
					}
				}
				$db->close();
		}else{
			echo getJsonResponse(2,'post参数没有设置',null);
		}
	}else if($_GET['content']=='password'){
		if(isset($_POST['user_id'])&&isset($_POST['old_password'])
			&&isset($_POST['new_password'])&&isset($_POST['force'])){
			$sql="select password from users where user_id={$_POST['user_id']};";
			$new=md5($_POST['new_password']);
			$sql1="update users set password='{$new}' where user_id={$_POST['user_id']}";
				if(!$_POST['force']){
					$res=$db->query($sql);
					if($res===false){
						echo getJsonResponse(1,$db->error,null);
					}else{
						if(empty($res)){
							echo getJsonResponse(2,"id错误",null);
						}else{
							if($res[0]['password']===md5($_POST['old_password']))
							{
								if($db->execute($sql1)===false){
									echo "here";
									echo getJsonResponse(1,$db->error,null);
								}else{
									echo getJsonResponse(0,"成功修改",null);
								}
							}else{
								echo getJsonResponse(3,"密码错误",null);
							}
						}
					}
				}else{
					if($db->execute($sql1)===false){
						echo getJsonResponse(1,$db->error,null);
					}else{
						echo getJsonResponse(0,"成功修改",null);
					}
				}
				$db->close();
		}else{
			echo getJsonResponse(2,'post参数没有设置',null);
		}
	}else if($_GET['content']=='sex'){
		if(isset($_POST['user_id'])&&isset($_POST['new_sex'])){
			$sql="update users set sex='{$_POST['new_sex']}' where user_id={$_POST['user_id']};";
			if($_POST['new_sex']=='男'||$_POST['new_sex']=='女'){
					if(false===$db->execute($sql)){
						echo getJsonResponse(1,$db->error,null);
					}else{
						echo getJsonResponse(0,"成功修改",null);
					}
				$db->close();
			}else{
				echo getJsonResponse(2,'post参数错误，sex',null);
			}
		}else{
			echo getJsonResponse(2,'post参数没有设置',null);
		}
	}else{
		echo getJsonResponse(2,'url参数错误',null);
	}
}
?>