<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

$_POST['canteen_id']=1;
$_POST['food_id']=1;
$_POST['user_id']=1;

$count=10;
if(isset($_GET['page'])){
	if(isset($_POST['canteen_id'])&&isset($_POST['food_id'])&&isset($_POST['user_id'])){
		/* $cachename=basename(__FILE__)."page={$_GET['page']}".md5("canteen_id={$_POST['canteen_id']}&food_id={$_POST['food_id']}").'.txt';
		$cache=new Cache();
		if(($val=$cache->get($cachename))!==false){
			echo $val;
			exit();
		} */
		$from=($_GET['page']-1)*$count;
		$sql="select comment_id,username,users.imageurl,content,food_comments.time,favor from food_comments,users 
				where food_comments.user_id=users.user_id and canteen_id={$_POST['canteen_id']}
				 and food_id={$_POST['food_id']} order by food_comments.time desc limit {$from},{$count};";
		$db=Db::getInstance();
		try {
			$db->connect();
			$res=$db->query($sql);
			if($res!==false){
				if(empty($res))
					$res=null;
				else{
					foreach ($res as &$value){
						$value['user_imageurl']=$value['imageurl'];
						getImageUrl($db,$value);
						getFavor($db,$_POST['user_id'],$value);
					}
				}
				$json=getJsonResponse(0,"success",$res);
				/* if($cache->set($cachename, $json,1200)===false)
					Log::error_log($cachename.'  '.$cache->error); */
				echo $json;
			}else{
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			}
			$db->close();
		} catch (Exception $e) {
			echo getJsonResponse(1,"数据库连接错误",null);
			Log::error_log("数据库连接错误");
			exit();
		}
	}else{
		echo getJsonResponse(2,"post参数错误",null);
		exit();
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
	exit();
}

function getImageUrl(&$db,&$value){
	$sql="select imageurl from food_comments_images where comment_id={$value['comment_id']};";
	$res=$db->query($sql);
	if($res!==false){
		$imageurl=array();
		if(empty($res)){
			$imageurl=null;
		}else{
			foreach ($res as $val){
				$imageurl[]=$val['imageurl'];
			}
		}
		$value['imageurl']=$imageurl;
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		exit();
	}
}

function getFavor(&$db,$userid,&$value){
	$sql="select user_id from food_comments_favor where comment_id={$value['comment_id']} and user_id={$userid};";
	$res=$db->query($sql);
	if($res!==false){
		if(empty($res)){
			$value['is_favor']=false;
		}else{
			$value['is_favor']=true;
		}
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		exit();
	}
}

?>