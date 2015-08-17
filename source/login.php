<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');

/*$_POST['username']="xiao";        //测试数据
$_POST['phone']="18819451372";
$_POST['password']='123456';*/

if(!isset($_GET['type']))
{	
	echo getJsonResponse(3,'type参数没有设置',null);
	exit;
}
$sql='';
$sql1='';
$result=null;
if($_GET['type']=='username'){
	if(isset($_POST['username'])&&isset($_POST['password'])){
		$sql="select password from users where username='{$_POST['username']}';";
		$sql1="select user_id from users where username='{$_POST['username']}';";
	}else{
		echo getJsonResponse(3,'post参数没有设置',null);
	}
}else if($_GET['type']=='phone'){
	if(isset($_POST['phone'])&&isset($_POST['password'])){
		$sql="select password from users where phone='{$_POST['phone']}';";
		$sql1="select user_id from users where phone='{$_POST['phone']}';";		
	}else{
		echo getJsonResponse(3,'post参数没有设置',null);
	}
}else{
	echo getJsonResponse(3,'type参数错误',null);
}
//查询数据库，验证密码
$db=Db::getInstance();
try {
	$db->connect();
	$result=$db->query($sql);
	if(!$result){
		echo getJsonResponse(2,$db->error,null);
	}
	else{
		if(md5($_POST['password'])==$result[0]['password'])   
		{
			$data=null;
			$res=$db->query($sql1);       //密码正确
			if($res){
				$data=array('user_id'=>$res[0]['user_id']);
				echo getJsonResponse(0,"登陆成功",$data);
			}else{
				echo getJsonResponse(1,"查询id出错",$data);
			}
		}
		else{
			echo getJsonResponse(2,'密码错误',null);
		}		
	}
	$db->close();
} catch (Exception $e) {
	echo getJsonResponse(1,'数据库连接失败',null);
}
?>