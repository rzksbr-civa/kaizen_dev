<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Preshift_check extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_preshift_check');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Preshift Check');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_several_data('facility');
		
		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_preshift_check', $data);
		$this->load->view('view_footer', null);
	}
	
	public function facility($facility_id) {
		$facility_data = $this->model_db_crud->get_specific_data('facility', $facility_id);
		
		if(empty($facility_data)) {
			$this->_show_404_page();
			return;
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Preshift Check ('.$facility_data['facility_name'].')');

		$data = array();
		
		$data['facility_data'] = $facility_data;
		$data['employees'] = $this->model_db_crud->get_data(
			'employees',
			array(
				'order_by' => array('employee_name' => 'asc')
			)
		);
		
		$data['checked_in_employees'] = $this->model_preshift_check->get_checked_in_employees();
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_preshift_check_facility', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_preshift_check_facility', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
