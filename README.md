# ci-test

```bash
TODO :
1. Ubah url parameter menjadi /riplaypersonal/generate?no_sppaj=
2. pada method data_from_sample sebelum mengambil template tambahkan query berikut :
SELECT SP.ID,
       SP.trx_id,
       SP.spaj_code,
       SP.no_polis,
       SP.product_code,
       SP.nama_pp,
       SP.effective_dt,
       SP.last_change_dt,
       SP.status_qc,
       DATE_FORMAT(SP.last_change_dt, '%Y%m%d') as docdir,
       MS.NAMA_AREA,
       MS.PIC_CLI,
       SP.pfa_code
FROM tbl_spaj SP
    left join tbl_bank_data_ms MS
        on MS.KODE_RM = SP.agen_code
WHERE SP.spaj_code = 'PP11209447'
3. Setelah exec query di atas berhasil tambahkan logic untuk mengambil path dari file json:
$trx_id = $rows['docdir'] . "/" . $rows['trx_id'];
4. Rubah method cpp_template_dir untuk pengambilan json menjadi :
private function other_doc($q, $type)
	{
		$dir = realpath(".") . "/submission/" . $q;

		$arr = "";
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					$split = explode("_", $file);
					if ($split[0] == $type) {

						$arr = file_get_contents($dir . "/" . $file);
					}
				}
				closedir($dh);
			}
		}
		return $arr;
	}
5. Setelah selesai melakukan perubahan TIDAK UDAH DI TEST DI SERVER KARENA TIDAK BISA HIT SERVER KANTOR UNTUK AMBIL DATA JSON
6. REVERENSI AMBIL DARI test-ci-pdf\application\controllers\Espajcppnew. METHOD kyc_live
```
