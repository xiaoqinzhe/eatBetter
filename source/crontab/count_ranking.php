<?php 

require_once('../common/common_func.php');
require_once('../../lib/Db.class.php');
require_once('../../lib/Cache.class.php');
require_once('../../lib/Log.class.php');

$db=Db::getInstance();
try {
	$db->connect();
} catch (Exception $e) {
	Log::error_log("count_ranking.php 连接数据库失败");
	exit();
}
$cache=new Cache();

//计算每个学校的食堂的综合评分，排名，写入缓存
//取学校id
$schoolids=$db->query("select school_id from schools;");
if($schoolids===false){
	Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
	exit();
}else{
	if(empty($schoolids))
		exit();
	foreach ($schoolids as $value){  //对每个学校
		$cachename="ranking.php".md5("getcanteen&schoolid={$value['school_id']}").'.txt';
		//查询每个食堂
		$canteen_info=$db->query("select canteens.canteen_id,canteenname,imageurl,grade 
					from canteens,school_canteen 
					where school_id={$value['school_id']} and canteens.canteen_id=school_canteen.canteen_id
					order by grade desc;");
		if($canteen_info===false){
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			exit();
		}else{
			if(empty($canteen_info))
				continue;
			foreach ($canteen_info as &$value2){
				
			}
		}
		//usort($canteen_info,"compare");
		$json=getJsonResponse(0,'success',$canteen_info);
		$cache->set($cachename, $json);
		//计算食物排名
		$food_info=$db->query("select canteens.canteen_id,canteenname,food.food_id,foodname,price,favor,dislike,imageurl,canteen_food.grade
					from canteens,food,canteen_food
					where canteens.canteen_id=canteen_food.canteen_id and food.food_id=canteen_food.food_id and 
					canteen_food.canteen_id in 
					(select canteen_id from school_canteen where school_id={$value['school_id']})
					order by grade desc limit 0,10;");
		if($food_info===false){
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			//echo $db->error;
			exit();
		}else{
			if(empty($food_info))
				continue;
			foreach ($food_info as &$value3){
		
			}
		}
		//usort($food_info,"compare");
		$json2=getJsonResponse(0,'success',$food_info);
		$cachename="ranking.php".md5("getfood&schoolid={$value['school_id']}").'.txt';
		$cache->set($cachename, $json2);
	}
}


function compare($a,$b){
	if($a['grade']<$b['grade'])
		return 1;
	else if($a['grade']==$b['grade'])
		return 0;
	else return -1;
}

?>