<?php 

$_POST['canteen_id']=1;

$count=10;
$from=1;
$page=1;
$sql='';
if(isset($_GET['page'])&&is_int($_GET['page'])&&isset($_GET['sorttype'])&&is_string($_GET['sorttype'])
		&&isset($_GET['order'])&&in_array($_GET['order'], array('asc','desc'))){
	if(isset($_POST['canteen_id'])&&is_int($_POST['canteen_id'])){
		$sql="select food.food_id,foodname,price,favor,dislike,imageurl from food,canteen_food 
				where food.food_id=canteen_food.food_id and canteen_food.canteen_id={$_POST['canteen_id']} order by ";
		if($_GET['sorttype']=='new'){
			$sql."time";
		}else if($_GET['sorttype']=='name'){
			$sql."foodname";
		}else if ($_GET['sorttype']=='price'){
			
		}else if($_GET['sorttype']=='favor'){
			
		}else if($_GET['sorttype']=='dislike'){
			
		}else if($_GET['sorttype']=='grade'){
			
		}else{
			echo getJsonResponse(2,"sorttypet参数错误",null);
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

?>