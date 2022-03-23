<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API extends CI_Controller {
	public function __construct(){
        parent::__construct();
		
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_logged_in') !== TRUE) {
			$result = array('success'=>false, 'error_message'=>'User not logged in', 'logged_in'=>false);
			echo json_encode($result);
			exit;
		}
		
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$this->_show_404_page();
	}
	
	public function get_carrier_tracking_status() {
		$result = array();

		$args = array(
			'carrier_code' => $this->input->post('carrier_code'),
			'track_number' => $this->input->post('track_number'),
			'mwe_expected_delivery_date' => $this->input->post('mwe_expected_delivery_date'),
			'mwe_status' => $this->input->post('mwe_status')
		);

		$this->load->model(PROJECT_CODE.'/model_carrier_status_dashboard');
		$result = $this->model_carrier_status_dashboard->get_carrier_tracking_status($args);
		
		echo json_encode($result);
	}
	
	public function get_shipment_board_data() {
		$result = array();
		
		$input = $this->input->post('data');
		
		$data = array();
		foreach($input as $item) {
			if(substr($item['name'], -2) == '[]') {
				$data[substr($item['name'], 0, -2)][] = $item['value'];
			}
			else {
				$data[$item['name']] = $item['value'];
			}
		}
		
		$this->load->model(PROJECT_CODE.'/model_shipment_board');
		$shipment_board_data = $this->model_shipment_board->get_shipment_board_data($data);
		
		$result['shipment_board_visualization_html'] = $shipment_board_data['shipment_board_visualization_html'];
		$result['js_shipment_board_visualization_html'] = $shipment_board_data['js_shipment_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function bulk_update_carrier_tracking_status() {
		$result = array();
		
		$package_ids = $this->input->post('package_ids');
		
		$this->load->model(PROJECT_CODE.'/model_carrier_status_dashboard');
		$result['updated_packages'] = $this->model_carrier_status_dashboard->update_carrier_info_of_packages_by_package_ids($package_ids);
	
		$result['success'] = true;
		
		echo json_encode($result);
	}
	
	public function get_sku_historical_demand_data() {
		$result = array();
		
		$args = array(
			'sku' => $this->input->post('sku'),
			'customer' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group')
		);

		$this->load->model(PROJECT_CODE.'/model_client_inventory_optimization_board');
		$result = $this->model_client_inventory_optimization_board->get_sku_historical_demand_data($args);
		
		echo json_encode($result);
	}
	
	public function get_historical_inventory_levels_graph_data() {
		$result = array();
		
		$args = array(
			'sku' => $this->input->post('sku'),
			'customer' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group')
		);

		$this->load->model(PROJECT_CODE.'/model_client_inventory_optimization_board');
		$result = $this->model_client_inventory_optimization_board->get_historical_inventory_levels_graph_data($args);
		
		echo json_encode($result);
	}
}
