<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_team_helper extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_team_helper_data($data) {
		$data['status_shortlist'] = array('Picking', 'Packing', 'Loading', 'Training', 'Support');
		if(!isset($data['status_list'])) {
			$data['status_list'] = array('Delivery', 'Processing', 'Put-Away', 'Picking' => 'Picking', 'Packing' => 'Packing', 'Cycle Count', 'Relocation', 'Loading' => 'Load', 'Kitting', 'Paid Break', 'Unpaid Break', 'Cleaning', 'Management Request', 'Replenishment', 'Support' => 'Support', 'Team Meeting', 'Training' => 'Training');
		}
		
		$data['hours_shift'] = null;
		$data['planned_time_in_seconds'] = null;
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			if(!empty($facility_data)) {
				$data['hours_shift'] = $facility_data['hours_shift'];
				$data['planned_time_in_seconds'] = $data['hours_shift'] * 3600;
				
				if($data['date'] == date('Y-m-d')) {
					// If it's today, hours shift is the elapsed time from the start of first block time to now
					
					// Get the start time of first block time
					$block_times = $this->db
						->select('start_time')
						->from('block_times')
						->where('data_status', DATA_ACTIVE)
						->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
						->order_by('block_time_no')
						->limit(1)
						->get()->result_array();
					
					if(!empty($block_times)) {
						$planned_time_in_seconds = strtotime(date('H:i:s')) - strtotime($block_times[0]['start_time']);
						
						if($planned_time_in_seconds > 0 && $planned_time_in_seconds < $data['planned_time_in_seconds']) {
							$data['planned_time_in_seconds'] = $planned_time_in_seconds;
							$data['hours_shift'] = $data['planned_time_in_seconds'] / 3600;
						}
					}
				}
			}
		}
		
		$staff_time_log_summary = array();
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		
		$this->load->model(PROJECT_CODE.'/model_db_crud');
		$employee_data = $this->db
			->select('employees.*, employee_shift_types.pay_scale')
			->from('employees')
			->join('employee_shift_types', 'employee_shift_types.id = employees.employee_shift', 'left')
			->where('employees.data_status', DATA_ACTIVE)
			->where('employees.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->get()->result_array();
		
		$employee_data_by_name = array();
		foreach($employee_data as $employee) {
			if(!empty($data['facility']) && $employee['facility'] <> $data['facility']) continue;
			if(!empty($data['department']) && $employee['department'] <> $data['department']) continue;
			
			$employee_data_by_name[$employee['employee_name']] = array(
				'facility' => $employee['facility'],
				'department' => $employee['department'],
				'pay_scale' => $employee['pay_scale']
			);
		}
		
		$status_by_action = array('Packing' => 'Packing', 'Picking' => 'Picking', 'Load' => 'Loading');
		
		$action_log_api_url = $this->model_outbound->get_api_url(array(
			'grid_type' => 'action_log',
			'period_from' => $data['date'],
			'period_to' => $data['date']
		));
		
		$staff_time_log_api_url = $this->model_outbound->get_api_url(array(
			'grid_type' => 'staff_time_log',
			'period_from' => $data['date'],
			'period_to' => $data['date']
		));
		
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
					
					// Filter by facility & department
					if(empty($employee_data_by_name[$user])) continue;
					
					if(!empty($user) && !empty($action) && !empty($status_by_action[$action])) {			
						$status = $status_by_action[$action];

						if(!isset($staff_time_log_summary[$user])) {
							$staff_time_log_summary[$user] = array(
								'qty_by_status' => array('Packing' => 0, 'Picking' => 0, 'Loading' => 0),
								'sum_of_time_by_status' => array(),
								'cost_by_status' => array(),
								'total_qty' => 0,
								'sum_of_time' => 0,
								'first_clock_in_time' => '23:59:59',
								'planned_time_in_seconds' => null,
								'actual_time_in_seconds' => 0,
								'value_added_cost' => null,
								'non_value_added_cost' => null,
								'total_cost' => null
							);
						}
						
						$staff_time_log_summary[$user]['qty_by_status'][$status]++;
						$staff_time_log_summary[$user]['total_qty']++;
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
					
					// Filter by facility & department
					if(empty($employee_data_by_name[$user])) continue;

					if(!empty($status) && !empty($user) && !empty($duration)) {
						if(!isset($staff_time_log_summary[$user])) {
							$staff_time_log_summary[$user] = array(
								'qty_by_status' => array('Packing' => 0, 'Picking' => 0, 'Loading' => 0),
								'sum_of_time_by_status' => array(),
								'cost_by_status' => array(),
								'total_qty' => 0,
								'sum_of_time' => 0,
								'first_clock_in_time' => '23:59:59',
								'planned_time_in_seconds' => null,
								'actual_time_in_seconds' => 0,
								'value_added_cost' => null,
								'non_value_added_cost' => null,
								'total_cost' => null,
							);
						}
						
						if(strtotime($start) < strtotime($staff_time_log_summary[$user]['first_clock_in_time'])) {
							$staff_time_log_summary[$user]['first_clock_in_time'] = $start;
						}
						
						sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
						$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
						
						$staff_time_log_summary[$user]['actual_time_in_seconds'] += $time_seconds;
						
						if(in_array($status, $data['status_shortlist'])) {
							if(!isset($staff_time_log_summary[$user]['sum_of_time_by_status'][$status])) {
								$staff_time_log_summary[$user]['sum_of_time_by_status'][$status] = 0;
							}
							
							$staff_time_log_summary[$user]['sum_of_time_by_status'][$status] += $time_seconds;
							$staff_time_log_summary[$user]['sum_of_time'] += $time_seconds;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		$current_time = date('H:i:s');
		foreach($staff_time_log_summary as $employee_name => $time_log_summary) {
			$work_elapsed_time_in_seconds = strtotime($current_time) - strtotime($time_log_summary['first_clock_in_time']);

			if($data['date'] == date('Y-m-d') && $work_elapsed_time_in_seconds > 0) {
				if(!empty($data['planned_time_in_seconds']) && $work_elapsed_time_in_seconds > $data['planned_time_in_seconds']) {
					$staff_time_log_summary[$employee_name]['productivity_rate'] = $staff_time_log_summary[$employee_name]['sum_of_time'] / $data['planned_time_in_seconds'] * 100;
					$staff_time_log_summary[$employee_name]['planned_time_in_seconds'] = $data['planned_time_in_seconds'];
				}
				else {
					$staff_time_log_summary[$employee_name]['productivity_rate'] = $staff_time_log_summary[$employee_name]['sum_of_time'] / $work_elapsed_time_in_seconds * 100;
					$staff_time_log_summary[$employee_name]['planned_time_in_seconds'] = $work_elapsed_time_in_seconds;
				}
			}			
			else if(!empty($data['planned_time_in_seconds'])) {
				$staff_time_log_summary[$employee_name]['productivity_rate'] = $staff_time_log_summary[$employee_name]['sum_of_time'] / $data['planned_time_in_seconds'] * 100;
				$staff_time_log_summary[$employee_name]['planned_time_in_seconds'] = $data['planned_time_in_seconds'];
			}
			
			$staff_time_log_summary[$employee_name]['non_value_added_time_in_seconds'] = $staff_time_log_summary[$employee_name]['actual_time_in_seconds'] - $staff_time_log_summary[$employee_name]['sum_of_time'];
			
			if(!empty($employee_data_by_name[$employee_name]['pay_scale'])) {
				$staff_time_log_summary[$employee_name]['value_added_cost'] = $staff_time_log_summary[$employee_name]['sum_of_time'] / 3600 *  $employee_data_by_name[$employee_name]['pay_scale'];
				$staff_time_log_summary[$employee_name]['non_value_added_cost'] = $staff_time_log_summary[$employee_name]['non_value_added_time_in_seconds'] / 3600 * $employee_data_by_name[$employee_name]['pay_scale'];
				$staff_time_log_summary[$employee_name]['total_cost'] = $staff_time_log_summary[$employee_name]['value_added_cost'] + $staff_time_log_summary[$employee_name]['non_value_added_cost'];
				
				foreach($time_log_summary['sum_of_time_by_status'] as $status => $sum_of_time) {
					$staff_time_log_summary[$employee_name]['cost_by_status'][$status] = $sum_of_time / 3600 * $employee_data_by_name[$employee_name]['pay_scale'];
				}
			}
		}
		
		uasort ( $staff_time_log_summary , function ($a, $b) {
				if(!isset($a['productivity_rate']) || !isset($b['productivity_rate'])) {
					return 1;
				}
				return ($a['productivity_rate'] < $b['productivity_rate']) ? 1 : -1;
			}
		);
		
		$data['staff_time_log_summary'] = $staff_time_log_summary;
		
		$data['team_helper_staff_time_log_html'] = $this->load->view(PROJECT_CODE.'/view_team_helper_board_staff_time_table', $data, true);
		
		return $data;
	}
	
	public function get_work_summary_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$data['period_to'] = isset($data['period_to']) ? $data['period_to'] : $data['period_from'];

		$work_summary_data = array();
		
		$stock_id = null;
		
		$this->load->model('model_db_crud');
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$stock_id = $facility_data['stock_id'];
		}
		
		$date_field = "IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern'))";
		
		if(!empty($stock_id)) {
			if($stock_id == 3 || $stock_id == 6) {
				$date_field = "CONVERT_TZ(started_at,'UTC','US/Mountain')";
			}
			else {
				$date_field = "CONVERT_TZ(started_at,'UTC','US/Eastern')";
			}
		}
		
		$label_field = null;
		switch($data['periodicity']) {
			case 'weekly':
				$label_field = 'CONCAT("WEEK ", DATE_ADD(DATE('.$date_field.'), INTERVAL - WEEKDAY(DATE('.$date_field.'))+6 DAY))';
				$data['periodicity_label'] = 'Week Ending';
				break;
			case 'monthly':
				$label_field = 'DATE_FORMAT('.$date_field.', "%Y-%m (%M %Y)")';
				$data['periodicity_label'] = 'Month';
				break;
			case 'yearly':
				$label_field = 'YEAR('.$date_field.')';
				$data['periodicity_label'] = 'Year';
				break;
			case 'daily':
			default:
				$label_field = 'DATE('.$date_field.')';
				$data['periodicity_label'] = 'Date';
				break;
		}
		
		// Time Log Data
		$redstag_db
			->select(
				$label_field . ' AS label,
				COUNT(DISTINCT user_id) AS num_operators,
				SUM(duration) AS sum_of_time', false)
			->from('time_log')
			->where_in('status', array('picking','packing','loading'))
			->group_by($label_field)
			->order_by($label_field);
		
		if(!empty($stock_id)) {
			if($stock_id == 3 || $stock_id == 6) {
				$redstag_db
					->where('stock_id', $stock_id)
					->where("started_at >= CONVERT_TZ('".$data['period_from']."','US/Mountain','UTC')", null, false)
					->where("started_at < CONVERT_TZ('".date('Y-m-d', strtotime('+1 day '.$data['period_to']))."','US/Mountain','UTC')", null, false);
			}
			else {
				$redstag_db
					->where('stock_id', $stock_id)
					->where("started_at >= CONVERT_TZ('".$data['period_from']."','US/Eastern','UTC')", null, false)
					->where("started_at < CONVERT_TZ('".date('Y-m-d', strtotime('+1 day '.$data['period_to']))."','US/Eastern','UTC')", null, false);
			}
		}
		else {
			$redstag_db
				->where($date_field . " >= '".$data['period_from']."'", null, false)
				->where($date_field . " < '".date('Y-m-d', strtotime('+1 day '.$data['period_to']))."'", null, false);
		}
		
		$time_log_data = $redstag_db->get()->result_array();
		
		if(!empty($time_log_data)) {
			foreach($time_log_data as $current_data) {
				$work_summary_data[$current_data['label']] = $current_data;
				$work_summary_data[$current_data['label']]['total_load_qty'] = 0;
			}
		}
		
		// Action Log Data
		$redstag_db
			->select(
				$label_field . ' AS label,
				COUNT(*) AS total_load_qty', false)
			->from('action_log')
			->where_in('action', 'load')
			->group_by($label_field)
			->order_by($label_field);
		
		if(!empty($stock_id)) {
			if($stock_id == 3 || $stock_id == 6) {
				$redstag_db
					->where('stock_id', $stock_id)
					->where("started_at >= CONVERT_TZ('".$data['period_from']."','US/Mountain','UTC')", null, false)
					->where("started_at < CONVERT_TZ('".date('Y-m-d', strtotime('+1 day '.$data['period_to']))."','US/Mountain','UTC')", null, false);
			}
			else {
				$redstag_db
					->where('stock_id', $stock_id)
					->where("started_at >= CONVERT_TZ('".$data['period_from']."','US/Eastern','UTC')", null, false)
					->where("started_at < CONVERT_TZ('".date('Y-m-d', strtotime('+1 day '.$data['period_to']))."','US/Eastern','UTC')", null, false);
			}
		}
		else {
			$redstag_db
				->where($date_field . " >= '".$data['period_from']."'", null, false)
				->where($date_field . " < '".date('Y-m-d', strtotime('+1 day '.$data['period_to']))."'", null, false);
		}
		
		$action_log_data = $redstag_db->get()->result_array();
			
		if(!empty($action_log_data)) {
			foreach($action_log_data as $current_data) {
				$work_summary_data[$current_data['label']]['total_load_qty'] = $current_data['total_load_qty'];
			}
		}
		
		$data['total_sum_of_time'] = 0;
		$data['total_load_qty'] = 0;
		if(!empty($work_summary_data)) {
			foreach($work_summary_data as $label => $current_data) {
				$work_summary_data[$label]['hours_per_package'] = !empty($current_data['total_load_qty']) ? $current_data['sum_of_time'] / 3600 / $current_data['total_load_qty'] : 0;
				
				if(!empty($data['facility']) && !empty($facility_data['operational_cost_per_package']) && !empty($facility_data['fte_cost_per_hour'])) {
					$work_summary_data[$label]['cost_per_package'] = !empty($current_data['total_load_qty']) ? $current_data['sum_of_time'] / 3600 / $current_data['total_load_qty'] * $facility_data['operational_cost_per_package'] * $facility_data['fte_cost_per_hour'] : 0;
				}
				
				$data['total_sum_of_time'] += $current_data['sum_of_time'];
				$data['total_load_qty'] += $current_data['total_load_qty'];
			}	
		}
			
		$data['hours_per_package'] = !empty($data['total_load_qty']) ? $data['total_sum_of_time'] / 3600 / $data['total_load_qty'] : 0;
		
		if(!empty($data['facility']) && !empty($facility_data['operational_cost_per_package']) && !empty($facility_data['fte_cost_per_hour'])) {
			$data['cost_per_package'] = !empty($data['total_load_qty']) ? $data['total_sum_of_time'] / 3600 / $data['total_load_qty'] * $facility_data['operational_cost_per_package'] * $facility_data['fte_cost_per_hour'] : 0;
		}
		
		$data['work_summary_data'] = array_values($work_summary_data);
		
		return $data;
	}
}