<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Docsubmission extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	

	public function index()
	{
		$message=array('error'=>1,'message'=>'Invalid Request');
		
		if($_SERVER['REQUEST_METHOD']!=="POST"){
			$message=array('error'=>1,'message'=>'Invalid Request Method');
		}else{
			$cases=$this->input->post("cases",true);
			$cases=base64_decode($cases);
			$cases=json_decode($cases,true);
			if(!is_array($cases)){
			$message=array('error'=>1,'message'=>'Invalid information.');
			}else{
				$this->load->database();
				
				
				$trx_id=isset($cases['trx_id']) ? $cases['trx_id']:"";
				$id=isset($cases['id']) ? $cases['id']:"";
				$doc=isset($cases['doc']) ? $cases['doc']:"";
				$spaj_nmbr=isset($cases['spaj_nmbr']) ? $cases['spaj_nmbr']:"";
				$agen_code=isset($cases['agen_code']) ? $cases['agen_code']:"";
				
				$row=$this->db->query("select * from tbl_spaj where spaj_code='$spaj_nmbr'")->row_array();
				
				$ktp=strlen($this->input->post("ktp",true))<10 ? "":$this->input->post("ktp",true);
				$ktp2=strlen($this->input->post("ktp2",true))<10 ? "":$this->input->post("ktp2",true);
				
				$ktp3=strlen($this->input->post("ktp3",true))<10 ? "":$this->input->post("ktp3",true);
				$ktp4=strlen($this->input->post("ktp4",true))<10 ? "":$this->input->post("ktp4",true);
				
				$spaj=strlen($this->input->post("spaj",true))<10 ? "":$this->input->post("spaj",true);
				$spaj2=strlen($this->input->post("spaj2",true))<10 ? "":$this->input->post("spaj2",true);
				$ub=strlen($this->input->post("ub",true))<10 ? "":$this->input->post("ub",true);
				$pb=strlen($this->input->post("pb",true))<10 ? "":$this->input->post("pb",true);
				$ttd=strlen($this->input->post("ttd",true))<10 ? "":$this->input->post("ttd",true);
				$other=strlen($this->input->post("other",true))<10 ? "":$this->input->post("other",true);
				
				$list_doc="";
				if($ktp!=""){
					$data_ktp = base64_decode(str_replace("[removed]","",$ktp));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.IDENTITAS1_".date("Ymdhis").".png", $data_ktp);
					$list_doc="PEMPOL : IDENTITAS 1,";
				}
				if($ktp2!=""){
					$data_ktp2 = base64_decode(str_replace("[removed]","",$ktp2));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.IDENTITAS2_".date("Ymdhis").".png", $data_ktp2);
					$list_doc="PEMPOL : IDENTITAS 2,";
				}
				if($ktp3!=""){
					$data_ktp3 = base64_decode(str_replace("[removed]","",$ktp3));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.IDENTITAS3_".date("Ymdhis").".png", $data_ktp3);
					$list_doc="TERTANGGUNG : IDENTITAS 1,";
				}
				if($ktp4!=""){
					$data_ktp4 = base64_decode(str_replace("[removed]","",$ktp4));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.IDENTITAS4_".date("Ymdhis").".png", $data_ktp4);
					$list_doc="TERTANGGUNG : IDENTITAS 2,";
				}
				if($spaj!=""){
					$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.SPAJDOC1_".date("Ymdhis").".png", $data_spaj);$list_doc="SPAJ Hal 1,";			
				}
				if($spaj2!=""){
					$data_spaj2 = base64_decode(str_replace("[removed]","",$spaj2));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.SPAJDOC2_".date("Ymdhis").".png", $data_spaj2);$list_doc="SPAJ Hal 2,";			
				}	
				if($ub!=""){
					$data_ub = base64_decode(str_replace("[removed]","",$ub));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.FORMUPBESAR_".date("Ymdhis").".png", $data_ub);
					$list_doc="Form UP Besar,";
				}
				if($pb!=""){
					$data_pb = base64_decode(str_replace("[removed]","",$pb));
					file_put_contents(realpath(".")."/submission/".$doc."/REV.FORMPB_".date("Ymdhis").".png", $data_pb);
					$list_doc="Form PB,";
				}
				if($ttd!=""){
					$data_ttd = base64_decode(str_replace("[removed]","",$ttd));
					file_put_contents(realpath(".")."/submission/".$doc."/TANDA-TERIMA-POLIS_".date("Ymdhis").".png", $data_ttd);
					$list_doc="Form Tanda Terima Polis,";
					$this->db->query("update tbl_spaj set polis_received_dt=NOW() where ID='$id'");
				}
				if($other!=""){
					$data_other = base64_decode(str_replace("[removed]","",$other));
					file_put_contents(realpath(".")."/submission/".$doc."/DOKUMEN-LAINNYA_".date("Ymdhis").".png", $data_other);
					$list_doc="Dokumen Lainnya,";
				}
				
				
				try{
					if($ktp!="" or $ktp2!="" or $ktp3!="" or $ktp4!="" or $spaj!="" or $spaj2!="" or $ub!="" or $pb!="" or $ttd!="" or $other!=""){
						
						
						$this->db->query("call usp_spaj_status_history_ia($id,'201','PR Submit Dokumen Pending','PR Submit Dokumen Pending - ".rtrim($list_doc,',')."','$agen_code')");
						
						$subject="E-Clip Mobile PR Submit Dokumen Pending, SPAJ : ".strtoupper($spaj_nmbr)." Tanggal: ".date("Y-m-d H:i a");
							$message="<p>Terlampir Dokumen Pending SPAJ ".strtoupper($spaj_nmbr)." an. ".$row['nama_pp']." dari PR : ".$agen_code." - Dokumen Pending : ".rtrim($list_doc,',')."  telah di kirim, silahkan cek.</p>";
							$this->email_notification("marketing.support@capitallife.co.id","paulus.yunior@capitallife.co.id",$subject,$message,"Marketing Support");
						
					}
				}catch(Exception $e){
					
				}
			
			}
		}
		echo json_encode($message);
	}
	
	public function email(){
		$subject="E-Clip Mobile PR Submit Dokumen Pending, SPAJ :- Tanggal: ".date("Y-m-d H:i a");
							$message="<p>Terlampir Dokumen Pending SPAJ</p>";
							$this->email_notification("paulus.yunior@capitallife.co.id","paulus.yunior@capitallife.co.id",$subject,$message,"Marketing Support");
	}
	
	private function email_notification($email_to,$email_cc,$subject,$message,$todiv){
		$fields=array(
		'to'=>$email_to,
		'cc'=>$email_cc,
		'subject'=>$subject,
		'message'=>'Dear Bapak/Ibu '.$todiv.',<br><br>'.$message.'
		<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><br><br><p>Regards,<br>PT Capital Life Indonesia.</p>'
		);
		$fields_string="";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, "http://www.capitallife.co.id/reminder/");
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
	}
}