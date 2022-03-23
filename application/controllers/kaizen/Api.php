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
		$this->load->model(PROJECT_CODE.'/model_kaizen');
    }
	
	public function index() {
		$this->_show_404_page();
	}
	
	public function assign_employee() {
		$result = array();

		$args = array(
			'employee_id' => $this->input->post('employee_id'),
			'date' => $this->input->post('date'),
			'shift' => $this->input->post('shift'),
			'assignment_type' => $this->input->post('assignment_type')
		);

		$this->load->model(PROJECT_CODE.'/model_assignment');
		$result = $this->model_assignment->assign_employee($args);
		
		echo json_encode($result);
	}
	
	public function get_current_employee_assignment() {
		$result = array();

		$args = array(
			'employee_name' => $this->input->post('employee_name'),
			'date' => $this->input->post('date')
		);

		$this->load->model(PROJECT_CODE.'/model_assignment');
		$result = $this->model_assignment->get_current_employee_assignment($args);
		
		echo json_encode($result);
	}
	
	public function do_edit_employee_assignment() {
		$result = array();
		
		$input = $this->input->post('data');
		
		$args = array();
		foreach($input as $current_input) {
			if(substr($current_input['name'], 0, strlen('assignment_type_')) === 'assignment_type_') {
				$parts = explode('_', $current_input['name']);
				$block = $parts[count($parts)-1];
				
				$args['assignments'][$block] = $current_input['value'];
			}
			else {
				$args[$current_input['name']] = $current_input['value'];
			}
		}
		
		$args['date'] = $this->input->post('date');
		
		$this->load->model(PROJECT_CODE.'/model_assignment');
		$result = $this->model_assignment->edit_employee_assignment($args);
		
		echo json_encode($result);
	}
	
	public function get_scoreboard_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_scoreboard');
		$scoreboard = $this->model_scoreboard->get_scoreboard_data($data);
		
		$result['scoreboard_tables_html'] = $scoreboard['scoreboard_tables_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_takt_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$takt_board_data = $this->model_outbound->get_takt_board_data($data);
		
		$result['cost_calculation_section_html'] = $takt_board_data['cost_calculation_section_html'];
		$result['block_times_section_html'] = $takt_board_data['block_times_section_html'];
		$result['completed_shipment_section_html'] = $takt_board_data['completed_shipment_section_html'];
		$result['graph_section_html'] = $takt_board_data['graph_section_html'];
		$result['js_graph_section_html'] = $takt_board_data['js_graph_section_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_metrics_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$metrics_board_data = $this->model_outbound->get_metrics_board_data($data);
		
		$result['evolution_points_leaderboard_html'] = $metrics_board_data['evolution_points_leaderboard_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_takt_data() {
		$result = array();

		$args = array(
			'facility' => $this->input->post('facility'),
			'date' => $this->input->post('date')
		);

		$this->load->model(PROJECT_CODE.'/model_assignment');
		$takt_data = $this->model_assignment->get_takt_data($args);
		
		if(!empty($takt_data)) {
			$result['success'] = true;
			$result['takt_data'] = $takt_data;
		}
		else {
			$result['success'] = false;
		}
		
		echo json_encode($result);
	}
	
	public function do_edit_takt_data() {
		$result = array();
		
		$input = $this->input->post('data');

		$args = array();
		foreach($input as $current_input) {
			$args[$current_input['name']] = $current_input['value'];
		}
		
		$args['facility'] = $this->input->post('facility');
		$args['date'] = $this->input->post('date');
		
		$this->load->model(PROJECT_CODE.'/model_assignment');
		$result = $this->model_assignment->edit_takt_data($args);
		
		echo json_encode($result);
	}
	
	public function do_set_metrics_board_note() {
		$result = array();
		
		$input = $this->input->post('data');
		
		$args = array();
		
		foreach($input as $current_input) {
			$args[$current_input['name']] = $current_input['value'];
		}
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$result = $this->model_outbound->set_metrics_board_note($args);
		
		echo json_encode($result);
	}
	
	public function get_team_helper_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_team_helper');
		$team_helper_board_data = $this->model_team_helper->get_team_helper_data($data);
		
		$result['team_helper_staff_time_log_html'] = $team_helper_board_data['team_helper_staff_time_log_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_loading_andon_board_data() {
		$result = array();
		
		$input = $this->input->post('data');
		
		$data = array();
		foreach($input as $item) {
			$data[$item['name']] = $item['value'];
		}
		
		$this->load->model(PROJECT_CODE.'/model_loading_andon');
		$loading_andon_board_data = $this->model_loading_andon->get_loading_andon_board_data($data);
		
		$result['loading_andon_board_visualization_html'] = $loading_andon_board_data['loading_andon_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $loading_andon_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_idle_status_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_idle_status');
		$idle_status_board_data = $this->model_idle_status->get_idle_status_board_data($data);
		
		$result['idle_status_board_visualization_html'] = $idle_status_board_data['idle_status_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $idle_status_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_idle_break_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_idle_status');
		$idle_break_board_data = $this->model_idle_status->get_idle_break_board_data($data);
		
		$result['idle_break_board_visualization_html'] = $idle_break_board_data['idle_break_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $idle_break_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function submit_preshift_check() {
		$result = array();
		
		$input = $this->input->post('data');
		
		$data = array();
		$data['responses'] = array();
		
		$data['date'] = $this->input->post('date');
		$data['time'] = $this->input->post('time');
		$data['facility_id'] = $this->input->post('facility_id');
		
		foreach($input as $item) {
			if(!is_numeric($item['name'])) {
				$data[$item['name']] = $item['value'];
			}
			else {
				$data['responses'][$item['name']] = $item['value'];
			}
		}
		
		$this->load->model(PROJECT_CODE.'/model_preshift_check');
		$result = $this->model_preshift_check->checkin($data);
		
		echo json_encode($result);
	}
	
	public function get_countdown_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_countdown');
		$result = $this->model_countdown->get_countdown_board_data($data);

		$result['success'] = true;
		$result['page_last_updated'] = $result['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_acs_idle_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_acs_idle');
		$acs_idle_board_data = $this->model_acs_idle->get_acs_idle_board_data($data);
		
		$result['acs_idle_board_visualization_html'] = $acs_idle_board_data['acs_idle_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $acs_idle_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function change_idle_order_state() {
		$result = array();
		
		$order_no = $this->input->post('order_no');
		$action = $this->input->post('action');
		
		$this->load->model(PROJECT_CODE.'/model_acs_idle');
		$result = $this->model_acs_idle->change_idle_order_state($action, $order_no);
		
		echo json_encode($result);
	}
	
	public function get_inbound_idle_time_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_inbound_idle_time');
		$inbound_idle_time_board_data = $this->model_inbound_idle_time->get_inbound_idle_time_board_data($data);
		
		$result['inbound_idle_time_waiting_asns_html'] = $inbound_idle_time_board_data['inbound_idle_time_waiting_asns_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $inbound_idle_time_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_carrier_tracking_status() {
		$result = array();

		$args = array(
			'carrier_code' => $this->input->post('carrier_code'),
			'track_number' => $this->input->post('track_number'),
			'mwe_expected_delivery_date' => $this->input->post('mwe_expected_delivery_date'),
			'mwe_status' => $this->input->post('mwe_status')
		);

		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$result = $this->model_package_status_board->get_carrier_tracking_status($args);
		
		echo json_encode($result);
	}
	
	public function get_inventory_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$inventory_board_data = $this->model_inventory->get_inventory_board_data($data);
		
		$result['inventory_board_visualization_html'] = $inventory_board_data['inventory_board_visualization_html'];
		$result['js_inventory_board_visualization_html'] = $inventory_board_data['js_inventory_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_live_drops_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$live_drops_board_data = $this->model_inventory->get_live_drops_board_data($data);
		
		$result['live_drops_board_visualization_html'] = $live_drops_board_data['live_drops_board_visualization_html'];
		$result['js_live_drops_board_visualization_html'] = $live_drops_board_data['js_live_drops_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = date('Y-m-d H:i:s');
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_loading_utilization_board_data() {
		$result = array();
		
		$input = $this->input->post('data');
		
		$data = array();
		foreach($input as $item) {
			$data[$item['name']] = $item['value'];
		}
		
		$this->load->model(PROJECT_CODE.'/model_loading_andon');
		$loading_utilization_board_data = $this->model_loading_andon->get_loading_utilization_board_data($data);
		
		$result['loading_utilization_board_visualization_html'] = $loading_utilization_board_data['loading_utilization_board_visualization_html'];
		$result['js_loading_utilization_board_visualization_html'] = $loading_utilization_board_data['js_loading_utilization_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $loading_utilization_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_idle_picking_batch_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_idle_status');
		$idle_picking_batch_board_data = $this->model_idle_status->get_idle_picking_batch_board_data($data);
		
		$result['idle_picking_batch_board_visualization_html'] = $idle_picking_batch_board_data['idle_picking_batch_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $idle_picking_batch_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function bulk_update_carrier_tracking_status() {
		$result = array();
		
		$package_ids = $this->input->post('package_ids');
		
		$this->load->model(PROJECT_CODE.'/model_package');
		$result['updated_packages'] = $this->model_package->update_carrier_info_of_packages_by_package_ids($package_ids);
	
		$result['success'] = true;
		
		echo json_encode($result);
	}
	
	public function get_status_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_status_board');
		$status_board_data = $this->model_status_board->get_status_board_data($data);
		
		$result['status_board_visualization_html'] = $status_board_data['status_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $status_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_sku_historical_demand_data() {
		$result = array();
		
		$args = array(
			'sku' => $this->input->post('sku'),
			'customer' => $this->input->post('customer')
		);

		$this->load->model(PROJECT_CODE.'/model_replenishment');
		$result = $this->model_replenishment->get_sku_historical_demand_data($args);
		
		echo json_encode($result);
	}
	
	public function get_historical_inventory_levels_graph_data() {
		$result = array();
		
		$args = array(
			'sku' => $this->input->post('sku'),
			'customer' => $this->input->post('customer')
		);

		$this->load->model(PROJECT_CODE.'/model_replenishment');
		$result = $this->model_replenishment->get_historical_inventory_levels_graph_data($args);
		
		echo json_encode($result);
	}
	
	public function get_kaizen_manager_data() {
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
		
		$data['break_times'][1]['start'] = $data['break_time_1_start'];
		$data['break_times'][1]['end'] = $data['break_time_1_end'];
		$data['break_times'][2]['start'] = $data['break_time_2_start'];
		$data['break_times'][2]['end'] = $data['break_time_2_end'];
		$data['break_times'][3]['start'] = $data['break_time_3_start'];
		$data['break_times'][3]['end'] = $data['break_time_3_end'];
		$data['break_times'][4]['start'] = $data['break_time_4_start'];
		$data['break_times'][4]['end'] = $data['break_time_4_end'];

		$this->load->model(PROJECT_CODE.'/model_kaizen_manager');
		$result = $this->model_kaizen_manager->get_kaizen_manager_data($data);
		
		$result['success'] = true;
		$result['page_last_updated'] = $result['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function get_idle_manifest_board_data() {
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
		
		$this->load->model(PROJECT_CODE.'/model_idle_status');
		$idle_manifest_board_data = $this->model_idle_status->get_idle_manifest_board_data($data);
		
		$result['idle_manifest_board_visualization_html'] = $idle_manifest_board_data['idle_manifest_board_visualization_html'];
		
		$result['success'] = true;
		$result['page_last_updated'] = $idle_manifest_board_data['page_generated_time'];
		$result['page_version'] = $data['page_version'] + 1;
		
		echo json_encode($result);
	}
	
	public function update_package_postcode_info($date) {
		$this->load->model(PROJECT_CODE.'/model_package');
		$this->model_package->update_postcode_info($date);
		
		echo $date;
		
		$one_day_before = date('Y-m-d', strtotime('-1 day '.$date));
		echo "<script> window.location = '".base_url(PROJECT_CODE.'/api/update_package_postcode_info/'.$one_day_before)."'; </script>";
	}
	
	public function get_ups_tracking($track_number) {
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$result = $this->model_package_status_board->get_ups_tracking_status(array('track_number'=>$track_number));
		
		print_r($result);
	}
	
	public function get_latest_action_log_time() {
		$redstag_db = $this->load->database('redstag', TRUE);
		$latest_action_log = $redstag_db
			->select('started_at')
			->from('action_log')
			->order_by('log_id', 'desc')
			->limit(1)
			->get()->result_array();
		
		echo $latest_action_log[0]['started_at'];
	}
}
