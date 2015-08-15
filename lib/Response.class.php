<?php

/**
* 返回json/xml数据
*/
class Response{

	/**
	* 返回json数据
	* @param mixed $value 
	* @return string
	*/
	public function getJson($value){
		if(empty($value))
			return false;
		return json_encode($value);
	}

	/**
	* 返回xml数据
	* @param mixed $mes
	* @return string
	*/
	public function getXml($mes){
		$xml="<?xml version='1.0' encoding='utf-8' ?>";
		$xml.="<root>";
		if(is_array($mes)){
			$xml.=$this->arrayToXml($mes);
		}else
			$xml.=$mes;
		$xml.="</root>";
		return $xml;
	}

	private function arrayToXml($mes){
		$xml='';
		foreach ($mes as $key => $value) {
			if(is_numeric($key)){
				$xml.="<item id='{$key}'>";
				if(is_array($value))
					$xml.=$this->arrayToXml($value);
				else{
					$xml.="{$value}";
				}
				$xml.="</item>";
			}else{
				$xml.="<{$key}>";
				if(is_array($value))
					$xml.=$this->arrayToXml($value);
				else{
					$xml.="{$value}";
				}
				$xml.="</{$key}>";
			}
		}
		return $xml;
	}
}

?>