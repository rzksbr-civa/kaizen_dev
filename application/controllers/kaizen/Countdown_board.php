<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Countdown_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_countdown');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Countdown Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
		}
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5;
		$timezone += date('I'); // Daylight saving time
		$current_local_time = date('Y-m-d H:i:s', strtotime($timezone.' hours '.gmdate('Y-m-d H:i:s')));

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : 1;
		$data['start_time'] = isset($_GET['start_time']) ? $_GET['start_time'] : date('H:i', strtotime('-5 min '.$current_local_time));
		$data['end_time'] = isset($_GET['end_time']) ? $_GET['end_time'] : '17:00';
		$data['cut_off_time'] = isset($_GET['cut_off_time']) ? $_GET['cut_off_time'] : '17:00';
		
		$data['break_time_1_start'] = isset($_GET['break_time_1_start']) ? $_GET['break_time_1_start'] : '10:00';
		$data['break_time_1_end'] = isset($_GET['break_time_1_end']) ? $_GET['break_time_1_end'] : '10:15';
		$data['break_time_2_start'] = isset($_GET['break_time_2_start']) ? $_GET['break_time_2_start'] : '13:00';
		$data['break_time_2_end'] = isset($_GET['break_time_2_end']) ? $_GET['break_time_2_end'] : '13:30';
		$data['break_time_3_start'] = isset($_GET['break_time_3_start']) ? $_GET['break_time_3_start'] : '16:00';
		$data['break_time_3_end'] = isset($_GET['break_time_3_end']) ? $_GET['break_time_3_end'] : '16:15';
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_countdown->get_countdown_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_countdown_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_countdown_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
