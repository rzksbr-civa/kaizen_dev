<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_status_board extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_status_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);

		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : null;
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		$redstag_db
			->select('status, admin_user.name')
			->from('time_log')
			->join('admin_user', 'admin_user.user_id = time_log.user_id')
			->where('finished_at IS NULL', null, false)
			->where("IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern')) >= '".trim($data['start_date']." ".$data['start_time'])."'", null, false)
			->group_by('status, name')
			->order_by('status', 'asc')
			->order_by('name', 'asc');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('stock_id', $stock_id);
		}
		
		if(!empty($data['status'])) {
			$redstag_db->where_in('status', $data['status']);
		}
		
		$active_staffs_data_tmp = $redstag_db->get()->result_array();
		
		$data['all_staffs'] = array();
		$active_staffs_data = array();
		
		foreach($active_staffs_data_tmp as $current_data) {
			if(!isset($active_staffs_data[$current_data['status']])) {
				$active_staffs_data[$current_data['status']] = array();
			}
			
			$active_staffs_data[$current_data['status']][] = $current_data['name'];
			$data['all_staffs'][$current_data['name']] = true;
		}

		$data['active_staffs_data'] = $active_staffs_data;
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['status_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_status_board_visualization', $data, true);
		
		return $data;
	}
}