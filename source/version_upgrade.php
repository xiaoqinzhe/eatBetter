<?php 

require_once('common/common_func.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

$_POST['device_id']=1;
$_POST['oldversion']='0.0.0';

if(isset($_POST['device_id'])&&isset($_POST['oldversion'])){
	list($major,$max,$min)=explode(".", $_POST['oldversion'],3);
	$db=Db::getInstance();
	try {
		$db->connect();
		$sql="select * from upgrade where device_id={$_POST['device_id']};";
		$res=$db->query($sql);
		if($res===false){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
		}else{
			if(empty($res)){
				echo getJsonResponse(2,"id错",null);
			}else{
				if ($res[0]['major_version_number']>$major||($res[0]['major_version_number']==$major&&$res[0]['minor_version_number']>$max)||
						($res[0]['major_version_number']==$major&&$res[0]['minor_version_number']==$max&&$res[0]['revision_number']>$min)){
					//需要更新
					$return=array();
					$return['newversion']=$res[0]['major_version_number'].'.'.$res[0]['minor_version_number'].'.'.$res[0]['revision_number'];
					$return['apkurl']=$res[0]['apkurl'];
					$return['upgrade_content']=$res[0]['upgrade_content'];
					echo getJsonResponse(0,"success",$return);
				}else{
					echo getJsonResponse(3,"版本已为最新",null);
				}
			}
		}
		$db->close();
	} catch (Exception $e) {
		echo getJsonResponse(1,'数据库连接错误',null);
		Log::error_log("数据库连接错误");
		exit();
	}
}else{
	echo getJsonResponse(2,"参数错误",null);
	exit();
}

?>