<?php

//配置文件

return array(
		//服务器ip
		'serverIp' => 'localhost',
		//'serverIp' => 'sqsmei.sinaapp.com',
		//数据库
		'host' => 'localhost',
		//'host' => SAE_MYSQL_HOST_M,
		'username' => 'root',
		//'username' => SAE_MYSQL_USER,
		'passwd' => 'root',
		//'passwd' => SAE_MYSQL_PASS,
		'dbname' => 'eatbetter',
		//'dbname' => SAE_MYSQL_DB,
		'port' => '3306',
		//'port' => SAE_MYSQL_PORT,
		//上传文件
	    'savePath'     =>  $_SERVER['DOCUMENT_ROOT'].'uploads', 
		//'savePath'     =>  '../uploads',
	    //缓存
	    'cachePath'    =>  $_SERVER['DOCUMENT_ROOT'].'data/cache',
		//'cachePath'    =>  './data/cache',
	    //错误日志
	    'logPath' => $_SERVER['DOCUMENT_ROOT'].'data/logs',
		//'logPath' => './data/logs',
	    'logMail' => 'xiaoqinzhe@qq.com'
	);

?>