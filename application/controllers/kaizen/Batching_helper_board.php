<?php
defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('max_execution_time', 300); 

class Batching_helper_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$this->load->model(PROJECT_CODE.'/model_batching_helper');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Batching Helper Board');

		$data = array();
		
		$data['customer_list'] = $this->model_package_status_board->get_customer_list();
		
		$data['carrier_list'] = array(
			'amazon' => 'Amazon',
			'custom' => 'Custom',
			'dhlint' => 'DHL Int',
			'external' => 'External',
			'fedex' => 'FedEx',
			'ups' => 'UPS',
			'usps' => 'USPS'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : array();
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : array();
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_batching_helper->get_batching_helper_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_batching_helper_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_batching_helper_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
