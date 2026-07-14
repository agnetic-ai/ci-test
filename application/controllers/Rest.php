<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rest extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/json');
		echo json_encode($this->config->item('api_version'));
		exit;
	}
	
}
