<?php
use Dompdf\Dompdf;
defined('BASEPATH') OR exit('No direct script access allowed');

class Espajcpp extends CI_Controller {

	public function index(){
		exit;
	}
	
	public function cpp(){
		require_once(realpath(".").'/application/libraries/PDFMerger/PDFMerger.php');
		
		if($this->input->get("id",true)==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir from tbl_spaj s where s.ID=?";
		$query=$this->db->query($str,array(base64_decode($this->input->get("id",true))));
		$rows=$query->row_array();
		if(count($rows)<=0){
			echo"invalid access";
			exit;
		}
		
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		if(file_exists(realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf')){
			redirect($this->config->item("api_outgoing_url")."/xtemp/".$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf?'.time());
			exit;
		}
		if($rows['product_code']=="CPP"){
			$this->cpp1();
			$this->cpp2();
		}else{
			$this->cpl1();
			$this->cpl2();
			$this->cpl3();
		}
		$files=scandir(realpath(".")."/xtemp/".$rows['spaj_code']);
		
		$pdf = new \PDFMerger;
		$arr=array();
		foreach($files as $f){
			if(!in_array($f,array('.','..'))){
				$arr[]=realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$f;
				$pdf->addPDF(realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$f);
			}
		}
		$pdf->merge('file', realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf');
		
		foreach($arr as $a){
			@unlink($a);
		}
		
		redirect($this->config->item("api_outgoing_url")."/xtemp/".$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf?'.time());
	}
	
	public function cpp_spaj(){
		require_once(realpath(".").'/application/libraries/PDFMerger/PDFMerger.php');
		
		if($this->input->get("spaj_code")==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir from tbl_spaj s where s.spaj_code=?";
		$query=$this->db->query($str,array($this->input->get("spaj_code")));
		$rows=$query->row_array();
		if(count($rows)<=0){
			echo"invalid access";
			exit;
		}
		
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		if(file_exists(realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf')){
			redirect($this->config->item("api_outgoing_url")."/xtemp/".$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf');
			exit;
		}
		if($rows['product_code']=="CPP"){
			$this->cpp1();
			$this->cpp2();
		}else{
			$this->cpl1();
			$this->cpl2();
			$this->cpl3();
		}
		$files=scandir(realpath(".")."/xtemp/".$rows['spaj_code']);
		
		$pdf = new \PDFMerger;
		$arr=array();
		foreach($files as $f){
			if(!in_array($f,array('.','..'))){
				$arr[]=realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$f;
				$pdf->addPDF(realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$f);
			}
		}
		$pdf->merge('file', realpath(".").'/xtemp/'.$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf');
		
		foreach($arr as $a){
			@unlink($a);
		}
		
		redirect($this->config->item("api_outgoing_url")."/xtemp/".$rows['spaj_code'].'/'.$rows['spaj_code'].'.pdf');
	}
	
	private function cpp1()
	{
		if($this->input->get("id",true)==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir from tbl_spaj s where s.ID=?";
		$query=$this->db->query($str,array(base64_decode($this->input->get("id",true))));
		$rows=$query->row_array();
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		
		
		
		//DATA PENGAJUAN		
		$json1 =$this->other_doc($trx_id,'DATA');
		
		//DATA SPAJ
		$json2 = $this->other_doc($trx_id,'SPAJ');
	
		$decode1=json_decode($json1,true);
		$decode2=json_decode($json2,true);
		//print_r($decode2);
		//exit;
		
		
		require_once(realpath(".").'/application/libraries/dompdf/autoload.inc.php');
		ob_start();
		$content=file_get_contents(realpath(".")."/template/cpp_page1.html");
		ob_end_clean();
		
		foreach($decode1 as $key=>$val){
			if ($val['name']=='identity_type_ktp1'){
				$identity_type_ktp1=array(
				'KTP'=>'identity_type_ktp1',
				'SIM'=>'identity_type_sim1',
				'PASPORT'=>'identity_type_paspor1',
				'AKTE'=>'identity_type_akte1',
				'LAINNYA'=>'identity_type_lain1',
				''=>''
				);
				$content=str_replace("{".$identity_type_ktp1[$val['value']]."}","V",$content);
				unset($identity_type_ktp1[$val['value']]);
				foreach($identity_type_ktp1 as $identity_type_ktp1_key=>$identity_type_ktp1_val){
					$content=str_replace("{".$identity_type_ktp1_val."}","",$content);
				}		
			}
			if ($val['name']=='citizenship_wni1'){
					$citizenship1=array(
					'WNI'=>'citizenship1',
					'WNA'=>'citizenship1',
					''=>''
					);
					$content=str_replace("{".$citizenship1[$val['value']]."}",$val['value'],$content);
					unset($citizenship1[$val['value']]);
					foreach($citizenship1 as $citizenship1_key=>$citizenship1_val){
						$content=str_replace("{".$citizenship1_val."}","",$content);
					}
					
				}
				
			if($val['name']=='gen_pria1'){
				$gen_pria1=array(
				'PRIA'=>'gen_pria1',
				'WANITA'=>'gen_wanita1',
				''=>''
				);
				$content=str_replace("{".$gen_pria1[$val['value']]."}","V",$content);
				unset($gen_pria1[$val['value']]);
				foreach($gen_pria1 as $gen_pria1_key=>$gen_pria1_val){
					$content=str_replace("{".$gen_pria1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='status_menikah1'){
				$status_menikah1=array(
				'MENIKAH'=>'status_menikah1',
				'BELUM MENIKAH'=>'status_belum1',
				'LAINNYA'=>'status_lain1',
				''=>''
				);
				$content=str_replace("{".$status_menikah1[$val['value']]."}","V",$content);
				unset($status_menikah1[$val['value']]);
				foreach($status_menikah1 as $status_menikah1_key=>$status_menikah1_val){
					$content=str_replace("{".$status_menikah1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='religion_islam1'){
				$religion_islam1=array(
				'ISLAM'=>'religion_islam1',
				'KRISTEN'=>'religion_kristen1',
				'KATHOLIK'=>'religion_katholik1',
				'BUDHA'=>'religion_budha1',
				'HINDU'=>'religion_hindu1',
				''=>''
				);
				$content=str_replace("{".$religion_islam1[$val['value']]."}","V",$content);
				unset($religion_islam1[$val['value']]);
				foreach($religion_islam1 as $religion_islam1_key=>$religion_islam1_val){
					$content=str_replace("{".$religion_islam1_val."}","",$content);
				}
				
			}
			if ($val['name']=='citizenship_wni2'){
					$citizenship2=array(
					'WNI'=>'citizenship2',
					'WNA'=>'citizenship2',
					''=>''
					);
					$content=str_replace("{".$citizenship2[$val['value']]."}",$val['value'],$content);
					unset($citizenship2[$val['value']]);
					foreach($citizenship2 as $citizenship2_key=>$citizenship2_val){
						$content=str_replace("{".$citizenship2_val."}","",$content);
					}
					
				}
			
			if($val['name']=='relationship_diri2'){
				$relationship_diri2=array(
				'DIRI SENDIRI'=>'relationship_diri2',
				'SUAMI/ISTRI'=>'relationship_suami2',
				'SUAMI'=>'relationship_suami2',
				'ANAK'=>'relationship_anak2',
				'ORANG TUA'=>'relationship_orangtua2',
				'LAINNYA'=>'relationship_lain2',
				''=>''
				);
				$content=str_replace("{".$relationship_diri2[$val['value']]."}","V",$content);
				unset($relationship_diri2[$val['value']]);
				foreach($relationship_diri2 as $relationship_diri2_key=>$relationship_diri2_val){
					$content=str_replace("{".$relationship_diri2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='identity_type_ktp2'){
				$identity_type_ktp2=array(
				'KTP'=>'identity_type_ktp2',
				'SIM'=>'identity_type_sim2',
				'PASPORT'=>'identity_type_paspor2',
				'AKTE'=>'identity_type_akte2',
				'LAINNYA'=>'identity_type_lain2',
				''=>''
				);
				$content=str_replace("{".$identity_type_ktp2[$val['value']]."}","V",$content);
				unset($identity_type_ktp2[$val['value']]);
				foreach($identity_type_ktp2 as $identity_type_ktp2_key=>$identity_type_ktp2_val){
					$content=str_replace("{".$identity_type_ktp2_val."}","",$content);
				}
				
			}
				
			if($val['name']=='gen_pria2'){
				$gen_pria2=array(
				'PRIA'=>'gen_pria2',
				'WANITA'=>'gen_wanita2',
				''=>''
				);
				$content=str_replace("{".$gen_pria2[$val['value']]."}","V",$content);
				unset($gen_pria2[$val['value']]);
				foreach($gen_pria2 as $gen_pria2_key=>$gen_pria2_val){
					$content=str_replace("{".$gen_pria2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='status_menikah2'){
				$status_menikah2=array(
				'MENIKAH'=>'status_menikah2',
				'BELUM MENIKAH'=>'status_belum2',
				'LAINNYA'=>'status_lain2',
				''=>''
				);
				$content=str_replace("{".$status_menikah2[$val['value']]."}","V",$content);
				unset($status_menikah2[$val['value']]);
				foreach($status_menikah2 as $status_menikah2_key=>$status_menikah2_val){
					$content=str_replace("{".$status_menikah2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='religion_islam2'){
				$religion_islam2=array(
				'ISLAM'=>'religion_islam2',
				'KRISTEN'=>'religion_kristen2',
				'KATHOLIK'=>'religion_katholik2',
				'BUDHA'=>'religion_budha2',
				'HINDU'=>'religion_hindu2',
				''=>''
				);
				$content=str_replace("{".$religion_islam2[$val['value']]."}","V",$content);
				unset($religion_islam2[$val['value']]);
				foreach($religion_islam2 as $religion_islam2_key=>$religion_islam2_val){
					$content=str_replace("{".$religion_islam2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='insurance_purpose1'){
				$insurance_purpose1=array(
				'PROTEKSI'=>'insurance_purpose1',
				'INVESTASI'=>'insurance_purpose2',
				'PENDIDIKAN'=>'insurance_purpose3',
				'PENSIUN'=>'insurance_purpose4',
				'TUJUAN USAHA'=>'insurance_purpose5',
				'LAIN_LAIN'=>'insurance_purpose6',
				''=>''
				);
				$content=str_replace("{".$insurance_purpose1[$val['value']]."}","V",$content);
				unset($insurance_purpose1[$val['value']]);
				foreach($insurance_purpose1 as $insurance_purpose1_key=>$insurance_purpose1_val){
					$content=str_replace("{".$insurance_purpose1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='premium_payer1'){
				$premium_payer1=array(
				'PEMEGANG POLIS'=>'premium_payer1',
				'TERTANGGUNG'=>'premium_payer2',
				'LAINNYA'=>'premium_payer3',
				''=>''
				);
				$content=str_replace("{".$premium_payer1[$val['value']]."}","V",$content);
				unset($premium_payer1[$val['value']]);
				foreach($premium_payer1 as $ipremium_payer1_key=>$premium_payer1_val){
					$content=str_replace("{".$premium_payer1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='income1'){
				$income1=array(
				'GAJI'=>'income1',
				'HASIL INVESTASI'=>'income2',
				'BISNIS PRIBADI'=>'income3',
				'BONUS/KOMISI'=>'income4',
				'LAINNYA'=>'income5',
				'HASIL USAHA'=>'income3',
				''=>''
				);
				$content=str_replace("{".$income1[$val['value']]."}","V",$content);
				unset($income1[$val['value']]);
				foreach($income1 as $income1_key=>$income1_val){
					$content=str_replace("{".$income1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='gross_income1'){
				$gross_income1=array(
				'&lt;60Juta'=>'gross_income1',
				'<60Juta'=>'gross_income1',
				'60Juta-180Juta'=>'gross_income2',
				'180Juta-360Juta'=>'gross_income3',
				'360Juta-600Juta'=>'gross_income4',
				'>600Juta'=>'gross_income5',
				'&gt;600Juta'=>'gross_income5',
				''=>''
				);	
				$content=str_replace("{".$gross_income1[$val['value']]."}","V",$content);
				unset($gross_income1[$val['value']]);
				foreach($gross_income1 as $gross_income1_key=>$gross_income1_val){
					$content=str_replace("{".$gross_income1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='assets1'){
				$assets1=array(
				'<100Jt'=>'assets1',
				'100Juta-1Milyar'=>'assets2',
				'>1Milyar-10Milyar'=>'assets3',
				'>10Milyar-100Milyar'=>'assets4',
				'>100Milyar-500Milyar'=>'assets5',
				''=>''
				);	
				$content=str_replace("{".$assets1[$val['value']]."}","V",$content);
				unset($assets1[$val['value']]);
				foreach($assets1 as $assets1_key=>$assets1_val){
					$content=str_replace("{".$assets1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='income_peryear1'){
				$income_peryear1=array(
				'<100Jt'=>'income_peryear1',
				'100Juta-500Juta'=>'income_peryear2',
				'>500Juta-1Milyar'=>'income_peryear3',
				'>1Milyar-10Milyar'=>'income_peryear4',
				'>10Milyar'=>'income_peryear5',
				''=>''
				);	
				$content=str_replace("{".$income_peryear1[$val['value']]."}","V",$content);
				unset($income_peryear1[$val['value']]);
				foreach($income_peryear1 as $income_peryear1_key=>$income_peryear1_val){
					$content=str_replace("{".$income_peryear1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='insurance_purpose8'){
				$insurance_purpose8=array(
				'Proteksi Pendapatan'=>'insurance_purpose8',
				'Proteksi Kredit'=>'insurance_purpose9',
				'Lain-lain'=>'insurance_purpose10',
				''=>''
				);	
				$content=str_replace("{".$insurance_purpose8[$val['value']]."}","V",$content);
				unset($insurance_purpose8[$val['value']]);
				foreach($insurance_purpose8 as $insurance_purpose8_key=>$insurance_purpose8_val){
					$content=str_replace("{".$insurance_purpose8_val."}","",$content);
				}
				
			}else{
				if(in_array($val['name'],array('date_of_birth1','date_of_birth2'))){
					$content=str_replace("{".$val['name']."}",$this->fix_date($val['value']),$content);
				}else{
					$content=str_replace("{".$val['name']."}",$val['value'],$content);
				}
			}
		}
		
		foreach($decode2 as $key1=>$val1){
			//echo $key1."|".$val1;
			if(!in_array($key1,array('data_tabel'))){
			$content=str_replace("{".$key1."}",$val1,$content);
			}
		}
		
		$dompdf = new DOMPDF();
		//{IMG_BG}
		$content=str_replace("{IMG_BG}","data:image/png;base64,".base64_encode(file_get_contents(realpath(".")."/template/CPP1.png")),$content);
		$dompdf->load_html($content);
		$dompdf->set_paper("A4", "portrait");
		$dompdf->render();
		$output = $dompdf->output();
		if(!is_dir(realpath(".")."/xtemp/".$rows['spaj_code'])){
			@mkdir(realpath(".")."/xtemp/".$rows['spaj_code']);
		}
		file_put_contents(realpath(".")."/xtemp/".$rows['spaj_code']."/".$rows['spaj_code']."_1.pdf", $output);
		//$dompdf->stream("cpp_page1.pdf",array("Attachment"=>0));
	}
	
	private function cpp2()
	{
		include(realpath(".").'/application/libraries/phpqrcode/qrlib.php');
		if($this->input->get("id",true)==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir,s.agen_code,p.kode_agen,
				case when k.agency_team_code in('151','078','079') then k.agency_team_name else 'Jakarta' end as nama_kota	
				from tbl_spaj s 
				join tbl_pfa p on p.pfa_code=s.pfa_code
				left join sw_agency_team k on cast(k.agency_team_code as int)=cast(s.branch_code as int)
				where s.ID=?";
		$query=$this->db->query($str,array(base64_decode($this->input->get("id",true))));
		$rows=$query->row_array();
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		
		$full_name1="";
		$full_name2="";
		
		//DATA PENGAJUAN		
		$json1 =$this->other_doc($trx_id,'DATA');
		
		//DATA SPAJ
		$json2 = $this->other_doc($trx_id,'SPAJ');
		
		//DATA AHLIWARIS
		$json3 = $this->other_doc($trx_id,'AHLI');
		
		$decode1 = json_decode($json1,true);
		$decode2 = json_decode($json2,true);
		$decode3 = json_decode($json3,true);
		require_once(realpath(".").'/application/libraries/dompdf/autoload.inc.php');
		ob_start();
		$content=file_get_contents(realpath(".")."/template/cpp_page2.html");
		ob_end_clean();
		
		$q_yes1="";
		$q_yes2="";
		$q_yes3="";
		
		$explain1="";
		$explain2="";
		$explain3="";
		
		$q_no1="";
		$q_no2="";
		$q_no3="";
		
		foreach($decode1 as $key=>$val){
			if($val['name']=="full_name1"){
			$full_name1=$val['value'];
			}
			
			if($val['name']=="full_name2"){
			$full_name2=$val['value'];
			}
			
			if($val['name']=="q_yes1"){ 
				$q_yes1=$val['value'];
			}
			
			if($val['name']=="q_yes2"){ 
				$q_yes2=$val['value'];
			}
			
			if($val['name']=="q_yes3"){ 
				$q_yes3=$val['value'];
			}
			
			if($val['name']=="explain1"){ 
				$explain1=$val['value'];
			}
			if($val['name']=="explain2"){ 
				$explain2=$val['value'];
			}
			if($val['name']=="explain3"){ 
				$explain3=$val['value'];
			}
			
			if($val['name']=="q_no1"){ 
				$q_no1=$val['value'];
			}
			if($val['name']=="q_no2"){ 
				$q_no2=$val['value'];
			}
			if($val['name']=="q_no3"){ 
				$q_no3=$val['value'];
			}
			if ($val['name']=='account_holder_name'){
				if(strlen($val['value'])>34){
					$content=str_replace("{style_".$val['name']."}"," style=\"font-size:6.5pt;top:119pt\" ",$content);
					$content=str_replace("{".$val['name']."}",$val['value'],$content);
				}else{
					$content=str_replace("{style_".$val['name']."}","",$content);
					$content=str_replace("{".$val['name']."}",$val['value'],$content);
				}
				
			}
			
			$content=str_replace("{".$val['name']."}",$val['value'],$content);
		}
		
		
		foreach($decode2 as $key=>$val){
			if(!in_array($key,array('data_tabel'))){
			if ($key=='cpp_periode'){
				$cpp_periode=array(
				'6 Bulan'=>'investment_payment_period1',
				'12 Bulan'=>'investment_payment_period2',
				''=>''
				);
				$content=str_replace("{".$cpp_periode[$val]."}","V",$content);
				unset($cpp_periode[$val]);
				foreach($cpp_periode as $cpp_periode_key=>$cpp_periode_val){
					$content=str_replace("{".$cpp_periode_val."}","",$content);
				}
				
			}
			
			if ($key=='cpp_currency'){
				$cpp_currency=array(
				'IDR'=>'currency1',
				'USD'=>'currency2',
				''=>''
				);
				$content=str_replace("{".$cpp_currency[$val]."}","V",$content);
				unset($cpp_currency[$val]);
				foreach($cpp_currency as $cpp_currency_key=>$cpp_currency_val){
					$content=str_replace("{".$cpp_currency_val."}","",$content);
				}
				
			}
			
			if ($key=='yes1'){
				$yes1=array(
				'Yes'=>'yes1',
				'No'=>'no1',
				''=>''
				);
				$content=str_replace("{".$yes1[$val]."}","V",$content);
				unset($yes1[$val]);
				foreach($yes1 as $yes1_key=>$yes1_val){
					$content=str_replace("{".$yes1_val."}","",$content);
				}
				
			}
			
			if ($key=='yes2'){
				$yes2=array(
				'Yes'=>'yes2',
				'No'=>'no2',
				''=>''
				);
				$content=str_replace("{".$yes2[$val]."}","V",$content);
				unset($yes2[$val]);
				foreach($yes2 as $yes2_key=>$yes2_val){
					$content=str_replace("{".$yes2_val."}","",$content);
				}
				
			}
			
			if ($key=='yes3'){
				$yes3=array(
				'Yes'=>'yes3',
				'No'=>'no3',
				''=>''
				);
				$content=str_replace("{".$yes3[$val]."}","V",$content);
				unset($yes3[$val]);
				foreach($yes3 as $yes3_key=>$yes3_val){
					$content=str_replace("{".$yes3_val."}","",$content);
				}
				
			}else{
				$content=str_replace("{".$key."}",str_replace("%","",$val),$content);
			}
			
			}
		}
		$jj=0;
		foreach($decode3 as $bn){
			$jj++;
			foreach ($bn as $key=>$val){
				$content=str_replace("{no".$jj."}",$jj,$content);
				if($key=="nama"){
					$content=str_replace("{beneficiary".$jj."}",$val,$content);
				}
				
				if($key=="hubungan"){
					$content=str_replace("{relationship".$jj."}",$val,$content);
				}
				
				if($key=="pct"){
					$content=str_replace("{benefit".$jj."}",number_format($val,2),$content);
				}
				
				if($key=="jenis_kelamin"){
					$content=str_replace("{gen".$jj."}",(($val=="PRIA" or $val=="L") ? "P":"W" ),$content);
				}
				
				if($key=="tanggal_lahir"){
					$content=str_replace("{birth_date".$jj."}",$this->fix_date($val),$content);
				}
				
				if($key=="tempat_lahir"){
					$content=str_replace("{place".$jj."}",$val,$content);
					
				}
			}
		}
		$content=str_replace("{beneficiary1}","",$content);
		$content=str_replace("{beneficiary2}","",$content);
		$content=str_replace("{beneficiary3}","",$content);
		$content=str_replace("{beneficiary4}","",$content);
		$content=str_replace("{beneficiary5}","",$content);
		$content=str_replace("{beneficiary6}","",$content);
		
		$content=str_replace("{relationship1}","",$content);
		$content=str_replace("{relationship2}","",$content);
		$content=str_replace("{relationship3}","",$content);
		$content=str_replace("{relationship4}","",$content);
		$content=str_replace("{relationship5}","",$content);
		$content=str_replace("{relationship6}","",$content);
		
		$content=str_replace("{benefit1}","",$content);
		$content=str_replace("{benefit2}","",$content);
		$content=str_replace("{benefit3}","",$content);
		$content=str_replace("{benefit4}","",$content);
		$content=str_replace("{benefit5}","",$content);
		$content=str_replace("{benefit6}","",$content);
		
		$content=str_replace("{gen1}","",$content);
		$content=str_replace("{gen2}","",$content);
		$content=str_replace("{gen3}","",$content);
		$content=str_replace("{gen4}","",$content);
		$content=str_replace("{gen5}","",$content);
		$content=str_replace("{gen6}","",$content);
		
		$content=str_replace("{birth_date1}","",$content);
		$content=str_replace("{birth_date2}","",$content);
		$content=str_replace("{birth_date3}","",$content);
		$content=str_replace("{birth_date4}","",$content);
		$content=str_replace("{birth_date5}","",$content);
		$content=str_replace("{birth_date6}","",$content);
		
		$content=str_replace("{place1}","",$content);
		$content=str_replace("{place2}","",$content);
		$content=str_replace("{place3}","",$content);
		$content=str_replace("{place4}","",$content);
		$content=str_replace("{place5}","",$content);
		$content=str_replace("{place6}","",$content);
		
		$content=str_replace("{no1}","",$content);
		$content=str_replace("{no2}","",$content);
		$content=str_replace("{no3}","",$content);
		$content=str_replace("{no4}","",$content);
		$content=str_replace("{no5}","",$content);
		$content=str_replace("{no6}","",$content);
		
		$q_yes1="";
		$q_yes2="";
		$q_yes3="";
		
		$explain1="";
		$explain2="";
		$explain3="";
		
		$q_no1="";
		$q_no2="";
		$q_no3="";
		
		if($q_yes1=="" and $q_no1==""){
			$content=str_replace("{q_yes1}","V",$content);
		}
		if($q_yes2=="" and $q_no2==""){
			$content=str_replace("{q_yes2}","V",$content);
		}
		if($q_yes3=="" and $q_no3==""){
			$content=str_replace("{q_yes3}","V",$content);
		}
		
		if($explain1==""){
			$content=str_replace("{explain1}","2 Tahun",$content);
		}
		
		
		$content=str_replace("{explain1}","",$content);
		$content=str_replace("{explain2}","",$content);
		$content=str_replace("{explain3}","",$content);
		
		$content=str_replace("{q_yes1}","",$content);
		$content=str_replace("{q_yes2}","",$content);
		$content=str_replace("{q_yes3}","",$content);
		
		$content=str_replace("{q_no1}","",$content);
		$content=str_replace("{q_no2}","",$content);
		$content=str_replace("{q_no3}","",$content);
		
		
		
		//TTD PEMPOL
		ob_start();
		QRCode::png($full_name1."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_policy_holder_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD TT
		ob_start();
		QRCode::png($full_name2."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_insured_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD PR
		ob_start();
		QRCode::png($rows['agen_code']."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_agen_reff_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD AGEN 
		QRCode::png($rows['kode_agen']."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_agen_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		
		$exp_date=explode("-",$rows['effective_dt']);
		
		
		$content=str_replace("{policy_holder_name_ttd}","data:image/png;base64,".$imageString_policy_holder_name_ttd,$content);
		$content=str_replace("{insured_name_ttd}","data:image/png;base64,".$imageString_insured_name_ttd,$content);
		$content=str_replace("{agen_reff_name_ttd}","data:image/png;base64,".$imageString_agen_reff_name_ttd,$content);
		$content=str_replace("{agen_name_ttd}","data:image/png;base64,".$imageString_agen_name_ttd,$content);
		
		$content=str_replace("{agen_reff_code}",$rows['agen_code'],$content);
		$content=str_replace("{agen_code}",$rows['kode_agen'],$content);
		
		$content=str_replace("{place_of_signature}",$rows['nama_kota'],$content);
		
		$content=str_replace("{date}",$exp_date[2],$content);
		$content=str_replace("{month}",$exp_date[1],$content);
		$content=str_replace("{year}",$exp_date[0],$content);
		
		
		
		
		
		
		
		$dompdf = new DOMPDF();
		//{IMG_BG}
		$content=str_replace("{IMG_BG}","data:image/png;base64,".base64_encode(file_get_contents(realpath(".")."/template/CPP2.png")),$content);
		$dompdf->load_html($content);
		$dompdf->set_paper("A4", "portrait");
		$dompdf->render();
		$output = $dompdf->output();
		if(!is_dir(realpath(".")."/xtemp/".$rows['spaj_code'])){
			@mkdir(realpath(".")."/xtemp/".$rows['spaj_code']);
		}
		file_put_contents(realpath(".")."/xtemp/".$rows['spaj_code']."/".$rows['spaj_code']."_2.pdf", $output);
		//$dompdf->stream("cpp_page2.pdf",array("Attachment"=>0));
	}
	
	private function cpl1()
	{
		
		if($this->input->get("id",true)==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir from tbl_spaj s where s.ID=?";
		$query=$this->db->query($str,array(base64_decode($this->input->get("id",true))));
		$rows=$query->row_array();
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		
		
		
		//DATA PENGAJUAN		
		$json1 =$this->other_doc($trx_id,'DATA');
		
		//DATA SPAJ
		$json2 = $this->other_doc($trx_id,'SPAJ');
	
		$decode1=json_decode($json1,true);
		$decode2=json_decode($json2,true);
		
		
		require_once(realpath(".").'/application/libraries/dompdf/autoload.inc.php');
		ob_start();
		$content=file_get_contents(realpath(".")."/template/cpl_page1.html");
		ob_end_clean();
		foreach($decode1 as $key=>$val){
			if($val['name']=='gen_pria1'){
			$gen_pria1=array(
				'PRIA'=>'gen_pria1',
				'WANITA'=>'gen_wanita1',
				''=>''
				);
				$content=str_replace("{".$gen_pria1[$val['value']]."}","V",$content);
				unset($gen_pria1[$val['value']]);
				foreach($gen_pria1 as $gen_pria1_key=>$gen_pria1_val){
					$content=str_replace("{".$gen_pria1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='identity_type_ktp1'){
			$identity_type_ktp1=array(
				'KTP'=>'identity_type_ktp1',
				'PASPORT'=>'identity_type_paspor1',
				'AKTE'=>'identity_type_akte1',
				'LAINNYA'=>'identity_type_lain1'
				);
				$content=str_replace("{".$identity_type_ktp1[$val['value']]."}","V",$content);
				unset($identity_type_ktp1[$val['value']]);
				foreach($identity_type_ktp1 as $identity_type_ktp1_key=>$identity_type_ktp1_val){
					$content=str_replace("{".$identity_type_ktp1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='status_menikah1'){
				$status_menikah1=array(
				'MENIKAH'=>'status_menikah1',
				'BELUM MENIKAH'=>'status_belum1',
				'JANDA/DUDA'=>'status_janda_duda1',
				'LAINNYA'=>'status_janda_duda1',
				''=>''
				);
				$content=str_replace("{".$status_menikah1[$val['value']]."}","V",$content);
				unset($status_menikah1[$val['value']]);
				foreach($status_menikah1 as $status_menikah1_key=>$status_menikah1_val){
					$content=str_replace("{".$status_menikah1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='religion_islam1'){
				$religion_islam1=array(
				'ISLAM'=>'religion_islam1',
				'KRISTEN'=>'religion_kristen1',
				'KATHOLIK'=>'religion_katholik1',
				'BUDHA'=>'religion_budha1',
				'HINDU'=>'religion_hindu1',
				'LAINNYA'=>'religion_lainnya1',
				''=>''
				);
				$content=str_replace("{".$religion_islam1[$val['value']]."}","V",$content);
				unset($religion_islam1[$val['value']]);
				foreach($religion_islam1 as $religion_islam1_key=>$religion_islam1_val){
					$content=str_replace("{".$religion_islam1_val."}","",$content);
				}
				
			}
			
			if ($val['name']=='citizenship_wni1'){
				$citizenship1=array(
				'WNI'=>'citizenship_wni1',
				'WNA'=>'citizenship_wna1',
				''=>''
				);
				$content=str_replace("{".$citizenship1[$val['value']]."}","V",$content);
				unset($citizenship1[$val['value']]);
				foreach($citizenship1 as $citizenship1_key=>$citizenship1_val){
					$content=str_replace("{".$citizenship1_val."}","",$content);
				}
				
			}
			
			if ($val['name']=='profession1'){
				$profession1=array(
				' KARYAWAN SWASTA'=>'profession_karyawan1',
				'PELAJAR/MAHASISWA'=>'profession_pelajar1',
				'PEGAWAI BUMN/BUMD'=>'profession_pegawai1',
				'LAINNYA'=>'profession_lainnya1',
				'IBU RUMAH TANGGA'=>'profession_irt1',
				'TNI/POLRI'=>'profession_tni1',
				'WIRASWASTA/PENGUSAHA'=>'profession_wiraswasta1',
				'PNS'=>'profession_pns1',
				'PENSIUNAN'=>'profession_pensiunan1',
				'PEJABAT PEMERINTAH/DPR/DPRD'=>'profession_pejabat1',
				''=>''
				);
				//{profession_jelaskan1}
				//{profession_jelaskan2}
				//$val['value']=in_array($val['value'],$profession1) ? $val['value']:"LAINNYA";
				
				$content=str_replace("{profession_jelaskan1}",$val['value'],$content);
				
				$profession1[$val['value']]=isset($profession1[$val['value']]) ? $profession1[$val['value']]:"";
				$val['value']=$profession1[$val['value']]!="" ? $val['value']:"LAINNYA";
				$content=str_replace("{".$profession1[$val['value']]."}","V",$content);
				
				unset($profession1[$val['value']]);
				foreach($profession1 as $profession1_key=>$profession1_val){
					$content=str_replace("{".$profession1_val."}","",$content);
				}
				
				
			}
			
			if($val['name']=='relationship_diri2'){
				$relationship_diri2=array(
				'DIRI SENDIRI'=>'relationship_diri2',
				'SUAMI/ISTRI'=>'relationship_suami2',
				'ANAK'=>'relationship_anak2',
				'ORANG TUA'=>'relationship_orangtua2',
				'LAINNYA'=>'relationship_lain2',
				''=>''
				);
				$content=str_replace("{".$relationship_diri2[$val['value']]."}","V",$content);
				unset($relationship_diri2[$val['value']]);
				foreach($relationship_diri2 as $relationship_diri2_key=>$relationship_diri2_val){
					$content=str_replace("{".$relationship_diri2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='gen_pria2'){
			$gen_pria2=array(
				'PRIA'=>'gen_pria2',
				'WANITA'=>'gen_wanita2',
				''=>''
				);
				$content=str_replace("{".$gen_pria2[$val['value']]."}","V",$content);
				unset($gen_pria2[$val['value']]);
				foreach($gen_pria2 as $gen_pria2_key=>$gen_pria2_val){
					$content=str_replace("{".$gen_pria2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='identity_type_ktp2'){
				$identity_type_ktp2=array(
				'KTP'=>'identity_type_ktp2',
				'PASPORT'=>'identity_type_paspor2',
				'AKTE'=>'identity_type_akte2',
				'LAINNYA'=>'identity_type_lain2',
				''=>''
				);
				$content=str_replace("{".$identity_type_ktp2[$val['value']]."}","V",$content);
				unset($identity_type_ktp2[$val['value']]);
				foreach($identity_type_ktp2 as $identity_type_ktp2_key=>$identity_type_ktp2_val){
					$content=str_replace("{".$identity_type_ktp2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='status_menikah2'){
				$status_menikah2=array(
				'MENIKAH'=>'status_menikah2',
				'BELUM MENIKAH'=>'status_belum2',
				'JANDA/DUDA'=>'status_janda_duda2',
				'LAINNYA'=>'status_janda_duda2',
				''=>''
				);
				$content=str_replace("{".$status_menikah2[$val['value']]."}","V",$content);
				unset($status_menikah2[$val['value']]);
				foreach($status_menikah2 as $status_menikah2_key=>$status_menikah2_val){
					$content=str_replace("{".$status_menikah2_val."}","",$content);
				}
				
			}
			
			if($val['name']=='religion_islam2'){
				$religion_islam2=array(
				'ISLAM'=>'religion_islam2',
				'KRISTEN'=>'religion_kristen2',
				'KATHOLIK'=>'religion_katholik2',
				'BUDHA'=>'religion_budha2',
				'HINDU'=>'religion_hindu2',
				'LAINNYA'=>'religion_lainnya2',
				''=>''
				);
				$content=str_replace("{".$religion_islam2[$val['value']]."}","V",$content);
				unset($religion_islam2[$val['value']]);
				foreach($religion_islam2 as $religion_islam2_key=>$religion_islam2_val){
					$content=str_replace("{".$religion_islam2_val."}","",$content);
				}
				
			}
			
			if ($val['name']=='citizenship_wni2'){
				$citizenship2=array(
				'WNI'=>'citizenship_wni2',
				'WNA'=>'citizenship_wna2',
				''=>''
				);
				$content=str_replace("{".$citizenship2[$val['value']]."}","V",$content);
				unset($citizenship2[$val['value']]);
				foreach($citizenship1 as $citizenship2_key=>$citizenship2_val){
					$content=str_replace("{".$citizenship2_val."}","",$content);
				}
				
			}
			
			if ($val['name']=='citizenship_wni2'){
				$citizenship2=array(
				'WNI'=>'citizenship_wni2',
				'WNA'=>'citizenship_wna2',
				''=>''
				);
				$content=str_replace("{".$citizenship2[$val['value']]."}","V",$content);
				unset($citizenship2[$val['value']]);
				foreach($citizenship2 as $citizenship2_key=>$citizenship2_val){
					$content=str_replace("{".$citizenship2_val."}","",$content);
				}
				
			}
			
			if ($val['name']=='profession2'){
				$profession2=array(
				' KARYAWAN SWASTA'=>'profession_karyawan2',
				'PELAJAR/MAHASISWA'=>'profession_pelajar2',
				'PEGAWAI BUMN/BUMD'=>'profession_pegawai2',
				'LAINNYA'=>'profession_lainnya2',
				'IBU RUMAH TANGGA'=>'profession_irt2',
				'TNI/POLRI'=>'profession_tni2',
				'WIRASWASTA/PENGUSAHA'=>'profession_wiraswasta2',
				'PNS'=>'profession_pns2',
				'PENSIUNAN'=>'profession_pensiunan2',
				'PEJABAT PEMERINTAH/DPR/DPRD'=>'profession_pejabat2',
				''=>''
				);
				
				$content=str_replace("{profession_jelaskan2}",$val['value'],$content);
				
				$profession2[$val['value']]=isset($profession2[$val['value']]) ? $profession2[$val['value']]:"";
				$val['value']=$profession2[$val['value']]!="" ? $val['value']:"LAINNYA";
				$content=str_replace("{".$profession2[$val['value']]."}","V",$content);
				unset($profession2[$val['value']]);
				foreach($profession2 as $profession2_key=>$profession2_val){
					$content=str_replace("{".$profession2_val."}","",$content);
				}
				
				
			}
			
			if($val['name']=='insurance_purpose1'){
				$insurance_purpose1=array(
				'PROTEKSI'=>'insurance_purpose1',
				'INVESTASI'=>'insurance_purpose2',
				'PROTEKSI DAN INVESTASI'=>'insurance_purpose3',
				'LAINNYA'=>'insurance_purpose4',
				''=>''
				);
				$content=str_replace("{".$insurance_purpose1[$val['value']]."}","V",$content);
				unset($insurance_purpose1[$val['value']]);
				foreach($insurance_purpose1 as $insurance_purpose1_key=>$insurance_purpose1_val){
					$content=str_replace("{".$insurance_purpose1_val."}","",$content);
				}
				
			}
			
			if($val['name']=='premium_payer1'){
				$premium_payer1=array(
				'PEMEGANG POLIS'=>'premium_payer1',
				'TERTANGGUNG'=>'premium_payer2',
				'LAINNYA'=>'premium_payer3',
				''=>''
				);
				$content=str_replace("{".$premium_payer1[$val['value']]."}","V",$content);
				unset($premium_payer1[$val['value']]);
				foreach($premium_payer1 as $ipremium_payer1_key=>$premium_payer1_val){
					$content=str_replace("{".$premium_payer1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='income1'){
				$income1=array(
				'GAJI'=>'income1',
				'HASIL INVESTASI'=>'income2',
				'BISNIS PRIBADI'=>'income3',
				'BONUS/KOMISI'=>'income4',
				'LAINNYA'=>'income5',
				''=>''
				);
				$content=str_replace("{".$income1[$val['value']]."}","V",$content);
				unset($income1[$val['value']]);
				foreach($income1 as $income1_key=>$income1_val){
					$content=str_replace("{".$income1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='gross_income1'){
				$gross_income1=array(
				'&lt;60Juta'=>'gross_income1',
				'<60Juta'=>'gross_income1',
				'60Juta-180Juta'=>'gross_income2',
				'180Juta-360Juta'=>'gross_income3',
				'360Juta-600Juta'=>'gross_income4',
				'>600Juta'=>'gross_income5',
				'&gt;600Juta'=>'gross_income5',
				''=>''
				);	
				$content=str_replace("{".$gross_income1[$val['value']]."}","V",$content);
				unset($gross_income1[$val['value']]);
				foreach($gross_income1 as $vincome1_key=>$gross_income1_val){
					$content=str_replace("{".$gross_income1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='assets1'){
				$assets1=array(
				'<100Jt'=>'assets1',
				'100Juta-1Milyar'=>'assets2',
				'>1Milyar-10Milyar'=>'assets3',
				'>10Milyar-100Milyar'=>'assets4',
				'>100Milyar-500Milyar'=>'assets5',
				'>500Milyar'=>'assets6',
				''=>''
				);	
				$content=str_replace("{".$assets1[$val['value']]."}","V",$content);
				unset($assets1[$val['value']]);
				foreach($assets1 as $assets1_key=>$assets1_val){
					$content=str_replace("{".$assets1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='income_peryear1'){
				$income_peryear1=array(
				'<100Jt'=>'income_peryear1',
				'100Juta-500Juta'=>'income_peryear2',
				'>500Juta-1Milyar'=>'income_peryear3',
				'>1Milyar-10Milyar'=>'income_peryear4',
				'>10Milyar'=>'income_peryear5',
				''=>''
				);	
				$content=str_replace("{".$income_peryear1[$val['value']]."}","V",$content);
				unset($income_peryear1[$val['value']]);
				foreach($income_peryear1 as $income_peryear1_key=>$income_peryear1_val){
					$content=str_replace("{".$income_peryear1_val."}","",$content);
				}	
				
			}
			
			if($val['name']=='insurance_purpose8'){
				$insurance_purpose8=array(
				'Proteksi Pendapatan'=>'insurance_purpose8',
				'Proteksi Kredit'=>'insurance_purpose9',
				'Lain-lain'=>'insurance_purpose10',
				''=>''
				);	
				$content=str_replace("{".$insurance_purpose8[$val['value']]."}","V",$content);
				unset($insurance_purpose8[$val['value']]);
				foreach($insurance_purpose8 as $insurance_purpose8_key=>$insurance_purpose8_val){
					$content=str_replace("{".$insurance_purpose8_val."}","",$content);
				}	
				
			}else{
				if(in_array($val['name'],array('date_of_birth1','date_of_birth2'))){
					$content=str_replace("{".$val['name']."}",$this->fix_date($val['value']),$content);
				}else{
					$content=str_replace("{".$val['name']."}",$val['value'],$content);
				}
			}
		}
		/*
		foreach($decode2 as $key=>$val){
			$content=str_replace("{".$key."}",$val,$content);
		}
		*/
		$content=str_replace("{cpp_no_spaj}",$decode2['No_SPAJ'],$content);
		
		$dompdf = new DOMPDF();
		//{IMG_BG}
		$content=str_replace("{IMG_BG}","data:image/png;base64,".base64_encode(file_get_contents(realpath(".")."/template/CPL1.png")),$content);
		$dompdf->load_html($content);
		$dompdf->set_paper("A4", "portrait");
		$dompdf->render();
		$output = $dompdf->output();
		if(!is_dir(realpath(".")."/xtemp/".$rows['spaj_code'])){
			@mkdir(realpath(".")."/xtemp/".$rows['spaj_code']);
		}
		file_put_contents(realpath(".")."/xtemp/".$rows['spaj_code']."/".$rows['spaj_code']."_1.pdf", $output);
		//$dompdf->stream("cpl_page1.pdf",array("Attachment"=>0));
	}
	private function cpl2()
	{
		include(realpath(".").'/application/libraries/phpqrcode/qrlib.php');
		if($this->input->get("id",true)==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir,s.agen_code,p.kode_agen,
				case when k.agency_team_code in('151','078','079') then k.agency_team_name else 'Jakarta' end as nama_kota	
				from tbl_spaj s 
				join tbl_pfa p on p.pfa_code=s.pfa_code
				left join sw_agency_team k on cast(k.agency_team_code as int)=cast(s.branch_code as int)
				where s.ID=?";
		$query=$this->db->query($str,array(base64_decode($this->input->get("id",true))));
		$rows=$query->row_array();
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		
		
		//DATA PENGAJUAN		
		$json1 =$this->other_doc($trx_id,'DATA');
		
		//DATA SPAJ
		$json2 = $this->other_doc($trx_id,'SPAJ');
		$json3 = $this->other_doc($trx_id,'AHLI');
	
		$decode1=json_decode($json1,true);
		$decode2=json_decode($json2,true);
		$decode3=json_decode($json3,true);
		
		$full_name1="";
		$full_name2="";
		
		require_once(realpath(".").'/application/libraries/dompdf/autoload.inc.php');
		ob_start();
		$content=file_get_contents(realpath(".")."/template/cpl_page2.html");
		ob_end_clean();
		foreach($decode1 as $key=>$val){
			if($val['name']=="full_name1"){
			$full_name1=$val['value'];
			}
			
			if($val['name']=="full_name2"){
			$full_name2=$val['value'];
			}

			if ($val['name']=='account_holder_name'){
				if(strlen($val['value'])>34){
					$content=str_replace("{style_".$val['name']."}"," style=\"font-size:6.5pt;top:71pt\" ",$content);
					$content=str_replace("{".$val['name']."}",$val['value'],$content);
				}else{
					$content=str_replace("{style_".$val['name']."}","",$content);
					$content=str_replace("{".$val['name']."}",$val['value'],$content);
				}
				
			}
			
			
			$content=str_replace("{".$val['name']."}",$val['value'],$content);
		}
		foreach ($decode2 as $key=>$val){
			if($key=='Mata_Uang'){
				$currency1=array(
				'IDR'=>'currency1',
				'USD'=>'currency2',
				''=>''
				);	
				$content=str_replace("{".$currency1[$val]."}","V",$content);
				unset($currency1[$val]);
				foreach($currency1 as $currency1_key=>$currency1_val){
					$content=str_replace("{".$currency1_val."}","",$content);
				}

			}
			if($key=='Mata_Uang'){
				$fund_type1=array(
				'IDR'=>'fund_type1',
				'USD'=>'fund_type2',
				''=>''
				);	
				$content=str_replace("{".$fund_type1[$val]."}","V",$content);
				unset($fund_type1[$val]);
				foreach($fund_type1 as $fund_type1_key=>$fund_type1_val){
					$content=str_replace("{".$fund_type1_val."}","",$content);
				}

			}
			
			if($key=='Masa_Target_Investasi'){
				$investment_period1=array(
				'6 Bulan'=>'investment_period1',
				'12 Bulan'=>'investment_period2',
				''=>''
				);	
				$content=str_replace("{".$investment_period1[$val]."}","V",$content);
				unset($investment_period1[$val]);
				foreach($investment_period1 as $investment_period1_key=>$investment_period1_val){
					$content=str_replace("{".$investment_period1_val."}","",$content);
				}	
				
			}
			if($key=='option_at_maturity1'){
				$option_at_maturity1=array(
				'ROLLOVER KESELURUHAN NILAI POLIS'=>'option_at_maturity1',
				'ROLLOVER PREMI SAJA DAN MENARIK TARGET INVESTASI'=>'option_at_maturity2',
				'MENARIK KESELURUHAN NILAI POLIS'=>'option_at_maturity3',
				''=>''
				);	
				$content=str_replace("{".$option_at_maturity1[$val]."}","V",$content);
				unset($option_at_maturity1[$val]);
				foreach($option_at_maturity1 as $option_at_maturity1_key=>$option_at_maturity1_val){
					$content=str_replace("{".$option_at_maturity1_val."}","",$content);
				}	
				
			}else{
				$content=str_replace("{".$key."}",$val,$content);
			}
		}
		$jj=0;
		foreach($decode3 as $bn){
			$jj++;
			foreach ($bn as $key=>$val){
				$content=str_replace("{no".$jj."}",$jj,$content);
				if($key=="nama"){
					$content=str_replace("{beneficiary".$jj."}",$val,$content);
				}
				
				if($key=="hubungan"){
					$content=str_replace("{relationship".$jj."}",$val,$content);
				}
				
				if($key=="pct"){
					$content=str_replace("{benefit".number_format($jj,2)."}",$val,$content);
				}
				
				if($key=="jenis_kelamin"){
					$content=str_replace("{gen".$jj."}",(($val=="PRIA" or $val=="L") ? "L":"P"),$content);
				}
				
				if($key=="tanggal_lahir"){
					$content=str_replace("{birth_date".$jj."}",$this->fix_date($val),$content);
				}
				
				if($key=="tempat_lahir"){
					$content=str_replace("{place".$jj."}",$val,$content);
					
				}
			}
		}
		$content=str_replace("{beneficiary1}","",$content);
		$content=str_replace("{beneficiary2}","",$content);
		$content=str_replace("{beneficiary3}","",$content);
		$content=str_replace("{beneficiary4}","",$content);
		$content=str_replace("{beneficiary5}","",$content);
		$content=str_replace("{beneficiary6}","",$content);
		
		$content=str_replace("{relationship1}","",$content);
		$content=str_replace("{relationship2}","",$content);
		$content=str_replace("{relationship3}","",$content);
		$content=str_replace("{relationship4}","",$content);
		$content=str_replace("{relationship5}","",$content);
		$content=str_replace("{relationship6}","",$content);
		
		$content=str_replace("{benefit1}","",$content);
		$content=str_replace("{benefit2}","",$content);
		$content=str_replace("{benefit3}","",$content);
		$content=str_replace("{benefit4}","",$content);
		$content=str_replace("{benefit5}","",$content);
		$content=str_replace("{benefit6}","",$content);
		
		$content=str_replace("{gen1}","",$content);
		$content=str_replace("{gen2}","",$content);
		$content=str_replace("{gen3}","",$content);
		$content=str_replace("{gen4}","",$content);
		$content=str_replace("{gen5}","",$content);
		$content=str_replace("{gen6}","",$content);
		
		$content=str_replace("{birth_date1}","",$content);
		$content=str_replace("{birth_date2}","",$content);
		$content=str_replace("{birth_date3}","",$content);
		$content=str_replace("{birth_date4}","",$content);
		$content=str_replace("{birth_date5}","",$content);
		$content=str_replace("{birth_date6}","",$content);
		
		$content=str_replace("{place1}","",$content);
		$content=str_replace("{place2}","",$content);
		$content=str_replace("{place3}","",$content);
		$content=str_replace("{place4}","",$content);
		$content=str_replace("{place5}","",$content);
		$content=str_replace("{place6}","",$content);
		
		$content=str_replace("{no1}","",$content);
		$content=str_replace("{no2}","",$content);
		$content=str_replace("{no3}","",$content);
		$content=str_replace("{no4}","",$content);
		$content=str_replace("{no5}","",$content);
		$content=str_replace("{no6}","",$content);
		
		//TTD PEMPOL
		ob_start();
		QRCode::png($full_name1."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_policy_holder_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD TT
		ob_start();
		QRCode::png($full_name2."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_insured_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD PR
		ob_start();
		QRCode::png($rows['agen_code']."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_agen_reff_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD AGEN 
		ob_start();
		QRCode::png($rows['kode_agen']."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_agen_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		$exp_date=explode("-",$rows['effective_dt']);
		
		
		$content=str_replace("{policy_holder_name_ttd}","data:image/png;base64,".$imageString_policy_holder_name_ttd,$content);
		$content=str_replace("{insured_name_ttd}","data:image/png;base64,".$imageString_insured_name_ttd,$content);
		$content=str_replace("{agen_reff_name_ttd}","data:image/png;base64,".$imageString_agen_reff_name_ttd,$content);
		
		
		$content=str_replace("{agen_name_ttd}","data:image/png;base64,".$imageString_agen_name_ttd,$content);
		
		$content=str_replace("{agen_reff_code}",$rows['agen_code'],$content);
		$content=str_replace("{agen_code}",$rows['kode_agen'],$content);
		
		$content=str_replace("{place_of_signature}",$rows['nama_kota'],$content);
		
		$content=str_replace("{date_of_signing}",$exp_date[2]."/".$exp_date[1]."/".$exp_date[0],$content);
		
		
		
		$dompdf = new DOMPDF();
		//{IMG_BG}
		$content=str_replace("{IMG_BG}","data:image/png;base64,".base64_encode(file_get_contents(realpath(".")."/template/CPL2.png")),$content);
		$dompdf->load_html($content);
		$dompdf->set_paper("A4", "portrait");
		$dompdf->render();
		$output = $dompdf->output();
		if(!is_dir(realpath(".")."/xtemp/".$rows['spaj_code'])){
			@mkdir(realpath(".")."/xtemp/".$rows['spaj_code']);
		}
		file_put_contents(realpath(".")."/xtemp/".$rows['spaj_code']."/".$rows['spaj_code']."_2.pdf", $output);
		
		//$dompdf->stream("cpl_page2.pdf",array("Attachment"=>0));
	}
	private function cpl3()
	{
		//include(realpath(".").'/application/libraries/phpqrcode/qrlib.php');
		if($this->input->get("id",true)==""){
			exit;
		}
		
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir,s.agen_code,p.kode_agen,
				case when k.agency_team_code in('151','078','079') then k.agency_team_name else 'Jakarta' end as nama_kota	
				from tbl_spaj s 
				join tbl_pfa p on p.pfa_code=s.pfa_code
				left join sw_agency_team k on cast(k.agency_team_code as int)=cast(s.branch_code as int)
				where s.ID=?";
		$query=$this->db->query($str,array(base64_decode($this->input->get("id",true))));
		$rows=$query->row_array();
		$trx_id=$rows['docdir']."/".$rows['trx_id'];
		
		
		$full_name1="";
		$full_name2="";
		
		
		//DATA SPAJ
		$json2 = $this->other_doc($trx_id,'SPAJ');
	
		$decode1=json_decode($json1,true);
		$decode2=json_decode($json2,true);
		
		foreach($decode1 as $key=>$val){
			if($val['name']=="full_name1"){
			$full_name1=$val['value'];
			}
			
			if($val['name']=="full_name2"){
			$full_name2=$val['value'];
			}
		}

		require_once(realpath(".").'/application/libraries/dompdf/autoload.inc.php');
		ob_start();
		$content=file_get_contents(realpath(".")."/template/cpl_page3.html");
		ob_end_clean();
		/*
		foreach($decode2 as $key=>$val){
			$content=str_replace("{".$key."}",$val,$content);
		}
		*/
		$content=str_replace("{cpp_no_spaj}",$decode2['No_SPAJ'],$content);
		
		
		//TTD PEMPOL
		ob_start();
		QRCode::png($full_name1."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_policy_holder_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD TT
		ob_start();
		QRCode::png($full_name2."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_insured_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD PR
		ob_start();
		QRCode::png($rows['agen_code']."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_agen_reff_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		//TTD AGEN 
		ob_start();
		QRCode::png($rows['kode_agen']."|".$rows['spaj_code']."|".$rows['effective_dt'], null);
		$imageString_agen_name_ttd = base64_encode( ob_get_contents() );
		ob_end_clean();
		
		$exp_date=explode("-",$rows['effective_dt']);
		
		
		$content=str_replace("{policy_holder_name_ttd}","data:image/png;base64,".$imageString_policy_holder_name_ttd,$content);
		$content=str_replace("{insured_name_ttd}","data:image/png;base64,".$imageString_insured_name_ttd,$content);
		$content=str_replace("{agen_reff_name_ttd}","data:image/png;base64,".$imageString_agen_reff_name_ttd,$content);
		
		
		$content=str_replace("{agen_name_ttd}","data:image/png;base64,".$imageString_agen_name_ttd,$content);
		
		$content=str_replace("{agen_reff_code}",$rows['agen_code'],$content);
		$content=str_replace("{agen_code}",$rows['kode_agen'],$content);
		
		$content=str_replace("{place_of_signature}",$rows['nama_kota'],$content);
		
		$content=str_replace("{date_of_signing}",$exp_date[2]."/".$exp_date[1]."/".$exp_date[0],$content);
		
		
		$dompdf = new DOMPDF();
		//{IMG_BG}
		$content=str_replace("{IMG_BG}","data:image/png;base64,".base64_encode(file_get_contents(realpath(".")."/template/CPL3.png")),$content);
		$dompdf->load_html($content);
		$dompdf->set_paper("A4", "portrait");
		$dompdf->render();
		$output = $dompdf->output();
		if(!is_dir(realpath(".")."/xtemp/".$rows['spaj_code'])){
			@mkdir(realpath(".")."/xtemp/".$rows['spaj_code']);
		}
		file_put_contents(realpath(".")."/xtemp/".$rows['spaj_code']."/".$rows['spaj_code']."_3.pdf", $output);
		//$dompdf->stream("cpl_page3.pdf",array("Attachment"=>0));
	}
	
	private function other_doc($q,$type){
		$dir=realpath(".")."/submission/".$q;
		
		$arr="";
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  $split=explode("_",$file);
			  if($split[0]==$type){
				
				$arr=file_get_contents($dir."/".$file);
			  }
			}
			closedir($dh);
		  }
		}
		return $arr;
	}
	
	private function other_doc_fname($q,$type){
		$dir=realpath(".")."/submission/".$q;
		
		$arr="";
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  $split=explode("_",$file);
			  if($split[0]==$type){
				
				$arr=$dir."/".$file;
			  }
			}
			closedir($dh);
		  }
		}
		return $arr;
	}
	
	private function fix_date($s){
		//$exp=explode("-",$str);
		//return $exp[2]."/".$exp[1]."/".$exp[0]; 
		if($s!=""){
			if(preg_match("/\-/i",$s)==true){
				$s=explode("T",$s);
				$s=explode("-",$s[0]);
				return $s[2]."/".$s[1]."/".$s[0];
			}else{
				$m=substr($s, 0, 3);
				$d=substr($s, 4, 2);
				$y=substr($s, 7, 4);
				
				return $d."/".$this->fix_month($m)."/".$y;
			}
		}
	}
	
	private function fix_month($d){
				switch($d){
							case 'Jan':
								return "01";
								break;
							case 'Feb':
								return "02";
								break;
							case 'Mar':
								return "03";
								break;
							case 'Apr':
								return "04";
								break;
							case 'May':
								return "05";
								break;
							case 'Jun':
								return "06";
								break;
							case 'Jul':
								return "07";
								break;
							case 'Aug':
								return "08";
								break;
							case 'Sep':
								return "09";
								break;
							case 'Oct':
								return "10";
								break;
							case 'Nov':
								return "11";
								break;
							case 'Dec':
								return "12";
								break;

						}
			}
	
	public function check_pengajuan(){
		//phpinfo();
		
		//$dbDB = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_INDIVIDU_PROD","admin", "asdasd");
		/*
		$dbDB = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_INDIVIDU_PROD","admin", "asdasd");
		$sql_core="exec SP_API_CPP_3BLN '2024-02-12',460000000,'3','3/12','IDR'";
		$stmt =$dbDB->prepare($sql_core);
		$stmt->execute();	
		$rows = $stmt->fetch(PDO::FETCH_ASSOC);
		echo"<pre>";
		print_r($rows);
		
		exit;
		*/
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir,s.birth_dt from tbl_spaj s where cast(s.effective_dt as date)='2024-02-13'";
		$query=$this->db->query($str);
		$rows=$query->result_array();
		echo"<pre>";
		echo "SPAJ|DOB PP|DOB TT|NAMA PP|NAMA TT|DOB DB|TRXID\n";
		foreach($rows as $row){
			$trx_id=$row['docdir']."/".$row['trx_id'];
			$json1 =$this->other_doc($trx_id,'DATA');
			$decode1=json_decode($json1,true);
			
			//18 -> PP
			//48 -> TT
			//print_r($decode1);
			if($decode1[18]['value']==""){
			echo $row['spaj_code']."|".$decode1[18]['value']."|".$decode1[48]['value']."|".$row['nama_pp']."|".$row['nama_pp']."|".$row['birth_dt']."|".$trx_id."\n";
			}
		}
		
	}	
	
	public function fix_pengajuan(){
		//phpinfo();
		
		//$dbDB = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_INDIVIDU_PROD","admin", "asdasd");
		/*
		$dbDB = new PDO("dblib:host=10.17.50.90:1433;version=7.0;charset=UTF-8;dbname=CAPITALLIFE_INDIVIDU_PROD","admin", "asdasd");
		$sql_core="exec SP_API_CPP_3BLN '2024-02-12',460000000,'3','3/12','IDR'";
		$stmt =$dbDB->prepare($sql_core);
		$stmt->execute();	
		$rows = $stmt->fetch(PDO::FETCH_ASSOC);
		echo"<pre>";
		print_r($rows);
		
		exit;
		*/
		$this->load->database();
		$str="select s.ID,s.trx_id,s.spaj_code,s.no_polis,s.product_code,s.nama_pp,s.effective_dt,s.last_change_dt,s.status_qc,
				DATE_FORMAT(s.last_change_dt,'%Y%m%d') as docdir,s.birth_dt from tbl_spaj s where cast(s.effective_dt as date)='2024-02-12' and s.spaj_code in(
				'PP11405214',
				'PP11415362',
				'PP11403425',
				'PP11424310',
				'PP11414961',
				'PP11384876',
				'PP11372174',
				'PP11396303',
				'PP11417271',
				'PP11393424',
				'PP11413684',
				'PP11422517',
				'PP11429855',
				'PP11396332',
				'PP11393425',
				'PP11419360',
				'PP11393478',
				'PP11396333',
				'PP11365587',
				'PP11393499',
				'PP11422514',
				'PP11422585',
				'PP11371459',
				'PP11411823',
				'PP11402157',
				'PP11402159'
				)";
		$query=$this->db->query($str);
		$rows=$query->result_array();
		echo"<pre>";
		foreach($rows as $row){
			$trx_id=$row['docdir']."/".$row['trx_id'];
			$json1 =$this->other_doc($trx_id,'DATA');
			$decode1=json_decode($json1,true);
			
			//$decode1[18]['value']=$row['birth_dt'];
			//$decode1[48]['value']=$row['birth_dt'];
			//file_put_contents($this->other_doc_fname($trx_id,'DATA'),json_encode($decode1));
			
			
			echo $this->other_doc_fname($trx_id,'DATA')."\n";
			//18 -> PP
			//48 -> TT
			print_r($decode1);
			
		}
		
	}	
	
}