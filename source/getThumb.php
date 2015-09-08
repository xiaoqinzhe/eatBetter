<?php 

require_once('common/common_func.php');

$_GET['width']=20;
$_GET['height']=20;
$_GET['imageurl']="http%3A%2F%2Flocalhost%2Fimages%2Fuser%2Fdefault.jpg";

if(isset($_GET['imageurl'])){
	$_GET['imageurl']=urldecode($_GET['imageurl']);
	$urlinfo=parse_url($_GET['imageurl']);
	$imagepath;
	if($urlinfo['host']==getServerIp()){
		$imagepath='..'.$urlinfo['path'];
	}else{
		exit();
	}
	if(!file_exists($imagepath)){
		exit();
	}
	$fileinfo=pathinfo($imagepath);
	if(isset($_GET['width'])&&isset($_GET['height'])){
		$thumbname=md5($imagepath."width={$_GET['width']}height={$_GET['height']}").'.'.$fileinfo['extension'];
	}else
		$thumbname=md5($imagepath).'.'.$fileinfo['extension'];
	$savepath='../images/thumb'.'/'.$thumbname;
	list($src_w,$src_h,$imagetype)=getimagesize($imagepath);
	$mime=image_type_to_mime_type($imagetype);
	header("Content-Type:".$mime);
	$createfunc=str_replace('/','createfrom',$mime);
	$outfunc=str_replace('/',null,$mime);
	if(file_exists($savepath))    //存在缩略图
	{		
		$im=$createfunc($savepath);
		$outfunc($im);
		imagedestroy($im);
	}else{    //不存在缩略图
		$scale=0.5;     //默认
		if(!isset($_GET['width'])||!isset($_GET['height'])){
			$dst_w=ceil($src_w*$scale);
			$dst_h=ceil($src_h*$scale);
		}else{
			$dst_w=(int)$_GET['width'];
			$dst_h=(int)$_GET['height'];
		}
		$src_image=$createfunc($imagepath);
		$dst_image=imagecreatetruecolor($dst_w, $dst_h);
		imagecopyresampled($dst_image, $src_image, 0,0,0,0, $dst_w, $dst_h, $src_w, $src_h);
		$outfunc($dst_image,$savepath);
		$outfunc($dst_image);
		imagedestroy($dst_image);
		imagedestroy($dst_image);
	}
}else{
	exit();
}

?>