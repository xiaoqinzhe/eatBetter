<?php 

require_once 'Config_func.php';

/**
* 文件上传类
*/
class UploadFile{

	private $config = array(
		'maxSize'      =>  -1,    // 上传文件的最大值
        'allowExts'    =>  array(),    // 允许上传的文件后缀 留空不作后缀检查
        'allowTypes'   =>  array(),    // 允许上传的文件类型 留空不做检查
        'savePath'     =>  '',// 上传文件保存路径 
        'autoSub'      =>  false,// 启用子目录保存文件
        /*'thumb'             =>  false,    // 使用对上传图片进行缩略图处理
        'imageClassPath'    =>  'ORG.Util.Image',    // 图库类包路径
        'thumbMaxWidth'     =>  '',// 缩略图最大宽度
        'thumbMaxHeight'    =>  '',// 缩略图最大高度
        'thumbPrefix'       =>  'thumb_',// 缩略图前缀
        'thumbSuffix'       =>  '',
        'thumbPath'         =>  '',// 缩略图保存路径
        'thumbFile'         =>  '',// 缩略图文件名
        'thumbExt'          =>  '',// 缩略图扩展名        
        'thumbRemoveOrigin' =>  false,// 是否移除原图
        'zipImages'         =>  false,// 压缩图片文件上传*/
        );
	private $errorNo=0;         //错误号
	private $errorMsg="";             //错误详细信息
	private $fileInfo=array();       //上传文件的信息

	/**
	* 构造函数
	* @access public
	* @param array $config  上传文件的配置
	*/
	public function __construct($config=array()){
		$this->config['savePath']=C('savePath');
		if(is_array($config)) {
            $this->config   =   array_merge($this->config,$config);
        }
	}

	/**
	* 多文件上传
	* @param string $savePath 文件保存的路径
	* @return string
	*/
	public function upload($savePath=''){
		if(empty($savePath))
			$savePath=$this->config['savePath'];       //默认目录
		
		if(!is_dir($savePath)){         //判断目录存不存在，不存在就创建
			if(!mkdir($savePath,0777,true)){
				$this->errorMes="创建目录失败";
				return false;
			}
		}

		//检查是否有上传
		if(empty($_FILES)){
			$this->errorMsg='$_files为空，没有文件上传';
			return false;
		}
		//处理每个$_FILES
		foreach ($_FILES as $key => $file) {
			if($file['error']){
				$this->setErrorMsg($file['error']);
				return false;
			}
			//检查是否为合法上传，即post方法传的
			if(!is_uploaded_file($file['tmp_name'])){
				$this->errorMsg="Possible file upload attack,不合法上传";
				return false;
			}
			//检查文件大小
			if($this->config['maxSize']!=-1&&$file['size']>$this->config['maxSize']){
				$this->errorNo=8;
				$this->errorMsg="文件大小超过限制";
				return false;
			}
			//检查是否为允许上传的后缀名
			$ext = $this->getExt($file['name']);
			$file['extension']=$ext;
			if(!empty($this->config['allowExts'])){				
				if(!in_array(strtolower($ext),$this->config['allowExts'])){
					$this->errorMsg="文件类型错误";
					return false;
				}else {
					if(in_array(strtolower($ext),array('gif','jpg','jpeg','bmp','png','swf'))){
						$info   = getimagesize($file['tmp_name']);
			            if(false === $info || ('gif' == strtolower($file['extension']) && empty($info['bits']))){
			                $this->errorMsg = '非法图像文件';
			                return false;                
			            }
					}

				}
			}
			//检查文件类型
			if(!empty($this->config['allowTypes'])){
				if(!in_array($file['type'],$this->config['allowTypes'])){
					$this->errorMsg="文件mime类型错误";
					return false;
				}
			}
			//重命名，防止覆盖
			$savename=uniqid();
			//是否创建子目录
			if($this->config['autoSub']){
				$subdir=md5($savename);
				$savename=$subdir.'/'.$savename;
				$dir=$savePath.'/'.$subdir;
				if(!mkdir($dir,0777,true)){
					$this->errorMes="创建子目录失败";
					return false;
				}
			}
			$savename=$savename.".".$file['extension'];   //最终的a
			//move
			if(!move_uploaded_file($file['tmp_name'],$savePath.'/'.$savename)) {
            	$this->errorMsg = '文件上传保存错误！';
            	return false;
        	}

        	$file['key']=$key;
			$file['savename']=$savename;
			$file['savepath']=$savePath;
			unset($file['error'],$file['tmp_name']);
			$this->fileInfo[]=$file;
		}

		return true;
	}

	/**
     * 上传单个上传字段中的文件
     * @access public
     * @param array $file  上传文件信息
     * @param string $savePath  上传文件保存路径
     * @return array/bool
     */
    public function uploadOne($file,$savePath=''){
    	//检查是否有上传
		if(empty($file)){
			$this->errorMsg='$file为空，没有文件上传';
			return false;
		}
		//存储路径处理
    	if(empty($savePath))
			$savePath=$this->config['savePath'];       //默认目录
		
		if(!is_dir($savePath)){         //判断目录存不存在，不存在就创建
			if(!mkdir($savePath,0777,true)){
				$this->errorMes="创建目录失败";
				return false;
			}
		}
		//处理$file
		if($file['error']){
			$this->setErrorMsg($file['error']);
			return false;
		}
		//检查是否为合法上传，即post方法传的
		if(!is_uploaded_file($file['tmp_name'])){
			$this->errorMsg="Possible file upload attack,不合法上传";
			return false;
		}
		//检查文件大小
		if($this->config['maxSize']!=-1&&$file['size']>$this->config['maxSize']){
			$this->errorNo=8;
			$this->errorMsg="文件大小超过限制";
			return false;
		}
		//检查是否为允许上传的后缀名
		$ext = $this->getExt($file['name']);
		$file['extension']=$ext;
		if(!empty($this->config['allowExts'])){				
			if(!in_array(strtolower($ext),$this->config['allowExts'])){
				$this->errorMsg="文件类型错误";
				return false;
			}else {
				if(in_array(strtolower($ext),array('gif','jpg','jpeg','bmp','png','swf'))){
					$info   = @getimagesize($file['tmp_name']);
		            if(false === $info || ('gif' == strtolower($file['extension']) && empty($info['bits']))){
		                $this->errorMsg = '非法图像文件';
		                return false;                
		            }
				}

			}
		}
		//检查文件类型
		if(!empty($this->config['allowTypes'])){
			if(!in_array($file['type'],$this->config['allowTypes'])){
				$this->errorMsg="文件mime类型错误";
				return false;
			}
		}
		//重命名，防止覆盖
		$savename=uniqid();
		//是否创建子目录
		if($this->config['autoSub']){
			$subdir=md5($savename);
			$savename=$subdir.'/'.$savename;
			$dir=$savePath.'/'.$subdir;
			if(!mkdir($dir,0777,true)){
				$this->errorMes="创建子目录失败";
				return false;
			}
		}
		$savename=$savename.".".$file['extension'];   //最终的a
		//move
		if(!move_uploaded_file($file['tmp_name'],$savePath.'/'.$savename)) {
        	$this->errorMsg = '文件上传保存错误！';
        	return false;
    	}

		$file['savename']=$savename;
		$file['savepath']=$savePath;
		unset($file['error'],$file['tmp_name']);

		return $file;
    }

	private function setErrorMsg($errorNo) {
		$this->errorNo=$errorNo;
         switch($errorNo) {
            case 1:
                $this->errorMsg = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->errorMsg = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->errorMsg = '文件只有部分被上传';
                break;
            case 4:
                $this->errorMsg = '没有文件被上传';
                break;
            case 6:
                $this->errorMsg = '找不到临时文件夹';
                break;
            case 7:
                $this->errorMsg = '文件写入失败';
                break;
            default:
                $this->errorMsg = '未知上传错误！';
        }
        return ;
    }

    private function getExt($filename){
    	$fileinfo=pathinfo($filename);
    	return $fileinfo['extension'];
    }

	public function getErrorMsg() {
        return $this->errorMsg;
    }

    public function getErrorNo(){
    	return $this->errorNo;
    }

    public function getUploadFileInfo() {
        return $this->fileInfo;
    }

	public function __get($name){
        if(isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }

    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name]=$value;
        }
    }

    public function __isset($name){
        return isset($this->config[$name]);
    }
}

?>