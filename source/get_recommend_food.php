<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

//$_POST['school_id']=1;

if(isset($_GET['page'])&&isset($_GET['come_true'])){
	if (isset($_POST['school_id'])){
		$cache=new Cache();
		$cachename=basename(__FILE__).md5("page={$_GET['page']}&come_true={$_GET['come_true']}&school_id={$_POST['school_id']}").'.txt';
		if(($val=$cache->get($cachename))!==false){
			echo $val;
			exit();
		}
		$db=Db::getInstance();
		try {
			$db->connect();
			$cometrue=0;
			$count=10;
			$from=((int)$_GET['page']-1)*$count;
			if($_GET['come_true']=='yes')
				$cometrue=1;
			$sql="select recommendedfood.user_id,recommendedfood.canteen_id,recommendedfood.food_id,username,canteenname,
				foodname,favor,recommendedfood.imageurl from users,canteens,food,recommendedfood
				where users.user_id=recommendedfood.user_id and canteens.canteen_id=recommendedfood.canteen_id 
				and food.food_id=recommendedfood.food_id
			     and recommendedfood.cometrue={$cometrue} and recommendedfood.user_id in 
				(select user_id from users where school_id={$_POST['school_id']})
				order by favor desc limit {$from},{$count};";
			$res=$db->query($sql);
			if($res!==false){
				if(empty($res))
					$res=null;
				else{
					foreach($res as &$value){
						getMethod($db,$value);
					}
				}
				$json=getJsonResponse(0,"success",$res);
				$cache->set($cachename, $json,1200);
				echo $json;
			}else {
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			}		
			$db->close();
		} catch (Exception $e) {
			echo getJsonResponse(1,"数据库连接错误",null);
			Log::error_log("数据库连接错误");
			exit();
		}
	}else {
		echo getJsonResponse(2,"post参数错误",null);
		exit();
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
	exit();
}

function getMethod(&$db,&$value){
	$sql="select content from foodmethod where user_id={$value['user_id']} and
		 canteen_id={$value['canteen_id']} and food_id={$value['food_id']} order by sequence;";
	$res=$db->query($sql);
	if($res!==false){
		$value['content']=array();
		if(empty($res))
			$value['content']=null;
		foreach ($res as $val){
			$value['content'][]=$val['content'];
		}
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		exit();
	}
}

?>