<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Outbound_performance_kpi extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model(PROJECT_CODE.'/model_outbound');
    }
	
	public function by($report_type = null) {
		if(empty($report_type) || !in_array($report_type, array('status', 'user'))) {
			$this->_show_404_page();
		}
		
		switch($report_type) {
			case 'status':
				return $this->_get_performance_kpi_report_by_status();
			case 'user':
				return $this->_get_performance_kpi_report_by_user();
			default:
				$this->_show_404_page();
		}
	}
	
	public function _get_performance_kpi_report_by_status() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Performance KPI Report by Status');

		$data = array();
		
		$data['status_list'] = array('loading', 'packing', 'picking');
		$data['periodicity_list'] = array('daily', 'weekly', 'monthly', 'quarterly', 'yearly');
		$data['data_to_show_list'] = array('qty' => 'Count of Type', 'time' => 'Sum of Time', 'average' => 'Average Count of Type Per Hour');
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['data_to_show'] = isset($_GET['data_to_show']) ? $_GET['data_to_show'] : 'average';
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : null;
		$data['status'] = isset($_GET['status']) ? $_GET['status'] : array();
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$data['graph_data'] = $this->model_outbound->get_performance_kpi_by_status_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_outbound_performance_kpi_by_status', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_outbound_performance_kpi_by_status', $data);
		$this->load->view('view_footer', $footer_data);
	}
	
	public function _get_performance_kpi_report_by_user() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Performance KPI Report by User');

		$data = array();
		
		$data['status_list'] = array('Loading', 'Packing', 'Picking');
		$data['periodicity_list'] = array('daily', 'weekly', 'monthly', 'quarterly', 'yearly');
		$data['data_to_show_list'] = array('qty' => 'Count of Type', 'time' => 'Sum of Time', 'average' => 'Average Count of Type Per Hour');
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['data_to_show'] = isset($_GET['data_to_show']) ? $_GET['data_to_show'] : 'average';
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : null;
		$data['status'] = isset($_GET['status']) ? $_GET['status'] : array();
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$data['data'] = $this->model_outbound->get_performance_kpi_by_user_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_outbound_performance_kpi_by_user', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_outbound_performance_kpi_by_user', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
