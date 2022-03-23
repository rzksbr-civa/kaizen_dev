<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Action_log_data_error extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_package_board');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Action Log Data Error Report');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-7 day'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$report_data = $this->model_package_board->get_action_log_error_data($data);
			$data['table_data'] = $report_data['table_data'];
		}
		
		$footer_data = array();
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_action_log_data_error_report', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_action_log_data_error_report', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
