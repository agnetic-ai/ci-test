<?php

try {
    $q=isset($_GET["q"]) ? $_GET["q"]:"";
	if($q=="") die("Access Denied");
	$arr=array();
	$q=str_replace("\\","/",$q);
	$dir=realpath(".")."/../../".$q;
	//echo $dir.".jpg"; 
	echo '<img src="data:image/jpg;base64,'.base64_encode(file_get_contents($dir.".jpg"))."' />";
}catch(Exception $e){
	print_r($e);
	//echo"Access denied";
}

?>