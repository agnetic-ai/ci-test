<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('display_errors', 0); 
ini_set('display_startup_errors', 0); 
error_reporting(E_ALL); 

class Revisiilustrasi extends CI_Controller {

	public function index()
	{
		$spaj=$this->input->get("spaj");
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir from tbl_spaj s 
				left join tbl_spaj_limit_up u on u.spaj_code=s.spaj_code
				where coalesce(u.is_json,0)=0 and s.age<=69 and s.spaj_code=?";
		$query=$this->db->query($str,$spaj);
		$rows=$query->row_array();
		
		$check=isset($rows['spaj_code']) ? $rows['spaj_code']:"";
		if($check==""){
			echo"invalid request";
			exit;
		}
		
		//echo"<pre>";
		//print_r($rows);
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		$data=$this->other_doc($trx_id,'SPAJ');
		//print_r($data['filename']);
		//print_r($data['path']);
		//print_r($data['data']);
		//step 1 copy file backup
		copy($data['path'],$data['rename']);
		
		//step 2 convert data to array
		$json=json_decode($data['data'],true);
		//step 3 update UP 1
		$json['cpp_up']='100.000.000,00 IDR';

		//step 4 update UP 2 di tabel list
		$arr=array();

		foreach($json['data_tabel'] as $row){
			$arr[]=array(
			'periode' => $row[periode],
			'bulan' => $row[bulan],
			'jumlah_hari' => $row[jumlah_hari],
			'mti_jumlah_hari' => $row[mti_jumlah_hari],
			'saldo_investasi' => $row[saldo_investasi],
			'manfaat_investasi' => $row[manfaat_investasi],
			'klaim' => '100.000.000,00'
			);	
		}
		$json['data_tabel']=$arr;
		
		//step 5 rebuild json dan update file
		file_put_contents($data['path'],json_encode($json));
		
		echo "Success update data";
		
	}
	
	private function other_doc($q,$type){
		$dir=realpath(".")."/submission/".$q;
		$name="";
		$rename="";
		$arr="";
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  $split=explode("_",$file);
			  if($split[0]==$type){
				$name=$file;
				$rename='REVISI_'.date('YmdHis').'_'.$file;
				$arr=file_get_contents($dir."/".$file);
			  }
			}
			closedir($dh);
		  }
		}
		return array('data'=>$arr,'filename'=>$name,'path'=>$dir.'/'.$name,'rename'=>$dir.'/'.$rename);
	}
}
