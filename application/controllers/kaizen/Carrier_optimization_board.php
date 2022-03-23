<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carrier_optimization_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_carrier_optimization');
    }
	
	public function index() {
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role') <> USER_ROLE_ADMIN_WITH_FINANCIAL) {
			$this->_show_access_denied_page();
			return;
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Carrier Optimization Board');

		$data = array();
		
		$data['report_type_list'] = array(
			//'summary' => 'Summary',
			'results_by_carrier_base_rate' => 'Results by Carrier Base Rate',
			'results_by_carrier_with_surcharge' => 'Results by Carrier with Surcharge',
			'package_details' => 'Package Details'
		);
		
		$data['merchant_list'] = $this->model_carrier_optimization->get_merchant_list();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['fedex_client_discount_tier_list'] = array(
			'standard_1' => 'Standard - Tier 1 (1-150 Pkgs/week)',
			'standard_2' => 'Standard - Tier 2 (151-450 Pkgs/week)',
			'standard_3' => 'Standard - Tier 3 (451-2000 Pkgs/week)',
			'flat_1' => 'Flat - Tier 1 (1-150 Pkgs/week)',
			'flat_2' => 'Flat - Tier 2 (151-450 Pkgs/week)',
			'flat_3' => 'Flat - Tier 3 (451-2000 Pkgs/week)',
			'lightweight_1' => 'Lightweight - Tier 1 (1-150 Pkgs/week)',
			'lightweight_2' => 'Lightweight - Tier 2 (151-450 Pkgs/week)',
			'lightweight_3' => 'Lightweight - Tier 3 (451-2000 Pkgs/week)'
		);
		
		$data['ups_client_discount_tier_list'] = array(
			'standard_1' => 'Standard - Tier 1 (1-150 Pkgs/week)',
			'standard_2' => 'Standard - Tier 2 (151-450 Pkgs/week)',
			'standard_3' => 'Standard - Tier 3 (451-2000 Pkgs/week)',
			'flat_1' => 'Flat - Tier 1 (1-150 Pkgs/week)',
			'flat_2' => 'Flat - Tier 2 (151-450 Pkgs/week)',
			'flat_3' => 'Flat - Tier 3 (451-2000 Pkgs/week)'
		);
		
		$data['fedex_earned_discount_list'] = array(21, 23, 24, 25, 27, 28, 29, 30);
		$data['ups_earned_discount_list'] = array(0,15.4,17.6,19.8,22,22.3,22.6);
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['report_type'] = isset($_GET['report_type']) ? $_GET['report_type'] : 'package_details';
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['merchant'] = isset($_GET['merchant']) ? $_GET['merchant'] : 149;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d');
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		$data['fedex_earned_discount'] = isset($_GET['fedex_earned_discount']) ? $_GET['fedex_earned_discount'] : 30;
		$data['rsf_fedex_dim_factor'] = isset($_GET['rsf_fedex_dim_factor']) ? $_GET['rsf_fedex_dim_factor'] : 225;
		$data['fedex_client_dim_factor'] = isset($_GET['fedex_client_dim_factor']) ? $_GET['fedex_client_dim_factor'] : 139;
		$data['fedex_reduction_to_minimum'] = isset($_GET['fedex_reduction_to_minimum']) ? $_GET['fedex_reduction_to_minimum'] : 1.25;
		$data['fedex_client_discount_tier'] = isset($_GET['fedex_client_discount_tier']) ? $_GET['fedex_client_discount_tier'] : 'standard_1';
		
		$data['fedex_residential_delivery_published_fee'] = isset($_GET['fedex_residential_delivery_published_fee']) ? $_GET['fedex_residential_delivery_published_fee'] : 4;
		$data['fedex_residential_delivery_rsf_fee'] = isset($_GET['fedex_residential_delivery_rsf_fee']) ? $_GET['fedex_residential_delivery_rsf_fee'] : 1.8;
		$data['fedex_residential_delivery_client_fee'] = isset($_GET['fedex_residential_delivery_client_fee']) ? $_GET['fedex_residential_delivery_client_fee'] : 4;
		
		$data['fedex_ahs_weight_surcharge_published_fee'] = isset($_GET['fedex_ahs_weight_surcharge_published_fee']) ? $_GET['fedex_ahs_weight_surcharge_published_fee'] : 24;
		$data['fedex_ahs_weight_surcharge_rsf_fee'] = isset($_GET['fedex_ahs_weight_surcharge_rsf_fee']) ? $_GET['fedex_ahs_weight_surcharge_rsf_fee'] : 2.4;
		$data['fedex_ahs_weight_surcharge_client_fee'] = isset($_GET['fedex_ahs_weight_surcharge_client_fee']) ? $_GET['fedex_ahs_weight_surcharge_client_fee'] : 24;
		
		$data['fedex_ahs_dimension_surcharge_published_fee'] = isset($_GET['fedex_ahs_dimension_surcharge_published_fee']) ? $_GET['fedex_ahs_dimension_surcharge_published_fee'] : 15;
		$data['fedex_ahs_dimension_surcharge_rsf_fee'] = isset($_GET['fedex_ahs_dimension_surcharge_rsf_fee']) ? $_GET['fedex_ahs_dimension_surcharge_rsf_fee'] : 3.75;
		$data['fedex_ahs_dimension_surcharge_client_fee'] = isset($_GET['fedex_ahs_dimension_surcharge_client_fee']) ? $_GET['fedex_ahs_dimension_surcharge_client_fee'] : 15;
		
		
		$data['ups_earned_discount'] = isset($_GET['ups_earned_discount']) ? $_GET['ups_earned_discount'] : 19.8;
		$data['rsf_ups_dim_factor'] = isset($_GET['rsf_ups_dim_factor']) ? $_GET['rsf_ups_dim_factor'] : 235;
		$data['ups_client_dim_factor'] = isset($_GET['ups_client_dim_factor']) ? $_GET['ups_client_dim_factor'] : 139;
		$data['ups_reduction_to_minimum'] = isset($_GET['ups_reduction_to_minimum']) ? $_GET['ups_reduction_to_minimum'] : 1.75;
		$data['ups_client_discount_tier'] = isset($_GET['ups_client_discount_tier']) ? $_GET['ups_client_discount_tier'] : 'standard_1';
		
		$data['ups_residential_delivery_published_fee'] = isset($_GET['ups_residential_delivery_published_fee']) ? $_GET['ups_residential_delivery_published_fee'] : 4.1;
		$data['ups_residential_delivery_rsf_fee'] = isset($_GET['ups_residential_delivery_rsf_fee']) ? $_GET['ups_residential_delivery_rsf_fee'] : 1.8;
		$data['ups_residential_delivery_client_fee'] = isset($_GET['ups_residential_delivery_client_fee']) ? $_GET['ups_residential_delivery_client_fee'] : 4.1;
		
		$data['ups_ahs_weight_surcharge_published_fee'] = isset($_GET['ups_ahs_weight_surcharge_published_fee']) ? $_GET['ups_ahs_weight_surcharge_published_fee'] : 24;
		$data['ups_ahs_weight_surcharge_rsf_fee'] = isset($_GET['ups_ahs_weight_surcharge_rsf_fee']) ? $_GET['ups_ahs_weight_surcharge_rsf_fee'] : 12;
		$data['ups_ahs_weight_surcharge_client_fee'] = isset($_GET['ups_ahs_weight_surcharge_client_fee']) ? $_GET['ups_ahs_weight_surcharge_client_fee'] : 24;
		
		$data['ups_ahs_dimension_surcharge_published_fee'] = isset($_GET['ups_ahs_dimension_surcharge_published_fee']) ? $_GET['ups_ahs_dimension_surcharge_published_fee'] : 15;
		$data['ups_ahs_dimension_surcharge_rsf_fee'] = isset($_GET['ups_ahs_dimension_surcharge_rsf_fee']) ? $_GET['ups_ahs_dimension_surcharge_rsf_fee'] : 7.5;
		$data['ups_ahs_dimension_surcharge_client_fee'] = isset($_GET['ups_ahs_dimension_surcharge_client_fee']) ? $_GET['ups_ahs_dimension_surcharge_client_fee'] : 15;
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		if($data['generate']) {
			$data = $this->model_carrier_optimization->get_carrier_optimization_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_carrier_optimization_board', $data, true);
		
		if(isset($data['revenue_board_js'])) {
			$footer_data['js'] .= $data['revenue_board_js'];
		}

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_carrier_optimization_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
