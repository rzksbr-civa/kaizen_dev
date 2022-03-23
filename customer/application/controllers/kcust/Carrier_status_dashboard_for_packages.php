<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carrier_status_dashboard_for_packages extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_carrier_status_dashboard');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Carrier Status Board for Packages');

		$data = array();
		
		$data['carrier_list'] = array(
			'amazon' => 'Amazon',
			'custom' => 'Custom',
			'dhlint' => 'DHL Int',
			'external' => 'External',
			'fedex' => 'FedEx',
			'ups' => 'UPS',
			'usps' => 'USPS',
			'ontrac' => 'OnTrac'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : array();
		$data['track_number'] = isset($_GET['track_number']) ? $_GET['track_number'] : null;
		$data['is_delivered'] = isset($_GET['is_delivered']) ? $_GET['is_delivered'] : null;
		$data['is_late'] = isset($_GET['is_late']) ? $_GET['is_late'] : null;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_carrier_status_dashboard->get_carrier_status_dashboard_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_carrier_status_dashboard_for_packages', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_carrier_status_dashboard_for_packages', $data);
		$this->load->view('view_footer', $footer_data);
	}
	
	public function download_pod($tracking_numbers, $file_name) {
		$arr_tracking_numbers = explode("-",$tracking_numbers);
		
		$zip = new ZipArchive();
		$tmp_file = tempnam('.', '');
		$zip->open($tmp_file, ZipArchive::CREATE);
		
		foreach($arr_tracking_numbers as $tracking_number) {
			$file_url = 'https://www.fedex.com/trackingCal/retrievePDF.jsp?accountNbr=&anon=true&appType=&destCountry=&locale=en_US&shipDate=&trackingCarrier=FDXG&trackingNumber='.$tracking_number.'&type=SPOD';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $file_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			
			$zip->addFromString($tracking_number.'.pdf', $data);
		}
	
		$zip->close();

		header('Content-disposition: attachment; filename="'.$file_name.'.zip"');
		header('Content-type: application/zip');
		readfile($tmp_file);
		unlink($tmp_file);
	}
}
