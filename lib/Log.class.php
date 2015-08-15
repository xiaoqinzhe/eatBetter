<?php

require_once 'Config_func.php';

/**
 * 日志处理类
 */
class Log {

	// 日志记录方式
    const SYSTEM    = 0;
    const MAIL      = 1;
    const FILE      = 3;
    const SAPI      = 4;

    //默认路径
	public static $destination='';

	public static function error_log($message,$type=self::FILE,$destination=''){
		$now=date('Y_m_d H:i:s');
		if(empty($destination)){
			if($type==self::FILE)            //文件保存
				$destination=C('logPath').'/'.date('Y_m_d').'.log';
			else if($type==self::MAIL)      //发送邮件
				$destination=C('logMail');
		}
		if($type==self::FILE){     //检查文件大小是否超过限制
			if(is_file($destination)&&filesize($destination)>=1000000)
			{
				$destination=dirname($destination).'/'.basename($destination,'.log').'-'.time().'.log';
				echo $destination;
			}
		}
		error_log("[{$now}] : {$message}\r\n",$type,$destination);
	}
}

?>