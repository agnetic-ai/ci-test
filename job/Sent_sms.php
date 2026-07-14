<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1); 
ini_set('memory_limit',-1);
ini_set('max_execution_time', -1);
date_default_timezone_set("Asia/Jakarta");
//echo ceil(date("H"))." - ".date("a");
//exit;
if(ceil(date("H"))<10){
	exit("denied");
}

try{
	$dbDB =null;
	$stmts=null;
	$db = new PDO('mysql:host=127.0.0.1:3306;dbname=dbsil;charset=utf8mb4', 'root', 'Dwh@2018');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$q="SELECT ID,sms_dest,sms_body,st_approve_date FROM tbl_sms_hst WHERE COALESCE(st_sent,0)=0 and cast(st_approve_date as date)<=cast(NOW() as date) and sms_limit>0";
	$stmt = $db->prepare($q);
	$stmt->execute();
	$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $rs){
		$curl = curl_init();
		$username="capital";
		$password="capital123";
		$message=$rs['sms_body'];
		$no_hp=trim($rs['sms_dest']);
		curl_setopt_array($curl, array(
		  CURLOPT_PORT => "8018",
		  CURLOPT_URL => "http://monitor.valdo-intl.com:8018/smsblast/nbuw",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 60,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"username\"\r\n\r\n".$username."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n".$password."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"message\"\r\n\r\n".$message."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"no_hp\"\r\n\r\n".$no_hp."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
			"postman-token: c6cdee33-2f9a-5462-31d7-48324674e4bd",
			"Token: ".sha1(date("d-m-Y"))
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  $db->query("UPDATE tbl_sms_hst SET st_sent=0,st_sent_date=NOW(),st_sent_by='BATCH',
st_reponse='".$err."',st_reponse_dt=NOW(),st_reponse_success=null,st_response_fail=1 
WHERE ID=".$rs['ID']);
			echo $rs['ID']." ".$err." \n";
		} else {
		  $sts=json_decode($response,true);	
		  $db->query("UPDATE tbl_sms_hst SET st_sent=".($sts['status']===true ? 1:0).",st_sent_date=NOW(),st_sent_by='BATCH',
st_reponse='".$response."',st_reponse_dt=NOW(),st_reponse_success=".($sts['status']===true ? 1:0).",st_response_fail=".($sts['status']===true ? 0:1).",sms_limit=(sms_limit-1) 
WHERE ID=".$rs['ID']);
		  echo $rs['ID']." ".$response." ".date("d-m-Y H:i:s a")." \n";
		}
	}
}catch(Exception $er){
	

}

/*
$curl = curl_init();
$username="capital";
$password="capital123";
$message="Nsbh Yth. Polis 1206180001, premi sbsr Rp25.000.000 akan jth tempo 07/06/2019. Harap lakukan pembayaran ke rek. 101-150-3547 (BCI) . Info: CS (021-22773898)";
$no_hp="081219743032";
curl_setopt_array($curl, array(
  CURLOPT_PORT => "8018",
  CURLOPT_URL => "http://monitor.valdo-intl.com:8018/smsblast/nbuw",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"username\"\r\n\r\n".$username."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n".$password."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"message\"\r\n\r\n".$message."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"no_hp\"\r\n\r\n".$no_hp."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
    "postman-token: c6cdee33-2f9a-5462-31d7-48324674e4bd",
    "Token: ".sha1(date("d-m-Y"))
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
*/
?>