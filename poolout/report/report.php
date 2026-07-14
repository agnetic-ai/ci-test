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
	$json['j_cpp_promo_periode_result']=isset($json['j_cpp_promo_periode_result']) ? $json['j_cpp_promo_periode_result']:"";
	if($json['j_cpp_promo_periode_result']!=""){
		$content = file_get_contents(dirname(__FILE__).'/template/cpp_promo.htm');
	}else{
		$content = file_get_contents(dirname(__FILE__).'/template/cpp.htm');		
	}
    
	$json['cpp_periode']=isset($json['cpp_periode']) ? $json['cpp_periode']:"";
	$content=str_replace("{LOGO}",dirname(__FILE__)."/img/logo.png",$content);
	$content=str_replace("{CART}",$dir."/".$arr['Cart'],$content);
	$content=str_replace("{cpp_nama_pp}",$json['cpp_nama_pp'],$content);
	$content=str_replace("{cpp_nama_tt}",$json['cpp_nama_tt'],$content);
	$content=str_replace("{cpp_tgl_asu}",$json['cpp_tgl_asu'],$content);
	$content=str_replace("{cpp_dbo}",$json['cpp_dbo'],$content);
	$content=str_replace("{cpp_age}",$json['cpp_age'],$content);
	$content=str_replace("{cpp_premi}",$json['cpp_premi'],$content);
	$content=str_replace("{cpp_periode}",$json['cpp_periode'],$content);
	$content=str_replace("{cpp_masa_asu}",$json['cpp_masa_asu'],$content);
	$content=str_replace("{cpp_akhir_asu}",$json['cpp_akhir_asu'],$content);
	$content=str_replace("{cpp_mti_hari}",$json['cpp_mti_hari'],$content);
	$content=str_replace("{cpp_mti_pa}",$json['cpp_mti_pa'],$content);
	$content=str_replace("{cpp_up}",$json['cpp_up'],$content);
	$content=str_replace("{cpp_mti_total}",$json['cpp_mti_total'],$content);
	$content=str_replace("{total_days}",$json['total_days'],$content);
	
	if((int)$json['cpp_age']>69){
	$content=str_replace("{CLAIM_UP}",($json['cpp_currency']=="IDR" ? "Rp. 10.000.000,- (sepuluh juta rupiah).":"USD 1.000,- (seribu dolar)"),$content);	
	}else{
	$content=str_replace("{CLAIM_UP}",($json['cpp_currency']=="IDR" ? "Rp. 2.000.000.000,- (dua miliar rupiah).":"USD 130.000,- (seratus tiga puluh ribu dolar)"),$content);	
	}

	$json['j_cpp_promo_periode_result']=isset($json['j_cpp_promo_periode_result']) ? $json['j_cpp_promo_periode_result']:"";
	
	$str_promo='';
	if($json['j_cpp_promo_periode_result']!=""){
		$str_promo.='
		
		<tr>
			<td colspan="3" align="left" style="padding-top:3px;padding-bottom:3px;border-top:1px #000 solid;border-bottom:1px #000 solid"><strong>PROGRAM PROMO</strong></td>
		</tr>
		<tr>
			<td>
			Pilihan Periode Pembayaran Manfaat Investasi 
			</td>
			<td >:</td>
			<td align="right">'.$json['j_cpp_promo_periode_result'].'</td>
		</tr>
		<tr>
			<td>
			Jangka Waktu Asuransi
			</td>
			<td>:</td>
			<td align="right">'.$json['j_cpp_promo_masa_asu'].'</td>
		</tr>
		<tr>
			<td>
			Tanggal Akhir Asuransi (Tanggal/Bulan/Tahun)
			</td>
			<td>:</td>
			<td align="right">'.$json['j_cpp_promo_akhir_asu'].'</td>
		</tr>
		<tr>
			<td>
			Masa Garansi Investasi (hari)
			</td>
			<td>:</td>
			<td align="right">'.$json['j_cpp_promo_mti_hari'].'</td>
		</tr>
		<tr>
			<td>
			Manfaat Investasi (%) pa. 
			</td>
			<td>:</td>
			<td align="right">'.$json['j_cpp_promo_mti_pa'].'</td>
		</tr>
		<tr>
			<td>
			Nilai Manfaat Investasi
			</td>
			<td>:</td>
			<td align="right">'.$json['j_cpp_promo_mti_nilai'].'</td>
		</tr>
		<tr>
			<td>
			Nilai Polis
			</td>
			<td>:</td>
			<td align="right">'.$json['j_cpp_promo_total_cair'].'</td>
		</tr>
		<tr>
			<td colspan="3" align="left" style="border-bottom:1px #000 solid"></td>
		</tr>
		';
	}
	
	$content=str_replace("{PROMO}",$str_promo,$content);
	
	
	//{PROMO}
	
	
	$str='';
	foreach($json['data_tabel'] as $row){
		$str.='<tr>
			<td align="center">'.$row['periode'].'</td>
			<td align="center">'.$row['bulan'].'</td>
			<td align="center">'.$row['jumlah_hari'].'</td>
			<td align="right">'.$row['mti_jumlah_hari'].' '.($json['cpp_currency']=="IDR" ? "":$json['cpp_currency']).'</td>
			<td align="right">'.$row['saldo_investasi'].' '.($json['cpp_currency']=="IDR" ? "":$json['cpp_currency']).'</td>
			<td align="right">'.(strlen($row['manfaat_investasi'])>1 ? $row['manfaat_investasi'].' '.($json['cpp_currency']=="IDR" ? "":$json['cpp_currency']):"").'</td>
			<td align="right">'.$row['klaim'].' '.($json['cpp_currency']=="IDR" ? "":$json['cpp_currency']).'</td>
		</tr>';
	}
	$content=str_replace("{data_tabel}",$str,$content);
	
    $html2pdf = new Html2Pdf('P', 'A4', 'fr');
    $html2pdf->setDefaultFont('Arial');
    $html2pdf->writeHTML($content);
    $html2pdf->output('SIL_CLI_'.str_replace(" ","_",$json['cpp_nama_pp']).'_CPP_'.date("YmdHis").'_new.pdf');
} catch (Html2PdfException $e) {
    $html2pdf->clean();

    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}
