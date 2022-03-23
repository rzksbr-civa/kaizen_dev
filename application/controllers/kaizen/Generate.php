<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generate extends CI_Controller {
	public function __construct(){
		parent::__construct();
		ignore_user_abort(true);
    }
	
	public function index() {
		
	}
	
	public function pods($args) {
		
	}
	
	public function ups_pod($track_number) {
		$result = array();
		
		if(empty($track_number)) {
			$result['success'] = false;
			$result['error_message'] = 'Missing track number';
			echo json_encode($result);
		}
		
		$this->load->library('Pdf');
		
		// Portrait, units in milimeter, A4 paper
		$pdf = new FPDF('P', 'mm', 'A4');
		
		$pdf->AddPage();
		$pdf->SetFont('Arial', 'B', 20);
		
		$pdf->Cell(0,7,'Proof of Delivery',0,1,'L');
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(0,20,'Dear Customer,',0,1);
		$pdf->Cell(0,0,'This notice serves as proof of delivery for the shipment listed below.',0,1);
		
		$pdf->Cell(0,10,'',0,1);
		
		$this->print_info_in_pdf($pdf, 'Tracking Number', '1Z9440F30396042705');
		$this->print_info_in_pdf($pdf, 'Service', 'UPS Ground');
		$this->print_info_in_pdf($pdf, 'Delivered On', '09/03/2021 1:23 P.M.');
		$this->print_info_in_pdf($pdf, 'Delivered To', 'SPICEWOOD, TX, US');
		$this->print_info_in_pdf($pdf, 'Weight', '34.50 LBS');
		$this->print_info_in_pdf($pdf, 'Shipped / Billed On', '08/31/2021');
		$this->print_info_in_pdf($pdf, 'Received By', 'DRIVER RELEASE');
		$this->print_info_in_pdf($pdf, 'Left At', 'Garage');
		
		$pdf->Cell(0,20,'Tracking results provided by UPS API: 09/30/2021 8:01 A.M. EST',0,1);
		
		$pdf->Cell(0,5,'https://www.ups.com/track?loc=en_US&tracknum=1Z9440F30396042705',0,1);
		$pdf->Cell(0,5,'Details are only available for shipments delivered within the last 120 days.',0,1);
		
		$file_name = 'assets/data/' . PROJECT_CODE . '/file/pod/ups/'.$track_number.'.pdf';
				
		$pdf->Output($file_name, 'F');
		
		/*$result['success'] = true;
		
		echo json_encode($result);*/
	}
	
	public function print_info_in_pdf($pdf, $header, $content) {
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(80,10,$header,0,1,'L');
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(0,5,$content,0,1,'L');
		
		$pdf->Cell(0,5,'',0,1,'L');
	}
}
