<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fillrekening extends CI_Controller {

	
	public function index()
	{
		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		effective_dt between '2022-01-01' and '2022-03-04'
		");
		//effective_dt between '2021-01-01' and '2021-10-31'
		$rows=$query->result_array();
		
		$data=array();
		$bank_name="";
		$branch_name="";
		$account_holder_name="";
		$account_holder_number="";
		$company_name="";

		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="DATA";
			$data=json_decode($this->other_doc($q,$type),true);
			
			//echo"<pre>";
			//print_r($data);
			//exit;
			
			$bank_name=$data[67]['value'];
			$branch_name=$data[68]['value'];
			$account_holder_name=str_replace("'","`",$data[69]['value']);
			$account_holder_number=$data[70]['value'];
			$company_name=$data[21]['value'];
			
			//$this->db->query("delete from tbl_spaj_rekening where spaj_code='".$rs['spaj_code']."'");
			//$this->db->query("insert tbl_spaj_rekening select '".$rs['spaj_code']."','$bank_name','$branch_name','$account_holder_name','$account_holder_number'");
			
			$this->db->query("delete from tbl_spaj_client_company where spaj_code='".$rs['spaj_code']."'");
			$this->db->query("insert tbl_spaj_client_company select '".$rs['spaj_code']."','".$company_name."'");

		}
		$data=array();
		$bank_name="";
		$branch_name="";
		$account_holder_name="";
		$account_holder_number="";		
		
	}

	public function byspaj()
	{
		$this->load->database();
		$spaj=$this->input->post("spaj");
		
		if($spaj==""){
			exit;
		}

		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code='".$spaj."'
		");
		$rows=$query->result_array();
		
		$data=array();
		$bank_name="";
		$branch_name="";
		$account_holder_name="";
		$account_holder_number="";
		$company_name="";

		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="DATA";
			$data=json_decode($this->other_doc($q,$type),true);

			//print_r($data);
			/*
			if($data[67]['name']=="bank_name"){
				$bank_name=$data[67]['value'];
				$branch_name=$data[68]['value'];
				$account_holder_name=str_replace("'","`",$data[69]['value']);
				$account_holder_number=$data[70]['value'];
				$company_name=str_replace("'","`",$data[21]['value']);
			}else{
				$bank_name=$data[68]['value'];
				$branch_name=$data[70]['value'];
				$account_holder_name=str_replace("'","`",$data[71]['value']);
				$account_holder_number=$data[72]['value'];
				$company_name=str_replace("'","`",$data[21]['value']);
			}
			*/
			
			if($data[77]['name']=="bank_name"){
				$bank_name=$data[77]['value'];
				$branch_name=$data[79]['value'];
				$account_holder_name=str_replace("'","`",$data[80]['value']);
				$account_holder_number=$data[81]['value'];
				$company_name=str_replace("'","`",$data[25]['value']);
			}else{
				$bank_name=$data[68]['value'];
				$branch_name=$data[70]['value'];
				$account_holder_name=str_replace("'","`",$data[71]['value']);
				$account_holder_number=$data[72]['value'];
				$company_name=str_replace("'","`",$data[21]['value']);
			}
			
			$this->db->query("delete from tbl_spaj_rekening where spaj_code='".$rs['spaj_code']."'");
			$this->db->query("insert tbl_spaj_rekening select '".$rs['spaj_code']."','$bank_name','$branch_name','$account_holder_name','$account_holder_number'");
			
			$this->db->query("delete from tbl_spaj_client_company where spaj_code='".$rs['spaj_code']."'");
			$this->db->query("insert tbl_spaj_client_company select '".$rs['spaj_code']."','".$company_name."'");
		
		}
		$data=array();
		$bank_name="";
		$branch_name="";
		$account_holder_name="";
		$account_holder_number="";		
		
	}
	
	
	public function byspajx()
	{
		$this->load->database();
		$spaj=$this->input->get("spaj");
		
		if($spaj==""){
			exit;
		}

		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code='".$spaj."'
		");
		$rows=$query->result_array();
		
		
		
		$data=array();
		$bank_name="";
		$branch_name="";
		$account_holder_name="";
		$account_holder_number="";
		$company_name="";
		
		echo"<pre>";	
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="DATA";
			$data=json_decode($this->other_doc($q,$type),true);

			print_r($data);
			exit;
			if($data[67]['name']=="bank_name"){
				$bank_name=$data[67]['value'];
				$branch_name=$data[68]['value'];
				$account_holder_name=str_replace("'","`",$data[69]['value']);
				$account_holder_number=$data[70]['value'];
				$company_name=str_replace("'","`",$data[21]['value']);
			}else{
				$bank_name=$data[68]['value'];
				$branch_name=$data[70]['value'];
				$account_holder_name=str_replace("'","`",$data[71]['value']);
				$account_holder_number=$data[72]['value'];
				$company_name=str_replace("'","`",$data[21]['value']);
			}
			
			$this->db->query("delete from tbl_spaj_rekening where spaj_code='".$rs['spaj_code']."'");
			$this->db->query("insert tbl_spaj_rekening select '".$rs['spaj_code']."','$bank_name','$branch_name','$account_holder_name','$account_holder_number'");
			
			$this->db->query("delete from tbl_spaj_client_company where spaj_code='".$rs['spaj_code']."'");
			$this->db->query("insert tbl_spaj_client_company select '".$rs['spaj_code']."','".$company_name."'");
			
		}
		$data=array();
		$bank_name="";
		$branch_name="";
		$account_holder_name="";
		$account_holder_number="";		
		
	}
	
	
	public function assetbyspaj()
	{
		
		$spaj=$this->input->post("spaj");
		
		if($spaj==""){
			exit;
		}

		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code='".$spaj."'
		");
		$rows=$query->result_array();
		
		$data=array();
		$asset="";
		$pendapatan="";
		$tujuan="";

		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="DATA";
			$data=json_decode($this->other_doc($q,$type),true);
			//echo"<pre>";
			//print_r($data);
			/*
			$asset=$data[64]['value'];
			$pendapatan=$data[65]['value'];
			$tujuan=$data[66]['value'];
			*/
			
			$asset=$data[73]['value'];
			$pendapatan=$data[74]['value'];
			$tujuan=$data[75]['value'];
			
			$this->conn = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_INDIVIDU_PROD", "admin","asdasd");
			$str="exec dbo.usp_KLIEN_PERUSAHAAN_iu '".$rs['spaj_code']."','$asset','$pendapatan','$tujuan','API'";
			$this->stmt=$this->conn->prepare($str);
			$this->stmt->execute();
			$asset="";
			$pendapatan="";
			$tujuan="";
			

		}
		$data=array();
		$asset="";
		$pendapatan="";
		$tujuan="";
		
	}
	
	public function assetbyspajget()
	{
		
		$spaj=$this->input->get("spaj");
		
		if($spaj==""){
			exit;
		}

		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code='".$spaj."'
		#cast(last_change_dt as date) between '2022-07-19' and '2022-07-31'
		");
		$rows=$query->result_array();
		
		$data=array();
		$asset="";
		$pendapatan="";
		$tujuan="";

		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="DATA";
			$data=json_decode($this->other_doc($q,$type),true);
			//echo"<pre>";
			//print_r($data);
			
			$asset=$data[64]['value'];
			$pendapatan=$data[65]['value'];
			$tujuan=$data[66]['value'];
			
			
			$this->conn = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_INDIVIDU_PROD", "admin","asdasd");
			$str="exec CAPITALLIFE_INDIVIDU_PROD.dbo.usp_KLIEN_PERUSAHAAN_iu '".$rs['spaj_code']."','$asset','$pendapatan','$tujuan','API'";
			echo $str;
			$this->stmt=$this->conn->prepare($str);
			$this->stmt->execute();
			$asset="";
			$pendapatan="";
			$tujuan="";
			

		}
		$data=array();
		$asset="";
		$pendapatan="";
		$tujuan="";
		
	}

	private function other_doc($q,$type){
		$dir=realpath(".")."/submission/".$q;
		
		$arr="";
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  $split=explode("_",$file);
			  if($split[0]==$type){
				$this->doc_filename=$file;
				$this->doc_filename_arr[$type]=$file;
				$arr=file_get_contents($dir."/".$file);
			  }
			}
			closedir($dh);
		  }
		}
		return $arr;
	}
}
