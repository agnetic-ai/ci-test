<?php
ini_set('display_errors', 0);
error_reporting(0);

$options = array(
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "spider", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
		);
		$url="https://www.bi.go.id/id/moneter/informasi-kurs/transaksi-bi/Default.aspx";
		$ch      = curl_init($url);
		curl_setopt_array( $ch, $options );
		$content = curl_exec( $ch );
		$err     = curl_errno( $ch );
		$errmsg  = curl_error( $ch );
		$header  = curl_getinfo( $ch );
		curl_close( $ch );
		$DOM = new DOMDocument();
		$DOM->loadHTML($content);
		$Header = $DOM->getElementsByTagName('th');
		$Detail = $DOM->getElementsByTagName('td');

		//#Get header name of the table
		foreach($Detail as $NodeHeader) 
		{
			$aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
		}
		//echo"<pre>";
		//print_r($aDataTableHeaderHTML); 
		/*
		[125] => USD
		[126] => 1.00
		[127] => 14,841.00
		[128] => 14,693.00
		*/
		/*
		echo"TGL : ".date("d M Y")."\n";
		echo"VALUTA : ".$aDataTableHeaderHTML[125]."\n";
		echo"Nilai Jual : ".$aDataTableHeaderHTML[127]."\n";
		echo"Nilai Beli : ".$aDataTableHeaderHTML[128]."\n";
		echo"Nilai Tengah : ".(((float)str_replace(",","",$aDataTableHeaderHTML[127])+(float)str_replace(",","",$aDataTableHeaderHTML[128]))/2.0)."\n";
		*/
		
		$Nilai_Jual=isset($aDataTableHeaderHTML[127]) ? str_replace(",","",$aDataTableHeaderHTML[127]):0;
		$Nilai_Beli=isset($aDataTableHeaderHTML[128]) ? str_replace(",","",$aDataTableHeaderHTML[128]):0;
		$Nilai_Tengah=((float)$Nilai_Jual+(float)$Nilai_Beli)/2.0;
		
		if($Nilai_Jual<=0 && $Nilai_Beli<=0){
			$status="No record Found";
			
		}else{
			try{
			$dbDB = new PDO("odbc:Driver={SQL Server};Server=10.17.44.28;Database=CAPITALLIFE_INDIVIDU_PROD", "sa","plita");
			$str="insert KURS_HARIAN select cast(dateadd(day,-1,GETDATE()) as date),'USD',".$Nilai_Jual.",".$Nilai_Beli.",".$Nilai_Tengah.",'SystemJob',GETDATE(),null,null";
			$stmt = $dbDB->prepare($str);
			$stmt->execute();
			$status="Success";
			}catch(Exception $er){
				echo $er->getMessage();	
			}
			
		}
		$file_log_name=date("dmY")."_log.txt";
		file_put_contents("E:\\WebServer\\www\\job\\logs\\".$file_log_name, "Result: ".$status." - ".date("Y-m-d H:i:s a")."\n".var_export($aDataTableHeaderHTML,true));
		echo $status;
		exit;
?>