<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Monitordrc extends CI_Controller {

	public function index()
	{
		if($this->getBearerToken()==true){
			$this->load->database();
			
			$data=$this->input->post("data");
			if($data!=""){
				$data=json_decode($data,true);
				foreach($data as $rs){
					$this->db->query("
					update daily_check.dbmonitor set
					last_restore_date='".$rs['last_restore_date']."',
					case_count_drc='".$rs['total']."',
					modify_date=now(),
					modify_by='system'
					where db_name='".$rs['dbname']."' and sent_date='".$rs['sent_date']."'
					");
				}
			}
		}
	}
	
	 /* Get header Authorization
	 * */
	private function getAuthorizationHeader(){
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		}
		else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			//print_r($requestHeaders);
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	/**
	 * get access token from header
	 * */
	private function getBearerToken() {
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}
