<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Dwh extends CI_Controller {
	private $login_type="";
	private $login_pass="";
	private $ID_department="";
	private $level="";
	private $full_name="";
	
	public function index()
	{
		exit;
	}
	
	private function login_validate($username,$password){
		$username=($username=="paulus.loe" ? "paulus.yunior":$username);
		$this->login_type="";
		$this->login_pass="";
		try{
			$this->conn = new PDO("mysql:host=10.17.44.32;port=3306;dbname=db_helpdesk", "root", "Dwh@2018");
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 $stmt = $this->conn->prepare("select count(1) as total,min(login_type) as login_type,min(`password`) as `password`,min(ID_department) as ID_department,min(level) as level,min(full_name) as full_name from db_helpdesk.staff where 
			 Username='".$username."' 
			 and delete_date is null limit 1");
			 $stmt->execute();
			 $row=$stmt->fetch();
			$this->login_type=$row['login_type'];
			$this->login_pass=$row['password'];
			$this->ID_department=$row['ID_department'];
			$this->level=$row['level'];
			$this->full_name=$row['full_name'];
			return isset($row['total']) ? $row['total']:0;
		}catch(PDOException $e){
			echo "Error : " . $e->getMessage();
			die();
		}
		
	}
	
	public function login_user(){
		$server_ldap = '10.17.44.21';
		$server_ldap_domain = 'capitallife.local';

		$username=$this->input->post("username",true);
		$password=$this->input->post("password",true);
		$array=array("error"=>1,"message"=>"Invalid Login");
		
		if($this->login_validate($username,$password)<=0){
			echo json_encode(array("error"=>1,"message"=>"Invalid Login"));
			exit;
		}
		
		$ldap = ldap_connect($server_ldap);
		$array=array("error"=>1,"message"=>"");
		$bind=false;
		$message="";
		
		if($this->login_type=="LDAP"){
			try{
				$bind = ldap_bind($ldap, $username."@".$server_ldap_domain, $password);	
			}catch(Exception $er){
				$message=$er->getMessage();	
			}
		}else{
			if($this->login_pass!=md5($password)){
				$bind=false;
			}else{
				$bind=true;
			}
		}

		if($bind){
		  // log them in!
		  $array=array("error"=>0,"message"=>"Login Success",'username'=>$username,'full_name'=>$this->full_name,'dept'=>$this->ID_department,'level'=>$this->level);
		}else{
		  // error message
		  $array=array("error"=>1,"message"=>"Login Failed (".$this->login_type.")",'username'=>$username,'full_name'=>$this->full_name,'dept'=>$this->ID_department,'level'=>$this->level);
		}
		echo json_encode($array);
	}
}
