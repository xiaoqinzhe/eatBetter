<?php 

require_once('common/common_func.php');
require_once('common/VoiceVerify.php');
require_once('../lib/Db.class.php');
require_once('../lib/Log.class.php');

/* $_POST['phone']='18819451372';
$_POST['capture']='981s432'; */

$AccountSid='aaf98f894f16fdb7014f1adf248f043d';
$AccountToken='423254c2e08a42d39b58b7be64f91d60';
$AppId='aaf98f894f16fdb7014f1ae13f6a0451';
$serverIp="sandboxapp.cloopen.com";
$port="8883";
$version="2013-12-26";

if(isset($_GET['type'])){
	if(!isset($_POST['phone'])){
		echo getJsonResponse(2,"post参数错误",null);
		exit();
	}
	$db=Db::getInstance();
	try {
		$db->connect();
	} catch (Exception $e) {
		echo getJsonResponse(1,$db->error,null);
		Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
	}
	if($_GET['type']=='send'){
		if(checkPhone($db,$_POST['phone'])){
			echo getJsonResponse(4,'手机号已经注册过',null);
			$db->close();
			exit();
		}
		$voice=new VoiceVerify($serverIp,$port,$version);
		$voice->setAccount($AccountSid, $AccountToken);
		$voice->setAppId($AppId);
		$captcha='';
		for($i=0;$i<6;$i++){
			$captcha.=rand(0, 9);
		}
		$res=$voice->voiceVerify($captcha, '2', $_POST['phone'], '121', '', 'zh');
		//var_dump($res);
		if($res['statusCode']!='000000'){    //error
			echo getJsonResponse(3,'发送失败',null);
			$db->close();
			exit();
		}
		$res=$db->execute("insert into captcha_cache values({$_POST['phone']},{$captcha},now());");
		if($res===false){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			$db->close();
			exit();
		}
		$db->close();
		echo getJsonResponse(0,'success',null);
	}elseif (isset($_GET['type'])=='check'){
		if(!isset($_POST['phone'])||!isset($_POST['capture'])){
			echo getJsonResponse(2,"post参数错误",null);
			$db->close();
			exit();
		}
		$res=$db->query("select code from captcha_cache where phone='{$_POST['phone']}' order by time desc limit 0,1;");
		if($res===false){
			echo getJsonResponse(1,$db->error,null);
			Log::error_log('database error：'.$db->error.' in '.basename(__FILE__));
			$db->close();
			exit();
		}
		if(empty($res)){
			echo getJsonResponse(2,"没有发过验证码或过期",null);
		}else{
			if($res[0]['code']==$_POST['capture']){
				echo getJsonResponse(0,'success',null);
			}else{
				echo getJsonResponse(3,'验证码错误',null);
			}
		}
		$db->close();
	}else{
		echo getJsonResponse(2,"get参数错误",null);
		exit();
	}
}else{
	echo getJsonResponse(2,"get参数错误",null);
	exit();
}

?>