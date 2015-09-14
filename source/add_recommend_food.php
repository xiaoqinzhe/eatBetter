<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');
require_once '../lib/UploadFile.class.php';

$_POST['user_id']=1;
$_POST['access_token']='9e5cd477f4ff4a04d915b3892e58c033';
$_POST['canteen_id']=1;
$_POST['foodname']='肖式炒饭';
$_POST['content']=json_encode(array("1.aaa","2.bbbb/","3./\dfa"));

if(isset($_POST['user_id'])&&isset($_POST['access_token'])&&isset($_POST['canteen_id'])&&isset($_POST['foodname'])){
	$db=Db::getInstance(array('autocommit'=>false));
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
	if(!$db->startTransaction()){
		//transiton
		echo getJsonResponse(1,"事务启动失败",null);
		$db->close();
		exit();
	}
	$foodname=stringToDb($_POST['foodname']);
	$flag=0;  //是否为新的食物
	$foodid=checkFoodname($db,$foodname,$flag);   //得到对应id
	//检查是否存在或者已被推荐给食堂
	if($flag)
		checkExist($db, $foodid);
	$imageurl="http://".getServerIp()."/uploads/recommendedfood/default.jpg";
	if(isset($_FILES['imageurl'])){
		//上传文件
		$config1=array(
				'maxSize'      =>  2100000,    // 上传文件的最大值,2M多
				'allowExts'    =>  array('png','jpg'),    // 允许上传的文件后缀 留空不作后缀检查
				'allowTypes'   =>  array('image/png','image/jpeg'),    // 允许上传的文件类型 留空不做检查
				'savePath'     =>  $_SERVER['DOCUMENT_ROOT'].'uploads/recommendedfood/',// 上传文件保存路径
		);
		$up=new UploadFile($config1);
		//上传食物图片
		$res1=$up->uploadOne($_FILES['imageurl']);
		if($res1!==false){
			$savePath='http://'.getServerIp().'/uploads/recommendedfood/'.$res1['savename'];
			$imageurl=stringToDb($savePath);
		}else{  //上传图片失败
			echo getJsonResponse(5,$uf->errorMsg,null);
			$db->rollback();  //回滚
			$db->close();
			exit();
		}
	}
	$sql="insert into recommendedfood values(null,{$_POST['user_id']},{$_POST['canteen_id']},{$foodid},current_date(),0,'{$imageurl}',false,null);";
	$res=$db->execute($sql);
	if($res){
		//echo getJsonResponse(0,"success",null);
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		$db->rollback();
		$db->close();
		exit();
	}
	//查推荐菜的id
	$rec=$db->query("select recommend_id from recommendedfood where user_id={$_POST['user_id']} and canteen_id={$_POST['canteen_id']} and food_id={$foodid};");
	if($rec===false){
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		$db->rollback();
		$db->close();
		exit();
	}
	if(empty($rec)){
		echo getJsonResponse(1,"",null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		$db->rollback();
		$db->close();
		exit();
	}
	$recid=$rec[0]['recommend_id'];
	$len=sizeof($_POST['content']);
	//上传步骤图片
	$imagefile=array();
	for($i=1;$i<=$len;$i++){
		$name='imagefile'.$i;
		if(isset($_FILES[$name])){
			$res1=$up->uploadOne($_FILES[$name]);
			if($res1!==false){
				$savePath='http://'.getServerIp().'/uploads/recommendedfood/'.$res1['savename'];
				$savePath=stringToDb($savePath);
				$imagefile[]=$savePath;
			}else{  //上传图片失败
				echo getJsonResponse(4,$uf->errorMsg,null);
				$db->rollback();  //回滚
				$db->close();
				exit();
			}
		}
	}
	//做法步骤
	if(isset($_POST['content']))
		setContent($db,$recid,$_POST['content'],$imagefile);
	$db->commit();
	echo getJsonResponse(0,'success',$rec[0]['recommend_id']);
	$db->close();
}else{
	echo getJsonResponse(2,"参数没有设置",null);
	exit();
}

/**
 * 检查是否有这食物
 * @param DB $db
 * @param string $foodname
 * @return int
 */
function checkFoodname(&$db,$foodname,&$flag){
	$sql="select food_id from food where foodname='{$foodname}';";
	$res=$db->query($sql);
	if($res===false){
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		$db->rollback();
		$db->close();
		exit();
	}else{
		if(empty($res)){
			$sql2="insert into food values(null,'{$foodname}');";
			if($db->execute($sql2)===false){
				echo getJsonResponse(1,$db->error,null);
				Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
				$db->rollback();
				$db->close();
				exit();
			}else{
				$sql3="select food_id from food where foodname='{$foodname}';";
				$res2=$db->query($sql);
				if($res2===false){
					echo getJsonResponse(1,$db->error,null);
					Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
					$db->rollback();
					$db->close();
					exit();
				}else{
					if(empty($res2)){
						echo getJsonResponse(1,$db->error,null);
						Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
						$db->rollback();
						$db->close();
						exit();
					}
					return $res2[0]['food_id'];
				}
			}
		}else{
			$flag=1;
			return $res[0];
		}
	}
}

/**
 * 
 * @param Db $db
 * @param int $foodid
 */
function checkExist(&$db, $foodid){
	$sql="select food_id from canteen_food where food_id={$foodid};";
	$res=$db->query($sql);
	if($res!==false){
		if(!empty($res)){
			echo getJsonResponse(3,"食物已经存在",null);
			$db->rollback();  //回滚
			$db->close();
			exit();
		}
		$sql2="select user_id from recommendedfood where user_id={$_POST['user_id']} and 
		 canteen_id={$_POST['canteen_id']} and food_id={$foodid};";
		$res2=$db->query($sql2);
		if($res2!==false){
			if(!empty($res2)){
				echo getJsonResponse(4,"食物已经推荐给食堂",null);
				$db->rollback();  //回滚
				$db->close();
				exit();
			}
		
		}else{
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			$db->rollback();
			$db->close();
			exit();
		}
	}else{
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		$db->rollback();
		$db->close();
		exit();
	}
}

/**
 * 食物做法步骤
 * @param unknown $db
 * @param unknown $foodid
 * @param unknown $content
 */
function setContent(&$db,$recid,$content,$imagefile){
	if($content==null)
		return;
	//$method=explode("||", $content);
	$method=json_decode($content,true);
	for($i=1;$i<=sizeof($method);$i++){
		if(empty($imagefile[$i-1]))
			$imagefile[$i-1]='http://'.getServerIp().'/uploads/recommendedfood/default.jpg';
		$sql="insert into foodmethod values({$recid},{$i},'".stringToDb($method[$i-1])."','{$imagefile[$i-1]}');";
		$res=$db->execute($sql);
		if(!$res){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			$db->rollback();
			$db->close();
			exit();
		}
	}
}

?>