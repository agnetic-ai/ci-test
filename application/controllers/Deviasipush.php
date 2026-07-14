<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Bangkok");
class Deviasipush extends CI_Controller {

	public function index()
	{
		
		if(php_sapi_name() === 'cli'){
		$loop=$this->db->query("select * from tbl_agen_webpush_hst where coalesce(status_sent,0)=1 and sent_date is null order by last_change_dt asc limit 10");
		$rows=$loop->result_array();
		
		foreach($rows as $row){
					$curl = curl_init();
					curl_setopt_array($curl, array(
					  CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_POSTFIELDS => "{\"to\":\"".$row['fcm_token']."\",\"notification\":{\"title\":\"".$row['title']."\",\"body\":\"".$row['body']."\"}}",
					  CURLOPT_HTTPHEADER => array(
						"accept: */*",
						"accept-encoding: gzip, deflate",
						"cache-control: no-cache",
						"content-type: application/json",
						"Authorization: key=AAAARXXEqQ0:APA91bHyfMAsIL1uv1YPfpQeXi2dPqPstbc3eAMihDZy47PScgBMdwCevCzOHbZkHIv73FKdZMHWZarb0d3WO-Sajj1A9U-qu4Mx8VsUYbohcveHMe5M0QsqBXQBr14MYiYfCy9OgydZ"
					  )
					));

					$opin = curl_exec($curl);
					$err = curl_error($curl);
					var_export($opin);
					curl_close($curl);
					$curl=null;
					$this->db->query("update tbl_agen_webpush_hst set sent_date=NOW() where ID=".$row['ID']);
		}
		}
	}
}
