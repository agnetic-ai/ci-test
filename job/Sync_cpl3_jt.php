<?php

	try{
		//$dbDB = new PDO("odbc:Driver={SQL Server};Server=10.17.44.25;Database=CAPITALLIFE_UL_PROD", "sa","plita");
		$dbDB = new PDO("dblib:host=10.17.44.25:1433;dbname=CAPITALLIFE_UL_PROD", "sa", "plita");
		$stmts = $dbDB->prepare("
		declare
@evaluation_date datetime

select @evaluation_date=GETDATE()

select n.application_no,n.policy_no,sp.status_name as policy_status,k.nama_klien,p.product_name,
start_date=convert(nvarchar(20),n.start_date,103),
end_date=convert(nvarchar(20),n.end_date,103),
n.currency_code,
cc.notation,
n.regular_premium,
(select COUNT(1) from fn_billing where application_no=n.application_no) total_billing,
upb.total_billing_period_paid,

convert(nvarchar(20),dbo.F_PS_GET_NEXT_BILL_STARTDATE(n.application_no, upb.due_date),103) as tgl_jatuh_tempo_pembayaran_premi,
convert(nvarchar(20),DATEADD(DAY,30,dbo.F_PS_GET_NEXT_BILL_STARTDATE(n.application_no, upb.due_date)),103) as tgl_akhir_grace_period,

convert(nvarchar(20),upb.recon_date,103) as tgl_pembayran_premi_terakhir,
uu.last_unit as last_unit,
replace(convert(nvarchar(20),@evaluation_date,111),'/','-')  as tgl_report,
DATEDIFF(DAY,dbo.F_PS_GET_NEXT_BILL_STARTDATE(n.application_no,upb.due_date),@evaluation_date) as T_minus,
n.notification_mobile
from nb_application n
join sw_currency cc on n.currency_code=cc.currency_code
join sw_policy_status sp on n.policy_status=sp.status_code
join (select application_no,count(1) as total_billing_period_paid,max(recon_date) as recon_date,due_date=MAX(due_date) 
from fn_billing where coalesce(recon_status,'N')='Y' group by application_no) upb on upb.application_no=n.application_no

join (select application_no,MAX(start_date) as start_date,due_date=MAX(due_date) 
from fn_billing group by application_no) bb on bb.application_no=n.application_no
join pm_product p on n.product_code=p.product_code
join vw_cd_klien k on n.policy_holder_no=k.no_klien
join(select 
n.application_no,
last_unit=sum(nu.unit_amt)-sum(np.deduct) 
from nb_application n
join nb_application_product p on n.application_no=p.application_no
join (select application_no,fund_code,unit_amt from ps_fund_transaction_history 
where transaction_type_code='RP' and coalesce(posting_status,'N')='Y') nu on n.application_no=nu.application_no
join (select application_no,fund_code,sum(unit_amt) as deduct from ps_fund_transaction_history 
where transaction_type_code<>'RP' and coalesce(posting_status,'N')='Y' 
and cast(pricing_date as date)<=@evaluation_date
group by application_no,fund_code) np on n.application_no=np.application_no and nu.fund_code=np.fund_code
where p.product_code in(
'1211104170',
'1211104171'
) and coalesce(n.approval_status,'N')='Y'  and cast(n.start_date as date)<=@evaluation_date
group by n.application_no
) uu on uu.application_no=n.application_no
where p.product_code in(
'1211104170',
'1211104171'
) 
and coalesce(n.approval_status,'N')='Y'
and DATEDIFF(DAY,dbo.F_PS_GET_NEXT_BILL_STARTDATE(n.application_no,upb.due_date),@evaluation_date) in(-14,-7,-2,0)
order by n.start_date asc
		");
		$stmts->execute();
		$rows = $stmts->fetchAll(PDO::FETCH_ASSOC);
		//print_r($rows);
		$arr_id=array(
		'-7'=>1,
		'-14'=>2,
		'-2'=>3,
		'0'=>4,
		);
		foreach($rows as $rs){
			$dbDB =null;
			$stmts=null;
			$db = new PDO('mysql:host=127.0.0.1:3306;dbname=dbsil;charset=utf8mb4', 'root', 'Dwh@2018');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			
			$db->query("call usp_tbl_sms_template_jt(".$arr_id[$rs['T_minus']].",'".$rs['notification_mobile']."','".$rs['policy_no']."','".$rs['nama_klien']."','".$rs['regular_premium']."','".$rs['notation']."','".$rs['tgl_jatuh_tempo_pembayaran_premi']."','".$rs['product_name']."','".$rs['tgl_report']."')");
		}
		
	}catch(Exception $ers){
		echo $ers->getMessage();
	}
	$stmts=null;
	$dbDB=null;
?>