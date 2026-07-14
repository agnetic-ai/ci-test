<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		
		
		/*
		//$this->load->view('welcome_message');
		require_once realpath("."). '/vendor/autoload.php';
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
		
		//$content_html=file_get_contents( realpath(".")."/template/cpp.htm");
		//echo $content_html;
		//exit;
		$content_html='';
		$mpdf->WriteHTML($content_html);
		$mpdf->Output();
		*/
		echo dirname(__FILE__).'/../../html2pdf/vendor/autoload.php';
		require_once dirname(__FILE__).'/../../html2pdf/vendor/autoload.php';
		//use Spipu\Html2Pdf\Html2Pdf;
		//use Spipu\Html2Pdf\Exception\Html2PdfException;
		//use Spipu\Html2Pdf\Exception\ExceptionFormatter;
		exit;
		/*
		require_once dirname(__FILE__).'/../vendor/autoload.php';

		use Spipu\Html2Pdf\Html2Pdf;
		use Spipu\Html2Pdf\Exception\Html2PdfException;
		use Spipu\Html2Pdf\Exception\ExceptionFormatter;

		try {
			ob_start();
			include dirname(__FILE__).'/res/exemple13.php';
			$content = ob_get_clean();

			$html2pdf = new Html2Pdf('P', 'A4', 'fr');
			$html2pdf->writeHTML($content);
			$html2pdf->output('exemple13.pdf');
		} catch (Html2PdfException $e) {
			$html2pdf->clean();

			$formatter = new ExceptionFormatter($e);
			echo $formatter->getHtmlMessage();
		}
		*/
	}
}
