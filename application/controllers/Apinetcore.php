<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apinetcore extends CI_Controller {

	public function index()
	{
		try{
	
	
		$dbDB =null;
		$stmts=null;
		$db = new PDO("mysql:host=10.17.44.32;port=3306;dbname=db_reminder", "root", "Dwh@2018");
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$ip="";
		$postdata = file_get_contents("php://input");
		/*
		if ($_SERVER['HTTP_X_FORWARDED_FOR']){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} 
		else{ 
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		*/
		
		$decode=json_decode($postdata,true);
		if(count($decode)<=0){
			exit;
		}
		foreach($decode['delivery_status'] as $rs){
			$message_id=$rs['ncmessage_id'];
			$message_status=$rs['status'];
			$stmt = $db->prepare("insert tbl_history(desc_header,desc_response,message_id,message_status,is_process,last_change_date) values('".$ip."','$postdata','$message_id','$message_status',null,NOW())");
			$stmt->execute();
		}
		
		
		}catch(Exception $er){
			
		}
		
	}
	
	public function incoming2(){
		try{
	
	
		$dbDB =null;
		$stmts=null;
		$db = new PDO("mysql:host=10.17.44.32;port=3306;dbname=db_reminder", "root", "Dwh@2018");
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		$postdata = file_get_contents("php://input");
		if ($_SERVER['HTTP_X_FORWARDED_FOR']){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} 
		else{ 
			$ip = $_SERVER['REMOTE_ADDR'];
		}
			$decode=json_decode($postdata,true);
			
			
			$default_message="Hi selamat datang di PT Capital Life Indonesia, Info lebih lanjut silahkan hubungi Layanan Pelanggan Capital Life Indonesia pada jam operasional kantor pukul 08.30 WIB – 17.30 WIBTelp. : 021 - 2277 3898 Fax : 021 - 2277 3897 Email : care@capitallife.co.id Website : http://www.capitallife.co.id/";
			
				
		
				$message_id=$decode['incoming_message'][0]['message_id'];
				$message_status=$decode['incoming_message'][0]['received_at'];
				$phone=$decode['incoming_message'][0]['from'];
				$message_desc=$decode['incoming_message'][0]['text_type']['text'];
				$stmt = $db->prepare("insert tbl_incoming(desc_header,desc_response,message_id,message_status,is_process,last_change_date,phone,message_desc) values('".$response."','$postdata','$message_id','$message_status',null,NOW(),'$phone','$message_desc')");
				$stmt->execute();
		
		}catch(Exception $er){
			
		}
	}
	
	public function incoming(){
		try{
	
	
		$dbDB =null;
		$stmts=null;
		$db = new PDO("mysql:host=10.17.44.32;port=3306;dbname=db_reminder", "root", "Dwh@2018");
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		$postdata = file_get_contents("php://input");
		if ($_SERVER['HTTP_X_FORWARDED_FOR']){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} 
		else{ 
			$ip = $_SERVER['REMOTE_ADDR'];
		}
			$decode=json_decode($postdata,true);
			
			
			$default_message="Terima kasih telah menghubungi PT Capital Life Indonesia. Kami informasikan pada saat ini layanan WhatsApp PT Capital Life Indonesia terbatas pada pemberian notifikasi kepada Bapak/Ibu. Informasi lebih lanjut berkenaan dengan Produk atau Polis Bapak/Ibu dapat menghubungi kami secara langsung di 021-22773898 atau e-mail ke care@capitallife.co.id atau menghubungi Tenaga Pemasar kami. Kami ucapkan terima kasih atas kepercayaan Bapak dan Ibu memilih Capital Life sebagai mitra perlindungan Bapak dan Ibu.";
			
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => "https://waapi.pepipost.com/api/v2/message/",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => "{\n\t\"message\" : [{\n\t\"recipient_whatsapp\" : ".$decode['incoming_message'][0]['from'].",\n\t\"message_type\" : \"text\",\n\t\"recipient_type\" : \"individual\",\n\t\"source\" : \"461089f9-1000-4211-b182-c7f0294f3d55\",\n\t\"x-apiheader\" : \"custome_data\",\n\t\"type_text\" : [{\n\t\t\"preview_url\" : \"false\",\n\t\t\"content\" :\"".$default_message."\"\n\t\t}]\n\t}]\n}",
				  CURLOPT_HTTPHEADER => array(
					"accept: */*",
					"accept-encoding: gzip, deflate",
					"authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJjYXBpdGFsbGlmZXdoYXRzYXBwIiwiZXhwIjoyNDQxMTAwNzc1fQ.pLXciPwFxzEasEVgBe-QZa6-lejHboVYPTHPug585wtA5G3Tnk4zSvLKlyB0m54opWG6g3ust_aOrWlnRy3ocw",
					"cache-control: no-cache",
					"content-type: application/json",
					"postman-token: cff3bf72-387e-2ed2-3e19-20bff740ebed"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {
				  $response="cURL Error #:" . $err;
				} else {
				  //nothing
				}
		
				$message_id="";
				$message_status="";
				$stmt = $db->prepare("insert tbl_incoming(desc_header,desc_response,message_id,message_status,is_process,last_change_date) values('".$response."','$postdata','$message_id','$message_status',null,NOW())");
				$stmt->execute();
		
		}catch(Exception $er){
			
		}
	}
	
	private function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
}
