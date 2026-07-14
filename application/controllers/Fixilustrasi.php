<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fixilustrasi extends CI_Controller {
	public $doc_filename;
	
	public function index()
	{
		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		effective_dt between '2024-02-29' and '2024-02-29' 
		");
		$rows=$query->result_array();
		$rename="";
		$path="";
		$json=array();
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="SPAJ";
			$data=json_decode($this->other_doc($q,$type),true);
			echo"<pre>";
			if($data['cpp_mti_hari']=="366"){
				
				$rename=realpath(".")."/submission/".$q."/".'REVISI_'.date('YmdHis').'_'.$this->doc_filename;
				$path=realpath(".")."/submission/".$q."/".$this->doc_filename;
				copy($path,$rename);
				$json=$data;
				$json['cpp_mti_hari']="365";
				file_put_contents($path,json_encode($json));
				
				echo $data['cpp_no_spaj']."|".$this->doc_filename;
				
				//print_r($data);
				//exit;
			}
			$data=array();
			$rename="";
			$path="";
			$json=array();
		}
		
	}
	
	
	public function spaj()
	{
		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code in(
		'PP11414516',
		'PP11431249',
		'PP11431250',
		'PP11414534',
		'PP11366529',
		'PP11433079',
		'PP11427259',
		'PP11431619',
		'PP11327943',
		'PP11424475',
		'PP11430401'
		)
		");
		$rows=$query->result_array();
		$rename="";
		$path="";
		$json=array();
		$tbl=array();
		$premi=0.0;
		$bunga=0.0;
		$saldo=0.0;
		$bunga_cair=0.0;
		echo"<pre>";
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="SPAJ";
			$data=json_decode($this->other_doc($q,$type),true);
			
				$premi=str_replace(",",".",str_replace(".","",str_replace(" IDR","",$data['cpp_premi'])));
				
				foreach($data['data_tabel'] as $r){
					$bunga=(((float)$r['jumlah_hari']/(float)$data['total_days'])*(float)$premi*((float)str_replace(",",".",str_replace(" %","",$data['cpp_mti_pa']))/100.00));
					$saldo=$saldo+$bunga;
					
					if($r['periode']=="6" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo;
					}elseif($r['periode']=="12" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}elseif($r['periode']=="12" and $data['cpp_periode']=="12 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}else{
						$bunga_cair=0.0;
					}
					
					$tbl[]=array(
					'periode' => $r['periode'],
                    'bulan' => $r['bulan'],
                    'jumlah_hari' =>  $r['jumlah_hari'],
                    'mti_jumlah_hari' => number_format($bunga,2,",","."),
                    'saldo_investasi' => number_format($saldo+$premi,2,",","."),
                    'manfaat_investasi' => number_format($bunga_cair,2,",","."),
                    'klaim' => $r['klaim']
					);	
				}
			$premi=0.0;	
			$bunga=0.0;
			$saldo=0.0;
			$data['data_tabel']=$tbl;
			
			$rename=realpath(".")."/submission/".$q."/".'REVISI_'.date('YmdHis').'_'.$this->doc_filename;
			$path=realpath(".")."/submission/".$q."/".$this->doc_filename;
			copy($path,$rename);
			$json=$data;
			file_put_contents($path,json_encode($json));
			
			print_r($data);
			
			
			$data=array();
			$rename="";
			$path="";
			$json=array();
			$tbl=array();
			
			
			
			
		}
		
		
	}
	
	public function update_ilustrasi()
	{
		$spaj=$this->input->get("spaj");
		$rate=$this->input->get("rate");
		
		if($spaj=="" or $rate==""){
			echo"Invalid Paramater";
			exit;
		}
		
		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code in('".$spaj."')
		");
		$rows=$query->result_array();
		$rename="";
		$path="";
		$json=array();
		$tbl=array();
		$premi=0.0;
		$bunga=0.0;
		$saldo=0.0;
		$bunga_cair=0.0;
		echo"<pre>";
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="SPAJ";
			$data=json_decode($this->other_doc($q,$type),true);
			
				$premi=str_replace(",",".",str_replace(".","",str_replace(" IDR","",$data['cpp_premi'])));
				$data['cpp_mti_pa']=str_replace(",",".",$rate);
				$data['cpp_mti_total']=number_format(ceil(($data['cpp_mti_pa']/100.00)*$premi),2,",",".")." IDR";
				
				foreach($data['data_tabel'] as $r){
					$bunga=(((float)$r['jumlah_hari']/(float)$data['total_days'])*(float)$premi*((float)str_replace(",",".",str_replace(" %","",$data['cpp_mti_pa']))/100.00));
					$saldo=$saldo+$bunga;
					
					if($r['periode']=="6" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo;
					}elseif($r['periode']=="12" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}elseif($r['periode']=="12" and $data['cpp_periode']=="12 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}else{
						$bunga_cair=0.0;
					}
					
					$tbl[]=array(
					'periode' => $r['periode'],
                    'bulan' => $r['bulan'],
                    'jumlah_hari' =>  $r['jumlah_hari'],
                    'mti_jumlah_hari' => number_format($bunga,2,",","."),
                    'saldo_investasi' => number_format($saldo+$premi,2,",","."),
                    'manfaat_investasi' => number_format($bunga_cair,2,",","."),
                    'klaim' => $r['klaim']
					);	
				}
				$data['cpp_mti_pa']=number_format(str_replace(",",".",$rate),2,",",".")." %";
				
			$premi=0.0;	
			$bunga=0.0;
			$saldo=0.0;
			$data['data_tabel']=$tbl;
			
			
			$rename=realpath(".")."/submission/".$q."/".'REVISI_'.date('YmdHis').'_'.$this->doc_filename;
			$path=realpath(".")."/submission/".$q."/".$this->doc_filename;
			copy($path,$rename);
			$json=$data;
			file_put_contents($path,json_encode($json));
			
			print_r($data);
			
			
			$data=array();
			$rename="";
			$path="";
			$json=array();
			$tbl=array();
			
			
			
			
		}
		
		
	}
	
	public function update_ilustrasi2()
	{
		
		
		$spaj=$this->input->post("spaj_code");
		$rate=$this->input->post("rate");
		
		if($spaj=="" or $rate==""){
			echo"Invalid Paramater";
			exit;
		}
		
		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code in('".$spaj."')
		");
		$rows=$query->result_array();
		$rename="";
		$path="";
		$json=array();
		$tbl=array();
		$premi=0.0;
		$bunga=0.0;
		$saldo=0.0;
		$bunga_cair=0.0;
		
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="SPAJ";
			$data=json_decode($this->other_doc($q,$type),true);
			
				$premi=str_replace(",",".",str_replace(".","",str_replace(" IDR","",$data['cpp_premi'])));
				$data['cpp_mti_pa']=str_replace(",",".",$rate);
				$data['cpp_mti_total']=number_format(ceil(($data['cpp_mti_pa']/100.00)*$premi),2,",",".")." IDR";
				
				foreach($data['data_tabel'] as $r){
					$bunga=(((float)$r['jumlah_hari']/(float)$data['total_days'])*(float)$premi*((float)str_replace(",",".",str_replace(" %","",$data['cpp_mti_pa']))/100.00));
					$saldo=$saldo+$bunga;
					
					if($r['periode']=="6" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo;
					}elseif($r['periode']=="12" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}elseif($r['periode']=="12" and $data['cpp_periode']=="12 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}else{
						$bunga_cair=0.0;
					}
					
					$tbl[]=array(
					'periode' => $r['periode'],
                    'bulan' => $r['bulan'],
                    'jumlah_hari' =>  $r['jumlah_hari'],
                    'mti_jumlah_hari' => number_format($bunga,2,",","."),
                    'saldo_investasi' => number_format($saldo+$premi,2,",","."),
                    'manfaat_investasi' => number_format($bunga_cair,2,",","."),
                    'klaim' => $r['klaim']
					);	
				}
				$data['cpp_mti_pa']=number_format(str_replace(",",".",$rate),2,",",".")." %";
				
			$premi=0.0;	
			$bunga=0.0;
			$saldo=0.0;
			$data['data_tabel']=$tbl;
			
			
			$rename=realpath(".")."/submission/".$q."/".'REVISI_'.date('YmdHis').'_'.$this->doc_filename;
			$path=realpath(".")."/submission/".$q."/".$this->doc_filename;
			copy($path,$rename);
			$json=$data;
			file_put_contents($path,json_encode($json));
			
			echo"Success Update";
			
			
			$data=array();
			$rename="";
			$path="";
			$json=array();
			$tbl=array();
			
			
			
			
		}
		
		
	}
	
	public function update_ilustrasi3()
	{
		
		
		$spaj=$this->input->get("spaj_code");
		$rate=$this->input->get("rate");
		
		if($spaj=="" or $rate==""){
			echo"Invalid Paramater";
			exit;
		}
		
		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code in('".$spaj."')
		");
		$rows=$query->result_array();
		$rename="";
		$path="";
		$json=array();
		$tbl=array();
		$premi=0.0;
		$bunga=0.0;
		$saldo=0.0;
		$bunga_cair=0.0;
		
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="SPAJ";
			$data=json_decode($this->other_doc($q,$type),true);
			
				$premi=str_replace(",",".",str_replace(".","",str_replace(" IDR","",$data['cpp_premi'])));
				$data['cpp_mti_pa']=str_replace(",",".",$rate);
				$data['cpp_mti_total']=number_format(ceil(($data['cpp_mti_pa']/100.00)*$premi),2,",",".")." IDR";
				
				foreach($data['data_tabel'] as $r){
					$bunga=(((float)$r['jumlah_hari']/(float)$data['total_days'])*(float)$premi*((float)str_replace(",",".",str_replace(" %","",$data['cpp_mti_pa']))/100.00));
					$saldo=$saldo+$bunga;
					
					if($r['periode']=="6" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo;
					}elseif($r['periode']=="12" and $data['cpp_periode']=="6 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}elseif($r['periode']=="12" and $data['cpp_periode']=="12 Bulan"){
						$bunga_cair=$saldo-str_replace(",",".",str_replace(".","",$tbl[5]['manfaat_investasi']));
					}else{
						$bunga_cair=0.0;
					}
					
					$tbl[]=array(
					'periode' => $r['periode'],
                    'bulan' => $r['bulan'],
                    'jumlah_hari' =>  $r['jumlah_hari'],
                    'mti_jumlah_hari' => number_format($bunga,2,",","."),
                    'saldo_investasi' => number_format($saldo+$premi,2,",","."),
                    'manfaat_investasi' => number_format($bunga_cair,2,",","."),
                    'klaim' => $r['klaim']
					);	
				}
				$data['cpp_mti_pa']=number_format(str_replace(",",".",$rate),2,",",".")." %";
				
			$premi=0.0;	
			$bunga=0.0;
			$saldo=0.0;
			$data['data_tabel']=$tbl;
			
			
			$rename=realpath(".")."/submission/".$q."/".'REVISI_'.date('YmdHis').'_'.$this->doc_filename;
			$path=realpath(".")."/submission/".$q."/".$this->doc_filename;
			copy($path,$rename);
			$json=$data;
			file_put_contents($path,json_encode($json));
			
			echo"Success Update";
			
			
			$data=array();
			$rename="";
			$path="";
			$json=array();
			$tbl=array();
			
			$this->db->query("update tbl_spaj set bunga='".$rate."' where spaj_code='".$spaj."'");
			
			
		}
		
		
	}
	
	public function update_ibu_kandung(){
		$spaj=$this->input->post("spaj_code");
		$nama=$this->input->post("nama");
		
		/*
		if($spaj=="" or $nama==""){
			echo"Invalid Paramater";
			exit;
		}*/
		
		$array['PP11454499']='ANITA PUSPITA ARIFIN';
$array['PP11458498']='HIOE ON YUN';
$array['PP11453594']='IPAH HATIPAH';



		$this->load->database();
		$query=$this->db->query("select trx_id,spaj_code,cast(last_change_dt as date) as effective_dt,spaj_code from tbl_spaj 
		where spaj_code not like'%DEL%' and 
		spaj_code in(
		'PP11454499',
'PP11458498',
'PP11453594'
		)
		");
		$rows=$query->result_array();
		
		
		
		$rename="";
		$path="";
		$json=array();
		$tbl=array();
		
		/*
		[23] => Array
        (
            [name] => mother_name1
            [value] => FENY PANGKEY
        )
		*/
		foreach($rows as $rs){
			$q=str_replace("-","",$rs['effective_dt'])."/".$rs['trx_id'];
			$type="DATA";
			$data=json_decode($this->other_doc($q,$type),true);
			
			echo $rs['spaj_code']."|".$data[23]['value']."|".$array[$rs['spaj_code']]."<br>"; 
			
			
			$data[23]=array(
			"name"=>"mother_name1",
			"value"=>$array[$rs['spaj_code']],
			);
			
			
			
			$rename=realpath(".")."/submission/".$q."/".'REVISI_'.date('YmdHis').'_'.$this->doc_filename;
			$path=realpath(".")."/submission/".$q."/".$this->doc_filename;
			copy($path,$rename);
			$json=$data;
			file_put_contents($path,json_encode($json));
			
			
			
			$data=array();
			$rename="";
			$path="";
			$json=array();
		}
	}
	
	private function other_doc($q,$type){
		$dir=realpath(".")."/submission/".$q;
		
		$arr="";
		if (is_dir($dir)){
		  if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){
			  $split=explode("_",$file);
			  if($split[0]==$type){
				$this->doc_filename=$file;
				$this->doc_filename_arr[$type]=$file;
				$arr=file_get_contents($dir."/".$file);
			  }
			}
			closedir($dh);
		  }
		}
		return $arr;
	}
}
/*
Array
(
    [cpp_no_spaj] => PP11271863
    [cpp_nama_pp] => LANIWATI HARDJONO
    [cpp_nama_tt] => LANIWATI HARDJONO
    [cpp_tgl_asu] => 29 Februari 2024
    [cpp_dbo] => 24 Maret 1961
    [cpp_age] => 63 Tahun
    [cpp_premi] => 250.000.000,00 IDR
    [cpp_periode] => 12 Bulan
    [cpp_masa_asu] => 1 (Satu) Tahun
    [cpp_akhir_asu] => 28 Februari 2025
    [cpp_mti_hari] => 366
    [cpp_mti_pa] => 5,65 %
    [cpp_up] => 375.000.000,00 IDR
    [cpp_mti_total] => 14.125.000,00 IDR
    [cpp_currency] => IDR
    [total_days] => 365
    [data_tabel] => Array
        (
            [0] => Array
                (
                    [periode] => 1
                    [bulan] => 29 Feb 2024 s.d 28 Mar 2024
                    [jumlah_hari] => 29
                    [mti_jumlah_hari] => 1.119.193,99
                    [saldo_investasi] => 251.119.193,99
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [1] => Array
                (
                    [periode] => 2
                    [bulan] => 29 Mar 2024 s.d 28 Apr 2024
                    [jumlah_hari] => 31
                    [mti_jumlah_hari] => 1.196.379,78
                    [saldo_investasi] => 252.315.573,77
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [2] => Array
                (
                    [periode] => 3
                    [bulan] => 29 Apr 2024 s.d 28 Mei 2024
                    [jumlah_hari] => 30
                    [mti_jumlah_hari] => 1.157.786,89
                    [saldo_investasi] => 253.473.360,66
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [3] => Array
                (
                    [periode] => 4
                    [bulan] => 29 Mei 2024 s.d 28 Jun 2024
                    [jumlah_hari] => 31
                    [mti_jumlah_hari] => 1.196.379,78
                    [saldo_investasi] => 254.669.740,44
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [4] => Array
                (
                    [periode] => 5
                    [bulan] => 29 Jun 2024 s.d 28 Jul 2024
                    [jumlah_hari] => 30
                    [mti_jumlah_hari] => 1.157.786,89
                    [saldo_investasi] => 255.827.527,32
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [5] => Array
                (
                    [periode] => 6
                    [bulan] => 29 Jul 2024 s.d 28 Agu 2024
                    [jumlah_hari] => 31
                    [mti_jumlah_hari] => 1.196.379,78
                    [saldo_investasi] => 257.023.907,10
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [6] => Array
                (
                    [periode] => 7
                    [bulan] => 29 Agu 2024 s.d 28 Sep 2024
                    [jumlah_hari] => 31
                    [mti_jumlah_hari] => 1.196.379,78
                    [saldo_investasi] => 258.220.286,89
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [7] => Array
                (
                    [periode] => 8
                    [bulan] => 29 Sep 2024 s.d 28 Okt 2024
                    [jumlah_hari] => 30
                    [mti_jumlah_hari] => 1.157.786,89
                    [saldo_investasi] => 259.378.073,77
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [8] => Array
                (
                    [periode] => 9
                    [bulan] => 29 Okt 2024 s.d 28 Nov 2024
                    [jumlah_hari] => 31
                    [mti_jumlah_hari] => 1.196.379,78
                    [saldo_investasi] => 260.574.453,55
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [9] => Array
                (
                    [periode] => 10
                    [bulan] => 29 Nov 2024 s.d 28 Des 2024
                    [jumlah_hari] => 30
                    [mti_jumlah_hari] => 1.157.786,89
                    [saldo_investasi] => 261.732.240,44
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [10] => Array
                (
                    [periode] => 11
                    [bulan] => 29 Des 2024 s.d 28 Jan 2025
                    [jumlah_hari] => 31
                    [mti_jumlah_hari] => 1.196.379,78
                    [saldo_investasi] => 262.928.620,22
                    [manfaat_investasi] => 0
                    [klaim] => 375.000.000,00
                )

            [11] => Array
                (
                    [periode] => 12
                    [bulan] => 29 Jan 2025 s.d 27 Feb 2025
                    [jumlah_hari] => 30
                    [mti_jumlah_hari] => 1.157.786,89
                    [saldo_investasi] => 264.086.407,10
                    [manfaat_investasi] => 14.125.000,00
                    [klaim] => 375.000.000,00
                )

        )

    [cpp_promo_bunga] => 
)

*/