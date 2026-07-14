<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhooks extends CI_Controller {
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        // ------------------------------
        // 🔧 KONFIGURASI
        // ------------------------------

        // Buat token bebas, tapi harus sama dengan yang diisi di Meta Dashboard
        $VERIFY_TOKEN = "Cli.V3RTKn.2025";  

        // App Secret dari WhatsApp Cloud API
        $APP_SECRET   = "ed2049bfd1e8f68da932b44a3864ecf6";

        // Lokasi file log untuk mencatat payload webhook
        $LOG_FILE     = __DIR__ . "/webhook/webhook_secure_log.txt";

        // ===========================================================
        //* STEP 1: VERIFIKASI TOKEN (GET REQUEST dari Meta)
        // ===========================================================
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $mode      = isset($_GET['hub_mode']) ? $_GET['hub_mode'] :
                        (isset($_GET['hub.mode']) ? $_GET['hub.mode'] : null);

            $token     = isset($_GET['hub_verify_token']) ? $_GET['hub_verify_token'] :
                        (isset($_GET['hub.verify_token']) ? $_GET['hub.verify_token'] : null);

            $challenge = isset($_GET['hub_challenge']) ? $_GET['hub_challenge'] :
                        (isset($_GET['hub.challenge']) ? $_GET['hub.challenge'] : null);

            if ($mode === 'subscribe' && $token === $VERIFY_TOKEN) {
                header('Content-Type: text/plain');
                echo $challenge; // Meta akan menganggap webhook kamu VERIFIED
                exit;
            } else {
                header('HTTP/1.1 403 Forbidden');
                echo "Invalid verify token";
                exit;
            }
        }

        // ===========================================================
        //* STEP 2: PROSES DATA WEBHOOK (POST REQUEST dari Meta)
        // ===========================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $rawBody = file_get_contents("php://input");
            if (!function_exists('getallheaders')) {
            function getallheaders() {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }

                return $headers;

                }
            }

            $headers = getallheaders();

            // 2.1 Verifikasi signature (keamanan penting)
            $signatureHeader = null;
            if (isset($headers['X-Hub-Signature-256'])) {
                $signatureHeader = $headers['X-Hub-Signature-256'];
            } elseif (isset($headers['x-hub-signature-256'])) {
                $signatureHeader = $headers['x-hub-signature-256'];
            }

            if (!$signatureHeader) {
                header("HTTP/1.1 400 Bad Request");
                echo "Missing X-Hub-Signature-256 header";
                exit;
            }

            $signatureHeader   = "sha256=" . hash_hmac("sha256", $rawBody, $APP_SECRET);
            $expectedSignature = "sha256=" . hash_hmac("sha256", $rawBody, $APP_SECRET);

            if (!hash_equals($expectedSignature, $signatureHeader)) {
                header("HTTP/1.1 403 Forbidden");
                echo "Invalid signature";
                exit;
            }

            // 2.2 Decode payload JSON
            $data = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                header("HTTP/1.1 400 Bad Request");
                echo "Invalid JSON";
                exit;
            }

            // 2.3 Simpan log payload ke file (untuk debugging)
            $logText = date("Y-m-d H:i:s") . " | Signature OK | Payload: " . $rawBody . PHP_EOL;
            file_put_contents($LOG_FILE, $logText, FILE_APPEND);

            // 2.4 Cek apakah payload berisi status pesan
            if (!empty($data['entry'])) {

                foreach ($data['entry'] as $entry) {

                    // Fallback array changes
                    $changes = array();
                    if (isset($entry['changes'])) {
                        $changes = $entry['changes'];
                    }

                    foreach ($changes as $change) {

                        $value = array();
                        if (isset($change['value'])) {
                            $value = $change['value'];
                        }

                        // Tangani message status
                        if (!empty($value['statuses'])) {
                            foreach ($value['statuses'] as $status) {

                                $msgId = isset($status['id']) ? $status['id'] : '(no id)';
                                $st    = isset($status['status']) ? $status['status'] : '(no status)';
                                $to    = isset($status['recipient_id']) ? $status['recipient_id'] : '(unknown)';
                                $err   = isset($status['errors'][0]['code']) ? $status['errors'][0]['code'] : '';

                                echo "[STATUS] MsgID: $msgId | To: $to | Status: $st | Error: $err\n";
                            }
                        }

                        // Tangani pesan masuk
                        if (!empty($value['messages'])) {
                            foreach ($value['messages'] as $msg) {

                                $from = isset($msg['from']) ? $msg['from'] : '(unknown)';
                                $text = isset($msg['text']['body']) ? $msg['text']['body'] : '';

                                echo "[INBOUND] From: $from | Message: $text\n";
                            }
                        }

                    }
                }
            }

            // 2.5 Balasan wajib (200 OK)
            header("HTTP/1.1 200 OK");
            echo "EVENT_RECEIVED";
            exit;
        }

        // ===========================================================
        // STEP 3: METHOD LAIN DITOLAK
        // ===========================================================
        header("HTTP/1.1 405 Method Not Allowed");
        echo "Method not allowed";
        exit;

    }
}
