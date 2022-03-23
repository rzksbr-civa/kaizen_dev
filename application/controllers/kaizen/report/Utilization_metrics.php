<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Utilization_metrics extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_utilization_metrics');
		$this->load->model(PROJECT_CODE.'/model_outbound');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Utilization Metrics Report');

		$data = array();
		
		$data['report_type_list'] = array(
			'utilization_report' => 'Utilization Report',
			'utilization_trend' => 'Utilization Trend'
		);
		
		$data['periodicity_list'] = array(
			array('name' => 'daily', 'label' => 'Daily'),
			array('name' => 'weekly', 'label' => 'Weekly'),
			array('name' => 'monthly', 'label' => 'Monthly'),
			array('name' => 'yearly', 'label' => 'Yearly')
		);
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['store_list'] = $this->model_outbound->get_store_list();
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['report_type'] = isset($_GET['report_type']) ? $_GET['report_type'] : 'utilization_report';
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : array();
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : null;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-7 day'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$data = $this->model_utilization_metrics->get_utilization_metrics_report_data($data);
		}
		
		$footer_data = array();
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_utilization_metrics_report', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_utilization_metrics_report', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
