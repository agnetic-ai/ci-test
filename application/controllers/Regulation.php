<?php
defined('BASEPATH') OR exit('No direct script access allowed');
DEFINE('DS', DIRECTORY_SEPARATOR);
class Regulation extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		
		$data=$this->input->post("data",true);
		$data=base64_decode($data);
		$data=json_decode($data,true);
		
		$agen_code=isset($data["agen_code"]) ? $data["agen_code"]:"";
		$product=isset($data["product"]) ? $data["product"]:"";
		
		$message=array('error'=>1,'message'=>'Invalid Request');
		if($agen_code!=""){
			$this->load->database();
			$str_product="select * from vw_agen_product_rule where agen_code=? and product_alias=?";
			$query=$this->db->query($str_product,array($agen_code,$product));
			$rows=$query->result_array();
			
			if(count($rows)>0){
				$message=array('error'=>0,'message'=>'success','data'=>$rows);
			}else{
				$message=array('error'=>1,'message'=>'Failed');
			}
		}
		echo json_encode($message);
	}
	
}
