<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Work_summary extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_team_helper');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Work Summary');

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
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : 'weekly';
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-7 day'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$data = $this->model_team_helper->get_work_summary_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_work_summary', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_work_summary', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
