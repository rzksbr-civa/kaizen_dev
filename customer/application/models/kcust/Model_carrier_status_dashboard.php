<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_carrier_status_dashboard extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_carrier_status_dashboard_data($data) {
		ini_set('max_execution_time', 300);
		$prod_db = $this->load->database('prod_packages', TRUE);
			
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');

		$prod_db
			->select(
				"package_id,
				store_name AS customer_name,
				package_created_at_local AS local_package_created_time,
				order_increment_id AS order_number,
				carrier_code,
				track_number,
				status,
				DATE(expected_delivery_date) AS expected_delivery_date,
				DATE(carrier_eta) AS carrier_eta,
				DATE(actual_delivery_date) AS actual_delivery_date,
				last_checked_at", false)
			->from('packages')
			->where('store_id', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where("package_created_at_local >=", $period_from)
			->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->order_by('package_created_at_local');
		
		if(!empty($data['carrier'])) {
			$prod_db->where_in('carrier_code', $data['carrier']);
		}
		
		if(!empty($data['track_number'])) {
			$prod_db->where('track_number', $data['track_number']);
		}
		
		if(!empty($data['is_delivered'])) {
			if($data['is_delivered'] == 'yes') {
				$prod_db->like('status', 'Delivered');
			}
			else if($data['is_delivered'] == 'no') {
				$prod_db->group_start()
					->where('status IS NULL', null, false)
					->or_not_like('status', 'Delivered')
					->group_end();
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
							->group_start()
								->where('status IS NULL', null, false)
								->or_not_like('status', 'Delivered')
							->group_end()
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

		$carrier_status_dashboard_data = $prod_db->get()->result_array();
		
		$need_updated_package_ids = array();
		$packages_to_update = array();
		
		// Last check threshold: The data before this time needs to check again
		$last_checked_at_threshold = strtotime('-1 min');
		
		$data['row_index_by_package_id'] = array();
		
		$delivered_packages_track_numbers = array();
		
		foreach($carrier_status_dashboard_data as $key => $current_data) {
			$data['row_index_by_package_id'][$current_data['package_id']] = $key;
			
			$late_days = null;
			$carrier_status_dashboard_data[$key]['is_delivered'] = (strpos($current_data['status'], 'Delivered') !== false);

			$late_days = null;
			
			if($carrier_status_dashboard_data[$key]['is_delivered']) {
				if(!empty($current_data['expected_delivery_date']) && !empty($current_data['actual_delivery_date'])) {
					$expected_delivery_date = date('Y-m-d', strtotime($current_data['expected_delivery_date']));
					$actual_delivery_date = date('Y-m-d', strtotime($current_data['actual_delivery_date']));
					
					if(strtotime($actual_delivery_date) > strtotime($expected_delivery_date)) {
						$late_days = (strtotime($actual_delivery_date) - strtotime($current_data['expected_delivery_date'])) / 86400;
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
						$late_days = (strtotime($current_date) - strtotime($current_data['expected_delivery_date'])) / 86400;
					}
				}
			}
			
			$carrier_status_dashboard_data[$key]['late_days'] = $late_days;
			
			$carrier_status_dashboard_data[$key]['color'] = 'normal';
			
			if(strpos(strtolower($current_data['status']), 'delivery exception') !== false) {
				$carrier_status_dashboard_data[$key]['color'] = 'yellow';
			}
			else if(!empty($carrier_status_dashboard_data[$key]['late_days'])) {
				$carrier_status_dashboard_data[$key]['color'] = 'red';
			}
			else if($carrier_status_dashboard_data[$key]['is_delivered']) {
				$carrier_status_dashboard_data[$key]['color'] = 'green';
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
		$data['carrier_status_dashboard_data'] = $carrier_status_dashboard_data;
		
		// Get carrier status summary data
		
		$carrier_status_summary_data = array();
		
		// #1 Num packages
		$num_packages_data = $prod_db
			->select(
				"carrier_code, COUNT(*) AS num_packages", false)
			->from('packages')
			->where('store_id', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where("package_created_at_local >=", $period_from)
			->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->where('carrier_code IS NOT NULL', null, false)
			->group_by('carrier_code')
			->order_by('carrier_code')
			->get()->result_array();
			
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
		$num_delivered_data = $prod_db
			->select(
				"carrier_code, COUNT(*) AS num_delivered", false)
			->from('packages')
			->where('store_id', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where("package_created_at_local >=", $period_from)
			->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->where('carrier_code IS NOT NULL', null, false)
			->like('status', 'delivered')
			->group_by('carrier_code')
			->order_by('carrier_code')
			->get()->result_array();
			
		if(!empty($num_delivered_data)) {
			foreach($num_delivered_data as $current_data) {
				$carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'] = $current_data['num_delivered'];
				$carrier_status_summary_data[$current_data['carrier_code']]['num_not_delivered'] = $carrier_status_summary_data[$current_data['carrier_code']]['num_packages'] - $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'];
			}
		}
		
		// #3 #Delivered - Late
		$num_delivered_late_data = $prod_db
			->select(
				"carrier_code, COUNT(*) AS num_delivered_late", false)
			->from('packages')
			->where('store_id', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where("package_created_at_local >=", $period_from)
			->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->like('status', 'delivered')
			->where('expected_delivery_date IS NOT NULL', null, false)
			->where('DATE(actual_delivery_date) > expected_delivery_date', null, false)
			->where('carrier_code IS NOT NULL', null, false)
			->group_by('carrier_code')
			->order_by('carrier_code')
			->get()->result_array();
			
		if(!empty($num_delivered_late_data)) {
			foreach($num_delivered_late_data as $current_data) {
				$carrier_status_summary_data[$current_data['carrier_code']]['num_delivered_late'] = $current_data['num_delivered_late'];
				$carrier_status_summary_data[$current_data['carrier_code']]['delivered_ontime_percentage'] = $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'] > 0 ? number_format(100 - $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered_late'] / $carrier_status_summary_data[$current_data['carrier_code']]['num_delivered'] * 100, 2) : 0;
			}
		}
		
		// #4 #Not Delivered - Late
		$num_not_delivered_late_data = $prod_db
			->select(
				"carrier_code, COUNT(*) AS num_not_delivered_late", false)
			->from('packages')
			->where('store_id', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where("package_created_at_local >=", $period_from)
			->where("package_created_at_local <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->not_like('status', 'delivered')
			->where('expected_delivery_date IS NOT NULL', null, false)
			->where('expected_delivery_date <', date('Y-m-d'))
			->where('carrier_code IS NOT NULL', null, false)
			->group_by('carrier_code')
			->order_by('carrier_code')
			->get()->result_array();
			
		if(!empty($num_not_delivered_late_data)) {
			foreach($num_not_delivered_late_data as $current_data) {
				$carrier_status_summary_data[$current_data['carrier_code']]['num_not_delivered_late'] = $current_data['num_not_delivered_late'];
			}
		}
		
		$data['carrier_status_summary_data'] = $carrier_status_summary_data;
		
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		$data['carrier_status_dashboard_for_packages_table_html'] = $this->load->view(PROJECT_CODE.'/view_carrier_status_dashboard_for_packages_table', $data, true);
		
		$data['js_carrier_status_dashboard_for_packages_table_html'] = $this->load->view(PROJECT_CODE.'/js_view_carrier_status_dashboard_for_packages_table', $data, true);
		
		return $data;
	}
	
	public function get_carrier_tracking_status($args) {
		$process = curl_init();
		curl_setopt( $process, CURLOPT_URL, 'https://shipit-api.herokuapp.com/api/carriers/'.$args['carrier_code'].'/'.$args['track_number']);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$return = json_decode(curl_exec($process), true);
		curl_close($process);
		
		$now = date('Y-m-d H:i:s');
		
		$status = isset($return['activities'][0]['details']) ? $return['activities'][0]['details'] : '';
		$eta = isset($return['eta']) ? substr($return['eta'],0,10) : '';
		$actual_delivery_date = (strtolower($status) == 'delivered') ? substr($return['activities'][0]['timestamp'],0,10) : '';
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
			'actual_delivery_date' => $actual_delivery_date,
			'color' => $color,
			'last_checked_at' => $now,
			'pod' => isset($actual_delivery_date) ? '<a href="https://www.fedex.com/trackingCal/retrievePDF.jsp?accountNbr=&anon=true&appType=&destCountry=&locale=en_US&shipDate=&trackingCarrier=FDXG&trackingNumber='.$args['track_number'].'&type=SPOD" target="_blank"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a>' : null
		);
		
		$prod_db = $this->load->database('prod', TRUE);
		
		if(!empty($status)) {
			$prod_db
				->set('status', $status)
				->set('carrier_eta', !empty($eta) ? $eta : null)
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
				->where('carrier_code', $args['carrier_code'])
				->where('track_number', $args['track_number'])
				->update('packages');
		}
		
		return $result;
	}
	
	public function update_carrier_info_of_packages_by_package_ids($package_ids) {
		$updated_packages = array();
		
		if(!empty($package_ids)) {
			$prod_db = $this->load->database('prod', TRUE);
			$packages = $prod_db
				->select('*')
				->from('packages')
				->where_in('package_id', $package_ids)
				->get()->result_array();
			
			$updated_packages = $this->update_carrier_info_of_packages($packages, true);
		}
		
		return $updated_packages;
	}
	
	public function update_carrier_info_of_packages($packages, $return_updated_packages = false) {
		$prod_db = $this->load->database('prod', TRUE);
		
		$packages_by_package_id = array_combine(
			array_column($packages, 'package_id'),
			array_values($packages)
		);
		
		$ch = array();
		$limit = count($packages);
		
		for($i=0; $i<$limit; $i++) {
			$ch[$i] = curl_init();
			curl_setopt($ch[$i], CURLOPT_URL, 'https://shipit-api.herokuapp.com/api/carriers/'.$packages[$i]['carrier_code'].'/'.$packages[$i]['track_number']);
			curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
			curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, TRUE);
		}

		//create the multiple cURL handle
		$mh = curl_multi_init();

		for($i=0; $i<$limit; $i++) {
			curl_multi_add_handle($mh,$ch[$i]);
		}

		$active = null;
		//execute the handles
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) != -1) {
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}

		//close the handles
		for($i=0; $i<$limit; $i++) {
			curl_multi_remove_handle($mh, $ch[$i]);
		}
		curl_multi_close($mh);
		
		$updated_packages = array();
		
		$return = array();
		for($i=0; $i<$limit; $i++) {
			$return[$i] = json_decode(curl_multi_getcontent($ch[$i]), true);
			
			$status = null;
			$actual_delivery_date = null;
			$eta = null;
			if(!empty($return[$i]['activities'])) {
				$latest_activity_datetime = '2000-01-01';
				
				foreach($return[$i]['activities'] as $activity) {
					if(!empty($activity['datetime']) || !empty($activity['timestamp'])) {
						if(!empty($activity['datetime'])) {
							$activity_datetime = str_replace('T',' ',$activity['datetime']);
						}
						else {
							$activity_datetime = convert_timezone(str_replace('T',' ',$activity['timestamp']),'UTC','US/Eastern');
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
			
			if(!empty($status)) {
				$updated_packages[] = array(
					'status' => $status,
					'carrier_eta' => $eta,
					'actual_delivery_date' => $actual_delivery_date,
					'carrier_data_updated_at' => date('Y-m-d H:i:s'),
					'last_checked_at' => date('Y-m-d H:i:s'),
					'package_id' => $packages[$i]['package_id']
				);
			}
			else {				
				$updated_packages[] = array(
					'status' => $packages[$i]['status'],
					'carrier_eta' => $packages[$i]['carrier_eta'],
					'actual_delivery_date' => $packages[$i]['actual_delivery_date'],
					'carrier_data_updated_at' =>  $packages[$i]['carrier_data_updated_at'],
					'last_checked_at' => date('Y-m-d H:i:s'),
					'package_id' => $packages[$i]['package_id']
				);
			}
		}
		
		if(!empty($updated_packages)) {
			$prod_db->update_batch('packages', $updated_packages, 'package_id');
		}
		
		if($return_updated_packages) {
			foreach($updated_packages as $key => $current_data) {
				$updated_packages[$key]['track_number'] = $packages_by_package_id[$current_data['package_id']]['track_number'];
				
				$late_days = null;
				$updated_packages[$key]['is_delivered'] = (strpos($current_data['status'], 'Delivered') !== false);

				$late_days = null;
				
				if($updated_packages[$key]['is_delivered']) {
					if(!empty($packages_by_package_id[$current_data['package_id']]['expected_delivery_date']) && !empty($current_data['actual_delivery_date'])) {
						$expected_delivery_date = date('Y-m-d', strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date']));
						$actual_delivery_date = date('Y-m-d', strtotime($current_data['actual_delivery_date']));
						
						if(strtotime($actual_delivery_date) > strtotime($expected_delivery_date)) {
							$late_days = (strtotime($actual_delivery_date) - strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date'])) / 86400;
						}
					}
				}
				else {
					if(!empty($packages_by_package_id[$current_data['package_id']]['expected_delivery_date'])) {
						$expected_delivery_date = date('Y-m-d', strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date']));
						$current_date = date('Y-m-d');
						
						if(strtotime($current_date) > strtotime($expected_delivery_date)) {
							$late_days = (strtotime($current_date) - strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date'])) / 86400;
						}
					}
				}
				
				$updated_packages[$key]['late_days'] = $late_days;
				
				$updated_packages[$key]['color'] = 'normal';
				
				if(strpos(strtolower($current_data['status']), 'delivery exception') !== false) {
					$updated_packages[$key]['color'] = 'yellow';
				}
				else if(!empty($updated_packages[$key]['late_days'])) {
					$updated_packages[$key]['color'] = 'red';
				}
				else if($updated_packages[$key]['is_delivered']) {
					$updated_packages[$key]['color'] = 'green';
				}
			}

			return $updated_packages;
		}
	}
}