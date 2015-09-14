<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');
require_once '../lib/UploadFile.class.php';

$_POST['user_id']=1;
$_POST['canteen_id']=1;
$_POST['food_id']=3;
$_POST['content']="shenmecaia..\';";

if(isset($_POST['user_id'])&&isset($_POST['canteen_id'])&&isset($_POST['food_id'])
		&&isset($_POST['content'])){
	if(!isset($_POST['access_token'])){
		echo getJsonResponse(2,"token参数没有设置",null);
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
	if(!checkUserToken($db, $_POST['user_id'], $_POST['access_token'])){
		echo getJsonResponse(2,'用户token错误',null);
		exit();
	}
	$content=stringToDb($_POST['content']);
	$sql="insert into food_comments values(null,{$_POST['user_id']},{$_POST['canteen_id']},
		{$_POST['food_id']},0,'{$content}',now());select last_insert_id();";
	if(!$db->startTransaction()){
		//transiton
		echo getJsonResponse(1,"事务启动失败",null);
		$db->close();
		exit();
	}
	$mysqli=$db->getMysqli();
	$commentid;
	if($mysqli->multi_query($sql)){      //第一条查询
		$mysqli->next_result();     //第二条查询结果
		if($result=$mysqli->store_result()){
			while($row=$result->fetch_row()){
				$commentid=(int)$row[0];
			}
			$result->free();
		}else {
			echo getJsonResponse(1,$mysqli->error,null);
			$db->rollback();  //回滚
			$db->close();
			Log::error_log('database error：'."查询评论id出错".' in '.basename(__FILE__));
			exit();
		}
		//是否有评论图片
		$config1=array(
				'maxSize'      =>  2100000,    // 上传文件的最大值,2M多
				'allowExts'    =>  array('png','jpg'),    // 允许上传的文件后缀 留空不作后缀检查
				'allowTypes'   =>  array('image/png','image/jpeg'),    // 允许上传的文件类型 留空不做检查
				'savePath'     =>  $_SERVER['DOCUMENT_ROOT'].'uploads/comments/',// 上传文件保存路径
		);
		$up=new UploadFile($config1);
		for($i=1;$i<=4;$i++){
			$name='imagefile'.$i;
			if(isset($_FILES[$name])){
				$res1=$up->uploadOne($_FILES[$name]);
				if($res1!==false){
					$savePath='http://'.getServerIp().'/uploads/comments/'.$res1['savename'];
					$savePath=stringToDb($savePath);
					if($db->execute("insert into food_comments_images values({$commentid},'{$savePath}');")===false){
						echo getJsonResponse(1,$db->error,null);
						Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
						$db->rollback();
						$db->close();
						exit();
					}
				}else{  //上传图片失败
					echo getJsonResponse(4,$uf->errorMsg,null);
					$db->rollback();  //回滚
					$db->close();
					exit();
				}
			}
		}
		$db->commit();
		echo getJsonResponse(0,"success",array("comment_id"=>$commentid));  
	}else{     //插入失败
		echo getJsonResponse(1,$mysqli->error,null);
		$db->rollback();  //回滚
		Log::error_log('database error：'.$mysqli->error.' in '.basename(__FILE__));			
	}
	$db->close();
}else{
	echo getJsonResponse(2,"post参数错误",null);
	exit();
}


?>