<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

/* $_POST['user_id']=1;
$_POST['access_token']='9e5cd477f4ff4a04d915b3892e58c033'; */

if(isset($_GET['page'])){
	$count=10;
	$from=((int)$_GET['page']-1)*$count;
	if(isset($_POST['user_id'])&&isset($_POST['access_token'])){
		$db=Db::getInstance();
		try {
			$db->connect();
		} catch (Exception $e) {
			echo getJsonResponse(1,'数据库连接错误',null);
			Log::error_log("数据库连接错误");
			exit();
		}
		//用户合法
		if(!checkUserToken($db, $_POST['user_id'], $_POST['access_token'])){
			echo getJsonResponse(2,'用户token错误',null);
			exit();
		}
		//查询评论内容
		$sql="select complaint_id,canteenname,content,time 
				from canteen_complaints,canteens
				where user_id={$_POST['user_id']} and canteen_complaints.canteen_id=canteens.canteen_id
			 order by time desc limit {$from},{$count};";
		$res=$db->query($sql);
		if($res===false){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		}else{
			if(empty($res)){
				$res=null;  //没有评论
			}else{			
				foreach ($res as &$value){
					//评论图片
					getImageUrl($db,$value);
					//获取评论回复信息
					getReply($db,$value);
				}
			}
			echo getJsonResponse(0,"success",$res);
		}
		$db->close();
	}else{
		echo getJsonResponse(2,"post参数错误",null);
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
}
	
function getImageUrl(&$db, &$value){
	$sql="select imageurl from canteen_complaints_images
			where complaint_id={$value['complaint_id']};";
	$res=$db->query($sql);
	if($res===false){
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		exit();
	}else{
		$value['imageurl']=array();
		if(empty($res)){
			$value['imageurl']=null;
		}else{
			foreach ($res as $val){
				$value['imageurl'][]=$val['imageurl'];
			}
		}
	}
}

function getReply(&$db, &$value){
	$sql="select content,time from complaint_replies
	where complaint_id={$value['complaint_id']};";
	$res=$db->query($sql);
	if($res===false){
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		exit();
	}else{
		$value['reply']=false;
		if(!empty($res)){			
			$value['reply']=true;
			$value['reply_content']=$res[0]['content'];
			$value['reply_time']=$res[0]['time'];			
		}
	}
}

?>