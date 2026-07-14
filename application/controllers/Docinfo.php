<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('memory_limit',-1);
ini_set('max_execution_time', -1);
class Docinfo extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	

	public function index()
	{
		$doc_code=$this->input->post("doc_code");
		if($doc_code==""){
			exit;
		}
		$this->load->database();
		$query_list=$this->db->query("
						select doc_type_desc from tbl_doc_type where is_expired='1' and doc_type_nmbr=?
						",array($doc_code));
		$rows_list=$query_list->result_array();
		$name=isset($rows_list[0]['doc_type_desc']) ? $rows_list[0]['doc_type_desc']:"";
		//echo ($name!="" ? "<b style='color:red;font-style: italic;font-size:11px;padding:20px;display:block;'>Note: ".$name." terindikasi sebagai dokumen dengan masa kadaluarsa mohon di cek kembali, dan silahkan upload dokumen ter-update.</b>":"");
		
		echo ($name!="" ? "<b style='color:red;font-style: italic;font-size:11px;padding:20px;display:block;'>Note: ".$name." merupakan dokumen yang memiliki masa kadaluarsa. Mohon cek kembali masa kadaluarsa dokumen.</b>":"");
	}

	public function spaj_detail(){
		$message=array('error'=>1,'message'=>'Invalid Request');
		$product_code="";
		$trx_idx="";
		$spaj_id="";
		$spaj_code="";
		if($_SERVER['REQUEST_METHOD']!=="POST"){
			$message=array('error'=>1,'message'=>'Invalid Request Method');
		}else{
			$cases=$this->input->post("data",true);
			$cases=base64_decode($cases);
			$cases=json_decode($cases,true);
			$id=isset($cases['id']) ? $cases['id']:"";
			$trx_id="";
			try{
				$this->load->database();
				
				$str_product="SELECT     s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir,concat(a.intermediary_name,' Cabang : ',cab.NAMA_CABANG,' - ',a.cluster) as nama_referal,concat(f.nama_agen,' (',f.kode_agen,')') as nama_agen_penutup
							FROM tbl_spaj s
join (
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
		from tbl_bank_data_ms)) a on a.intermediary_code=s.agen_code 
join tbl_pfa f on f.pfa_code=s.pfa_code
left join (select KODE_CAB,NAMA_CABANG from tbl_bank_data_ms group by KODE_CAB,NAMA_CABANG) cab on cab.KODE_CAB=s.branch_code
							WHERE s.no_polis=? order by effective_dt desc limit 1";
				$query=$this->db->query($str_product,array($id));
				$rows=$query->row_array();
				$product_code=$rows['product_code'];
				$trx_idx=$rows['trx_id'];
				$spaj_id=$rows['ID'];
				$spaj_code=$rows['spaj_code'];
				$trx_id=$rows['docdir']."/".$rows['trx_id'];
				
				
				
				
				$str_doclist="
				select t.doc_type_desc,d.path from tbl_spaj s
				join tbl_spaj_doc d on d.spaj_code=s.spaj_code
				join tbl_doc_type t on d.doc_type_nmbr=t.doc_type_nmbr
				where s.ID=?
				";
				$query_doclist=$this->db->query($str_doclist,array($spaj_id));
				$rows_doclist=$query_doclist->result_array();
				
				
			}catch(Exception $er){
				
			}
			
			$str_detail='';
			
			$str_doc="";
			$docurl="submission/".$trx_id;
			if(count($rows_doclist)>0){
			$str_doc.='<li>
					  <a href="#" onclick="downloadDOCSIL(\''.$docurl.'\',\''.$this->config->item('api_outgoing_url').'/index.php/espajcpp/cpp?id='.base64_encode($spaj_id).'\')" class="item-link item-content">
						<div class="item-media"><i class="f7-icons">document_text_fill</i></div>
						<div class="item-inner">
						  <div class="item-title">
							<div class="item-header">E-SPAJ</div>
						  </div>
						  <div class="item-after">Unduh</div>
						</div>
					  </a>
					</li>';
			}
			
			
			foreach($rows_doclist as $rdlist){
				$str_doc.='<li>
					  <a href="#" onclick="downloadDOC(\''.$rdlist['doc_type_desc'].'\',\''.$this->config->item('api_outgoing_url').'/'.$rdlist['path'].'?'.time().'\')" class="item-link item-content">
						<div class="item-media"><i class="f7-icons">document_text_fill</i></div>
						<div class="item-inner">
						  <div class="item-title">
							<div class="item-header">'.$rdlist['doc_type_desc'].'</div>
						  </div>
						  <div class="item-after">Unduh</div>
						</div>
					  </a>
					</li>';
			}
			$str='<div class="list">
				  <div class="list-group">
					<ul>
					<li class="list-group-title">Dokumen</li>
					  '.$str_doc.'
					</ul>
					  </div>
					  </div>
						';	  
			
			$message=array('error'=>0,'product_name'=>$product_code,'trx_id'=>$trx_idx,'spaj_id'=>$spaj_id,'message'=>$str);
		}
		//$str_data_pengajuan
		echo $str;
	}
}
		
?>