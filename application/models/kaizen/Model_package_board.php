<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_package_board extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function get_package_board_data($args) {
		$result = $args;
		
		$startTime = microtime(true);
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$this->model_outbound->update_employees_data();
		
		$this->load->model(PROJECT_CODE.'/model_shipment');
		
		$latest_date_data = $this->db
			->select('DATE(started_at) AS latest_date')
			->from('action_log')
			->where('data_status', DATA_ACTIVE)
			->order_by('started_at', 'DESC')
			->limit(1)
			->get()->result_array();

		$latest_date = $latest_date_data[0]['latest_date'];
		
		if(strtotime($latest_date) <= strtotime($args['period_to'])) {
			$this->model_shipment->sync_action_log_table();
		}

		$redstag_db = $this->load->database('redstag', TRUE);
		
		$facility_data = $this->model_db_crud->get_several_data($facility_data);
		$stock_id_by_facility = array_combine(
			array_column($facility_data, 'id'),
			array_column($facility_data, 'stock_id')
		);
		
		$stock_ids = array();
		if(!empty($args['facility'])) {
			if(!is_array($args['facility'])) {
				$args['facility'] = array($args['facility']);
			}
			
			foreach($args['facility'] as $facility_id) {
				$stock_ids[] = $stock_id_by_facility[$facility_id];
			}
		}
		
		$table_data_row_template = array(
			'row_name' => null,
			'action_summary' => array(
				'pick' => array(
					'num_shipments' => 0,
					'va_time' => 0,
					'nva_time' => 0,
					'time' => 0,
					'cost' => 0,
					'avg_time' => 0,
					'avg_cost' => 0
				),
				'pack' => array(
					'num_shipments' => 0,
					'va_time' => 0,
					'nva_time' => 0,
					'time' => 0,
					'cost' => 0,
					'avg_time' => 0,
					'avg_cost' => 0
				),
				'load' => array(
					'num_shipments' => 0,
					'va_time' => 0,
					'nva_time' => 0,
					'time' => 0,
					'cost' => 0,
					'avg_time' => 0,
					'avg_cost' => 0
				)
			),
			'total_shipments' => 0,
			'total_va_time' => 0,
			'total_nva_time' => 0,
			'total_labor_hours' => 0,
			'total_labor_hours_per_shipment' => 0,
			'total_labor_cost' => 0,
			'total_labor_cost_per_shipment' => 0
		);
		
		$table_data = array();
		$table_total_data = $table_data_row_template;
		$table_total_data['row_name'] = 'Total';
		$table_total_data['customer_name'] = $table_total_data['family_name'] = null;
		
		$sum_field_by_calculation_method = array(
			'uniform' => 
				array(
					'value_added_time' => ' SUM(alt_value_added_time) AS total_value_added_time ',
					'total_time' => ' SUM(alt_total_time) AS total_time ',
					// 'cost' => ' SUM(alt_cost) AS total_cost '
					'cost' => ' COALESCE(SUM(alt_total_time) * employees.pay_rate, SUM(alt_cost)) AS total_cost '
				),
			'distributed' => 
				array(
					'value_added_time' => ' SUM(value_added_time) AS total_value_added_time ',
					'total_time' => ' SUM(total_time) AS total_time ',
					// 'cost' => ' SUM(cost) AS total_cost '
					// 'cost' => ' COALESCE(SUM(total_time) * employees.pay_rate, SUM(cost)) AS total_cost '
					'cost' => ' IF(employees.pay_rate IS NOT NULL, SUM(total_time) * employees.pay_rate, SUM(cost)) AS total_cost '
				)
		);
		
		if($args['breakdown_type'] == 'customer') {
			$customer_list = $redstag_db
				->select('store_id, core_store.name, core_website.name AS merchant_name')
				->from('core_store')
				->join('core_website', 'core_website.website_id = core_store.website_id')
				->get()->result_array();
			
			$customer_name_by_store_id = array_combine(
				array_column($customer_list, 'store_id'),
				array_column($customer_list, 'name')
			);
			
			$merchant_name_by_store_id = array_combine(
				array_column($customer_list, 'store_id'),
				array_column($customer_list, 'merchant_name')
			);
			
			$this->db
				->select('store_id, action, COUNT(DISTINCT shipment_increment_id) AS num_shipments, '.$sum_field_by_calculation_method[$args['calculation_method']]['value_added_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['total_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['cost'])
				->from('action_log')
				->join('employees', 'employees.id = action_log.user_id')
				->where('data_valid', true)
				->where('action_log.data_status', DATA_ACTIVE)
				->where('started_at >=', $args['period_from'])
				->where('started_at <', date('Y-m-d', strtotime('+1 day '. $args['period_to'])))
				->group_by('store_id, action')
				->order_by('store_id');
				
			if(!empty($args['facility'])) {
				$this->db->where_in('stock_id', $stock_ids);
			}
			
			if($args['calculation_method'] == 'uniform') {
				$this->db->where('alt_value_added_time IS NOT NULL', null, false);
			}
			
			$raw_data = $this->db->get()->result_array();
			
			foreach($raw_data as $current_data) {
				if(!isset($table_data[$current_data['store_id']])) {
					$table_data[$current_data['store_id']] = $table_data_row_template;
				}
				
				$table_data[$current_data['store_id']]['row_name'] = $customer_name_by_store_id[$current_data['store_id']];
				$table_data[$current_data['store_id']]['merchant_name'] = $merchant_name_by_store_id[$current_data['store_id']];
				$table_data[$current_data['store_id']]['action_summary'][$current_data['action']] = array(
					'num_shipments' => $current_data['num_shipments'],
					'va_time' => $current_data['total_value_added_time'],
					'nva_time' => $current_data['total_time'] - $current_data['total_value_added_time'],
					'time' => $current_data['total_time'],
					'cost' => $current_data['total_cost'],
					'avg_time' => $current_data['total_time'] / $current_data['num_shipments'],
					'avg_cost' => $current_data['total_cost'] / $current_data['num_shipments']
				);
				$table_data[$current_data['store_id']]['total_va_time'] += $current_data['total_value_added_time'];
				$table_data[$current_data['store_id']]['total_nva_time'] += ($current_data['total_time'] - $current_data['total_value_added_time']);
				$table_data[$current_data['store_id']]['total_labor_hours'] += $current_data['total_time'];
				$table_data[$current_data['store_id']]['total_labor_hours_per_shipment'] += $current_data['total_time'] / $current_data['num_shipments'];
				$table_data[$current_data['store_id']]['total_labor_cost'] += $current_data['total_cost'];
				$table_data[$current_data['store_id']]['total_labor_cost_per_shipment'] += $current_data['total_cost'] / $current_data['num_shipments'];
			}
		}
		else if($args['breakdown_type'] == 'family') {
			$assignment_types = $this->model_db_crud->get_several_data('assignment_type');
			
			$family_name_by_assignment_type_id = array_combine(
				array_column($assignment_types, 'id'),
				array_column($assignment_types, 'assignment_type_name')
			);
			
			$family_name_by_assignment_type_id[0] = 'No Assignment';
			
			$this->db
				->select('assignment_type, action, COUNT(DISTINCT shipment_increment_id) AS num_shipments, '.$sum_field_by_calculation_method[$args['calculation_method']]['value_added_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['total_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['cost'])
				->from('action_log')
				->join('employees', 'employees.id = action_log.user_id', 'left')
				->where('data_valid', true)
				->where('action_log.data_status', DATA_ACTIVE)
				->where('action_log.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('started_at >=', $args['period_from'])
				->where('started_at <', date('Y-m-d', strtotime('+1 day '. $args['period_to'])))
				->group_by('assignment_type, action')
				->order_by('assignment_type');
				
			if(!empty($args['facility'])) {
				$this->db->where_in('stock_id', $stock_ids);
			}
			
			if($args['calculation_method'] == 'uniform') {
				$this->db->where('alt_value_added_time IS NOT NULL', null, false);
			}
			
			$raw_data = $this->db->get()->result_array();
			
			foreach($raw_data as $current_data) {
				if(!isset($table_data[$current_data['assignment_type']])) {
					$table_data[$current_data['assignment_type']] = $table_data_row_template;
				}
				
				$table_data[$current_data['assignment_type']]['row_name'] = $family_name_by_assignment_type_id[$current_data['assignment_type']];
				$table_data[$current_data['assignment_type']]['action_summary'][$current_data['action']] = array(
					'num_shipments' => $current_data['num_shipments'],
					'va_time' => $current_data['total_value_added_time'],
					'nva_time' => $current_data['total_time'] - $current_data['total_value_added_time'],
					'time' => $current_data['total_time'],
					'cost' => $current_data['total_cost'],
					'avg_time' => $current_data['total_time'] / $current_data['num_shipments'],
					'avg_cost' => $current_data['total_cost'] / $current_data['num_shipments']
				);
				$table_data[$current_data['assignment_type']]['total_va_time'] += $current_data['total_value_added_time'];
				$table_data[$current_data['assignment_type']]['total_nva_time'] += ($current_data['total_time'] - $current_data['total_value_added_time']);
				$table_data[$current_data['assignment_type']]['total_labor_hours'] += $current_data['total_time'];
				$table_data[$current_data['assignment_type']]['total_labor_hours_per_shipment'] += $current_data['total_time'] / $current_data['num_shipments'];
				$table_data[$current_data['assignment_type']]['total_labor_cost'] += $current_data['total_cost'];
				$table_data[$current_data['assignment_type']]['total_labor_cost_per_shipment'] += $current_data['total_cost'] / $current_data['num_shipments'];
			}
		}
		else if($args['breakdown_type'] == 'shipment') {
			$customer_list = $redstag_db
				->select('store_id, name')
				->from('core_store')
				->get()->result_array();
			
			$customer_name_by_store_id = array_combine(
				array_column($customer_list, 'store_id'),
				array_column($customer_list, 'name')
			);
			
			$assignment_types = $this->model_db_crud->get_several_data('assignment_type');
			
			$family_name_by_assignment_type_id = array_combine(
				array_column($assignment_types, 'id'),
				array_column($assignment_types, 'assignment_type_name')
			);
			
			$family_name_by_assignment_type_id[0] = 'No Assignment';
			
			$this->db
				->select('shipment_increment_id, assignment_type, store_id, action, '.$sum_field_by_calculation_method[$args['calculation_method']]['value_added_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['total_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['cost'])
				->from('action_log')
				->join('employees', 'employees.id = action_log.user_id', 'left')
				->where('data_valid', true)
				->where('action_log.data_status', DATA_ACTIVE)
				->where('action_log.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('started_at >=', $args['period_from'])
				->where('started_at <', date('Y-m-d', strtotime('+1 day '. $args['period_to'])))
				->group_by('shipment_increment_id, store_id, action')
				->order_by('shipment_increment_id');
				
			if(!empty($args['facility'])) {
				$this->db->where_in('stock_id', $stock_ids);
			}
			
			if($args['calculation_method'] == 'uniform') {
				$this->db->where('alt_value_added_time IS NOT NULL', null, false);
			}
			
			$raw_data = $this->db->get()->result_array();
			
			foreach($raw_data as $current_data) {
				if(!isset($table_data[$current_data['shipment_increment_id']])) {
					$table_data[$current_data['shipment_increment_id']] = $table_data_row_template;
				}
				
				$table_data[$current_data['shipment_increment_id']]['row_name'] = $current_data['shipment_increment_id'];
				$table_data[$current_data['shipment_increment_id']]['customer_name'] = $customer_name_by_store_id[$current_data['store_id']];
				$table_data[$current_data['shipment_increment_id']]['family_name'] = $family_name_by_assignment_type_id[$current_data['assignment_type']];

				$table_data[$current_data['shipment_increment_id']]['action_summary'][$current_data['action']] = array(
					'va_time' => $current_data['total_value_added_time'],
					'nva_time' => $current_data['total_time'] - $current_data['total_value_added_time'],
					'time' => $current_data['total_time'],
					'cost' => $current_data['total_cost']
				);
				$table_data[$current_data['shipment_increment_id']]['total_va_time'] += $current_data['total_value_added_time'];
				$table_data[$current_data['shipment_increment_id']]['total_nva_time'] += ($current_data['total_time'] - $current_data['total_value_added_time']);
				$table_data[$current_data['shipment_increment_id']]['total_labor_hours'] += $current_data['total_time'];
				$table_data[$current_data['shipment_increment_id']]['total_labor_cost'] += $current_data['total_cost'];
			}
		}
		else if($args['breakdown_type'] == 'employee') {
			$employee_list = $redstag_db
				->select('user_id, name')
				->from('admin_user')
				->get()->result_array();
			
			$employee_name_by_user_id = array_combine(
				array_column($employee_list, 'user_id'),
				array_column($employee_list, 'name')
			);
			
			$this->db
				->select('action_log.user_id, action, COUNT(DISTINCT shipment_increment_id) AS num_shipments, '.$sum_field_by_calculation_method[$args['calculation_method']]['value_added_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['total_time'].', '.$sum_field_by_calculation_method[$args['calculation_method']]['cost'])
				->from('action_log')
				->join('employees', 'employees.id = action_log.user_id', 'left')
				->where('data_valid', true)
				->where('action_log.data_status', DATA_ACTIVE)
				->where('action_log.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('started_at >=', $args['period_from'])
				->where('started_at <', date('Y-m-d', strtotime('+1 day '. $args['period_to'])))
				->group_by('action_log.user_id, action')
				->order_by('action_log.user_id');
				
			if(!empty($args['facility'])) {
				$this->db->where_in('stock_id', $stock_ids);
			}
			
			if($args['calculation_method'] == 'uniform') {
				$this->db->where('alt_value_added_time IS NOT NULL', null, false);
			}
			
			$raw_data = $this->db->get()->result_array();
			
			foreach($raw_data as $current_data) {
				if(!isset($table_data[$current_data['user_id']])) {
					$table_data[$current_data['user_id']] = $table_data_row_template;
				}
				
				$table_data[$current_data['user_id']]['row_name'] = $employee_name_by_user_id[$current_data['user_id']];
				$table_data[$current_data['user_id']]['action_summary'][$current_data['action']] = array(
					'num_shipments' => $current_data['num_shipments'],
					'va_time' => $current_data['total_value_added_time'],
					'nva_time' => $current_data['total_time'] - $current_data['total_value_added_time'],
					'time' => $current_data['total_time'],
					'cost' => $current_data['total_cost'],
					'avg_time' => $current_data['total_time'] / $current_data['num_shipments'],
					'avg_cost' => $current_data['total_cost'] / $current_data['num_shipments']
				);
				$table_data[$current_data['user_id']]['total_va_time'] += $current_data['total_value_added_time'];
				$table_data[$current_data['user_id']]['total_nva_time'] += ($current_data['total_time'] - $current_data['total_value_added_time']);
				$table_data[$current_data['user_id']]['total_labor_hours'] += $current_data['total_time'];
				$table_data[$current_data['user_id']]['total_labor_hours_per_shipment'] += $current_data['total_time'] / $current_data['num_shipments'];
				$table_data[$current_data['user_id']]['total_labor_cost'] += $current_data['total_cost'];
				$table_data[$current_data['user_id']]['total_labor_cost_per_shipment'] += $current_data['total_cost'] / $current_data['num_shipments'];
			}
		}
				
		foreach($table_data as $row_data_key => $row_data) {
			$row_data['total_shipments'] = max(
				isset($row_data['action_summary']['pick']['num_shipments']) ? $row_data['action_summary']['pick']['num_shipments'] : 0,
				isset($row_data['action_summary']['pack']['num_shipments']) ? $row_data['action_summary']['pack']['num_shipments'] : 0,
				isset($row_data['action_summary']['load']['num_shipments']) ? $row_data['action_summary']['load']['num_shipments'] : 0);
			
			$table_data[$row_data_key]['total_shipments'] = $row_data['total_shipments'];
			
			foreach($row_data as $key => $current_data) {
				if(in_array($key, array('row_name', 'customer_name', 'family_name', 'merchant_name'))) continue;
				
				if($key == 'action_summary') {
					foreach(array('pick', 'pack', 'load') as $action) {
						foreach($current_data[$action] as $action_key => $value) {
							$table_total_data['action_summary'][$action][$action_key] += $value;
						}
					}
				}
				else {
					$table_total_data[$key] += $current_data;
				}
			}
		}
				
		$table_total_data['total_labor_hours_per_shipment'] = !empty($table_total_data['total_shipments']) ? $table_total_data['total_labor_hours'] / $table_total_data['total_shipments'] : 0;
		
		$table_total_data['total_labor_cost_per_shipment'] = !empty($table_total_data['total_shipments']) ? $table_total_data['total_labor_cost'] / $table_total_data['total_shipments'] : 0;
		
		$result['table_data'] = $table_data;
		$result['table_total_data'] = $table_total_data;
		
		$result['table_width'] = $args['breakdown_type'] == 'shipment' ? 2400 : 3200;
				
		return $result;
	}
	
	public function get_action_log_error_data($args) {
		$result = $args;
		
		$this->load->model(PROJECT_CODE.'/model_shipment');
		$this->model_shipment->sync_action_log_table();
		
		$stock_id_by_facility = array(
			1 => 2,
			2 => 3,
			3 => 4
		);
		
		$facility_name_by_stock_id = array(
			1 => 'Springdale',
			2 => 'Island River',
			3 => 'Salt Lake City',
			4 => 'TYS-1B'
		);
		
		$table_data = array();
		
		$redstag_db = $this->load->database('redstag', TRUE);
		$employee_list = $redstag_db
			->select('user_id, name')
			->from('admin_user')
			->get()->result_array();
		
		$employee_name_by_user_id = array_combine(
			array_column($employee_list, 'user_id'),
			array_column($employee_list, 'name')
		);
		
		$customer_list = $redstag_db
			->select('store_id, name')
			->from('core_store')
			->get()->result_array();
		
		$customer_name_by_store_id = array_combine(
			array_column($customer_list, 'store_id'),
			array_column($customer_list, 'name')
		);
		
		$this->db
			->select('log_id, stock_id, shipment_increment_id, user_id, store_id, action, started_at, finished_at, duration')
			->from('action_log')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('data_valid', false)
			->where('started_at >=', $args['period_from'])
			->where('started_at <', date('Y-m-d', strtotime('+1 day '. $args['period_to'])));
		
		if(!empty($args['facility'])) {
			$this->db->where('stock_id', $stock_id_by_facility[$args['facility']]);
		}
		
		$error_data = $this->db->get()->result_array();
		
		foreach($error_data as $current_data) {
			$table_data[] = array(
				'log_id' => $current_data['log_id'],
				'facility' => $facility_name_by_stock_id[$current_data['stock_id']],
				'customer' => $customer_name_by_store_id[$current_data['store_id']],
				'employee' => $employee_name_by_user_id[$current_data['user_id']],
				'action' => ucwords($current_data['action']),
				'shipment_no' => $current_data['shipment_increment_id'],
				'started_at' => $current_data['started_at'],
				'finished_at' => $current_data['finished_at'],
				'duration' => $current_data['duration']
			);
		}
		
		$result['table_data'] = $table_data;
		
		return $result;
	}
	
	public function update_employees_pay_rate_data() {
		$result = array();
		
		$result['employees_with_no_employee_number'] = array();
		$result['employees_with_wrong_employee_number'] = array();
		$result['employees_with_updated_pay_rate'] = array();
		
		$current_employees_payrate_data = $this->db
			->select('id, employee_name, pay_rate')
			->from('employees')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->get()->result_array();
			
		$current_employees_payrate = array();
		foreach($current_employees_payrate_data as $current_data) {
			$current_employees_payrate[$current_data['id']] = $current_data;
		}
		
		$updated_employees = array();
		
		// Get current pay rate from TSheets
		$page = 1;
		
		do {
			$host='https://rest.tsheets.com/api/v1/users?page=' . $page;
			$process = curl_init();

			curl_setopt($process, CURLOPT_URL, $host);
			curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

			$return = curl_exec($process);
			$response = curl_getinfo($process);
			curl_close($process);

			$curl_result = json_decode($return, true);
			
			if(empty($curl_result['results']['users'])) break;
			
			$tsheets_employees = $curl_result['results']['users'];
			
			foreach($tsheets_employees as $current_tsheets_employee) {
				$tsheets_employee_number = $current_tsheets_employee['employee_number'];
				$tsheets_employee_name = $current_tsheets_employee['first_name'] . ' ' . $current_tsheets_employee['last_name'];
				$tsheets_employee_payroll_id = $current_tsheets_employee['payroll_id'];
				$tsheets_employee_pay_rate = $current_tsheets_employee['pay_rate'];
				
				$employee_detail = array(
					'tsheets_employee_name' => $tsheets_employee_name,
					'tsheets_employee_number' => $tsheets_employee_number,
					'tsheets_employee_payroll_id' => $tsheets_employee_payroll_id,
					'tsheets_employee_pay_rate' => $tsheets_employee_pay_rate,
					'kaizen_employee_number' => null,
					'kaizen_employee_name' => null,
					'kaizen_employee_pay_rate' => null
				);
				
				if(empty($tsheets_employee_number)) {
					$result['employees_with_no_employee_number'][] = $employee_detail;
					continue;
				}
				
				if(!isset($current_employees_payrate[$tsheets_employee_number])) {
					$result['employees_with_wrong_employee_number'][] = $employee_detail;
					continue;
				}
				
				$current_kaizen_employee = $current_employees_payrate[$tsheets_employee_number];
				$employee_detail['kaizen_employee_number'] = $current_kaizen_employee['id'];
				$employee_detail['kaizen_employee_name'] = $current_kaizen_employee['employee_name'];
				$employee_detail['kaizen_employee_pay_rate'] = $current_kaizen_employee['pay_rate'];
				
				// If employee name in db & TSheets are different...
				if(strpos(strtolower($current_kaizen_employee['employee_name']), strtolower($tsheets_employee_name)) === false) {
					$result['employees_with_wrong_employee_number'][] = $employee_detail;
					continue;
				}
				
				if($current_kaizen_employee['pay_rate'] <> $tsheets_employee_pay_rate) {
					$updated_employees[] = array(
						'id' => $employee_detail['kaizen_employee_number'],
						'pay_rate' => $employee_detail['tsheets_employee_pay_rate']
					);
					
					$result['employees_with_updated_pay_rate'][] = $employee_detail;
				}
			}
			
			$page++;
		}
		while( true );

		if(!empty($updated_employees)) {
			$result['success'] = $this->db->update_batch('employees', $updated_employees, 'id');
		}
		else {
			$result['success'] = true;
		}
		
		return $result;
	}
}