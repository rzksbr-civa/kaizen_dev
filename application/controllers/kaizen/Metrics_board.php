<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Metrics_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_outbound');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Metrics Board');

		$data = array();
		
		$data['action_list'] = array('Picking', 'Packing', 'Load');
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['block_time_list'] = $this->model_db_crud->get_data(
			'block_times', 
			array(
				'select' => array('id', 'block_time_name', 'start_time', 'end_time'),
				'order_by' => array('block_time_name' => 'asc')
			)
		);
		
		$data['department_list'] = $this->model_db_crud->get_data(
			'departments', 
			array(
				'select' => array('id', 'department_name'),
				'order_by' => array('department_name' => 'asc')
			)
		);
		
		$data['assignment_type_list'] = $this->model_db_crud->get_data(
			'assignment_types', 
			array(
				'select' => array('id', 'assignment_type_name'),
				'order_by' => array('id' => 'asc')
			)
		);
		
		$data['employee_shift_type_list'] = $this->model_db_crud->get_data(
			'employee_shift_types', 
			array(
				'select' => array('id', 'employee_shift_type_name'),
				'order_by' => array('id' => 'asc')
			)
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['action'] = isset($_GET['action']) ? $_GET['action'] : array();
		$data['block_time'] = isset($_GET['block_time']) ? $_GET['block_time'] : array();
		$data['assignment_type'] = isset($_GET['assignment_type']) ? $_GET['assignment_type'] : array();
		$data['employee_shift_type'] = isset($_GET['employee_shift_type']) ? $_GET['employee_shift_type'] : array();
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$this->load->model(PROJECT_CODE.'/model_outbound');
			$data = $this->model_outbound->get_metrics_board_data($data);

			$data['page_version'] = 1;
			$data['page_generated_time'] = date('Y-m-d H:i:s');
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_metrics_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_metrics_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
