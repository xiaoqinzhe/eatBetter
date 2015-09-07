<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Cache.class.php');
require_once('../lib/Log.class.php');

//$_POST['canteen_id']=1;

$cachename='';
$count=10;
$sql='';
if(isset($_GET['page'])&&isset($_GET['sorttype'])&&is_string($_GET['sorttype'])
		&&isset($_GET['order'])&&in_array($_GET['order'], array('asc','desc'))){
	if(isset($_POST['canteen_id'])){
		$sql="select food.food_id,foodname,price,favor,dislike,imageurl from food,canteen_food 
				where food.food_id=canteen_food.food_id and canteen_food.canteen_id={$_POST['canteen_id']} order by ";
		if($_GET['sorttype']=='new'){
			$sql.="time";
		}else if($_GET['sorttype']=='name'){
			$sql.="foodname";
		}else if ($_GET['sorttype']=='price'){
			$sql.="price";
		}else if($_GET['sorttype']=='favor'){
			$sql.="favor";
		}else if($_GET['sorttype']=='dislike'){
			$sql.="dislike";
		}else if($_GET['sorttype']=='grade'){
			$sql.="grade";
		}else{
			echo getJsonResponse(2,"sorttypet参数错误",null);
			exit();
		}
		$sql.=" {$_GET['order']}";
		$from=((int)$_GET['page']-1)*$count;
		$sql.=" limit {$from},{$count};";
		$cachename=basename(__FILE__).md5("page={$_GET['page']}&sorttype={$_GET['sorttype']}
		&order={$_GET['order']}&canteen_id={$_POST['canteen_id']}").'.txt';
	}else{
		echo getJsonResponse(2,"post参数错误",null);
		exit();
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
	exit();
}
$cache=new Cache();
if(($val=$cache->get($cachename))!==false){
	echo $val;
	exit();
}
$db=Db::getInstance();
try {
	$db->connect();
	$res=$db->query($sql);
	if($res!==false){
		//var_dump($res);
		if(empty($res))
			$res=null;
		else{
			//是否为新菜
			foreach ($res as &$value){
				getNew($db,$value);
			}
		}
		$json=getJsonResponse(0,"success",$res);
		if($cache->set($cachename, $json,1200)===false){
			Log::error_log($cachename.'  '.$cache->error);
		}
		echo $json;
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
	}
	$db->close();
} catch (Exception $e) {
	echo getJsonResponse(1,"数据库连接错误",null);
	Log::error_log("数据库连接错误");
	exit();
}

//算出三天内的为新菜
function getNew(&$db,&$value){
	$sql="select time>curdate()-3 from canteen_food where canteen_id={$_POST['canteen_id']} 
			and food_id={$value['food_id']};";
	$res=$db->query($sql);
	if($res===false){
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
		exit();
	}else{
		$value['new']=$res[0]['time>curdate()-3'];
	}
}

?>