<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_inventory_optimization_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_general');
		$this->load->model(PROJECT_CODE.'/model_client_inventory_optimization_board');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Client Inventory Optimization Board');

		$data = array();
		
		$data['sort_order_list'] = array(
			'highest_to_lowest' => 'Highest to Lowest',
			'lowest_to_highest' => 'Lowest to Highest'
		);
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['customer'] = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		$data['service_level_percentage'] = isset($_GET['service_level_percentage']) ? $_GET['service_level_percentage'] : 97;
		$data['sort_order'] = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'highest_to_lowest';
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		if($data['generate']) {
			$data = $this->model_client_inventory_optimization_board->get_client_inventory_optimization_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_client_inventory_optimization_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_client_inventory_optimization_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
