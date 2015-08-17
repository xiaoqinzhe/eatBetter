<?php

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/UploadFile.class.php');

//$_POST['user_id']=2;

if(isset($_POST['user_id'])&&isset($_FILES['image'])){
	if(checkUserId($_POST['user_id'])){     //先检测用户是否存在
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
			$db=Db::getInstance();
			$db->connect();
			if(false!=$db->execute("update users set imageurl='{$savePath}' where user_id={$_POST['user_id']};"))
			{
				echo getJsonResponse(0,'上传成功',null);
			}else{				
				echo getJsonResponse(1,$db->error,null);
			}
			$db->close();
		}else{
			echo getJsonResponse(1,$uf->errorMsg,null);
		}
	}else{
		echo getJsonResponse(1,'id错误',null);
	}
}else{
	echo getJsonResponse(1,'post参数没有设置',null);
}

?>