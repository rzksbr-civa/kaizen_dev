<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_scoreboard extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_outbound');
	}
	
	public function get_scoreboard_data($data) {
		$result = array();
		$scoreboard = array();
		
		$result['scoreboard_data'] = null;
		$result['scoreboard_tables_html'] = null;
		
		$current_time = date('H:i:s');
		
		$data['action_shortlist'] = array('Picking', 'Packing', 'Load');
		$data['status_shortlist'] = array('Picking', 'Packing', 'Loading');
		
		if(!isset($data['block_time_list'])) {
			$data['block_time_list'] = $this->model_db_crud->get_data(
				'block_times', 
				array(
					'select' => array('id', 'block_time_name', 'start_time', 'end_time'),
					'order_by' => array('block_time_name' => 'asc')
				)
			);
		}
		
		if(!isset($data['status_list'])) {
			$data['status_list'] = array('Delivery', 'Processing', 'Put-Away', 'Picking' => 'Picking', 'Packing' => 'Packing', 'Cycle Count', 'Relocation', 'Loading' => 'Load', 'Kitting', 'Paid Break', 'Unpaid Break', 'Cleaning', 'Management Request', 'Replenishment', 'Support', 'Team Meeting', 'Training');
		}
		
		foreach($data['block_time_list'] as $block_time) {
			if($current_time >= $block_time['start_time'] && $current_time <= $block_time['end_time']) {
				$current_block_time = $block_time['id'];
				break;
			}
		}
		
		$user_list = $this->model_db_crud->get_data(
			'employees', 
			array(
				'select' => array('employee_name', 'facility', 'department', 'employee_shift')
			)
		);
		$users = array();
		foreach($user_list as $user) {
			$users[$user['employee_name']] = array(
				'facility' => $user['facility'],
				'department' => $user['department'],
				'employee_shift' => $user['employee_shift'],
				'assignment_types' => array(),
				'assignment_type_names' => array()
			);
		}
		
		$this->db
			->select('assignments.employee, employees.employee_name, assignments.assignment_type, assignment_types.assignment_type_name')
			->from('assignments')
			->join('assignment_types', 'assignment_types.id = assignments.assignment_type', 'left')
			->join('employees', 'employees.id = assignments.employee')
			->where('assignments.data_status', 'active')
			->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('assignments.date', $data['date'])
			->where('employees.is_active', true);
			
		if(!empty($data['block_time'])) {
			$this->db->where_in('assignments.shift', $data['block_time']);
		}
		
		if(!empty($data['assignment_type'])) {
			$this->db->where_in('assignments.assignment_type', $data['assignment_type']);
		}
		
		$employee_assignments = $this->db->get()->result_array();
		
		foreach($employee_assignments as $asg) {
			if(!isset($users[$asg['employee_name']])) {
				$users[$asg['employee_name']] = array(
					'facility' => null,
					'assignment_types' => array(),
					'assignment_type_names' => array()
				);
			}
			
			if(!in_array($asg['assignment_type'], $users[$asg['employee_name']]['assignment_types'])) {
				$users[$asg['employee_name']]['assignment_types'][] = $asg['assignment_type'];
			}
			
			if(!in_array($asg['assignment_type_name'], $users[$asg['employee_name']]['assignment_type_names'])) {
				$users[$asg['employee_name']]['assignment_type_names'][] = $asg['assignment_type_name'];
			}
		}
		
		// Get employee assignments from MWE
		$redstag_db = $this->load->database('redstag', TRUE);
		$mwe_active_employee_data = $redstag_db
			->select('user_id, name')
			->from('admin_user')
			->where('is_staff', true)
			->where('is_active', true)
			->get()->result_array();
		$mwe_active_employee_name_by_id = array_combine(
			array_column($mwe_active_employee_data, 'user_id'),
			array_column($mwe_active_employee_data, 'name')
		);
		
		$this->load->model(PROJECT_CODE.'/model_shipment');
		$this->model_shipment->sync_action_log_table();
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
		}
		
		$assignment_types = $this->db->select('*')->from('assignment_types')->where('data_status', DATA_ACTIVE)->get()->result_array();
		$assignment_type_name_by_id = array();
		foreach($assignment_types as $assignment_type) {
			$assignment_type_name_by_id[$assignment_type['id']] = $assignment_type['assignment_type_name'];
		}
		
		$auto_employee_assignments = array();
		
		$this->db
			->select('user_id, action, assignment_type')
			->from('action_log')
			->where('action_log.data_status', DATA_ACTIVE)
			->where('action_log.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('action_log.started_at >=', $data['date'])
			->where('action_log.started_at <', date('Y-m-d', strtotime('+1 day '.$data['date'])))
			->group_by('user_id, action, assignment_type');
		
		if(!empty($data['facility'])) {
			$this->db->where('action_log.stock_id', $facility_data['stock_id']);
		}
		
		if(!empty($data['assignment_type'])) {
			$this->db->where_in('assignment_types.id', $data['assignment_type']);
		}
		
		$auto_employee_assignments_data = $this->db->get()->result_array();

		$action_map = array('pick' => 'Picking', 'pack' => 'Packing', 'load' => 'Load');
		
		foreach($auto_employee_assignments_data as $current_data) {
			$assignment_type_name = isset($assignment_type_name_by_id[$current_data['assignment_type']]) ? $assignment_type_name_by_id[$current_data['assignment_type']] : null;
			
			if(!empty($mwe_active_employee_name_by_id[$current_data['user_id']])) {
				$employee_name = $mwe_active_employee_name_by_id[$current_data['user_id']];
				if(!isset($auto_employee_assignments[$employee_name])) {
					$auto_employee_assignments[$employee_name] = array();
				}
				
				if(!isset($auto_employee_assignments[$employee_name][$action_map[$current_data['action']]])) {
					$auto_employee_assignments[$employee_name][$action_map[$current_data['action']]] = $assignment_type_name;
				}
				else {
					$auto_employee_assignments[$employee_name][$action_map[$current_data['action']]] .= ', ' . $assignment_type_name;
				}
			}
		}
		
		// Block time filter
		$block_times = array();
		if(!empty($data['block_time'])) {
			$block_times = $this->db
				->select('start_time, end_time')
				->from('block_times')
				->where('block_times.data_status', 'active')
				->where('block_times.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where_in('block_times.id', $data['block_time'])
				->get()->result_array();
		}
		else {
			$block_times = $this->db
				->select('start_time, end_time')
				->from('block_times')
				->where('block_times.data_status', 'active')
				->where('block_times.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				// ->where('block_times.id', $current_block_time)
				->get()->result_array();
		}
		
		$grid_filter = 'started_at[from]='.date('m', strtotime($data['date'])).'/'.date('j', strtotime($data['date'])).'/'.date('Y', strtotime($data['date'])).'&started_at[to]='.date('m', strtotime($data['date'])).'/'.date('j', strtotime($data['date'])).'/'.date('Y', strtotime($data['date'])).'&started_at[locale]=en_US&finished_at[locale]=en_US';
		
		$grid_filter = base64_encode(str_replace(array('%3D', '%26'), array('=','&'), urlencode($grid_filter)));
		
		$action_log_api_url = 'https://wms.redstagfulfillment.com/automationv1.php?action=grid&auth_key=4CJeQNbMeiWuH7D782xmctOgbZBwPT4e&grid_type=action_log&grid_format=csv&grid_filter='.$grid_filter;
		
		$staff_time_log_api_url = 'https://wms.redstagfulfillment.com/automationv1.php?action=grid&auth_key=4CJeQNbMeiWuH7D782xmctOgbZBwPT4e&grid_type=staff_time_log&grid_format=csv&grid_filter='.$grid_filter;
		
		// Count Qty from Action Log
		
		if (($handle = fopen($action_log_api_url, "r")) !== FALSE) {
			$row = 1;
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$type = $csv_data[1];
					$action = $csv_data[2];
					$user = $csv_data[4];
					$start = substr($csv_data[5],-8);
					$duration = $csv_data[7];
					
					if(!empty($user) && in_array($action, $data['action_shortlist'])) {
						// Block time filter
						$current_block_time = $this->model_outbound->get_block_time_by_time($start, $data['block_time_list']);
						if(!empty($data['block_time']) && !in_array($current_block_time, $data['block_time'])) continue;
						
						if((empty($data['type']) || in_array($type, $data['type'])) && (empty($data['action']) || in_array($action, $data['action'])) && (empty($data['time_from']) || $start >= $data['time_from']) && (empty($data['time_to']) || $start <= $data['time_to'])) {
							if(!isset($scoreboard[$action][$user])) {
								$scoreboard[$action][$user] = array(
									'qty' => 0,
									'sum_of_time' => 0
								);
							}
							
							$scoreboard[$action][$user]['qty']++;
							
							/*sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
							$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
							$scoreboard[$action][$user]['total_duration'] += $time_seconds;*/
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		// Count Sum of Time from Staff Time Log
		if (($handle = fopen($staff_time_log_api_url, "r")) !== FALSE) {
			$row = 1;
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$status = $csv_data[1];
					$user = $csv_data[2];
					$start = substr($csv_data[4],-8);
					$duration = $csv_data[6];
					
					if(!empty($status) && !empty($user) && in_array($status, $data['status_shortlist'])) {
						// Block time filter
						$current_block_time = $this->model_outbound->get_block_time_by_time($start, $data['block_time_list']);
						if(!empty($data['block_time']) && !in_array($current_block_time, $data['block_time'])) continue;
						
						$action = $data['status_list'][$status];

						if(
							(empty($data['action']) || in_array($action, $data['action']))
							&& (empty($data['time_from']) || $start >= $data['time_from'])
							&& (empty($data['time_to']) || $start <= $data['time_to'])) {
							if(!isset($scoreboard[$action][$user])) {
								$scoreboard[$action][$user] = array(
									'qty' => 0,
									'sum_of_time' => 0
								);
							}
							
							if(!empty($duration)) {
								sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
								$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
							}
							else {
								$time_seconds = strtotime(date('H:i:s')) - strtotime($start);
							}
							
							$scoreboard[$action][$user]['sum_of_time'] += $time_seconds;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		foreach($scoreboard as $action => $action_scoreboard) {
			foreach($action_scoreboard as $user => $user_data) {
				// Filter user based on their facility
				if(!empty($data['facility']) && array_key_exists($user, $users) && $users[$user]['facility'] <> $data['facility']) {
					unset($scoreboard[$action][$user]);
					continue;
				}
				
				// Filter user based on their department
				if(!empty($data['department']) && array_key_exists($user, $users) && $users[$user]['department'] <> $data['department']) {
					unset($scoreboard[$action][$user]);
					continue;
				}
				
				// Filter user based on their assignment
				if(!empty($data['assignment_type'])
					&& empty($users[$user]['assignment_types'])
					&& empty($auto_employee_assignments[$user][$action])
				) {
					unset($scoreboard[$action][$user]);
					continue;
				}
				
				// Filter user based on their employee shift type
				if(!empty($data['employee_shift_type']) && array_key_exists($user, $users) && !in_array($users[$user]['employee_shift'], $data['employee_shift_type'])) {
					unset($scoreboard[$action][$user]);
					continue;
				}
				
				$scoreboard[$action][$user]['sum_of_time_in_hours'] = $scoreboard[$action][$user]['sum_of_time'] / 3600;
				
				$scoreboard[$action][$user]['average'] = ($scoreboard[$action][$user]['sum_of_time_in_hours'] > 0) ? $scoreboard[$action][$user]['qty'] / $scoreboard[$action][$user]['sum_of_time_in_hours'] : 0;
				
				$scoreboard[$action][$user]['formatted_sum_of_time'] = sprintf('%02d', floor($scoreboard[$action][$user]['sum_of_time'] / 3600)) . ':' . sprintf('%02d', floor(($scoreboard[$action][$user]['sum_of_time'] % 3600) / 60)) . ':' . sprintf('%02d', $scoreboard[$action][$user]['sum_of_time'] % 60);
				
				$average_time_in_secs = $scoreboard[$action][$user]['average'] * 3600;
				
				$scoreboard[$action][$user]['formatted_average'] = number_format($scoreboard[$action][$user]['average'], 2);
				
				$scoreboard[$action][$user]['assignment'] = isset($users[$user]) ? implode(', ', $users[$user]['assignment_type_names']) : null;
			}
		}
		
		ksort($scoreboard);
		
		foreach($scoreboard as $action => $scoreboard_action) {
			if($data['sort_by'] == 'Qty') {
				uasort ( $scoreboard[$action] , function ($a, $b) {
						return ($a['qty'] < $b['qty']) ? 1 : -1;
					}
				);
			}
			else if($data['sort_by'] == 'Total Time') {
				uasort ( $scoreboard[$action] , function ($a, $b) {
						return ($a['sum_of_time'] < $b['sum_of_time']) ? 1 : -1;
					}
				);
			}
			else if($data['sort_by'] == 'Average Time') {
				uasort ( $scoreboard[$action] , function ($a, $b) {
						return ($a['average'] < $b['average']) ? 1 : -1;
					}
				);
			}
		}
		
		$result['scoreboard_data'] = $scoreboard;
		
		$result['scoreboard_tables_html'] = $this->load->view(PROJECT_CODE.'/view_scoreboard_tables', array('scoreboard' => $scoreboard, 'auto_employee_assignments' => $auto_employee_assignments), true);
		
		return $result;
	}
}