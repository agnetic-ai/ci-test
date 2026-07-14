<?php
try{
	
	$dbDB = new PDO("odbc:Driver={SQL Server};Server=localhost\\SQLEXPRESS;Database=CAPITALLIFE_INDIVIDU_PROD", "sa","plita");				
}catch(Exception $er){
	echo "1. ".$er->getMessage();
}
?>