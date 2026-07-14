<?php
require_once dirname(__FILE__).'/../vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

try {
	
	
	$q=isset($_GET["q"]) ? $_GET["q"]:"";
	if($q=="") die("Access Denied");
	$arr=array();
	$q=str_replace("\\","/",$q);
	$dir=realpath(".")."/../../".$q;
	if (is_dir($dir)){
	  if ($dh = opendir($dir)){
		while (($file = readdir($dh)) !== false){
		  if($file!="." && $file!=".."){
			$split=explode("_",$file);
			$arr[$split[0]]=$file;
		  }
		}
		closedir($dh);
	  }
	}else die("Invalid Directory Access");
	
    $json=file_get_contents($dir."/".$arr['SPAJ']);
	$json=json_decode($json,true);
	
	$content = file_get_contents(dirname(__FILE__).'/template/cpl.htm');
	$content=str_replace("{LOGO}",dirname(__FILE__)."/img/logo.png",$content);
	$content=str_replace("{No_SPAJ}",$json['No_SPAJ'],$content);
	$content=str_replace("{Nama_Pemegang_Polis}",$json['Nama_Pemegang_Polis'],$content);
	$content=str_replace("{Nama_Tertanggung}",$json['Nama_Tertanggung'],$content);
	$content=str_replace("{Tanggal_Lahir}",$json['Tanggal_Lahir'],$content);
	$content=str_replace("{Usia_Tertanggung}",$json['Usia_Tertanggung'],$content);
	$content=str_replace("{Tanggal_Mulai_Pertanggungan}",$json['Tanggal_Mulai_Pertanggungan'],$content);
	$content=str_replace("{Masa_Pertanggungan}",$json['Masa_Pertanggungan'],$content);
	$content=str_replace("{Uang_Pertanggungan}",$json['Uang_Pertanggungan'],$content);
	$content=str_replace("{Premi}",$json['Premi'],$content);
	$content=str_replace("{Premi_Sekaligus}",$json['Premi_Sekaligus'],$content);
	$content=str_replace("{Premi_Top_Up_Sekaligus}",$json['Premi_Top_Up_Sekaligus'],$content);
	$content=str_replace("{Masa_Target_Investasi}",$json['Masa_Target_Investasi'],$content);
	$content=str_replace("{Tingkat_Target_Investasi}",$json['Tingkat_Target_Investasi'],$content);
	$content=str_replace("{Tanggal_Awal_MTI}",$json['Tanggal_Awal_MTI'],$content);
	$content=str_replace("{Tanggal_Jatuh_Tempo_MTI}",$json['Tanggal_Jatuh_Tempo_MTI'],$content);
	$content=str_replace("{Jenis_Dana_Investasi}",$json['Jenis_Dana_Investasi'],$content);
	$content=str_replace("{Alokasi_Premi}",$json['Alokasi_Premi'],$content);
	
	$str='';
	foreach($json['data_tabel'] as $row){
		$str.='<tr>
			<td align="center">'.$row['Tanggal_Jatuh_Tempo_Masa_Target_Investasi'].'</td>
			<td align="center">'.$row['Usia_Polis_Tahun'].'</td>
			<td align="center">'.$row['Usia_Polis_Bulan'].'</td>
			<td align="right">'.$row['Premi_Sekaligus'].'</td>
			<td align="right">'.$row['Premi_Top_Up_Sekaligus'].'</td>
			<td align="right">'.$row['Estimasi_Nilai_Polis_low'].'</td>
			<td align="right">'.$row['Estimasi_Nilai_Polis_moderate'].'</td>
			<td align="right">'.$row['Estimasi_Nilai_Polis_high'].'</td>
			<td align="right">'.$row['Estimasi_Manfaat_Meninggal_low'].'</td>
			<td align="right">'.$row['Estimasi_Manfaat_Meninggal_moderate'].'</td>
			<td align="right">'.$row['Estimasi_Manfaat_Meninggal_high'].'</td>
		</tr>';
	}
	$content=str_replace("{data_tabel}",$str,$content);
	$content=str_replace("{DATE_PRINT}",date("Y M d"),$content);
	
	$arr['SignAgen']=isset($arr['SignAgen']) ? $dir."/".$arr['SignAgen']:(isset($arr['TTDAgen']) ? $dir."/".$arr['TTDAgen']:dirname(__FILE__)."/img/blank.png");
	$arr['SignNasabah']=isset($arr['SignNasabah']) ? $dir."/".$arr['SignNasabah']:(isset($arr['TTDNasabah']) ? $dir."/".$arr['TTDNasabah']:dirname(__FILE__)."/img/blank.png");
	
	$content=str_replace("{SignAgen}",$arr['SignAgen'],$content);
	$content=str_replace("{SignNasabah}",$arr['SignNasabah'],$content);
	
    //$html2pdf = new Html2Pdf('L', 'A4', 'fr');
    $width_in_mm = 11.5 * 25.4; 
	$height_in_mm = 15.3 * 25.4;
	$html2pdf = new Html2Pdf('P', array($width_in_mm,$height_in_mm), 'en', true, 'UTF-8', array(3, 3, 3, 3));

	$html2pdf->setDefaultFont('Arial');
    $html2pdf->writeHTML($content);
    //$html2pdf->output('SIL_CPL.pdf');
    $html2pdf->output('SIL_CLI_'.str_replace(" ","_",$json['Nama_Pemegang_Polis']).'_CPL_'.date("YmdHis").'.pdf');
} catch (Html2PdfException $e) {
    $html2pdf->clean();

    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}
