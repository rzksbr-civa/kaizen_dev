<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_package_board');
    }
	
	public function index() {
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role') <> USER_ROLE_ADMIN_WITH_FINANCIAL) {
			$this->_show_access_denied_page();
			return;
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Package Board');

		$data = array();
		
		$data['breakdown_type_list'] = array(
			'customer' => 'Customer',
			'family' => 'Family',
			'shipment' => 'Shipment',
			'employee' => 'Employee'
		);
		
		$data['calculation_method_list'] = array(
			'uniform' => 'Uniform VA/NVA Time',
			'distributed' => 'Distributed VA/NVA Time'
		);
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['display_type_list'] = array('all', 'summary');

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['breakdown_type'] = isset($_GET['breakdown_type']) ? $_GET['breakdown_type'] : 'customer';
		$data['calculation_method'] = isset($_GET['calculation_method']) ? $_GET['calculation_method'] : 'distributed';
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : array();
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('Yesterday'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d', strtotime('Yesterday'));
		$data['display_type'] = isset($_GET['display_type']) ? $_GET['display_type'] : 'all';
		
		if($data['generate']) {
			$data = $this->model_package_board->get_package_board_data($data);	
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_package_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_package_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
