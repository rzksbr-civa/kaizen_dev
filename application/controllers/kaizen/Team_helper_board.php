<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Team_helper_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_team_helper');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Team Helper Board');

		$data = array();
		
		$data['action_list'] = array('Picking', 'Packing', 'Load');
		
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

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_team_helper->get_team_helper_data($data);

			$data['page_generated_time'] = date('Y-m-d H:i:s');
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_team_helper_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_team_helper_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
