<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_inventory_replenishment_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_replenishment');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Client Inventory Optimization Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['sort_order_list'] = array(
			'highest_to_lowest' => 'Highest to Lowest',
			'lowest_to_highest' => 'Lowest to Highest'
		);
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$data['store_list'] = $this->model_outbound->get_store_list();
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : null;
		$data['service_level_percentage'] = isset($_GET['service_level_percentage']) ? $_GET['service_level_percentage'] : 97;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-90 day'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		$data['sort_order'] = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'highest_to_lowest';
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		if($data['generate']) {
			$data = $this->model_replenishment->get_client_inventory_replenishment_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_client_inventory_replenishment_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_client_inventory_replenishment_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
