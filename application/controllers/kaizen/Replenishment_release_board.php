<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Replenishment_release_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_replenishment');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Replenishment Release Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$data['store_list'] = $this->model_outbound->get_store_list();
		
		$data['sku_tier_list'] = array(
			'T1' => 'Tier 1',
			'T2' => 'Tier 2',
			'T3' => 'Tier 3',
			'T4' => 'Tier 4',
			'T5' => 'Tier 5'
		);
		
		$data['default_replenish_freq_tier'] = array(
			1 => 3,
			2 => 7,
			3 => 14,
			4 => 30,
			5 => 30
		);
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : null;
		$data['service_level_percentage'] = isset($_GET['service_level_percentage']) ? $_GET['service_level_percentage'] : 97;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-30 day'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		$data['sku_tier'] = isset($_GET['sku_tier']) ? $_GET['sku_tier'] : array();
		
		$data['replenish_freq_tier_1'] = isset($_GET['replenish_freq_tier_1']) ? $_GET['replenish_freq_tier_1'] : $data['default_replenish_freq_tier'][1];
		$data['replenish_freq_tier_2'] = isset($_GET['replenish_freq_tier_2']) ? $_GET['replenish_freq_tier_2'] : $data['default_replenish_freq_tier'][2];
		$data['replenish_freq_tier_3'] = isset($_GET['replenish_freq_tier_3']) ? $_GET['replenish_freq_tier_3'] : $data['default_replenish_freq_tier'][3];
		$data['replenish_freq_tier_4'] = isset($_GET['replenish_freq_tier_4']) ? $_GET['replenish_freq_tier_4'] : $data['default_replenish_freq_tier'][4];
		$data['replenish_freq_tier_5'] = isset($_GET['replenish_freq_tier_5']) ? $_GET['replenish_freq_tier_5'] : $data['default_replenish_freq_tier'][5];
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		if($data['generate']) {
			$data = $this->model_replenishment->get_replenishment_release_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_replenishment_release_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_replenishment_release_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
