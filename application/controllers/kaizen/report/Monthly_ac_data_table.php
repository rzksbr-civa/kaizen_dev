<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monthly_ac_data_table extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model(PROJECT_CODE.'/model_kaizen');
    }
	
	public function index() {
		if(empty($date)) {
			$date = date('Y-m-d');
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Monthly AC Data Table');

		$data = array();
		
		$data['abnormal_type_list'] = $this->model_db_crud->get_data(
			'abnormal_types', 
			array(
				'select' => array('id', 'abnormal_type_name'),
				'order_by' => array('abnormal_type_name' => 'asc')
			)
		);
		
		$data['carrier_list'] = $this->model_db_crud->get_data(
			'carriers', 
			array(
				'select' => array('id', 'carrier_name'),
				'order_by' => array('carrier_name' => 'asc')
			)
		);
		
		$data['customer_list'] = $this->model_db_crud->get_data(
			'customers', 
			array(
				'select' => array('id', 'customer_name'),
				'order_by' => array('customer_name' => 'asc')
			)
		);
		
		$data['department_list'] = $this->model_db_crud->get_data(
			'departments', 
			array(
				'select' => array('id', 'department_name'),
				'order_by' => array('department_name' => 'asc')
			)
		);
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : null;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['year'] = isset($_GET['year']) ? $_GET['year'] : date('Y');
		
		$args = array(
			'carrier' => $data['carrier'],
			'customer' => $data['customer'],
			'department' => $data['department'],
			'facility' => $data['facility'],
			'year' => $data['year']
		);
		
		$data['ac_table_data'] = $this->model_kaizen->get_monthly_ac_data($args);
		
		$data['monthly_total'] = array('total'=>0);
		for($i=1; $i<=12; $i++) {
			$data['monthly_total'][$i] = 0;
			foreach($data['ac_table_data'] as $current_data) {
				$data['monthly_total'][$i] += $current_data['monthly_count'][$i];
				$data['monthly_total']['total'] += $current_data['monthly_count'][$i];
			}
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_monthly_ac_data_table', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_monthly_ac_data_table', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
