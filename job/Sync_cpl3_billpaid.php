<?php

	try{
		//$dbDB = new PDO("odbc:Driver={SQL Server};Server=10.17.44.25;Database=CAPITALLIFE_UL_PROD", "sa","plita");
		$dbDB = new PDO("dblib:host=10.17.44.25:1433;dbname=CAPITALLIFE_UL_PROD", "sa", "plita");
		$stmts = $dbDB->prepare("
select 
n.policy_no,
regular_premium=c.amount,
cc.notation,
n.notification_mobile,
k.nama_klien,
p.product_name,
tgl_report=replace(convert(nvarchar(20),dbo.F_SW_SYSTEM_TIME(),111),'/','-')
from fn_collection c
join nb_application n on c.application_no=n.application_no
join pm_product p on p.product_code=n.product_code
join sw_currency cc on cc.currency_code=c.currency_code
join vw_cd_klien k on k.no_klien=n.policy_holder_no
where 
coalesce(p.guaranted_roi_status,'N')='N'
and cast(c.recon_date as DATE)=cast(dbo.F_PS_GET_PREV_WORKING_DATE(dbo.F_SW_SYSTEM_TIME()) as date)
		");
		$stmts->execute();
		$rows = $stmts->fetchAll(PDO::FETCH_ASSOC);
		//print_r($rows);
		
		foreach($rows as $rs){
			$dbDB =null;
			$stmts=null;
			$db = new PDO('mysql:host=127.0.0.1:3306;dbname=dbsil;charset=utf8mb4', 'root', 'Dwh@2018');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			
			$db->query("call usp_tbl_sms_template_jt(5,'".$rs['notification_mobile']."','".$rs['policy_no']."','".$rs['nama_klien']."','".$rs['regular_premium']."','".$rs['notation']."','','".$rs['product_name']."','".$rs['tgl_report']."')");
		}
		
	}catch(Exception $ers){
		echo $ers->getMessage();
	}
	$stmts=null;
	$dbDB=null;
?>