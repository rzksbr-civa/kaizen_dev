<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_record_screen extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_kaizen');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('AC Record Screen');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['customer_list'] = $this->model_db_crud->get_data(
			'customers', 
			array(
				'select' => array('id', 'customer_name'),
				'order_by' => array('customer_name' => 'asc')
			)
		);
		
		$data['abnormal_type_list'] = $this->model_db_crud->get_data(
			'abnormal_types', 
			array(
				'select' => array('id', 'abnormal_type_name'),
				'order_by' => array('abnormal_type_name' => 'asc')
			)
		);
		
		$data['employee_list'] = $this->model_db_crud->get_data(
			'employees', 
			array(
				'select' => array('id', 'employee_name'),
				'order_by' => array('employee_name' => 'asc')
			)
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			//$data = $this->model_kaizen->get_ac_record_screen_data($data);

			$data['page_generated_time'] = date('Y-m-d H:i:s');
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_ac_record_screen', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_ac_record_screen', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
