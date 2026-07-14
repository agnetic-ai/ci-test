<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Bangkok");
class Deviasiapi extends CI_Controller {
	public function __construct(){
		parent::__construct();
		
		
		
		if(!$_POST){
			exit;
		}
		
		$hd=$this->input->request_headers();
		
		
		
		if($hd['Origin']!="http://localhost:3000" and $hd['Origin']!="https://deviasi2.capitallife.co.id" and $hd['Origin']!="http://deviasi2.capitallife.co.id"
		and $hd['Origin']!="https://sicepot.capitallife.co.id" and $hd['Origin']!="https://sicepotuat.capitallife.co.id" 
		){
			echo json_encode(array(
				'error'=>1,
				'message'=>'Invalid Access',
				'info'=>false
				));
				exit;
		}
		
		
	}
	
	public function index()
	{
		exit;
	}
	
	public function login(){
		
			$credential=$this->input->post("credential",true);
			$credential=json_decode(base64_decode($credential),true);
			
			
			
			if($credential==""){
				echo json_encode(array(
				'error'=>1,
				'message'=>'Invalid Access',
				'info'=>false
				));
				exit;
			}
			
			$username=isset($credential['username']) ? $credential['username']:"";
			$password=isset($credential['password']) ? $credential['password']:"";
			
			if($username=="" or $password==""){
				echo json_encode(array(
				'error'=>1,
				'message'=>'Invalid Credential Login',
				'info'=>false
				));
				exit;
			}

			$login=$this->db->query("select agen_code,agen_name,case when agen_level='ABMCLI' then 'BBC'
			when agen_level='HOBCLI' then 'BBH'
			when agen_level='MSCLI' then 'DIRBCI'
			else
				agen_level
			end as agen_level, bbc_code, branch
			from tbl_agen where username='$username' and password='$password' and status_type='A'");
			$row=$login->row_array();
			$this->db->query("insert tbl_agen_login_hst(agen_code,login_info,login_date) select '$username','".json_encode($_SERVER)."',now();");
			
			if(count($row)>0){
				
				$rp=array();
				if(in_array($row['agen_level'],array('RM','BM','BBC','ABMCLI','HOBCLI'))){
					$this->db->reconnect();
					$pengajuan=$this->db->query("call usp_generate_agen_ticket('".$row['agen_code']."')");
					$rp=$pengajuan->row_array();	
				}else{
					
				}
				
				echo json_encode(array(
				'error'=>0,
				'message'=>'Success Login',
				'info'=>$row,
				'pengajuan'=>$rp
				));
				
			}else{
				echo json_encode(array(
				'error'=>1,
				'message'=>'Your login account is not registered.',
				'info'=>false,
				'pengajuan'=>false
				));
			}
			
			
		
	}
	
	public function webpush(){
		$webpush=$this->input->post("web-push",true);
		$webpush=json_decode(base64_decode($webpush),true);
		
		if($webpush==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access',
			'info'=>false
			));
			exit;
		}
		
		$agen_code=isset($webpush['agen_code']) ? $webpush['agen_code']:"";
		$token=isset($webpush['token']) ? $webpush['token']:"";
		
		$this->db->query("insert into tbl_agen_webpush(agen_code,fcm_token,last_change_dt) values('$agen_code','$token',NOW())");
		/*
		$this->db->query("insert  tbl_agen_webpush_hst(agen_code,fcm_token,title,body,status_sent,sent_date,last_change_dt,status_read,status_read_date) 
				select agen_code,fcm_token,'Welcome ".$agen_code."','Selamat anda berhasil masuk.',1,null,now(),0,null from tbl_agen_webpush where agen_code='".$agen_code."' group by agen_code,fcm_token"); */
		
	}
	
	public function submit_pengajuan(){
		$data=$this->input->post("data",true);
		$agen=$this->input->post("agen",true);
		
		if($data=="" or $agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access',
			'info'=>false
			));
			exit;
		}
		
		$data=json_decode(base64_decode($data),true);
		$agen=json_decode(base64_decode($agen),true);
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		$data['no_spaj']=isset($data['no_spaj']) ? $data['no_spaj']:"";
		
		if($data['no_spaj']=="" or $agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Pengajuan',
			'info'=>false
			));
			exit;
		}
		
		
		
		
		
		
		
		//echo"<pre>";
		//print_r($data);
		//print_r($agen);
		
		$query_check=$this->db->query("select count(1) as total from tbl_agen_pengajuan_deviasi where coalesce(is_submited,0)=1 and coalesce(reject_status,0)=0 and spaj_code='".$data['no_spaj']."'");//or ticket_id='".$data['no_ticket']."'
		$row_check=$query_check->row_array();
		
		if($row_check['total']>0){
			echo json_encode(array(
			'error'=>1,
			'message'=>'No Spaj atau tiket Sudah pernah pengajukan pengajuan sebelumnya',
			'info'=>false
			));
			exit;
		}
		
		//get level mapping
		$query=$this->db->query("select * from tbl_agen_approval_level_bci where 
		level1='".$agen['info']['agen_code']."' or 
		level2='".$agen['info']['agen_code']."' or 
		level3='".$agen['info']['agen_code']."'
		");
		$rl=$query->row_array();
		
		$premi=$data['premi'];
		if(substr_count($data['premi'],",")>0){
			$premi=str_replace(",","",$data['premi']);
		}else if(substr_count($data['premi'],".")>1){
			$premi=str_replace(",",".",str_replace(".","",$data['premi']));
		}else{
			$premi=$premi;
		}
		
		if(!preg_match("/^([a-zA-Z'\,\. ]+)$/",$data['nama_pempol'])){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Nama pemegang polis tidak valid.',
			'info'=>false
			));
			exit;
		}
		
		if((float)$data['subsidi_pr']>0.5){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Maksimum subsidi PR 0.5%.',
			'info'=>false
			));
			exit;
		}
		
		if($agen['info']['agen_level']=="RM"){
			$this->db->query("update tbl_agen_pengajuan_deviasi set 
			spaj_code='".$data['no_spaj']."',
			client_name='".$data['nama_pempol']."',
			product_name='".$data['produk']."',
			tanggal_masuk='".$data['tanggal_masuk']."',
			invesment_period='".$data['tenor']."',
			premium='".$premi."',
			current_rate='".$data['current_rate']."',
			cashback='".$data['cashback']."',
			propose_rate='".$data['propose_rate']."',
			subsidi_pr='".$data['subsidi_pr']."',
			subsidi_cli='".$data['subsidi_cli']."',
			subsidi_total='".$data['subsidi_total']."',
			alasan_pengajuan='".$data['alasan_pengajuan']."',
			is_submited=1,
			approval_1='".$rl['level1']."',
			approval_1_date=now(),
			approval_2='".($rl['level2']=="" ? ($rl['level3']=="" ? $rl['level4']:$rl['level3']):$rl['level2'])."',
			approval_2_date=".($rl['level2']=="" ? "now()":"null").",
			approval_3='".($rl['level3']=="" ? ($rl['level4']=="" ? $rl['level5']:$rl['level4']):$rl['level3'])."',
			approval_3_date=".($rl['level3']=="" ? "now()":"null").",
			approval_4='".$rl['level4']."',
			approval_4_date=null,
			approval_5='".$rl['level5']."',
			approval_5_date=null,
			approval_6='robin.winata',
			approval_6_date=null,
			approval_7='jamaludin',
			approval_7_date=null,
			approval_8='antony.japari',
			approval_8_date=null,
			create_date=now(),
			create_by='".$agen['info']['agen_code']."',
			last_change_date=now(),
			last_change_by='".$agen['info']['agen_code']."'
			where ticket_id='".$data['no_ticket']."' and agen_code_request='".$agen['info']['agen_code']."'
			");
		}else if($agen['info']['agen_level']=="BM"){
			$this->db->query("update tbl_agen_pengajuan_deviasi set 
			spaj_code='".$data['no_spaj']."',
			client_name='".$data['nama_pempol']."',
			product_name='".$data['produk']."',
			tanggal_masuk='".$data['tanggal_masuk']."',
			invesment_period='".$data['tenor']."',
			premium='".$premi."',
			current_rate='".$data['current_rate']."',
			cashback='".$data['cashback']."',
			propose_rate='".$data['propose_rate']."',
			subsidi_pr='".$data['subsidi_pr']."',
			subsidi_cli='".$data['subsidi_cli']."',
			subsidi_total='".$data['subsidi_total']."',
			alasan_pengajuan='".$data['alasan_pengajuan']."',
			is_submited=1,
			approval_1=null,
			approval_1_date=null,
			approval_2='".$rl['level2']."',
			approval_2_date=now(),
			approval_3='".($rl['level3']=="" ? $rl['level4']:$rl['level3'])."',
			approval_3_date=".($rl['level3']=="" ? "now()":"null").",
			approval_4='".$rl['level4']."',
			approval_4_date=null,
			approval_5='".$rl['level5']."',
			approval_5_date=null,
			approval_6='robin.winata',
			approval_6_date=null,
			approval_7='jamaludin',
			approval_7_date=null,
			approval_8='antony.japari',
			approval_8_date=null,
			create_date=now(),
			create_by='".$agen['info']['agen_code']."',
			last_change_date=now(),
			last_change_by='".$agen['info']['agen_code']."'
			where ticket_id='".$data['no_ticket']."' and agen_code_request='".$agen['info']['agen_code']."'
			");
		}else if($agen['info']['agen_level']=="BBC"){
			$this->db->query("update tbl_agen_pengajuan_deviasi set 
			spaj_code='".$data['no_spaj']."',
			client_name='".$data['nama_pempol']."',
			product_name='".$data['produk']."',
			tanggal_masuk='".$data['tanggal_masuk']."',
			invesment_period='".$data['tenor']."',
			premium='".$premi."',
			current_rate='".$data['current_rate']."',
			cashback='".$data['cashback']."',
			propose_rate='".$data['propose_rate']."',
			subsidi_pr='".$data['subsidi_pr']."',
			subsidi_cli='".$data['subsidi_cli']."',
			subsidi_total='".$data['subsidi_total']."',
			alasan_pengajuan='".$data['alasan_pengajuan']."',
			is_submited=1,
			approval_1=null,
			approval_1_date=null,
			approval_2=null,
			approval_2_date=null,
			approval_3='".$rl['level3']."',
			approval_3_date=now(),
			approval_4='".$rl['level4']."',
			approval_4_date=null,
			approval_5='".$rl['level5']."',
			approval_5_date=null,
			approval_6='robin.winata',
			approval_6_date=null,
			approval_7='jamaludin',
			approval_7_date=null,
			approval_8='antony.japari',
			approval_8_date=null,
			create_date=now(),
			create_by='".$agen['info']['agen_code']."',
			last_change_date=now(),
			last_change_by='".$agen['info']['agen_code']."'
			where ticket_id='".$data['no_ticket']."' and agen_code_request='".$agen['info']['agen_code']."'
			");
		}
		
		
		$approval_layer=($agen['info']['agen_level']=="RM" ? $rl['level2']:($agen['info']['agen_level']=="BM" ? $rl['level3']:($agen['info']['agen_level']=="BBC" ? $rl['level4']:"")));
		
		//sent notifikasi satu level di atas.
		$this->db->query("insert into tbl_agen_webpush_hst(agen_code,fcm_token,title,body,status_sent,sent_date,status_read,status_read_date,last_change_dt)
		select agen_code,fcm_token,'Hi ".$approval_layer." anda memiliki permintaan persetujuan','Permintaan persetujuan untuk no ticket : ".$data['no_ticket']."',1,null,now(),0,null from tbl_agen_webpush where agen_code='".$approval_layer."' group by agen_code,fcm_token");
		
		$this->db->query("insert into tbl_agen_pengajuan_deviasi_hst(ID_deviasi,agen_code,agen_level,trans_type,remarks,create_date,create_by)
		select ID,agen_code_request,agen_code_request_level,'Register','Pengajuan Baru',now(),agen_code_request from tbl_agen_pengajuan_deviasi where ticket_id='".$data['no_ticket']."' and spaj_code='".$data['no_spaj']."'");
		
		$this->db->query("update dbsil.`tbl_agen_pengajuan_deviasi` set subsidi_total=subsidi_pr+subsidi_cli where is_submited='1' and coalesce(subsidi_total,0)<=0");
		
		
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success Update pengajuan'
			));
			exit; 
		
	}
	
	public function refresh_ticket(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		
		$agen=json_decode(base64_decode($agen),true);
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		
		
		$login=$this->db->query("select agen_code,agen_name,case when agen_level='ABMCLI' then 'BBC'
			when agen_level='HOBCLI' then 'BBH'
			when agen_level='MSCLI' then 'DIRBCI'
			else
				agen_level
			end as agen_level, bbc_code, branch from tbl_agen where username='".$agen['info']['agen_code']."' and status_type='A'"); //* updated By Yopi 'add bbc_code'
		$row=$login->row_array();
		
		$this->db->reconnect();
		$pengajuan=$this->db->query("call usp_generate_agen_ticket('".$row['agen_code']."')");
		$rp=$pengajuan->row_array();	
		
		echo json_encode(array(
				'error'=>0,
				'message'=>'Success Login',
				'info'=>$row,
				'pengajuan'=>$rp
				));
		exit;		
	}
	
	public function get_client_rate(){
		$data=$this->input->post("data",true);
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		//$this->db->query("insert tbl_debug(remarks,create_date) values('".base64_decode($data)."',NOW())");
		$agen=json_decode(base64_decode($agen),true);
		$data=json_decode(base64_decode($data),true);
		
		
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		/*
		if(strpos($data['premi'],",")>1){
			$data['premi']=str_replace(",","",$data['premi']);
		}else{
			$data['premi']=str_replace(",",".",str_replace(".","",$data['premi']));
		}
		*/
		
		$data['premi']=str_replace(",","",$data['premi']);
		
		try{
			$dbDB = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_UL_PROD", "sa", "C4p1t4l.L1f3.2021");
			$sql="exec CAPITALLIFE_UL_PROD.dbo.usp_get_client_rate_api '".$data['product']."',".$data['premi'].",'".$data['tenor']."','".$data['tanggal_masuk']."'";
			
			//$this->db->query("insert tbl_debug(remarks,create_date) values('".addcslashes($sql,"'")."',NOW())");
			
			$stmts = $dbDB->prepare($sql);
			$stmts->execute();
			$rows = $stmts->fetch(PDO::FETCH_ASSOC);
			echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			'data'=>$rows
			));
			exit;
			
		}catch(Exception $erx){
			
		}
		
	}
	
	public function approval_list(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		$agen 	   = json_decode(base64_decode($agen),true);
		$bbcCode   = $agen['info']['bbc_code'];
		$brancCode = $agen['info']['branch'];

		$agen['info']['agen_code'] = isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}

		$sql="call usp_agen_pengajuan_approval_list('".$agen['info']['agen_code']."')";
		//exit($sql);

		$isApproval = '';
		$isDirCli2  = false;
		// if($agen['info']['agen_level'] == "BBC" && preg_match('/bbc/i', $agen['info']['bbc_code'])){
		if($agen['info']['agen_level'] == "BBC" && preg_match('/bbc/i', $agen['info']['bbc_code'])){
			$isApproval = "a.bbc_code = '$bbcCode' AND status = '1' AND";	
		} else if($agen['info']['agen_level'] == "BBH" && preg_match('/bbh/i', $agen['info']['bbc_code'])){
			$isApproval = "a.bbc_code NOT IN ('BBC 1','BBC 2','BBC 3','BBC 4','BBC 5','BBC 6','BBC 7','BBC 8','BBC 9','BBC 10') AND status = '1' AND";	
		} else if($agen['info']['bbc_code'] == "DIRCLI1"){
			$isApproval = "a.status = '2' AND";
		} else if($agen['info']['bbc_code'] == "DIRCLI2"){
			$isApproval = "a.status = '3' AND";
			$isDirCli2  = true;
		} else {
			$isApproval = "a.status = '0' AND";
		}

		$deviationOther = $this->db->query("
		    SELECT 
				a.id, a.ticket_id, b.agen_name, a.spaj_polis_code AS spaj_code, a.client_name, '' AS product_name, '' AS propose_rate, '' AS subsidi_pr, a.created_at AS create_date, '' AS premium, '' AS subsidi_cli
				,'deviasi_other' AS source, a.remark, deviations AS deviation_detail
			FROM tbl_agen_pengajuan_deviasi_other a
			JOIN tbl_agen b ON b.agen_code = a.agen_code_request
			WHERE ".$isApproval." a.reject_date IS NULL ORDER BY a.created_at ASC
		")->result_array();

		if(!$isDirCli2){ //* Exclude obligor for DIRCLI2
			$query = $this->db->query($sql);
			$rp	   = $query->result_array();
		} else {
			$rp    = array();
		}

		$result = array_merge($deviationOther, $rp);

		echo json_encode(array(
			'error'	 	=> 0,
			'message'	=> 'Success',
			'data'		=> $result
		));
		exit;

	}
	public function get_detail_ticket(){
		$data 		= $this->input->post("data",true);
		$agen		= $this->input->post("agen",true);
		$sourceType = $this->input->post("sourceType",true);

		if($agen=="" or $data==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		$data 		= base64_decode($data);
		$agen 		= json_decode(base64_decode($agen),true);
		$sourceType = base64_decode($sourceType);

		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}

		if($sourceType == "deviation_other"){ //* Updated notification By Yopi Deviation Other 20-10-2025

			$rp = [];
			$sql2 = "
				SELECT DATE_FORMAT(a.created_at,\"%d %b %y\") as dt_tgl, DATE_FORMAT(a.created_at,\"%h:%i\") as dt_jam, a.remark AS remarks, b.agen_name,
					b.agen_code, case when b.agen_level in('BBC','BBH','DIRBCI','DIRCLI') then '' else d.agency_team_name end as agency_team_name, coalesce(d.agency_team_desc,'') as agency_team_desc, b.agen_level
				FROM tbl_agen_pengajuan_deviasi_other a
					JOIN tbl_agen b on b.agen_code = a.agen_code_request
			 		LEFT JOIN dc_intermediary c on c.intermediary_code = a.agen_code_request
			 		LEFT JOIN sw_agency_team d on d.agency_team_code = c.agency_team_code
				WHERE a.id = ".$data."

				UNION

				SELECT DATE_FORMAT(e.created_at,\"%d %b %y\") as dt_tgl, DATE_FORMAT(e.created_at,\"%h:%i\") as dt_jam, e.remark AS remarks, b.agen_name,
					b.agen_code, case when b.agen_level in('BBC','BBH','DIRBCI','DIRCLI') then '' else d.agency_team_name end as agency_team_name, coalesce(d.agency_team_desc,'') as agency_team_desc, b.agen_level
				FROM tbl_agen_pengajuan_deviasi_other a
					LEFT JOIN tbl_agen_pengajuan_deviasi_other_approval e on e.deviation_other_id = a.id
					JOIN tbl_agen b on b.agen_code = e.approval
					LEFT JOIN dc_intermediary c on c.intermediary_code = a.agen_code_request
					LEFT JOIN sw_agency_team d on d.agency_team_code = c.agency_team_code
				WHERE e.deviation_other_id = ".$data."

				UNION

				SELECT DATE_FORMAT(a.reject_date,\"%d %b %y\") as dt_tgl, DATE_FORMAT(a.reject_date,\"%h:%i\") as dt_jam, a.reject_remark AS remarks, b.agen_name,
					b.agen_code, case when b.agen_level in('BBC','BBH','DIRBCI','DIRCLI') then '' else d.agency_team_name end as agency_team_name, coalesce(d.agency_team_desc,'') as agency_team_desc, b.agen_level
				FROM tbl_agen_pengajuan_deviasi_other a
					JOIN tbl_agen b on b.agen_code = a.reject_by
			 		LEFT JOIN dc_intermediary c on c.intermediary_code = a.agen_code_request
			 		LEFT JOIN sw_agency_team d on d.agency_team_code = c.agency_team_code
				WHERE a.id = ".$data." AND reject_date IS NOT NULL

			";
			$query2	= $this->db->query($sql2);
			$rp2	= $query2->result_array();
			//* Updated notification By Yopi Deviation Other 20-10-2025
		} else {

			$sql="select t.*,case when datediff(t.create_date,t.tanggal_masuk)>7 then 'Pengajuan ini ter-indikasi <strong>Backdated</strong> lebih dari satu minggu (hari kalender).' else '' end  as msg_backend_notification from tbl_agen_pengajuan_deviasi t where t.ID=".$data;
			$query=$this->db->query($sql);
			$rp=$query->row_array();
			
			$sql2="
			select DATE_FORMAT(h.create_date,\"%d %b %y\") as dt_tgl,DATE_FORMAT(h.create_date,\"%h:%i\") as dt_jam,h.remarks,a.agen_name,a.agen_code,case when h.agen_level in('BBC','BBH','DIRBCI','DIRCLI') then '' else t.agency_team_name end as agency_team_name,coalesce(t.agency_team_desc,'') as agency_team_desc,h.agen_level from tbl_agen_pengajuan_deviasi_hst h 
			join tbl_agen a on a.agen_code=h.agen_code
			left join dc_intermediary d on d.intermediary_code=h.agen_code
			left join sw_agency_team t on t.agency_team_code=d.agency_team_code
			where h.ID_deviasi=".$data." AND h.source_type = 'deviation_obligor' order by h.create_date asc
			";
			$query2 = $this->db->query($sql2);
			$rp2	= $query2->result_array();


		}

		
		echo json_encode(array(
			'error'		=> 0,
			'message'	=> 'Success',
			'data'		=> $rp,
			'timeline'	=> $rp2
		));

		exit;
	}
	
	public function tolak_ticket(){
		$data=$this->input->post("data",true);
		$agen=$this->input->post("agen",true);
		if($agen=="" or $data==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		$data=json_decode(base64_decode($data),true);
		$agen=json_decode(base64_decode($agen),true);
		
		//print_r($agen);
		//exit;
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		$sql="update tbl_agen_pengajuan_deviasi set  
		reject_status='1',
		reject_date=now(),
		reject_by='".$agen['info']['agen_code']."',
		reject_reason='".$data['pesan']."'
		where ticket_id='".$data['id']."' ";
		//exit($sql);
		$query=$this->db->query($sql);
		
		//get detail pengajuan
		$qticket=$this->db->query("select * from tbl_agen_pengajuan_deviasi where ticket_id='".$data['id']."'");
		$rt=$qticket->row_array();
		
		//kirim notifikasi ke pr pengajuan
		$this->db->query("insert into tbl_agen_webpush_hst(agen_code,fcm_token,title,body,status_sent,sent_date,status_read,status_read_date,last_change_dt)
		select agen_code,fcm_token,'Hi ".$rt['agen_code_request']." ada update untuk pengajuan anda','Pengajuan untuk no ticket : ".$rt['ticket_id']."  Telah ditolak oleh ".$agen['info']['agen_code']."',1,null,now(),0,null from tbl_agen_webpush where agen_code='".$rt['agen_code_request']."' group by agen_code,fcm_token");
		
		$this->db->query("insert into tbl_agen_pengajuan_deviasi_hst(ID_deviasi,agen_code,agen_level,trans_type,remarks,create_date,create_by)
		select ID,'".$agen['info']['agen_code']."','".$agen['info']['agen_level']."','Reject','Penolakan : ".$data['pesan']."',now(),'".$agen['info']['agen_code']."' from tbl_agen_pengajuan_deviasi where ticket_id='".$data['id']."'");
		
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success'
			));
			exit;
	}
	
	public function approve_ticket(){
		$data=$this->input->post("data",true);
		$agen=$this->input->post("agen",true);
		if($agen=="" or $data==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		$data=json_decode(base64_decode($data),true);
		$agen=json_decode(base64_decode($agen),true);
		
		//print_r($agen);
		//exit;
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		//get detail pengajuan
		$qticket=$this->db->query("select * from tbl_agen_pengajuan_deviasi where ticket_id='".$data['id']."'");
		$rt=$qticket->row_array();
		
		//get level mapping
		$query=$this->db->query("select * from tbl_agen_approval_level_bci where 
		level1='".$agen['info']['agen_code']."' or 
		level2='".$agen['info']['agen_code']."' or 
		level3='".$agen['info']['agen_code']."'
		");
		$rl=$query->row_array();
		
		$update_tanggal=($agen['info']['agen_level']=="RM" ? "approval_1_date":($agen['info']['agen_level']=="BM" ? "approval_2_date":($agen['info']['agen_level']=="BBC" ? "approval_3_date":($agen['info']['agen_level']=="BBH" ? "approval_4_date":($agen['info']['agen_level']=="DIRBCI" ? "approval_5_date":($agen['info']['agen_level']=="DIRCLI" ? "approval_6_date":""))))));
		
		
		if($agen['info']['agen_level']=="DIRCLI" and $agen['info']['agen_code']=="antony.japari"){
			$update_tanggal="approval_8_date";	
		}else if($agen['info']['agen_level']=="DIRCLI" and $agen['info']['agen_code']=="jamaludin"){
			$update_tanggal="approval_7_date";	
		}else{
			
			$check_next_approve=$rt[str_replace("_date","",$update_tanggal)];
			$check_nilai_1=(int)str_replace("approval_","",str_replace("_date","",$update_tanggal))+1;
			$check_nilai_2=(int)str_replace("approval_","",str_replace("_date","",$update_tanggal))+2;
			
			if($rt[str_replace("_date","",$update_tanggal)]=="" and $rt["approval_".$check_nilai_1]!=""){
				$update_tanggal="approval_".$check_nilai_1."_date";
			}else if($rt[str_replace("_date","",$update_tanggal)]=="" and $rt["approval_".$check_nilai_1]==""){
				$update_tanggal="approval_".$check_nilai_2."_date";
			}			
			
		}
		
		
		
		$sql="update tbl_agen_pengajuan_deviasi set  
		".$update_tanggal."=NOW()
		where ticket_id='".$data['id']."'";
		$query=$this->db->query($sql);
		
		
		
		$approval_layer=($agen['info']['agen_level']=="RM" ? $rl['level2']:($agen['info']['agen_level']=="BM" ? $rl['level3']:($agen['info']['agen_level']=="BBC" ? $rl['level4']:($agen['info']['agen_level']=="BBH" ? $rl['level5']:($agen['info']['agen_level']=="DIRBCI" ? $rl['level6']:"")))));
		//sent notifikasi satu level di atas.
		if($agen['info']['agen_level']=="DIRBCI"){
			$this->db->query("insert into tbl_agen_webpush_hst(agen_code,fcm_token,title,body,status_sent,sent_date,status_read,status_read_date,last_change_dt)
		select agen_code,fcm_token,concat('Hi ',agen_code,' anda memiliki permintaan persetujuan'),'Permintaan persetujuan untuk no ticket : ".$data['id']."',1,null,now(),0,null from tbl_agen_webpush where agen_code in('robin.winata','jamaludin','antony.japari') group by agen_code,fcm_token");
		
		}else{
			if($agen['info']['agen_level']!="DIRCLI"){
			$this->db->query("insert into tbl_agen_webpush_hst(agen_code,fcm_token,title,body,status_sent,sent_date,status_read,status_read_date,last_change_dt)
		select agen_code,fcm_token,'Hi ".$approval_layer." anda memiliki permintaan persetujuan','Permintaan persetujuan untuk no ticket : ".$data['id']."',1,null,now(),0,null from tbl_agen_webpush where agen_code='".$approval_layer."' group by agen_code,fcm_token");
			}
		
		}
		
		//kirim notifikasi ke pr pengajuan
		$this->db->query("insert into tbl_agen_webpush_hst(agen_code,fcm_token,title,body,status_sent,sent_date,status_read,status_read_date,last_change_dt)
		select agen_code,fcm_token,'Hi ".$rt['agen_code_request']." ada update untuk pengajuan anda','Permintaan persetujuan untuk no ticket : ".$data['id']."  Telah disetujui oleh ".$agen['info']['agen_code']."',1,null,now(),0,null from tbl_agen_webpush where agen_code='".$rt['agen_code_request']."' group by agen_code,fcm_token");
		
		
		$this->db->query("insert into tbl_agen_pengajuan_deviasi_hst(ID_deviasi,agen_code,agen_level,trans_type,remarks,create_date,create_by)
		select ID,'".$agen['info']['agen_code']."','".$agen['info']['agen_level']."','Approve','Menyetujui',now(),'".$agen['info']['agen_code']."' from tbl_agen_pengajuan_deviasi where ticket_id='".$data['id']."'");
		
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			
			));
			exit;
	}
	
	public function notification(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}

		$agen = json_decode(base64_decode($agen),true);

		$agen['info']['agen_code'] = isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";

		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}

		//* Updated By Yopi Deviation Other Notification 17-10-2025
		$sql2="
			select 
				d.ID,ticket_id,a.agen_name,d.spaj_code,d.client_name,d.product_name,ROUND(d.propose_rate,2) AS propose_rate,d.subsidi_pr,d.subsidi_cli,d.create_date,
				case when coalesce(reject_status,0)=1 then 'Reject' when (d.approval_6_date is not null or d.approval_7_date is not null or d.approval_8_date is not null) then 'Closed' else 'On-going' end as status_pengajuan,
				concat(fnAmountIndo(d.premium),' (',d.product_name,' (',d.invesment_period,'))') as premium, 'deviation_obligor' as source_type, '' as remark

				from tbl_agen_pengajuan_deviasi d 
				join tbl_agen a on a.agen_code=d.agen_code_request
				where coalesce(d.is_submited,0)=1 and agen_code_request='".$agen['info']['agen_code']."'

			UNION

			SELECT
				d.id, ticket_id, a.agen_name, d.spaj_polis_code, d.client_name, '' AS product_name, '' AS propose_rate, '' AS subsidi_pr, '' AS subsidi_cli, d.created_at AS create_date,
			CASE
				WHEN d.reject_date IS NOT NULL THEN 'Reject' 
				WHEN d.status = '4' THEN 'Closed'
				WHEN d.status = '5' THEN 'Closed'
			ELSE 'On-going'
			END AS status_pengajuan,
				'0' as premium,
			'deviation_other' as source_type, d.remark

			FROM tbl_agen_pengajuan_deviasi_other d 
				JOIN tbl_agen a ON a.agen_code = d.agen_code_request
				LEFT JOIN tbl_agen_pengajuan_deviasi_other_approval h ON h.deviation_other_id = d.id
			WHERE agen_code_request = '".$agen['info']['agen_code']."'
			ORDER BY create_date DESC

		";

		$query2 = $this->db->query($sql2);
		$rp2	= $query2->result_array();
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			'data'=>$rp2
		));

		exit;
		
	}
	
	public function confirmation(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		
		$sql2="
		SELECT 
		ID,
NO_POLIS,
fnDateDayIndo(TGL_BAYAR) as TGL_BAYAR,
fnDateDayIndo(TGL_KONFIRMASI) as TGL_KONFIRMASI,
fnAmountIndo(JML_PREMI) as JML_PREMI,
KD_VALUTA,
NAMA_PEMPOL,
KODE_PR,
NAMA_PR,
CABANG,
CLUSTER,
PRODUK,
PERIODE_CAIR,
STATUS,
STATUS_CODE,fnDateDayIndo(create_date) as TGL_INFO
FROM `tbl_spaj_surrender_book` where kode_PR='".$agen['info']['agen_code']."' 
and STATUS_KONFIRMASI is null and cast(TGL_KONFIRMASI as date)>=cast(NOW() as date)

order by DATE(TGL_BAYAR) asc
		";
		$query2=$this->db->query($sql2);
		$rp2=$query2->result_array();
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			'data'=>$rp2
			));
			exit;
		
	}
	
	public function confirmation_save(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		$agen=json_decode(base64_decode($agen),true);
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		$data=json_decode(base64_decode($this->input->post("data",true)),true);
		//print_r($data);
		//exit;
		/*
		TGL_INFO	"Kamis, 13 Oktober 2022"
		NAMA_PEMPOL	"DESSY "
		NO_POLIS	"0100077516"
		JML_PREMI	"1.000.000.000,00"
		TGL_BAYAR	"Kamis, 3 November 2022"
		PERIODE_CAIR	"CPP IDR - 3/12 "
		TGL_KONFIRMASI	"Kamis, 25 Oktober 2022"
		STATUS	"Followup Program (KLAIM BUNGA INVESTASI (3/12))"
		ID	"107"
		KONFIRMASI	"Cair Semua"
		KODE_KONFIRMASI	"300"
		*/
		$data['ID']=isset($data['ID']) ? $data['ID']:"";
		if($data['ID']!=""){
		$this->db->query("call usp_tbl_spaj_surrender_book_conf(".$data['ID'].",'".$data['KODE_KONFIRMASI']."');");
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success'
			));
			exit;
		}
	}
	
	public function confirmation_detail(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		$id=base64_decode($this->input->post("data",true));
		$sql2="
		SELECT 
		ID,
NO_POLIS,
fnDateDayIndo(TGL_BAYAR) as TGL_BAYAR,
fnDateDayIndo(TGL_KONFIRMASI) as TGL_KONFIRMASI,
fnAmountIndo(JML_PREMI) as JML_PREMI,
KD_VALUTA,
NAMA_PEMPOL,
KODE_PR,
NAMA_PR,
CABANG,
CLUSTER,
PRODUK,
concat(PRODUK,' - ',PERIODE_CAIR) as PERIODE_CAIR,
STATUS,
STATUS_CODE,fnDateDayIndo(create_date) as TGL_INFO,
cast(TGL_BAYAR as date) as TGL_BAYAR_ORI
FROM `tbl_spaj_surrender_book` where kode_PR='".$agen['info']['agen_code']."' 
and STATUS_KONFIRMASI is null and cast(TGL_KONFIRMASI as date)>=cast(NOW() as date)
and ID='".$id."'
order by TGL_BAYAR asc
		";
		$query2=$this->db->query($sql2);
		$rp2=$query2->row_array();
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			'data'=>$rp2
			));
			exit;
		
	}
	
	public function change_password(){
		$agen=$this->input->post("agen",true);
		$data=$this->input->post("data",true);
		if($agen=="" or $data==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		$data=json_decode(base64_decode($data),true);
		
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		$data['old_password']=isset($data['old_password']) ? $data['old_password']:"";
		$data['new_password']=isset($data['new_password']) ? $data['new_password']:"";
		$data['retry_password']=isset($data['retry_password']) ? $data['retry_password']:"";
		
		if($agen['info']['agen_code']=="" or $data['old_password']=="" or $data['new_password']=="" or $data['retry_password']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		
		$query=$this->db->query("select count(1) as total from tbl_agen where username='".$agen['info']['agen_code']."' and password='".$data['new_password']."'");
		$rp=$query->row_array();
		$rp['total']=isset($rp['total']) ? $rp['total']:0;
		
		$uppercase = preg_match('@[A-Z]@', $data['new_password']);
		$lowercase = preg_match('@[a-z]@', $data['new_password']);
		$number    = preg_match('@[0-9]@', $data['new_password']);
		$specialChars = preg_match('@[^\w]@', $data['new_password']);
		
		if($rp['total']>0){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Passsword baru tidak boleh sama dengan password lama.'
			));
			exit;
		}else if($data['new_password']!=$data['retry_password']){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Kedua Passsword baru yang ada masukan tidak sama.'
			));
			exit;
		}else if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($data['new_password']) < 8){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Kata sandi harus setidaknya 8 karakter dan harus mencakup setidaknya satu huruf besar, satu angka, dan satu karakter khusus.'
			));
			exit;
		}
		
		$this->db->query("update tbl_agen set `password`='".$data['new_password']."' where username='".$agen['info']['agen_code']."'");
		
		echo json_encode(array(
			'error'=>0,
			'message'=>'Password baru berhasil di ganti'
			));
			exit;
		
	}
	
	public function cashbonus(){
		$agen=$this->input->post("agen",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		/*
		echo json_encode(array(
			'error'=>1,
			'message'=>'No record Found',
			'cashbonus'=>[],
			'payment'=>[]
			));
			exit;
		*/
		$periode=base64_decode($this->input->post("periode",true));
		$rows_cashbonus=array();
		$rows_payment=array();
		$this->conn = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_DWH_PROD", "sa","C4p1t4l.L1f3.2021");			
		
		$this->stmt=$this->conn->prepare("select c.NO_POLIS,c.NAMA_PEMPOL,
		case when c.NAMA_PRODUK like'%Asuransi Capital Proteksi Link%' then 'CPLI' 
		 when c.NAMA_PRODUK like'%CAPITAL PROTEKSI PLUS (IDR)%' then 'CPPI' 
		 when c.NAMA_PRODUK like'%CAPITAL PROTEKSI PLUS (USD)%' then 'CPPU' end
		as NAMA_PRODUK,
		c.PERIODE,
round(c.NILAI_CASHBONUSC,2) as NILAI_CASHBONUS,c.JUMLAH_PREMI,convert(varchar,d.TGL_MTI_AWAL,103) as TGL_MTI_AWAL
from dwh_master_cashbonus_ms c
left join dwh_master_dashboard d on d.NO_SPAJ=c.NO_SPAJ 
where PERIOD_PRODUKSI='$periode' and KODE_REFERAL='".$agen['info']['agen_code']."'");
		$this->stmt->execute();
		$rows_cashbonus=$this->stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$this->stmt=null;
		
		$this->stmt=$this->conn->prepare("select 
		Kode_Ref,
		Nama_Referal,
		Rekening_Atas_Nama,
		Nomor_Rekening,
		Nama_Bank,
		Nomor_NPWP,
		Cash_Bonus_IDR_Gross,
		Cash_Bonus_USD_Gross,
		TERHUTANG,
		FREELOOK_ATAU_SURRENDER_CASH_BONUS,
		FREELOOK_ATAU_SURRENDER_OR,
		TOTAL_CASH_BONUS,
		TOTAL_TERHUTANG_DAN_CLAWBACK,
		CASH_BONUS_25_PCT,
		TOTAL_CASH_BONUS_GROSS,
		DASAR_POTONG_PAJAK,
		convert(varchar,round(PERSENTASE_PAJAK_PPH*100,2)) as PERSENTASE_PAJAK_PPH,
		Pajak_Progresif_PPH,
		convert(varchar,round(PPN*100,2)) as PPN,
		NOMINAL_PPN,
		SISA_TERHUTANG,
		TOTAL_CASH_BONUS_NETT,
		ROUND_UP,
		CASH_BONUS_DIBAYARKAN,
		KETERANGAN,
		case right(PERIOD_PRODUKSI,2) 
		when '01' then 'Januari'
		when '02' then 'Februari'
		when '03' then 'Maret'
		when '04' then 'April'
		when '05' then 'Mei'
		when '06' then 'Juni'
		when '07' then 'Juli'
		when '08' then 'Agustus'
		when '09' then 'September'
		when '10' then 'Oktober'
		when '11' then 'November'
		else 'Desember' end+', '+left(PERIOD_PRODUKSI,4)
		as PERIOD_PRODUKSI
		from dwh_master_payment_ms where PERIOD_PRODUKSI='$periode' and Kode_Ref='".$agen['info']['agen_code']."'");
		$this->stmt->execute();
		$rows_payment=$this->stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if(count($rows_cashbonus)<=0){
			echo json_encode(array(
			'error'=>1,
			'message'=>'No record Found',
			'cashbonus'=>$rows_cashbonus,
			'payment'=>$rows_payment
			));
			exit;
		}
		
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			'cashbonus'=>$rows_cashbonus,
			'payment'=>$rows_payment
			));
			exit;
		
		
	}

	/**
	 * * Add By Yopi
	 * TODO: save detail data deviasi other
	 * TODO: get detail data deviasi
	*/

	function submit_deviasi_other_with_upload(){

		$data = $this->input->post("data",true);
		$agen = $this->input->post("agen",true);

		if($agen == "" or $data == ""){
			echo json_encode(array(
				'error'=>1,
				'message'=>'Invalid Access'
			));

			exit; 
		}

		$data = json_decode(base64_decode($data),true);
		$agen = json_decode(base64_decode($agen),true);

		if(!preg_match("/^([a-zA-Z'\,\. ]+)$/", $data['client_name'])){
			echo json_encode(array(
				'error'		=> 1,
				'message'	=> 'Nama pemegang polis tidak valid.',
				'info'		=> false
			));
			exit;
		}

		$year 		= date("Y");
		$month 		= date("F");
		$codeAgen 	= $agen['info']['agen_code'];
		$ticket_id	= explode("-",$agen['pengajuan']['no_ticket']);

		//get deviation ticket_id
		$noTicket = $this->getTicketDeviationOther($codeAgen);

		// print_r($noTicket);
		// dd($noTicket);

		//* Upload File
		// print_r(realpath(".")."/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[1]);
		$allowedExtension = array('jpg', 'jpeg','png','pdf');
		if(isset($_FILES['files']) && $_FILES['files']['error'] == 0){
			$extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);

			if(!in_array(strtolower($extension), $allowedExtension)){
				$message = array(
					'error'=>1,
					'message'=> 'Invalid Extension'
					// 'message'=> $doc_name.': Invalid Extension'
				);
			}else{
				if(!is_dir(realpath(".")."/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[1])){ // path upload masih harus disesuaikan 
					mkdir(realpath(".")."/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[1]);
				}

				$path="/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[1]."/deviation_document".".".strtolower($extension);
				if(file_exists(realpath(".").$path)){
					@unlink(realpath(".").$path);
				}
				
				if(move_uploaded_file($_FILES['files']['tmp_name'], realpath(".").$path)){

				} else {
					$message=array('error'=>1,'message'=> ': Failed Upload');
				}
			}
		
		}
		//* Upload File

		//get level mapping
		$query=$this->db->query("select * from tbl_agen_approval_level_bci where 
			level1='".$agen['info']['agen_code']."' or 
			level2='".$agen['info']['agen_code']."' or 
			level3='".$agen['info']['agen_code']."'
		");

		$approvalCode = $query->row_array();

		$insert = $this->db->query("
			INSERT INTO tbl_agen_pengajuan_deviasi_other
			(
				ticket_id,
				agen_code_request,
				agen_code_request_level,
				spaj_polis_code,
				client_name,
				remark,
				deviations,
				approval_1,
				approval_2,
				approval_3,
				approval_4,
				created_by,
				created_at
			)
			VALUES
			(
				'".$noTicket."',
				'".$codeAgen."',
				'".$agen['info']['agen_level']."',
				'".$data['polis_spaj_no']."',
				'".$data['client_name']."',
				'".addslashes($data['remark'])."',
				'".json_encode($data['type_deviation'])."',
				'".$approvalCode['level1']."',
				'".($approvalCode['level2'] == "" ? $approvalCode['level3'] : $approvalCode['level2'])."',
				'".($approvalCode['level3'] == "" ? $approvalCode['level4'] : $approvalCode['level3'])."',
				'".($approvalCode['level4'] == "" ? $approvalCode['level5'] : $approvalCode['level4'])."',
				'".$agen['info']['agen_code']."',
				NOW()
			)
		");

		$this->db->query("
			INSERT INTO tbl_agen_pengajuan_deviasi_hst(
				ID_deviasi, agen_code, agen_level, trans_type, remarks, created_at, create_by, source_type)
			SELECT
				id, agen_code_request, agen_code_request_level, 'Register', 'Pengajuan Baru', now(), agen_code_request, 'deviation_other' as source_type FROM tbl_agen_pengajuan_deviasi_other WHERE ticket_id = '".$noTicket."' AND spaj_polis_code = '".$data['polis_spaj_no']."'
		");

		echo json_encode(array(
			'error'		=> 0,
			'message'	=> 'success'
		));

		exit; 
	}

	function submit_deviasi_other(){
		$data = $this->input->post("data",true);
		$agen = $this->input->post("agen",true);

		if($agen == "" or $data == ""){
			echo json_encode(array(
				'error'=>1,
				'message'=>'Invalid Access'
			));

			exit; 
		}

		$data = json_decode(base64_decode($data),true);
		$agen = json_decode(base64_decode($agen),true);

		if(!preg_match("/^([a-zA-Z'\,\. ]+)$/", $data['client_name'])){
			echo json_encode(array(
				'error'		=> 1,
				'message'	=> 'Nama pemegang polis tidak valid.',
				'info'		=> false
			));
			exit;
		}

		//* Upload File
		$year 		= date("Y");
		$month 		= date("F");
		
		$codeAgen 	= $agen['info']['agen_code'];
		$agenLevel 	= $agen['info']['agen_level'];
		$bbcCode 	= $agen['info']['bbc_code'];
		$branchCode = $agen['info']['branch'];
		$noTicket 	= $this->getTicketDeviationOther($codeAgen);
		$ticket_id	= explode("-",$noTicket);
		$path 		= array();

		// print_r(realpath(".")."/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[1]);
		$allowedExtension = array('jpg', 'jpeg','png','pdf');
		if(isset($_FILES['files']) && $_FILES['files']['error'] == 0){
			$extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);

			if(!in_array(strtolower($extension), $allowedExtension)){
				$message = array(
					'error'=>1,
					'message'=> 'Invalid Extension'
					// 'message'=> $doc_name.': Invalid Extension'
				);
			} else {
				if(!is_dir(realpath(".")."/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[2])){
					mkdir(realpath(".")."/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[2],0777,true);
				}

				// $document = "/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[2]."/deviation_document_".$ticket_id[3].".".strtolower($extension);
				$document = "/helpdesk_doc/".$codeAgen."/".$year."/".$month."/".$ticket_id[2]."/deviation_document_".$ticket_id[3].".".strtolower($extension);
				if(file_exists(realpath(".").$document)){
					@unlink(realpath(".").$document);
				}
				
				if(move_uploaded_file($_FILES['files']['tmp_name'], realpath(".").$document)){
					$path[]['doc_1'] = $document;
				} else {
					$message=array('error'=>1,'message'=> ': Failed Upload');
				}
			}
		
		}
		//* Upload File

		//get PFA CODE
		$queryPfaCode = $this->db->query("
			SELECT
				(SELECT sicepot_agen_code FROM tbl_pfa WHERE pfa_code = tb.pfa_code) AS pfa_code
			FROM tbl_branch tb
			WHERE tb.branch_code = '".$branchCode."'
		");
		$pfaCode = $queryPfaCode->row_array();

		$isStatus = '1';
		if($agenLevel == 'BBC'){
			$isStatus = '2';
		}
		//get deviation ticket_id
		$noTicket = $this->getTicketDeviationOther($codeAgen);
		$insert = $this->db->query("
			INSERT INTO tbl_agen_pengajuan_deviasi_other
			(
				ticket_id,
				agen_code_request,
				agen_code_request_level,
				spaj_polis_code,
				client_name,
				remark,
				deviations,
				bbc_code,
				pfa_code,
				status,
				branch_code,
				document,
				created_by,
				created_at
			)
			VALUES
			(
				'".$noTicket."',
				'".$codeAgen."',
				'".$agen['info']['agen_level']."',
				'".$data['polis_spaj_no']."',
				'".$data['client_name']."',
				'".addslashes($data['remark'])."',
				'".json_encode($data['type_deviation'])."',
				'".$bbcCode."',
				'".$pfaCode['pfa_code']."',
				'".$isStatus."',
				'".$branchCode."',
				'".json_encode($path)."',
				'".$codeAgen."',
				NOW()
			)
		");

		//* Log History
		$this->db->query("
		INSERT INTO tbl_agen_pengajuan_deviasi_hst(
			ID_deviasi, agen_code, agen_level, trans_type, remarks, create_date, create_by, source_type)
			SELECT
			id, agen_code_request, agen_code_request_level, 'Register', 'Pengajuan Baru', now(), agen_code_request, 'deviation_other' as source_type FROM tbl_agen_pengajuan_deviasi_other WHERE ticket_id = '".$noTicket."' AND spaj_polis_code = '".$data['polis_spaj_no']."'
		");
		//* Log History

		echo json_encode(array(
			'error'		=> 0,
			'message'	=> 'success'
		));

		exit; 
	}

	function get_detail_other_deviation(){
		$data = $this->input->post("data",true);
		$agen = $this->input->post("agen",true);

		if($agen == "" or $data == ""){
			echo json_encode(array(
				'error'		=> 1,
				'message'	=> 'Invalid Access'
			));

			exit; 
		}

		$data = base64_decode($data);
		$agen = json_decode(base64_decode($agen),true);

		$sql = "SELECT 
					a.id, a.deviations, a.client_name, a.spaj_polis_code, a.remark, a.document
				FROM tbl_agen_pengajuan_deviasi_other a
				LEFT JOIN tbl_agen_pengajuan_deviasi_other_approval b ON b.deviation_other_id = a.id
				WHERE a.id = ".$data.";
		";

		$queryApproval = "
			SELECT
				a.remark, a.approval
			FROM tbl_agen_pengajuan_deviasi_other_approval a
			WHERE a.deviation_other_id = ".$data." GROUP BY a.approval, a.remark ORDER BY a.id ASC;
		";

		$queryApproval 	= $this->db->query($queryApproval);
		$resultApproval = $queryApproval->result_array();

		$query 	= $this->db->query($sql);
		$result = $query->row_array();

		echo json_encode(array(
			'error'			=> 0,
			'message'		=> 'success',
			'data'			=> $result,
			'data_approval'	=> $resultApproval
		));

		exit;

	}
	
	function approve_deviation_other(){
		$id = $this->input->post("id",true);
		$remark = $this->input->post("remark",true);
		$agen = $this->input->post("agen",true);

		if($agen == "" or $id == ""){
			echo json_encode(array(
				'error'		=> 1,
				'message'	=> 'Invalid Access'
			));

			exit; 
		}

		$id 	  = base64_decode($id);
		$remark   = base64_decode($remark);
		$agen 	  = json_decode(base64_decode($agen),true);
		$agenCode = $agen['info']['agen_code'];

		// if($agen['info']['agen_level'] == "BBC" && preg_match('/bbc/i', $agen['info']['bbc_code'])){
		// 	$update = $this->db->query("
		// 		UPDATE tbl_agen_pengajuan_deviasi_other_approval
		// 		SET 
		// 			approval_1		= '".$agenCode."',
		// 			approval_1_date = now(),
		// 			remark_1 		= '".$remark."'
		// 		WHERE deviation_other_id = ".$id."
		// 	");
		// }else if($agen['info']['agen_level'] == "DIRCLI"){
			
		// 	if($agenCode == "robin.winata"){
		// 		$update = $this->db->query("
		// 			UPDATE tbl_agen_pengajuan_deviasi_other_approval
		// 			SET 
		// 				approval_2_date = now(),
		// 				remark_2 		= '".$remark."'
		// 			WHERE deviation_other_id = ".$id."
		// 		");
		// 	} else if($agenCode == "jamaludin"){
		// 		$update = $this->db->query("
		// 			UPDATE tbl_agen_pengajuan_deviasi_other_approval
		// 			SET 
		// 				approval_3_date = now(),
		// 				remark_3 		= '".$remark."'
		// 			WHERE deviation_other_id = ".$id."
		// 		");
		// 	}

		// }else if(preg_match('/pfa/i', $agen['info']['bbc_code'])){
		// 	$update = $this->db->query("
		// 		UPDATE tbl_agen_pengajuan_deviasi_other_approval
		// 		SET 
		// 			approval_4		= '".$agenCode."',
		// 			approval_4_date = now(),
		// 			remark_4 		= '".$remark."'
		// 		WHERE deviation_other_id = ".$id."
		// 	");

		// }

		$isApproval = '';
		$isBbc 		= array('BBC 1','BBC 2','BBC 3','BBC 4','BBC 5','BBC 6','BBC 7','BBC 8','BBC 9','BBC 10','SEMARANG');
		if($agen['info']['agen_level'] == "BBC" && preg_match('/bbc/i', $agen['info']['bbc_code'])){
			$update = $this->db->query("
				UPDATE tbl_agen_pengajuan_deviasi_other
				SET 
					status			= '2',
					updated_at		= now()
				WHERE status = '1' AND id = '".$id."' 
			");
		} else if($agen['info']['agen_level'] == "BBH" && preg_match('/bbh/i', $agen['info']['bbc_code'])){
			$update = $this->db->query("
				UPDATE tbl_agen_pengajuan_deviasi_other
				SET 
					status			= '2',
					updated_at		= now()
				WHERE status = '1' AND id = '".$id."' 
			");

		} else if($agen['info']['agen_level'] == "DIRCLI" && $agen['info']['bbc_code'] == "DIRCLI1"){
			$update = $this->db->query("
				UPDATE tbl_agen_pengajuan_deviasi_other
				SET 
					status			= '3',
					updated_at		= now()
				WHERE status = '2' AND id = '".$id."'
			");

		} else if($agen['info']['agen_level'] == "DIRCLI" && $agen['info']['bbc_code'] == "DIRCLI2"){
			$update = $this->db->query("
				UPDATE tbl_agen_pengajuan_deviasi_other
				SET 
					status			= '4',
					updated_at		= now()
				WHERE status = '3' AND id = '".$id."'
			");
		} else {
			$update = $this->db->query("
				UPDATE tbl_agen_pengajuan_deviasi_other
				SET 
					status			= '5',
					updated_at		= now()
				WHERE status = '4' AND id = '".$id."'
			");
		}

		//* Approval History
		$this->db->query("
			INSERT INTO tbl_agen_pengajuan_deviasi_other_approval(
				deviation_other_id, approval, approval_date, remark, created_by, created_at)
			SELECT
				'".$id."', '".$agen['info']['agen_code']."', now(), '".$remark."', '".$agen['info']['agen_code']."', now()
		");
		//* Approval History

		$this->db->query("
			INSERT INTO tbl_agen_pengajuan_deviasi_hst(
				ID_deviasi, agen_code, agen_level, trans_type, remarks, create_date, create_by, source_type)
			SELECT
				id, '".$agen['info']['agen_code']."', '".$agen['info']['agen_level']."', 'Approve', 'Menyetujui', now(), '".$agen['info']['agen_code']."', 'deviation_other' as source_type FROM tbl_agen_pengajuan_deviasi_other WHERE id = '".$id."'
		");
		//* Log History

		echo json_encode(array(
			'error'		=> 0,
			'message'	=> 'success',
			'data'		=> $agen['info']
		));

		exit;
	}

	function reject_deviation_other(){
		$id = $this->input->post("id",true);
		$remark = $this->input->post("remark",true);
		$agen = $this->input->post("agen",true);

		if($agen == "" or $id == ""){
			echo json_encode(array(
				'error'		=> 1,
				'message'	=> 'Invalid Access'
			));

			exit; 
		}

		$id = base64_decode($id);
		$remark = base64_decode($remark);
		$agen = json_decode(base64_decode($agen),true);

		$update = $this->db->query("
			UPDATE tbl_agen_pengajuan_deviasi_other
			SET 
				status = '6',
				reject_by = '".$agen['info']['agen_code']."',
				reject_date = NOW(),
				updated_at = NOW(),
				reject_remark = '".$remark."'
			WHERE id = ".$id."
		");

		echo json_encode(array(
			'error'		=> 0,
			'message'	=> 'success',
			'data'		=> $agen['info']
		));

		exit;
	}

	function getTicketDeviationOther($agenCode){

		$date 		= date("Y-m-d"); 
		$newDate 	= explode('-',$date);
		$year  		= $newDate[0];
		$newMM 		= $newDate[1];

		//GET KODE MAX BY TICKET ID + SQUENCE
		$result = $this->db->query("SELECT created_at, LPAD(MAX(RIGHT(REPLACE(ticket_id, SUBSTR(ticket_id , -16, 9), ''),4))+1,4,'0000') AS maxval FROM tbl_agen_pengajuan_deviasi_other WHERE agen_code_request = '$agenCode' AND YEAR(created_at) = '$year' AND MONTH(created_at) = '$newMM' ")->row_array();

		// $result = $query->row_array();
		if(count($result) <= 0){
			$maxCode = '';
		} else {
			$maxCode = $result['maxval'];
		}

		if ($maxCode == '') {
				$okeCode = 'DV-'.$agenCode.'-'.date("y").$newMM.'-0001';
		}else {
			$transDate 	= explode('-',$result['created_at']);
			$transMM 	= $transDate[1];

			if ($transMM == $newMM) {//DALAM BULAN YANG SAMA
					$okeCode = 'DV-'.$agenCode.'-'.date("y").$newMM.'-'.$maxCode;
			}else {
				$okeCode = 'DV-'.$agenCode.'-'.date("y").$newMM.'-0001';
			}
		}

		return $okeCode;
	}

}
