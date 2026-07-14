<?php
defined('BASEPATH') OR exit('No direct script access allowed');
DEFINE('DS', DIRECTORY_SEPARATOR);
class Users extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	
	public function listdata(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$data=$this->input->post("data",true);
		$data=base64_decode($data);
		$data=json_decode($data,true);
		$agen_code=isset($data["agen_code"]) ? $data["agen_code"]:"";
		$limit=isset($data["limit"]) ? $data["limit"]:"";
		$message=array('error'=>1,'message'=>'Invalid Request');
		if($agen_code!=""){
			$this->load->database();
			$str_product="SELECT *
						FROM (
						SELECT ID,title,is_publish_dt
						FROM tbl_news
						WHERE is_publish=1 AND is_public=1 UNION
						SELECT ID,title,is_publish_dt
						FROM tbl_news
						WHERE is_publish=1 AND agen_code='$agen_code') AS vw_news
						ORDER BY is_publish_dt DESC ".($limit=="" ? "":"limit ".$limit);
			$query=$this->db->query($str_product,array($agen_code));
			$rows=$query->result_array();
			
			if(count($rows)>0){
				$message=array('error'=>0,'message'=>'success','data'=>$rows);
			}else{
				$message=array('error'=>1,'message'=>'Failed');
			}
		}
		echo json_encode($message);
	}
	
	public function index()
	{
		
	}
	public function change_password()
	{
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$data=$this->input->post("data",true);
		$data=base64_decode($data);
		$data=json_decode($data,true);
		$agen_code=isset($data["agen_code"]) ? $data["agen_code"]:"";
		$pass1=isset($data["pass1"]) ? $data["pass1"]:"";
		$pass2=isset($data["pass2"]) ? $data["pass2"]:"";
		$message=array('error'=>1,'message'=>'Invalid Request');
		if($agen_code!="" && $pass1!="" && $pass2!=""){
			$this->load->database();
			$str_product="update tbl_agen set `password`=?,`password_hash`=? where agen_code=?";
			$query=$this->db->query($str_product,array($pass1,md5($pass2),$agen_code));
			if($query){
				$message=array('error'=>0,'message'=>'Success Save Your New Password');
			}else{
				$message=array('error'=>1,'message'=>'Failed Save Your New Password');
			}
		}
		echo json_encode($message);
	}
	
}
