<?php
    $q=isset($_GET["q"]) ? $_GET["q"]:"";
	$f=isset($_GET["f"]) ? $_GET["f"]:"";
	if($q=="") die("Access Denied");
	$arr=array();
	$q=str_replace("\\","/",$q);
	$dir=realpath(".")."/../../".$q;
	if (is_dir($dir)){
	  /*
	  if ($dh = opendir($dir)){
		while (($file = readdir($dh)) !== false){
		  if($file!="." && $file!=".."){
			$split=explode("_",$file);
			$arr[$split[0]]=$file;
		  }
		}
		closedir($dh);
	  }
	  //echo file_get_contents($dir."/".$f);
	  */
	  $file=$dir."/".$f;
	  header('Expires: 0');
	  header("Content-type: image/png");
	  header('Content-Length: ' . filesize($file));
	  readfile($file);
	}else die("Invalid Directory Access");
	
	
?>