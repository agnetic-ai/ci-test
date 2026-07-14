<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Preview extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	public function demodebug(){
		exit;
		$product_code="CPP";
		$uuid="20180101";
		$trx_id="20180101";
		$agen_code="AG001";
		$no_spaj="SPAJ01";
		$currency="IDR";
		$nama_pp="Paulus";
		$nama_tt="Paulus";
		$tanggal_input="2018-01-01";
		$status_terakhir="2018-01-01";
		$uuid=$uuid."_".$product_code;
		
		$spaj_json=file_get_contents(realpath(".")."/preview/20180518/21391165_AG001_CPP/SPAJ_JSON_20180518054630.json");
		$parse_json=json_decode($spaj_json,true);
		//result parse json
		
		if($product_code=="CPP"){
			$birth_dt=$this->date_indo2sys($parse_json['cpp_dbo']);
			$age=explode(" ",$parse_json['cpp_age']);
			$age=$age[0];
			$effective_dt=$this->date_indo2sys($parse_json['cpp_tgl_asu']);
			$expired_dt=$this->date_indo2sys($parse_json['cpp_akhir_asu']);
			$currency=$parse_json['cpp_currency'];
			
			$premium_amt=explode(" ",$parse_json['cpp_premi']); 
			$premium_amt=$premium_amt[0];
			$premium_amt=str_replace(".","",$premium_amt);
			$premium_amt=str_replace(",",".",$premium_amt);
			
			$si_amt=explode(" ",$parse_json['cpp_up']);
			$si_amt=$si_amt[0];
			$si_amt=str_replace(".","",$si_amt);
			$si_amt=str_replace(",",".",$si_amt);
			
			$month_of_tenure=explode(" ",$parse_json['cpp_periode']);
			$month_of_tenure=$month_of_tenure[0];
			$bungan=explode(" ",$parse_json['cpp_mti_pa']);
			$bungan=str_replace(",",".",$bungan[0]);
			
		}elseif($product_code=="CPL"){
			
			$birth_dt=$this->date_indo2sys($parse_json['Tanggal_Lahir']);
			$age=explode(" ",$parse_json['Usia_Tertanggung']);
			$age=$age[0];
			$effective_dt=$this->date_indo2sys($parse_json['Tanggal_Mulai_Pertanggungan']);
			$expired_dt=$this->date_indo2sys($parse_json['Tanggal_Jatuh_Tempo_MTI']);
			$currency=$parse_json['Mata_Uang'];
			
			$premium_amt=explode(" ",$parse_json['Premi']); 
			$premium_amt=$premium_amt[0];
			$premium_amt=str_replace(".","",$premium_amt);
			$premium_amt=str_replace(",",".",$premium_amt);
			
			$si_amt=explode(" ",$parse_json['Uang_Pertanggungan']);
			$si_amt=$si_amt[0];
			$si_amt=str_replace(".","",$si_amt);
			$si_amt=str_replace(",",".",$si_amt);
			
			$month_of_tenure=explode(" ",$parse_json['Masa_Target_Investasi']);
			$month_of_tenure=$month_of_tenure[0];
			$bungan=explode(" ",$parse_json['Tingkat_Target_Investasi']);
			$bungan=str_replace(",",".",$bungan[0]);
		}
		
		$cart=$this->input->post("cart",true);
		$sign_nasabah=strlen($this->input->post("sign_nasabah",true))<10 ? "":$this->input->post("sign_nasabah",true);
		$sign_agen=strlen($this->input->post("sign_agen",true))<10 ? "":$this->input->post("sign_agen",true);
		
		$cart=strlen($this->input->post("cart",true))<10 ? "":$this->input->post("cart",true);
		$ktp=strlen($this->input->post("ktp",true))<10 ? "":$this->input->post("ktp",true);
		$spaj=strlen($this->input->post("spaj",true))<10 ? "":$this->input->post("spaj",true);
		$spaj2=strlen($this->input->post("spaj2",true))<10 ? "":$this->input->post("spaj2",true);
		$spaj3=strlen($this->input->post("spaj3",true))<10 ? "":$this->input->post("spaj3",true);
		$spaj4=strlen($this->input->post("spaj4",true))<10 ? "":$this->input->post("spaj4",true);
		$spaj5=strlen($this->input->post("spaj5",true))<10 ? "":$this->input->post("spaj5",true);
		
		$ilustrasi=strlen($this->input->post("ilustrasi",true))<10 ? "":$this->input->post("ilustrasi",true);
		$ub=strlen($this->input->post("ub",true))<10 ? "":$this->input->post("ub",true);
		$pb=strlen($this->input->post("pb",true))<10 ? "":$this->input->post("pb",true);
		
		$today=date("Ymd");
		
		if(!is_dir(realpath(".")."/preview/".$today)){
			mkdir(realpath(".")."/preview/".$today);
		}
		if(!is_dir(realpath(".")."/preview/".$today."/".$uuid)){
			mkdir(realpath(".")."/preview/".$today."/".$uuid);
		}
		if($spaj_json!=""){
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SPAJ_JSON_".date("Ymdhis").".json", $spaj_json);
		}
		if($sign_nasabah!=""){
			$data_snasabah = base64_decode(str_replace("[removed]","",$sign_nasabah));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SignNasabah_".date("Ymdhis").".png", $data_snasabah);
		}
		if($sign_agen!=""){
			$data_sagen = base64_decode(str_replace("[removed]","",$sign_agen));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SignAgen_".date("Ymdhis").".png", $data_sagen);
		}
		if($ktp!=""){
			$data_ktp = base64_decode(str_replace("[removed]","",$ktp));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/KTP_".date("Ymdhis").".png", $data_ktp);
		}
		if($spaj!=""){
			$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SPAJ_".date("Ymdhis").".png", $data_spaj);			
		}
		if($spaj2!=""){
			$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
			file_put_contents(realpath(".")."/submission/".$today."/".$uuid."/SPAJDOC2_".date("Ymdhis").".png", $data_spaj);			
		}
		if($spaj3!=""){
			$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
			file_put_contents(realpath(".")."/submission/".$today."/".$uuid."/SPAJDOC3_".date("Ymdhis").".png", $data_spaj);			
		}
		if($spaj4!=""){
			$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
			file_put_contents(realpath(".")."/submission/".$today."/".$uuid."/SPAJDOC4_".date("Ymdhis").".png", $data_spaj);			
		}
		if($spaj5!=""){
			$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
			file_put_contents(realpath(".")."/submission/".$today."/".$uuid."/SPAJDOC5_".date("Ymdhis").".png", $data_spaj);			
		}
		if($cart!=""){
			$data_cart = base64_decode(str_replace("[removed]","",$cart));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/Cart_".date("Ymdhis").".png", $data_cart);
		}
		
		if($ilustrasi!=""){
			$data_ilustrasi = base64_decode(str_replace("[removed]","",$ilustrasi));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/ILUSTRASI_".date("Ymdhis").".png", $data_ilustrasi);
		}
		if($ub!=""){
			$data_ub = base64_decode(str_replace("[removed]","",$ub));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/UP_BESAR_".date("Ymdhis").".png", $data_ub);
		}
		if($pb!=""){
			$data_pb = base64_decode(str_replace("[removed]","",$pb));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/DOK_PB_".date("Ymdhis").".png", $data_pb);
		}
		
		try{
			$this->load->database();
			
			$str="INSERT INTO `tbl_sil`( `trx_id`, `spaj_code`, `product_code`, `agen_code`, `nama_pp`,`nama_tt`, `birth_dt`, `age`, `effective_dt`, `expired_dt`, `currency`, `premium_amt`, `si_amt`, `month_of_tenure`, `bunga`, `raw_data`, `last_change_dt`, `last_change_by`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$query=$this->db->query($str,array($uuid,$no_spaj,$product_code,$agen_code,$nama_pp,$nama_tt,$birth_dt,$age, $effective_dt, $expired_dt, $currency, $premium_amt, $si_amt, $month_of_tenure,$bungan, 0, date("Y-m-d H:i:s"), $agen_code.' - WS'));
			
			/*
			$str="INSERT INTO `tbl_sil`( `trx_id`, `spaj_code`, `product_code`, `agen_code`, `nama_pp`,`nama_tt`, `birth_dt`, `age`, `effective_dt`, `expired_dt`, `currency`, `premium_amt`, `si_amt`, `month_of_tenure`, `bunga`, `raw_data`, `last_change_dt`, `last_change_by`) 
			VALUES('$uuid','$no_spaj','$product_code','$agen_code','$nama_pp','$nama_tt','$birth_dt','$age', '$effective_dt', '$expired_dt', '$currency', 
			'$premium_amt', '$si_amt', '$month_of_tenure','$bungan', 0, NOW(), '$agen_code')";
			$query=$this->db->query($str);
			*/
			$message=array('error'=>0,'message'=>'Succes save your information.');
		}catch(Exception $e){
			$message=array('error'=>0,'message'=>'Succes Generate Output but fail save information. cause of: '.$e->getMessage());
		}
		
		
		
		echo json_encode($message);
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
		$product_code=$cases['product_code'];
		$uuid=$cases['uuid'];
		$trx_id=$cases['trx_id'];
		$agen_code=$cases['agen_code'];
		$no_spaj=$cases['no_spaj'];
		$currency=$cases['currency'];
		$nama_pp=$cases['nama_pp'];
		$nama_tt=$cases['nama_tt'];
		$tanggal_input=$cases['tanggal_input'];
		$status_terakhir=$cases['status_terakhir'];
		$uuid=$uuid."_".$product_code;
		
		$spaj_json=$this->input->post("data",true);
		$parse_json=json_decode($spaj_json,true);
		//result parse json
		
		if($product_code=="CPP"){
			$birth_dt=$this->date_indo2sys($parse_json['cpp_dbo']);
			$age=explode(" ",$parse_json['cpp_age']);
			$age=$age[0];
			$effective_dt=$this->date_indo2sys($parse_json['cpp_tgl_asu']);
			$expired_dt=$this->date_indo2sys($parse_json['cpp_akhir_asu']);
			$currency=$parse_json['cpp_currency'];
			
			$premium_amt=explode(" ",$parse_json['cpp_premi']); 
			$premium_amt=$premium_amt[0];
			$premium_amt=str_replace(".","",$premium_amt);
			$premium_amt=str_replace(",",".",$premium_amt);
			
			$si_amt=explode(" ",$parse_json['cpp_up']);
			$si_amt=$si_amt[0];
			$si_amt=str_replace(".","",$si_amt);
			$si_amt=str_replace(",",".",$si_amt);
			$parse_json['cpp_periode']=isset($parse_json['cpp_periode']) ? $parse_json['cpp_periode']:"";
			$month_of_tenure=explode(" ",$parse_json['cpp_periode']);
			$month_of_tenure=$month_of_tenure[0];
			$bungan=explode(" ",$parse_json['cpp_mti_pa']);
			$bungan=str_replace(",",".",$bungan[0]);
			
		}elseif($product_code=="CPL"){
			
			$birth_dt=$this->date_indo2sys($parse_json['Tanggal_Lahir']);
			$age=explode(" ",$parse_json['Usia_Tertanggung']);
			$age=$age[0];
			$effective_dt=$this->date_indo2sys($parse_json['Tanggal_Mulai_Pertanggungan']);
			$expired_dt=$this->date_indo2sys($parse_json['Tanggal_Jatuh_Tempo_MTI']);
			$currency=$parse_json['Mata_Uang'];
			
			$premium_amt=explode(" ",$parse_json['Premi']); 
			$premium_amt=$premium_amt[0];
			$premium_amt=str_replace(".","",$premium_amt);
			$premium_amt=str_replace(",",".",$premium_amt);
			
			$si_amt=explode(" ",$parse_json['Uang_Pertanggungan']);
			$si_amt=$si_amt[0];
			$si_amt=str_replace(".","",$si_amt);
			$si_amt=str_replace(",",".",$si_amt);
			
			$month_of_tenure=explode(" ",$parse_json['Masa_Target_Investasi']);
			$month_of_tenure=$month_of_tenure[0];
			$bungan=explode(" ",$parse_json['Tingkat_Target_Investasi']);
			$bungan=str_replace(",",".",$bungan[0]);
		}
		
		$cart=$this->input->post("cart",true);
		$sign_nasabah=strlen($this->input->post("sign_nasabah",true))<10 ? "":$this->input->post("sign_nasabah",true);
		$sign_agen=strlen($this->input->post("sign_agen",true))<10 ? "":$this->input->post("sign_agen",true);
		
		$cart=strlen($this->input->post("cart",true))<10 ? "":$this->input->post("cart",true);
		$ktp=strlen($this->input->post("ktp",true))<10 ? "":$this->input->post("ktp",true);
		$spaj=strlen($this->input->post("spaj",true))<10 ? "":$this->input->post("spaj",true);
		
		$ilustrasi=strlen($this->input->post("ilustrasi",true))<10 ? "":$this->input->post("ilustrasi",true);
		$ub=strlen($this->input->post("ub",true))<10 ? "":$this->input->post("ub",true);
		$pb=strlen($this->input->post("pb",true))<10 ? "":$this->input->post("pb",true);
		
		$today=date("Ymd");
		
		if(!is_dir(realpath(".")."/preview/".$today)){
			mkdir(realpath(".")."/preview/".$today);
		}
		if(!is_dir(realpath(".")."/preview/".$today."/".$uuid)){
			mkdir(realpath(".")."/preview/".$today."/".$uuid);
		}
		if($spaj_json!=""){
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SPAJ_JSON_".date("Ymdhis").".json", $spaj_json);
		}
		if($sign_nasabah!=""){
			$data_snasabah = base64_decode(str_replace("[removed]","",$sign_nasabah));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SignNasabah_".date("Ymdhis").".png", $data_snasabah);
		}
		if($sign_agen!=""){
			$data_sagen = base64_decode(str_replace("[removed]","",$sign_agen));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SignAgen_".date("Ymdhis").".png", $data_sagen);
		}
		if($ktp!=""){
			$data_ktp = base64_decode(str_replace("[removed]","",$ktp));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/KTP_".date("Ymdhis").".png", $data_ktp);
		}
		if($spaj!=""){
			$data_spaj = base64_decode(str_replace("[removed]","",$spaj));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/SPAJ_".date("Ymdhis").".png", $data_spaj);			
		}
		if($cart!=""){
			$data_cart = base64_decode(str_replace("[removed]","",$cart));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/Cart_".date("Ymdhis").".png", $data_cart);
		}
		
		if($ilustrasi!=""){
			$data_ilustrasi = base64_decode(str_replace("[removed]","",$ilustrasi));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/ILUSTRASI_".date("Ymdhis").".png", $data_ilustrasi);
		}
		if($ub!=""){
			$data_ub = base64_decode(str_replace("[removed]","",$ub));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/UP_BESAR_".date("Ymdhis").".png", $data_ub);
		}
		if($pb!=""){
			$data_pb = base64_decode(str_replace("[removed]","",$pb));
			file_put_contents(realpath(".")."/preview/".$today."/".$uuid."/DOK_PB_".date("Ymdhis").".png", $data_pb);
		}
		
			try{
				$this->load->database();
				$str="INSERT INTO `tbl_sil`( `trx_id`, `spaj_code`, `product_code`, `agen_code`, `nama_pp`,`nama_tt`, `birth_dt`, `age`, `effective_dt`, `expired_dt`, `currency`, `premium_amt`, `si_amt`, `month_of_tenure`, `bunga`, `raw_data`, `last_change_dt`, `last_change_by`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$query=$this->db->query($str,array($uuid,strtoupper($no_spaj),$product_code,$agen_code,strtoupper($nama_pp),strtoupper($nama_tt),$birth_dt,$age, $effective_dt, $expired_dt, $currency, $premium_amt, $si_amt, $month_of_tenure,$bungan, 0, date("Y-m-d H:i:s"), $agen_code.' - WS'));
				$message=array('error'=>0,'message'=>'Succes save your information.');
			}catch(Exception $e){
				$message=array('error'=>0,'message'=>'Succes Generate Output but fail save information. cause of: '.$e->getMessage());
			}
			
		}
		}
		echo json_encode($message);
	}
	
	private function date_indo2sys($str){
		$arr_m=array(
		'Januari'=>'01',
		'Februari'=>'02',
		'Maret'=>'03',
		'April'=>'04',
		'Mei'=>'05',
		'Juni'=>'06',
		'Juli'=>'07',
		'Agustus'=>'08',
		'September'=>'09',
		'Oktober'=>'10',
		'November'=>'11',
		'Desember'=>'12'
		);
		$arr=explode(" ",$str);
		$d=$arr[0];
		$m=$arr_m[$arr[1]];
		$y=$arr[2];
		return $y.'-'.$m.'-'.$d; 
	}
}
