<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Revenue_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_revenue');
    }
	
	public function index() {
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role') <> USER_ROLE_ADMIN_WITH_FINANCIAL) {
			$this->_show_access_denied_page();
			return;
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Revenue Board');

		$data = array();
		
		$data['report_type_list'] = array(
			'trending_graph' => 'Trending Graph',
			'revenue_summary' => 'Summary',
			'package_pivot' => 'Package Pivot',
			'outbound_packages' => 'Outbound Packages',
			'inbound_pivot' => 'Inbound Pivot',
			'inbound_revenue' => 'Inbound Revenue',
			'wages' => 'Wages'
		);
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['department_list'] = array(
			'inbound' => 'Inbound',
			'inventory' => 'Inventory',
			'kitting' => 'Kitting',
			'leads' => 'Leads',
			'ltl' => 'LTL',
			'material' => 'Material Handling',
			'outbound' => 'Outbound'
		);
		
		$data['periodicity_list'] = array(
			'hourly', 'daily', 'weekly', 'monthly', 'yearly'
		);
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['report_type'] = isset($_GET['report_type']) ? $_GET['report_type'] : 'revenue_summary';
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : 'daily';
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['report_type'] == 'trending_graph') {
			unset($data['periodicity_list'][0]);
		}
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		if($data['generate']) {
			$data = $this->model_revenue->get_revenue_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_revenue_board', $data, true);
		
		if(isset($data['revenue_board_js'])) {
			$footer_data['js'] .= $data['revenue_board_js'];
		}

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_revenue_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
