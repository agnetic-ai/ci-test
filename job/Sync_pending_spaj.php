<?php

try{
	//$wh="cast(data.approve_spa as date) between cast(dateadd(day,-1,getdate()) as date) and cast(dateadd(day,-1,getdate()) as date)";
	$wh="cast(data.approve_spa as date) between '20190402' and '20190402'";
	//$wh=" data.spaj_code in('PP11172412')";
	/*
	$wh="between 
					cast(case when lower(datename(weekday,getdate()))='monday' 
					then dateadd(day,-3,getdate()) else getdate() end as date)
					and 					
					cast(case when lower(datename(weekday,getdate()))='monday' 
					then dateadd(day,-3,getdate()) else getdate() end as date)";*/
	//$dbDB = new PDO("odbc:Driver={SQL Server};Server=".$server[$app]['ip'].";Database=".$server[$app]['db'], $server[$app]['username'], $server[$app]['password']);				
	$dbDB = new PDO("odbc:Driver={SQL Server};Server=localhost\\SQLEXPRESS;Database=CAPITALLIFE_INDIVIDU_PROD", "sa","plita");
$str="select * from (
					select
					reff_no=a.no_aplikasi,
					policy_no=ps.no_polis,
					spaj_code=a.no_spaj,
					agen_code=a.no_agen_reff,
					agen_name=ag.nama_agen,
					policy_holder_no=a.no_pemegang_polis,
					policy_holder_name=k.nama_klien,
					product_code=upper(p.nama_produk_teknis),
					-- ag.NAMA_KANTOR_CABANG,
					-- ag.KD_KANTOR_CABANG,
					ag.KD_KANTOR_CABANG, 
					ag.NAMA_KANTOR_CABANG, 	
					-- approve_spa=coalesce(a.tgl_mulai,it.tgl_masuk)  
					approve_spa=case when a.tgl_mulai>it.tgl_masuk then it.tgl_masuk 
					when a.tgl_mulai<=it.tgl_masuk then a.tgl_mulai end,
					keterangan=u.keterangan
					from capitallife_individu_prod.dbo.aplikasi a 
					join capitallife_individu_prod.dbo.polis ps on a.no_aplikasi=ps.no_aplikasi
					join capitallife_individu_prod.dbo.akseptasi u on u.no_aplikasi=a.no_aplikasi
					join capitallife_individu_prod.dbo.inward_temp it on a.no_spaj=it.no_spaj
					join capitallife_individu_prod.dbo.produk p on p.kd_produk=a.kd_produk
					join capitallife_individu_prod.dbo.klien k on k.no_klien=a.no_pemegang_polis
					join capitallife_individu_prod.dbo.vw_ms_agen ag on a.no_agen_reff=ag.NO_AGEN collate database_default
					union all
					select 
					reff_no=a.application_no,
					policy_no=a.policy_no,
					spaj_code=a.form_no,
					agen_code=a.intermediary_code_reff,
					agen_name=ag.nama_agen,
					policy_holder_no=a.policy_holder_no,
					policy_holder_name=k.nama_klien,
					product_code=upper(p.technical_product_name),
					-- ag.NAMA_KANTOR_CABANG,
					-- ag.KD_KANTOR_CABANG,
					ag.KD_KANTOR_CABANG, 
					ag.NAMA_KANTOR_CABANG, 
					approve_spa=a.approval_date,
					keterangan=u.remark
					from capitallife_ul_prod.dbo.nb_application a 
					join capitallife_ul_prod.dbo.uw_underwriting u on u.application_no=a.application_no
					join capitallife_ul_prod.dbo.pm_product p on p.product_code=a.product_code
					join capitallife_ul_prod.dbo.vw_cd_klien k  on k.no_klien=a.policy_holder_no
					join capitallife_individu_prod.dbo.vw_ms_agen ag on a.intermediary_code_reff=ag.NO_AGEN collate database_default
					) as data  where $wh
					"; //$wh
$stmt = $dbDB->prepare($str);
$stmt->execute();
//print_r($stmt->fetchAll());
//exit;
$log="";
foreach($stmt->fetchAll() as $rs){
	$log.=json_encode(insert_pending(
$rs['policy_no'],
	$rs['spaj_code'],
$rs['reff_no'],
$rs['agen_code'],
$rs['agen_name'],
$rs['policy_holder_no'],
$rs['policy_holder_name'],
$rs['product_code'],
$rs['NAMA_KANTOR_CABANG'],
$rs['KD_KANTOR_CABANG'],//keterangan
//date("Y-m-d H:i:s"),
$rs['approve_spa'],
'SPAJ Asli',
$rs['keterangan'],
'Batch'))."\n";
}
$file_log_name=date("dmY")."_logsync.txt";
//file_put_contents("E:\\WebServer\\www\\job\\logs\\".$file_log_name, $log);

}catch(Exception $er){
	echo "1. ".$er->getMessage();	
}
function insert_pending(
$ipolicy_no,
$ispaj_code,
$ireff_no,
$iagen_code,
$iagen_name,
$ipolicy_holder_no,
$ipolicy_holder_name,
$iproduct_code,
$iNAMA_KANTOR_CABANG,
$iKD_KANTOR_CABANG,
$ispaj_received_dt,
$icatatan,
$iremarks,
$iusrid){
try{
	$sql="call usp_insert_spaj_pending('$ipolicy_no','$ispaj_code','$ireff_no','$iagen_code','$iagen_name','$ipolicy_holder_no','$ipolicy_holder_name','$iproduct_code','$iNAMA_KANTOR_CABANG','$iKD_KANTOR_CABANG','$ispaj_received_dt','$icatatan','$iremarks','$iusrid');";
	//echo $sql;
	$db = new PDO('mysql:host=localhost:3309;dbname=dbsil;charset=utf8mb4', 'usersil', 'Dwh@2018');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$stmt = $db->query($sql);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}catch(Exception $er){
	echo "2. ".$er->getMessage();
}
}

?>