<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_kaizen_manager extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_kaizen_manager_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data_tmp = $this->db
				->select('*')
				->from('facilities')
				->where('data_status',DATA_ACTIVE)
				->where('id',$data['facility'])
				->get()->result_array();
				
			if(!empty($facility_data_tmp)) {
				$facility_data = $facility_data_tmp[0];
			}
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2;
		
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I');
		
		$timezone_name = ($stock_id == 3 || $stock_id == 6) ? 'US/Mountain' : 'US/Eastern';
		
		if(!isset($data['start_date'])) {
			$data['start_date'] = date('Y-m-d');
		}
		
		if(!isset($data['start_time'])) {
			$data['start_time'] = '08:00:00';
		}
		
		if(!isset($data['end_time'])) {
			$data['end_time'] = '18:00:00';
		}
		
		$start_datetime = $data['start_date'] . ' ' . $data['start_time'];
		$end_datetime = (strtotime($data['end_time']) > strtotime($data['start_time'])) ? ($data['start_date'] . ' ' . $data['end_time']) : (date('Y-m-d', strtotime('+1 day '.$data['start_date'])) . ' ' . $data['end_time']);
		
		$time_template = array();
		$this_hour = date('Y-m-d H:00', strtotime($start_datetime));
		
		while(strtotime($this_hour) < strtotime($end_datetime)) {
			$time_template[date('H:00', strtotime($this_hour))] = null;
			$this_hour = date('Y-m-d H:00', strtotime('+1 hour '.$this_hour));
		}
		
		$packing_action_count_data_tmp = $redstag_db
			->select("HOUR(CONVERT_TZ(finished_at,'UTC','".$timezone_name."')) AS the_hour, COUNT(*) AS total_action", false)
			->from('action_log')
			->where('action', 'pack')
			->where("finished_at >= CONVERT_TZ('".$start_datetime."','".$timezone_name."','UTC')", null, false)
			->where("finished_at < CONVERT_TZ('".$end_datetime."','".$timezone_name."','UTC')", null, false)
			->where('stock_id', $stock_id)
			->group_by("HOUR(CONVERT_TZ(finished_at,'UTC','".$timezone_name."'))")
			->get()->result_array();
		
		$packing_action_count_data = $time_template;
		if(isset($packing_action_count_data_tmp)) {
			foreach($packing_action_count_data_tmp as $current_data) {
				$packing_action_count_data[sprintf('%02d:00', $current_data['the_hour'])] = $current_data['total_action'];
			}
		}
		
		$picking_action_count_data_tmp = $redstag_db
			->select("HOUR(CONVERT_TZ(finished_at,'UTC','".$timezone_name."')) AS the_hour, COUNT(*) AS total_action", false)
			->from('action_log')
			->where('action', 'pick')
			->where("finished_at >= CONVERT_TZ('".$start_datetime."','".$timezone_name."','UTC')", null, false)
			->where("finished_at < CONVERT_TZ('".$end_datetime."','".$timezone_name."','UTC')", null, false)
			->where('stock_id', $stock_id)
			->group_by("HOUR(CONVERT_TZ(finished_at,'UTC','".$timezone_name."'))")
			->get()->result_array();
		
		$picking_action_count_data = $time_template;
		if(isset($picking_action_count_data_tmp)) {
			foreach($picking_action_count_data_tmp as $current_data) {
				$picking_action_count_data[sprintf('%02d:00', $current_data['the_hour'])] = $current_data['total_action'];
			}
		}
		
		$loading_action_count_data_tmp = $redstag_db
			->select("HOUR(CONVERT_TZ(finished_at,'UTC','".$timezone_name."')) AS the_hour, COUNT(*) AS total_action", false)
			->from('action_log')
			->where('action', 'load')
			->where("finished_at >= CONVERT_TZ('".$start_datetime."','".$timezone_name."','UTC')", null, false)
			->where("finished_at < CONVERT_TZ('".$end_datetime."','".$timezone_name."','UTC')", null, false)
			->where('stock_id', $stock_id)
			->group_by("HOUR(CONVERT_TZ(finished_at,'UTC','".$timezone_name."'))")
			->get()->result_array();
		
		$loading_action_count_data = $time_template;
		if(isset($loading_action_count_data_tmp)) {
			foreach($loading_action_count_data_tmp as $current_data) {
				$loading_action_count_data[sprintf('%02d:00', $current_data['the_hour'])] = $current_data['total_action'];
			}
		}
		
		$packing_picking_loading_pace_data_tmp = $redstag_db
			->select('action, COUNT(*) / '.$data['pace_period'].' AS total_action_per_minute')
			->from('action_log')
			->where('finished_at >=', gmdate('Y-m-d H:i:s', strtotime('-'.$data['pace_period'].' min')))
			->where('stock_id', $stock_id)
			->where_in('action', array('pack','pick','load'))
			->group_by('action')
			->get()->result_array();
		
		$data['action_pace'] = array(
			'picking' => 0,
			'packing' => 0,
			'loading' => 0
		);
		
		if(!empty($packing_picking_loading_pace_data_tmp)) {
			foreach($packing_picking_loading_pace_data_tmp as $current_data) {
				switch($current_data['action']) {
					case 'pack':
						$data['action_pace']['packing'] = round($current_data['total_action_per_minute'],2);
						break;
					case 'pick':
						$data['action_pace']['picking'] = round($current_data['total_action_per_minute'],2);
						break;
					case 'load':
						$data['action_pace']['loading'] = round($current_data['total_action_per_minute'],2);
						break;
				}
			}
		}

		$data['packing_action_count'] = $packing_action_count_data;
		$data['picking_action_count'] = $picking_action_count_data;
		$data['loading_action_count'] = $loading_action_count_data;
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$takt_board_data = $this->model_outbound->get_takt_board_data($data);
		
		// First 15 mins is also considered a break
		$data['break_times'][] = array(
			'start' => date('H:i', strtotime($start_datetime)),
			'end' => date('H:i', strtotime('+15 min '.$start_datetime))
		);
		
		$break_time_in_minutes = array();
		$inactive_mins = array();
		for($i=0; $i<24; $i++) {
			$inactive_mins[sprintf('%02d:00',$i)] = 0;
		}
		foreach($data['break_times'] as $break_time) {
			if(!empty($break_time['start']) && !empty($break_time['end'])) {
				$max_mins = (strtotime(date('Y-m-d H:00', strtotime('+1 hour 2021-01-01 '.$break_time['start']))) - strtotime('2021-01-01 '.$break_time['start']))/60;
				
				$this_break_time = $break_time['start'];
				$break_times_in_mins = (strtotime($break_time['end']) - strtotime($this_break_time)) / 60;
				
				if($break_times_in_mins <= $max_mins) {
					$inactive_mins[date('H:00', strtotime($break_time['start']))] += $break_times_in_mins;
				}
				else {
					$inactive_mins[date('H:00', strtotime($break_time['start']))] += $max_mins;
					$break_times_in_mins -= $max_mins;
					
					$i = 1;
					while($break_times_in_mins > 0) {
						if($break_times_in_mins < 60) {
							$inactive_mins[date('H:00', strtotime('+'.$i.' hour '.$break_time['start']))] += $break_times_in_mins;
						}
						else {
							$inactive_mins[date('H:00', strtotime('+'.$i.' hour '.$break_time['start']))] += 60;
						}
						$break_times_in_mins -= 60;
					}
				}
			}
		}
		
		$takt_value = $time_template;

		foreach($takt_value as $the_hour => $value) {
			$active_mins = 60;
			if(!empty($inactive_mins[$the_hour])) {
				$active_mins = 60 - $inactive_mins[$the_hour];
			}
			
			$takt_value[$the_hour] = round($active_mins * $takt_board_data['takt_time_in_min']);
		}
		
		$data['takt_value'] = $takt_value;
		$data['current_takt_value_per_minute'] = round($takt_board_data['takt_time_in_min'],2);
		
		$data['js_kaizen_manager_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_kaizen_manager_visualization', $data, true);
		$data['kaizen_manager_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_kaizen_manager_visualization', $data, true);
		
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		return $data;
	}
}