<?php 

class VoiceVerify{
	private $AccountSid;
	private $AccountToken;
	private $AppId;
	private $ServerIP;
	private $ServerPort;
	private $Batch;  //时间戳
	private $BodyType = "json";//包体格式，可填值：json 、xml
	private $Handle;
	
	function __construct($ServerIP,$ServerPort,$SoftVersion)	
	{
		$this->Batch = date("YmdHis");
		$this->ServerIP = $ServerIP;
		$this->ServerPort = $ServerPort;
		$this->SoftVersion = $SoftVersion;
	}
	
	function setAccount($AccountSid,$AccountToken){
		$this->AccountSid = $AccountSid;
		$this->AccountToken = $AccountToken;
	}
	
	function setAppId($AppId){
		$this->AppId = $AppId;
	}
	
	/**
	 * 语音验证码
	 * @param verifyCode 验证码内容，为数字和英文字母，不区分大小写，长度4-8位
	 * @param playTimes 播放次数，1－3次
	 * @param to 接收号码
	 * @param displayNum 显示的主叫号码
	 * @param respUrl 语音验证码状态通知回调地址，云通讯平台将向该Url地址发送呼叫结果通知
	 * @param lang 语言类型
	 * @param userData 第三方私有数据
	 */
	function voiceVerify($verifyCode,$playTimes,$to,$displayNum,$respUrl,$lang,$userData='',$welcomePrompt='',$playVerifyCode='')
	{
		//主帐号鉴权信息验证，对必选参数进行判空。
		if(!$this->accAuth()){
			return false;
		}
		// 拼接请求包体
		if($this->BodyType=="json"){
			$body= "{'appId':'$this->AppId','verifyCode':'$verifyCode','playTimes':'$playTimes','to':'$to','respUrl':'$respUrl','displayNum':'$displayNum',
			'lang':'$lang'}";
		}else{
			$body="<VoiceVerify>
			<appId>$this->AppId</appId>
			<verifyCode>$verifyCode</verifyCode>
			<playTimes>$playTimes</playTimes>
			<to>$to</to>
			<respUrl>$respUrl</respUrl>
			<displayNum>$displayNum</displayNum>
			<lang>$lang</lang>
			<userData>$userData</userData>
			<welcomePrompt>$welcomePrompt</welcomePrompt>
			<playVerifyCode>$playVerifyCode</playVerifyCode>
			</VoiceVerify>";
		}
		// 大写的sig参数
		$sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
		// 生成请求URL
		$url="https://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Calls/VoiceVerify?sig=$sig";
		// 生成授权：主帐户Id + 英文冒号 + 时间戳。
		$authen = base64_encode($this->AccountSid . ":" . $this->Batch);
		// 生成包头
		$header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
		// 发送请求
		$result = $this->curl_post($url,$body,$header);
		if($result===false){
			return false;
		}
		//var_dump($result);
		if($this->BodyType=="json"){//JSON格式
			$datas=json_decode($result,true);
		}else{ //xml格式
			$datas = simplexml_load_string(trim($result," \t\n\r"));
		}
	
		return $datas;
	}
	
	function curl_post($url,$data,$header,$post=1)
	{
		//初始化curl
		$ch = curl_init();
		//参数设置
		curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, $post);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  //文件流返回
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		$result = curl_exec ($ch);
		/* //连接失败
		if($result == FALSE){
			if($this->BodyType=='json'){
				$result = "{\"statusCode\":\"172001\",\"statusMsg\":\"网络错误\"}";
			} else {
				$result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
			}
		} */
	
		curl_close($ch);
		return $result;
	}
	
	function accAuth()
	{
		if($this->ServerIP==""){
			/* $data = new stdClass();
			$data->statusCode = '172004';
			$data->statusMsg = 'IP为空';
			return $data; */
			return false;
		}
		if($this->ServerPort<=0){
			/* $data = new stdClass();
			$data->statusCode = '172005';
			$data->statusMsg = '端口错误（小于等于0）';
			return $data; */
			return false;
		}
		/* if($this->SoftVersion==""){
			$data = new stdClass();
			$data->statusCode = '172013';
			$data->statusMsg = '版本号为空';
			return $data;
		} */
		if($this->AccountSid==""){
			/* $data = new stdClass();
			$data->statusCode = '172006';
			$data->statusMsg = '主帐号为空';
			return $data; */
			return false;
		}
		if($this->AccountToken==""){
			/* $data = new stdClass();
			$data->statusCode = '172007';
			$data->statusMsg = '主帐号令牌为空';
			return $data; */
			return false;
		}
		if($this->AppId==""){
			/* $data = new stdClass();
			$data->statusCode = '172012';
			$data->statusMsg = '应用ID为空';
			return $data; */
			return false;
		}
		return true;
	}
}

?>