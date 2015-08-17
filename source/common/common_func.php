<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/lib/Db.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/Config_func.php');

//返回json格式的数据
function getJsonResponse($code,$mes='',$data=null){
	$res=array(
			'statuscode' => 0,
			'message' => '',
			'data' => null
		);
	if(is_int($code))
		$res['statuscode']=$code;
	else 
		return false;
	if(is_string($mes))
		$res['message']=$mes;
	if($data!=null)
		$res['data']=$data;
	return json_encode($res);
}

//检验用户id号是否存在,用于必须检测才能继续进行操作的地方
function checkUserId($id){
	$db=Db::getInstance();
	try {
		$db->connect();        
		$res=false;
		if(is_int($id))
			$res=$db->query("select user_id from users where user_id={$id};");
		if($res){
			$db->close();       
			return true;
		}else{
			return false;
		}
	} catch (Exception $e) {
		return false;
	}
}

//得到服务器ip
function getServerIp(){
	return C('serverIp');
}

//字符串格式化在保存进数据库之前   *****
function stringToDb($str){
	if(!get_magic_quotes_gpc())
		$str=addslashes($str);
	return $str;
}

//字符串格式化在取出数据库时  *****
function dbToString($str){
	if(!get_magic_quotes_gpc())
		$str=stripslashes($str);
	return $str;
}

?>