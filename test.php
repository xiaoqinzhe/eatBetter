<?php 

require_once('lib/Db.class.php');

$db=Db::getInstance();
try {
	$db->connect();
	echo "success";
} catch (Exception $e) {
	echo $e->getMessage();
}

var_dump(file_exists("./lib/Cache.class.php"));

//echo urlencode('http://localhost/images/user/default.jpg');

//preg_match_all("//", "http://localhost/images/user/default.jpg",$array);
//var_dump(parse_url("http://localhost/images/user/default.jpg"));
//var_dump($array);

/* list($major,$max,$min)=explode(".", "1.0",3);
echo $major.$max.$min; */

//$_POST['a']=null;

//var_dump(is_null($_POST['a']));


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
