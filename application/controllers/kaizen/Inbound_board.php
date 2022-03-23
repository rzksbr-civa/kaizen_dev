<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inbound_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_inbound');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Inbound Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['periodicity_list'] = array('daily', 'weekly', 'monthly', 'yearly');

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : 'daily';
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_inbound->get_inbound_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_inbound_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_inbound_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
