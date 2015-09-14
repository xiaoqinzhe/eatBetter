<?php 

require_once('common/common_func.php');
require_once '../lib/Cache.class.php' ;

//$_POST['school_id']=1;

if(isset($_GET['get'])){
	if (isset($_POST['school_id'])){
		$cache=new Cache();
		$cachename=basename(__FILE__);
		if($_GET['get']=='canteen'){
			$cachename.=md5("getcanteen&schoolid={$_POST['school_id']}").'.txt';
		}elseif($_GET['get']=='food'){
			$cachename.=md5("getfood&schoolid={$_POST['school_id']}").'.txt';
		}else{
			echo getJsonResponse(2,"get参数错误",null);
			exit();
		}
		if(($val=$cache->get($cachename))!==false){
			echo $val;
		}else{
			echo getJsonResponse(1,"缓存错误",null);
		}
	}else{
		echo getJsonResponse(2,"post参数没有设置",null);
	}
}else{
	echo getJsonResponse(2,"get参数没有设置",null);
}

?>