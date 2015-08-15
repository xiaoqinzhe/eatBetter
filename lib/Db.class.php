<?php

require_once 'Config_func.php';

/**
* 数据库操作类
*/
class Db{

	static private $_instance =null;      //Db对象的实例
	private $config = array(
			'host' => 'localhost',
			'username' => 'root',
			'passwd' => 'root',
			'dbname' => 'test',
			'port' => '80',
			'autocommit' => false
		);
	private $mysqli = null;          //mysqli 对象
	private $connected = false;      //是否连接上数据库
	public $numRows = 0;             //返回或影响结果数
	public $error = '';           //错误信息

	/**
     * 构造函数
     * @access public
     * @param array $config 数据库配置数组
     */
	private function __construct($config=array()){
        $this->config['host']=C('host');
        $this->config['username']=C('username');
        $this->config['passwd']=C('passwd');
        $this->config['dbname']=C('dbname');
        $this->config['port']=C('port');
		if(!empty($config)){
			$this->config=array_merge($this->config,$config);
		}
	}

	/**
     * 数据库连接
     * @access public
     * @throws Execption
     */
	public function connect(){
		if(!$this->connected){
			$this->mysqli = @new mysqli($this->config['host'],$this->config['username'],$this->config['passwd'],$this->config['dbname']);
			if($this->mysqli->connect_errno){
				throw new Exception("数据库连接失败：".$this->mysqli->connect_error);
			}
			$this->mysqli->query("set names utf8;");
			$this->connected = true;
			if($this->config['autocommit'])
				$this->mysqli->autocommit(true);
			else 
				$this->mysqli->autocommit(false);
		}
	}

	/**
     * 选择数据库
     * @access public
     * @param string $dbname 数据库名
     * @return bool
     */
	public function selectDb($dbname){
		$this->connect();
		if(!empty($dbname))
			return $this->mysqli->select_db($dbname);
		return false;
	}

	/**
     * 执行查询 主要针对 SELECT,SHOW等指令
     * 返回数据集
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function query($str) {
    	//$this->connect();
    	if(empty($str)){
    		$this->error="查询字段不能为空";
    		return false;
    	}
    	$result=$this->mysqli->query($str);
    	if(!$result){
    		$this->error();       //查询出错
    		return false;
    	}else{
    		$array = array();
    		$this->numRows=$result->num_rows;
    		while($row=$result->fetch_assoc()){
    			$array[] = $row;
    		}
    		$result->free();
    		return $array;
    	}
    }

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function execute($str) {
        //$this->connect();
        if(empty($str)){
    		$this->error="查询字段不能为空";
    		return false;
    	}
    	$result=$this->mysqli->query($str);
    	if(!$result){
    		$this->error();
    		return false;          //执行出错
    	}else{
    		return $this->numRows=$this->mysqli->affected_rows;   		 
    	}
    }

    /**
     * 启动事务
     * @access public
     * @return boolean
     */
    public function startTransaction() {
        //$this->connect();
        
        if($this->mysqli!=null){
        	return $this->mysqli->query("start transaction;");
        }
        return false;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolen
     */
    public function commit() {
    	if($this->mysqli!=null){
        	return $this->mysqli->query("commit;");
        }
        return false;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolen
     * @throws ThinkExecption
     */
    public function rollback() {
        if($this->mysqli!=null){
        	return $this->mysqli->query("rollback;");
        }
        return false;
    }

    public function error(){
    	$this->error=$this->mysqli->error;
    	return $this->error;
    }

    public function getMysqli(){
    	return $this->mysqli;
    }

	public static function getInstance($config=array()){
		if(self::$_instance==null){
			self::$_instance=new Db($config);
		}
		return self::$_instance;
	}

	/**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 关闭连接
        $this->close();
    }

    public function close(){
    	if($this->mysqli!=null)
    		$this->mysqli->close();
    }
}

?>