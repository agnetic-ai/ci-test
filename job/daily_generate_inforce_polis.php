<?php
//open connection
$ch = curl_init();
//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, "http://10.17.44.27:83/index.php/corehelper/generate_inforce_polis");
//execute post
$result = curl_exec($ch);
//close connection
curl_close($ch);
?>