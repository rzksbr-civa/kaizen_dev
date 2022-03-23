<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_outbound extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_performance_kpi_by_status_data($args) {
		$result = array();
		
		$action_shortlist = array('Load', 'Picking', 'Packing');
		$status_shortlist = array('Loading', 'Picking', 'Packing');
		
		$data_to_show = $args['data_to_show'];
		$periodicity = $args['periodicity'];
		$filter_status = $args['status']; // Array of status
		$filter_period_from = !empty($args['period_from']) ? $args['period_from'] : date('Y-m-d');
		$filter_period_to = !empty($args['period_to']) ? $args['period_to'] : date('Y-m-d');
		
		$filter_action = array();
		foreach($filter_status as $status) {
			$filter_action[] = $this->get_action_by_status($status);
		}
		
		$date_periodicity_mapping = array();
		$current_date = $filter_period_from;
		while($current_date <= $filter_period_to) {
			$date_periodicity_mapping[$current_date] = $this->get_periodicity_date($periodicity, $current_date);
			$current_date = date('Y-m-d', strtotime('+1 day '.$current_date));
		}
		
		$date_label = array();
		
		// Create data template for period
		$current_date = $this->get_periodicity_date($periodicity, $filter_period_from);
		while($current_date <= $filter_period_to) {
			switch($periodicity) {
				case 'daily':
					$label = $current_date;
					break;
				case 'weekly':
					$label = 'W'.date('W', strtotime($current_date)).' '.date('Y', strtotime($current_date));
					break;
				case 'monthly':
					$label = date('M Y', strtotime($current_date));
					break;
				case 'quarterly':
					$month = date('n', strtotime($current_date));
					$label = 'Q'.(floor(($month-1)/4)+1) . ' ' . date('Y', strtotime($current_date));
					break;
				case 'yearly':
					$label = date('Y', strtotime($current_date));
					break;
			}
			
			$date_label[$current_date] = array(
				'label' => $label,
				'total' => array('qty'=>0, 'time'=>0, 'average'=>0)
			);
			
			switch($periodicity) {
				case 'daily':
					$current_date = date('Y-m-d', strtotime('+1 day '.$current_date));
					break;
				case 'weekly':
					$current_date = date('Y-m-d', strtotime('+7 day '.$current_date));
					break;
				case 'monthly':
					$current_date = date('Y-m-d', strtotime('+1 month '.$current_date));
					break;
				case 'quarterly':
					$current_date = date('Y-m-d', strtotime('+3 month '.$current_date));
					break;
				case 'yearly':
					$current_date = date('Y-m-d', strtotime('+1 year '.$current_date));
					break;
			}
		}
		
		$date_label['total'] = array(
			'label' => 'Total',
			'total' => 0
		);
		
		$date_label_template = array();
		foreach($date_label as $key => $value) {
			$date_label_template[$key] = array('qty' => 0, 'time' => 0, 'average' => 0);
		}
		
		$action_log_api_url = $this->get_api_url(array('grid_type'=>'action_log', 'period_from' => $filter_period_from, 'period_to' => $filter_period_to));
		
		$row = 1;
		if (($handle = fopen($action_log_api_url, "r")) !== FALSE) {
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$type = $csv_data[1];
					$action = $csv_data[2];
					$user = $csv_data[4];
					$date = substr($csv_data[5],0,10);
					$duration = $csv_data[7];
					
					$date = $date_periodicity_mapping[$date];

					if(!empty($type) && !empty($user) && !empty($duration) && in_array($action, $action_shortlist)) {
						if(empty($filter_action) || in_array($action, $filter_action)) {
							$date_label[$date]['total']['qty']++;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		$staff_time_log_api_url = $this->get_api_url(array('grid_type'=>'staff_time_log', 'period_from' => $filter_period_from, 'period_to' => $filter_period_to));
		
		$row = 1;
		if (($handle = fopen($staff_time_log_api_url, "r")) !== FALSE) {
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$status = $csv_data[1];
					$user = $csv_data[2];
					$date = substr($csv_data[4],0,10);
					$duration = $csv_data[6];
					
					$date = $date_periodicity_mapping[$date];

					if(!empty($type) && !empty($user) && !empty($duration) && in_array($status, $status_shortlist)) {
						if(empty($filter_status) || in_array($status, $filter_status)) {
							sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
							$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;

							$date_label[$date]['time'] += $time_seconds;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		foreach($date_label as $date => $current_date_label) {
			$date_label[$date]['average'] = $date_label[$date]['qty'] / ($date_label[$date]['time']/3600);
			
			switch($data_to_show) {
				case 'qty':
					$result['date_label'][$date]['total'] = $set_of_data['qty'];
					break;
				case 'time':
					$result['user_data'][$user_name][$the_date] = $set_of_data['time'];
					break;
				case 'average':
					$result['user_data'][$user_name][$the_date] = $set_of_data['average'];
					break;
			}
		}
		
		foreach($user_data as $user_name => $current_user_data) {
			$result['user_data'][$user_name] = array('total' => 0);
			
			foreach($current_user_data as $the_date => $set_of_data) {
				if($the_date == 'total') continue;
				
				switch($data_to_show) {
					case 'qty':
						$result['user_data'][$user_name][$the_date] = $set_of_data['qty'];
						break;
					case 'time':
						$result['user_data'][$user_name][$the_date] = $set_of_data['time'];
						break;
					case 'average':
						$result['user_data'][$user_name][$the_date] = $set_of_data['average'];
						break;
				}
				
				$result['user_data'][$user_name]['total'] += $result['user_data'][$user_name][$the_date];
				$result['date_label'][$the_date]['total'] += $result['user_data'][$user_name][$the_date];
				$result['date_label']['total']['total'] += $result['user_data'][$user_name][$the_date];
			}
		}
		
		foreach($result['user_data'] as $user_name => $user_data_by_date) {
			foreach($user_data_by_date as $date => $current_user_data) {
				if($data_to_show == 'time') {
					$result['user_data'][$user_name][$date] = sprintf('%02d', floor($result['user_data'][$user_name][$date] / 3600)) . ':' . sprintf('%02d', floor(($result['user_data'][$user_name][$date] % 3600) / 60)) . ':' . sprintf('%02d', $result['user_data'][$user_name][$date] % 60);
				}
				else if($data_to_show == 'average') {
					$result['user_data'][$user_name][$date] = sprintf('%02d', floor($result['user_data'][$user_name][$date])) . ':' . sprintf('%02d', floor((($result['user_data'][$user_name][$date] * 3600) % 3600) / 60)) . ':' . sprintf('%02d', ($result['user_data'][$user_name][$date] * 3600) % 60);
				}
			}
		}
		
		foreach($result['date_label'] as $date => $date_info) {
			if($data_to_show == 'time') {
				$result['date_label'][$date]['total'] = sprintf('%02d', floor($result['date_label'][$date]['total'] / 3600)) . ':' . sprintf('%02d', floor(($result['date_label'][$date]['total'] % 3600) / 60)) . ':' . sprintf('%02d', $result['date_label'][$date]['total'] % 60);
			}
			else if($data_to_show == 'average') {
				$result['date_label'][$date]['total'] = sprintf('%02d', floor($result['date_label'][$date]['total'])) . ':' . sprintf('%02d', floor((($result['date_label'][$date]['total'] * 3600) % 3600) / 60)) . ':' . sprintf('%02d', ($result['date_label'][$date]['total']*3600) % 60);
			}
		}
		
		
		$yaxis_label = 'Count of Type';
		
		$chart_options = array(
			'chart' => array(
				'type' => 'bar',
				'height' => 300,
				'fontFamily' => 'Mukta'
			),
			'plotOptions' => array(
				'bar' => array(
					'dataLabels' => array(
						'position' => 'top'
					)
				)
			),
			'dataLabels' => array(
				'offsetY' => -30,
				'style' => array(
					'colors' => array('lightgrey')
				)
			),
			'grid' => array(
				'borderColor' => 'grey',
				'row' => array(
					'colors' => 'grey'
				)
			),
			'tooltip' => array(
				'enabled' => true,
				'followCursor' => true
			),
			'series' => array(
				array(
					'name' => $yaxis_label,
					'data' => array_values($data)
				)
			),
			'xaxis' => array(
				'categories' => array_keys($data),
				'labels' => array(
					'style' => array(
						'colors' => 'lightgrey'
					),
				),
				'title' => array(
					'style' => array(
						'colors' => 'lightgrey'
					),
				)
			),
			'yaxis' => array(
				'min' => 0,
				'title' => array(
					'text' => $yaxis_label,
					'style' => array(
						'color' => 'lightgrey'
					)
				),
				'labels' => array(
					'style' => array(
						'color' => 'lightgrey'
					)
				)
			)
		);
		
		$result['chart_options'] = $chart_options;
		
		return $result;
	}
	
	public function get_performance_kpi_by_user_data($args) {
		$result = array();
		
		/*$result = array(
			'date_label' => array(
				'2019-09-01' => array(
					'label' => '2019-09-01',
					'total' => 27
				),
				'2019-09-02' => array(
					'label' => '2019-09-02',
					'total' => 60
				),
				'2019-09-03' => array(
					'label' => '2019-09-02',
					'total' => 57
				),
				'total' => array(
					'label' => 'Total',
					'total' => 144
				)
			),
			'user_data' => array(
				'A' => array(
					'2019-09-01' => 12,
					'2019-09-02' => 15,
					'2019-09-03' => 17,
					'total' => 44
				),
				'B' => array(
					'2019-09-01' => 10,
					'2019-09-02' => 25,
					'2019-09-03' => 25,
					'total' => 60
				),
				'C' => array(
					'2019-09-01' => 5,
					'2019-09-02' => 20,
					'2019-09-03' => 15,
					'total' => 40
				)
			)
		);*/
		
		$action_shortlist = array('Load', 'Picking', 'Packing');
		$status_shortlist = array('Loading', 'Picking', 'Packing');
		
		$data_to_show = $args['data_to_show'];
		$periodicity = $args['periodicity'];
		$filter_status = $args['status']; // Array of status
		$filter_period_from = !empty($args['period_from']) ? $args['period_from'] : date('Y-m-d');
		$filter_period_to = !empty($args['period_to']) ? $args['period_to'] : date('Y-m-d');
		
		$filter_action = array();
		foreach($filter_status as $status) {
			$filter_action[] = $this->get_action_by_status($status);
		}
		
		$date_periodicity_mapping = array();
		$current_date = $filter_period_from;
		while($current_date <= $filter_period_to) {
			$date_periodicity_mapping[$current_date] = $this->get_periodicity_date($periodicity, $current_date);
			$current_date = date('Y-m-d', strtotime('+1 day '.$current_date));
		}
		
		$date_label = array();
		
		// Create data template for period
		$current_date = $this->get_periodicity_date($periodicity, $filter_period_from);
		while($current_date <= $filter_period_to) {
			switch($periodicity) {
				case 'daily':
					$label = $current_date;
					break;
				case 'weekly':
					$label = 'W'.date('W', strtotime($current_date)).' '.date('Y', strtotime($current_date));
					break;
				case 'monthly':
					$label = date('M Y', strtotime($current_date));
					break;
				case 'quarterly':
					$month = date('n', strtotime($current_date));
					$label = 'Q'.(floor(($month-1)/4)+1) . ' ' . date('Y', strtotime($current_date));
					break;
				case 'yearly':
					$label = date('Y', strtotime($current_date));
					break;
			}
			
			$date_label[$current_date] = array(
				'label' => $label,
				'total' => 0
			);
			
			switch($periodicity) {
				case 'daily':
					$current_date = date('Y-m-d', strtotime('+1 day '.$current_date));
					break;
				case 'weekly':
					$current_date = date('Y-m-d', strtotime('+7 day '.$current_date));
					break;
				case 'monthly':
					$current_date = date('Y-m-d', strtotime('+1 month '.$current_date));
					break;
				case 'quarterly':
					$current_date = date('Y-m-d', strtotime('+3 month '.$current_date));
					break;
				case 'yearly':
					$current_date = date('Y-m-d', strtotime('+1 year '.$current_date));
					break;
			}
		}
		
		$date_label['total'] = array(
			'label' => 'Total',
			'total' => 0
		);

		$result['date_label'] = $date_label;
		
		$date_label_template = array();
		foreach($date_label as $key => $value) {
			$date_label_template[$key] = array('qty' => 0, 'time' => 0, 'average' => 0);
		}
		
		$user_data = array();
		
		$action_log_api_url = $this->get_api_url(array('grid_type'=>'action_log', 'period_from' => $filter_period_from, 'period_to' => $filter_period_to));
		
		$row = 1;
		if (($handle = fopen($action_log_api_url, "r")) !== FALSE) {
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$type = $csv_data[1];
					$action = $csv_data[2];
					$user = $csv_data[4];
					$date = substr($csv_data[5],0,10);
					$duration = $csv_data[7];
					
					$date = $date_periodicity_mapping[$date];

					if(!empty($type) && !empty($user) && !empty($duration) && in_array($action, $action_shortlist)) {
						if(empty($filter_action) || in_array($action, $filter_action)) {
							if(!isset($user_data[$user])) {
								$user_data[$user] = $date_label_template;
							}
							
							$user_data[$user][$date]['qty']++;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		$staff_time_log_api_url = $this->get_api_url(array('grid_type'=>'staff_time_log', 'period_from' => $filter_period_from, 'period_to' => $filter_period_to));
		
		$row = 1;
		if (($handle = fopen($staff_time_log_api_url, "r")) !== FALSE) {
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$status = $csv_data[1];
					$user = $csv_data[2];
					$date = substr($csv_data[4],0,10);
					$duration = $csv_data[6];
					
					$date = $date_periodicity_mapping[$date];

					if(!empty($type) && !empty($user) && !empty($duration) && in_array($status, $status_shortlist)) {
						if(empty($filter_status) || in_array($status, $filter_status)) {
							if(!isset($user_data[$user])) {
								$user_data[$user] = $date_label_template;
							}
							
							sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
							$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;

							$user_data[$user][$date]['time'] += $time_seconds;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		if(!empty($user_data)) {
			foreach($user_data as $user_name => $user_data_by_date) {
				foreach($user_data_by_date as $date => $current_user_data) {
					if($user_data[$user_name][$date]['time'] > 0) {
						$user_data[$user_name][$date]['average'] = $user_data[$user_name][$date]['qty'] / ($user_data[$user_name][$date]['time']/3600);
					}
				}
			}
			
			foreach($user_data as $user_name => $current_user_data) {
				$result['user_data'][$user_name] = array('total' => 0);
				
				foreach($current_user_data as $the_date => $set_of_data) {
					if($the_date == 'total') continue;
					
					switch($data_to_show) {
						case 'qty':
							$result['user_data'][$user_name][$the_date] = $set_of_data['qty'];
							break;
						case 'time':
							$result['user_data'][$user_name][$the_date] = $set_of_data['time'];
							break;
						case 'average':
							$result['user_data'][$user_name][$the_date] = $set_of_data['average'];
							break;
					}
					
					$result['user_data'][$user_name]['total'] += $result['user_data'][$user_name][$the_date];
					$result['date_label'][$the_date]['total'] += $result['user_data'][$user_name][$the_date];
					$result['date_label']['total']['total'] += $result['user_data'][$user_name][$the_date];
				}
			}
			
			uasort ( $result['user_data'] , function ($a, $b) {
					return ($a['total'] < $b['total']) ? 1 : -1;
				}
			);
			
			
			foreach($result['user_data'] as $user_name => $user_data_by_date) {
				foreach($user_data_by_date as $date => $current_user_data) {
					if($data_to_show == 'time') {
						$result['user_data'][$user_name][$date] = sprintf('%02d', floor($result['user_data'][$user_name][$date] / 3600)) . ':' . sprintf('%02d', floor(($result['user_data'][$user_name][$date] % 3600) / 60)) . ':' . sprintf('%02d', $result['user_data'][$user_name][$date] % 60);
					}
					else if($data_to_show == 'average') {
						$result['user_data'][$user_name][$date] = sprintf('%04d', floor($result['user_data'][$user_name][$date])) . ':' . sprintf('%02d', floor((($result['user_data'][$user_name][$date] * 3600) % 3600) / 60)) . ':' . sprintf('%02d', ($result['user_data'][$user_name][$date]*3600) % 60);
					}
				}
			}
			
			foreach($result['date_label'] as $date => $date_info) {
				if($data_to_show == 'time') {
					$result['date_label'][$date]['total'] = sprintf('%02d', floor($result['date_label'][$date]['total'] / 3600)) . ':' . sprintf('%02d', floor(($result['date_label'][$date]['total'] % 3600) / 60)) . ':' . sprintf('%02d', $result['date_label'][$date]['total'] % 60);
				}
				else if($data_to_show == 'average') {
					$result['date_label'][$date]['total'] = sprintf('%04d', floor($result['date_label'][$date]['total'])) . ':' . sprintf('%02d', floor((($result['date_label'][$date]['total'] * 3600) % 3600) / 60)) . ':' . sprintf('%02d', ($result['date_label'][$date]['total'] * 3600) % 60);
				}
			}
		}

		return $result;
	}

	public function get_action_by_status($status) {
		$pair_status_action = array(
			'Loading' => 'Load',
			'Packing' => 'Packing',
			'Picking' => 'Picking'
		);
		
		return $pair_status_action[$status];
	}
	
	public function get_periodicity_date($periodicity, $date) {
		switch($periodicity) {
			case 'hourly':
				return date('Y-m-d H:00:00', strtotime($date));
			case 'daily':
				return date('Y-m-d', strtotime($date));
			case 'weekly':
				return date('Y-m-d', strtotime('Last Monday ' . date('Y-m-d', strtotime('+1 day ' . $date))));
			case 'monthly':
				return date('Y-m-01', strtotime($date));
			case 'quarterly':
				$month = date('n', strtotime($date));
				return date('Y-'.sprintf('%02d',((floor(($month-1)/4)+1)*3-2)).'-01', strtotime($date));
			case 'yearly':
				return date('Y-01-01', strtotime($date));
		}
	}
	
	public function get_api_url($args) {
		$auth_key = '4CJeQNbMeiWuH7D782xmctOgbZBwPT4e';
		$grid_type = isset($args['grid_type']) ? $args['grid_type'] : null;
		$grid_format = isset($args['grid_format']) ? $args['grid_format'] : 'csv';
		$grid_filter = isset($args['grid_filter']) ? $args['grid_filter'] : null;
		
		switch($grid_type) {
			case 'action_log':
			case 'staff_time_log':
				if(!empty($args['period_from']) && !empty($args['period_to'])) {
					$grid_filter = 'started_at[from]='.date('m/j/Y', strtotime($args['period_from'])).'&started_at[to]='.date('m/j/Y', strtotime($args['period_to'])).'&started_at[locale]=en_US&finished_at[locale]=en_US';
				
					$grid_filter = base64_encode(str_replace(array('%3D', '%26'), array('=','&'), urlencode($grid_filter)));
				}
				break;
		}
		
		$api_url = 'https://wms.redstagfulfillment.com/automationv1.php?action=grid&auth_key=' . $auth_key;
		
		if(!empty($grid_type)) {
			$api_url .= '&grid_type='.$grid_type;
		}
		
		$api_url .= '&grid_format='.$grid_format;
		
		if(!empty($grid_filter)) {
			$api_url .= '&grid_filter='.$grid_filter;
		}
		
		return $api_url;
	}
	
	public function update_employees_data() {
		$result = array();
		
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		
		$this->load->model('model_db_crud');
		$employee_data = $this->db
			->select('*')
			->from('employees')
			->where('data_group', $user_group)
			->get()->result_array();

		$employees = array();
		foreach($employee_data as $employee) {
			$employees[$employee['id']] = $employee;
		}
		
		$now = date('Y-m-d H:i:s');
		
		$user_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
		
		$new_employees = array();
		$updated_employees = array();
		
		
		$redstag_db = $this->load->database('redstag', TRUE);
		$mwe_employee_data = $redstag_db
			->select('user_id, name, email, username, is_active, is_staff')
			->from('admin_user')
			->get()->result_array();
			
		foreach($mwe_employee_data as $current_data) {
			if(empty($employees[$current_data['user_id']])) {
				$new_employees[] = array(
					'id' => $current_data['user_id'],
					'employee_username' => $current_data['username'],
					'employee_name' => $current_data['name'],
					'email' => $current_data['email'],
					'is_active' => $current_data['is_active'],
					'is_staff' => $current_data['is_staff'],
					'data_status' => DATA_ACTIVE,
					'data_group' => $user_group,
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
				
				$result[] = array(
					'status' => 'New',
					'employee_name' => $current_data['name']
				);
			}
			else if(
						$employees[$current_data['user_id']]['employee_username'] <> $current_data['username'] ||
						$employees[$current_data['user_id']]['employee_name'] <> $current_data['name'] ||
						$employees[$current_data['user_id']]['email'] <> $current_data['email'] ||
						$employees[$current_data['user_id']]['is_active'] <> $current_data['is_active'] ||
						$employees[$current_data['user_id']]['is_staff'] <> $current_data['is_staff']
					) {
				$updated_employees[] = array(
					'id' => $current_data['user_id'],
					'employee_username' => $current_data['username'],
					'employee_name' => $current_data['name'],
					'email' => $current_data['email'],
					'is_active' => $current_data['is_active'],
					'is_staff' => $current_data['is_staff'],
					'data_status' => DATA_ACTIVE,
					'data_group' => $user_group,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
				
				$result[] = array(
					'status' => 'Updated',
					'employee_name' => $current_data['name']
				);
			}
		}
		
		if(!empty($new_employees)) {
			$this->db->insert_batch('employees', $new_employees);
		}
		
		if(!empty($updated_employees)) {
			$this->db->update_batch('employees', $updated_employees, 'id');
		}
		
		return $result;
	}
	
	public function get_shipment_report_data($args) {
		$result = array();
		
		$report_type = $args['report_type'];
		$facility = $args['facility'];
		$periodicity = $args['periodicity'];
		$period_from = $args['period_from'];
		$period_to = $args['period_to'];
		$excluded_customers = $args['excluded_customers'];

		$this->load->model('model_db_crud');
		$facility_data = !empty($facility) ? $this->model_db_crud->get_specific_data('facility', $facility) : null;
		$facility_name = !empty($facility_data) ? $facility_data['facility_name'] : null;
		
		$result['table_data'] = array();
		
		$date_map = array();
		
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		for($date = $period_from; strtotime($date) <= strtotime($period_to); $date = date('Y-m-d', strtotime('+1 day '.$date))) {
			switch($periodicity) {
				case 'daily':
					$date_map[$date]['date'] = $date;
					$date_map[$date]['period_label'] = $date;
					break;
				case 'weekly':
					$date_map[$date]['date'] = date('Y-m-d', strtotime('last monday', strtotime('+1 day '. $date)));
					$date_map[$date]['period_label'] = 'Week ' . $date_map[$date]['date'];
					break;
				case 'monthly':
					$date_map[$date]['date'] = date('Y-m-01', strtotime($date));
					$date_map[$date]['period_label'] = date('M Y', strtotime($date));
					break;
				case 'yearly':
					$date_map[$date]['date'] = date('Y-01-01', strtotime($date));
					$date_map[$date]['period_label'] = date('Y', strtotime($date));
					break;
			}
			
			if(!isset($result['table_data'][$date_map[$date]['period_label']])) {
				if($report_type == 'no_breakdown') {
					$result['table_data'][$date_map[$date]['period_label']] = array(
						'demand' => 0,
						'num_employees' => 0,
						'labor_hours_worked' => 0,
						'labor_hours_per_package' => 0,
						'cost' => 0,
						'cost_per_package' => 0,
						'date' => $date
					);
				}
				else if($report_type == 'breakdown_by_product_family') {
					$result['table_data'][$date_map[$date]['period_label']] = array();
				}
			}
		}
		
		$this->load->model(PROJECT_CODE.'/model_shipment');
		$this->model_shipment->update_shipment_report_table();
		
		$period_in_query = "DATE(started_at)";
		$period_in_new_query = "DATE(date)";
		/*switch($periodicity) {
			case 'daily':
				$period_in_query = "DATE(started_at)";
				$period_in_new_query = "DATE(date)";
				break;
			case 'weekly':
				$period_in_query = "DATE(DATE_ADD(started_at, INTERVAL - WEEKDAY(started_at) DAY))";
				$period_in_new_query = "DATE(DATE_ADD(date, INTERVAL - WEEKDAY(date) DAY))";
				break;
			case 'monthly':
				$period_in_query = "DATE_FORMAT(started_at, '%Y-%m-01')";
				$period_in_new_query = "DATE_FORMAT(date, '%Y-%m-01')";
				break;
			case 'yearly':
				$period_in_query = "DATE_FORMAT(started_at, '%Y-01-01')";
				$period_in_new_query = "DATE_FORMAT(date, '%Y-01-01')";
				break;
		}*/
		
		if($report_type == 'no_breakdown') {
			// Demand
			$prod_db
				->select($period_in_new_query.' AS period_date, SUM(shipment_qty) AS volume, SUM(labor_hours_worked) AS total_labor_time, SUM(cost) AS total_cost')
				->from('shipment_report')
				->where('date >=', $period_from)
				->where('date <', date('Y-m-d H:i:s', strtotime('+1 day ' . $period_to)))
				->group_by($period_in_new_query);
			
			if(isset($facility)) {
				$prod_db->where('facility', $facility);
			}
			
			if(!empty($excluded_customers)) {
				$prod_db->where_not_in('store_id', $excluded_customers);
			}
			
			$demand_data = $prod_db->get()->result_array();
			
			foreach($demand_data as $current_data) {
				switch($periodicity) {
					case 'daily':
						$period_label = $current_data['period_date'];
						break;
					case 'weekly':
						$period_label = 'Week ' . date('Y-m-d', strtotime('previous monday', strtotime('+1 day '.$current_data['period_date'])));
						break;
					case 'monthly':
						$period_label = date('M Y', strtotime($current_data['period_date']));
						break;
					case 'yearly':
						$period_label = date('Y', strtotime($current_data['period_date']));
						break;
				}
				
				$result['table_data'][$period_label]['demand'] += $current_data['volume'];
				
				$result['table_data'][$period_label]['labor_hours_worked'] += $current_data['total_labor_time'];
				
				$result['table_data'][$period_label]['labor_hours_per_package'] += $current_data['volume'] > 0 ? $current_data['total_labor_time'] / $current_data['volume'] : 0;
				
				$result['table_data'][$period_label]['cost'] += $current_data['total_cost'];
				
				$result['table_data'][$period_label]['cost_per_package'] += $current_data['volume'] > 0 ? $current_data['total_cost'] / $current_data['volume'] : 0;
			}
			
			$prod_db
				->select($period_in_query.' AS period_date, COUNT(DISTINCT user_id) AS num_employees')
				->from('action_log')
				->where('data_valid', true)
				->where('started_at >=', $period_from)
				->where('started_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $period_to)))
				->group_by($period_in_query);
			
			if(isset($facility_data['stock_id'])) {
				$prod_db->where('stock_id', $facility_data['stock_id']);
			}
			
			if(!empty($excluded_customers)) {
				$prod_db->where_not_in('store_id', $excluded_customers);
			}
			
			$num_employees_data = $prod_db->get()->result_array();
						
			foreach($num_employees_data as $current_data) {
				switch($periodicity) {
					case 'daily':
						$period_label = $current_data['period_date'];
						break;
					case 'weekly':
						$period_label = 'Week ' . date('Y-m-d', strtotime('previous monday', strtotime('+1 day '.$current_data['period_date'])));
						break;
					case 'monthly':
						$period_label = date('M Y', strtotime($current_data['period_date']));
						break;
					case 'yearly':
						$period_label = date('Y', strtotime($current_data['period_date']));
						break;
				}
				
				$result['table_data'][$period_label]['num_employees'] = $current_data['num_employees'];
			}
		}
		else if($report_type == 'breakdown_by_product_family') {	
			$assignment_types_data = $this->model_db_crud->get_several_data('assignment_type');

			$assignment_type_of_printer = array_combine(
				array_column($assignment_types_data, 'label_printer_prefix'),
				array_column($assignment_types_data, 'id')
			);
			
			$result['overall_total'] = array(
				'action' => 0,
				'labor_hours_worked' => 0,
				'labor_hours_per_assignment' => 0,
				'cost' => 0,
				'cost_per_assignment' => 0,
				'num_employees_worked' => 0
			);
			
			$assignment_types = array(
				0 => array(
					'assignment_type_name' => '(No Assignment)',
					'show' => true,
					'total_actions' => 0,
					'total_labor_hours_worked' => 0,
					'total_labor_hours_per_assignment' => 0,
					'total_cost' => 0,
					'total_cost_per_assignment' => 0,
					'total_num_employees_worked' => 0
				)
			);
			$assignment_type_template = array(0);
			foreach($assignment_types_data as $assignment_type) {
				if(isset($assignment_type['label_printer_prefix'])) {
					$assignment_types[$assignment_type['id']] = array(
						'assignment_type_name' => $assignment_type['assignment_type_name'],
						'show' => true,
						'total_actions' => 0,
						'total_labor_hours_worked' => 0,
						'total_labor_hours_per_assignment' => 0,
						'total_cost' => 0,
						'total_cost_per_assignment' => 0,
						'total_num_employees_worked' => 0
					);
					$assignment_type_template[$assignment_type['id']] = 0;
				}
			}

			foreach($result['table_data'] as $period_label => $data) {
				$result['table_data'][$period_label] = array(
					'action' => $assignment_type_template,
					'num_employees' => $assignment_type_template,
					'labor_hours_worked' => $assignment_type_template,
					'labor_hours_per_assignment' => $assignment_type_template,
					'cost' => $assignment_type_template,
					'cost_per_assignment' => $assignment_type_template,
					'total' => array(
						'action' => 0,
						'labor_hours_worked' => 0,
						'labor_hours_per_assignment' => 0,
						'cost' => 0,
						'cost_per_assignment' => 0,
						'num_employees_worked' => 0
					),
					'date' => $date
				);
			}

			$prod_db
				->select($period_in_new_query.' AS period_date, assignment_type, GREATEST(SUM(pack_qty),SUM(pick_qty),SUM(load_qty)) AS volume, SUM(labor_hours_worked) AS total_labor_time, SUM(cost) AS total_cost', false)
				->from('shipment_report')
				->where('date >=', $period_from)
				->where('date <', date('Y-m-d H:i:s', strtotime('+1 day ' . $period_to)))
				->group_by($period_in_new_query.', assignment_type');
			
			if(isset($facility)) {
				$prod_db->where('facility', $facility);
			}
			
			if(!empty($excluded_customers)) {
				$prod_db->where_not_in('store_id', $excluded_customers);
			}
			
			$volume_per_family_data = $prod_db->get()->result_array();
						
			foreach($volume_per_family_data as $current_data) {
				switch($periodicity) {
					case 'daily':
						$period_label = $current_data['period_date'];
						break;
					case 'weekly':
						$period_label = 'Week ' . date('Y-m-d', strtotime('previous monday', strtotime('+1 day '.$current_data['period_date'])));
						break;
					case 'monthly':
						$period_label = date('M Y', strtotime($current_data['period_date']));
						break;
					case 'yearly':
						$period_label = date('Y', strtotime($current_data['period_date']));
						break;
				}
				
				$result['table_data'][$period_label]['action'][$current_data['assignment_type']] += $current_data['volume'];
				$result['table_data'][$period_label]['total']['action'] += $current_data['volume'];
				$assignment_types[$current_data['assignment_type']]['total_actions'] += $current_data['volume'];
				$result['overall_total']['action'] += $current_data['volume'];
				
				$result['table_data'][$period_label]['labor_hours_worked'][$current_data['assignment_type']] += $current_data['total_labor_time'];
				$result['table_data'][$period_label]['total']['labor_hours_worked'] += $current_data['total_labor_time'];
				$assignment_types[$current_data['assignment_type']]['total_labor_hours_worked'] += $current_data['total_labor_time'];
				$result['overall_total']['labor_hours_worked'] += $current_data['total_labor_time'];
				
				$result['table_data'][$period_label]['cost'][$current_data['assignment_type']] += $current_data['total_cost'];
				$result['table_data'][$period_label]['total']['cost'] += $current_data['total_cost'];
				$assignment_types[$current_data['assignment_type']]['total_cost'] += $current_data['total_cost'];
				$result['overall_total']['cost'] += $current_data['total_cost'];
				
				$result['table_data'][$period_label]['labor_hours_per_assignment'][$current_data['assignment_type']] += $current_data['volume'] > 0 ? $current_data['total_labor_time'] / $current_data['volume'] : 0;
				
				$result['table_data'][$period_label]['cost_per_assignment'][$current_data['assignment_type']] += $current_data['volume'] > 0 ? $current_data['total_cost'] / $current_data['volume'] : 0;
			}

			$result['assignment_types'] = $assignment_types;
		}
		
		return $result;
	}
	
	public function get_takt_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$data['break_times'] = array();
		$data['break_times'][1]['start'] = isset($data['break_time_1_start']) ? $data['break_time_1_start'] : '10:30';
		$data['break_times'][1]['end'] = isset($data['break_time_1_end']) ? $data['break_time_1_end'] : '10:45';
		$data['break_times'][2]['start'] = isset($data['break_time_2_start']) ? $data['break_time_2_start'] : '13:00';
		$data['break_times'][2]['end'] = isset($data['break_time_2_end']) ? $data['break_time_2_end'] : '13:30';
		$data['break_times'][3]['start'] = isset($data['break_time_3_start']) ? $data['break_time_3_start'] : '16:00';
		$data['break_times'][3]['end'] = isset($data['break_time_3_end']) ? $data['break_time_3_end'] : '16:15';
		$data['break_times'][4]['start'] = isset($data['break_time_4_start']) ? $data['break_time_4_start'] : null;
		$data['break_times'][4]['end'] = isset($data['break_time_4_end']) ? $data['break_time_4_end'] : null;
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$data['timezone'] = $timezone;
		
		if(isset($data['date'])) {
			$data['start_date'] = $data['date'];
		}
		if(isset($data['custom_start_time'])) {
			$data['start_time'] = $data['custom_start_time'];
		}
		
		if(empty($data['end_time'])) {
			$data['end_time'] = '00:00:00';
		}
		$data['end_date'] = strtotime($data['end_time']) > strtotime($data['start_time']) ? $data['start_date'] : date('Y-m-d', strtotime('+1 day '.$data['start_date']));
		
		$data['start_datetime'] = $data['start_date'] . (!empty($data['start_time']) ? ' ' . $data['start_time'] : '');
		$data['end_datetime'] = $data['end_date'] . (!empty($data['end_time']) ? ' ' . $data['end_time'] : ' 23:59:59');
		
		$data['num_hours'] = floor((strtotime($data['end_datetime']) - strtotime($data['start_datetime'])) / 3600);
		
		$data['hour_offsets'] = array();
		for($i=0; $i<24; $i++) {
			$data['hour_offsets'][date('G', strtotime('+'.$i.' hour '.$data['start_time']))] = $i;
		}
		
		$start_day = date('w', strtotime($data['start_date']));
		
		$start_datetime_in_utc = date('Y-m-d H:i:s', strtotime('+'.($timezone*-1).' hour ' . $data['start_datetime']));
		$end_datetime_in_utc = date('Y-m-d H:i:s', strtotime('+'.($timezone*-1).' hour ' . $data['end_datetime']));
		$current_local_time = date('Y-m-d H:i:s', strtotime($timezone.' hours '.gmdate('Y-m-d H:i:s')));

		$data['completed_shipments_count'] = 0;
		$data['hourly_completed_shipments_count'] = array();
		$data['hourly_completed_orders_count'] = array();
		$data['past_hourly_completed_shipments_count'] = array();
		
		$data['hourly_orders_count'] = array();
		$data['past_hourly_orders_count'] = array();
		
		$tmp_time = date('Y-m-d H:00:00', strtotime($data['start_datetime']));
		while(strtotime($tmp_time) < strtotime($data['end_datetime'])) {
			$data['hourly_completed_shipments_count'][$tmp_time]['value'] = 0;
			$data['hourly_completed_orders_count'][$tmp_time]['value'] = 0;
			$data['past_hourly_completed_shipments_count'][$tmp_time]['value'] = 0;
			
			$data['hourly_completed_shipments_count'][$tmp_time]['value_per_minute'] = 0;
			$data['hourly_completed_orders_count'][$tmp_time]['value_per_minute'] = 0;
			$data['past_hourly_completed_shipments_count'][$tmp_time]['value_per_minute'] = 0;
			
			$data['hourly_completed_shipments_count'][$tmp_time]['employee'] = array();
			
			$data['hourly_shipments_count_per_minute'][$tmp_time] = 0;
			
			$data['hourly_orders_count'][$tmp_time] = 0;
			$data['past_hourly_orders_count'][$tmp_time] = 0;
			
			$tmp_time = date('Y-m-d H:00:00', strtotime('+1 hour '.$tmp_time));
		}

		$packages_data = $redstag_db
			->select('DATE(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_date, HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
			->from('sales_flat_shipment_package')
			->where('created_at >=', $start_datetime_in_utc)
			->where('created_at <=', $end_datetime_in_utc)
			->where('stock_id', $stock_id)
			->group_by('the_date, the_hour')
			->get()->result_array();
			
		foreach($packages_data as $current_data) {
			$data['completed_shipments_count'] += $current_data['qty'];
			$data['hourly_completed_shipments_count'][$current_data['the_date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])]['value'] += $current_data['qty'];
		}
		
		
		foreach($data['hourly_completed_shipments_count'] as $the_hour => $current_data) {
			$elapsed_mins = 60;
			if($the_hour == date('Y-m-d H:00:00', strtotime($current_local_time))) {
				$elapsed_mins = (strtotime(date('H:i:s')) - strtotime(date('H:00:00'))) / 60;
				if($elapsed_mins == 0) $elapsed_mins = 1;
			}
			
			$data['hourly_completed_shipments_count'][$the_hour]['value_per_minute'] = round($current_data['value'] / $elapsed_mins, 2);
		}
		
		$completed_orders_data = $redstag_db
			->select('COUNT(DISTINCT(order_id)) AS completed_orders_count')
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->where('sales_flat_shipment_package.created_at >=', $start_datetime_in_utc)
			->where('sales_flat_shipment_package.created_at <', $end_datetime_in_utc)
			->where('sales_flat_shipment_package.stock_id', $stock_id)
			->where('sales_flat_shipment.defunct', 0)
			->get()->result_array();
		
		$data['completed_orders_count'] = $completed_orders_data[0]['completed_orders_count'];
				
		$hourly_completed_orders_data = $redstag_db
			->select("
				DATE(IF(sales_flat_shipment_package.stock_id IN(3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))) AS the_date,
				HOUR(IF(sales_flat_shipment_package.stock_id IN(3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))) AS the_hour,
				COUNT(DISTINCT(order_id)) AS completed_orders_count", false)
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->where('sales_flat_shipment_package.created_at >=', $start_datetime_in_utc)
			->where('sales_flat_shipment_package.created_at <', $end_datetime_in_utc)
			->where('sales_flat_shipment_package.stock_id', $stock_id)
			->where('sales_flat_shipment.defunct', 0)
			->group_by('the_date, the_hour')
			->get()->result_array();

		foreach($hourly_completed_orders_data as $current_data) {
			$the_hour = $current_data['the_date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour']);
			
			$elapsed_mins = 60;
			if($the_hour == date('Y-m-d H:00:00')) {
				$elapsed_mins = (strtotime(date('H:i:s')) - strtotime(date('H:00:00'))) / 60;
				if($elapsed_mins == 0) $elapsed_mins = 1;
			}
			
			$data['hourly_completed_orders_count'][$the_hour]['value'] += $current_data['completed_orders_count'];
			$data['hourly_completed_orders_count'][$the_hour]['value_per_minute'] += round($current_data['completed_orders_count'] / $elapsed_mins, 2);
		}
		
		$data['hourly_staffs_status_list'] = array();
		$data['hourly_staffs_count_by_status'] = array();
		$data['total_hourly_staffs_count_by_status'] = array();
		$data['hourly_staffs_count'] = array();
		$data['past_hourly_staffs_count_by_status'] = array();
		$data['past_total_hourly_staffs_count_by_status'] = array();
		$data['past_hourly_staffs_count'] = array();
		
		$working_staffs = array();
		$working_staffs_by_status = array();
		
		$last_four_week_dates = array();
		$last_four_week_start_datetimes_in_utc = array();
		$last_four_week_end_datetimes_in_utc = array();
		
		$last_four_week_dates[] = date('Y-m-d', strtotime($data['start_datetime']));
		$last_four_week_start_datetimes_in_utc[] = $start_datetime_in_utc;
		$last_four_week_end_datetimes_in_utc[] = $end_datetime_in_utc;
		for($i=1; $i<=4; $i++) {
			$last_four_week_start_datetimes_in_utc[] = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $start_datetime_in_utc));
			$last_four_week_end_datetimes_in_utc[] = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $end_datetime_in_utc));
			$last_four_week_dates[] = date('Y-m-d', strtotime('-'.($i*7). ' days ' . $data['start_datetime']));
		}
		
		$timezone_name = 'US/Eastern';
		if($stock_id == 3 || $stock_id == 6) $timezone_name = 'US/Mountain';
		
		for($i=0; $i<=4; $i++) {
			$working_staffs[$i] = array();
			
			$time_log_data = $redstag_db
				->select(
					"status,
					user_id,
					HOUR(CONVERT_TZ(started_at,'UTC','".$timezone_name."')) AS start_hour", false)
				->from('time_log')
				->where('stock_id', $stock_id)
				->group_start()
					->where('started_at >=', $last_four_week_start_datetimes_in_utc[$i])
					->where('started_at <', $last_four_week_end_datetimes_in_utc[$i])
				->group_end()
				->where_not_in('status', array('cycle_count','delivery','kitting','processing','putaway','relocation'))
				->group_by('status, user_id, start_hour')
				->get()->result_array();
			
			if(!empty($time_log_data)) {
				foreach($time_log_data as $current_data) {
					if(!in_array($current_data['status'], $data['hourly_staffs_status_list'])) {
						$data['hourly_staffs_status_list'][] = $current_data['status'];
					}
					
					$hour_offset = $data['hour_offsets'][$current_data['start_hour']];
					
					$the_status = $current_data['status'];
					
					if(!isset($working_staffs[$i][$the_status.'-'.$hour_offset])) {
						$working_staffs[$i][$the_status.'-'.$hour_offset] = array();
					}
					
					$working_staffs[$i][$the_status.'-'.$hour_offset][$current_data['user_id']] = true;
					
					if(!isset($working_staffs_by_status[$i][$the_status])) {
						$working_staffs_by_status[$i][$the_status] = array();
					}
					if(!isset($working_staffs_by_status[$i]['total'])) {
						$working_staffs_by_status[$i]['total'] = array();
					}
					$working_staffs_by_status[$i][$the_status][$current_data['user_id']] = true;
					$working_staffs_by_status[$i]['total'][$current_data['user_id']] = true;
					
					if(!isset($working_staffs[$i]['total-'.$hour_offset])) {
						$working_staffs[$i]['total-'.$hour_offset] = array();
					}
					
					$working_staffs[$i]['total-'.$hour_offset][$current_data['user_id']] = true;
				}
			}
		}
		
		foreach($data['hourly_staffs_status_list'] as $the_status) {
			for($hour_offset=0; $hour_offset<$data['num_hours']; $hour_offset++) {
				$data['hourly_staffs_count_by_status'][$the_status.'-'.$hour_offset] = isset($working_staffs[0][$the_status.'-'.$hour_offset]) ? count($working_staffs[0][$the_status.'-'.$hour_offset]) : 0;
			}
									
			$data['total_hourly_staffs_count_by_status'][$the_status] = isset($working_staffs_by_status[0][$the_status]) ? count($working_staffs_by_status[0][$the_status]) : 0;
		}
		
		for($hour_offset=0; $hour_offset<$data['num_hours']; $hour_offset++) {
			$data['hourly_staffs_count'][$hour_offset] = isset($working_staffs[0]['total-'.$hour_offset]) ? count($working_staffs[0]['total-'.$hour_offset]) : 0;
		}
		
		$data['total_hourly_staffs_count_by_status']['total'] = isset($working_staffs_by_status[0]['total']) ? count($working_staffs_by_status[0]['total']) : 0;

		for($i=1; $i<=4; $i++) {
			foreach($data['hourly_staffs_status_list'] as $the_status) {
				for($hour_offset=0; $hour_offset<$data['num_hours']; $hour_offset++) {
					if(!isset($data['past_hourly_staffs_count_by_status'][$the_status.'-'.$hour_offset])) {
						$data['past_hourly_staffs_count_by_status'][$the_status.'-'.$hour_offset] = 0;
					}
					
					$data['past_hourly_staffs_count_by_status'][$the_status.'-'.$hour_offset] += isset($working_staffs[$i][$the_status.'-'.$hour_offset]) ? count($working_staffs[$i][$the_status.'-'.$hour_offset]) / 4 : 0;
				}
				
				if(!isset($data['past_total_hourly_staffs_count_by_status'][$the_status])) {
					$data['past_total_hourly_staffs_count_by_status'][$the_status] = 0;
				}
				
				$data['past_total_hourly_staffs_count_by_status'][$the_status] += isset($working_staffs_by_status[$i][$the_status]) ? count($working_staffs_by_status[$i][$the_status]) / 4 : 0;
			}
			
			for($hour_offset=0; $hour_offset<$data['num_hours']; $hour_offset++) {
				if(!isset($data['past_hourly_staffs_count'][$hour_offset])) {
					$data['past_hourly_staffs_count'][$hour_offset] = 0;
				}
				
				$data['past_hourly_staffs_count'][$hour_offset] += isset($working_staffs[$i]['total-'.$hour_offset]) ? count($working_staffs[$i]['total-'.$hour_offset]) / 4 : 0;
			}
			
			if(!isset($data['past_total_hourly_staffs_count_by_status']['total'])) {
				$data['past_total_hourly_staffs_count_by_status']['total'] = 0;
			}
			
			$data['past_total_hourly_staffs_count_by_status']['total'] += isset($working_staffs_by_status[$i]['total']) ? count($working_staffs_by_status[$i]['total']) / 4 : 0;
		}

		// Past 4 weeks hourly completed shipments
		$current_hh_mm_ss = gmdate('H:i:s');
		$data['past_average_completed_shipments_count_to_time'] = 0;
		$past_completed_shipments_count_to_time = array();
		for($i=1; $i<=4; $i++) {
			$past_completed_shipments_count_to_time[$i] = 0;
			
			$past_start_datetime_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $start_datetime_in_utc));
			$past_end_datetime_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $end_datetime_in_utc));
			
			$redstag_db
				->select('DATE(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_date, HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
				->from('sales_flat_shipment_package')
				->where('created_at >=', $past_start_datetime_in_utc)
				->where('created_at <', $past_end_datetime_in_utc)
				->group_by('the_date, the_hour');
			
			if(!empty($stock_id)) {
				$redstag_db->where('stock_id', $stock_id);
			}
			
			$packages_data = $redstag_db->get()->result_array();
		
			foreach($packages_data as $current_data) {
				$the_date = (date('w', strtotime($current_data['the_date'])) == $start_day) ? $data['start_date'] : date('Y-m-d', strtotime('+1 day '.$data['start_date']));
				
				$data['past_hourly_completed_shipments_count'][$the_date . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])]['value'] += $current_data['qty'];
			}
			
			$redstag_db
				->select('DATE(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_date, HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
				->from('sales_flat_shipment_package')
				->where('created_at >=', $past_start_datetime_in_utc)
				->where('created_at <', date('Y-m-d H:i:s', strtotime($past_start_datetime_in_utc) + strtotime(date('Y-m-d H:i:s')) - strtotime($data['start_datetime']) ))
				->group_by('the_date, the_hour');
			
			if(!empty($stock_id)) {
				$redstag_db->where('stock_id', $stock_id);
			}
			
			$packages_data = $redstag_db->get()->result_array();
			
			$past_completed_shipments_count_to_time[$i] = !empty($packages_data) ? $packages_data[0]['qty'] : 0;
		}

		foreach($data['past_hourly_completed_shipments_count'] as $key => $value) {
			$data['past_hourly_completed_shipments_count'][$key]['value'] = round($data['past_hourly_completed_shipments_count'][$key]['value'] / 4);
			$data['past_hourly_completed_shipments_count'][$key]['value_per_minute'] = round($data['past_hourly_completed_shipments_count'][$key]['value'] / 60, 2);
		}
		
		$data['past_average_completed_shipments_count_to_time'] = round(array_sum($past_completed_shipments_count_to_time) / count($past_completed_shipments_count_to_time));
		
		// GET SHIPMENT COUNT FIGURE
		$redstag_db
			->select('sales_flat_shipment.status, COUNT(sales_flat_shipment.entity_id) AS shipments_count, COUNT(DISTINCT(order_id)) AS orders_count')
			->from('sales_flat_shipment')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('sales_flat_shipment.target_ship_date', date('Y-m-d', strtotime($data['end_datetime'])))
			->where_in('sales_flat_shipment.status', array('new','picking','picked','packing'))
			->where('sales_flat_order.can_fulfill', 1)
			->where('sales_flat_shipment.defunct', 0)
			->not_like('sales_flat_shipment.shipping_method', 'external', 'after')
			->group_by('sales_flat_shipment.status')
			->order_by('sales_flat_shipment.status');
		
		if(!empty($stock_id)) {
			$redstag_db->where('sales_flat_shipment.stock_id', $stock_id);
		}
		
		$total_shipments_data = $redstag_db->get()->result_array();
		
		$data['new_shipments_count'] = 0;
		$data['new_orders_count'] = 0;
		
		$data['in_processing_shipments_count'] = 0;
		$data['in_processing_orders_count'] = 0;
		
		$data['in_processing_shipments_by_status'] = array();
		
		foreach($total_shipments_data as $current_data) {
			if($current_data['status'] == 'new') {
				$data['new_shipments_count'] = $current_data['shipments_count'];
				$data['new_orders_count'] = $current_data['orders_count'];
			}
			else {
				$data['in_processing_shipments_count'] += $current_data['shipments_count'];
				$data['in_processing_orders_count'] += $current_data['orders_count'];
				$data['in_processing_shipments_by_status'][] = $current_data;
			}
		}

		$data['total_shipments_count'] = $data['new_shipments_count'] + $data['in_processing_shipments_count'];
		$data['total_orders_count'] = $data['new_orders_count'] + $data['in_processing_orders_count'];
		
		// TOTAL SHIPMENTS BY SHIPPING METHOD
		$data['total_shipments_by_shipping_method'] = array(
			'FedEx' => 0,
			'FedEx Express' => 0,
			'UPS' => 0,
			'UPS Express' => 0,
			'All USPS' => 0,
			'All OnTrac' => 0,
			'All LaserShip' => 0,
			'All Amazon' => 0,
			'Other' => 0
		);
		$redstag_db
			->select('sales_flat_shipment.shipping_method, COUNT(sales_flat_shipment.entity_id) AS shipments_count')
			->from('sales_flat_shipment')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('sales_flat_shipment.target_ship_date', date('Y-m-d', strtotime($data['end_datetime'])))
			->where_in('sales_flat_shipment.status', array('new','picking','picked','packing'))
			->where('sales_flat_order.can_fulfill', 1)
			->where('sales_flat_shipment.defunct', 0)
			->not_like('sales_flat_shipment.shipping_method', 'external', 'after')
			->group_by('sales_flat_shipment.shipping_method')
			->order_by('sales_flat_shipment.shipping_method');
			
		if(!empty($stock_id)) {
			$redstag_db->where('sales_flat_shipment.stock_id', $stock_id);
		}
		
		$total_shipments_by_shipping_method = $redstag_db->get()->result_array();
		
		if(!empty($total_shipments_by_shipping_method)) {
			foreach($total_shipments_by_shipping_method as $current_data) {
				$shipping_method = strtolower($current_data['shipping_method']);
				if( substr($shipping_method, 0, strlen('fedex')) == 'fedex' ) {
					if( in_array($shipping_method, array('fedex_fedex_ground', 'fedex_ground_home_delivery', 'fedex_smart_post')) ) {
						$data['total_shipments_by_shipping_method']['FedEx'] += $current_data['shipments_count'];
					}
					else {
						$data['total_shipments_by_shipping_method']['FedEx Express'] += $current_data['shipments_count'];
					}
				}
				else if( substr($shipping_method, 0, strlen('ups')) == 'ups' ) {
					if( in_array($shipping_method, array('ups_03', 'ups_sp')) ) {
						$data['total_shipments_by_shipping_method']['UPS'] += $current_data['shipments_count'];
					}
					else {
						$data['total_shipments_by_shipping_method']['UPS Express'] += $current_data['shipments_count'];
					}
				}
				else if( substr($shipping_method, 0, strlen('usps')) == 'usps' ) {
					$data['total_shipments_by_shipping_method']['All USPS'] += $current_data['shipments_count'];
				}
				else if( substr($shipping_method, 0, strlen('lasership')) == 'lasership' ) {
					$data['total_shipments_by_shipping_method']['All LaserShip'] += $current_data['shipments_count'];
				}
				else if( substr($shipping_method, 0, strlen('ontrac')) == 'ontrac' ) {
					$data['total_shipments_by_shipping_method']['All OnTrac'] += $current_data['shipments_count'];
				}
				else if( substr($shipping_method, 0, strlen('amazon')) == 'amazon' ) {
					$data['total_shipments_by_shipping_method']['All Amazon'] += $current_data['shipments_count'];
				}
				else {
					$data['total_shipments_by_shipping_method']['Other'] += $current_data['shipments_count'];
				}
			}
		}
		
		// SHIPMENTS BY HOUR
		$data['hourly_shipments_count_per_minute'] = array();

		$redstag_db
			->select("
				DATE(IF(stock_id IN(3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern'))) AS the_date,
				HOUR(IF(stock_id IN(3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern'))) AS the_hour,
				COUNT(*) AS qty")
			->from('sales_flat_shipment')
			->where('sales_flat_shipment.created_at >=', $start_datetime_in_utc)
			->where('sales_flat_shipment.created_at <', $end_datetime_in_utc)
			->group_by('the_date, the_hour')
			->order_by('the_date, the_hour');
		
		if(!empty($stock_id)) {
			$redstag_db->where('sales_flat_shipment.stock_id', $stock_id);
		}
		
		$hourly_shipments_data = $redstag_db->get()->result_array();
		
		foreach($hourly_shipments_data as $current_data) {
			$the_hour = $current_data['the_date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour']);
			
			$elapsed_mins = 60;
			if($the_hour == date('Y-m-d H:00:00', strtotime($current_local_time))) {
				$elapsed_mins = (strtotime(date('H:i:s')) - strtotime(date('H:00:00'))) / 60;
				if($elapsed_mins == 0) $elapsed_mins = 1;
			}
			
			$data['hourly_shipments_count_per_minute'][$current_data['the_date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])] = round($current_data['qty'] / $elapsed_mins, 2);
		}
		
		
		// ORDERS BY HOUR
		$data['projected_demand'] = 0;
		
		$redstag_db
			->select('DATE(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_date, HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
			->from('sales_flat_order_stock')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_stock.order_id')
			->where('created_at >=', $start_datetime_in_utc)
			->where('created_at <', $end_datetime_in_utc)
			->group_by('the_date, the_hour');

		if(!empty($stock_id)) {
			$redstag_db->where('sales_flat_order_stock.stock_id', $stock_id);
		}
		
		$hourly_orders_data = $redstag_db->get()->result_array();
		
		foreach($hourly_orders_data as $current_data) {
			$data['hourly_orders_count'][$current_data['the_date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])] = $current_data['qty'];
		}
		
		// Past 4 weeks hourly orders
		for($i=1; $i<=4; $i++) {
			$past_start_datetime_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $start_datetime_in_utc));
			$past_end_datetime_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $end_datetime_in_utc));
			
			$redstag_db
				->select('DATE(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_date, HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
				->from('sales_flat_order_stock')
				->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_stock.order_id')
				->where('created_at >=', $past_start_datetime_in_utc)
				->where('created_at <', $past_end_datetime_in_utc)
				->group_by('the_date, the_hour');

			if(!empty($stock_id)) {
				$redstag_db->where('sales_flat_order_stock.stock_id', $stock_id);
			}
			
			$past_hourly_orders_data = $redstag_db->get()->result_array();
			
			foreach($past_hourly_orders_data as $current_data) {
				$the_date = (date('w', strtotime($current_data['the_date'])) == $start_day) ? $data['start_date'] : date('Y-m-d', strtotime('+1 day '.$data['start_date']));
				
				$data['past_hourly_orders_count'][$the_date . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])] += $current_data['qty'];
				$data['projected_demand'] += $current_data['qty'];
			}
		}

		// Take the average of the past 4 weeks projected demand
		$data['projected_demand'] = round($data['projected_demand'] / 4);
		
		foreach($data['past_hourly_orders_count'] as $key => $value) {
			$data['past_hourly_orders_count'][$key] = round($data['past_hourly_orders_count'][$key] / 4);
		}
		
		$tmp_time = date('Y-m-d H:00:00', strtotime($data['start_datetime']));
		while(strtotime($tmp_time) < strtotime($data['end_datetime'])) {
			$data['hourly_completed_shipments_count'][$tmp_time]['is_completed_shipments_less_than_new_orders'] = ($data['hourly_completed_shipments_count'][$tmp_time]['value'] < $data['hourly_orders_count'][$tmp_time]);
			
			$data['hourly_completed_shipments_count'][$tmp_time]['is_completed_shipments_better_than_previous_weeks'] = ($data['hourly_completed_shipments_count'][$tmp_time]['value'] > $data['past_hourly_completed_shipments_count'][$tmp_time]['value']);
			
			$data['hourly_completed_shipments_count'][$tmp_time]['is_completed_shipments_worse_than_previous_weeks'] = ($data['hourly_completed_shipments_count'][$tmp_time]['value'] < $data['past_hourly_completed_shipments_count'][$tmp_time]['value']);
			
			$data['hourly_completed_shipments_count'][$tmp_time]['completed_shipments_diff_compared_to_previous_weeks'] = ($data['hourly_completed_shipments_count'][$tmp_time]['value'] - $data['past_hourly_completed_shipments_count'][$tmp_time]['value']);
			
			$tmp_time = date('Y-m-d H:00:00', strtotime('+1 hour '.$tmp_time));
		}

		$data['hourly_completed_shipments_chart_max_scale'] = max(
			array(
				600,
				ceil((max(array_column($data['hourly_completed_shipments_count'],'value')) * 1.1) / 100) * 100,
				ceil((max(array_column($data['past_hourly_completed_shipments_count'],'value')) * 1.1) / 100) * 100
			)
		);
		
		$max_hourly_shipments_count_per_minute = !empty($data['hourly_shipments_count_per_minute']) ? ceil((max(array_values($data['hourly_shipments_count_per_minute'])) * 1.1) / 10) * 10 : 0;
		
		$data['hourly_completed_shipments_per_minute_chart_max_scale'] = max(
			array(
				ceil((max(array_column($data['hourly_completed_shipments_count'],'value_per_minute')) * 1.1) / 10) * 10,
				ceil((max(array_column($data['past_hourly_completed_shipments_count'],'value_per_minute')) * 1.1) / 10) * 10,
				$max_hourly_shipments_count_per_minute
			)
		);
		$data['hourly_orders_chart_max_scale'] = max(
			ceil((max($data['hourly_orders_count']) * 1.1) / 100) * 100,
			ceil((max($data['past_hourly_orders_count']) * 1.1) / 100) * 100,
			$data['hourly_completed_shipments_chart_max_scale']
		);
		
		// Updated Takt Board Calculation
		
		$data['hours_shift'] = null;
		$data['available_time_per_shift_in_min'] = null;
		$data['break_time_per_shift_in_min'] = null;
		$data['lunch_time_per_shift_in_min'] = null;
		$data['net_available_time_per_day_in_min'] = null;
		$data['net_available_time_per_day_in_sec'] = null;
		$data['takt_time_in_min'] = null;
		$data['takt_time_in_sec'] = null;
		
		$data['takt_time_per_package_in_min'] = null;
		$data['picking_cycle_time_in_min'] = null;
		$data['packing_cycle_time_in_min'] = null;
		$data['loading_cycle_time_in_min'] = null;
		$data['picking_headcount_required'] = null;
		$data['packing_headcount_required'] = null;
		$data['loading_headcount_required'] = null;
		$data['total_value_add_employees'] = null;
		
		$data['num_employees_scheduled'] = null;
		$data['num_employees_needed'] = null;
		$data['projected_hours_worked'] = null;
		$data['projected_order_volume'] = null;
		$data['operational_cost_per_package'] = null;
		$data['fte_cost_per_hour'] = null;
		$data['hours_per_package'] = null;
		$data['cost_per_package'] = null;
		$data['cost_per_package_target'] = null;
		
		// Employee assignments count
		$this->load->model(PROJECT_CODE.'/model_assignment');
		$args = array(
			'date' => date('Y-m-d', strtotime($data['start_datetime'])),
			'facility' => $data['facility']
		);
		$data['employee_assignment_count_by_assignment_type'] = $this->model_assignment->get_employee_assignment_count_by_assignment_type($args);
		
		$takt_data = $this->model_assignment->get_takt_data($args);
		
		$data['num_employees_scheduled'] = isset($takt_data['number_of_employees_scheduled']) ? $takt_data['number_of_employees_scheduled'] : null;
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			
			$default_projected_demand = $data['projected_demand'];
			$default_hours_shift = $facility_data['hours_shift'];
			$default_break_time_per_shift_in_min = $facility_data['break_time_per_shift_in_min'];
			$default_lunch_time_per_shift_in_min = $facility_data['lunch_time_per_shift_in_min'];
			
			if(empty($takt_data)) {
				$this->model_db_crud->add_item(
					'takt_data',
					array(
						'facility' => $data['facility'],
						'date' => date('Y-m-d', strtotime($data['start_datetime'])),
						'projected_demand' => $default_projected_demand,
						'hours_shift' => $default_hours_shift,
						'break_time_per_shift_in_min' => $default_break_time_per_shift_in_min,
						'lunch_time_per_shift_in_min' => $default_lunch_time_per_shift_in_min
					),
					array('changed_by_system'=>true)
				);
			}
			else if(!isset($takt_data['projected_demand']) && !isset($takt_data['hours_shift']) && !isset($takt_data['break_time_per_shift_in_min']) && !isset($takt_data['lunch_time_per_shift_in_min'])) {
				$this->model_db_crud->edit_item(
					'takt_data',
					$takt_data['id'],
					array(
						'projected_demand' => $default_projected_demand,
						'hours_shift' => $default_hours_shift,
						'break_time_per_shift_in_min' => $default_break_time_per_shift_in_min,
						'lunch_time_per_shift_in_min' => $default_lunch_time_per_shift_in_min
					),
					array('changed_by_system'=>true)
				);
			}
			
			$data['projected_demand'] = !empty($takt_data['projected_demand']) ? $takt_data['projected_demand'] : $default_projected_demand;
			$data['hours_shift'] = isset($takt_data['hours_shift']) ? $takt_data['hours_shift'] : $default_hours_shift;
			$data['break_time_per_shift_in_min'] = isset($takt_data['break_time_per_shift_in_min']) ? $takt_data['break_time_per_shift_in_min'] : $default_break_time_per_shift_in_min;
			$data['lunch_time_per_shift_in_min'] = isset($takt_data['lunch_time_per_shift_in_min']) ? $takt_data['lunch_time_per_shift_in_min'] : $default_lunch_time_per_shift_in_min;
			
			$data['packing_cycle_time_in_min'] = $facility_data['packing_cycle_time_in_min'];
			$data['picking_cycle_time_in_min'] = $facility_data['picking_cycle_time_in_min'];
			$data['loading_cycle_time_in_min'] = $facility_data['loading_cycle_time_in_min'];
			$data['operational_cost_per_package'] = $facility_data['operational_cost_per_package'];
			$data['fte_cost_per_hour'] = $facility_data['fte_cost_per_hour'];
			
			$data['available_time_per_shift_in_min'] = (isset($data['hours_shift'])) ? $data['hours_shift'] * 60 : null;
			$data['net_available_time_per_day_in_min'] = (isset($data['available_time_per_shift_in_min']) && isset($data['break_time_per_shift_in_min']) && isset($data['lunch_time_per_shift_in_min'])) ? $data['available_time_per_shift_in_min'] - $data['break_time_per_shift_in_min'] - $data['lunch_time_per_shift_in_min'] - 15 : null; // 15 mins was reduced from the start shift time
			$data['net_available_time_per_day_in_sec'] = isset($data['net_available_time_per_day_in_min']) ? $data['net_available_time_per_day_in_min'] * 60 : null;
			$data['takt_time_in_min'] = !empty($data['net_available_time_per_day_in_min']) ? $data['projected_demand'] / $data['net_available_time_per_day_in_min'] : null;
			$data['takt_time_in_sec'] = !empty($data['net_available_time_per_day_in_sec']) ? $data['projected_demand'] / $data['net_available_time_per_day_in_sec'] : null;
			
			$data['takt_time_per_package_in_min'] = (!empty($data['net_available_time_per_day_in_min']) && !empty($data['projected_demand'])) ? $data['net_available_time_per_day_in_min'] / $data['projected_demand'] : null;
			$data['picking_headcount_required'] = (isset($data['picking_cycle_time_in_min']) && !empty($data['takt_time_per_package_in_min'])) ? $data['picking_cycle_time_in_min'] / $data['takt_time_per_package_in_min'] : null;
			$data['packing_headcount_required'] = (isset($data['packing_cycle_time_in_min']) && !empty($data['takt_time_per_package_in_min'])) ? $data['packing_cycle_time_in_min'] / $data['takt_time_per_package_in_min'] : null;
			$data['loading_headcount_required'] = (isset($data['loading_cycle_time_in_min']) && !empty($data['takt_time_per_package_in_min'])) ? $data['loading_cycle_time_in_min'] / $data['takt_time_per_package_in_min'] : null;
			$data['total_value_add_employees'] = (isset($data['picking_headcount_required']) && isset($data['packing_headcount_required']) && isset($data['loading_headcount_required'])) ? $data['picking_headcount_required'] + $data['packing_headcount_required'] + $data['loading_headcount_required'] : null;
			
			$data['num_employees_needed'] = isset($data['total_value_add_employees']) ? ceil($data['total_value_add_employees']) : null;
			$data['projected_hours_worked'] = (isset($data['num_employees_scheduled']) && isset($data['hours_shift'])) ? $data['num_employees_scheduled'] * $data['hours_shift'] : null;
			$data['projected_order_volume'] = $data['projected_demand'];
			$data['hours_per_package'] = (isset($data['projected_hours_worked']) && !empty($data['projected_order_volume'])) ? $data['projected_hours_worked'] / $data['projected_order_volume'] : null;
			$data['cost_per_package'] = (isset($data['hours_per_package']) && isset($data['operational_cost_per_package']) && isset($data['fte_cost_per_hour'])) ? $data['hours_per_package'] * $data['operational_cost_per_package'] * $data['fte_cost_per_hour'] : null;
			
			$data['cost_per_package_target'] = $facility_data['cost_per_package_target'];
		}
		
		$data['takt_value'] = isset($data['takt_time_in_min']) ? $data['takt_time_in_min'] * 60 : null;
		$data['takt_value_per_minute'] = isset($data['takt_value']) ? $data['takt_value'] / 60 : null;
		
		// Graph Takt Value
		if(!empty($data['takt_value'])) {
			$data['graph_takt_value'] = array();
			$data['graph_takt_value_per_minute'] = array();
			
			$data['completed_shipments_count_difference_to_time'] = 0;
			
			$tmp_time = date('Y-m-d H:00:00', strtotime($data['start_datetime']));
			while(strtotime($tmp_time) < strtotime($data['end_datetime'])) {
				$data['graph_takt_value'][$tmp_time] = 0;
				$data['graph_takt_value_per_minute'][$tmp_time] = 0;
				
				$tmp_time = date('Y-m-d H:00:00', strtotime('+1 hour '.$tmp_time));
			}
			
			foreach($data['graph_takt_value'] as $key => $value) {
				$data['graph_takt_value'][$key] = $data['takt_value'];
			}
			
			// First 15 mins is also considered a break
			$data['break_times'][] = array(
				'start' => date('H:i', strtotime($data['start_datetime'])),
				'end' => date('H:i', strtotime('+15 min '.$data['start_datetime']))
			);
			
			$break_time_in_minutes = array();
			$inactive_mins = array();
			for($i=0; $i<24; $i++) {
				$inactive_mins[sprintf('%02d:00:00',$i)] = 0;
			}
			foreach($data['break_times'] as $break_time) {
				//debug_var($inactive_mins,'start_inactive_mins');
				//debug_var($break_time, 'start_break_time');
				
				if(!empty($break_time['start']) && !empty($break_time['end'])) {
					$max_mins = (strtotime(date('Y-m-d H:00:00', strtotime('+1 hour 2021-01-01 '.$break_time['start']))) - strtotime('2021-01-01 '.$break_time['start']))/60;
					
					$this_break_time = $break_time['start'];
					$break_times_in_mins = (strtotime($break_time['end']) - strtotime($this_break_time)) / 60;
					
					if($break_times_in_mins <= $max_mins) {
						$inactive_mins[date('H:00:00', strtotime($break_time['start']))] += $break_times_in_mins;
					}
					else {
						$inactive_mins[date('H:00:00', strtotime($break_time['start']))] += $max_mins;
						$break_times_in_mins -= $max_mins;
						
						$i = 1;
						while($break_times_in_mins > 0) {
							if($break_times_in_mins < 60) {
								$inactive_mins[date('H:00:00', strtotime('+'.$i.' hour '.$break_time['start']))] += $break_times_in_mins;
							}
							else {
								$inactive_mins[date('H:00:00', strtotime('+'.$i.' hour '.$break_time['start']))] += 60;
							}
							$break_times_in_mins -= 60;
						}
					}
				}
				
				//debug_var($inactive_mins,'end_inactive_mins');
				//debug_var($break_time, 'end_break_time');
			}
			
			foreach($data['graph_takt_value'] as $tmp_time => $value) {
				$the_hour = date('H:00:00', strtotime($tmp_time));
				$active_mins = 60;
				if(!empty($inactive_mins[$the_hour])) {
					$active_mins = 60 - $inactive_mins[$the_hour];
				}
				
				$data['graph_takt_value'][$tmp_time] = round($active_mins * $data['takt_time_in_min']);
				$data['graph_takt_value_per_minute'][$tmp_time] = number_format($data['graph_takt_value'][$tmp_time] / 60, 2);
				
				$data['hourly_completed_shipments_count'][$tmp_time]['completed_shipments_diff_compared_to_takt_value'] = round($data['hourly_completed_shipments_count'][$tmp_time]['value'] - $data['graph_takt_value'][$tmp_time]);
				
				if($data['hourly_completed_shipments_count'][$tmp_time]['value'] > 0) {
					$data['completed_shipments_count_difference_to_time'] += $data['hourly_completed_shipments_count'][$tmp_time]['completed_shipments_diff_compared_to_takt_value'];
				}
				
			}
		}
		
		if(!empty($data['num_employees_scheduled']) && !empty($data['operational_cost_per_package']) && !empty($data['fte_cost_per_hour'])) {
			$tmp_time = date('Y-m-d H:00:00', strtotime($data['start_datetime']));
			while(strtotime($tmp_time) < strtotime($data['end_datetime'])) {
				$data['hourly_completed_shipments_count'][$tmp_time]['cost_per_package'] = ($data['hourly_completed_shipments_count'][$tmp_time]['value'] > 0) ? number_format($data['num_employees_scheduled'] / $data['hourly_completed_shipments_count'][$tmp_time]['value'] * $data['operational_cost_per_package'] * $data['fte_cost_per_hour'], 2) : 0;
				
				$data['hourly_completed_shipments_count'][$tmp_time]['total_cost'] = number_format($data['hourly_completed_shipments_count'][$tmp_time]['cost_per_package'] * $data['hourly_completed_shipments_count'][$tmp_time]['value'], 2);
				
				$tmp_time = date('Y-m-d H:00:00', strtotime('+1 hour '.$tmp_time));
			}
		}
		
		// Hourly Hours Per Package
		
		// First, we need to find out the num_employees
		
		$num_employees_data = $redstag_db
			->select("
				DATE(IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern'))) AS start_date,
				HOUR(IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern'))) AS start_hour,
				COUNT(DISTINCT name) AS num_employees", false)
			->from('action_log')
			->join('admin_user', 'admin_user.user_id = action_log.user_id')
			->where_in('action', array('pick','pack','load'))
			->where('started_at >=', $start_datetime_in_utc)
			->where('started_at <', $end_datetime_in_utc)
			->where('stock_id', $stock_id)
			->group_by('start_date, start_hour')
			->get()->result_array();
		
		if(!empty($num_employees_data)) {
			foreach($num_employees_data as $current_data) {
				$data['hourly_completed_shipments_count'][$current_data['start_date'] . ' ' . sprintf('%02d:00:00', $current_data['start_hour'])]['num_employees'] = $current_data['num_employees'];
			}
		}
		
		$tmp_time = date('Y-m-d H:00:00', strtotime($data['start_datetime']));
		while(strtotime($tmp_time) < strtotime($data['end_datetime'])) {
			if(!isset($data['hourly_completed_shipments_count'][$tmp_time]['num_employees'])) {
				$data['hourly_completed_shipments_count'][$tmp_time]['num_employees'] = 0;
			}
			
			$data['hourly_completed_shipments_count'][$tmp_time]['hour_per_package'] = $data['hourly_completed_shipments_count'][$tmp_time]['value'] > 0 ? number_format($data['hourly_completed_shipments_count'][$tmp_time]['num_employees'] / $data['hourly_completed_shipments_count'][$tmp_time]['value'], 2) : 0;
			
			unset($data['hourly_completed_shipments_count'][$tmp_time]['employee']);
			
			$tmp_time = date('Y-m-d H:00:00', strtotime('+1 hour '.$tmp_time));
		}
		
		// Filter custom start time
		/*$start_hour = intval(date('H', strtotime($data['custom_start_time'])));
		for($i=0; $i < $start_hour; $i++) {
			unset($data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['hourly_completed_orders_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $i)]);
			unset($data['hourly_shipments_count_per_minute'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['past_hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $i)]);
		}*/
		
		$data['hourly_cost_per_package_chart_max_scale'] = !empty(array_column($data['hourly_completed_shipments_count'],'cost_per_package')) ? max(
			array(
				ceil((max(array_column($data['hourly_completed_shipments_count'],'cost_per_package')) * 1.1) / 10) * 10,
			)
		) : 10;
		
		$data['hourly_total_cost_chart_max_scale'] = !empty(array_column($data['hourly_completed_shipments_count'],'total_cost')) ? max(
			array(
				ceil((max(array_column($data['hourly_completed_shipments_count'],'total_cost')) * 1.1) / 100) * 100,
			)
		) : 100;
		
		$data['hourly_hour_per_package_chart_max_scale'] = !empty(array_column($data['hourly_completed_shipments_count'],'hour_per_package')) ? max(
			array(
				(max(array_column($data['hourly_completed_shipments_count'],'hour_per_package')) * 1.1),
			)
		) : 1;
		
		$data['date'] = date('Y-m-d', strtotime($data['start_datetime']));
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['cost_calculation_section_html'] = $this->load->view(PROJECT_CODE.'/view_takt_board_cost_calculation_section', $data, true);
		$data['block_times_section_html'] = $this->load->view(PROJECT_CODE.'/view_takt_board_block_times_section', $data, true);
		$data['completed_shipment_section_html'] = $this->load->view(PROJECT_CODE.'/view_takt_board_completed_shipment_section', $data, true);
		$data['graph_section_html'] = $this->load->view(PROJECT_CODE.'/view_takt_board_graph_section', $data, true);
		$data['js_graph_section_html'] = $this->load->view(PROJECT_CODE.'/js_view_takt_board_graph_section', $data, true);
	
		return $data;
	}
	
	public function get_metrics_board_data($data) {
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$data['evolution_goals'] = !empty($facility_data) ? $facility_data['evolution_goals'] : 10000000;
		$data['evolution_points_data'] = $this->get_evolution_points_data($data);
		
		$scoreboard = array();

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
			
			foreach($data['block_time_list'] as $this_block_time) {
				$users[$user['employee_name']]['assignment_types'][$this_block_time['id']] = array();
				$users[$user['employee_name']]['assignment_type_names'][$this_block_time['id']] = array();
			}
		}
		
		$this->db
			->select('assignments.employee, employees.employee_name, assignments.assignment_type, assignments.shift, assignment_types.assignment_type_name')
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
				
				foreach($data['block_time_list'] as $this_block_time) {
					$users[$asg['employee_name']]['assignment_types'][$this_block_time['id']] = array();
					$users[$asg['employee_name']]['assignment_type_names'][$this_block_time['id']] = array();
				}
			}
			
			if(!in_array($asg['assignment_type'], $users[$asg['employee_name']]['assignment_types'][$asg['shift']])) {
				$users[$asg['employee_name']]['assignment_types'][$asg['shift']][] = $asg['assignment_type'];
			}
			
			if(!in_array($asg['assignment_type_name'], $users[$asg['employee_name']]['assignment_type_names'][$asg['shift']])) {
				$users[$asg['employee_name']]['assignment_type_names'][$asg['shift']][] = $asg['assignment_type_name'];
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
						$current_block_time = $this->get_block_time_by_time($start, $data['block_time_list']);
						
						if((empty($data['type']) || in_array($type, $data['type'])) && (empty($data['action']) || in_array($action, $data['action'])) && (empty($data['time_from']) || $start >= $data['time_from']) && (empty($data['time_to']) || $start <= $data['time_to'])) {
							if(!isset($scoreboard[$current_block_time][$action][$user])) {
								$scoreboard[$current_block_time][$action][$user] = array(
									'qty' => 0,
									'sum_of_time' => 0
								);
							}
							
							if(!isset($scoreboard['total'][$action][$user])) {
								$scoreboard['total'][$action][$user] = array(
									'qty' => 0,
									'sum_of_time' => 0
								);
							}
							
							$scoreboard[$current_block_time][$action][$user]['qty']++;
							$scoreboard['total'][$action][$user]['qty']++;
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
					
					if(!empty($status) && !empty($user) && !empty($duration) && in_array($status, $data['status_shortlist'])) {
						$current_block_time = $this->get_block_time_by_time($start, $data['block_time_list']);
						
						$action = $data['status_list'][$status];

						if(
							(empty($data['action']) || in_array($action, $data['action']))
							&& (empty($data['time_from']) || $start >= $data['time_from'])
							&& (empty($data['time_to']) || $start <= $data['time_to'])) {
							if(!isset($scoreboard[$current_block_time][$action][$user])) {
								$scoreboard[$current_block_time][$action][$user] = array(
									'qty' => 0,
									'sum_of_time' => 0
								);
							}
							
							if(!isset($scoreboard['total'][$action][$user])) {
								$scoreboard['total'][$action][$user] = array(
									'qty' => 0,
									'sum_of_time' => 0
								);
							}
							
							sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
							$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
							$scoreboard[$current_block_time][$action][$user]['sum_of_time'] += $time_seconds;
							$scoreboard['total'][$action][$user]['sum_of_time'] += $time_seconds;
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		foreach($scoreboard as $block_time => $block_time_scoreboard) {
			foreach($block_time_scoreboard as $action => $action_scoreboard) {
				foreach($action_scoreboard as $user => $user_data) {
					if(!array_key_exists($user, $users) &&
						(!empty($data['facility']) || !empty($data['department']) || !empty($data['assignment_type']) || !empty($data['employee_shift_type']))
					) {
						unset($scoreboard[$block_time][$action][$user]);
						continue;
					}
					
					// Filter user based on their facility
					if(!empty($data['facility']) && array_key_exists($user, $users) && $users[$user]['facility'] <> $data['facility']) {
						unset($scoreboard[$block_time][$action][$user]);
						continue;
					}
					
					// Filter user based on their department
					if(!empty($data['department']) && array_key_exists($user, $users) && $users[$user]['department'] <> $data['department']) {
						unset($scoreboard[$block_time][$action][$user]);
						continue;
					}
					
					// Filter user based on their assignment
					if((!empty($data['assignment_type'])) 
						&& array_key_exists($user, $users) && empty($users[$user]['assignment_types'])) {
						unset($scoreboard[$block_time][$action][$user]);
						continue;
					}
					
					// Filter user based on their shift
					if((!empty($data['employee_shift_type'])) 
						&& array_key_exists($user, $users) && !in_array($users[$user]['employee_shift'], $data['employee_shift_type'])) {
						unset($scoreboard[$block_time][$action][$user]);
						continue;
					}
					
					$scoreboard[$block_time][$action][$user]['sum_of_time_in_hours'] = $scoreboard[$block_time][$action][$user]['sum_of_time'] / 3600;
					
					$scoreboard[$block_time][$action][$user]['average'] = ($scoreboard[$block_time][$action][$user]['sum_of_time_in_hours'] > 0) ? $scoreboard[$block_time][$action][$user]['qty'] / $scoreboard[$block_time][$action][$user]['sum_of_time_in_hours'] : 0;
					
					$scoreboard[$block_time][$action][$user]['formatted_sum_of_time'] = sprintf('%02d', floor($scoreboard[$block_time][$action][$user]['sum_of_time'] / 3600)) . ':' . sprintf('%02d', floor(($scoreboard[$block_time][$action][$user]['sum_of_time'] % 3600) / 60)) . ':' . sprintf('%02d', $scoreboard[$block_time][$action][$user]['sum_of_time'] % 60);
					
					$average_time_in_secs = $scoreboard[$block_time][$action][$user]['average'] * 3600;
					
					$scoreboard[$block_time][$action][$user]['formatted_average'] = number_format($scoreboard[$block_time][$action][$user]['average'], 2);
					
					if($block_time <> 'total') {
						$scoreboard[$block_time][$action][$user]['assignment'] = isset($users[$user]) ? implode(', ', $users[$user]['assignment_type_names'][$block_time]) : null;
					}
				}
			}
		}
		
		$data['scoreboard'] = $scoreboard;
		
		// Get attendance data
		$attendance = $this->db
			->select('employee, COUNT(*) AS total_attendance')
			->from('attendance')
			->where('attendance.data_status', DATA_ACTIVE)
			->where('attendance.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->group_by('employee')
			->get()->result_array();
		
		$data['total_attendance'] = array();
		
		foreach($attendance as $atd) {
			$data['total_attendance'][$atd['employee']] = $atd['total_attendance'];
		}
		
		// Get rewards data
		$rewards = $this->db
			->select('employee, COUNT(*) AS total_rewards')
			->from('rewards')
			->where('rewards.data_status', DATA_ACTIVE)
			->where('rewards.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->group_by('employee')
			->get()->result_array();
		
		$data['total_rewards'] = array();
		
		foreach($rewards as $reward) {
			$data['total_rewards'][$reward['employee']] = $reward['total_rewards'];
		}
		
		// Get metrics board note
		$metrics_board_note_data = $this->db
			->select('note_content')
			->from('metrics_board_notes')
			->where('metrics_board_notes.data_status', DATA_ACTIVE)
			->where('metrics_board_notes.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->order_by('datetime', 'desc')
			->limit(1)
			->get()->result_array();
		
		$data['metrics_board_note'] = !empty($metrics_board_note_data) ? $metrics_board_note_data[0]['note_content'] : null;
		
		$data['evolution_points_leaderboard_html'] = $this->load->view(PROJECT_CODE.'/view_metrics_board_evolution_points_leaderboard', $data, true);
		
		return $data;
	}
	
	public function get_evolution_points_data($args) {
		$result = array();
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		
		$this->refresh_evolution_points();
		
		$this->db
			->select('employees.id AS employee_id, employee_name, SUM(evolution_points) AS lifetime_evolution_points')
			->from('evolution_points_logs')
			->join('employees', 'employees.id = evolution_points_logs.employee', 'right')
			->where('evolution_points_logs.data_status', DATA_ACTIVE)
			->where('evolution_points_logs.data_group', $user_group)
			->where('employees.data_status', DATA_ACTIVE)
			->where('employees.data_group', $user_group)
			->where('employees.is_active', true)
			->group_by('employees.id')
			->order_by('lifetime_evolution_points', 'desc');
		
		if(!empty($args['facility'])) {
			$this->db->where('employees.facility', $args['facility']);
		}
		
		if(!empty($args['department'])) {
			$this->db->where('employees.department', $args['department']);
		}
		
		if(!empty($args['employee_shift_type'])) {
			$this->db->where_in('employees.employee_shift', $args['employee_shift_type']);
		}
		
		$lifetime_evolution_points_data = $this->db->get()->result_array();
			
		$this->db
			->select('employees.id AS employee_id, employee_name, SUM(evolution_points) AS daily_evolution_points')
			->from('evolution_points_logs')
			->join('employees', 'employees.id = evolution_points_logs.employee', 'right')
			->where('evolution_points_logs.data_status', DATA_ACTIVE)
			->where('evolution_points_logs.data_group', $user_group)
			->where('employees.data_status', DATA_ACTIVE)
			->where('employees.data_group', $user_group)
			->where('employees.is_active', true)
			->like('datetime', $args['date'])
			->group_by('employees.id')
			->order_by('daily_evolution_points', 'desc');
			
		if(!empty($args['facility'])) {
			$this->db->where('employees.facility', $args['facility']);
		}
		
		if(!empty($args['department'])) {
			$this->db->where('employees.department', $args['department']);
		}
		
		if(!empty($args['employee_shift_type'])) {
			$this->db->where_in('employees.employee_shift', $args['employee_shift_type']);
		}
		
		$daily_evolution_points_data = $this->db->get()->result_array();
		
		$evolution_points_data = array();
		
		$evolution_goals = 0;
		if(!empty($args['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $args['facility']);
			
			if(!empty($facility_data) && !empty($facility_data['evolution_goals'])) {
				$evolution_goals = $facility_data['evolution_goals'];
			}
		}
		
		foreach($lifetime_evolution_points_data as $item) {
			$evolution_points_data[$item['employee_id']] = $item;
			$evolution_points_data[$item['employee_id']]['daily_evolution_points'] = 0;
			
			if(!empty($evolution_goals)) {
				switch(true) {
					case $item['lifetime_evolution_points'] >= 4 * $evolution_goals:
						$evolution_points_data[$item['employee_id']]['evolution_status'] = 'purple';
						break;
					case $item['lifetime_evolution_points'] >= 3 * $evolution_goals:
						$evolution_points_data[$item['employee_id']]['evolution_status'] = 'gold';
						break;
					case $item['lifetime_evolution_points'] >= 2 * $evolution_goals:
						$evolution_points_data[$item['employee_id']]['evolution_status'] = 'silver';
						break;
					case $item['lifetime_evolution_points'] >= $evolution_goals:
						$evolution_points_data[$item['employee_id']]['evolution_status'] = 'bronze';
						break;
					default:
						$evolution_points_data[$item['employee_id']]['evolution_status'] = 'normal';
				}
			}
			else {
				$evolution_points_data[$item['employee_id']]['evolution_status'] = 'normal';
			}
		}
		
		foreach($daily_evolution_points_data as $item) {
			$evolution_points_data[$item['employee_id']]['daily_evolution_points'] = $item['daily_evolution_points'];
		}
		
		$result = $evolution_points_data;
		
		return $result;
	}
	
	public function refresh_evolution_points() {
		$start_date = '2019-11-01';
		
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');

		// Search for the latest updated date in evolution_points_log table with reason type "work"
		$latest_date_data = $this->db
			->select_max('datetime', 'latest_date')
			->from('evolution_points_logs')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where('reason_type', 'work')
			->get()->result_array();

		$latest_date = $latest_date_data[0]['latest_date'] > $start_date ? date('Y-m-d', strtotime($latest_date_data[0]['latest_date'])) : $start_date;
		
		$today = date('Y-m-d');
		
		for($date = $latest_date; $date <= $today; $date = date('Y-m-d', strtotime('+1 day '.$date))) {
			$this->update_work_evolution_points($date);
		}
	}
	
	public function update_work_evolution_points($date) {
		$this->load->model('model_db_crud');
		
		$block_time_list = $this->model_db_crud->get_data(
			'block_times', 
			array(
				'select' => array('id', 'block_time_name', 'start_time', 'end_time'),
				'order_by' => array('block_time_name' => 'asc')
			)
		);
		
		$employee_data = $this->model_db_crud->get_several_data('employee');
		
		$employee_id_by_name = array();
		$evolution_points_data = array();
		foreach($employee_data as $employee) {
			$employee_id_by_name[$employee['employee_name']] = $employee['id'];
			$evolution_points_data[$employee['id']] = array();
			
			foreach($block_time_list as $block_time) {
				$evolution_points_data[$employee['id']][$block_time['id']] = 0;
			}
		}
		
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		
		$existing_evolution_points_data = $this->db
			->select('*')
			->from('evolution_points_logs')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where('reason_type', 'work')
			->like('datetime', $date)
			->get()->result_array();
		$existing_evolution_points_log_id = array();
		foreach($existing_evolution_points_data as $item) {
			if(isset($item['block_time']) && isset($item['work_action_type'])) {
				$existing_evolution_points_log_id[$item['employee']][$item['block_time']][$item['work_action_type']] = $item['id'];
			}
		}
		
		$employee_assignments_tmp = $this->db
			->select('employee, shift, assignment_type, evolution_point_factor')
			->from('assignments')
			->join('assignment_types', 'assignment_types.id = assignments.assignment_type')
			->where('assignments.data_status', DATA_ACTIVE)
			->where('assignments.data_group', $user_group)
			->like('assignments.date', $date)
			->get()->result_array();
		
		$employee_assignments = array();
		foreach($employee_assignments_tmp as $asg) {
			$employee_assignments[$asg['employee']][$asg['shift']] = array(
				'assignment_type' => $asg['assignment_type'],
				'evolution_point_factor' => $asg['evolution_point_factor']
			);
		}
		
		$action_shortlist = array('load', 'picking', 'packing');
		$status_shortlist = array('Loading', 'Picking', 'Packing');
		
		$grid_filter = 'started_at[from]='.date('m/j/Y', strtotime($date)).'&started_at[to]='.date('m/j/Y', strtotime($date)).'&started_at[locale]=en_US&finished_at[locale]=en_US';
		
		$grid_filter = base64_encode(str_replace(array('%3D', '%26'), array('=','&'), urlencode($grid_filter)));
		
		$action_log_api_url = 'https://wms.redstagfulfillment.com/automationv1.php?action=grid&auth_key=4CJeQNbMeiWuH7D782xmctOgbZBwPT4e&grid_type=action_log&grid_format=csv&grid_filter='.$grid_filter;
		
		$staff_time_log_api_url = 'https://wms.redstagfulfillment.com/automationv1.php?action=grid&auth_key=4CJeQNbMeiWuH7D782xmctOgbZBwPT4e&grid_type=staff_time_log&grid_format=csv&grid_filter='.$grid_filter;
		
		// Count Qty from Action Log
		if (($handle = fopen($action_log_api_url, "r")) !== FALSE) {
			$row = 1;
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$action = strtolower($csv_data[2]);
					$employee_name = $csv_data[4];
					$start = substr($csv_data[5],-8);
					$hour = intval(substr($start,0,2));
					
					$current_block_time = $this->get_block_time_by_time($start, $block_time_list);
					
					if(!isset($employee_id_by_name[$employee_name])) {
						$add_employee = $this->model_db_crud->add_item(
							'employees',
							array('employee_name' => $employee_name)
						);
						
						$employee_id_by_name[$employee_name] = $add_employee['insert_id'];
						$evolution_points_data[$add_employee['insert_id']] = array();
					}
					
					if(!empty($employee_name) && in_array($action, $action_shortlist) && isset($employee_id_by_name[$employee_name])) {
						if(isset($evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action]['qty'])) {
							$evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action]['qty']++;
						}
						else {
							if(empty($evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time])) {
								$evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time] = array();
							}
							
							$evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action] = array('qty' => 1);
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		$action_map_from_status = array('Loading' => 'load', 'Picking' => 'picking', 'Packing' => 'packing');
		
		// Count Sum of Time from Staff Time Log
		if (($handle = fopen($staff_time_log_api_url, "r")) !== FALSE) {
			$row = 1;
			while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if($row > 1) {
					$status = $csv_data[1];
					$employee_name = $csv_data[2];
					$start = substr($csv_data[4],-8);
					$duration = $csv_data[6];
					
					if(!isset($employee_id_by_name[$employee_name])) {
						continue;
					}
					
					if(!empty($status) && !empty($employee_name) && !empty($duration) && in_array($status, $status_shortlist)) {
						$current_block_time = $this->get_block_time_by_time($start, $block_time_list);
						
						$action = $action_map_from_status[$status];
						
						sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);
						$time_seconds = isset($hours) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;
						
						if(isset($evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action]['sum_of_time'])) {
							$evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action]['sum_of_time'] += $time_seconds;
						}
						else {
							if(isset($evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action]['qty'])) {
								$evolution_points_data[$employee_id_by_name[$employee_name]][$current_block_time][$action]['sum_of_time'] = $time_seconds;
							}
						}
					}
				}

				$row++;
			}
			fclose($handle);
		}
		
		$this->db->trans_start();
		
		$new_evolution_points_logs = array();
		$updated_evolution_points_logs = array();
		$now = date('Y-m-d H:i:s');
		$user_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
		
		foreach($evolution_points_data as $employee_id => $all_block_times_evolution_points) {
			foreach($all_block_times_evolution_points as $block_time => $all_action_types_evolution_points) {
				if(empty($all_action_types_evolution_points)) continue;
				
				foreach($all_action_types_evolution_points as $action_type => $evolution_points) {
					if($evolution_points['qty'] > 0) {
						$evolution_points['sum_of_time'] = !empty($evolution_points['sum_of_time']) ? $evolution_points['sum_of_time'] : 0;
						
						$evolution_points['sum_of_time_in_hours'] = !empty($evolution_points['sum_of_time']) ? $evolution_points['sum_of_time'] / 3600 : 0;
						
						$evolution_points['average'] = ($evolution_points['sum_of_time_in_hours'] > 0) ? $evolution_points['qty'] / $evolution_points['sum_of_time_in_hours'] : 0;
						
						$is_counted = 1;
						if(($action_type == 'picking' && $evolution_points['average'] < 50) || ($action_type == 'packing' && $evolution_points['average'] < 25)) {
							$is_counted = 0;
						}
						
						$evolution_point_factor = isset($employee_assignments[$employee_id][$block_time]['evolution_point_factor']) ? $employee_assignments[$employee_id][$block_time]['evolution_point_factor'] : 1;
						
						$ref_assignment_type = isset($employee_assignments[$employee_id][$block_time]['assignment_type']) ? $employee_assignments[$employee_id][$block_time]['assignment_type'] : null;
						
						if(isset($existing_evolution_points_log_id[$employee_id][$block_time][$action_type])) {
							$updated_evolution_points_logs[] = array(
								'id' => $existing_evolution_points_log_id[$employee_id][$block_time][$action_type],
								'base_evolution_points' => $evolution_points['qty'],
								'ref_assignment_type' => $ref_assignment_type,
								'avg' => $evolution_points['average'],
								'labor_time_in_secs' => $evolution_points['sum_of_time'],
								'evolution_point_factor' => $evolution_point_factor,
								'is_counted' => $is_counted,
								'evolution_points' => $evolution_points['qty'] * $evolution_point_factor * $is_counted,
								'last_modified_time' => $now,
								'last_modified_user' => $user_id
							);
						}
						else {
							$new_evolution_points_logs[] = array(
								'employee' => $employee_id,
								'datetime' => $date,
								'reason_type' => 'work',
								'work_action_type' => $action_type,
								'block_time' => $block_time,
								'ref_assignment_type' => $ref_assignment_type,
								'avg' => $evolution_points['average'],
								'labor_time_in_secs' => $evolution_points['sum_of_time'],
								'base_evolution_points' => $evolution_points['qty'],
								'evolution_point_factor' => $evolution_point_factor,
								'is_counted' => $is_counted,
								'evolution_points' => $evolution_points['qty'] * $evolution_point_factor * $is_counted,
								'data_status' => DATA_ACTIVE,
								'data_group' => $user_group,
								'created_time' => $now,
								'created_user' => $user_id,
								'last_modified_time' => $now,
								'last_modified_user' => $user_id
							);
						}
					}
				}
			}
		}
		
		if(!empty($new_evolution_points_logs)) {
			$this->db->insert_batch('evolution_points_logs', $new_evolution_points_logs);
		}
		
		if(!empty($updated_evolution_points_logs)) {
			$this->db->update_batch('evolution_points_logs', $updated_evolution_points_logs, 'id');
		}
		
		$this->db->trans_complete();
	}
	
	public function get_block_time_by_time($time, $block_times) {
		$result = $block_times[0]['id'];
		
		foreach($block_times as $block_time) {
			if($time >= $block_time['start_time']) {
				$result = $block_time['id'];
			}
		}
		
		return $result;
	}
	
	public function set_metrics_board_note($args) {
		$result = array();

		$this->load->model('model_db_crud');
		
		$latest_metrics_board_note = $this->db
			->select('metrics_board_notes.note_content')
			->from('metrics_board_notes')
			->where('metrics_board_notes.data_status', 'active')
			->where('metrics_board_notes.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->order_by('metrics_board_notes.datetime', 'desc')
			->limit(1)
			->get()->result_array();
			
		if(empty($latest_metrics_board_note) || $latest_metrics_board_note[0]['note_content'] <> $args['note_content']) {
			$add_metrics_board_note = $this->model_db_crud->add_item(
				'metrics_board_notes',
				array(
					'datetime' => date('Y-m-d H:i:s'),
					'note_content' => $args['note_content']
				)
			);
		}
			
		$result['metrics_board_note'] = nl2br($args['note_content']);
		
		$result['success'] = true;
		
		return $result;
	}
	
	public function get_store_list() {
		$redstag_db = $this->load->database('redstag', TRUE);
		$store_list = $redstag_db->select('store_id, name')->from('core_store')->order_by('name')->get()->result_array();
		return $store_list;
	}
}