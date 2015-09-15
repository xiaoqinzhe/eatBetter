<?php

//配置文件

return array(
		//服务器ip
		'serverIp' => 'sqsmei.sinaapp.com',
		//数据库
		'host' => SAE_MYSQL_HOST_M,
		'username' => SAE_MYSQL_USER,
		'passwd' => SAE_MYSQL_PASS,
		'port' => SAE_MYSQL_PORT,
		'dbname' => SAE_MYSQL_DB,
		//上传文件
		'savePath'     =>  '../uploads',
		//缓存
		'cachePath'    =>  './data/cache',
		 //错误日志
		//'logPath' => $_SERVER['DOCUMENT_ROOT'].'data/logs',
		'logPath' => './data/logs',
		'logMail' => 'xiaoqinzhe@qq.com'
		
		/*//服务器ip
		'serverIp' => 'localhost',
		//数据库
		'host' => 'localhost',
		'username' => 'root',
		'passwd' => 'root',
		'dbname' => 'eatbetter',
		'port' => '80',
		//上传文件
	    'savePath'     =>  $_SERVER['DOCUMENT_ROOT'].'uploads', 
	    //缓存
	    'cachePath'    =>  $_SERVER['DOCUMENT_ROOT'].'data/cache',
	    //错误日志
	    'logPath' => $_SERVER['DOCUMENT_ROOT'].'data/logs', 
	    'logMail' => 'xiaoqinzhe@qq.com' */
	);

?>