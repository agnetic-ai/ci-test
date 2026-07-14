<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Backofficedownloadalldoc extends CI_Controller {
	public function index()
	{
		$ip_client=$this->input->ip_address();
		if(substr($ip_client,0,5)!="10.17"){
			echo"Access Denied";
			exit;
		}
		
		$spaj=trim($this->input->get("spaj",true));
		$q=trim($this->input->get("trx_id",true));
		if($spaj=="" || $q==""){
			echo"Invalid request.";
			exit;
		}
		$arr_f=$this->doclist($q);
		$dir=realpath(".")."\\submission\\".$q;
		if(!is_dir($dir)){
			echo"File not found";
			exit;
		}
		$zipname = $spaj.'_'.date("YmdHis").'.zip';
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);
		foreach ($arr_f as $key=>$val) {
		  if($key!="SPAJ" && $key!="AHLI" && $key!="ALAMAT" && $key!="REKENING"){
			$zip->addFromString($val,  file_get_contents($dir."\\".$val));
		  }
		}
		
		$zip->close();
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
		
	}
	
	private function doclist($q){
		$dir=realpath(".")."/submission/".$q;
		
		$arr=array();
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  $split=explode("_",$file);
			  if($file!="." && $file!=".." && $split[0]!="Thumbs.db" && $split[0]!="Cart"){
				
				$arr[$split[0]]=$file;
			  }
			}
			closedir($dh);
		  }
		}
		return $arr;
	}
}
