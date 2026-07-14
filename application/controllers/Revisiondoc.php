<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Revisiondoc extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	public function index()
	{
		/*
		echo date_default_timezone_get();
		echo"<br>";
		echo date("Y-m-d H:i:s a");
		*/
		if($_POST){
			$id=$this->input->post("id",true);
			$name=$this->input->post("name",true);
			$this->load->database();
			$query=$this->db->query("select trx_id,date_format(last_change_dt,'%Y%m%d') as last_change_dt from tbl_spaj where ID=$id");
			$rows=$query->row_array();
			//print_r($_FILES);
			//exit;
			move_uploaded_file($_FILES['myfile']['tmp_name'],realpath(".")."/submission/".$rows['last_change_dt']."/".$rows['trx_id']."/".$name."_".date("Ymdhis").".png");
			
		}
	}	
}