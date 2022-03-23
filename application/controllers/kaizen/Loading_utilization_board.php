<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loading_utilization_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_loading_andon');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Loading Utilization Board');

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
			'usps' => 'USPS'
		);
		
		$data['container_type_list'] = array(
			'pallet' => 'Pallet',
			'rolling_bin' => 'Rolling Bin',
			'flat_cart' => 'Flat Cart',
			'truck_trailer' => 'Truck Trailer'
		);
		
		$data['manifest_status_list'] = array(
			'loaded' => 'Loaded',
			'open' => 'Open',
			'sealed' => 'Sealed'
		);
		
		$data['sort_list'] = array(
			'created_time' => 'Created Time',
			'weight_percentage' => 'Weight Percentage',
			'cubic_ft_percentage' => 'Cubic Ft Percentage'
		);
		
		$data['utilization_list'] = array(
			'green' => 'Green',
			'yellow' => 'Yellow',
			'red' => 'Red (Blow Out)'
		);
		
		$data['breakdown_list'] = array(
			'no_breakdown' => 'No Breakdown',
			'customer' => 'Customer'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : null;
		$data['status'] = isset($_GET['status']) ? $_GET['status'] : null;
		$data['container_type'] = isset($_GET['container_type']) ? $_GET['container_type'] : null;
		$data['load_location'] = isset($_GET['load_location']) ? $_GET['load_location'] : null;
		$data['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'created_time';
		$data['utilization'] = isset($_GET['utilization']) ? $_GET['utilization'] : null;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		$data['breakdown'] = isset($_GET['breakdown']) ? $_GET['breakdown'] : 'no_breakdown';
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_loading_andon->get_loading_utilization_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_loading_utilization_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_loading_utilization_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
