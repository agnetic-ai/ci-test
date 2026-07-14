<?php
function get_spaj_date(){
try{
	$sql="select 
p.span_no as spaj_no,
DATE_FORMAT(p.close_dt,'%Y%m%d') as spaj_date
from tbl_spaj_pending p
where  p.source_id='100' and ifnull(p.close_dt,'')<>''";
	//echo $sql;
	$db = new PDO('mysql:host=localhost:3306;dbname=dbsil;charset=utf8mb4', 'usersil', 'Dwh@2018');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$stmt = $db->query($sql);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	//echo  json_encode($results);
	$dbDB =null;
	$stmts=null;
	try{
		//$dbDB = new PDO("odbc:Driver={SQL Server};Server=10.17.44.25;Database=CAPITALLIFE_INDIVIDU_PROD", "sa","plita");
		$dbDB = new PDO("dblib:host=10.17.44.25:1433;dbname=CAPITALLIFE_INDIVIDU_PROD", "sa", "plita");
		foreach($results as $rs){
			
			$stmts = $dbDB->prepare("exec dbo.usp_sync_pending_close_date '".$rs['spaj_no']."','".$rs['spaj_date']."'");
			$stmts->execute();
			$stmts =null;
			echo "exec dbo.usp_sync_pending_close_date '".$rs['spaj_no']."','".$rs['spaj_date']."'; \n";
		}
		//$stmts = $dbDB->prepare($str);
		//$stmts->execute();
		
	}catch(Exception $ers){
		echo $ers->getMessage();
	}finally{
		$stmts=null;
		$dbDB=null;
	}
	
}catch(Exception $er){
	echo $er->getMessage();
}
}
get_spaj_date();
?>