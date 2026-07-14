<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{
		exit;
	}
	
	public function push(){
		$data=$this->input->post("data",true);
		$data=base64_decode($data);
		$data=json_decode($data,true);
		$agen_code=isset($data['agen_code']) ? $data['agen_code']:"";
		$id=isset($data['id']) ? $data['id']:"";
	}
	
	public function sent(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die('Invalid Request');
		
		
		$data=$this->input->post("data",true);
		$data=base64_decode($data);
		$data=json_decode($data,true);
		$agen_code=isset($data['agen_code']) ? $data['agen_code']:"";
		$id=isset($data['id']) ? $data['id']:"";
		$message=isset($data['message']) ? $data['message']:"";
		
		if($agen_code=="" || $id=="" || $message==""){
			$message=array('error'=>1,'message'=>'Invalid Information');
			echo json_encode($message);
			exit;
		}
		$this->load->database();
		$str="exec usp_spaj_timeline_i ?,?,?";
		$query=$this->db->query($str,array($id,$agen_code,$message));
		$message=array('error'=>0,'message'=>'success');
		echo json_encode($message);
	}
	
	public function status(){
	
	}
	
	public function doclist(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die('Invalid Request');
		
		$this->load->database();
		$str="select doc_type_nmbr,doc_type_desc from tbl_doc_type where is_publish=1 order by doc_type_nmbr asc";
		$query=$this->db->query($str);
		$rows=$query->result_array();
		$message=array('error'=>0,'message'=>'success','data'=>$rows);
		echo json_encode($message);
	}
}
