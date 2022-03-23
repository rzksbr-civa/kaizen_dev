<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trailer_utilization_forecast_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_trailer_utilization_forecast');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Trailer Utilization Forecast Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['carrier_list'] = array(
			'express' => 'Express',
			'fedex' => 'FedEx Ground',
			'smartpost' => 'FedEx SmartPost',
			'ups' => 'UPS',
			'upsnext' => 'UPS Next',
			'usps' => 'USPS',
			'ontrac' => 'OnTrac'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : 1;
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : array('fedex','ups');
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_trailer_utilization_forecast->get_trailer_utilization_forecast_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_trailer_utilization_forecast_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_trailer_utilization_forecast_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
