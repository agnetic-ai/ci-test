<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pushnotification extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{
		
		// Set POST variables
		$firebase_token=$this->input->post("fcm_token",true);
		$notification = array();
		$notification['title'] = $this->input->post("title",true);
		$notification['body'] = $this->input->post("message",true);
		$notification['sound'] = 'Default';
		$notification['image'] = $this->input->post("image",true);
		
		
		$url = 'https://fcm.googleapis.com/fcm/send';
		$firebase_api='AAAAClLhVkg:APA91bGtLnKKNsUHA8ITktSBIs7Ryvd3FbrVvARmVWCtEapzshf68W8581NOFjGwKXIleOJkNWrh3wYyQPcRA-YmmivPwqRvo9ZdSz1fHncLmAj8ptkcpbGHzOJskwMPuylJ3e2PXZap';
		$headers = array(
			'Authorization: key=' . $firebase_api,
			'Content-Type: application/json'
		);
		
		$fields = array(
            'registration_ids' =>array($firebase_token),
            'priority' => 'high',
            'notification' => $notification,
			'data'=>array(
				"title"=>$notification['title'],   //Any value 
				"message"=>$notification['body'],
			)
        );
		
		$ch = curl_init();
 
		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarily
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}
		echo"Success sent your data, ".$result."<br>".json_encode($fields);
		// Close connection
		curl_close($ch);
	}
	
}
