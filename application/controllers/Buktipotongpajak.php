<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Buktipotongpajak extends CI_Controller {

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
		
		
		
		$data=$this->input->post("periode",true);
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
		$agen['info']['agen_name']=isset($agen['info']['agen_name']) ? $agen['info']['agen_name']:"";
		
		
		if($agen['info']['agen_code']=="" || $data==""){
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Parameter',
			'info'=>false
			));
			exit;
		}
		
			/*
			echo json_encode(array(
			'error'=>1,
			'message'=>'Invalid Parameter',
			'info'=>false
			));
			exit;*/
			
		
		//sentmailcurl($receiver,$subject,$body,$filename,$filepath)
		try{
			$email_agent=$this->email_agen($agen['info']['agen_code']);
			
			$query=$this->db->query("select * from tbl_agen_bukti_potong_pajak where NO_AGEN='".$agen['info']['agen_code']."' and concat(periode_tahun,periode_bulan)='".$data."' order by create_date desc");
			$row=$query->result_array();
			
			if(count($row)>0){
					
					$pdf_array=array();
					$path="";
					$body=file_get_contents(realpath(".")."/template/bukti_potong_pajak_pr.html");
					
					require_once(realpath(".").'/application/libraries/dompdf/autoload.inc.php');
					require_once(realpath(".").'/application/libraries/PDFMerger/PDFMerger.php');
					$pdf = new \PDFMerger;
					foreach($row as $rs){
						$path="/home/bpp_prbci/".$rs['periode_tahun']."/".$rs['periode_bulan'];
						//$receiver="paulus.yunior@capitallife.co.id"; //Dede Suryanda <dede.suryanda@capitallife.co.id>
						$receiver=$email_agent;
						$subject="Surat Bukti Potong Pajak Periode: ".$rs['periode_bulan'].'/'.$rs['periode_tahun'];
						$body=str_replace("{MARKETER_NAME}",$rs['NAMA_AGEN'],$body);
						$body=str_replace("{PERIODE}",$rs['periode_bulan'].'/'.$rs['periode_tahun'],$body);
						$filename="Bukti_Potong_Pajak_".$rs['periode_bulan']."_".$rs['periode_tahun']."_".$agen['info']['agen_code'].".pdf";
						
						//$pdf_array[]=$path.'/'.$rs['filename'];
						if(!file_exists(realpath(".").'/xtemp/'.$filename)){
							$pdf->addPDF($path.'/'.$rs['filename']);
						}
					}
					
					/*
					$pdf = new Imagick($pdf_array);
					$pdf->setImageFormat('pdf');
					$pdf->writeImages(realpath(".").'/xtemp/'.$filename, true);
					$filepath=realpath(".").'/xtemp/'.$filename;
					*/
					$filepath=realpath(".").'/xtemp/'.$filename;
					if(!file_exists($filepath)){
						$pdf->merge('file', $filepath); 
						
					}
					$this->sentmailcurl($receiver,$subject,$body,$filename,$filepath);
					
					$this->db->query("insert tbl_agen_bukti_potong_pajak_history(NO_AGEN,NAMA_AGEN,EMAIL_AGEN,NAMA_CABANG,BBC,NPWP,periode_tahun,periode_bulan,create_date,create_by)
									select '".$row[0]['NO_AGEN']."',
											'".$row[0]['NAMA_AGEN']."',
											'".$row[0]['EMAIL_AGEN']."',
											'".$row[0]['NAMA_CABANG']."',
											'".$row[0]['BBC']."',
											'".$row[0]['NPWP']."',
											'".$row[0]['periode_tahun']."',
											'".$row[0]['periode_bulan']."',NOW(),'".$agen['info']['agen_name']."'");
					
					echo json_encode(array(
					'error'=>0,
					'message'=>'success',
					'data'=>$row
					));
					
			}else{
					echo json_encode(array(
					'error'=>1,
					'message'=>'Failed process your request',
					'data'=>$row
					));			
			}	
		
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
