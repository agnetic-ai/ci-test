<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Controller {


	public function index()
	{
		
		exit;
		
	}
	
	public function srcdata(){
		try{
			$full_name=$this->input->get("full_name");
			$dob=str_replace("-","",$this->input->get("dob"));
			$reff_no=$this->input->get("reff_no");
			$id_no=$this->input->get("id_no");
			$product=$this->input->get("product");
			
			if($full_name=="" and $dob=="" and $reff_no=="" and $id_no==""){
				exit;
			}
			
			$full_name=substr($full_name,0,100);
			$dob=substr($dob,0,20);
			$reff_no=substr($reff_no,0,20);
			$id_no=substr($id_no,0,60);
			
			
			$this->conn = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_UL_PROD", "sa","C4p1t4l.L1f3.2021");
			
			$str="exec dbo.usp_src_policy_all_status_check_data_existing '$full_name','$dob','$reff_no','$id_no','$product'";

			$this->stmt=$this->conn->prepare($str);
			
			$this->stmt->execute();
			$this->rows=$this->stmt->fetchAll(PDO::FETCH_ASSOC);
			
			//* Change By Yopi
			if(count($this->rows) <= 0){
							$this->connSpek = new PDO("dblib:host=10.17.44.55:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_UL_PROD", "sa","C4p1t4l.L1f3.2021");

							$str="exec dbo.usp_src_policy_all_status_check_data_existing '$full_name','$dob','$reff_no','$id_no','$product'";

							$this->stmtSpek=$this->connSpek->prepare($str);
							
							$this->stmtSpek->execute();
							$this->rows=$this->stmtSpek->fetchAll(PDO::FETCH_ASSOC);

			}
			//* Change By Yopi


			/*
			$this->load->model('login_model','login_model');
			$this->login_model->audit_trail($this->session->userdata("Username"),'SRC_DATA','Full name: '.$full_name.' | DOB: '.$dob.' | Reff No: '.$reff_no.' | NO ID: '.$id_no.' | Record Found : '.count($this->rows));
			*/
			if(count($this->rows)<=0){
				exit;
			}else  echo json_encode(array('data'=>$this->rows));
			
		}catch(Exception $er){
			
		}
	}
	
	public function srcdata_detail(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		try{
			
			$this->conn = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_UL_PROD", "sa","C4p1t4l.L1f3.2021");
			
			
			$reff_no=$this->input->post("policy_no",true);
			$full_name=$this->input->post("full_name",true);
			if($reff_no==""){
				exit;
			}
			
			$reff_no=substr($reff_no,0,60);
			$full_name=substr($full_name,0,200);
			
			$str="exec CAPITALLIFE_UL_PROD.dbo.usp_src_policy_all_status_check_data_existing_detail_frm '$reff_no'";

			$this->stmt=$this->conn->prepare($str);
			
			$this->stmt->execute();
			$this->rows=$this->stmt->fetchAll(PDO::FETCH_ASSOC);
			
			//* Change By Yopi
			if(count($this->rows) <= 0){
							$this->connSpek = new PDO("dblib:host=10.17.44.55:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_UL_PROD", "sa","C4p1t4l.L1f3.2021");

											$str="exec CAPITALLIFE_UL_PROD.dbo.usp_src_policy_all_status_check_data_existing_detail_frm '$reff_no'";

							$this->stmtSpek=$this->connSpek->prepare($str);
							
							$this->stmtSpek->execute();
							$this->rows=$this->stmtSpek->fetchAll(PDO::FETCH_ASSOC);

			}
			//* Change By Yopi

			/*
			$this->load->model('login_model','login_model');
			$this->login_model->audit_trail($this->session->userdata("Username"),'VIEW_DATA','Full name: '.$full_name.' | POLIS: '.$reff_no);
			*/
			
			echo json_encode($this->rows);
			
		}catch(Exception $er){
			
		}
	}
}
