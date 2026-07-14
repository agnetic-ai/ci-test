<?php
defined('BASEPATH') OR exit('No direct script access allowed');
DEFINE('DS', DIRECTORY_SEPARATOR);
class Login extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}
	/*
	public function demo(){
		$this->load->database();
		$str="select 
				a.agen_code,
				a.agen_name,
				ifnull(a.parent_code,'') as parent_code,
				ifnull(a.email,'') as email,
				ifnull(a.phone,'') as phone,
				ifnull(a.fax,'') as fax,
				ifnull(a.mobile,'') as mobile,
				a.agen_level,
				ifnull(a.city,'') as city,
				ifnull(a.province,'') as province,
				ifnull(a.country,'') as country,
				ifnull(c.branch_desc,'') as branch,
				ifnull(a.sub_branch,'') as sub_branch,
				ifnull(p.pfa_desc,'') as referal_code,
				ifnull(b.bbc_desc,'') as bbc	
				from tbl_agen a
				left join tbl_agen_mapping m on a.agen_code=m.agen_code
				left join tbl_bbc b on m.bbc_code=b.bbc_code
				left join tbl_pfa p on p.pfa_code=m.pfa_code
				left join tbl_branch c on c.branch_code=m.branch_code where a.username=? and a.password_hash=? and a.status_type='A'";
			$query=$this->db->query($str,array("paulus",md5("12345678")));
			$rows=$query->row_array(); print_r($rows);
	}
	*/
	public function index()
	{
		
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$fcm_token=$this->input->post("fcm_token",true);
		$data=$this->input->post("data",true);
		$data=base64_decode($data);
		$data=json_decode($data,true);
		
		$username=isset($data["username"]) ? $data["username"]:"";
		$password=isset($data["password"]) ? $data["password"]:"";
		
		$message=array('error'=>1,'message'=>'Invalid Request');
		if($username!="" && $password!=""){
			$this->load->database();
			/*
			$str="select agen_code,agen_name,ifnull(parent_code,'') as parent_code,ifnull(email,'') as email,
			ifnull(phone,'') as phone,ifnull(fax,'') as fax,ifnull(mobile,'') as mobile,
			agen_level,ifnull(city,'') as city,ifnull(province,'') as province,ifnull(country,'') as country,
			ifnull(branch,'') as branch,ifnull(sub_branch,'') as sub_branch,ifnull(referal_code,'') as referal_code,ifnull(bbc,'') as bbc	
			from tbl_agen where username=? and password_hash=? and status_type='A'";
			*/
			$str="
			select * from (
			select 
				a.agen_code,
				a.agen_name,
				ifnull(a.parent_code,'') as parent_code,
				ifnull(a.email,'') as email,
				ifnull(a.phone,'') as phone,
				ifnull(a.fax,'') as fax,
				ifnull(a.mobile,'') as mobile,
				a.agen_level,
				ifnull(a.city,'') as city,
				ifnull(a.province,'') as province,
				ifnull(a.country,'') as country,
				ifnull(c.branch_desc,'') as branch,
				ifnull(a.sub_branch,'') as sub_branch,
				ifnull(p.pfa_desc,'') as referal_code,
				ifnull(b.bbc_desc,'') as bbc,
				a.username,
				a.password_hash		
				from tbl_agen a
				left join tbl_agen_mapping m on a.agen_code=m.agen_code
				left join tbl_bbc b on m.bbc_code=b.bbc_code
				left join tbl_pfa p on p.pfa_code=m.pfa_code
				left join tbl_branch c on c.branch_code=m.branch_code where a.status_type='A'
				union
				SELECT 
				pfa_code as `agen_code`,
				pfa_desc as `agen_name`,
				'' as `parent_code`,
				email as `email`,
				'' as `phone`,
				'' as `fax`,
				mobile as `mobile`,
				'PFA' as `agen_level`,
				'JAKARTA' as `city`,
				'DKI JAKARTA' as `province`,
				'JAKARTA' as `country`,
				'PUSAT' as `branch`,
				'PUSAT' as `sub_branch`,
				'' as `referal_code`,
				'' as `bbc`,
				username,
				password_hash
				FROM `tbl_pfa` where status_type='A'
				) as data 
				where data.username=? and data.password_hash=?
				";
			$query=$this->db->query($str,array($username,$password));
			$rows=$query->row_array();
			
			if(count($rows)>0){
				$query=null;
				$str_product="select * from vw_agen_product where agen_code=?";
				$query=$this->db->query($str_product,array($rows['agen_code']));
				
				$str_hubungan="select insurable_type_desc as hubungan from tbl_insurable_type order by cast(insurable_type_id as int) asc";
				$query_hubungan=$this->db->query($str_hubungan);
				
				$message=array('error'=>0,'message'=>'success','data'=>$rows,'agen_product'=>$query->result_array(),'hubungan'=>$query_hubungan->result_array());
				if($fcm_token!=""){
				$this->db->query("insert into tbl_agen_fcm(agen_code,fcm_token,last_change_dt) values('".$rows['agen_code']."','$fcm_token',NOW())");
				}
				
			}else{
				$message=array('error'=>1,'message'=>'Login tidak di kenali');
			}
		}
		echo json_encode($message);
	}
	
}
