<?php 

require_once "lib/thumb_func.php";

$data=array(
		"code"=>200,
		"message"=>'asdf',
		"data"=> array("schoolid"=>array(1,2,3),
			"name"=>array("a","b","c"))
	);
echo json_encode($data);
echo '<br/>';
echo md5("123456");
echo '<br/>';
echo strlen('http://localhost/images/users/default.jpg');

?>