<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Ups\Tracking;

class Model_package_status_board extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_customer_list() {
		$prod_db = $this->load->database('prod', TRUE);
		
		$customers = $prod_db
			->select('store_id AS customer_id, store_name AS customer_name', false)
			->from('packages')
			->group_by('store_id, store_name')
			->order_by('store_name')
			->get()->result_array();
		
		return $customers;
	}
	
	public function get_package_status_board_data($data) {
		$prod_db = $this->load->database('prod', TRUE);
			
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		/* Some stores are excluded because they shipped their packages using their own account and not using RSF Main Account */
		$excluded_store_ids = array(
			261, // Bidetking
			317, // WS Distribution
			309, // My Pet Chicken
			277, 300, // ToiletTree
			// 264, // Rocker
			153, // FireResQ
			2, 3 // LGDC
		);
		
		$prod_db
			->select(
				"package_id,
				store_name AS customer_name,
				package_created_at_local AS local_package_created_time,
				order_increment_id AS order_number,
				carrier_code,
				shipping_method,
				track_number,
				status,
				DATE(expected_delivery_date) AS expected_delivery_date,
				DATE(carrier_eta) AS carrier_eta,
				DATE(actual_delivery_date) AS actual_delivery_date,
				last_checked_at", false)
			->from('packages')
			->where("package_created_at_local >=", $period_from)
			->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->where_not_in('store_id', $excluded_store_ids)
			->order_by('package_created_at_local', 'desc')
			->limit(10000);
		
		if(!empty($data['customer'])) {
			$prod_db->where('store_id', $data['customer']);
		}
		
		if(!empty($data['carrier'])) {
			$prod_db->where_in('carrier_code', $data['carrier']);
		}
		
		if(!empty($data['shipping_method'])) {
			$prod_db->where_in('shipping_method', $data['shipping_method']);
		}
		
		if(!empty($data['stock_ids'])) {
			$prod_db->where_in('stock_id', $data['stock_ids']);
		}
		
		if(!empty($data['track_number'])) {
			$prod_db->where('track_number', $data['track_number']);
		}
		
		if(!empty($data['is_delivered'])) {
			if($data['is_delivered'] == 'yes') {
				//$prod_db->like('status', 'Delivered');
				$prod_db->where('actual_delivery_date IS NOT NULL', null, false);
			}
			else if($data['is_delivered'] == 'no') {
				/*$prod_db->group_start()
					->where('status IS NULL', null, false)
					->or_not_like('status', 'Delivered')
					->group_end();*/
				$prod_db->where('actual_delivery_date IS NULL', null, false);
			}
		}
		
		if(!empty($data['is_late'])) {
			if($data['is_late'] == 'yes') {
				$prod_db->group_start()
						->group_start()
							->like('status', 'Delivered')
							->where('actual_delivery_date IS NOT NULL', null, false)
							->where('expected_delivery_date IS NOT NULL', null, false)
							->where('DATE(actual_delivery_date) > DATE(expected_delivery_date)', null, false)
						->group_end()
						->or_group_start()
							/*->group_start()
								->where('status IS NULL', null, false)
								->or_not_like('status', 'Delivered')
							->group_end()*/
							->where('actual_delivery_date IS NULL', null, false)
							->where('expected_delivery_date IS NOT NULL', null, false)
							->where('CURDATE() > DATE(expected_delivery_date)', null, false)
						->group_end()
					->group_end();
			}
			else if($data['is_late'] == 'no') {
				$prod_db->group_start()
					->where('actual_delivery_date IS NULL', null, false)
					->or_where('expected_delivery_date IS NULL', null, false)
					->or_where('DATE(actual_delivery_date) <= DATE(expected_delivery_date)', null, false)
					->group_end();
			}
		}

		$package_status_board_data = $prod_db->get()->result_array();
		
		$need_updated_package_ids = array();
		$packages_to_update = array();
		
		// Last check threshold: The data before this time needs to check again
		$last_checked_at_threshold = strtotime('-1 hour');
		
		$data['row_index_by_package_id'] = array();
		
		$delivered_packages_track_numbers = array();
		
		foreach($package_status_board_data as $key => $current_data) {
			$data['row_index_by_package_id'][$current_data['package_id']] = $key;
			
			$late_days = null;
			$package_status_board_data[$key]['is_delivered'] = !empty($current_data['actual_delivery_date']); //(strpos($current_data['status'], 'Delivered') !== false);

			$late_days = null;
			
			if($package_status_board_data[$key]['is_delivered']) {
				if(!empty($current_data['expected_delivery_date']) && !empty($current_data['actual_delivery_date'])) {
					$expected_delivery_date = date('Y-m-d', strtotime($current_data['expected_delivery_date']));
					$actual_delivery_date = date('Y-m-d', strtotime($current_data['actual_delivery_date']));
					
					if(strtotime($actual_delivery_date) > strtotime($expected_delivery_date)) {
						$late_days = floor((strtotime($actual_delivery_date) - strtotime($current_data['expected_delivery_date'])) / 86400);
					}
				}
				
				if($current_data['carrier_code'] == 'fedex') {
					$delivered_packages_track_numbers[] = $current_data['track_number'];
				}
			}
			else {
				if(!empty($current_data['expected_delivery_date'])) {
					$expected_delivery_date = date('Y-m-d', strtotime($current_data['expected_delivery_date']));
					$current_date = date('Y-m-d');
					
					if(strtotime($current_date) > strtotime($expected_delivery_date)) {
						$late_days = floor((strtotime($current_date) - strtotime($current_data['expected_delivery_date'])) / 86400);
					}
				}
			}
			
			$package_status_board_data[$key]['late_days'] = $late_days;
			
			$package_status_board_data[$key]['color'] = 'normal';
			
			if(strpos(strtolower($current_data['status']), 'delivery exception') !== false) {
				$package_status_board_data[$key]['color'] = 'yellow';
			}
			else if(!empty($package_status_board_data[$key]['late_days'])) {
				$package_status_board_data[$key]['color'] = 'red';
			}
			else if($package_status_board_data[$key]['is_delivered']) {
				$package_status_board_data[$key]['color'] = 'green';
			}
			
			if(strtotime($current_data['last_checked_at']) < $last_checked_at_threshold) {
				$packages_to_update[] = array(
					'package_id' => $current_data['package_id'],
					'last_checked_at' => $current_data['last_checked_at']
				);
			}
		}
		
		$data['delivered_packages_track_numbers'] = array_chunk($delivered_packages_track_numbers, 100);
		
		usort($packages_to_update, function($a, $b) {return strtotime($b['last_checked_at']) - strtotime($a['last_checked_at']);});
		
		$need_updated_package_ids = array_column($packages_to_update, 'package_id');
		
		$data['need_updated_package_ids'] = $need_updated_package_ids;
		$data['package_status_board_data'] = $package_status_board_data;
		
		if($data['report_type'] == 'zone_and_state') {
			// All packages by zone
			$prod_db
				->select('zone, COUNT(*) AS total')
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				->where_not_in('store_id', $excluded_store_ids)
				->group_by('zone')
				->order_by('total', 'desc');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			$all_packages_count_by_zone = $prod_db->get()->result_array();
			
			$packages_info_by_zone = array();
			if(!empty($all_packages_count_by_zone)) {
				foreach($all_packages_count_by_zone as $current_data) {
					$zone = !empty($current_data['zone']) ? $current_data['zone'] : 'N/A';
					$packages_info_by_zone[$zone] = array(
						'all_packages' => $current_data['total'],
						'late_packages' => 0
					);
				}
			}
			
			// Late packages by zone
			$prod_db
				->select('zone, COUNT(*) AS total')
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				->where_not_in('store_id', $excluded_store_ids)
				->group_start()
					->where('DATE(actual_delivery_date) > expected_delivery_date', null, false)
					->or_group_start()
						->where('actual_delivery_date IS NULL', null, false)
						->where('expected_delivery_date IS NOT NULL', null, false)
						->where('expected_delivery_date <', date('Y-m-d'))
					->group_end()
				->group_end()
				->group_by('zone')
				->order_by('total', 'desc');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			$late_packages_count_by_zone = $prod_db->get()->result_array();
			
			if(!empty($late_packages_count_by_zone)) {
				foreach($late_packages_count_by_zone as $current_data) {
					$zone = !empty($current_data['zone']) ? $current_data['zone'] : 'N/A';
					$packages_info_by_zone[$zone]['late_packages'] = $current_data['total'];
				}
			}
			
			// All packages by state
			$prod_db
				->select('state, COUNT(*) AS total')
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				->where_not_in('store_id', $excluded_store_ids)
				->group_by('state')
				->order_by('total', 'desc');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			$all_packages_count_by_state = $prod_db->get()->result_array();
			
			$packages_info_by_state = array();
			if(!empty($all_packages_count_by_state)) {
				foreach($all_packages_count_by_state as $current_data) {
					$state = !empty($current_data['state']) ? $current_data['state'] : 'N/A';
					$packages_info_by_state[$state] = array(
						'all_packages' => $current_data['total'],
						'late_packages' => 0
					);
				}
			}
			
			// Late packages by state
			$prod_db
				->select('state, COUNT(*) AS total')
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				->where_not_in('store_id', $excluded_store_ids)
				->group_start()
					->where('DATE(actual_delivery_date) > expected_delivery_date', null, false)
					->or_group_start()
						->where('actual_delivery_date IS NULL', null, false)
						->where('expected_delivery_date IS NOT NULL', null, false)
						->where('expected_delivery_date <', date('Y-m-d'))
					->group_end()
				->group_end()
				->group_by('state')
				->order_by('total', 'desc');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			$late_packages_count_by_state = $prod_db->get()->result_array();
			
			if(!empty($late_packages_count_by_state)) {
				foreach($late_packages_count_by_state as $current_data) {
					$state = !empty($current_data['state']) ? $current_data['state'] : 'N/A';
					$packages_info_by_state[$state]['late_packages'] = $current_data['total'];
				}
			}
			
			$data['summary'] = array(
				'zone' => $packages_info_by_zone,
				'state' => $packages_info_by_state
			);
		}
		else {
			// Get transit time data
			$data['transit_time_data'] = $this->get_transit_time_data($data);
			
			// Get carrier status summary data
			
			$carrier_status_summary_data = array();
			
			// #1 Num packages
			$prod_db
				->select(
					"carrier_code, COUNT(*) AS num_packages", false)
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				->where('carrier_code IS NOT NULL', null, false)
				->where_not_in('store_id', $excluded_store_ids)
				->group_by('carrier_code')
				->order_by('carrier_code');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			$num_packages_data = $prod_db->get()->result_array();
			
			if(!empty($num_packages_data)) {
				foreach($num_packages_data as $current_data) {
					if(!isset($carrier_status_summary_data[$current_data['carrier_code']])) {
						$carrier_status_summary_data[$current_data['carrier_code']] = array(
							'num_packages' => 0,
							'num_delivered' => 0,
							'num_delivered_late' => 0,
							'delivered_ontime_percentage' => 100,
							'num_not_delivered' => 0,
							'num_not_delivered_late' => 0
						);
					}
					
					$carrier_status_summary_data[$current_data['carrier_code']]['num_packages'] = $current_data['num_packages'];
				}
			}
			
			// #2 #Delivered
			$prod_db
				->select(
					"carrier_code, COUNT(*) AS num_delivered", false)
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				->where('carrier_code IS NOT NULL', null, false)
				//->like('status', 'delivered')
				->where('actual_delivery_date IS NOT NULL', null, false)
				->where_not_in('store_id', $excluded_store_ids)
				->group_by('carrier_code')
				->order_by('carrier_code');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			$num_delivered_data = $prod_db->get()->result_array();
				
			if(!empty($num_delivered_data)) {
				foreach($num_delivered_data as $current_data) {
					$carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'] = $current_data['num_delivered'];
					$carrier_status_summary_data[$current_data['carrier_code']]['num_not_delivered'] = $carrier_status_summary_data[$current_data['carrier_code']]['num_packages'] - $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'];
				}
			}
			
			// #3 #Delivered - Late
			$prod_db
				->select(
					"carrier_code, COUNT(*) AS num_delivered_late", false)
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				//->like('status', 'delivered')
				->where('actual_delivery_date IS NOT NULL', null, false)
				->where('expected_delivery_date IS NOT NULL', null, false)
				->where('DATE(actual_delivery_date) > expected_delivery_date', null, false)
				->where('carrier_code IS NOT NULL', null, false)
				->where_not_in('store_id', $excluded_store_ids)
				->group_by('carrier_code')
				->order_by('carrier_code');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			$num_delivered_late_data = $prod_db->get()->result_array();
				
			if(!empty($num_delivered_late_data)) {
				foreach($num_delivered_late_data as $current_data) {
					$carrier_status_summary_data[$current_data['carrier_code']]['num_delivered_late'] = $current_data['num_delivered_late'];
					$carrier_status_summary_data[$current_data['carrier_code']]['delivered_ontime_percentage'] = $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'] > 0 ? number_format(100 - $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered_late'] / $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'] * 100, 2) : 0;
				}
			}
			
			// #4 #Not Delivered - Late
			$prod_db
				->select(
					"carrier_code, COUNT(*) AS num_not_delivered_late", false)
				->from('packages')
				->where("package_created_at_local >=", $period_from)
				->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
				//->not_like('status', 'delivered')
				->where('actual_delivery_date IS NULL', null, false)
				->where('expected_delivery_date IS NOT NULL', null, false)
				->where('expected_delivery_date <', date('Y-m-d'))
				->where('carrier_code IS NOT NULL', null, false)
				->where_not_in('store_id', $excluded_store_ids)
				->group_by('carrier_code')
				->order_by('carrier_code');
			
			if(!empty($data['customer'])) {
				$prod_db->where('store_id', $data['customer']);
			}
			
			if(!empty($data['shipping_method'])) {
				$prod_db->where_in('shipping_method', $data['shipping_method']);
			}
			
			if(!empty($data['stock_ids'])) {
				$prod_db->where_in('stock_id', $data['stock_ids']);
			}
			
			if(!empty($data['carrier'])) {
				$prod_db->where_in('carrier_code', $data['carrier']);
			}
			
			$num_not_delivered_late_data = $prod_db->get()->result_array();
				
			if(!empty($num_not_delivered_late_data)) {
				foreach($num_not_delivered_late_data as $current_data) {
					$carrier_status_summary_data[$current_data['carrier_code']]['num_not_delivered_late'] = $current_data['num_not_delivered_late'];
				}
			}
			
			$data['carrier_status_summary_data'] = $carrier_status_summary_data;
		} // End report type
		
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		$data['package_status_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_package_status_board_table', $data, true);
		
		$data['js_package_status_board_table_html'] = $this->load->view(PROJECT_CODE.'/js_view_package_status_board_table', $data, true);
		
		return $data;
	}
	
	public function get_carrier_tracking_status($args) {
		$now = date('Y-m-d H:i:s');
		$carrier_first_scan_datetime = null;
		
		if($args['carrier_code'] == 'ups') {
			$result_tmp = $this->get_ups_tracking_status($args);
			
			$status = $result_tmp['status'];
			$eta = $result_tmp['eta'];
			$actual_delivery_date = $result_tmp['actual_delivery_date'];
		}
		else if($args['carrier_code'] == 'fedex') {
			$result_tmp = $this->get_fedex_tracking_status($args);
			
			$status = $result_tmp['status'];
			$eta = null;
			$actual_delivery_date = !empty($result_tmp['actual_delivery_date']) ? $result_tmp['actual_delivery_date'] : null;
			$carrier_first_scan_datetime = !empty($result_tmp['carrier_first_scan_at']) ? $result_tmp['carrier_first_scan_at'] : null;
		}
		else {
			$process = curl_init();
			curl_setopt($process, CURLOPT_URL, 'https://shipit-api.herokuapp.com/api/carriers/'.$args['carrier_code'].'/'.$args['track_number']);
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$return = json_decode(curl_exec($process), true);
			curl_close($process);
			
			
			$status = null;
			$actual_delivery_date = null;
			$eta = null;
			
			if(!empty($return['activities'])) {
				$latest_activity_datetime = '2000-01-01';
				
				foreach($return['activities'] as $activity) {
					if(!empty($activity['datetime']) || !empty($activity['timestamp'])) {
						if(!empty($activity['datetime'])) {
							$activity_datetime = str_replace('T',' ',$activity['datetime']);
						}
						else {
							$activity_datetime = convert_timezone(str_replace('T',' ',$activity['timestamp']),'UTC','US/Eastern');
						}
						
						if(strtolower($activity['details']) == 'picked up') {
							if(empty($carrier_first_scan_datetime) || (strtotime($activity['datetime']) < strtotime($carrier_first_scan_datetime))) {
								$carrier_first_scan_datetime = $activity_datetime;
							}
						}
						
						if((strpos(strtolower($activity['details']), 'delivered') !== false) || (strtotime($activity_datetime) > strtotime($latest_activity_datetime) && (strpos(strtolower($status), 'delivered') === false))) {
							$status = $activity['details'];
							if(strpos(strtolower($status), 'delivered') !== false) {
								$actual_delivery_date = $activity_datetime; //substr($activity_datetime,0,10);
							}
							$latest_activity_datetime = $activity_datetime;
						}
					}
				}
			}
			
			$eta = isset($return[$i]['eta']) ? substr($return[$i]['eta'],0,10) . ' ' . substr($return[$i]['eta'],11,8) : null;

			if(strpos(strtolower($status), 'delivered') === false && strtolower($packages[$i]['mwe_package_status']) == 'exception') {
				$status = 'Exception';
			}
		}
		
		$color = '';
		
		if(!empty($actual_delivery_date) && !empty($eta)) {
			if(strtotime($actual_delivery_date) <= strtotime($eta)) {
				$color = 'green';
			}
			else {
				$color = 'red';
			}
		}
		else if(strpos(strtolower($status), 'delivery exception') !== false) {
			$color = 'yellow';
		}
		else if(strpos(strtolower($status), 'late') !== false) {
			$color = 'red';
		}
		
		if(strtolower($args['mwe_status']) == 'exception') {
			$status = 'Exception';
			$color = 'yellow';
		}
		
		$result = array(
			'success' => true,
			'status' => $status,
			'eta' => $eta,
			'carrier_first_scan_at' => $carrier_first_scan_datetime,
			'actual_delivery_date' => !empty($actual_delivery_date) ? date('Y-m-d', strtotime($actual_delivery_date)) : null,
			'is_delivered' => empty($actual_delivery_date) ? 'No' : 'Yes',
			'color' => $color,
			'last_checked_at' => $now
		);
		
		$prod_db = $this->load->database('prod', TRUE);
		
		foreach(array('carrier_service_name', 'destination_city', 'weight', 'weight_measurement', 'received_by', 'left_at', 'track_url', 'actual_delivery_time_utc', 'carrier_first_scan_at_utc') as $field_name) {
			if(!empty($result_tmp[$field_name])) {
				$prod_db->set($field_name, $result_tmp[$field_name]);
			}
		}
		
		if(!empty($status)) {
			$prod_db
				->set('status', $status)
				->set('carrier_eta', !empty($eta) ? $eta : null)
				->set('carrier_first_scan_at', $carrier_first_scan_datetime)
				->set('actual_delivery_date', !empty($actual_delivery_date) ? $actual_delivery_date : null)
				->set('carrier_data_updated_at', date('Y-m-d H:i:s'))
				->set('last_checked_at', date('Y-m-d H:i:s'))
				->where('carrier_code', $args['carrier_code'])
				->where('track_number', $args['track_number'])
				->update('packages');
		}
		else if(!empty($eta)) {
			$prod_db
				->set('carrier_eta', !empty($eta) ? $eta : null)
				->set('actual_delivery_date', !empty($actual_delivery_date) ? $actual_delivery_date : null)
				->set('carrier_data_updated_at', date('Y-m-d H:i:s'))
				->set('carrier_first_scan_at', $carrier_first_scan_datetime)
				->set('last_checked_at', date('Y-m-d H:i:s'))
				->where('carrier_code', $args['carrier_code'])
				->where('track_number', $args['track_number'])
				->update('packages');
		}
		else {
			if(!empty($eta)) {
				$prod_db->set('carrier_eta', $eta);
			}
			
			if(!empty($actual_delivery_date)) {
				$prod_db
					->set('status', 'Delivered*')
					->set('actual_delivery_date', $actual_delivery_date);
			}
			
			if(!empty($eta) || !empty($actual_delivery_date)) {
				$prod_db->set('carrier_data_updated_at', date('Y-m-d H:i:s'));
			}
			
			$prod_db
				->set('last_checked_at', $now)
				->set('carrier_first_scan_at', $carrier_first_scan_datetime)
				->where('carrier_code', $args['carrier_code'])
				->where('track_number', $args['track_number'])
				->update('packages');
		}
		
		return $result;
	}
	
	public function get_transit_time_data($data) {
		$prod_db = $this->load->database('prod', TRUE);
			
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		$transit_time_data = $prod_db
			->query(
				"SELECT transit_data.transit_day, COUNT(*) AS total
				FROM (
					SELECT DATEDIFF(DATE(actual_delivery_date), DATE(package_created_at_local)) AS transit_day
					FROM packages
					WHERE actual_delivery_date IS NOT NULL
					". ( (!empty($data['customer'])) ? "AND store_id=".$prod_db->escape($data['customer'])." " : null ) ."
					". ( (!empty($data['stock_ids'])) ? "AND stock_id IN(".implode(',',$data['stock_ids']).") " : null ) ."
					". ( (!empty($data['carrier'])) ? "AND carrier_code IN('".implode('\',\'',$data['carrier'])."') " : null ) ."
					AND package_created_at_local >= ".$prod_db->escape($period_from)." 
					AND package_created_at_local < " . $prod_db->escape(date('Y-m-d', strtotime('+1 day '.$period_to))) . ") transit_data
				WHERE transit_day > 0
				". (!empty($data['max_transit_day']) ? "AND transit_day <= " . $data['max_transit_day'] . " " : null) ."
				GROUP BY transit_data.transit_day
				ORDER BY transit_data.transit_day"
			)
			->result_array();
		
		if(!empty($transit_time_data)) {
			$total_packages = 0;
			for($i=0; $i<count($transit_time_data); $i++) {
				$total_packages += $transit_time_data[$i]['total'];
			}
			
			$cumulative = 0;
			for($i=0; $i<count($transit_time_data); $i++) {
				$cumulative += $transit_time_data[$i]['total'];
				$transit_time_data[$i]['cumulative'] = $cumulative;
				$transit_time_data[$i]['actual_percentage'] = $cumulative / $total_packages * 100;
			}
		}
		
		return $transit_time_data;
	}
	
	public function get_ups_tracking_status($args) {
		$result = array(
			'status' => null,
			'actual_delivery_date' => null,
			'eta' => null,
		);
		$tracking = new Ups\Tracking('0DA0012065349055', 'chch50', 'Csups2116@');
		
		$shipment = $tracking->track($args['track_number']);
		
		//debug_var($shipment);
		
		$activities = $shipment->Package->Activity;
		
		$latest_result = array();
		
		if(!empty($activities)) {
			foreach($activities as $index => $activity) {
				if($activity->Status->StatusType->Code == 'D') {
					$result['status'] = $activity->Status->StatusType->Description;
					
					$actual_delivery_date = date_create_from_format('YmdHis', $activity->Date . $activity->Time);
					$result['actual_delivery_date'] = $actual_delivery_date->format('Y-m-d H:i:s');
				}
				
				if($index == 0) {
					$latest_result = array(
						'status' => $activity->Status->StatusType->Description,
						'actual_delivery_date' => null,
						'eta' => null
					);
				}
			}
			
			if(empty($result['status']) && empty($result['actual_delivery_date'])) {
				$result = $latest_result;
			}
		}
		
		return $result;
	}
	
	public function get_fedex_tracking_status($args) {
		$this->load->model(PROJECT_CODE.'/model_carrier_fedex');
		
		if(empty($args['track_number'])) {
			return null;
		}
		
		$result_tmp = $this->model_carrier_fedex->get_fedex_tracking_info($args['track_number']);
		
		$result = array(
			'status' => null,
			'actual_delivery_date' => null,
			'eta' => null,
		);
		if(!empty($result_tmp['tracking_data']['status'])) {
			$result = $result_tmp['tracking_data'];
		}
		
		return $result;
	}
	
	/*public function update_ups_packages_tracking_by_date($date) {
		$result = array();
		
		$prod_db = $this->load->database('prod', TRUE);
		$ups_packages = $prod_db
			->select('track_number')
			->from('packages')
			->where('is_failed', false)
			->where('carrier_code', 'ups')
			->where('package_created_at_local >=', $date)
			->where('package_created_at_local <', date('Y-m-d H:i:s', strtotime('+10 minute '.$date)))
			//->where('package_created_at_local <', date('Y-m-d', strtotime('+1 day '.$date)))
			//->where('actual_delivery_date IS NULL', null, false)
			->get()->result_array();

		if(!empty($ups_packages)) {
			foreach($ups_packages as $package) {
				$this->get_carrier_tracking_status(
					array(
						'carrier_code' => 'ups',
						'track_number' => $package['track_number'],
						'mwe_status' => null
					)
				);
			}
		}
		
		$result['success'] = true;
		$result['count'] = count($ups_packages);
		
		return $result;
	}*/
	
	public function update_fedex_packages_tracking_by_date($date) {
		$result = array();
		
		$prod_db = $this->load->database('prod', TRUE);
		$fedex_packages = $prod_db
			->select('track_number')
			->from('packages')
			->where('is_failed', false)
			->where('carrier_code', 'fedex')
			->where('package_created_at_local >=', $date)
			->where('package_created_at_local <', date('Y-m-d H:i:s', strtotime('+5 minute '.$date)))
			//->where('package_created_at_local <', date('Y-m-d', strtotime('+1 day '.$date)))
			->where('actual_delivery_date IS NULL', null, false)
			->get()->result_array();
		//debug_var($ups_packages);
		if(!empty($fedex_packages)) {
			foreach($fedex_packages as $package) {
				$this->get_carrier_tracking_status(
					array(
						'carrier_code' => 'fedex',
						'track_number' => $package['track_number'],
						'mwe_status' => null
					)
				);
			}
		}
		
		$result['success'] = true;
		$result['count'] = count($fedex_packages);
		
		return $result;
	}
	
	// Getting next UPS package to be checked
	public function get_next_ups_packages_to_check($limit = 50, $cutoff_date = '-119 day') {
		$prod_db = $this->load->database('prod', TRUE);
		
		$ups_packages = $prod_db
			->select('package_id, track_number, last_checked_at')
			->from('packages')
			->where('carrier_code', 'ups')
			->group_start()
				->where('actual_delivery_date IS NULL', null, false)
				->or_where('carrier_first_scan_at_utc IS NULL', null, false)
			->group_end()
			->where('package_created_at_utc >=', date('Y-m-d', strtotime($cutoff_date)))
			->order_by('last_checked_at')
			->limit($limit)
			->get()->result_array();
			
		return $ups_packages;
	}
	
	// Getting next FedEx package to be checked
	public function get_next_fedex_packages_to_check($limit = 30, $cutoff_date = '-120 day') {
		$prod_db = $this->load->database('prod', TRUE);
		
		$fedex_packages = $prod_db
			->select('package_id, track_number, last_checked_at')
			->from('packages')
			->where('carrier_code', 'fedex')
			->where('track_number IS NOT NULL', null, false)
			->where('is_failed', false)
			->where('actual_delivery_date IS NULL', null, false)
			->where('package_created_at_local <', date('Y-m-d'))
			->where('package_created_at_utc >=', date('Y-m-d', strtotime($cutoff_date)))
			->order_by('last_checked_at')
			->limit($limit)
			->get()->result_array();

		return $fedex_packages;
	}
	
	public function get_next_other_carriers_packages_to_check($limit = 50, $cutoff_date = '-120 day') {
		$prod_db = $this->load->database('prod', TRUE);
		
		$packages = $prod_db
			->select('package_id, carrier_code, track_number, last_checked_at')
			->from('packages')
			->where_in('carrier_code', array('lasership','ontrac','usps'))
			->where('actual_delivery_date IS NULL', null, false)
			->where('package_created_at_utc >=', date('Y-m-d', strtotime($cutoff_date)))
			->order_by('last_checked_at')
			->limit($limit)
			->get()->result_array();
			
		return $packages;
	}
	
	// This is to update the status of UPS packages
	public function update_ups_packages_status() {
		$prod_db = $this->load->database('prod', TRUE);
		
		$result = array('success_count' => 0, 'unsuccessful_package_track_numbers' => array());
		
		$ups_packages = $this->get_next_ups_packages_to_check();
		
		$this->load->model(PROJECT_CODE.'/model_carrier_ups');
		
		$updated_ups_packages = array();
		$unsuccessful_package_track_numbers = array();
		
		foreach($ups_packages as $ups_package) {
			$ups_package_tracking_info = $this->model_carrier_ups->get_ups_tracking_info($ups_package['track_number']);
			
			if($ups_package_tracking_info['success']) {
				$tracking_data = $ups_package_tracking_info['tracking_data'];
				$tracking_data['package_id'] = $ups_package['package_id'];
				$tracking_data['last_checked_at'] = date('Y-m-d H:i:s');

				$updated_ups_packages[] = $tracking_data;
			}
			else {
				$error_message = $ups_package_tracking_info['error_message'];
				$this->mark_package_as_failed($ups_package['package_id'], $error_message);
				$unsuccessful_package_track_numbers[] = $ups_package['track_number'];
			}
		}
		
		if(!empty($updated_ups_packages)) {
			$prod_db->trans_start();
			$prod_db->update_batch('packages', $updated_ups_packages, 'package_id');
			$prod_db->trans_complete();
		}
		
		$result['success_count'] = count($updated_ups_packages);
		$result['unsuccessful_package_track_numbers'] = $unsuccessful_package_track_numbers;
		$result['previously_checked_at'] = $ups_packages[0]['last_checked_at'];
		
		return $result;
	}
	
	// This is to update the status of FedEx packages
	public function update_fedex_packages_status() {
		$prod_db = $this->load->database('prod', TRUE);
		
		$result = array('success_count' => 0, 'unsuccessful_package_track_numbers' => array());
		
		$fedex_packages = $this->get_next_fedex_packages_to_check();
		
		$this->load->model(PROJECT_CODE.'/model_carrier_fedex');
		
		$updated_fedex_packages = array();
		$unsuccessful_package_track_numbers = array();
		
		/*foreach($fedex_packages as $fedex_package) {
			$fedex_package_tracking_info = $this->model_carrier_fedex->get_fedex_tracking_info($fedex_package['track_number']);
			
			if($fedex_package_tracking_info['success'] && !empty($fedex_package_tracking_info['tracking_data']['carrier_service_name'])) {
				$tracking_data = $fedex_package_tracking_info['tracking_data'];
				$tracking_data['package_id'] = $fedex_package['package_id'];
				$tracking_data['last_checked_at'] = date('Y-m-d H:i:s');

				$updated_fedex_packages[] = $tracking_data;
			}
			else {
				$error_message = isset($fedex_package_tracking_info['error_message']) ? $fedex_package_tracking_info['error_message'] : 'Empty result';
				
				// $this->mark_package_as_failed($fedex_package['package_id'], $error_message);
				$unsuccessful_package_track_numbers[] = $fedex_package['track_number'];
				$updated_fedex_packages[] = array(
					'package_id' => $fedex_package['package_id'],
					'last_checked_at' => date('Y-m-d H:i:s')
				);
			}
		}*/
		
		$track_numbers = array();
		$package_id_by_track_number = array();
		if(!empty($fedex_packages)) {
			foreach($fedex_packages as $fedex_package) {
				if(!empty($fedex_package['track_number'])) {
					$track_numbers[] = $fedex_package['track_number'];
					$package_id_by_track_number[$fedex_package['track_number']] = $fedex_package['package_id'];
				}
				else {
					$updated_fedex_packages[] = array(
						'package_id' => $fedex_package['package_id'],
						'is_failed' => true,
						'failure_reason' => 'Empty track number',
						'last_checked_at' => date('Y-m-d H:i:s')
					);
				}
			}
			
	
			$fedex_packages_tracking_info = $this->model_carrier_fedex->get_fedex_tracking_info($track_numbers);
			
			if($fedex_packages_tracking_info['success']) {
				foreach($fedex_packages_tracking_info['tracking_data'] as $track_number => $tracking_data) {
					if(!empty($tracking_data['carrier_service_name'])) {
						$tracking_data['package_id'] = $package_id_by_track_number[$tracking_data['track_number']];
						$tracking_data['last_checked_at'] = date('Y-m-d H:i:s');

						$updated_fedex_packages[] = $tracking_data;
					}
					else {
						$error_message = isset($fedex_package_tracking_info['error_message']) ? $fedex_package_tracking_info['error_message'] : 'Empty result';
						
						// $this->mark_package_as_failed($fedex_package['package_id'], $error_message);
						$unsuccessful_package_track_numbers[] = $fedex_package['track_number'];
						$updated_fedex_packages[] = array(
							'package_id' => $package_id_by_track_number[$track_number],
							'last_checked_at' => date('Y-m-d H:i:s')
						);
					}
				}
			}
		}

		if(!empty($updated_fedex_packages)) {
			$prod_db->trans_start();
			$prod_db->update_batch('packages', $updated_fedex_packages, 'package_id');
			$prod_db->trans_complete();
		}
		
		$result['success_count'] = count($updated_fedex_packages);
		$result['unsuccessful_package_track_numbers'] = $unsuccessful_package_track_numbers;
		$result['previously_checked_at'] = $fedex_packages[0]['last_checked_at'];
		
		return $result;
	}
	
	// This is to update the status of Lasership, FedEx, OnTrac, USPS packages
	public function update_other_carriers_packages_status() {
		$prod_db = $this->load->database('prod', TRUE);
		
		$result = array('success_count' => 0, 'unsuccessful_package_track_numbers' => array());
		
		$packages = $this->get_next_other_carriers_packages_to_check();
		
		$this->load->model(PROJECT_CODE.'/model_carrier');
		
		$updated_packages = array();
		$unsuccessful_package_ids = array();

		$tracking_info = $this->model_carrier->get_tracking_info_in_bulk($packages);
		
		if(!empty($tracking_info)) {
			foreach($tracking_info as $current_tracking_info) {
				if($current_tracking_info['success']) {
					$tracking_data = $current_tracking_info['tracking_data'];
					$tracking_data['last_checked_at'] = date('Y-m-d H:i:s');

					$updated_packages[] = $tracking_data;
				}
				else {
					$error_message = $current_tracking_info['error_message'];
					$this->mark_package_as_failed($current_tracking_info['package_id'], $error_message);
					$unsuccessful_package_ids[] = $current_tracking_info['package_id'];
				}
			}
		}
		
		if(!empty($updated_packages)) {
			$prod_db->trans_start();
			$prod_db->update_batch('packages', $updated_packages, 'package_id');
			$prod_db->trans_complete();
		}
		
		$result['success_count'] = count($updated_packages);
		$result['unsuccessful_package_ids'] = $unsuccessful_package_ids;
		$result['previously_checked_at'] = $packages[0]['last_checked_at'];
		
		return $result;
	}
	
	public function mark_package_as_failed($package_id, $failure_reason = 'Unknown failure.') {
		// Mark package which was failed when was retrieved the tracking info
		$prod_db = $this->load->database('prod', TRUE);
		
		$prod_db
			->set('is_failed', true)
			->set('failure_reason', $failure_reason)
			->set('last_checked_at', date('Y-m-d H:i:s'))
			->where('package_id', $package_id)
			->update('packages');
	}
}