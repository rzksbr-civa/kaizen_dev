<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_outbound');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Shipment Report');

		$data = array();
		
		$data['report_type_list'] = array(
			array('name' => 'no_breakdown', 'label' => 'No Breakdown'),
			array('name' => 'breakdown_by_product_family', 'label' => 'Breakdown by Product Family')
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
		$data['report_type'] = isset($_GET['report_type']) ? $_GET['report_type'] : null;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : null;
		$data['excluded_customers'] = isset($_GET['excluded_customers']) ? $_GET['excluded_customers'] : array();
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-7 day'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$report_data = $this->model_outbound->get_shipment_report_data($data);
			$data['table_data'] = $report_data['table_data'];
			
			if($data['report_type'] == 'breakdown_by_product_family') {
				$data['assignment_types'] = $report_data['assignment_types'];
				$data['overall_total'] = $report_data['overall_total'];
			}
		}
		
		$footer_data = array();
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_shipment_report_general', $data, true);
		
		switch($data['report_type']) {
			case 'no_breakdown':
				$footer_data['js'] .= $this->load->view(PROJECT_CODE.'/report/js_view_shipment_report', $data, true);
				break;
			case 'breakdown_by_product_family':
				$footer_data['js'] .= $this->load->view(PROJECT_CODE.'/report/js_view_shipment_report_breakdown_by_product_family', $data, true);
				break;
		}

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_shipment_report', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
