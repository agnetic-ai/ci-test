<?php
defined('BASEPATH') OR exit('No direct script access allowed');
DEFINE('DS', DIRECTORY_SEPARATOR);
class News extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	
	public function detail(){
		$message=array('error'=>1,'message'=>'Invalid Request');
		
		if($_SERVER['REQUEST_METHOD']!=="POST"){
			$message=array('error'=>1,'message'=>'Invalid Request Method');
		}else{
			$cases=$this->input->post("data",true);
			$cases=base64_decode($cases);
			$cases=json_decode($cases,true);
			$agen_code=isset($cases['agen_code']) ? $cases['agen_code']:"";
			$id=isset($cases['id']) ? $cases['id']:"";
			
			$this->load->database();
			$str_product="SELECT * FROM	tbl_news where ID=$id";
			$query=$this->db->query($str_product,array($agen_code));
			$rows=$query->row_array();
			
			$str='
			<div class="card">
			  <div class="card-header"><strong>'.$rows['title'].'</strong></div>
			  <div class="card-content card-content-padding">
			  '.$rows['body'].'
			  </div>
			  <div class="card-footer">Tgl. '.$rows['is_publish_dt'].'</div>
			</div>';
			
			$message=array('error'=>0,'message'=>$str);
		}
		echo json_encode($message);
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
	
	public function faq(){
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
						FROM tbl_faq
						WHERE is_publish=1 AND is_public=1 UNION
						SELECT ID,title,is_publish_dt
						FROM tbl_faq
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
	
	public function faqdetail(){
		$message=array('error'=>1,'message'=>'Invalid Request');
		
		if($_SERVER['REQUEST_METHOD']!=="POST"){
			$message=array('error'=>1,'message'=>'Invalid Request Method');
		}else{
			$cases=$this->input->post("data",true);
			$cases=base64_decode($cases);
			$cases=json_decode($cases,true);
			$agen_code=isset($cases['agen_code']) ? $cases['agen_code']:"";
			$id=isset($cases['id']) ? $cases['id']:"";
			
			$this->load->database();
			$str_product="SELECT * FROM	tbl_faq where ID=$id";
			$query=$this->db->query($str_product,array($agen_code));
			$rows=$query->row_array();
			
			$str='
			<div class="card">
			  <div class="card-header"><strong>'.$rows['title'].'</strong></div>
			  <div class="card-content card-content-padding">
			  '.$rows['body'].'
			  </div>
			  <div class="card-footer">Tgl. '.$rows['is_publish_dt'].'</div>
			</div>';
			
			$message=array('error'=>0,'message'=>$str);
		}
		echo json_encode($message);
	}
	
	public function index()
	{
		
	}
	
}
