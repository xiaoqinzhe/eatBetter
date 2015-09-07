<?php 

require_once('../../lib/Db.class.php');
require_once('../../lib/Log.class.php');

$db=Db::getInstance();
try {
	$db->connect();
} catch (Exception $e) {
	Log::error_log("count_canteens_grade.php 连接数据库失败");
	exit();
}
//食堂评分
$canteenids=$db->query("select canteen_id from canteens;");
if($canteenids===false)
{
	Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
	exit();
}else{
	foreach ($canteenids as $value){
		//食物评分
		$grade=$db->query("select avg(grade) from canteen_grade where canteen_id={$value['canteen_id']};");
		if($grade===false){
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
			exit();
		}else{
			//写入食堂评分
			$res=$db->execute("update canteens set grade={$grade[0]['avg(grade)']} where canteen_id={$value['canteen_id']};");
			if($res===false){
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
				exit();
			}
			$canteenfood=$db->query("select food_id from canteen_food where canteen_id={$value['canteen_id']}");
			if ($canteenfood===false){
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
				exit();
			}
			foreach ($canteenfood as $foodid){
				$foodgrade=$db->query("select avg(grade) from food_grade where canteen_id={$value['canteen_id']} and food_id={$foodid['food_id']};");
				if($foodgrade===false){
					Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
					exit();
				}
				$res=$db->execute("update canteen_food set grade={$foodgrade[0]['avg(grade)']} where canteen_id={$value['canteen_id']} and food_id={$foodid['food_id']};");
				if($res===false){
					Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));     //错误日志
					exit();
				}
			}			
		}
	}
}



?>