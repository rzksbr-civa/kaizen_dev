<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Idle_status_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_idle_status');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Idle Status Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['department_list'] = $this->model_db_crud->get_data(
			'departments', 
			array(
				'select' => array('id', 'department_name'),
				'order_by' => array('department_name' => 'asc')
			)
		);
		
		$data['status_list'] = array(
			'picking' => 'Picking',
			'packing' => 'Packing',
			'loading' => 'Loading',
			'team_meeting' => 'Team Meeting',
			'replenishment' => 'Repenishment',
			'management_request' => 'Management Request',
			'cleaning' => 'Cleaning',
			'training' => 'Training');

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : array();
		$data['status'] = isset($_GET['status']) ? $_GET['status'] : array();
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_idle_status->get_idle_status_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_idle_status_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_idle_status_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
