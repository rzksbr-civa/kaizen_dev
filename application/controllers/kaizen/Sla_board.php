<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sla_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_sla');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Carrier SLA Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		foreach($data['facility_list'] as $key => $facility) {
			if($facility['id'] == 1) { // TYS-1
				$data['facility_list'][$key]['facility_name'] = 'TYS';
			}
			else if($facility['id'] == 3) { // TYS-2
				unset($data['facility_list'][$key]);
			}
		}
		
		$data['carrier_list'] = array(
			'fedex' => 'FedEx',
			'ups' => 'UPS',
			'usps' => 'USPS'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : 1;
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : 'fedex';
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-10 days'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_sla->get_sla_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_sla_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_sla_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
