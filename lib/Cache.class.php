<?php

require_once 'Config_func.php';

/**
* 缓存类，get，set，delete缓存文件
*/
class Cache{

	private $cachePath = '';
	public $error = '';

	public function __construct(){
		$this->cachePath=C('cachePath');
	}

	/**
	* 设置缓存
	* @param string $key 键，实际是缓存文件名
	* @param string $value 值，缓存的内容，为serialize后的
	* @param integer $expire 缓存过期时间，单位s，默认0为永久不过期
	* @return boolean
	*/
	public function set($key, $value, $expire = 0){
		if($expire!=0)
			$expire+=time();
		$timestr=sprintf("%'018d",$expire);
		if(file_put_contents($this->cachePath.'/'.$key,$timestr.$value)==false){
			$this->error="缓存保存错误";
			return false;
		}
		return true;
	}

	/**
	* 得到缓存
	* @param string $key 键，实际是缓存文件名
	* @return mixed false为失败/失效，否则返回内容
	*/
	public function get($key){
		if(!file_exists($this->cachePath.'/'.$key)){
			$this->error="缓存不存在";
			return false;
		}
		$content=file_get_contents($this->cachePath.'/'.$key);
		if($content==false){
			$this->error="缓存读取失败";
			return false;
		}
		$time=(int)substr($content,0,18);
		if(time()>$time){
			$this->error="缓存失效";
			return false;
		}
		return substr($content,18);
	}

	/**
	* 删除一个缓存
	* @param string $key 键，实际是缓存文件名
	* @return boolean 
	*/
	public function delete($key){
		if(!file_exists($this->cachePath.'/'.$key)){
			$this->error="缓存不存在";
			return false;
		}
		return unlink($this->cachePath.'/'.$key);
	}

	/**
	* 删除所有缓存
	* @return boolean 
	*/
	public function clear(){
		$dir=opendir($this->cachePath);
		if($dir===false){
			$this->error="打开目录失败";
			return false;
		}
		while($file=readdir($dir)){
			if($file!='.'&&$file!='..'){
				if(!is_dir($this->cachePath.'/'.$file)){
					unlink($this->cachePath.'/'.$file);
				}
			}
		}
		closedir($dir);
		return true;
	}

}

?>