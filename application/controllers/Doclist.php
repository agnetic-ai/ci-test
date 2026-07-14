<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Doclist extends CI_Controller {

	
	public function index()
	{
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q");
		if($q=="") die("Access Denied");
		$q=base64_decode($_POST['q']);
		
		$arr=array();
		$q=str_replace("\\","/",$q);
		$dir=realpath(".")."/".$q;
		//exit($dir);
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  if($file!="." && $file!=".."){
				$split=explode("_",$file);
				if($split[0]!="SPAJ"  && $split[0]!="ILUSTRASI" && $split[0]!="Thumbs.db" && $split[0]!="Cart" && $split[0]!="AHLI" && $split[0]!="REKENING" && $split[0]!="ALAMAT"){
					$arr[$split[0]]=$file;
				}
			  }
			}
			closedir($dh);
		  }
		}else die("Invalid Directory Access");
		
		echo json_encode($arr);
	}
	
	public function show()
	{
		
		//$q=$this->input->get("q");
		$q=isset($_GET['q']) ? $_GET['q']:"";
		if($q=="") die("Access Denied");
		$q=base64_decode($q);
		
		$arr=array();
		$q=str_replace("\\","/",$q);
		$dir=realpath(".")."/".$_GET['q'];;
		//echo $_GET['q'];
		//exit;
		//print_r(realpath(".")."/".$_GET['q']);
		//exit($dir);
		
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  if($file!="." && $file!=".."){
				$split=explode("_",$file);
				if($split[0]!="SPAJ"  && $split[0]!="ILUSTRASI" && $split[0]!="Thumbs.db" && $split[0]!="Cart" && $split[0]!="AHLI" && $split[0]!="REKENING" && $split[0]!="ALAMAT"){
					$arr[$split[0]]=$file;
				}
			  }
			}
			closedir($dh);
		  }
		}else die("Invalid Directory Access");
		
		echo json_encode($arr);
	}
	
	public function other_doc(){
		
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q",true);
		$type=$this->input->post("type",true);
		if($q=="") die("Access Denied");
		$q=base64_decode($q);
		
		$arr="";
		$q=str_replace("\\","/",$q);
		$dir=realpath(".")."/".$q;
		//exit($dir);
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  if($file!="." && $file!=".."){
				$split=explode("_",$file);
				if($split[0]==$type){
					$arr=file_get_contents($dir.'/'.$file);
				}
			  }
			}
			closedir($dh);
		  }
		}else die("Invalid Directory Access");
		
		echo $arr;
	}
	
	
	public function doc_pengajuan(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q",true);
		$str="select t.* from tbl_product_doc d
join tbl_doc_type t on t.doc_type_nmbr=d.doc_type_nmbr
where d.product_code=? and d.is_mandatory=1 and d.doc_type_nmbr not in('D004','D007','D008','D009','D010','D011','D012','D013')";
		$query=$this->db->query($str,array($q));
		$rows=$query->result_array();
		echo json_encode($rows);
	}
	public function other_doc_pengajuan(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q",true);
		$str="select t.* from tbl_product_doc d
join tbl_doc_type t on t.doc_type_nmbr=d.doc_type_nmbr
where d.product_code=? and coalesce(d.is_mandatory,0)=0 and d.doc_type_nmbr not in('D004','D007','D008','D009','D010','D011','D012','D013')";
		$query=$this->db->query($str,array($q));
		$rows=$query->result_array();
		echo json_encode($rows);
	}
	
	public function all_doc_pengajuan(){
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q",true);
		$str="select t.* from tbl_product_doc d
join tbl_doc_type t on t.doc_type_nmbr=d.doc_type_nmbr
where d.product_code=? and d.doc_type_nmbr not in('D004','D007','D008','D009','D010','D011','D012','D013')";
		$query=$this->db->query($str,array($q));
		$rows=$query->result_array();
		echo json_encode($rows);
	}

	public function get_old_file()
	{
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q");
		if($q=="") die("Access Denied");
		$q=base64_decode($_POST['q']);
		
		$arr=array();
		$q=str_replace("\\","/",$q);
		//$dir=realpath(".")."/".$q;
		$dir=$q;
		//exit($dir);
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
						while (($file = readdir($dh)) !== false){
						  if($file!="." && $file!=".."){
									$split=explode("_",$file);

									if($split[0]!="SPAJ"  && $split[0]!="ILUSTRASI" && $split[0]!="Thumbs.db" && $split[0]!="Cart" && $split[0]!="AHLI" && $split[0]!="REKENING" && $split[0]!="ALAMAT"){
									$arr[$split[0]]=file_get_contents($dir.'/'.$file);;
								}
					  }
						}
						closedir($dh);
		  }
		}else die("Invalid Directory Access");
		
		echo json_encode($arr);
	}
	
	public function get_old_file2()
	{
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q");
		if($q=="") die("Access Denied");
		$q=base64_decode($_POST['q']);
		
		$arr=array();
		$q=str_replace("\\","/",$q);
		//$dir=realpath(".")."/".$q;
		$dir=$q;
		//exit($dir);
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
						while (($file = readdir($dh)) !== false){
						  if($file!="." && $file!=".."){
									$split=explode("_",$file);

									if($split[0]!="SPAJ"  && $split[0]!="ILUSTRASI" && $split[0]!="Thumbs.db" && $split[0]!="Cart" && $split[0]!="AHLI" && $split[0]!="REKENING" && $split[0]!="ALAMAT"){
									$arr[$split[0]]=$file;
								}
					  }
						}
						closedir($dh);
		  }
		}else die("Invalid Directory Access");
		
		echo json_encode($arr);
	}
	
	public function other_doc2(){
		
		if($_SERVER['REQUEST_METHOD']!=="POST") die("Invalid Request");
		$q=$this->input->post("q",true);
		$type=$this->input->post("type",true);
		if($q=="") die("Access Denied");
		$q=base64_decode($q);
		
		$arr="";
		$q=str_replace("\\","/",$q);
		//$dir=realpath(".")."/".$q;
		$dir=$q;
		//exit($dir);
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  if($file!="." && $file!=".."){
				$split=explode("_",$file);
				if($split[0]==$type){
					$arr=file_get_contents($dir.'/'.$file);
				}
			  }
			}
			closedir($dh);
		  }
		}else die("Invalid Directory Access");
		
		echo $arr;
	}
	
}
