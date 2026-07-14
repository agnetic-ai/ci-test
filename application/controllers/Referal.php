<?php
defined('BASEPATH') OR exit('No direct script access allowed');
DEFINE('DS', DIRECTORY_SEPARATOR);
class Referal extends CI_Controller {
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
		$agen_name=isset($data["agen_name"]) ? $data["agen_name"]:"";
		
		$message=array('error'=>1,'message'=>'Invalid Request');
		if($agen_code!=""){
			$this->load->database();
			/*	
			$str_product="
			select * from (
(select 
		kode_bbc as intermediary_code, 
		concat(nama_bbc,' (',kode_bbc,')') as intermediary_name,min(pic_cli) as pic_cli,min(cluster) as cluster,'bbc' as level,min(KODE_CAB) as KODE_CAB,min(NAMA_CABANG) as NAMA_CABANG
		from tbl_bank_data_ms group by kode_bbc,nama_bbc)
union		
(select 
		kode_bm as intermediary_code, 
		concat(nama_bm,' (',kode_bm,')') as intermediary_name,min(pic_cli) as pic_cli,min(cluster) as cluster,'bm' as level,min(KODE_CAB) as KODE_CAB,min(NAMA_CABANG) as NAMA_CABANG
		from tbl_bank_data_ms group by kode_bm,nama_bm)
union		
(select 
		kode_rm as intermediary_code, 
		concat(nama_rm,' (',kode_rm,')') as intermediary_name,pic_cli,cluster,LEVEL_AGEN as level,KODE_CAB,NAMA_CABANG
		from tbl_bank_data_ms)) as data
		where data.pic_cli like'%".$agen_name."%' order by cluster
			";
			*/
		$str_product="
			select * from (
(select 
		kode_bbc as intermediary_code, 
		concat(nama_bbc,' (',kode_bbc,')') as intermediary_name,pic_cli,min(cluster) as cluster,'bbc' as level,'' as KODE_CAB,'' as NAMA_CABANG
		from tbl_bank_data_ms where coalesce(kode_bbc,'')<>'' and coalesce(is_active,1)=1 group by kode_bbc,nama_bbc,pic_cli)
union		
(select 
		kode_bm as intermediary_code, 
		concat(nama_bm,' (',kode_bm,')') as intermediary_name,min(pic_cli) as pic_cli,min(cluster) as cluster,'bm' as level,min(KODE_CAB) as KODE_CAB,min(NAMA_CABANG) as NAMA_CABANG
		from tbl_bank_data_ms where  coalesce(is_active,1)=1 group by kode_bm,nama_bm)
union		
(select 
		kode_rm as intermediary_code, 
		concat(nama_rm,' (',kode_rm,')') as intermediary_name,pic_cli,cluster,LEVEL_AGEN as level,KODE_CAB,NAMA_CABANG
		from tbl_bank_data_ms where  coalesce(is_active,1)=1)) as data
		where data.pic_cli like'%".$agen_name."%' order by cluster
			";
			$query=$this->db->query($str_product);
			$rows=$query->result_array();
			
			
			
			if(count($rows)>0){
				$query2=$this->db->query("SELECT trim(cluster) as cluster,NAMA_CABANG,KODE_CAB
FROM `tbl_bank_data_ms`
WHERE `PIC_CLI` LIKE '%".$agen_name."%' group by trim(cluster),NAMA_CABANG,KODE_CAB");
			$rows2=$query2->result_array();
				
				
				$message=array('error'=>0,'message'=>'success','data'=>array('referal'=>$rows,'branch'=>$rows2));
			}else{
				$message=array('error'=>1,'message'=>'Failed');
			}
		}
		echo json_encode($message);
	}
	
}
