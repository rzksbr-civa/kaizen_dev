<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_idle_status extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_idle_status_board_data($data) {
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
			->select('user_id, status, TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",started_at)) AS working_time')
			->from('time_log')
			->where('finished_at IS NULL', null, false)
			->where('started_at >=', date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d'))));
		
		if(!empty($data['facility'])) {
			$redstag_db->where('stock_id', $stock_id);
		}
		
		if(!empty($data['status'])) {
			$redstag_db->where_in('status', $data['status']);
		}
		else {
			$redstag_db->where_in('status', array('picking','packing','loading'));
		}
		
		$users_in_active_status_data_raw = $redstag_db->get()->result_array();
		
		$user_ids_in_active_status_data = array_column($users_in_active_status_data_raw, 'user_id');
		
		$users_in_active_status_data = array();
		foreach($users_in_active_status_data_raw as $current_data) {
			$users_in_active_status_data[$current_data['user_id']] = array(
				'status' => $current_data['status'],
				'working_time' => $current_data['working_time']
			);
		}
		
		$users_in_departments_data = array();
		if(!empty($user_ids_in_active_status_data)) {
			$this->db
				->select('employees.id AS employee_id, department_name')
				->from('employees')
				->join('departments', 'employees.department = departments.id', 'left')
				->where('employees.data_status', DATA_ACTIVE)
				->where('employees.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where_in('employees.id', $user_ids_in_active_status_data);
			if(!empty($data['department'])) {
				$this->db->where_in('department', $data['department']);
			}
			$users_in_departments_data = $this->db->get()->result_array();
		}
		
		$user_ids_in_departments = array_column($users_in_departments_data, 'employee_id');
		
		$users_in_departments = array_combine(
			$user_ids_in_departments,
			array_column($users_in_departments_data, 'department_name')
		);
		
		foreach($users_in_active_status_data as $user_id => $value) {
			// Department filter
			if(!in_array($user_id, $user_ids_in_departments)) {
				unset($users_in_active_status_data[$user_id]);
			}
		}

		$redstag_db
			->select('user_id')
			->from('action_log')
			->where_in('action', array('pick','pack','load'))
			->where('finished_at IS NULL', null, false)
			->where('started_at >=', date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d'))))
			->group_by('user_id');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('stock_id', $stock_id);
		}
		
		$active_users_in_action = $redstag_db->get()->result_array();
		
		$idle_time_data = array();
		if(!empty($users_in_active_status_data)) {
			$redstag_db
				->select('admin_user.user_id, admin_user.name, MAX(finished_at) AS last_finished_at, TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",MAX(finished_at))) AS idle_time')
				->from('action_log')
				->join('admin_user', 'admin_user.user_id = action_log.user_id')
				->where('started_at >=', date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d'))))
				->where_in('action', array('pick','pack','load'))
				->where_in('admin_user.user_id', $user_ids_in_active_status_data)
				->where_in('admin_user.user_id', $user_ids_in_departments)
				->group_by('admin_user.user_id, admin_user.name')
				->order_by('idle_time', 'desc');
			
			if(!empty($active_users_in_action)) {
				$redstag_db->where_not_in('admin_user.user_id', array_column($active_users_in_action,'user_id'));
			}
			
			if(!empty($data['facility'])) {
				$redstag_db->where('action_log.stock_id', $stock_id);
			}
			
			$idle_time_data = $redstag_db->get()->result_array();
		}
		
		foreach($idle_time_data as $key => $user_idle_time_data) {
			$idle_time_data[$key]['status'] = isset($users_in_active_status_data[$user_idle_time_data['user_id']]) ? $users_in_active_status_data[$user_idle_time_data['user_id']]['status'] : null;
			$idle_time_data[$key]['department'] = isset($users_in_departments[$user_idle_time_data['user_id']]) ? $users_in_departments[$user_idle_time_data['user_id']] : null;
			
			if($user_idle_time_data['idle_time'] > $users_in_active_status_data[$user_idle_time_data['user_id']]['working_time']) {
				$idle_time_data[$key]['idle_time'] = $users_in_active_status_data[$user_idle_time_data['user_id']]['working_time'];
			}
			
			if($idle_time_data[$key]['idle_time'] >= 4 * 60) {
				$idle_time_data[$key]['color'] = 'red';
			}
			else if($idle_time_data[$key]['idle_time'] >= 2 * 60) {
				$idle_time_data[$key]['color'] = 'yellow';
			}
			else {
				unset($idle_time_data[$key]);
				// $idle_time_data[$key]['color'] = 'green';
			}
		}
		
		// Idle in status: Team meeting, replenishment, management request, cleaning, training
		$secondary_status_name_list = array(
			'team_meeting' => 'Team Meeting',
			'replenishment' => 'Repenishment',
			'management_request' => 'Management Request',
			'cleaning' => 'Cleaning',
			'training' => 'Training');
		$secondary_status_list = array_keys($secondary_status_name_list);
		
		$filtered_secondary_status_list = array();
		if(empty($data['status'])) {
			$filtered_secondary_status_list = $secondary_status_list;
		}
		else {
			foreach($data['status'] as $current_status) {
				if(in_array($current_status, $secondary_status_list)) {
					$filtered_secondary_status_list[] = $current_status;
				}
			}
		}
		
		if(!empty($filtered_secondary_status_list)) {
			$redstag_db
				->select('admin_user.user_id, admin_user.name, status, TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",started_at)) AS idle_time')
				->from('time_log')
				->join('admin_user', 'admin_user.user_id = time_log.user_id')
				->where('finished_at IS NULL', null, false)
				->where('started_at >=', date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d'))))
				->where_in('status', $filtered_secondary_status_list);
			
			if(!empty($data['facility'])) {
				$redstag_db->where('stock_id', $stock_id);
			}
			
			$users_in_active_secondary_status_data_raw = $redstag_db->get()->result_array();
			
			if(!empty($users_in_active_secondary_status_data_raw)) {
				foreach($users_in_active_secondary_status_data_raw as $current_data) {
					if($current_data['idle_time'] > 2 * 60) {
						$idle_time_data[] = array(
							'user_id' => $current_data['user_id'],
							'name' => $current_data['name'],
							'idle_time' => $current_data['idle_time'],
							'last_finished_at' => null,
							'status' => $secondary_status_name_list[$current_data['status']],
							'department' => isset($users_in_departments[$current_data['user_id']]) ? $users_in_departments[$current_data['user_id']] : null,
							'color' => $current_data['idle_time'] <= 3 * 60 ? 'yellow' : 'red'
						);
					}
				}
			}
		}
		
		$data['idle_time_data'] = $idle_time_data;
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['idle_status_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_idle_status_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_idle_break_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);

		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		$this->db
			->select('employees.id AS employee_id, department_name')
			->from('employees')
			->join('departments', 'employees.department = departments.id', 'left')
			->where('employees.data_status', DATA_ACTIVE)
			->where('employees.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'));
		if(!empty($data['department'])) {
			$this->db->where_in('department', $data['department']);
		}
		$users_in_departments_data = $this->db->get()->result_array();
		
		$user_ids_in_departments = array_column($users_in_departments_data, 'employee_id');
		
		$users_in_departments = array_combine(
			$user_ids_in_departments,
			array_column($users_in_departments_data, 'department_name')
		);

		$idle_time_data = array();
		$redstag_db
			->select('admin_user.user_id, admin_user.name, time_log.status, TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",time_log.started_at)) AS idle_time')
			->from('time_log')
			->join('admin_user', 'admin_user.user_id = time_log.user_id')
			->where('finished_at IS NULL', null, false)
			->where('started_at >=', date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d'))));
		
		$this->db->group_start();
		$user_ids_in_departments_chunk = array_chunk($user_ids_in_departments,25);
		foreach($user_ids_in_departments_chunk as $current_data) {
			$this->db->or_where_in('admin_user.user_id', $current_data);
		}
		$this->db->group_end();
		
		if(!empty($data['facility'])) {
			$redstag_db->where('stock_id', $stock_id);
		}
		
		if(!empty($data['status'])) {
			$redstag_db->where_in('status', $data['status']);
		}
		else {
			$redstag_db->where_in('status', array('paid_break', 'unpaid_break'));
		}
		
		$idle_time_data = $redstag_db->get()->result_array();
		
		foreach($idle_time_data as $key => $user_idle_time_data) {
			$idle_time_data[$key]['department'] = isset($users_in_departments[$user_idle_time_data['user_id']]) ? $users_in_departments[$user_idle_time_data['user_id']] : null;
			switch($user_idle_time_data['status']) {
				case 'paid_break':
					$idle_time_data[$key]['status'] = 'Paid Break';
					if($user_idle_time_data['idle_time'] > 17 * 60) {
						$idle_time_data[$key]['color'] = 'red';
					}
					else if($user_idle_time_data['idle_time'] > 15 * 60) {
						$idle_time_data[$key]['color'] = 'yellow';
					}
					else {
						//$idle_time_data[$key]['color'] = 'green';
						unset($idle_time_data[$key]);
					}
					break;
				case 'unpaid_break':
					$idle_time_data[$key]['status'] = 'Unpaid Break';
					if($user_idle_time_data['idle_time'] > 47 * 60) {
						$idle_time_data[$key]['color'] = 'red';
					}
					else if($user_idle_time_data['idle_time'] > 45 * 60) {
						$idle_time_data[$key]['color'] = 'yellow';
					}
					else {
						// $idle_time_data[$key]['color'] = 'green';
						unset($idle_time_data[$key]);
					}
					break;
				default:
					$idle_time_data[$key]['status'] = null;
			}			
		}
		
		$data['idle_time_data'] = $idle_time_data;
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['idle_break_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_idle_status_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_idle_picking_batch_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);

		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		// 1. Batches that have been created but have no start time.
		$redstag_db
			->select('
				batch.batch_id,
				IF(batch.stock_id=3,
					CONVERT_TZ(batch.created_at,"UTC","US/Mountain"),
					CONVERT_TZ(batch.created_at,"UTC","US/Eastern")) AS created_at_local,
				batch.created_at,
				batch.status,
				batch.progress,
				admin_user.name,
				TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",batch.created_at)) AS time_since_created')
			->from('batch')
			->join('admin_user','batch.user_id = admin_user.user_id')
			->where('batch.status', 'new')
			->where('batch.created_at >=', gmdate('Y-m-d H:i:s', strtotime('-1 day')))
			->where('batch.created_at <', gmdate('Y-m-d H:i:s', strtotime('-30 min')));
		
		if(!empty($data['facility'])) {
			$redstag_db->where('batch.stock_id', $stock_id);
		}
		
		$idle_new_batches = $redstag_db->get()->result_array();
		
		foreach($idle_new_batches as $key => $current_data) {
			$idle_new_batches[$key]['started_at'] = null;
			$idle_new_batches[$key]['started_at_local'] = null;
			$idle_new_batches[$key]['time_since_started'] = null;
		}
		
		$data['idle_batches_data'] = $idle_new_batches;
		
		// 2. Batches that have been started but have no end time.
		$redstag_db
			->select('
				entity_id AS batch_id,
				started_at,
				finished_at,
				IF(stock_id IN (3,6),
					CONVERT_TZ(started_at,"UTC","US/Mountain"),
					CONVERT_TZ(started_at,"UTC","US/Eastern")) AS started_at_local,
				IF(stock_id IN (3,6),
					CONVERT_TZ(finished_at,"UTC","US/Mountain"),
					CONVERT_TZ(finished_at,"UTC","US/Eastern")) AS finished_at_local,
				TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",action_log.started_at)) AS time_since_started')
			->from('action_log')
			->where('entity_type', 'batch')
			->where('action', 'pick')
			->where('action_log.started_at >=', gmdate('Y-m-d H:i:s', strtotime('-1 day')))
			->where('action_log.started_at <', gmdate('Y-m-d H:i:s', strtotime('-15 min')))
			->where('action_log.finished_at IS NULL', null, false);
		
		if(!empty($data['facility'])) {
			$redstag_db->where('action_log.stock_id', $stock_id);
		}
		
		$unfinished_idle_batches = $redstag_db->get()->result_array();
		$unfinished_idle_batches_ids = array();
		if(!empty($unfinished_idle_batches)) {
			$unfinished_idle_batches_ids = array_column($unfinished_idle_batches, 'batch_id');
			
			$unfinished_idle_batches_by_batch_id = array();
			foreach($unfinished_idle_batches as $current_data) {
				$unfinished_idle_batches_by_batch_id[$current_data['batch_id']] = $current_data;
			}
			
			$redstag_db
				->select('
					batch.batch_id,
					IF(batch.stock_id IN (3,6),
						CONVERT_TZ(batch.created_at,"UTC","US/Mountain"),
						CONVERT_TZ(batch.created_at,"UTC","US/Eastern")) AS created_at_local,
					batch.created_at,
					batch.status,
					batch.progress,
					admin_user.name,
					TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",batch.created_at)) AS time_since_created')
				->from('batch')
				->join('admin_user','batch.user_id = admin_user.user_id')
				->where('batch.status', 'picking')
				->where('batch.created_at >=', gmdate('Y-m-d H:i:s', strtotime('-1 day')))
				->where_in('batch_id', $unfinished_idle_batches_ids);
			
			if(!empty($data['facility'])) {
				$redstag_db->where('batch.stock_id', $stock_id);
			}
			
			$unfinished_idle_batches_data = $redstag_db->get()->result_array();
			
			foreach($unfinished_idle_batches_data as $key => $current_data) {
				$unfinished_idle_batches_data[$key]['started_at'] = $unfinished_idle_batches_by_batch_id[$current_data['batch_id']]['started_at'];
				$unfinished_idle_batches_data[$key]['started_at_local'] = $unfinished_idle_batches_by_batch_id[$current_data['batch_id']]['started_at_local'];
				$unfinished_idle_batches_data[$key]['time_since_started'] = $unfinished_idle_batches_by_batch_id[$current_data['batch_id']]['time_since_started'];
			}
			
			$data['idle_batches_data'] = array_merge(
				$data['idle_batches_data'],
				$unfinished_idle_batches_data
			);
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['idle_picking_batch_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_idle_picking_batch_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_idle_manifest_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);

		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		$threshold_time = date('Y-m-d H:i:s', strtotime('-'.$data['threshold_mins'].' min '.$current_utc_time));
		$threshold_date = date('Y-m-d', strtotime($threshold_time));
		
		$data['idle_manifest_board_data'] = $redstag_db
			->query(
				"SELECT sales_flat_shipment_package.package_id,
				   sales_flat_shipment_package.shipment_id,
				   sales_flat_shipment_package.length,
				   sales_flat_shipment_package.width,
				   sales_flat_shipment_package.height,
				   sales_flat_shipment_package.carrier_code,
				   sales_flat_shipment.increment_id AS shipment_number,
				   sales_flat_order.increment_id AS order_number,
				   sales_flat_order_address.shipping_name
				 FROM sales_flat_shipment_package
				 JOIN sales_flat_shipment ON sales_flat_shipment_package.shipment_id = sales_flat_shipment.entity_id
				 JOIN sales_flat_order ON sales_flat_shipment.order_id = sales_flat_order.entity_id
				 JOIN sales_flat_order_address ON sales_flat_shipment.shipping_address_id = sales_flat_order_address.entity_id
				 WHERE shipment_id IN
				 (
				    SELECT entity_id
					FROM action_log
					WHERE entity_type='shipment'
					AND action='pack'
					AND finished_at >= '".$threshold_date."'
					AND finished_at < '".$threshold_time."'
				 )
				 AND sales_flat_shipment.shipping_method NOT LIKE 'external%'
				 AND package_id NOT IN 
				 (
					SELECT package_id
					FROM manifest_item
					WHERE loaded_at >= '".$threshold_date."'
				 )
				 ".(!empty($data['facility']) ? "AND sales_flat_shipment_package.stock_id=".$redstag_db->escape($stock_id) : "")."
				 "
			)
			->result_array();
		
		if(!empty($data['idle_manifest_board_data'])) {
			$shipment_ids = array_unique(array_column($data['idle_manifest_board_data'], 'shipment_id'));
			
			$packed_times_tmp = $redstag_db
				->select('entity_id AS shipment_id, finished_at')
				->from('action_log')
				->where('entity_type', 'shipment')
				->where('action', 'pack')
				->where_in('entity_id', $shipment_ids)
				->get()->result_array();
			
			$packed_times = array_combine(
				array_column($packed_times_tmp, 'shipment_id'),
				$packed_times_tmp
			);
			
			foreach($data['idle_manifest_board_data'] as $key => $current_data) {
				$data['idle_manifest_board_data'][$key]['packed_elapsed_secs'] = strtotime($current_utc_time) - strtotime($packed_times[$current_data['shipment_id']]['finished_at']);
			}
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['idle_manifest_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_idle_manifest_board_visualization', $data, true);
		
		return $data;
	}
}