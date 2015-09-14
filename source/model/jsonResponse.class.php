<?php

/**
* 返回数据的json格式
*/
class jsonResponse{
	private $res=array(
			'statuscode' => 0,
			'message' => '',
			'data' => null
		);

	public function __construct($code=null,$mes=null,$data=null){
		if($code!=null&&is_int($code))
			$this->res['statuscode']=$code;
		if($mes!=null&&is_string($mes))
			$this->res['message']=$mes;
		if($data!=null)
			$this->res['data']=$data;
	}

	public function setCode($code){
		if(is_int($code))
			$this->res['statuscode']=$code;
	}

	public function setMes($mes){
		if(is_string($mes))
			$this->res['message']=$mes;
	}

	public function setData($data){
		$this->res['data']=$data;
	}

	public function getJson(){
		return json_encode($this->res);
	}
}

?>