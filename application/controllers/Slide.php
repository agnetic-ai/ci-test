<?php
defined('BASEPATH') OR exit('No direct script access allowed');
DEFINE('DS', DIRECTORY_SEPARATOR);
class Slide extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		
		$this->load->database();
		$str_product="select slide_name,slide_img from tbl_slide where is_publish='Y'";
		$query=$this->db->query($str_product);
		$rows=$query->result_array();
		
		if(count($rows)>0){
			$message=array('error'=>0,'message'=>'success','data'=>$rows);
		}else{
			$message=array('error'=>1,'message'=>'Failed');
		}
		
		echo json_encode($message);
	}
	
}
