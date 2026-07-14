<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Portofolio extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		
		
		if(!$_POST){
			exit;
		}
		
		$hd=$this->input->request_headers();
		
		$hd['Origin']=isset($hd['Origin']) ? $hd['Origin']:"";
		
		if($hd['Origin']!="http://localhost:3000" and $hd['Origin']!="https://deviasi2.capitallife.co.id" and $hd['Origin']!="http://deviasi2.capitallife.co.id"
		and $hd['Origin']!="https://sicepot.capitallife.co.id" and $hd['Origin']!="https://sicepotuat.capitallife.co.id" 
		){
			echo json_encode(array(
				'error'=>1,
				'message'=>'Invalid Access',
				'info'=>false
				));
				exit;
		}
		
		
	}
	
	public function index()
	{
		
		
		
		$data=$this->input->post("data",true);
		$agen=$this->input->post("agen",true);
		
		if($data=="" or $agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access',
			'info'=>false
			));
			exit;
		}
		
		$data=json_decode(base64_decode($data),true);
		$agen=json_decode(base64_decode($agen),true);
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		$data['nama_pp']=isset($data['nama_pp']) ? $data['nama_pp']:"";
		$data['dob_pp']=isset($data['dob_pp']) ? $data['dob_pp']:"";
		
		if($agen['info']['agen_code']=="" || $data['nama_pp']=="" || $data['dob_pp']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Parameter',
			'info'=>false
			));
			exit;
		}
		
		try{
			$agen_email=$this->email_agen($agen['info']['agen_code']);
			
			
			$dbDB =null;
			$stmts=null;			
			
			$dbDB = new PDO("mysql:host=10.17.44.253;port=3306;dbname=db_cli", "root", "Elea@2023");
			
			//check hari kerja
			$stmts = $dbDB->prepare("
			select 
			NAMA_PEMEGANG_POLIS,fnExcelDate(TGL_LAHIR_PEMEGANG_POLIS) as TGL_LAHIR_PEMEGANG_POLIS,TGL_LAHIR_PEMEGANG_POLIS as TGL_LAHIR_PEMEGANG_POLIS_ORI,
			PERIOD,NAMA_AGEN,CIF_PEMPOL,NO_SURAT,min(NAMA_KANTOR_CABANG) as NAMA_KANTOR_CABANG
			from laporan_pajak
			where NO_AGEN='".$agen['info']['agen_code']."' and cast(fnExcelDate(TGL_LAHIR_PEMEGANG_POLIS) as date)='".$data['dob_pp']."' 
			and NAMA_PEMEGANG_POLIS like'%".$data['nama_pp']."%'
			group by NAMA_PEMEGANG_POLIS,TGL_LAHIR_PEMEGANG_POLIS,PERIOD,NAMA_AGEN,CIF_PEMPOL,NO_SURAT
			order by NAMA_PEMEGANG_POLIS asc limit 1
			");
			$stmts->execute();
			$rows=$stmts->fetchAll(PDO::FETCH_ASSOC);
			
			$rows_nama_pp=isset($rows[0]['NAMA_PEMEGANG_POLIS']) ? $rows[0]['NAMA_PEMEGANG_POLIS']:"";
			$rows_dob_pp=isset($rows[0]['TGL_LAHIR_PEMEGANG_POLIS_ORI']) ? $rows[0]['TGL_LAHIR_PEMEGANG_POLIS_ORI']:"";
			$rows_period_pp=isset($rows[0]['PERIOD']) ? $rows[0]['PERIOD']:"";
			$rows_agen_pp=isset($rows[0]['NAMA_AGEN']) ? $rows[0]['NAMA_AGEN']:"";
			
			if($rows_nama_pp!=""){
				$new_file_real_name="Portofolio_Polis_".$this->clean_spc($rows_nama_pp)."_".$rows_period_pp."_".time().".pdf";
				$new_file_name = realpath(".")."/xtemp/".$new_file_real_name;
				$url = "http://10.17.44.32:82/pajak/index _2023.php?key=".urlencode($rows_nama_pp.$rows_dob_pp);

				$temp_file_contents = $this->collect_file($url);
				
				$this->write_to_file($temp_file_contents,$new_file_name);
				
				
				//$receiver="paulus.yunior@capitallife.co.id;nurcahya.ilmiawan@capitallife.co.id;dede.suryanda@capitallife.co.id";//Nurcahya Ilmiawan <nurcahya.ilmiawan@capitallife.co.id>; Dede Suryanda <dede.suryanda@capitallife.co.id>
				$receiver=$agen_email;
				$subject="Surat Portofolio Polis Nasabah an  ".$rows_nama_pp." Periode: ".$rows_period_pp;
				$body="Dummy Surat Lapor Pajak ".time();
				$filename=$new_file_real_name;
				$filepath=$new_file_name;
				
				
				$body=file_get_contents(realpath(".")."/template/policy_notification_portofolio.html");
				$body=str_replace("{MARKETER_NAME}",$rows_agen_pp,$body);
				$body=str_replace("{POLICY_HOLDER_NAME}",$rows_nama_pp,$body);
				$body=str_replace("{PERIODE}",$rows_period_pp,$body);
				
				
				$this->sentmailcurl($receiver,$subject,$body,$filename,$filepath);
				
				//keep history
				$iNO_AGEN=$agen['info']['agen_code'];
				$iNAMA_AGEN=$rows[0]['NAMA_AGEN'];
				$iNAMA_CABANG_AGEN=$rows[0]['NAMA_KANTOR_CABANG'];
				$iEMAIL_AGEN=$this->email_agen($agen['info']['agen_code']);
				$iNAMA_PEMPOL=$rows[0]['NAMA_PEMEGANG_POLIS'];
				$iDOB_PEMPOL=$rows[0]['TGL_LAHIR_PEMEGANG_POLIS'];
				$iCIF_PEMPOL=$rows[0]['CIF_PEMPOL'];
				$iNO_SURAT=$rows[0]['NO_SURAT'];
				$iPERIODE=$rows[0]['PERIOD'];
				
				
				$this->db->query("call usp_agen_portofolio_history_i(
				'$iNO_AGEN',
				'$iNAMA_AGEN',
				'$iNAMA_CABANG_AGEN',
				'$iEMAIL_AGEN',
				'$iNAMA_PEMPOL',
				'$iDOB_PEMPOL',
				'$iCIF_PEMPOL',
				'$iNO_SURAT',
				'$iPERIODE'
				)");
			}
			
			echo json_encode(array(
			'error'=>0,
			'message'=>'Success',
			'data'=>$rows
			));
			exit;
		
		}catch(Exception $er){
			echo $er->getMessage();

		}
		
	}
	
	public function report(){
		$agen=$this->input->post("agen",true);
		
		if($agen==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Access',
			'info'=>false
			));
			exit;
		}
		
		
		$agen=json_decode(base64_decode($agen),true);
		
		$agen['info']['agen_code']=isset($agen['info']['agen_code']) ? $agen['info']['agen_code']:"";
		if($agen['info']['agen_code']==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Parameter',
			'info'=>false
			));
			exit;
		}
		
		$query=$this->db->query("select * from tbl_agen_portofolio_history where NO_AGEN='".$agen['info']['agen_code']."' order by create_date desc");
		$row=$query->result_array();
		echo json_encode(array(
			'error'=>0,
			'message'=>'success',
			'data'=>$row
			));
	}
	
	private function email_agen($kode_agen){
		$query=$this->db->query("select email from dc_intermediary where intermediary_code='".$kode_agen."'");
		$row=$query->row_array();
		return isset($row['email']) ? $row['email']:""; 
	}
	
	private function collect_file($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_REFERER, "https://www.google.com");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return($result);
    }

    private function write_to_file($text,$new_filename){
        $fp = fopen($new_filename, 'w');
        fwrite($fp, $text);
        fclose($fp);
    }
	
	private function sentmailcurl($receiver,$subject,$body,$filename,$filepath){
			$data=array(
				'subject'=>base64_encode($subject),
				'body'=>base64_encode($body),
				'receiver'=>base64_encode($receiver),
				'name'=>base64_encode($filename),
				'file'=>base64_encode($filepath),
			);
			
			$url="http://127.0.0.1:81/sentmailfilenoreply.php";
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$response = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);
			if ($error !== '') {
				throw new \Exception($error);
			}

			return $response;
	}
	private function clean_spc($string) {
		   $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

		   return preg_replace('/[^A-Za-z0-9\-\_]/', '_', $string); // Removes special chars.
		}
}
