<?php 

/**
* 生成缩略图函数
* @param $filename string 源文件
* @param $dst_w int 目标的宽，px
* @param $dst_h int 目标的高，px    默认都为一半
* @param $savepath string 保存路径
* @param $ifdelsrc boolean 是否删除源文件
* @return 缩略图文件名
*/

function thumb($filename,$dst_w=null,$dst_h=null,$savepath=null,$ifdelsrc=true){
	list($src_w,$src_h,$imagetype)=getimagesize($filename);
	$scale=0.5;
	if(is_null($dst_w)||is_null($dst_h)){
		$dst_w=ceil($src_w*$scale);
		$dst_h=ceil($src_h*$scale);
	}
	$mime=image_type_to_mime_type($imagetype);
	$createfunc=str_replace('/','createfrom',$mime);
	$outfunc=str_replace('/',null,$mime);
	$src_image=$createfunc($filename);
	$dst_image=imagecreatetruecolor($dst_w, $dst_h);
	imagecopyresampled($dst_image, $src_image, 0,0,0,0, $dst_w, $dst_h, $src_w, $src_h);
	if(is_null($savepath))
		$savepath=$_SERVER['DOCUMENT_ROOT'].'images/thumb';
	if(!empty($savepath)&&!is_dir($savepath))
		mkdir($savepath,0777,true);
	$fileinfo=pathinfo($filename);
	$savename=uniqid().'.'.$fileinfo['extension'];
	if(!empty($savepath))
		$outfunc($dst_image,$savepath.'/'.$savename);
	else $outfunc($dst_image,$savename);
	imagedestroy($src_image);
	imagedestroy($dst_image);
	if(!$ifdelsrc)
		unlink($filename);
	return $savename;
}