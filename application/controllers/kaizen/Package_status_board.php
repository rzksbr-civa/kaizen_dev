<?php
defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('max_execution_time', 300); 

class Package_status_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Package Status Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'stock_id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['customer_list'] = $this->model_package_status_board->get_customer_list();
		
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
		
		$data['shipping_method_list'] = array(
			'amazon_ANY',
			'dhlint_P',
			'external_client_pickup',
			'external_kitting',
			'external_ltl',
			'external_ltl_thirdparty',
			'external_photo',
			'external_pickup',
			'external_vendor',
			'fedex_FEDEX_2_DAY',
			'fedex_FEDEX_2_DAY_AM',
			'fedex_FEDEX_EXPRESS_SAVER',
			'fedex_FEDEX_GROUND',
			'fedex_FIRST_OVERNIGHT',
			'fedex_GROUND_HOME_DELIVERY',
			'fedex_INTERNATIONAL_ECONOMY',
			'fedex_INTERNATIONAL_PRIORITY',
			'fedex_PRIORITY_OVERNIGHT',
			'fedex_SMART_POST',
			'fedex_STANDARD_OVERNIGHT',
			'ups_01',
			'ups_02',
			'ups_03',
			'ups_07',
			'ups_08',
			'ups_11',
			'ups_12',
			'ups_13',
			'ups_SP',
			'usps_US-EMI',
			'usps_US-FC',
			'usps_US-FCI',
			'usps_US-PM',
			'usps_US-PMI',
			'usps_US-PS',
			'usps_US-XM'
		);
		
		$data['report_type_list'] = array(
			'summary' => 'Summary & Transit Time',
			'zone_and_state' => 'Ontime Delivery By Zone And State'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : 51;
		$data['stock_ids'] = isset($_GET['stock_ids']) ? $_GET['stock_ids'] : array();
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : array();
		$data['shipping_method'] = isset($_GET['shipping_method']) ? $_GET['shipping_method'] : array();
		$data['track_number'] = isset($_GET['track_number']) ? $_GET['track_number'] : null;
		$data['is_delivered'] = isset($_GET['is_delivered']) ? $_GET['is_delivered'] : null;
		$data['is_late'] = isset($_GET['is_late']) ? $_GET['is_late'] : null;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		$data['max_transit_day'] = isset($_GET['max_transit_day']) ? $_GET['max_transit_day'] : 5;
		$data['report_type'] = isset($_GET['report_type']) ? $_GET['report_type'] : 'summary';
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_package_status_board->get_package_status_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_package_status_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_package_status_board', $data);
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
