<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Acs_idle_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_acs_idle');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('ACs Idle Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['data_visibility_list'] = array(
			'not_removed' => 'Not Removed',
			'removed' => 'Removed',
			'all' => 'All'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['data_visibility'] = isset($_GET['data_visibility']) ? $_GET['data_visibility'] : 'not_removed';
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_acs_idle->get_acs_idle_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_acs_idle_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_acs_idle_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
