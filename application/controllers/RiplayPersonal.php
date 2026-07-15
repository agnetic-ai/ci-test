<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RiplayPersonal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('riplay-personal/cpp_pdf');
        $this->load->database();
    }

    /**
     * Halaman tombol Generate PDF (IDR / USD).
     */
    public function index()
    {
        $html = '<!doctype html>'
            . '<html lang="id"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Generate RIPLAY Personal PDF</title>'
            . '<style>'
            . 'body{font-family:Arial,sans-serif;margin:40px;color:#1f2937}'
            . '.actions{display:flex;gap:12px;flex-wrap:wrap}'
            . 'button{border:0;border-radius:6px;padding:12px 18px;font-size:15px;'
            . 'font-weight:700;color:#fff;background:#005691;cursor:pointer}'
            . 'button.usd{background:#047857}'
            . '</style></head><body>'
            . '<h1>Generate PDF RIPLAY Personal</h1>'
            . '<div class="actions">'
            . '<form method="get" action="' . site_url('riplaypersonal/generate') . '">'
            . '<input type="text" name="no_sppaj" placeholder="No SPPAJ" required>'
            . '<button type="submit">Generate PDF</button></form>'
            . '</div></body></html>';

        $this->output
            ->set_content_type('text/html', 'UTF-8')
            ->set_output($html);
    }

    /**
     * Generate & stream PDF.
     * URL: /riplaypersonal/generate?no_sppaj=XX
     */
    public function generate()
    {
        try {
            $data = $this->resolve_request_data();
        } catch (InvalidArgumentException $e) {
            return $this->fail(400, $e->getMessage());
        } catch (RuntimeException $e) {
            return $this->fail(500, $e->getMessage());
        }

        try {
            $pdf = cpp_render_pdf($data);
        } catch (InvalidArgumentException $e) {
            return $this->fail(400, $e->getMessage());
        } catch (RuntimeException $e) {
            return $this->fail(500, $e->getMessage());
        } catch (Exception $e) {
            return $this->fail(500, 'Gagal generate PDF: ' . $e->getMessage());
        }

        $filename = cpp_pdf_filename($data) . '.pdf';

        $this->output
            ->set_content_type('application/pdf')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_header('Content-Length: ' . strlen($pdf))
            ->set_output($pdf);
    }

    /**
     * Tentukan sumber data: query ?no_sppaj=, body JSON, atau query ?data={...}.
     */
    private function resolve_request_data()
    {
        // Primary: no_sppaj parameter -> query DB -> ambil JSON dari submission/
        $no_sppaj = $this->input->get('no_sppaj');
        if (is_string($no_sppaj) && trim($no_sppaj) !== '') {
            return $this->data_from_sample($no_sppaj);
        }

        // Fallback: raw JSON body
        $rawJson = $this->input->raw_input_stream;
        if (is_string($rawJson) && trim($rawJson) !== '') {
            return $this->data_from_json($rawJson);
        }

        // Fallback: query ?data={...}
        $queryData = $this->input->get('data');
        if (is_string($queryData) && trim($queryData) !== '') {
            return $this->data_from_json($queryData);
        }

        throw new InvalidArgumentException(
            'Request data kosong. Kirim ?no_sppaj=XX, JSON body, atau ?data={...}.'
        );
    }

    /**
     * Query tbl_spaj + tbl_bank_data_ms, lalu ambil JSON dari folder submission/.
     */
    private function data_from_sample($no_sppaj)
    {
        $no_sppaj = trim($no_sppaj);

        $str = "SELECT SP.ID,
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
                LEFT JOIN tbl_bank_data_ms MS
                    ON MS.KODE_RM = SP.agen_code
            WHERE SP.spaj_code = ?";

        $query = $this->db->query($str, array($no_sppaj));
        $rows = $query->row_array();

        if (empty($rows)) {
            throw new InvalidArgumentException('Data SPAJ tidak ditemukan untuk no_sppaj: ' . $no_sppaj);
        }

        $trx_id = $rows['docdir'] . "/" . $rows['trx_id'];

        // Ambil DATA PENGAJUAN dari submission/
        $json1 = $this->other_doc($trx_id, 'DATA');
        if (empty($json1)) {
            throw new RuntimeException('File DATA tidak ditemukan di submission/' . $trx_id);
        }

        // Ambil DATA SPAJ dari submission/
        $json2 = $this->other_doc($trx_id, 'SPAJ');
        if (empty($json2)) {
            throw new RuntimeException('File SPAJ tidak ditemukan di submission/' . $trx_id);
        }

        $decode1 = json_decode($json1, true);
        $decode2 = json_decode($json2, true);

        if (!is_array($decode1)) {
            throw new RuntimeException('Invalid JSON pada file DATA submission/' . $trx_id);
        }

        if (!is_array($decode2)) {
            throw new RuntimeException('Invalid JSON pada file SPAJ submission/' . $trx_id);
        }

        // Merge data dari kedua JSON + DB row untuk render PDF
        $data = array();

        // Dari DB row
        $data['cpp_nama_pp'] = isset($rows['nama_pp']) ? $rows['nama_pp'] : '';
        $data['cpp_no_spaj'] = isset($rows['spaj_code']) ? $rows['spaj_code'] : '';
        $data['cpp_no_polis'] = isset($rows['no_polis']) ? $rows['no_polis'] : '';
        $data['cpp_nama_area'] = isset($rows['NAMA_AREA']) ? $rows['NAMA_AREA'] : '';
        $data['cpp_pic_cli'] = isset($rows['PIC_CLI']) ? $rows['PIC_CLI'] : '';
        $data['cpp_pfa_code'] = isset($rows['pfa_code']) ? $rows['pfa_code'] : '';

        // Merge decode1 (DATA PENGAJUAN) dan decode2 (SPAJ) ke data
        // Format dari Espajcppnew: array of {name, value}
        if (isset($decode1[0]['name'])) {
            foreach ($decode1 as $item) {
                if (isset($item['name']) && isset($item['value'])) {
                    $data[$item['name']] = $item['value'];
                }
            }
        } else {
            $data = array_merge($data, $decode1);
        }

        if (isset($decode2[0]['name'])) {
            foreach ($decode2 as $item) {
                if (isset($item['name']) && isset($item['value'])) {
                    $data[$item['name']] = $item['value'];
                }
            }
        } else {
            $data = array_merge($data, $decode2);
        }

        return $data;
    }

    /**
     * Ambil file JSON dari folder submission/ berdasarkan prefix type.
     * Scan directory submission/{$q}/ dan cari file yang prefix-nya == $type.
     */
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

    /**
     * Decode JSON string -> array. PHP 5.4 safe (tanpa JSON_THROW_ON_ERROR).
     */
    private function data_from_json($json)
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON data: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON data: root value must be an object.');
        }

        return $data;
    }

    /**
     * Kirim response error plain-text.
     */
    private function fail($statusCode, $message)
    {
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('text/plain', 'UTF-8')
            ->set_output($message);
    }
}
