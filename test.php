<?php 

require_once('source/common/common_func.php');

echo stringToDb('/sdf/"\\');

/*$data=array(
		"code"=>200,
		"message"=>'asdf',
		"data"=> array(array('id'=>1,'name'=>'adsf'),
			array('id'=>2,'name'=>'adsf'),
			array('id'=>3,'name'=>'adsf'))
	);
echo json_encode($data);
echo '<br/>';
echo md5("123456");
echo '<br/>';
echo strlen('http://localhost/images/users/default.jpg');*/

?>