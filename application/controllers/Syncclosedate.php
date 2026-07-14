<?php
defined('BASEPATH') OR exit('No direct script access allowed');
define('DS',DIRECTORY_SEPARATOR);
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1); 
class Syncclosedate extends CI_Controller {

	public function index()
	{
		echo $this->input->ip_address();
		try{
			$sql="select 
p.span_no as spaj_no,
DATE_FORMAT(p.close_dt,'%Y%m%d') as spaj_date
from tbl_spaj_pending p
where  p.source_id='100' and ifnull(p.close_dt,'')<>''";
			//echo $sql;
			$db = new PDO('mysql:host=localhost:3309;dbname=dbsil;charset=utf8mb4', 'usersil', 'Dwh@2018');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$stmt = $db->query($sql);
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			//echo  json_encode($results);
			$dbDB =null;
			$stmts=null;
			try{
				$dbDB = new PDO("odbc:Driver={SQL Server};Server=10.17.50.90;Database=CAPITALLIFE_UL_PROD", "sa","plita");
				foreach($results as $rs){
					
					$stmts = $dbDB->prepare("exec dbo.usp_sync_pending_close_date '".$rs['spaj_no']."','".$rs['spaj_date']."'");
					$stmts->execute();
					$stmts =null;
					//echo "exec dbo.usp_sync_spaj_date_original_from_pr '".$rs['spaj_no']."','".$rs['spaj_date']."'; \n";
					echo "SPAJ:'".$rs['spaj_no']."',DATE:'".$rs['spaj_date']."'; \n";
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
}
