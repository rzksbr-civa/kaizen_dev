<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_empty_spots extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_empty_spots_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);

		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		$redstag_db
			->select('label, COUNT(*) AS count_of_building')
			->from('cataloginventory_stock_location')
			->where_in('location_type_id', array(3,5,7,9))
			->where('qty_unreserved', 0)
			->group_by('label')
			->order_by('label');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('stock_id', $stock_id);
		}
		
		$data['empty_spots_data'] = $redstag_db->get()->result_array();
		
		$data['total_empty_spots'] = 0;
		foreach($data['empty_spots_data'] as $current_data) {
			$data['total_empty_spots'] += $current_data['count_of_building'];
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['empty_spots_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_empty_spots_board_visualization', $data, true);
		$data['js_empty_spots_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_empty_spots_board_visualization', $data, true);
		
		return $data;
	}
}