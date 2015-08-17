<?php

//配置文件

return array(
		//服务器ip
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
	    'logMail' => 'xiaoqinzhe@qq.com'
	);

?>