<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Bangkok");
class Bookingsubmission extends CI_Controller {
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
	
	public function upload(){
		
		$message=array('error'=>1,'message'=>'Invalid Request');
		
		if($_SERVER['REQUEST_METHOD']!=="POST"){
			$message=array('error'=>1,'message'=>'Invalid Request Method');
		}else{

			$data=$this->input->post("data",true);
			$data=json_decode(base64_decode($data),true);
			
			$doc_type=isset($data['doc_type']) ? $data['doc_type']:"";
			$doc_name=isset($data['doc_name']) ? $data['doc_name']:"";
			$agen_code=isset($data['agen_code']) ? $data['agen_code']:"";
			$ticket_id=isset($data['ticket_id']) ? $data['ticket_id']:"";
			
			
			
			$allowed = array('jpg', 'jpeg','png','pdf');
			if(isset($_FILES['files']) && $_FILES['files']['error'] == 0){
				$extension = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);

				if(!in_array(strtolower($extension), $allowed)){
					$message=array('error'=>1,'message'=>$doc_name.': Invalid Extension');
				}else{
				
					
				
					if(!is_dir(realpath(".")."/booking_doc/".$ticket_id)){
						mkdir(realpath(".")."/booking_doc/".$ticket_id);
					}
					
					$path="/booking_doc/".$ticket_id."/".$doc_type.".".strtolower($extension);
					if(file_exists(realpath(".").$path)){
						@unlink(realpath(".").$path);
					}
					
					if(move_uploaded_file($_FILES['files']['tmp_name'], realpath(".").$path)){
						$sql="";
						$this->load->database();
						$this->db->query("delete from tbl_agen_pengajuan_bisnis_doc where ticket_id=? and doc_type=?",array($ticket_id,$doc_type));
						
						
						$str="insert into tbl_agen_pengajuan_bisnis_doc(ticket_id,doc_type,doc_path,create_date,create_by) values(?,?,?,NOW(),?);";
						$this->db->query($str,array($ticket_id,$doc_type,$path,$agen_code));
						
						$this->pass_backoffice($ticket_id,$doc_type,realpath(".").$path);
						
						$message=array('error'=>0,'message'=>$doc_name.': File berhasil di upload');
					}else{
						$message=array('error'=>1,'message'=>$doc_name.': Failed Upload');
					}
				}
			
			}
		}
		echo json_encode($message);
		
	}
	
	private function pass_backoffice($ticket_id,$doc_type,$path){
		if(in_array($doc_type,array('104'))){
			$this->load->database();
			$query=$this->db->query("select b.spaj_code,s.ID,s.agen_code,date_format(effective_dt,'%Y%m%d') as today from tbl_agen_pengajuan_bisnis b 
			join tbl_spaj s on s.spaj_code=b.spaj_code
			where b.ticket_id='".$ticket_id."' and s.issued_dt is null");
			$row=$query->row_array();
			if(count($row)>1){
				$doc_code='D005';
				$doc_code_name="Bukti Transfer";
				
				$extension = pathinfo($path, PATHINFO_EXTENSION);
				$path_new="/submission/".$row['today']."/".$row['spaj_code']."/".$doc_code.".".strtolower($extension);
				if(file_exists(realpath(".").$path_new)){
					@rename($path_new,$path_new.".".time());
					//@unlink(realpath(".").$path_new);
				}
				
				//clean old PB document
				$this->db->query("delete from tbl_spaj_doc where spaj_code=? and doc_type_nmbr=?",array($row['spaj_code'],$doc_code));
				
				$str="insert into tbl_spaj_doc(spaj_code,doc_type_nmbr,path,create_date,create_by) values(?,?,?,NOW(),?);";
				$this->db->query($str,array($row['spaj_code'],$doc_code,$path_new,$row['agen_code']));
				
				$sql="call usp_spaj_status_history_ia('".$row['ID']."','201','Submit Dokumen Pending','".$doc_code_name."','".$row['agen_code']."')";
				$this->db->query($sql);
				copy($path, realpath(".").$path_new);
			}
			
		}
	}
	
	public function ticket_detail(){
	
		$agen=$this->input->post("agen",true);
		$data=$this->input->post("data",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		if($data!=""){
			$data=json_decode(base64_decode($data),true);
		}
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		$data['ticket_id']=isset($data['ticket_id']) ? $data['ticket_id']:"";
		
		
		
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		if($data['ticket_id']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Parameter'
			));
			exit;
		}
		
		
		
		
		$sql2="
		select 
ticket_id as no_ticket,
            create_date as tanggal,
            spaj_code as no_spaj,
            client_name as nama_pempol,
            client_insured_name as nama_tertanggung,
            product_name as produk,
            tanggal_masuk as tanggal_masuk,
            invesment_period as tenor,
            fnAmountIndo(premium) as premi,
            alasan_pengajuan as alasan_pengajuan,
            no_slip as no_slip,
             DATE_FORMAT(dob_ctu,'%Y-%m-%d') as dob_ctu,
            jenis_nasabah as jenis_nasabah,
            konfirmasi_pakai_data_cli as konfirmasi_pakai_data_cli
from tbl_agen_pengajuan_bisnis
where
ticket_id='".$data['ticket_id']."'
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
	
	public function notification(){
		$agen=$this->input->post("agen",true);
		$data=$this->input->post("data",true);
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access'
			));
			exit; 
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		if($data!=""){
			$data=json_decode(base64_decode($data),true);
		}
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		
		
		
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Request'
			));
			exit;
		}
		
		$data['nama_pp']=isset($data['nama_pp']) ? $data['nama_pp']:"";
		$data['nama_tt']=isset($data['nama_tt']) ? $data['nama_tt']:"";
		$data['no_spaj']=isset($data['no_spaj']) ? $data['no_spaj']:"";
		$data['tgl_awal']=isset($data['tgl_awal']) ? $data['tgl_awal']:"";
		$data['tgl_akhir']=isset($data['tgl_akhir']) ? $data['tgl_akhir']:"";
		
		$wh='';
		if($data['nama_pp']!="" and strlen(trim($data['nama_pp']))>3){
			$wh.=" and d.client_name like'%".$data['nama_pp']."%'";
		}
		if($data['nama_tt']!="" and strlen(trim($data['nama_tt']))>3){
			$wh.=" and d.client_insured_name like'%".$data['nama_tt']."%'";
		}
		if($data['no_spaj']!="" and strlen(trim($data['no_spaj']))>3){
			$wh.=" and d.spaj_code like'%".$data['no_spaj']."%'";
		}
		if($data['tgl_awal']!="" and $data['tgl_akhir']!=""){
			$wh.=" and (cast(d.tanggal_masuk as date) between '".$data['tgl_awal']."' and '".$data['tgl_akhir']."')";
		}else{
			$wh.=" and cast(d.create_date as date)=cast(now() as date)";
		}
		
		
		$sql2="
		select 
d.ID,ticket_id,a.agen_name,d.spaj_code,d.client_name,d.product_name,d.create_date,
#case when ss.spaj_code is null then 'On-going' else 'Closed' end as status_pengajuan,
case when ifnull((select status_type_nmbr from tbl_spaj_history where spaj_code=d.spaj_code  order by status_dt desc limit 1),'')>210 then 'Closed' 
when coalesce(d.reject_status,0)=1 then 'Reject'
else 'On-going' end as status_pengajuan,
concat(fnAmountIndo(d.premium),' (',d.product_name,' (',d.invesment_period,'))') as premium,fnDateIndo(d.tanggal_masuk) as tanggal_masuk,
d.no_slip,
case when coalesce(d.dob_ctu,'1900-01-01')='1900-01-01' then '-' else fnDateIndo(d.dob_ctu) end as dob_ctu_indo,
d.jenis_nasabah,
d.konfirmasi_pakai_data_cli,
d.alasan_pengajuan,
d.client_insured_name

from tbl_agen_pengajuan_bisnis d 
join tbl_agen a on a.agen_code=d.agen_code_request
left join tbl_spaj ss on ss.spaj_code=d.spaj_code
where coalesce(d.is_submited,0)=1 and agen_code_request='".$agen['info']['agen_code']."'  ".$wh."
order by d.create_date desc 
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
	
	public function update_pengajuan(){
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
		$data['no_slip']=isset($data['no_slip']) ? $data['no_slip']:"";
		$data['ticket_id']=isset($data['ticket_id']) ? $data['ticket_id']:"";
		//$data['no_spaj']=="" or 
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Pengajuan',
			'info'=>false
			));
			exit;
		}
		/*
		if($data['no_slip']=="" or $data['ticket_id']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Parameter',
			'info'=>false
			));
			exit;
		}*/
		
		$this->db->query("update tbl_agen_pengajuan_bisnis set 
			no_slip='".$data['no_slip']."',
			last_change_date=now(),
			last_change_by='".$agen['info']['agen_code']."',
			is_submited_pfa_date=now(),
			is_submited_pfa=1
			where ticket_id='".$data['ticket_id']."' and agen_code_request='".$agen['info']['agen_code']."'
			");
			
		$this->db->query("update tbl_spaj set payment_flg=1,payment_date=NOW(),payment_remarks='".$data['no_slip']."' 
		where spaj_code in(select spaj_code from tbl_agen_pengajuan_bisnis where ticket_id='".$data['ticket_id']."' )");	
		
		
		
		
		echo json_encode(array(
			'error'=>0,
			'message'=>'Success Update pengajuan'
			));
			exit; 
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
		//$data['no_spaj']=="" or 
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Pengajuan',
			'info'=>false
			));
			exit;
		}
		
		
		
		$premi=$data['premi'];
		if(substr_count($data['premi'],",")>0){
			$premi=str_replace(",","",$data['premi']);
		}else if(substr_count($data['premi'],".")>1){
			$premi=str_replace(",",".",str_replace(".","",$data['premi']));
		}else{
			$premi=$premi;
		}
		
		if(!preg_match("/^([a-zA-Z'\,\.\/ ]+)$/",$data['nama_pempol']) or !preg_match("/^([a-zA-Z'\,\. ]+)$/",$data['nama_tertanggung'])){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Nama pemegang polis atau Nama Tertanggung tidak valid.',
			'info'=>false
			));
			exit;
		}
		//product_name CPP IDR
		if($data['produk']=="CPP-IDR" and $premi<10000000){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Premi yang anad masukan salah silahkan di cek kembali. min: Rp. 10Jt',
			'info'=>false
			));
			exit;
		}
		
		//product_name CPP USD
		if($data['produk']=="CPP-USD" and $premi<10000){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Premi yang anad masukan salah silahkan di cek kembali, min: 1000 USD.',
			'info'=>false
			));
			exit;
		}
		
		//check pb dan no slip
		$rpb=$this->db->query("select count(1) as total from tbl_agen_pengajuan_bisnis_doc where doc_type='104' and ticket_id='".$data['no_ticket']."'")->row_array();
		if(($rpb['total']>0 and $data['no_slip']=="") or ($rpb['total']<0 and $data['no_slip']!="")){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Mohon lengkapi No slip dan bukti PB.',
			'info'=>false
			));
			exit;
		}
		
		
		$this->db->query("update tbl_agen_pengajuan_bisnis set 
			spaj_code='".$data['no_spaj']."',
			client_name='".$data['nama_pempol']."',
			client_insured_name='".$data['nama_tertanggung']."',
			product_name='".$data['produk']."',
			tanggal_masuk='".$data['tanggal_masuk']."',
			invesment_period='".$data['tenor']."',
			premium='".$premi."',
			alasan_pengajuan='".$data['alasan_pengajuan']."',
			
			no_slip='".$data['no_slip']."',
			dob_ctu='".$data['dob_ctu']."',
			jenis_nasabah='".$data['jenis_nasabah']."',
			konfirmasi_pakai_data_cli='".$data['konfirmasi_pakai_data_cli']."',
			
			is_submited=1,
			create_date=now(),
			create_by='".$agen['info']['agen_code']."',
			last_change_date=now(),
			last_change_by='".$agen['info']['agen_code']."'
			where ticket_id='".$data['no_ticket']."' and agen_code_request='".$agen['info']['agen_code']."'
			");
		
		
		
		
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
			end as agen_level from tbl_agen where username='".$agen['info']['agen_code']."' and status_type='A'");
		$row=$login->row_array();
		
		$this->db->reconnect();
		$pengajuan=$this->db->query("call usp_generate_agen_submission_ticket('".$row['agen_code']."')");
		$rp=$pengajuan->row_array();	
		
		$this->db->reconnect();
		$docq=$this->db->query("select doc_type,doc_name,coalesce(is_mandatory,0) as is_mandatory from tbl_agen_pengajuan_bisnis_doc_type where is_active=1 order by sort_order asc");
		$rdocq=$docq->result_array();	
		
		echo json_encode(array(
				'error'=>0,
				'message'=>'Success Login',
				'info'=>$row,
				'pengajuan'=>$rp,
				'doc'=>$rdocq
				));
		exit;		
	}
}