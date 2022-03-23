<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scoreboard extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Scoreboard');

		$data = array();
		
		$data['type_list'] = array('User', 'Order', 'Shipment', 'Package', 'Stock', 'Delivery', 'Batch', 'Relocation', 'Manifest');
		
		$data['action_list'] = array('Picking', 'Packing', 'Accept', 'Reject', 'Clock In', 'Clock Out', 'Process', 'Put-Away', 'Relocate', 'Seal', 'Load', 'Packaging');
		
		$data['sort_by_list'] = array('Qty', 'Total Time', 'Average Time');
		
		$data['status_list'] = array('Delivery', 'Processing', 'Put-Away', 'Picking' => 'Picking', 'Packing' => 'Packing', 'Cycle Count', 'Relocation', 'Loading' => 'Load', 'Kitting', 'Paid Break', 'Unpaid Break', 'Cleaning', 'Management Request', 'Replenishment', 'Support', 'Team Meeting', 'Training');
		
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
		$data['type'] = isset($_GET['type']) ? $_GET['type'] : array();
		$data['action'] = isset($_GET['action']) ? $_GET['action'] : array();
		$data['block_time'] = isset($_GET['block_time']) ? $_GET['block_time'] : array();
		$data['assignment_type'] = isset($_GET['assignment_type']) ? $_GET['assignment_type'] : array();
		$data['employee_shift_type'] = isset($_GET['employee_shift_type']) ? $_GET['employee_shift_type'] : array();
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : 1;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['sort_by'] = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'Average Time';
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		$data['time_from'] = isset($_GET['time_from']) ? $_GET['time_from'] : null;
		$data['time_to'] = isset($_GET['time_to']) ? $_GET['time_to'] : null;
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		$current_block_time = 0;
		
		if($data['generate']) {
			$this->load->model(PROJECT_CODE.'/model_scoreboard');
			$scoreboard = $this->model_scoreboard->get_scoreboard_data($data);
			
			$data['scoreboard'] = $scoreboard['scoreboard_data'];
			$data['scoreboard_tables_html'] = $scoreboard['scoreboard_tables_html'];
			
			$data['page_generated_time'] = date('Y-m-d H:i:s');
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_scoreboard', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_scoreboard', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
