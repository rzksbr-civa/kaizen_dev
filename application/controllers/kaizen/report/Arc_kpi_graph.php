<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Arc_kpi_graph extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model(PROJECT_CODE.'/model_kaizen');
    }
	
	public function index() {
		if(empty($date)) {
			$date = date('Y-m-d');
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('ARC KPI\'s');

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
		
		$generating_graph = isset($_GET['generate']) ? $_GET['generate'] : false;
		
		$data['generate'] = $generating_graph;
		$data['abnormal_type'] = isset($_GET['abnormal_type']) ? $_GET['abnormal_type'] : null;
		$data['carrier'] = isset($_GET['carrier']) ? $_GET['carrier'] : null;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-7 days'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		$data['graph'] = array();
		
		if($generating_graph) {
			// Get ontime data
			$this_data_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
			
			$args = array(
				'period_from' => $data['period_from'],
				'period_to' => $data['period_to'],
				'abnormal_type' => $data['abnormal_type'],
				'carrier' => $data['carrier'],
				'customer' => $data['customer'],
				'department' => $data['department'],
				'facility' => $data['facility']
			);
			$data['graph'] = $this->model_kaizen->get_kpi_graph_data($args);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_arc_kpi_graph', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_arc_kpi_graph', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
