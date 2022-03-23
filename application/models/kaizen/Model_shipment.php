<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_shipment extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function sync_action_log_table() {
		$start_date = '2020-03-01';
		
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');

		// Search for the latest updated date in evolution_points_log table with reason type "work"
		$latest_date_data = $this->db
			->select('DATE(started_at) AS latest_date')
			->from('action_log')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->order_by('started_at', 'DESC')
			->limit(1)
			->get()->result_array();

		$latest_date = !empty($latest_date_data) && ($latest_date_data[0]['latest_date'] > $start_date) ? $latest_date_data[0]['latest_date'] : $start_date;
		
		$today = date('Y-m-d');
		
		for($date = $latest_date; $date <= $today; $date = date('Y-m-d', strtotime('+1 day '.$date))) {
			$this->update_action_log_data($date);
		}
	}
	
	public function update_action_log_data($date) {
		if(empty($date)) return;
		
		ini_set('max_execution_time', 300);
		
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$date_in_utc = gmdate('Y-m-d H:i:s', strtotime($date));
		
		$now = date('Y-m-d H:i:s');
		$user_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
		
		$assignment_types = $this->model_db_crud->get_several_data('assignment_type', array('label_printer_prefix <>' => null));
		
		$assignment_type_of_printer = array_combine(
			array_column($assignment_types, 'label_printer_prefix'),
			array_column($assignment_types, 'id')
		);
		
		$facility_data = $this->model_db_crud->get_several_data('facility');
		$stock_id_cost_factor_data = array();
		foreach($facility_data as $current_data) {
			$stock_id_cost_factor_data[$current_data['stock_id']] = array(
				'operational_cost_per_package' => $current_data['operational_cost_per_package'],
				'fte_cost_per_hour' => $current_data['fte_cost_per_hour']
			);
		}
		
		// Get existing action log data
		$existing_action_log_data = $this->db
			->select('id, log_id')
			->from('action_log')
			->where('started_at_utc >=', $date_in_utc)
			->where('started_at_utc <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->get()->result_array();
		
		$existing_action_log_id = !empty($existing_action_log_data) ? 
			array_combine(
				array_column($existing_action_log_data, 'log_id'),
				array_column($existing_action_log_data, 'id') ) :
			array();
		
		$user_summary_data = array(
			1 => array(), // Springdale
			2 => array(), // Island River
			3 => array(),
			4 => array()
		);
		
		$user_data_template = array();
		foreach( array('pick', 'pack', 'load') as $current_action ) {
			$user_data_template[$current_action] = array(
				'total_time' => 0,
				'total_qty' => 0,
				'alt_total_qty' => 0,
				'total_value_added_time' => 0,
				'alt_total_value_added_time' => 0,
				'value_added_time_per_shipment' => 0,
				'non_value_added_time_per_shipment' => 0,
				'shipment_list' => array()
			);
		}
		
		// Get staff time log
		$staff_time_log_data = $redstag_db
			->select("time_log.stock_id, time_log.user_id, REPLACE(time_log.status, 'ing', '') AS action, SUM(duration) / 3600 AS total_time")
			->from('time_log')
			->where('time_log.started_at >=', $date_in_utc)
			->where('time_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->where_in('time_log.status', array('picking', 'packing', 'loading'))
			->group_by('time_log.stock_id, time_log.user_id, time_log.status')
			->get()->result_array();

		foreach($staff_time_log_data as $current_data) {
			if(!isset($user_summary_data[$current_data['stock_id']][$current_data['user_id']])) {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']] = $user_data_template;
			}
			
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] = $current_data['total_time'];
		}
		
		unset($staff_time_log_data);
		
		// Get user value added time
		$value_added_time_data = $redstag_db
			->select('action_log.stock_id, action_log.user_id, action_log.action, SUM(duration) / 3600 AS total_value_added_time, COUNT(action_log.log_id) AS total_qty')
			->from('action_log')
			->group_start()
				->group_start()
					->where('action_log.action', 'pack')
					->where('action_log.entity_type', 'shipment')
				->group_end()
				->or_group_start()
					->where('action_log.action', 'load')
					->where('action_log.entity_type', 'package')
				->group_end()
			->group_end()
			->where('action_log.started_at >=', $date_in_utc)
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->group_start()
				->where('action_log.action', 'pick')
				->or_where('action_log.duration <', 10000)
			->group_end()
			->group_by('action_log.stock_id, action_log.user_id, action_log.action')
			->get()->result_array();

		foreach($value_added_time_data as $current_data) {
			if(!isset($user_summary_data[$current_data['stock_id']][$current_data['user_id']])) {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']] = $user_data_template;
			}
			
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time'] = $current_data['total_value_added_time'];
			
			if($current_data['total_value_added_time'] > $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time']) {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] = $current_data['total_value_added_time'];
			}
			
			if($current_data['action'] <> 'pick') {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_qty'] = $current_data['total_qty'];
				
				if(!empty($current_data['total_qty'])) {
					$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['value_added_time_per_shipment'] = $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time'] / $current_data['total_qty'];
					
					$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['non_value_added_time_per_shipment'] = ($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] - $current_data['total_value_added_time']) / $current_data['total_qty'];
				}
			}
			
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['non_value_added_time_per_shipment'] = ($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] - $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time']) / $current_data['total_qty'];
		}
		
		unset($value_added_time_data);
		
		// Get picking batch duration data
		$picking_batch_duration_data = $redstag_db
			->select('stock_id, user_id, SUM(duration) AS total_duration')
			->from('action_log')
			->where('action', 'pick')
			->where('entity_type', 'batch')
			->where('action_log.started_at >=', $date_in_utc)
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->group_by('stock_id, user_id')
			->get()->result_array();
		
		foreach($picking_batch_duration_data as $current_data) {
			if(!isset($user_summary_data[$current_data['stock_id']][$current_data['user_id']])) {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']] = $user_data_template;
			}
			
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']]['pick']['alt_total_value_added_time'] = $current_data['total_duration'] / 3600;
		}
		
		unset($picking_batch_duration_data);
		
		// Get picking batch qty data
		$picking_batch_shipment_data = $redstag_db
			->distinct('stock_id, user_id, shipment_id')
			->from('action_log')
			->join('batch_item', 'batch_item.batch_id = action_log.entity_id')
			->where('action', 'pick')
			->where('entity_type', 'batch')
			->where('action_log.started_at >=', $date_in_utc)
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->get()->result_array();
		
		foreach($picking_batch_shipment_data as $current_data) {
			if(!isset($user_summary_data[$current_data['stock_id']][$current_data['user_id']])) {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']] = $user_data_template;
			}
			
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']]['pick']['alt_total_qty']++;
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']]['pick']['shipment_list'][] = $current_data['shipment_id'];
		}
		
		unset($picking_batch_shipment_data);
		
		// Get picking value added time data
		$picking_value_added_time_data = $redstag_db
			->select("stock_id, user_id, IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern')) AS started_at, MAX(duration) AS total_duration, COUNT(*) AS total_qty, MAX(duration) / COUNT(*) / 3600 AS value_added_time_per_shipment", false)
			->from('action_log')
			->where('action', 'pick')
			->where('entity_type', 'shipment')
			->where('action_log.started_at >=', $date_in_utc)
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->group_by('stock_id, user_id, started_at')
			->get()->result_array();
		
		$picking_value_added_time_by_started_time = array();
		foreach($picking_value_added_time_data as $current_data) {
			$picking_value_added_time_by_started_time[$current_data['stock_id'].'/'.$current_data['user_id'].'/'.$current_data['started_at']] = $current_data['value_added_time_per_shipment'];
			
			if(!isset($user_summary_data[$current_data['stock_id']][$current_data['user_id']])) {
				$user_summary_data[$current_data['stock_id']][$current_data['user_id']] = $user_data_template;
			}
			
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']]['pick']['total_qty'] += $current_data['total_qty'];
			$user_summary_data[$current_data['stock_id']][$current_data['user_id']]['pick']['total_value_added_time'] += $current_data['total_duration'] / 3600;
		}
		
		unset($picking_value_added_time_data);
				
		$new_action_log_data = array();
		$updated_action_log_data = array();
		
		// Get data for picking & packing action
		$pick_and_pack_action_log_data = $redstag_db
			->select(
				"action_log.log_id, 
				action_log.stock_id,
				action_log.action,
				sales_flat_shipment.entity_id AS shipment_entity_id,
				sales_flat_shipment.increment_id AS shipment_increment_id,
				sales_flat_shipment.order_id,
				sales_flat_order.store_id,
				sales_flat_shipment.created_at AS shipment_created_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment.created_at,'UTC','US/Eastern')) AS shipment_created_at,
				sales_flat_shipment.updated_at AS shipment_updated_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment.updated_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment.updated_at,'UTC','US/Eastern')) AS shipment_updated_at,
				action_log.user_id,
				action_log.started_at AS started_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(action_log.started_at,'UTC','US/Mountain'),CONVERT_TZ(action_log.started_at,'UTC','US/Eastern')) AS started_at,
				action_log.finished_at AS finished_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(action_log.finished_at,'UTC','US/Mountain'),CONVERT_TZ(action_log.finished_at,'UTC','US/Eastern')) AS finished_at,
				action_log.duration,
				sales_flat_shipment.label_print_target", false)
			->from('action_log')
			->join('sales_flat_shipment', 'sales_flat_shipment.increment_id = action_log.entity_description')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->group_start()
				->where('action_log.action', 'pick')
				->or_where('action_log.action', 'pack')
			->group_end()
			->where('action_log.entity_type', 'shipment')
			->where('action_log.started_at >=', $date_in_utc)
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->order_by('action_log.action, action_log.user_id, action_log.started_at, action_log.finished_at')
			->get()->result_array();
		
		foreach($pick_and_pack_action_log_data as $key => $current_data) {
			$data_valid = true;
			
			if(empty($current_data['finished_at'])) {
				$data_valid = false;
			}
			
			$label_printer_prefix = substr($current_data['label_print_target'], 2, 3);
			
			$current_data['assignment_type'] = isset($assignment_type_of_printer[$label_printer_prefix]) ? $assignment_type_of_printer[$label_printer_prefix] : 0;
			
			// For TYS2
			$label_printer_first_6_chars = substr($current_data['label_print_target'], 0, 6);
			switch($label_printer_first_6_chars) {
				case 'TYS2-B':
				case 'TYS2BL':
					$current_data['assignment_type'] = 4; // BLK
					break;
				case 'TYS2-O':
				case 'TYS2OB':
					$current_data['assignment_type'] = 3; // OBX
					break;
			}
			
			if( strpos(strtoupper($current_data['label_print_target']), 'MOB') !== false ) {
				$current_data['assignment_type'] = isset($assignment_type_of_printer['MOB']) ? $assignment_type_of_printer['MOB'] : 0;
			}
			
			$value_added_time = 0;
			$total_time = 0;
			
			// Alt: using Excel calculation (uniform VA/NVA time)
			$alt_value_added_time = 0;
			$alt_total_time = 0;
			
			if($current_data['action'] == 'pick') {
				if(isset($picking_value_added_time_by_started_time[$current_data['stock_id'].'/'.$current_data['user_id'].'/'.$current_data['started_at']])) {
					$value_added_time = $picking_value_added_time_by_started_time[$current_data['stock_id'].'/'.$current_data['user_id'].'/'.$current_data['started_at']];
				}
				
				$total_time = !empty($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time']) ? ($value_added_time / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time']) * $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] : 0;
				
				$alt_value_added_time = null;
				$alt_total_time = null;
				if(!empty($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['alt_total_qty'])
					&& in_array($current_data['shipment_entity_id'], $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['shipment_list'])) {
						
					$alt_value_added_time = $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['alt_total_value_added_time'] / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['alt_total_qty'];
					
					$alt_total_time = $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['alt_total_qty'];
				}
			}
			else if($current_data['action'] == 'pack') {
				// Check for data error: if the finished time > start time of next row, skip the data.
				if(isset($pick_and_pack_action_log_data[$key+1]) 
					&& ($pick_and_pack_action_log_data[$key+1]['action'] == 'pack')
					&& ($pick_and_pack_action_log_data[$key+1]['stock_id'] == $current_data['stock_id'])
					&& ($pick_and_pack_action_log_data[$key+1]['user_id'] == $current_data['user_id'])
					&& !empty($pick_and_pack_action_log_data[$key+1]['started_at'])
					&& !empty($current_data['finished_at'])
					&& (strtotime($pick_and_pack_action_log_data[$key+1]['started_at']) < strtotime($current_data['finished_at']))) {
						$data_valid = false;
				}
				
				$value_added_time = $alt_value_added_time = $current_data['duration'] / 3600;
				$total_time = 0;
				$alt_total_time = 0;
				if(!empty($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time']) && $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time'] > 0) {
					$total_time = ($value_added_time / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time']) * $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'];
				}
				
				if(!empty($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['non_value_added_time_per_shipment'])) {
					$alt_total_time = $alt_value_added_time + $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['non_value_added_time_per_shipment'];
				}
				else {
					$alt_total_time = $alt_value_added_time;
				}
			}
			
			if($total_time < $value_added_time) {
				$total_time = $value_added_time;
			}
			
			if($alt_total_time < $alt_value_added_time) {
				$alt_total_time = $alt_value_added_time;
			}
			
			$cost = 0;
			$alt_cost = 0;
			if(
				!empty($stock_id_cost_factor_data[$current_data['stock_id']]['operational_cost_per_package'])
				&& !empty($stock_id_cost_factor_data[$current_data['stock_id']]['fte_cost_per_hour'])
				&& !empty($total_time)
			) {
				$cost = $total_time * $stock_id_cost_factor_data[$current_data['stock_id']]['operational_cost_per_package'] * $stock_id_cost_factor_data[$current_data['stock_id']]['fte_cost_per_hour'];
			}
			
			$alt_cost = isset($alt_total_time) ? $alt_total_time * $stock_id_cost_factor_data[$current_data['stock_id']]['operational_cost_per_package'] * $stock_id_cost_factor_data[$current_data['stock_id']]['fte_cost_per_hour'] : null;
			
			if(!isset($existing_action_log_id[$current_data['log_id']])) {
				$new_action_log_data[] = array(
					'log_id' => $current_data['log_id'],
					'stock_id' => $current_data['stock_id'],
					'action' => $current_data['action'],
					'shipment_entity_id' => $current_data['shipment_entity_id'],
					'shipment_increment_id' => $current_data['shipment_increment_id'],
					'order_id' => $current_data['order_id'],
					'store_id' => $current_data['store_id'],
					'shipment_created_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_created_at' => $current_data['shipment_created_at'],
					'shipment_updated_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_updated_at' => $current_data['shipment_created_at'],
					'package_id' => null,
					'user_id' => $current_data['user_id'],
					'started_at_utc' => $current_data['started_at_utc'],
					'started_at' => $current_data['started_at'],
					'finished_at_utc' => $current_data['finished_at_utc'],
					'finished_at' => $current_data['finished_at'],
					'duration' => $current_data['duration'],
					'value_added_time' => $value_added_time,
					'total_time' => $total_time,
					'cost' => $cost,
					'alt_value_added_time' => $alt_value_added_time,
					'alt_total_time' => $alt_total_time,
					'alt_cost' => $alt_cost,
					'label_print_target' => $current_data['label_print_target'],
					'assignment_type' => $current_data['assignment_type'],
					'data_valid' => $data_valid,
					'data_status' => 'active',
					'data_group' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'),
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
			else {
				$updated_action_log_data[] = array(
					'id' => $existing_action_log_id[$current_data['log_id']],
					'log_id' => $current_data['log_id'],
					'stock_id' => $current_data['stock_id'],
					'action' => $current_data['action'],
					'shipment_entity_id' => $current_data['shipment_entity_id'],
					'shipment_increment_id' => $current_data['shipment_increment_id'],
					'order_id' => $current_data['order_id'],
					'store_id' => $current_data['store_id'],
					'shipment_created_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_created_at' => $current_data['shipment_created_at'],
					'shipment_updated_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_updated_at' => $current_data['shipment_created_at'],
					'package_id' => null,
					'user_id' => $current_data['user_id'],
					'started_at_utc' => $current_data['started_at_utc'],
					'started_at' => $current_data['started_at'],
					'finished_at_utc' => $current_data['finished_at_utc'],
					'finished_at' => $current_data['finished_at'],
					'duration' => $current_data['duration'],
					'value_added_time' => $value_added_time,
					'total_time' => $total_time,
					'cost' => $cost,
					'alt_value_added_time' => $alt_value_added_time,
					'alt_total_time' => $alt_total_time,
					'alt_cost' => $alt_cost,
					'label_print_target' => $current_data['label_print_target'],
					'assignment_type' => $current_data['assignment_type'],
					'data_valid' => $data_valid,
					'data_status' => 'active',
					'data_group' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'),
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
		}
		
		unset($pick_and_pack_action_log_data);
		unset($picking_value_added_time_by_started_time);
		
		$load_action_log_data = $redstag_db
			->select(
				"action_log.log_id, 
				action_log.stock_id,
				action_log.action,
				sales_flat_shipment_package.package_id,
				sales_flat_shipment.entity_id AS shipment_entity_id,
				sales_flat_shipment.increment_id AS shipment_increment_id,
				sales_flat_shipment.order_id,
				sales_flat_order.store_id,
				sales_flat_shipment.created_at AS shipment_created_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment.created_at,'UTC','US/Eastern')) AS shipment_created_at,
				sales_flat_shipment.updated_at AS shipment_updated_at_utc,	IF(action_log.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment.updated_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment.updated_at,'UTC','US/Eastern')) AS shipment_updated_at,
				action_log.user_id,
				action_log.started_at AS started_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(action_log.started_at,'UTC','US/Mountain'),CONVERT_TZ(action_log.started_at,'UTC','US/Eastern')) AS started_at,
				action_log.finished_at AS finished_at_utc,
				IF(action_log.stock_id IN (3,6),CONVERT_TZ(action_log.finished_at,'UTC','US/Mountain'),CONVERT_TZ(action_log.finished_at,'UTC','US/Eastern')) AS finished_at,
				action_log.duration,
				sales_flat_shipment.label_print_target", false)
			->from('action_log')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = action_log.entity_id')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('action_log.action', 'load')
			->where('action_log.entity_type', 'package')
			->where('action_log.started_at >=', $date_in_utc)
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->order_by('action_log.user_id, action_log.stock_id, action_log.started_at, action_log.finished_at')
			->get()->result_array();
				
		foreach($load_action_log_data as $key => $current_data) {
			$data_valid = true;
			
			if(empty($current_data['finished_at'])) {
				$data_valid = false;
			}
			
			$label_printer_prefix = substr($current_data['label_print_target'], 2, 3);
			
			$current_data['assignment_type'] = isset($assignment_type_of_printer[$label_printer_prefix]) ? $assignment_type_of_printer[$label_printer_prefix] : 0;
			
			// For TYS2
			$label_printer_first_6_chars = substr($current_data['label_print_target'], 0, 6);
			switch($label_printer_first_6_chars) {
				case 'TYS2-B':
				case 'TYS2BL':
					$current_data['assignment_type'] = 4; // BLK
					break;
				case 'TYS2-O':
				case 'TYS2OB':
					$current_data['assignment_type'] = 3; // OBX
					break;
			}
			
			if( strpos(strtoupper($current_data['label_print_target']), 'MOB') !== false ) {
				$current_data['assignment_type'] = isset($assignment_type_of_printer['MOB']) ? $assignment_type_of_printer['MOB'] : 0;
			}
			
			// Check for data error: if the finished time > start time of next row, skip the data.
			if(isset($load_action_log_data[$key+1]) 
				&& ($load_action_log_data[$key+1]['stock_id'] == $current_data['stock_id'])
				&& ($load_action_log_data[$key+1]['user_id'] == $current_data['user_id'])
				&& !empty($load_action_log_data[$key+1]['started_at'])
				&& !empty($current_data['finished_at'])
				&& (strtotime($load_action_log_data[$key+1]['started_at']) < strtotime($current_data['finished_at']))) {
					$data_valid = false;
			}
			
			$value_added_time = $current_data['duration'] / 3600;
			
			$alt_value_added_time = !empty($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_qty']) ? $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time'] / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_qty'] : 0;
			
			$total_time = $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time'] > 0 ? $value_added_time / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_value_added_time'] * $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] : 0;
			
			$alt_total_time = !empty($user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_qty']) ? $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_time'] / $user_summary_data[$current_data['stock_id']][$current_data['user_id']][$current_data['action']]['total_qty'] : 0;
			
			if($total_time < $value_added_time) {
				$total_time = $value_added_time;
			}
			
			if($alt_total_time < $alt_value_added_time) {
				$alt_total_time = $alt_value_added_time;
			}
			
			$cost = 0;
			$alt_cost = 0;
			if(
				!empty($stock_id_cost_factor_data[$current_data['stock_id']]['operational_cost_per_package'])
				&& !empty($stock_id_cost_factor_data[$current_data['stock_id']]['fte_cost_per_hour'])
				&& !empty($total_time)
			) {
				$cost = $total_time * $stock_id_cost_factor_data[$current_data['stock_id']]['operational_cost_per_package'] * $stock_id_cost_factor_data[$current_data['stock_id']]['fte_cost_per_hour'];
			}
			
			$alt_cost = $alt_total_time * $stock_id_cost_factor_data[$current_data['stock_id']]['operational_cost_per_package'] * $stock_id_cost_factor_data[$current_data['stock_id']]['fte_cost_per_hour'];

			if(!isset($existing_action_log_id[$current_data['log_id']])) {
				$new_action_log_data[] = array(
					'log_id' => $current_data['log_id'],
					'stock_id' => $current_data['stock_id'],
					'action' => $current_data['action'],
					'shipment_entity_id' => $current_data['shipment_entity_id'],
					'shipment_increment_id' => $current_data['shipment_increment_id'],
					'order_id' => $current_data['order_id'],
					'store_id' => $current_data['store_id'],
					'shipment_created_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_created_at' => $current_data['shipment_created_at'],
					'shipment_updated_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_updated_at' => $current_data['shipment_created_at'],
					'package_id' => $current_data['package_id'],
					'user_id' => $current_data['user_id'],
					'started_at_utc' => $current_data['started_at_utc'],
					'started_at' => $current_data['started_at'],
					'finished_at_utc' => $current_data['finished_at_utc'],
					'finished_at' => $current_data['finished_at'],
					'duration' => $current_data['duration'],
					'value_added_time' => $value_added_time,
					'total_time' => $total_time,
					'cost' => $cost,
					'alt_value_added_time' => $alt_value_added_time,
					'alt_total_time' => $alt_total_time,
					'alt_cost' => $alt_cost,
					'label_print_target' => $current_data['label_print_target'],
					'assignment_type' => $current_data['assignment_type'],
					'data_valid' => $data_valid,
					'data_status' => 'active',
					'data_group' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'),
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
			else {
				$updated_action_log_data[] = array(
					'id' => $existing_action_log_id[$current_data['log_id']],
					'log_id' => $current_data['log_id'],
					'stock_id' => $current_data['stock_id'],
					'action' => $current_data['action'],
					'shipment_entity_id' => $current_data['shipment_entity_id'],
					'shipment_increment_id' => $current_data['shipment_increment_id'],
					'order_id' => $current_data['order_id'],
					'store_id' => $current_data['store_id'],
					'shipment_created_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_created_at' => $current_data['shipment_created_at'],
					'shipment_updated_at_utc' => $current_data['shipment_created_at_utc'],
					'shipment_updated_at' => $current_data['shipment_created_at'],
					'package_id' => $current_data['package_id'],
					'user_id' => $current_data['user_id'],
					'started_at_utc' => $current_data['started_at_utc'],
					'started_at' => $current_data['started_at'],
					'finished_at_utc' => $current_data['finished_at_utc'],
					'finished_at' => $current_data['finished_at'],
					'duration' => $current_data['duration'],
					'value_added_time' => $value_added_time,
					'total_time' => $total_time,
					'cost' => $cost,
					'alt_value_added_time' => $alt_value_added_time,
					'alt_total_time' => $alt_total_time,
					'alt_cost' => $alt_cost,
					'label_print_target' => $current_data['label_print_target'],
					'assignment_type' => $current_data['assignment_type'],
					'data_valid' => $data_valid,
					'data_status' => 'active',
					'data_group' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'),
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
		}
		
		unset($user_summary_data);
		unset($load_action_log_data);
		unset($existing_action_log_id);
		$new_action_log_data_count = count($new_action_log_data);
		$updated_action_log_data_count = count($updated_action_log_data);

		$this->db->trans_start();
		
		$this->db
			->where('started_at_utc >=', $date_in_utc)
			->where('started_at_utc <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->delete('action_log');
		
		if(!empty($new_action_log_data)) {
			$this->db->insert_batch('action_log', $new_action_log_data);
		}

		if(!empty($updated_action_log_data)) {
			$this->db->insert_batch('action_log', $updated_action_log_data);
		}

		unset($updated_action_log_data);
		
		$this->db->trans_complete();
	}
	
	public function update_shipment_report_table() {
		$message = '';
		$prod_db = $this->load->database('prod', TRUE);
			
		// Search for the latest updated date in shipment_report_table
		$latest_date_data = $prod_db
			->select_max('DATE(date)', 'latest_date')
			->from('shipment_report')
			->get()->result_array();
		
		$latest_date = $latest_date_data[0]['latest_date'];
		
		while(strtotime($latest_date) <= strtotime(date('Y-m-d'))) {
			$num_rows = $this->update_shipment_report_table_on_date($latest_date);
			$latest_date = date('Y-m-d', strtotime('+1 day '.$latest_date));
			$message .= $latest_date . ': Shipment report updated ('.$num_rows.' rows).<br>';
		}
		
		return $message;
	}
	
	public function update_shipment_report_table_on_date($date) {
		$prod_db = $this->load->database('prod', TRUE);
		
		$facility_data = $prod_db->select('id, stock_id')->from('facilities')->where('data_status',DATA_ACTIVE)->get()->result_array();
		$facility_id_by_stock_id = array();
		foreach($facility_data as $current_data) {
			$facility_id_by_stock_id[$current_data['stock_id']] = $current_data['id'];
		}
		
		$shipment_report_data = $prod_db
			->select('DATE(started_at) AS the_date, store_id, stock_id, action, assignment_type, COUNT(DISTINCT shipment_increment_id) AS qty, SUM(total_time) AS labor_hours_worked, SUM(cost) AS total_cost', false)
			->from('action_log')
			->where('action_log.data_status', DATA_ACTIVE)
			->where('action_log.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('action_log.data_valid', 1)
			->where('action_log.started_at >=', $date)
			->where('action_log.started_at <', date('Y-m-d', strtotime('+1 day '.$date)))
			->group_by('DATE(started_at), stock_id, action, assignment_type, store_id')
			->get()->result_array();
		
		$shipment_report_data_to_insert = array();
		
		foreach($shipment_report_data as $current_data) {
			$facility_id = isset($facility_id_by_stock_id[$current_data['stock_id']]) ? $facility_id_by_stock_id[$current_data['stock_id']] : $current_data['stock_id'];
			$identifier = $current_data['the_date'].$facility_id.$current_data['assignment_type'].'-'.$current_data['store_id'];
			
			if(!isset($shipment_report_data_to_insert[$identifier])) {
				$shipment_report_data_to_insert[$identifier] = array(
					'date' => $current_data['the_date'],
					'facility' => $facility_id,
					'assignment_type' => $current_data['assignment_type'],
					'store_id' => $current_data['store_id'],
					'shipment_qty' => 0,
					'pack_qty' => 0,
					'pick_qty' => 0,
					'load_qty' => 0,
					'labor_hours_worked' => 0,
					'cost' => 0
				);
			}
			
			switch($current_data['action']) {
				case 'pack':
					$shipment_report_data_to_insert[$identifier]['pack_qty'] = $current_data['qty'];
					$shipment_report_data_to_insert[$identifier]['shipment_qty'] = $current_data['qty'];
					break;
				case 'pick':
					$shipment_report_data_to_insert[$identifier]['pick_qty'] = $current_data['qty'];
					break;
				case 'load':
					$shipment_report_data_to_insert[$identifier]['load_qty'] = $current_data['qty'];	
					break;
			}
			
			$shipment_report_data_to_insert[$identifier]['labor_hours_worked'] += $current_data['labor_hours_worked'];
			$shipment_report_data_to_insert[$identifier]['cost'] += $current_data['total_cost'];
		}
		
		$shipment_report_data_to_insert = array_values($shipment_report_data_to_insert);
		$num_rows = 0;
		
		if(!empty($shipment_report_data_to_insert)) {
			$prod_db->trans_start();
			
			$prod_db->where('date', $date)->delete('shipment_report');
			$num_rows = $prod_db->insert_batch('shipment_report', $shipment_report_data_to_insert);
			
			if($num_rows === false) {
				$num_rows = -1;
			}
			
			$prod_db->trans_complete();
		}
		
		return $num_rows;
	}
}