<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/UploadFile.class.php');

$_POST['user_id']=1;
$_POST['access_token']='9e5cd477f4ff4a04d915b3892e58c033';


if(isset($_POST['user_id'])&&isset($_FILES['image'])){
	if(checkUserId($_POST['user_id'])){     //先检测用户是否存在
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
		$config1=array(
				'maxSize'      =>  2100000,    // 上传文件的最大值,2M多
		        'allowExts'    =>  array('png','jpg'),    // 允许上传的文件后缀 留空不作后缀检查
		        'allowTypes'   =>  array('image/png','image/jpeg'),    // 允许上传的文件类型 留空不做检查
		        'savePath'     =>  $_SERVER['DOCUMENT_ROOT'].'images/users/',// 上传文件保存路径 
			);
		$uf=new UploadFile($config1);
		$res=$uf->uploadOne($_FILES['image']);
		if($res){
			$savePath='http://'.getServerIp().'/images/users/'.$res['savename'];
			if(false!==$db->execute("update users set imageurl='{$savePath}' where user_id={$_POST['user_id']};"))
			{
				echo getJsonResponse(0,'上传成功',null);
			}else{				
				echo getJsonResponse(1,$db->error,null);
			}
			$db->close();
		}else{
			if($uf->errorNo==1||$uf->errorNo==2||$uf->errorNo==8){
				echo getJsonResponse(3,$uf->errorMsg,null);
			}else if($uf->errorNo==9){
				echo getJsonResponse(4,$uf->errorMsg,null);
			}
			else echo getJsonResponse(2,$uf->errorMsg,null);
		}
	}else{
		echo getJsonResponse(2,'id错误',null);
	}
}else{
	echo getJsonResponse(2,'post参数没有设置',null);
}

?>