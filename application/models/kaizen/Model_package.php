<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_package extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function update_package_data($date) {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		if(empty($date)) {
			$date = date('Y-m-d');
		}
		
		$redstag_db
			->select(
				"sales_flat_shipment_package.package_id,
				 sales_flat_shipment_package.stock_id,
				 sales_flat_shipment.entity_id AS shipment_entity_id,
				 sales_flat_shipment.increment_id AS shipment_increment_id,
				 sales_flat_shipment.target_ship_date,
				 sales_flat_order.entity_id AS order_entity_id,
				 sales_flat_order.increment_id AS order_increment_id,
				 sales_flat_shipment_package.status AS mwe_package_status,
				 sales_flat_shipment_package.carrier_code,
				 sales_flat_shipment_package.track_number,
				 sales_flat_shipment_package.track_shipment_number,
				 sales_flat_shipment_package.track_description,
				 sales_flat_shipment_package.created_at AS package_created_at_utc,
				 IF(sales_flat_shipment_package.stock_id IN (3,6),
					CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),
					CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) AS package_created_at_local,
				sales_flat_shipment_package.updated_at AS package_updated_at_utc,
				 IF(sales_flat_shipment_package.stock_id IN (3,6),
					CONVERT_TZ(sales_flat_shipment_package.updated_at,'UTC','US/Mountain'),
					CONVERT_TZ(sales_flat_shipment_package.updated_at,'UTC','US/Eastern')) AS package_updated_at_local,
				sales_flat_shipment_package.expected_delivery_date,
				sales_flat_shipment.defunct AS shipment_defunct,
				sales_flat_shipment.shipping_method,
				sales_flat_order.website_id,
				core_website.name AS website_name,
				sales_flat_order.store_id,
				core_store.name AS store_name,
				sales_flat_order_address.postcode", false)
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('sales_flat_order_address', 'sales_flat_order_address.entity_id = sales_flat_order.shipping_address_id')	
			->join('core_website', 'core_website.website_id = sales_flat_order.website_id')
			->join('core_store', 'core_store.store_id = sales_flat_order.store_id')
			->where('sales_flat_shipment_package.created_at >=', $date)
			->where('sales_flat_shipment_package.created_at <', date('Y-m-d', strtotime('+1 day '.$date)))
			->order_by('sales_flat_shipment_package.package_id');

		$package_data = $redstag_db->get()->result_array();
		
		$existing_package_data = $prod_db
			->select('package_id')
			->from('packages')
			->where('package_created_at_utc >=', $date)
			->where('package_created_at_utc <', date('Y-m-d', strtotime('+1 day '.$date)))
			->get()->result_array();
		
		$existing_package_ids = array();
		if(!empty($existing_package_data)) {
			$existing_package_ids = array_column($existing_package_data, 'package_id');
		}
		
		$new_package_data = array();
		$updated_package_data = array();
		
		foreach($package_data as $package) {
			if(in_array($package['package_id'], $existing_package_ids)) {
				$updated_package_data[] = $package;
			}
			else {
				$new_package_data[] = $package;
			}
		}
		
		$prod_db->trans_start();
		
		if(!empty($new_package_data)) {
			$prod_db->insert_batch('packages', $new_package_data);
		}
		
		if(!empty($updated_package_data)) {
			$prod_db->update_batch('packages', $updated_package_data, 'package_id');
		}
		
		$prod_db->trans_complete();
		
		$this->update_ontrac_expected_delivery_date();
		// $this->update_ups_expected_delivery_date();
		
		return $data;
	}
	
	public function update_carrier_info($date) {
		$prod_db = $this->load->database('prod', TRUE);
		
		$packages = $prod_db
			->select('carrier_code, track_number')
			->from('packages')
			->where('package_created_at_utc >=', $date)
			->where('package_created_at_utc <', date('Y-m-d', strtotime('+1 day '.$date)))
			->where('carrier_data_updated_at IS NULL', null, false)
			->get()->result_array();
		
		foreach($packages as $package) {
			$process = curl_init();
			curl_setopt( $process, CURLOPT_URL, 'https://shipit-api.herokuapp.com/api/carriers/'.$package['carrier_code'].'/'.$package['track_number']);
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$return = json_decode(curl_exec($process), true);
			curl_close($process);
			
			$status = '';
			$actual_delivery_date = '';
			$carrier_first_scan_datetime = null;
			
			if(!empty($return['activities'])) {
				$latest_activity_datetime = '2000-01-01';

				foreach($return['activities'] as $activity) {
					$activity_datetime = str_replace('T',' ',$activity['datetime']);
					
					if(strtolower($activity['details']) == 'picked up') {
						if(empty($carrier_first_scan_datetime) || (strtotime($activity['datetime']) < strtotime($carrier_first_scan_datetime))) {
							$carrier_first_scan_datetime = $activity_datetime;
						}
					}

					if((strpos(strtolower($activity['details']), 'delivered') !== false) || (strtotime($activity['datetime']) > strtotime($latest_activity_datetime) && (strpos(strtolower($status), 'delivered') === false))) {
						$status = $activity['details'];
						if(strpos(strtolower($status), 'delivered') !== false) {
							$actual_delivery_date = substr($activity_datetime,0,10);
						}
						$latest_activity_datetime = $activity_datetime;
					}
				}
			}
			
			$eta = isset($return['eta']) ? substr($return['eta'],0,10) . ' ' . substr($return['eta'],11,8) : null;
			
			if(!empty($status)) {
				$prod_db
					->set('status', $status)
					->set('carrier_eta', $eta)
					->set('actual_delivery_date', $actual_delivery_date)
					->set('carrier_first_scan_at', $carrier_first_scan_datetime)
					->set('carrier_data_updated_at', date('Y-m-d H:i:s'))
					->where('carrier_code', $package['carrier_code'])
					->where('track_number', $package['track_number'])
					->update('packages');
			}
		}
	}
	
	public function get_carrier_tracking_status($args) {
		if($args['carrier_code'] == 'ups') {
			$this->load->model(PROJECT_CODE.'/model_package_status_board');
			$result_tmp = $this->model_package_status_board->get_ups_tracking_status($args);
			
			$status = $result_tmp['status'];
			$eta = $result_tmp['eta'];
			$actual_delivery_date = $result_tmp['actual_delivery_date'];
		}
		else {
			$process = curl_init();
			curl_setopt( $process, CURLOPT_URL, 'https://shipit-api.herokuapp.com/api/carriers/'.$args['carrier_code'].'/'.$args['track_number']);
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$return = json_decode(curl_exec($process), true);
			curl_close($process);
			
			$status = isset($return['activities'][0]['details']) ? $return['activities'][0]['details'] : '';
			$eta = isset($return['eta']) ? substr($return['eta'],0,10) : '';
			$actual_delivery_date = (strpos(strtolower($status), 'delivered') !== false) ? substr($return['activities'][0]['timestamp'],0,10) : '';
		}
		
		$color = '';
		
		if(empty($eta) && !empty($args['mwe_expected_delivery_date'])) {
			$eta = $args['mwe_expected_delivery_date'];
		}
		
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
			'color' => $color
		);
		
		if(!empty($status)) {
			$prod_db
				->set('status', $status)
				->set('carrier_eta', $eta)
				->set('actual_delivery_date', $actual_delivery_date)
				->set('carrier_data_updated_at', date('Y-m-d H:i:s'))
				->where('carrier_code', $args['carrier_code'])
				->where('track_number', $args['track_number'])
				->update('packages');
		}
		
		return $result;
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
			$carrier_first_scan_datetime = null;
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
			
			if(!empty($status)) {
				$updated_packages[] = array(
					'status' => $status,
					'carrier_eta' => $eta,
					'carrier_first_scan_at' => $carrier_first_scan_datetime,
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
					'carrier_first_scan_at' => $carrier_first_scan_datetime,
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
							$late_days = floor((strtotime($actual_delivery_date) - strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date'])) / 86400);
						}
					}
				}
				else {
					if(!empty($packages_by_package_id[$current_data['package_id']]['expected_delivery_date'])) {
						$expected_delivery_date = date('Y-m-d', strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date']));
						$current_date = date('Y-m-d');
						
						if(strtotime($current_date) > strtotime($expected_delivery_date)) {
							$late_days = floor((strtotime($current_date) - strtotime($packages_by_package_id[$current_data['package_id']]['expected_delivery_date'])) / 86400);
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
	
	public function get_packages_by_month_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : '2013-02-01';
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		$stock_warehouses_tmp = $redstag_db
			->select('stock_id, name')
			->from('cataloginventory_stock')
			->where('stock_id <>', 5) // Don't include Mock Training warehouse
			->get()->result_array();
		
		$stock_warehouse_name_by_id = array();
		foreach($stock_warehouses_tmp as $stock_warehouse) {
			$stock_warehouse_name_by_id[$stock_warehouse['stock_id']] = $stock_warehouse['name'];
		}
		
		$data_template = array();
		$data['stock_ids'] = array();
		foreach($stock_warehouses_tmp as $current_data) {
			$data_template[$current_data['stock_id']] = null;
			$data['stock_ids'][] = $current_data['stock_id'];
		}
		
		$packages_by_month_data_tmp = $redstag_db
			->select("
				YEAR(CONVERT_TZ(created_at,'UTC','US/Eastern')) AS the_year,
				MONTH(CONVERT_TZ(created_at,'UTC','US/Eastern')) AS the_month,
				stock_id,
				COUNT(*) AS num_packages")
			->from('sales_flat_shipment_package')
			->where("CONVERT_TZ(created_at,'UTC','US/Eastern') >= '".$period_from."'", null, false)
			->where("CONVERT_TZ(created_at,'UTC','US/Eastern') <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->group_by('stock_id, the_year, the_month')
			->order_by('stock_id, the_year, the_month')
			->get()->result_array();

		$packages_by_month_data = array();
		foreach($packages_by_month_data_tmp as $current_data) {
			$the_date = sprintf('%02d-%02d-01', $current_data['the_year'], $current_data['the_month']);
			
			if(!isset($packages_by_month_data[$the_date])) {
				$packages_by_month_data[$the_date] = $data_template;
			}
			
			$packages_by_month_data[$the_date][$current_data['stock_id']] = $current_data['num_packages'];
		}
		
		$data['stock_warehouse_name_by_id'] = $stock_warehouse_name_by_id;
		$data['packages_by_month_data'] = $packages_by_month_data;
		
		$data['packages_by_month_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_packages_by_month_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_packages_by_week_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : '2013-02-01';
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		$first_day_of_week = !empty($data['first_day_of_week']) ? $data['first_day_of_week'] : 'sunday';
		$week_grouping = !empty($data['week_grouping']) ? $data['week_grouping'] : 'week_00_to_53';
		
		if(!in_array($first_day_of_week, array('sunday','monday'))) {
			$first_day_of_week = 'sunday';
		}
		
		$stock_warehouses_tmp = $redstag_db
			->select('stock_id, name')
			->from('cataloginventory_stock')
			->where('stock_id <>', 5) // Don't include Mock Training warehouse
			->get()->result_array();
		
		$stock_warehouse_name_by_id = array();
		foreach($stock_warehouses_tmp as $stock_warehouse) {
			$stock_warehouse_name_by_id[$stock_warehouse['stock_id']] = $stock_warehouse['name'];
		}
		
		$data_template = array();
		$data['stock_ids'] = array();
		foreach($stock_warehouses_tmp as $current_data) {
			$data_template[$current_data['stock_id']] = null;
			$data['stock_ids'][] = $current_data['stock_id'];
		}
		
		$sql_date_format = '%Y-%U';
		if($week_grouping == 'week_00_to_53') {
			$sql_date_format = ($first_day_of_week == 'monday') ? '%Y-%u' : '%Y-%U';
		}
		else if($week_grouping == 'week_01_to_53') {
			$sql_date_format = ($first_day_of_week == 'monday') ? '%x-%v' : '%X-%V';
		}
		
		$packages_by_week_data_tmp = $redstag_db
			->select("
				DATE_FORMAT(IF(stock_id IN (3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern')),'".$sql_date_format."') AS the_week,
				stock_id,
				COUNT(*) AS num_packages")
			->from('sales_flat_shipment_package')
			->where("IF(stock_id IN (3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern')) >= '".$period_from."'", null, false)
			->where("IF(stock_id IN (3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern')) < '".date('Y-m-d', strtotime('+1 day '.$period_to))."'", null, false)
			->group_by('stock_id, the_week')
			->order_by('the_week')
			->get()->result_array();

		$packages_by_week_data = array();
		foreach($packages_by_week_data_tmp as $current_data) {
			$the_week = $current_data['the_week'];
			if(!isset($packages_by_week_data[$the_week])) {
				$packages_by_week_data[$the_week] = $data_template;
			}
			
			$packages_by_week_data[$the_week][$current_data['stock_id']] = $current_data['num_packages'];
		}
		
		$data['stock_warehouse_name_by_id'] = $stock_warehouse_name_by_id;
		$data['packages_by_week_data'] = $packages_by_week_data;
		
		// Get week range
		$week_range = $this->db
			->select("DATE_FORMAT(date,'".$sql_date_format."') AS the_week, MIN(date) AS date_from, MAX(date) AS date_to")
			->from('dates')
			->where('date >=', $period_from)
			->where('date <=', $period_to)
			->group_by('the_week')
			->get()->result_array();
		
		$week_range = array_combine(
			array_column($week_range, 'the_week'),
			$week_range
		);
		$data['week_range'] = $week_range;
		
		$data['packages_by_week_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_packages_by_week_board_visualization', $data, true);
		
		
		return $data;
	}
	
	public function update_ontrac_expected_delivery_date() {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$result = array('success' => true);
		
		// Get the latest ontrac package id which already has the postcode
		$latest_package_id_tmp = $prod_db
			->select('MAX(package_id) AS latest_package_id')
			->from('packages')
			->where('carrier_code', 'ontrac')
			->where('expected_delivery_date IS NOT NULL', null, false)
			->get()->result_array();

		$latest_package_id = !empty($latest_package_id_tmp[0]['latest_package_id']) ? $latest_package_id_tmp[0]['latest_package_id'] : 0;
		
		$new_ontrac_packages = $redstag_db
			->select("sales_flat_shipment_package.package_id,
					  sales_flat_order_address.postcode,
					  CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain') AS package_created_at_mt")
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('sales_flat_order_address', 'sales_flat_order_address.entity_id = sales_flat_order.shipping_address_id')
			->where('sales_flat_shipment_package.carrier_code', 'ontrac')
			->where('sales_flat_shipment_package.package_id >', $latest_package_id)
			->get()->result_array();
		
		$synced_ontrac_packages = $prod_db
			->select('package_id')
			->from('packages')
			->where('carrier_code', 'ontrac')
			->where('expected_delivery_date IS NULL', null, false)
			->get()->result_array();
		
		if(empty($synced_ontrac_packages)) {
			return $result;
		}
		
		$synced_ontrac_packages = array_column($synced_ontrac_packages, 'package_id');
		
		$updated_packages = array();
		
		foreach($new_ontrac_packages as $package) {
			if(in_array($package['package_id'], $synced_ontrac_packages)) {
				$five_digits_postcode = substr($package['postcode'],0,5);
				
				$expected_delivery_date = $this->get_ontrac_expected_delivery_date($package['package_created_at_mt'], $five_digits_postcode);
				
				$updated_packages[] = array(
					'package_id' => $package['package_id'],
					'postcode' => $package['postcode'],
					'expected_delivery_date' => $expected_delivery_date
				);
			}
		}

		if(!empty($updated_packages)) {
			$prod_db->trans_start();
			
			$prod_db->update_batch('packages', $updated_packages, 'package_id');
			
			$prod_db->trans_complete();
		}
		
		return $result;
	}
	
	public function get_ontrac_expected_delivery_date($package_created_at_mt, $postcode) {
		$threshold_time = '16:15:00';
		
		$package_created_date = null;
		
		// If package created after threshold time of Mountain Time, the package should be considered created the next day
		if(strtotime(date('H:i:s', strtotime($package_created_at_mt))) > strtotime($threshold_time)) {
			$package_created_date = date('Y-m-d', strtotime('+1 day '.$package_created_at_mt));
		}
		else {
			$package_created_date = date('Y-m-d', strtotime($package_created_at_mt));
		}
		
		// If package created date is on Saturday or Sunday, it should be considered created the next Monday
		$day_code = date('N', strtotime($package_created_date));
		if($day_code >= 6) {
			$package_created_date = date('Y-m-d', strtotime('+'.(8-$day_code).' day '.$package_created_date));
		}
		
		$zip_ref = array(80001=>1,80002=>1,80003=>1,80004=>1,80005=>1,80006=>1,80007=>1,80010=>1,80011=>1,80012=>1,80013=>1,80014=>1,80015=>1,80016=>1,80017=>1,80018=>1,80019=>1,80020=>1,80021=>1,80022=>1,80023=>1,80024=>1,80025=>1,80026=>1,80027=>1,80030=>1,80031=>1,80033=>1,80034=>1,80035=>1,80036=>1,80037=>1,80038=>1,80040=>1,80041=>1,80042=>1,80044=>1,80045=>1,80046=>1,80047=>1,80104=>1,80108=>1,80109=>1,80110=>1,80111=>1,80112=>1,80113=>1,80120=>1,80121=>1,80122=>1,80123=>1,80124=>1,80125=>1,80126=>1,80127=>1,80128=>1,80129=>1,80130=>1,80134=>1,80138=>1,80150=>1,80151=>1,80155=>1,80160=>1,80161=>1,80162=>1,80163=>1,80165=>1,80166=>1,80201=>1,80202=>1,80203=>1,80204=>1,80205=>1,80206=>1,80207=>1,80208=>1,80209=>1,80210=>1,80211=>1,80212=>1,80214=>1,80215=>1,80216=>1,80217=>1,80218=>1,80219=>1,80220=>1,80221=>1,80222=>1,80223=>1,80224=>1,80225=>1,80226=>1,80227=>1,80228=>1,80229=>1,80230=>1,80231=>1,80232=>1,80233=>1,80234=>1,80235=>1,80236=>1,80237=>1,80238=>1,80239=>1,80241=>1,80243=>1,80244=>1,80246=>1,80247=>1,80248=>1,80249=>1,80250=>1,80256=>1,80257=>1,80259=>1,80260=>1,80261=>1,80262=>1,80263=>1,80264=>1,80265=>1,80266=>1,80271=>1,80273=>1,80274=>1,80281=>1,80290=>1,80291=>1,80293=>1,80294=>1,80299=>1,80301=>1,80302=>1,80303=>1,80304=>1,80305=>1,80306=>1,80307=>1,80308=>1,80309=>1,80310=>1,80314=>1,80401=>1,80402=>1,80403=>1,80419=>1,80501=>1,80502=>1,80503=>1,80504=>1,80513=>1,80514=>1,80516=>1,80520=>1,80521=>1,80522=>1,80523=>1,80524=>1,80525=>1,80526=>1,80527=>1,80528=>1,80530=>1,80533=>1,80534=>1,80535=>1,80537=>1,80538=>1,80539=>1,80540=>1,80542=>1,80543=>1,80549=>1,80550=>1,80551=>1,80553=>1,80601=>1,80602=>1,80603=>1,80614=>1,80615=>1,80620=>1,80621=>1,80623=>1,80624=>1,80631=>1,80632=>1,80633=>1,80634=>1,80638=>1,80639=>1,80640=>1,80644=>1,80645=>1,80646=>1,80651=>1,80840=>1,80841=>1,80901=>1,80902=>1,80903=>1,80904=>1,80905=>1,80906=>1,80907=>1,80908=>1,80909=>1,80910=>1,80911=>1,80912=>1,80913=>1,80914=>1,80915=>1,80916=>1,80917=>1,80918=>1,80919=>1,80920=>1,80921=>1,80922=>1,80923=>1,80924=>1,80925=>1,80926=>1,80928=>1,80929=>1,80930=>1,80931=>1,80932=>1,80933=>1,80934=>1,80935=>1,80936=>1,80937=>1,80941=>1,80942=>1,80946=>1,80947=>1,80949=>1,80950=>1,80951=>1,80960=>1,80962=>1,80970=>1,80977=>1,80995=>1,80997=>1,83301=>2,83318=>2,83338=>2,83605=>2,83607=>2,83616=>2,83634=>2,83642=>2,83644=>2,83646=>2,83651=>2,83669=>2,83686=>2,83687=>2,83702=>2,83703=>2,83704=>2,83705=>2,83706=>2,83709=>2,83712=>2,83713=>2,83714=>2,83716=>2,83814=>3,83815=>3,83854=>3,84003=>1,84004=>1,84005=>1,84006=>1,84009=>1,84010=>1,84011=>1,84014=>1,84015=>1,84016=>1,84017=>1,84020=>1,84024=>1,84025=>1,84029=>1,84032=>1,84035=>1,84036=>1,84037=>1,84040=>1,84041=>1,84042=>1,84043=>1,84044=>1,84045=>1,84047=>1,84049=>1,84050=>1,84054=>1,84055=>1,84056=>1,84057=>1,84058=>1,84059=>1,84060=>1,84061=>1,84062=>1,84065=>1,84067=>1,84068=>1,84070=>1,84074=>1,84075=>1,84078=>1,84081=>1,84082=>1,84084=>1,84087=>1,84088=>1,84089=>1,84090=>1,84091=>1,84092=>1,84093=>1,84094=>1,84095=>1,84096=>1,84097=>1,84098=>1,84101=>1,84102=>1,84103=>1,84104=>1,84105=>1,84106=>1,84107=>1,84108=>1,84109=>1,84110=>1,84111=>1,84112=>1,84113=>1,84114=>1,84115=>1,84116=>1,84117=>1,84118=>1,84119=>1,84120=>1,84121=>1,84122=>1,84123=>1,84124=>1,84125=>1,84126=>1,84127=>1,84128=>1,84129=>1,84130=>1,84131=>1,84132=>1,84133=>1,84134=>1,84138=>1,84139=>1,84141=>1,84143=>1,84145=>1,84147=>1,84148=>1,84150=>1,84151=>1,84152=>1,84157=>1,84158=>1,84165=>1,84170=>1,84171=>1,84180=>1,84184=>1,84190=>1,84199=>1,84201=>1,84244=>1,84301=>1,84302=>1,84304=>1,84305=>1,84306=>1,84308=>1,84309=>1,84310=>1,84311=>1,84312=>1,84314=>1,84315=>1,84317=>1,84318=>1,84319=>1,84320=>1,84321=>1,84322=>1,84323=>1,84324=>1,84325=>1,84326=>1,84327=>1,84328=>1,84330=>1,84332=>1,84333=>1,84334=>1,84335=>1,84337=>1,84338=>1,84339=>1,84340=>1,84341=>1,84401=>1,84402=>1,84403=>1,84404=>1,84405=>1,84407=>1,84408=>1,84409=>1,84412=>1,84414=>1,84415=>1,84601=>1,84602=>1,84603=>1,84604=>1,84605=>1,84606=>1,84626=>1,84651=>1,84653=>1,84655=>1,84660=>1,84663=>1,84664=>1,84713=>1,84719=>1,84720=>1,84721=>1,84733=>1,84737=>1,84738=>1,84742=>1,84745=>1,84746=>1,84761=>1,84765=>1,84770=>1,84771=>1,84774=>1,84780=>1,84781=>1,84782=>1,84783=>1,84790=>1,84791=>1,85001=>2,85002=>2,85003=>2,85004=>2,85005=>2,85006=>2,85007=>2,85008=>2,85009=>2,85010=>2,85011=>2,85012=>2,85013=>2,85014=>2,85015=>2,85016=>2,85017=>2,85018=>2,85019=>2,85020=>2,85021=>2,85022=>2,85023=>2,85024=>2,85026=>2,85027=>2,85028=>2,85029=>2,85030=>2,85031=>2,85032=>2,85033=>2,85034=>2,85035=>2,85036=>2,85037=>2,85038=>2,85039=>2,85040=>2,85041=>2,85042=>2,85043=>2,85044=>2,85045=>2,85046=>2,85048=>2,85050=>2,85051=>2,85053=>2,85054=>2,85060=>2,85061=>2,85063=>2,85064=>2,85065=>2,85066=>2,85067=>2,85068=>2,85069=>2,85071=>2,85072=>2,85073=>2,85074=>2,85075=>2,85076=>2,85078=>2,85079=>2,85080=>2,85082=>2,85083=>2,85085=>2,85086=>2,85087=>2,85117=>2,85118=>2,85119=>2,85120=>2,85122=>2,85123=>2,85127=>2,85130=>2,85131=>2,85138=>2,85139=>2,85140=>2,85141=>2,85142=>2,85143=>2,85172=>2,85178=>2,85193=>2,85194=>2,85201=>2,85202=>2,85203=>2,85204=>2,85205=>2,85206=>2,85207=>2,85208=>2,85209=>2,85210=>2,85211=>2,85212=>2,85213=>2,85215=>2,85216=>2,85224=>2,85225=>2,85226=>2,85233=>2,85234=>2,85236=>2,85244=>2,85248=>2,85249=>2,85250=>2,85251=>2,85252=>2,85253=>2,85254=>2,85255=>2,85256=>2,85257=>2,85258=>2,85259=>2,85260=>2,85262=>2,85263=>2,85264=>2,85266=>2,85267=>2,85268=>2,85269=>2,85271=>2,85274=>2,85275=>2,85277=>2,85280=>2,85281=>2,85282=>2,85283=>2,85284=>2,85285=>2,85286=>2,85287=>2,85295=>2,85296=>2,85297=>2,85298=>2,85299=>2,85301=>2,85302=>2,85303=>2,85304=>2,85305=>2,85306=>2,85307=>2,85308=>2,85309=>2,85310=>2,85311=>2,85312=>2,85318=>2,85323=>2,85326=>2,85329=>2,85331=>2,85335=>2,85338=>2,85339=>2,85340=>2,85345=>2,85351=>2,85353=>2,85355=>2,85361=>2,85363=>2,85364=>2,85365=>2,85366=>2,85367=>2,85372=>2,85373=>2,85374=>2,85375=>2,85377=>2,85378=>2,85379=>2,85380=>2,85381=>2,85382=>2,85383=>2,85387=>2,85388=>2,85392=>2,85395=>2,85396=>2,85614=>2,85619=>2,85622=>2,85629=>2,85641=>2,85653=>2,85658=>2,85701=>2,85702=>2,85704=>2,85705=>2,85706=>2,85707=>2,85708=>2,85709=>2,85710=>2,85711=>2,85712=>2,85713=>2,85714=>2,85715=>2,85716=>2,85717=>2,85718=>2,85719=>2,85721=>2,85722=>2,85723=>2,85724=>2,85725=>2,85726=>2,85728=>2,85730=>2,85731=>2,85732=>2,85733=>2,85734=>2,85735=>2,85736=>2,85737=>2,85738=>2,85739=>2,85740=>2,85741=>2,85742=>2,85743=>2,85744=>2,85745=>2,85746=>2,85747=>2,85748=>2,85749=>2,85750=>2,85751=>2,85754=>2,85755=>2,85756=>2,85757=>2,86001=>3,86004=>3,86011=>3,86301=>3,86302=>3,86303=>3,86304=>3,86305=>3,86312=>3,86313=>3,86314=>3,86315=>3,86323=>3,86326=>3,86327=>3,86329=>3,86336=>3,86351=>3,89002=>1,89004=>1,89005=>1,89011=>1,89012=>1,89014=>1,89015=>1,89016=>1,89030=>1,89031=>1,89032=>1,89033=>1,89041=>1,89044=>1,89048=>1,89052=>1,89060=>1,89061=>1,89074=>1,89081=>1,89084=>1,89085=>1,89086=>1,89087=>1,89101=>1,89102=>1,89103=>1,89104=>1,89106=>1,89107=>1,89108=>1,89109=>1,89110=>1,89111=>1,89113=>1,89115=>1,89117=>1,89118=>1,89119=>1,89120=>1,89121=>1,89122=>1,89123=>1,89128=>1,89129=>1,89130=>1,89131=>1,89134=>1,89135=>1,89138=>1,89139=>1,89141=>1,89142=>1,89143=>1,89144=>1,89145=>1,89146=>1,89147=>1,89148=>1,89149=>1,89150=>1,89151=>1,89152=>1,89153=>1,89154=>1,89155=>1,89156=>1,89158=>1,89159=>1,89163=>1,89164=>1,89165=>1,89166=>1,89169=>1,89178=>1,89179=>1,89183=>1,89191=>1,89199=>1,89402=>1,89403=>1,89406=>1,89408=>1,89410=>1,89411=>1,89413=>1,89423=>1,89429=>1,89431=>1,89433=>1,89434=>1,89436=>1,89437=>1,89439=>1,89440=>1,89441=>1,89442=>1,89447=>1,89448=>1,89449=>1,89450=>1,89451=>1,89452=>1,89460=>1,89496=>1,89501=>1,89502=>1,89503=>1,89505=>1,89506=>1,89507=>1,89508=>1,89509=>1,89510=>1,89511=>1,89512=>1,89519=>1,89520=>1,89521=>1,89523=>1,89557=>1,89595=>1,89701=>1,89702=>1,89703=>1,89704=>1,89705=>1,89706=>1,89711=>1,89712=>1,89713=>1,89721=>1,90001=>2,90002=>2,90003=>2,90004=>2,90005=>2,90006=>2,90007=>2,90008=>2,90009=>2,90010=>2,90011=>2,90012=>2,90013=>2,90014=>2,90015=>2,90016=>2,90017=>2,90018=>2,90019=>2,90020=>2,90021=>2,90022=>2,90023=>2,90024=>2,90025=>2,90026=>2,90027=>2,90028=>2,90029=>2,90030=>2,90031=>2,90032=>2,90033=>2,90034=>2,90035=>2,90036=>2,90037=>2,90038=>2,90039=>2,90040=>2,90041=>2,90042=>2,90043=>2,90044=>2,90045=>2,90046=>2,90047=>2,90048=>2,90049=>2,90050=>2,90051=>2,90052=>2,90053=>2,90054=>2,90055=>2,90056=>2,90057=>2,90058=>2,90059=>2,90060=>2,90061=>2,90062=>2,90063=>2,90064=>2,90065=>2,90066=>2,90067=>2,90068=>2,90069=>2,90070=>2,90071=>2,90072=>2,90073=>2,90074=>2,90075=>2,90076=>2,90077=>2,90078=>2,90079=>2,90080=>2,90081=>2,90082=>2,90083=>2,90084=>2,90086=>2,90087=>2,90088=>2,90089=>2,90091=>2,90093=>2,90094=>2,90095=>2,90096=>2,90099=>2,90189=>2,90201=>2,90202=>2,90209=>2,90210=>2,90211=>2,90212=>2,90213=>2,90220=>2,90221=>2,90222=>2,90223=>2,90224=>2,90230=>2,90231=>2,90232=>2,90233=>2,90239=>2,90240=>2,90241=>2,90242=>2,90245=>2,90247=>2,90248=>2,90249=>2,90250=>2,90251=>2,90254=>2,90255=>2,90260=>2,90261=>2,90262=>2,90263=>2,90264=>2,90265=>2,90266=>2,90267=>2,90270=>2,90272=>2,90274=>2,90275=>2,90277=>2,90278=>2,90280=>2,90290=>2,90291=>2,90292=>2,90293=>2,90294=>2,90295=>2,90296=>2,90301=>2,90302=>2,90303=>2,90304=>2,90305=>2,90306=>2,90307=>2,90308=>2,90309=>2,90310=>2,90311=>2,90312=>2,90401=>2,90402=>2,90403=>2,90404=>2,90405=>2,90406=>2,90407=>2,90408=>2,90409=>2,90410=>2,90411=>2,90501=>2,90502=>2,90503=>2,90504=>2,90505=>2,90506=>2,90507=>2,90508=>2,90509=>2,90510=>2,90601=>2,90602=>2,90603=>2,90604=>2,90605=>2,90606=>2,90607=>2,90608=>2,90609=>2,90610=>2,90620=>2,90621=>2,90622=>2,90623=>2,90624=>2,90630=>2,90631=>2,90632=>2,90633=>2,90637=>2,90638=>2,90639=>2,90640=>2,90650=>2,90651=>2,90652=>2,90660=>2,90661=>2,90662=>2,90670=>2,90671=>2,90680=>2,90701=>2,90702=>2,90703=>2,90706=>2,90707=>2,90710=>2,90711=>2,90712=>2,90713=>2,90714=>2,90715=>2,90716=>2,90717=>2,90720=>2,90721=>2,90723=>2,90731=>2,90732=>2,90733=>2,90734=>2,90740=>2,90742=>2,90743=>2,90744=>2,90745=>2,90746=>2,90747=>2,90748=>2,90749=>2,90755=>2,90801=>2,90802=>2,90803=>2,90804=>2,90805=>2,90806=>2,90807=>2,90808=>2,90809=>2,90810=>2,90813=>2,90814=>2,90815=>2,90822=>2,90831=>2,90832=>2,90833=>2,90840=>2,90842=>2,90844=>2,90846=>2,90847=>2,90848=>2,90853=>2,90895=>2,91001=>2,91003=>2,91006=>2,91007=>2,91008=>2,91009=>2,91010=>2,91011=>2,91012=>2,91016=>2,91017=>2,91020=>2,91021=>2,91023=>2,91024=>2,91025=>2,91030=>2,91031=>2,91040=>2,91041=>2,91042=>2,91043=>2,91046=>2,91066=>2,91077=>2,91101=>2,91102=>2,91103=>2,91104=>2,91105=>2,91106=>2,91107=>2,91108=>2,91109=>2,91110=>2,91114=>2,91115=>2,91116=>2,91117=>2,91118=>2,91121=>2,91123=>2,91124=>2,91125=>2,91126=>2,91129=>2,91182=>2,91184=>2,91185=>2,91188=>2,91189=>2,91199=>2,91201=>2,91202=>2,91203=>2,91204=>2,91205=>2,91206=>2,91207=>2,91208=>2,91209=>2,91210=>2,91214=>2,91221=>2,91222=>2,91224=>2,91225=>2,91226=>2,91301=>2,91302=>2,91303=>2,91304=>2,91305=>2,91306=>2,91307=>2,91308=>2,91309=>2,91310=>2,91311=>2,91313=>2,91316=>2,91319=>2,91320=>2,91321=>2,91322=>2,91324=>2,91325=>2,91326=>2,91327=>2,91328=>2,91329=>2,91330=>2,91331=>2,91333=>2,91334=>2,91335=>2,91337=>2,91340=>2,91341=>2,91342=>2,91343=>2,91344=>2,91345=>2,91346=>2,91350=>2,91351=>2,91352=>2,91353=>2,91354=>2,91355=>2,91356=>2,91357=>2,91358=>2,91359=>2,91360=>2,91361=>2,91362=>2,91364=>2,91365=>2,91367=>2,91371=>2,91372=>2,91376=>2,91377=>2,91380=>2,91381=>2,91382=>2,91383=>2,91384=>2,91385=>2,91386=>2,91387=>2,91390=>2,91392=>2,91393=>2,91394=>2,91395=>2,91396=>2,91401=>2,91402=>2,91403=>2,91404=>2,91405=>2,91406=>2,91407=>2,91408=>2,91409=>2,91410=>2,91411=>2,91412=>2,91413=>2,91416=>2,91423=>2,91426=>2,91436=>2,91470=>2,91482=>2,91495=>2,91496=>2,91499=>2,91501=>2,91502=>2,91503=>2,91504=>2,91505=>2,91506=>2,91507=>2,91508=>2,91510=>2,91521=>2,91522=>2,91523=>2,91526=>2,91601=>2,91602=>2,91603=>2,91604=>2,91605=>2,91606=>2,91607=>2,91608=>2,91609=>2,91610=>2,91611=>2,91612=>2,91614=>2,91615=>2,91616=>2,91617=>2,91618=>2,91701=>2,91702=>2,91706=>2,91708=>2,91709=>2,91710=>2,91711=>2,91714=>2,91715=>2,91716=>2,91722=>2,91723=>2,91724=>2,91729=>2,91730=>2,91731=>2,91732=>2,91733=>2,91734=>2,91735=>2,91737=>2,91739=>2,91740=>2,91741=>2,91743=>2,91744=>2,91745=>2,91746=>2,91747=>2,91748=>2,91749=>2,91750=>2,91752=>2,91754=>2,91755=>2,91756=>2,91758=>2,91759=>2,91761=>2,91762=>2,91763=>2,91764=>2,91765=>2,91766=>2,91767=>2,91768=>2,91769=>2,91770=>2,91771=>2,91772=>2,91773=>2,91775=>2,91776=>2,91778=>2,91780=>2,91784=>2,91785=>2,91786=>2,91788=>2,91789=>2,91790=>2,91791=>2,91792=>2,91793=>2,91801=>2,91802=>2,91803=>2,91804=>2,91896=>2,91899=>2,91901=>2,91902=>2,91903=>2,91905=>2,91906=>2,91908=>2,91909=>2,91910=>2,91911=>2,91912=>2,91913=>2,91914=>2,91915=>2,91916=>2,91917=>2,91921=>2,91931=>2,91932=>2,91933=>2,91934=>2,91935=>2,91941=>2,91942=>2,91943=>2,91944=>2,91945=>2,91946=>2,91948=>2,91950=>2,91951=>2,91962=>2,91963=>2,91976=>2,91977=>2,91978=>2,91979=>2,91980=>2,91987=>2,92003=>2,92004=>2,92007=>2,92008=>2,92009=>2,92010=>2,92011=>2,92013=>2,92014=>2,92018=>2,92019=>2,92020=>2,92021=>2,92022=>2,92023=>2,92024=>2,92025=>2,92026=>2,92027=>2,92028=>2,92029=>2,92030=>2,92033=>2,92036=>2,92037=>2,92038=>2,92039=>2,92040=>2,92046=>2,92049=>2,92051=>2,92052=>2,92054=>2,92055=>2,92056=>2,92057=>2,92058=>2,92059=>2,92060=>2,92061=>2,92064=>2,92065=>2,92066=>2,92067=>2,92068=>2,92069=>2,92070=>2,92071=>2,92072=>2,92074=>2,92075=>2,92078=>2,92079=>2,92081=>2,92082=>2,92083=>2,92084=>2,92085=>2,92086=>2,92088=>2,92091=>2,92092=>2,92093=>2,92096=>2,92101=>2,92102=>2,92103=>2,92104=>2,92105=>2,92106=>2,92107=>2,92108=>2,92109=>2,92110=>2,92111=>2,92112=>2,92113=>2,92114=>2,92115=>2,92116=>2,92117=>2,92118=>2,92119=>2,92120=>2,92121=>2,92122=>2,92123=>2,92124=>2,92126=>2,92127=>2,92128=>2,92129=>2,92130=>2,92131=>2,92132=>2,92134=>2,92135=>2,92136=>2,92137=>2,92138=>2,92139=>2,92140=>2,92142=>2,92143=>2,92145=>2,92147=>2,92149=>2,92150=>2,92152=>2,92153=>2,92154=>2,92155=>2,92158=>2,92159=>2,92160=>2,92161=>2,92163=>2,92165=>2,92166=>2,92167=>2,92168=>2,92169=>2,92170=>2,92171=>2,92172=>2,92173=>2,92174=>2,92175=>2,92176=>2,92177=>2,92178=>2,92179=>2,92182=>2,92186=>2,92187=>2,92191=>2,92192=>2,92193=>2,92195=>2,92196=>2,92197=>2,92198=>2,92199=>2,92201=>2,92202=>2,92203=>2,92210=>2,92211=>2,92220=>2,92222=>2,92223=>2,92225=>2,92226=>2,92227=>2,92230=>2,92231=>2,92232=>2,92233=>2,92234=>2,92235=>2,92236=>2,92239=>2,92240=>2,92241=>2,92242=>2,92243=>2,92244=>2,92249=>2,92250=>2,92251=>2,92252=>2,92253=>2,92254=>2,92255=>2,92256=>2,92257=>2,92258=>2,92259=>2,92260=>2,92261=>2,92262=>2,92263=>2,92264=>2,92266=>2,92267=>2,92268=>2,92270=>2,92273=>2,92274=>2,92275=>2,92276=>2,92277=>2,92278=>2,92280=>2,92281=>2,92282=>2,92283=>2,92284=>2,92285=>2,92286=>2,92301=>2,92304=>2,92305=>2,92307=>2,92308=>2,92309=>2,92310=>2,92311=>2,92312=>2,92313=>2,92314=>2,92315=>2,92316=>2,92317=>2,92318=>2,92320=>2,92321=>2,92322=>2,92323=>2,92324=>2,92325=>2,92327=>2,92328=>1,92329=>2,92332=>2,92333=>2,92334=>2,92335=>2,92336=>2,92337=>2,92338=>2,92339=>2,92340=>2,92341=>2,92342=>2,92344=>2,92345=>2,92346=>2,92347=>2,92350=>2,92352=>2,92354=>2,92356=>2,92357=>2,92358=>2,92359=>2,92363=>2,92364=>2,92365=>2,92366=>2,92368=>2,92369=>2,92371=>2,92372=>2,92373=>2,92374=>2,92375=>2,92376=>2,92377=>2,92378=>2,92382=>2,92384=>1,92385=>2,92386=>2,92389=>1,92391=>2,92392=>2,92393=>2,92394=>2,92395=>2,92397=>2,92398=>2,92399=>2,92401=>2,92402=>2,92403=>2,92404=>2,92405=>2,92406=>2,92407=>2,92408=>2,92410=>2,92411=>2,92413=>2,92415=>2,92418=>2,92423=>2,92427=>2,92501=>2,92502=>2,92503=>2,92504=>2,92505=>2,92506=>2,92507=>2,92508=>2,92509=>2,92513=>2,92514=>2,92516=>2,92517=>2,92518=>2,92519=>2,92521=>2,92522=>2,92530=>2,92531=>2,92532=>2,92536=>2,92539=>2,92543=>2,92544=>2,92545=>2,92546=>2,92548=>2,92549=>2,92551=>2,92552=>2,92553=>2,92554=>2,92555=>2,92556=>2,92557=>2,92561=>2,92562=>2,92563=>2,92564=>2,92567=>2,92570=>2,92571=>2,92572=>2,92581=>2,92582=>2,92583=>2,92584=>2,92585=>2,92586=>2,92587=>2,92589=>2,92590=>2,92591=>2,92592=>2,92593=>2,92595=>2,92596=>2,92599=>2,92602=>2,92603=>2,92604=>2,92605=>2,92606=>2,92607=>2,92609=>2,92610=>2,92612=>2,92614=>2,92615=>2,92616=>2,92617=>2,92618=>2,92619=>2,92620=>2,92623=>2,92624=>2,92625=>2,92626=>2,92627=>2,92628=>2,92629=>2,92630=>2,92637=>2,92646=>2,92647=>2,92648=>2,92649=>2,92650=>2,92651=>2,92652=>2,92653=>2,92654=>2,92655=>2,92656=>2,92657=>2,92658=>2,92659=>2,92660=>2,92661=>2,92662=>2,92663=>2,92672=>2,92673=>2,92674=>2,92675=>2,92676=>2,92677=>2,92678=>2,92679=>2,92683=>2,92684=>2,92685=>2,92688=>2,92690=>2,92691=>2,92692=>2,92693=>2,92694=>2,92697=>2,92698=>2,92701=>2,92702=>2,92703=>2,92704=>2,92705=>2,92706=>2,92707=>2,92708=>2,92711=>2,92712=>2,92728=>2,92735=>2,92780=>2,92781=>2,92782=>2,92799=>2,92801=>2,92802=>2,92803=>2,92804=>2,92805=>2,92806=>2,92807=>2,92808=>2,92809=>2,92811=>2,92812=>2,92814=>2,92815=>2,92816=>2,92817=>2,92821=>2,92822=>2,92823=>2,92825=>2,92831=>2,92832=>2,92833=>2,92834=>2,92835=>2,92836=>2,92837=>2,92838=>2,92840=>2,92841=>2,92842=>2,92843=>2,92844=>2,92845=>2,92846=>2,92850=>2,92856=>2,92857=>2,92859=>2,92860=>2,92861=>2,92862=>2,92863=>2,92864=>2,92865=>2,92866=>2,92867=>2,92868=>2,92869=>2,92870=>2,92871=>2,92877=>2,92878=>2,92879=>2,92880=>2,92881=>2,92882=>2,92883=>2,92885=>2,92886=>2,92887=>2,92899=>2,93001=>2,93002=>2,93003=>2,93004=>2,93005=>2,93006=>2,93007=>2,93009=>2,93010=>2,93011=>2,93012=>2,93013=>2,93014=>2,93015=>2,93016=>2,93020=>2,93021=>2,93022=>2,93023=>2,93024=>2,93030=>2,93031=>2,93032=>2,93033=>2,93034=>2,93035=>2,93036=>2,93040=>2,93041=>2,93042=>2,93043=>2,93044=>2,93060=>2,93061=>2,93062=>2,93063=>2,93064=>2,93065=>2,93066=>2,93067=>2,93094=>2,93099=>2,93101=>2,93102=>2,93103=>2,93105=>2,93106=>2,93107=>2,93108=>2,93109=>2,93110=>2,93111=>2,93116=>2,93117=>2,93118=>2,93120=>2,93121=>2,93130=>2,93140=>2,93150=>2,93160=>2,93190=>2,93199=>2,93201=>2,93202=>2,93203=>2,93204=>2,93205=>2,93206=>2,93207=>2,93208=>2,93210=>2,93212=>2,93215=>2,93216=>2,93218=>2,93219=>2,93220=>2,93221=>2,93222=>2,93223=>2,93224=>2,93225=>2,93226=>2,93227=>2,93230=>2,93232=>2,93234=>2,93235=>2,93237=>2,93238=>2,93239=>2,93240=>2,93241=>2,93242=>2,93243=>2,93244=>2,93245=>2,93246=>2,93247=>2,93249=>2,93250=>2,93251=>2,93252=>2,93254=>2,93255=>2,93256=>2,93257=>2,93258=>2,93260=>2,93261=>2,93262=>2,93263=>2,93265=>2,93266=>2,93267=>2,93268=>2,93270=>2,93271=>2,93272=>2,93274=>2,93275=>2,93276=>2,93277=>2,93278=>2,93279=>2,93280=>2,93282=>2,93283=>2,93285=>2,93286=>2,93287=>2,93290=>2,93291=>2,93292=>2,93301=>2,93302=>2,93303=>2,93304=>2,93305=>2,93306=>2,93307=>2,93308=>2,93309=>2,93311=>2,93312=>2,93313=>2,93314=>2,93380=>2,93383=>2,93384=>2,93385=>2,93386=>2,93387=>2,93388=>2,93389=>2,93390=>2,93401=>2,93402=>2,93403=>2,93405=>2,93406=>2,93407=>2,93408=>2,93409=>2,93410=>2,93412=>2,93420=>2,93421=>2,93422=>2,93423=>2,93424=>2,93426=>2,93427=>2,93428=>2,93429=>2,93430=>2,93432=>2,93433=>2,93434=>2,93435=>2,93436=>2,93437=>2,93438=>2,93440=>2,93441=>2,93442=>2,93443=>2,93444=>2,93445=>2,93446=>2,93447=>2,93448=>2,93449=>2,93450=>2,93451=>2,93452=>2,93453=>2,93454=>2,93455=>2,93456=>2,93457=>2,93458=>2,93460=>2,93461=>2,93463=>2,93464=>2,93465=>2,93483=>2,93501=>2,93502=>2,93504=>2,93505=>2,93510=>2,93512=>2,93513=>2,93514=>2,93515=>2,93516=>2,93517=>1,93518=>2,93519=>2,93522=>2,93523=>2,93524=>2,93526=>2,93527=>2,93528=>2,93529=>2,93530=>2,93531=>2,93532=>2,93534=>2,93535=>2,93536=>2,93539=>2,93541=>1,93542=>2,93543=>2,93544=>2,93545=>2,93546=>2,93549=>2,93550=>2,93551=>2,93552=>2,93553=>2,93554=>2,93555=>2,93556=>2,93558=>2,93560=>2,93561=>2,93562=>2,93563=>2,93581=>2,93584=>2,93586=>2,93590=>2,93591=>2,93592=>2,93596=>2,93599=>2,93601=>2,93602=>2,93603=>2,93604=>2,93605=>2,93606=>2,93607=>2,93608=>2,93609=>2,93610=>2,93611=>2,93612=>2,93613=>2,93614=>2,93615=>2,93616=>2,93618=>2,93619=>2,93620=>2,93621=>2,93622=>2,93623=>2,93624=>2,93625=>2,93626=>2,93627=>2,93628=>2,93630=>2,93631=>2,93633=>2,93634=>2,93635=>2,93636=>2,93637=>2,93638=>2,93639=>2,93640=>2,93641=>2,93642=>2,93643=>2,93644=>2,93645=>2,93646=>2,93647=>2,93648=>2,93649=>2,93650=>2,93651=>2,93652=>2,93653=>2,93654=>2,93656=>2,93657=>2,93660=>2,93661=>2,93662=>2,93664=>2,93665=>2,93666=>2,93667=>2,93668=>2,93669=>2,93670=>2,93673=>2,93675=>2,93701=>2,93702=>2,93703=>2,93704=>2,93705=>2,93706=>2,93707=>2,93708=>2,93709=>2,93710=>2,93711=>2,93712=>2,93714=>2,93715=>2,93716=>2,93717=>2,93718=>2,93720=>2,93721=>2,93722=>2,93723=>2,93724=>2,93725=>2,93726=>2,93727=>2,93728=>2,93729=>2,93730=>2,93737=>2,93740=>2,93741=>2,93744=>2,93745=>2,93747=>2,93750=>2,93755=>2,93760=>2,93761=>2,93764=>2,93765=>2,93771=>2,93772=>2,93773=>2,93774=>2,93775=>2,93776=>2,93777=>2,93778=>2,93779=>2,93786=>2,93790=>2,93791=>2,93792=>2,93793=>2,93794=>2,93844=>2,93888=>2,93901=>2,93902=>2,93905=>2,93906=>2,93907=>2,93908=>2,93912=>2,93915=>2,93920=>2,93921=>2,93922=>2,93923=>2,93924=>2,93925=>2,93926=>2,93927=>2,93928=>2,93930=>2,93932=>2,93933=>2,93940=>2,93942=>2,93943=>2,93944=>2,93950=>2,93953=>2,93954=>2,93955=>2,93960=>2,93962=>2,94002=>2,94005=>2,94010=>2,94011=>2,94014=>2,94015=>2,94016=>2,94017=>2,94018=>2,94019=>2,94020=>2,94021=>2,94022=>2,94023=>2,94024=>2,94025=>2,94026=>2,94027=>2,94028=>2,94030=>2,94035=>2,94037=>2,94038=>2,94039=>2,94040=>2,94041=>2,94042=>2,94043=>2,94044=>2,94060=>2,94061=>2,94062=>2,94063=>2,94064=>2,94065=>2,94066=>2,94070=>2,94074=>2,94080=>2,94083=>2,94085=>2,94086=>2,94087=>2,94088=>2,94089=>2,94102=>2,94103=>2,94104=>2,94105=>2,94107=>2,94108=>2,94109=>2,94110=>2,94111=>2,94112=>2,94114=>2,94115=>2,94116=>2,94117=>2,94118=>2,94119=>2,94120=>2,94121=>2,94122=>2,94123=>2,94124=>2,94125=>2,94126=>2,94127=>2,94128=>2,94129=>2,94130=>2,94131=>2,94132=>2,94133=>2,94134=>2,94137=>2,94139=>2,94140=>2,94141=>2,94142=>2,94143=>2,94144=>2,94145=>2,94146=>2,94147=>2,94151=>2,94158=>2,94159=>2,94160=>2,94161=>2,94163=>2,94164=>2,94172=>2,94177=>2,94188=>2,94203=>2,94204=>2,94205=>2,94206=>2,94207=>2,94208=>2,94211=>2,94229=>2,94230=>2,94232=>2,94234=>2,94235=>2,94236=>2,94237=>2,94239=>2,94240=>2,94244=>2,94245=>2,94247=>2,94248=>2,94249=>2,94250=>2,94252=>2,94254=>2,94256=>2,94257=>2,94258=>2,94259=>2,94261=>2,94262=>2,94263=>2,94267=>2,94268=>2,94269=>2,94271=>2,94273=>2,94274=>2,94277=>2,94278=>2,94279=>2,94280=>2,94282=>2,94283=>2,94284=>2,94285=>2,94287=>2,94288=>2,94289=>2,94290=>2,94291=>2,94293=>2,94294=>2,94295=>2,94296=>2,94297=>2,94298=>2,94299=>2,94301=>2,94302=>2,94303=>2,94304=>2,94305=>2,94306=>2,94309=>2,94401=>2,94402=>2,94403=>2,94404=>2,94497=>2,94501=>2,94502=>2,94503=>2,94505=>2,94506=>2,94507=>2,94508=>2,94509=>2,94510=>2,94511=>2,94512=>2,94513=>2,94514=>2,94515=>2,94516=>2,94517=>2,94518=>2,94519=>2,94520=>2,94521=>2,94522=>2,94523=>2,94524=>2,94525=>2,94526=>2,94527=>2,94528=>2,94529=>2,94530=>2,94531=>2,94533=>2,94534=>2,94535=>2,94536=>2,94537=>2,94538=>2,94539=>2,94540=>2,94541=>2,94542=>2,94543=>2,94544=>2,94545=>2,94546=>2,94547=>2,94548=>2,94549=>2,94550=>2,94551=>2,94552=>2,94553=>2,94555=>2,94556=>2,94557=>2,94558=>2,94559=>2,94560=>2,94561=>2,94562=>2,94563=>2,94564=>2,94565=>2,94566=>2,94567=>2,94568=>2,94569=>2,94570=>2,94571=>2,94572=>2,94573=>2,94574=>2,94575=>2,94576=>2,94577=>2,94578=>2,94579=>2,94580=>2,94581=>2,94582=>2,94583=>2,94585=>2,94586=>2,94587=>2,94588=>2,94589=>2,94590=>2,94591=>2,94592=>2,94595=>2,94596=>2,94597=>2,94598=>2,94599=>2,94601=>2,94602=>2,94603=>2,94604=>2,94605=>2,94606=>2,94607=>2,94608=>2,94609=>2,94610=>2,94611=>2,94612=>2,94613=>2,94614=>2,94615=>2,94617=>2,94618=>2,94619=>2,94620=>2,94621=>2,94622=>2,94623=>2,94624=>2,94649=>2,94659=>2,94660=>2,94661=>2,94662=>2,94666=>2,94701=>2,94702=>2,94703=>2,94704=>2,94705=>2,94706=>2,94707=>2,94708=>2,94709=>2,94710=>2,94712=>2,94720=>2,94801=>2,94802=>2,94803=>2,94804=>2,94805=>2,94806=>2,94807=>2,94808=>2,94820=>2,94850=>2,94901=>2,94903=>2,94904=>2,94912=>2,94913=>2,94914=>2,94915=>2,94920=>2,94922=>2,94923=>2,94924=>2,94925=>2,94926=>2,94927=>2,94928=>2,94929=>2,94930=>2,94931=>2,94933=>2,94937=>2,94938=>2,94939=>2,94940=>2,94941=>2,94942=>2,94945=>2,94946=>2,94947=>2,94948=>2,94949=>2,94950=>2,94951=>2,94952=>2,94953=>2,94954=>2,94955=>2,94956=>2,94957=>2,94960=>2,94963=>2,94964=>2,94965=>2,94966=>2,94970=>2,94971=>2,94972=>2,94973=>2,94974=>2,94975=>2,94976=>2,94977=>2,94978=>2,94979=>2,94998=>2,94999=>2,95001=>2,95002=>2,95003=>2,95004=>2,95005=>2,95006=>2,95007=>2,95008=>2,95009=>2,95010=>2,95011=>2,95012=>2,95013=>2,95014=>2,95015=>2,95017=>2,95018=>2,95019=>2,95020=>2,95021=>2,95023=>2,95024=>2,95026=>2,95030=>2,95031=>2,95032=>2,95033=>2,95035=>2,95036=>2,95037=>2,95038=>2,95039=>2,95041=>2,95042=>2,95043=>2,95044=>2,95045=>2,95046=>2,95050=>2,95051=>2,95052=>2,95053=>2,95054=>2,95055=>2,95056=>2,95060=>2,95061=>2,95062=>2,95063=>2,95064=>2,95065=>2,95066=>2,95067=>2,95070=>2,95071=>2,95073=>2,95075=>2,95076=>2,95077=>2,95101=>2,95103=>2,95106=>2,95108=>2,95109=>2,95110=>2,95111=>2,95112=>2,95113=>2,95115=>2,95116=>2,95117=>2,95118=>2,95119=>2,95120=>2,95121=>2,95122=>2,95123=>2,95124=>2,95125=>2,95126=>2,95127=>2,95128=>2,95129=>2,95130=>2,95131=>2,95132=>2,95133=>2,95134=>2,95135=>2,95136=>2,95138=>2,95139=>2,95140=>2,95141=>2,95148=>2,95150=>2,95151=>2,95152=>2,95153=>2,95154=>2,95155=>2,95156=>2,95157=>2,95158=>2,95159=>2,95160=>2,95161=>2,95164=>2,95170=>2,95172=>2,95173=>2,95190=>2,95191=>2,95192=>2,95193=>2,95194=>2,95196=>2,95201=>2,95202=>2,95203=>2,95204=>2,95205=>2,95206=>2,95207=>2,95208=>2,95209=>2,95210=>2,95211=>2,95212=>2,95213=>2,95215=>2,95219=>2,95220=>2,95221=>2,95222=>2,95223=>2,95224=>2,95225=>2,95226=>2,95227=>2,95228=>2,95229=>2,95230=>2,95231=>2,95232=>2,95233=>2,95234=>2,95236=>2,95237=>2,95240=>2,95241=>2,95242=>2,95245=>2,95246=>2,95247=>2,95248=>2,95249=>2,95251=>2,95252=>2,95253=>2,95254=>2,95255=>2,95257=>2,95258=>2,95267=>2,95269=>2,95296=>2,95297=>2,95301=>2,95303=>2,95304=>2,95305=>2,95306=>2,95307=>2,95309=>2,95310=>2,95311=>2,95312=>2,95313=>2,95315=>2,95316=>2,95317=>2,95318=>2,95319=>2,95320=>2,95321=>2,95322=>2,95323=>2,95324=>2,95325=>2,95326=>2,95327=>2,95328=>2,95329=>2,95330=>2,95333=>2,95334=>2,95335=>2,95336=>2,95337=>2,95338=>2,95340=>2,95341=>2,95343=>2,95344=>2,95345=>2,95346=>2,95347=>2,95348=>2,95350=>2,95351=>2,95352=>2,95353=>2,95354=>2,95355=>2,95356=>2,95357=>2,95358=>2,95360=>2,95361=>2,95363=>2,95364=>2,95365=>2,95366=>2,95367=>2,95368=>2,95369=>2,95370=>2,95372=>2,95373=>2,95374=>2,95375=>2,95376=>2,95377=>2,95378=>2,95379=>2,95380=>2,95381=>2,95382=>2,95383=>2,95385=>2,95386=>2,95387=>2,95388=>2,95389=>2,95391=>2,95397=>2,95401=>2,95402=>2,95403=>2,95404=>2,95405=>2,95406=>2,95407=>2,95409=>2,95410=>2,95412=>2,95415=>2,95416=>2,95417=>2,95418=>2,95419=>2,95420=>2,95421=>2,95422=>2,95423=>2,95424=>2,95425=>2,95426=>2,95427=>2,95428=>2,95429=>2,95430=>2,95431=>2,95432=>2,95433=>2,95435=>2,95436=>2,95437=>2,95439=>2,95441=>2,95442=>2,95443=>2,95444=>2,95445=>2,95446=>2,95448=>2,95449=>2,95450=>2,95451=>2,95452=>2,95453=>2,95454=>2,95456=>2,95457=>2,95458=>2,95459=>2,95460=>2,95461=>2,95462=>2,95463=>2,95464=>2,95465=>2,95466=>2,95467=>2,95468=>2,95469=>2,95470=>2,95471=>2,95472=>2,95473=>2,95476=>2,95480=>2,95481=>2,95482=>2,95485=>2,95486=>2,95487=>2,95488=>2,95490=>2,95492=>2,95493=>2,95494=>2,95497=>2,95501=>2,95502=>2,95503=>2,95511=>2,95514=>2,95518=>2,95519=>2,95521=>2,95524=>2,95525=>2,95526=>2,95527=>2,95528=>2,95531=>2,95532=>2,95534=>2,95536=>2,95537=>2,95538=>2,95540=>2,95542=>2,95543=>2,95545=>2,95546=>2,95547=>2,95548=>2,95549=>2,95550=>2,95551=>2,95552=>2,95553=>2,95554=>2,95555=>2,95556=>2,95558=>2,95559=>2,95560=>2,95562=>2,95563=>2,95564=>2,95565=>2,95567=>2,95568=>2,95569=>2,95570=>2,95571=>2,95573=>2,95585=>2,95587=>2,95589=>2,95595=>2,95601=>2,95602=>2,95603=>2,95604=>2,95605=>2,95606=>2,95607=>2,95608=>2,95609=>2,95610=>2,95611=>2,95612=>2,95613=>2,95614=>2,95615=>2,95616=>2,95617=>2,95618=>2,95619=>2,95620=>2,95621=>2,95623=>2,95624=>2,95625=>2,95626=>2,95627=>2,95628=>2,95629=>2,95630=>2,95631=>2,95632=>2,95633=>2,95634=>2,95635=>2,95636=>2,95637=>2,95638=>2,95639=>2,95640=>2,95641=>2,95642=>2,95644=>2,95645=>2,95646=>1,95648=>2,95650=>2,95651=>2,95652=>2,95653=>2,95654=>2,95655=>2,95656=>2,95658=>2,95659=>2,95660=>2,95661=>2,95662=>2,95663=>2,95664=>2,95665=>2,95666=>2,95667=>2,95668=>2,95669=>2,95670=>2,95671=>2,95672=>2,95673=>2,95674=>2,95675=>2,95676=>2,95677=>2,95678=>2,95679=>2,95680=>2,95681=>2,95682=>2,95683=>2,95684=>2,95685=>2,95686=>2,95687=>2,95688=>2,95689=>2,95690=>2,95691=>2,95692=>2,95693=>2,95694=>2,95695=>2,95696=>2,95697=>2,95698=>2,95699=>2,95701=>2,95703=>2,95709=>2,95712=>2,95713=>2,95714=>2,95715=>1,95717=>2,95720=>2,95721=>1,95722=>2,95724=>1,95726=>2,95728=>1,95735=>1,95736=>2,95741=>2,95742=>2,95746=>2,95747=>2,95757=>2,95758=>2,95759=>2,95762=>2,95763=>2,95765=>2,95776=>2,95798=>2,95799=>2,95811=>2,95812=>2,95813=>2,95814=>2,95815=>2,95816=>2,95817=>2,95818=>2,95819=>2,95820=>2,95821=>2,95822=>2,95823=>2,95824=>2,95825=>2,95826=>2,95827=>2,95828=>2,95829=>2,95830=>2,95831=>2,95832=>2,95833=>2,95834=>2,95835=>2,95836=>2,95837=>2,95838=>2,95840=>2,95841=>2,95842=>2,95843=>2,95851=>2,95852=>2,95853=>2,95860=>2,95864=>2,95865=>2,95866=>2,95867=>2,95894=>2,95899=>2,95901=>2,95903=>2,95910=>2,95912=>2,95913=>2,95914=>2,95915=>1,95916=>2,95917=>2,95918=>2,95919=>2,95920=>2,95922=>2,95923=>1,95924=>2,95925=>2,95926=>2,95927=>2,95928=>2,95929=>2,95930=>2,95932=>2,95934=>1,95935=>2,95936=>2,95937=>2,95938=>2,95939=>2,95940=>2,95941=>2,95942=>2,95943=>2,95944=>2,95945=>2,95946=>2,95947=>1,95948=>2,95949=>2,95950=>2,95951=>2,95953=>2,95954=>2,95955=>2,95956=>1,95957=>2,95958=>2,95959=>2,95960=>2,95961=>2,95962=>2,95963=>2,95965=>2,95966=>2,95967=>2,95968=>2,95969=>2,95970=>2,95971=>1,95972=>2,95973=>2,95974=>2,95975=>2,95976=>2,95977=>2,95978=>2,95979=>2,95980=>1,95981=>2,95982=>2,95983=>1,95984=>1,95986=>2,95987=>2,95988=>2,95991=>2,95992=>2,95993=>2,96001=>2,96002=>2,96003=>2,96006=>2,96007=>2,96008=>2,96009=>2,96010=>2,96011=>2,96013=>2,96014=>2,96015=>2,96016=>2,96017=>2,96019=>2,96020=>1,96021=>2,96022=>2,96023=>2,96024=>2,96025=>2,96027=>2,96028=>2,96029=>2,96031=>2,96032=>2,96033=>2,96034=>2,96035=>2,96037=>2,96038=>2,96039=>2,96040=>2,96041=>2,96044=>2,96046=>2,96047=>2,96048=>2,96049=>2,96050=>2,96051=>2,96052=>2,96054=>2,96055=>2,96056=>2,96057=>2,96058=>2,96059=>2,96061=>2,96062=>2,96063=>2,96064=>2,96065=>2,96067=>2,96068=>2,96069=>2,96070=>2,96071=>2,96073=>2,96074=>2,96075=>2,96076=>2,96078=>2,96079=>2,96080=>2,96084=>2,96085=>2,96086=>2,96087=>2,96088=>2,96089=>2,96090=>2,96091=>2,96092=>2,96093=>2,96094=>2,96095=>2,96096=>2,96097=>2,96099=>2,96101=>2,96103=>1,96104=>2,96105=>1,96106=>1,96107=>1,96108=>2,96109=>1,96110=>2,96111=>1,96112=>2,96113=>1,96114=>1,96115=>2,96116=>2,96117=>1,96118=>1,96119=>2,96120=>1,96121=>1,96122=>1,96123=>1,96124=>1,96125=>1,96126=>1,96127=>1,96128=>1,96129=>1,96130=>1,96132=>1,96133=>1,96134=>2,96135=>1,96136=>1,96137=>1,96140=>1,96141=>1,96142=>1,96143=>1,96145=>1,96146=>1,96148=>1,96150=>1,96151=>1,96152=>1,96154=>1,96155=>1,96156=>1,96157=>1,96158=>1,96160=>1,96161=>1,96162=>1,97002=>2,97003=>2,97004=>2,97005=>2,97006=>2,97007=>2,97008=>2,97009=>2,97013=>2,97015=>2,97022=>2,97023=>2,97024=>2,97027=>2,97030=>2,97031=>2,97034=>2,97035=>2,97036=>2,97038=>2,97042=>2,97045=>2,97051=>2,97053=>2,97055=>2,97056=>2,97058=>2,97060=>2,97062=>2,97068=>2,97070=>2,97071=>2,97075=>2,97076=>2,97077=>2,97078=>2,97079=>2,97080=>2,97086=>2,97089=>2,97106=>2,97111=>2,97113=>2,97114=>2,97115=>2,97116=>2,97117=>2,97119=>2,97123=>2,97124=>2,97125=>2,97127=>2,97128=>2,97132=>2,97133=>2,97140=>2,97148=>2,97201=>2,97202=>2,97203=>2,97204=>2,97205=>2,97206=>2,97208=>2,97209=>2,97210=>2,97211=>2,97212=>2,97213=>2,97214=>2,97215=>2,97216=>2,97217=>2,97218=>2,97219=>2,97220=>2,97221=>2,97222=>2,97223=>2,97224=>2,97225=>2,97227=>2,97228=>2,97229=>2,97230=>2,97231=>2,97232=>2,97233=>2,97236=>2,97238=>2,97239=>2,97240=>2,97242=>2,97250=>2,97251=>2,97252=>2,97253=>2,97254=>2,97258=>2,97266=>2,97267=>2,97268=>2,97269=>2,97280=>2,97281=>2,97282=>2,97283=>2,97286=>2,97290=>2,97291=>2,97292=>2,97293=>2,97294=>2,97296=>2,97298=>2,97301=>2,97302=>2,97303=>2,97304=>2,97305=>2,97306=>2,97308=>2,97309=>2,97310=>2,97314=>2,97317=>2,97321=>2,97322=>2,97330=>2,97331=>2,97333=>2,97338=>2,97339=>2,97351=>2,97355=>2,97361=>2,97370=>2,97381=>2,97401=>2,97402=>2,97403=>2,97404=>2,97405=>2,97408=>2,97417=>2,97424=>2,97426=>2,97431=>2,97455=>2,97470=>2,97471=>2,97477=>2,97478=>2,97479=>2,97486=>2,97501=>2,97502=>2,97503=>2,97504=>2,97520=>2,97524=>2,97525=>2,97526=>2,97527=>2,97528=>2,97530=>2,97535=>2,97537=>2,97538=>2,97540=>2,97543=>2,97544=>2,97601=>2,97602=>2,97603=>2,97625=>2,97627=>2,97632=>2,97633=>2,97634=>2,97701=>2,97702=>2,97703=>2,97707=>2,97708=>2,97709=>2,97739=>2,97741=>2,97756=>2,97759=>2,97760=>2,98001=>2,98002=>2,98003=>2,98004=>2,98005=>2,98006=>2,98007=>2,98008=>2,98010=>2,98011=>2,98012=>2,98014=>2,98019=>2,98020=>2,98021=>2,98022=>2,98023=>2,98024=>2,98026=>2,98027=>2,98028=>2,98029=>2,98030=>2,98031=>2,98032=>2,98033=>2,98034=>2,98036=>2,98037=>2,98038=>2,98039=>2,98040=>2,98042=>2,98043=>2,98045=>2,98046=>2,98047=>2,98052=>2,98053=>2,98055=>2,98056=>2,98057=>2,98058=>2,98059=>2,98063=>2,98065=>2,98072=>2,98074=>2,98075=>2,98077=>2,98087=>2,98092=>2,98101=>2,98102=>2,98103=>2,98104=>2,98105=>2,98106=>2,98107=>2,98108=>2,98109=>2,98110=>2,98111=>2,98112=>2,98115=>2,98116=>2,98117=>2,98118=>2,98119=>2,98121=>2,98122=>2,98125=>2,98126=>2,98131=>2,98133=>2,98134=>2,98136=>2,98144=>2,98146=>2,98148=>2,98154=>2,98155=>2,98158=>2,98161=>2,98164=>2,98166=>2,98168=>2,98174=>2,98177=>2,98178=>2,98188=>2,98195=>2,98198=>2,98199=>2,98201=>2,98203=>2,98204=>2,98207=>2,98208=>2,98213=>2,98223=>2,98225=>2,98226=>2,98229=>2,98233=>2,98258=>2,98270=>2,98271=>2,98272=>2,98273=>2,98274=>2,98275=>2,98284=>2,98290=>2,98291=>2,98294=>2,98296=>2,98310=>2,98311=>2,98312=>2,98314=>2,98315=>2,98327=>2,98329=>2,98332=>2,98335=>2,98337=>2,98338=>2,98354=>2,98366=>2,98367=>2,98370=>2,98371=>2,98372=>2,98373=>2,98374=>2,98375=>2,98383=>2,98387=>2,98388=>2,98390=>2,98391=>2,98392=>2,98402=>2,98403=>2,98404=>2,98405=>2,98406=>2,98407=>2,98408=>2,98409=>2,98413=>2,98415=>2,98416=>2,98418=>2,98421=>2,98422=>2,98424=>2,98431=>2,98433=>2,98438=>2,98443=>2,98444=>2,98445=>2,98446=>2,98447=>2,98465=>2,98466=>2,98467=>2,98493=>2,98498=>2,98499=>2,98501=>2,98502=>2,98503=>2,98505=>2,98506=>2,98512=>2,98513=>2,98516=>2,98531=>2,98532=>2,98579=>2,98589=>2,98591=>2,98596=>2,98604=>2,98606=>2,98607=>2,98625=>2,98626=>2,98629=>2,98632=>2,98642=>2,98660=>2,98661=>2,98662=>2,98663=>2,98664=>2,98665=>2,98668=>2,98671=>2,98674=>2,98682=>2,98683=>2,98684=>2,98685=>2,98686=>2,98687=>2,98901=>3,98902=>3,98903=>3,98908=>3,99001=>3,99005=>3,99019=>3,99021=>3,99027=>3,99037=>3,99201=>3,99202=>3,99203=>3,99204=>3,99205=>3,99206=>3,99207=>3,99208=>3,99209=>3,99210=>3,99211=>3,99212=>3,99213=>3,99214=>3,99215=>3,99216=>3,99217=>3,99218=>3,99220=>3,99223=>3,99224=>3,99301=>3,99336=>3,99337=>3,99338=>3,99352=>3,99353=>3,99354=>3);
		
		$transit_day = isset($zip_ref[$postcode]) ? $zip_ref[$postcode] : 2;
		$additional_day = 0; // additional day because of weekend
		
		$day_code = date('N', strtotime($package_created_date));
		if(
			($day_code == 3 && $transit_day >= 3) ||
			($day_code == 4 && $transit_day >= 2) ||
			($day_code == 5 && $transit_day >= 1) ||
			$day_code >= 6
		) {
			$additional_day = 2;
		}
		
		$expected_delivery_date = date('Y-m-d', strtotime('+'.($transit_day+$additional_day).' day '.$package_created_date));
		
		return $expected_delivery_date;
	}
	
	public function get_packages_by_date_location_carrier_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : '2013-02-01';
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		$stock_warehouses_tmp = $redstag_db
			->select('stock_id, name')
			->from('cataloginventory_stock')
			->where('stock_id <>', 5) // Don't include Mock Training warehouse
			->get()->result_array();
		
		$stock_warehouse_name_by_id = array();
		foreach($stock_warehouses_tmp as $stock_warehouse) {
			$stock_warehouse_name_by_id[$stock_warehouse['stock_id']] = $stock_warehouse['name'];
		}
		
		$data_template = array();
		$data['stock_ids'] = array();
		foreach($stock_warehouses_tmp as $current_data) {
			$data_template[$current_data['stock_id']] = null;
			$data['stock_ids'][] = $current_data['stock_id'];
		}
		
		// Get carrier
		$carrier_list_tmp = $redstag_db
			->select('carrier_code')
			->from('sales_flat_shipment_package')
			->where("CONVERT_TZ(created_at,'UTC','US/Eastern') >= '".$period_from."'", null, false)
			->where("CONVERT_TZ(created_at,'UTC','US/Eastern') <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->group_by('carrier_code')
			->order_by('carrier_code')
			->get()->result_array();
		
		$carrier_list = !empty($carrier_list_tmp) ? array_column($carrier_list_tmp, 'carrier_code') : array();
		
		$packages_by_date_location_carrier_data_tmp = $redstag_db
			->select("
				DATE(CONVERT_TZ(created_at,'UTC','US/Eastern')) AS the_date,
				stock_id,
				carrier_code,
				COUNT(*) AS num_packages")
			->from('sales_flat_shipment_package')
			->where("CONVERT_TZ(created_at,'UTC','US/Eastern') >= '".$period_from."'", null, false)
			->where("CONVERT_TZ(created_at,'UTC','US/Eastern') <", date('Y-m-d', strtotime('+1 day '.$period_to)))
			->group_by('the_date, stock_id, carrier_code')
			->order_by('the_date, stock_id, carrier_code')
			->get()->result_array();

		$packages_by_date_location_carrier_data = array();
		
		if(!empty($carrier_list) && strtotime($period_from) <= strtotime($period_to)) {
			$the_date = $period_from;
			while(strtotime($the_date) <= strtotime($period_to)) {
				$packages_by_date_location_carrier_data[$the_date] = array();
				
				$the_date = date('Y-m-d', strtotime('+1 day '.$the_date));
			}
			
			
			if(!empty($packages_by_date_location_carrier_data_tmp)) {
				foreach($packages_by_date_location_carrier_data_tmp as $current_data) {
					$packages_by_date_location_carrier_data[$current_data['the_date']][$current_data['stock_id'].'-'.$current_data['carrier_code']] = $current_data['num_packages'];
				}
			}
		}
		
		$data['stock_warehouse_name_by_id'] = $stock_warehouse_name_by_id;
		$data['carrier_list'] = $carrier_list;
		$data['packages_by_date_location_carrier_data'] = $packages_by_date_location_carrier_data;
		
		$data['packages_by_date_location_carrier_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_packages_by_date_location_carrier_board_visualization', $data, true);
		
		return $data;
	}
	
	public function update_ups_expected_delivery_date() {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$result = array('success' => true);
		
		// Get the latest ups package id which already has the postcode
		$latest_package_id_tmp = $prod_db
			->select('MAX(package_id) AS latest_package_id')
			->from('packages')
			->where('carrier_code', 'ups')
			->where('expected_delivery_date IS NOT NULL', null, false)
			->where('postcode IS NOT NULL', null, false)
			->get()->result_array();

		$latest_package_id = !empty($latest_package_id_tmp[0]['latest_package_id']) ? $latest_package_id_tmp[0]['latest_package_id'] : 4980000;
		
		if($latest_package_id < 7724359) {
			$latest_package_id = 7724359;
		}
		
		$new_ups_packages = $redstag_db
			->select("sales_flat_shipment_package.stock_id,
					  sales_flat_shipment_package.package_id,
					  sales_flat_order_address.postcode,
					  IF(sales_flat_shipment_package.stock_id IN (3,6),
					    CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),
						CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) AS package_created_at_local")
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('sales_flat_order_address', 'sales_flat_order_address.entity_id = sales_flat_order.shipping_address_id')
			->where('sales_flat_shipment_package.carrier_code', 'ups')
			->where('sales_flat_shipment_package.package_id >', $latest_package_id)
			->order_by('sales_flat_shipment_package.package_id')
			->limit(1000)
			->get()->result_array();
			
		$synced_ups_packages = $prod_db
			->select('package_id')
			->from('packages')
			->where('carrier_code', 'ups')
			->where('expected_delivery_date IS NULL', null, false)
			->get()->result_array();
		
		if(empty($synced_ups_packages)) {
			return $result;
		}
		
		$synced_ups_packages = array_column($synced_ups_packages, 'package_id');
		
		$cutoff_times_tmp = $prod_db
			->select('stock_id, carrier_ups_cutoff_time')
			->from('facilities')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->get()->result_array();
		
		$cutoff_time_by_stock_id = array();
		if(!empty($cutoff_times_tmp)) {
			foreach($cutoff_times_tmp as $current_data) {
				$cutoff_time_by_stock_id[$current_data['stock_id']] = $current_data['carrier_ups_cutoff_time'];
			}
		}
		
		$updated_packages = array();
		
		foreach($new_ups_packages as $package) {
			if(in_array($package['package_id'], $synced_ups_packages)) {
				$cutoff_time = !empty($cutoff_time_by_stock_id[$package['stock_id']]) ? $cutoff_time_by_stock_id[$package['stock_id']] : '17:00:00';
				$five_digits_postcode = substr($package['postcode'],0,5);
				
				$expected_delivery_date = $this->get_ups_expected_delivery_date($package['stock_id'], $package['package_created_at_local'], $five_digits_postcode, $cutoff_time);
				
				$updated_packages[] = array(
					'package_id' => $package['package_id'],
					'postcode' => $package['postcode'],
					'expected_delivery_date' => $expected_delivery_date
				);
			}
			else {
				$updated_packages[] = array(
					'package_id' => $package['package_id'],
					'postcode' => '-',
					'expected_delivery_date' => null
				);
			}
		}

		if(!empty($updated_packages)) {
			$prod_db->trans_start();
			
			$prod_db->update_batch('packages', $updated_packages, 'package_id');
			
			$prod_db->trans_complete();
		}
		
		return $result;
	}
	
	public function get_ups_expected_delivery_date($stock_id, $package_created_at_local, $postcode, $cutoff_time) {
		$package_created_date = null;
		$prod_db = $this->load->database('prod', TRUE);
		
		if(!isset($cutoff_time)) {
			$cutoff_time = '17:00:00';
		}
		
		// If package created after threshold time of Mountain Time, the package should be considered created the next day
		if(strtotime(date('H:i:s', strtotime($package_created_at_local))) > strtotime($cutoff_time)) {
			$package_created_date = date('Y-m-d', strtotime('+1 day '.$package_created_at_local));
		}
		else {
			$package_created_date = date('Y-m-d', strtotime($package_created_at_local));
		}
		
		// If package created date is on Saturday or Sunday, it should be considered created the next Monday
		$day_code = date('N', strtotime($package_created_date));
		if($day_code >= 6) {
			$package_created_date = date('Y-m-d', strtotime('+'.(8-$day_code).' day '.$package_created_date));
		}
		
		$prod_db
			->select('theoretical_transit_days')
			->from('carrier_theoretical_transit_days')
			->where('carrier_code', 'ups')
			->where('destination_zip', $postcode);
		
		if($stock_id == 3) {
			$prod_db->where('origin', 'slc');
		}
		else {
			$prod_db->where('origin', 'knoxville');
		}
		
		$theoretical_transit_day_tmp = $prod_db->get()->result_array();

		$transit_day = !empty($theoretical_transit_day_tmp) ? $theoretical_transit_day_tmp[0]['theoretical_transit_days'] : 2;
		$additional_day = 0; // additional day because of weekend
		
		$day_code = date('N', strtotime($package_created_date));
		if($day_code >= 5 && $transit_day >= 6) {
			$additional_day = 4 - ($day_code - 5);
		}
		else if(
			($day_code == 1 && $transit_day >= 5) ||
			($day_code == 2 && $transit_day >= 4) ||
			($day_code == 3 && $transit_day >= 3) ||
			($day_code == 4 && $transit_day >= 2) ||
			($day_code == 5 && $transit_day >= 1)
		) {
			$additional_day = 2;
		}
		else if($day_code >= 6 && $transit_day <= 5) {
			$additional_day = 2 - ($day_code - 5);
		}
		
		$expected_delivery_date = date('Y-m-d', strtotime('+'.($transit_day+$additional_day).' day '.$package_created_date));
		
		return $expected_delivery_date;
	}
	
	public function update_target_ship_date($limit = 1000) {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$latest_package = $prod_db
			->select('MAX(package_id) AS max_package_id')
			->from('packages')
			->where('target_ship_date IS NOT NULL', null, false)
			->get()->result_array();
		
		$packages = $prod_db
			->select('package_id')
			->from('packages')
			->where('target_ship_date IS NULL', null, false)
			->where('package_id >', $latest_package[0]['max_package_id'])
			->order_by('package_id')
			->limit($limit)
			->get()->result_array();
		
		$package_ids = array_column($packages, 'package_id');
		
		if(empty($package_ids)) return 0;
		
		$min_package_id = $package_ids[0];
		$max_package_id = $package_ids[count($package_ids)-1];
		
		$data = $redstag_db
			->select('package_id, target_ship_date')
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->where('package_id >=', $min_package_id)
			->where('package_id <=', $max_package_id)
			->get()->result_array();
		
		$updated_data = array();
		foreach($data as $current_data) {
			if(in_array($current_data['package_id'], $package_ids)) {
				$updated_data[] = $current_data;
			}
		}
		
		$prod_db->update_batch('packages', $updated_data, 'package_id');
		
		return array('min' => $min_package_id, 'max' => $max_package_id);
	}
	
	public function update_postcode_info($date) {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$carrier_code = 'ups';
		
		$packages = $redstag_db
			->select(
				"sales_flat_shipment_package.package_id,
				sales_flat_shipment_package.stock_id,
				sales_flat_order_address.postcode", false)
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('sales_flat_order_address', 'sales_flat_order_address.entity_id = sales_flat_order.shipping_address_id')	
			->where('sales_flat_shipment_package.created_at >=', $date)
			->where('sales_flat_shipment_package.created_at <', date('Y-m-d', strtotime('+1 day '.$date)))
			->where('sales_flat_shipment_package.carrier_code', $carrier_code)
			->order_by('sales_flat_shipment_package.package_id')
			->get()->result_array();
		
		$carrier_zone_info_tmp = $prod_db
			->select('*')
			->from('carrier_theoretical_transit_days')
			->where('carrier_code', $carrier_code)
			->get()->result_array();
			
		$carrier_zone_info = array();
		foreach($carrier_zone_info_tmp as $current_data) {
			$carrier_zone_info[$current_data['origin'].'-'.$current_data['destination_zip']] = $current_data;
		}
		unset($carrier_zone_info_tmp);
		
		$updated_packages = array();
		if(!empty($packages)) {
			$i=0;
			foreach($packages as $package) {
				$carrier_data = array();
				
				$five_digits_postcode = substr($package['postcode'],0,5);
				if($package['stock_id'] == 3) {
					$carrier_data = !empty($carrier_zone_info['slc-'.$five_digits_postcode]) ? $carrier_zone_info['slc-'.$five_digits_postcode] : null ;
				}
				else {
					$carrier_data = !empty($carrier_zone_info['knoxville-'.$five_digits_postcode]) ? $carrier_zone_info['knoxville-'.$five_digits_postcode] : null ;
				}
				
				$updated_package = array(
					'package_id' => $package['package_id'],
					'postcode' => $package['postcode'],
				);
				
				if(!empty($carrier_data)) {
					$updated_package['zone'] = $carrier_data['zone'];
					$updated_package['state'] = $carrier_data['state'];
				}

				$updated_packages[] = $updated_package;
			}
		}
		if(!empty($updated_packages)) {
			$prod_db->update_batch('packages', $updated_packages, 'package_id');
		}
	}
	
	public function generate_next_pods() {
		$result = array();
		
		$prod_db = $this->load->database('prod', TRUE);
		
		// Find delivered packages which POD have not been generated
		$packages = $prod_db
			->select('*')
			->from('packages')
			->where('pod_url IS NULL', null, false)
			->where('actual_delivery_date IS NOT NULL', null, false)
			->where_in('carrier_code', array('ups'))
			->where('destination_city IS NOT NULL', null, false)
			->where('store_id', 51)
			->order_by('package_created_at_local')
			->limit(10)
			->get()->result_array();
			
		if(!empty($packages)) {
			foreach($packages as $package) {
				$result[] = $this->generate_pod($package);
			}
		}
	}
	
	public function generate_pod($package) {
		$result = array('package_id' => $package['package_id']);
		
		if(empty($package['track_number'])) {
			$result['success'] = false;
			$result['error_message'] = 'Missing track number';
			return $result;
		}
		
		if(empty($package['actual_delivery_date'])) {
			$result['success'] = false;
			$result['error_message'] = 'Package has not been delivered.';
			return $result;
		}
		
		$this->load->library('Pdf');
		
		// Portrait, units in milimeter, A4 paper
		$pdf = new FPDF('P', 'mm', 'A4');
		
		$pdf->AddPage();
		$pdf->SetFont('Arial', 'B', 20);
		
		$pdf->Cell(0,7,'Proof of Delivery',0,1,'L');
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(0,20,'Dear Customer,',0,1);
		$pdf->Cell(0,0,'This notice serves as proof of delivery for the shipment listed below.',0,1);
		
		$pdf->Cell(0,10,'',0,1);
		
		$this->print_info_in_pdf($pdf, 'Tracking Number', $package['track_number']);
		$this->print_info_in_pdf($pdf, 'Service', $package['carrier_service_name']);
		$this->print_info_in_pdf($pdf, 'Delivered On', date('m/d/Y g:i A', strtotime($package['actual_delivery_date'])));
		$this->print_info_in_pdf($pdf, 'Delivered To', $package['destination_city']);
		$this->print_info_in_pdf($pdf, 'Weight', $package['weight'] . ' ' . $package['weight_measurement']);
		$this->print_info_in_pdf($pdf, 'Shipped / Billed On', date('m/d/Y', strtotime($package['package_created_at_local'])));
		
		if(!empty($package['received_by'])) {
			$this->print_info_in_pdf($pdf, 'Received By', $package['received_by']);
		}
		
		if(!empty($package['left_at'])) {
			$this->print_info_in_pdf($pdf, 'Left At', $package['left_at']);
		}
		
		$pdf->Cell(0,20,'Tracking results provided by UPS API.',0,1);
		
		switch($package['carrier_code']) {
			case 'ups':
				$pdf->Cell(0,5,'https://www.ups.com/track?loc=en_US&tracknum=' . $package['track_number'],0,1);
				$pdf->Cell(0,5,'Details are only available for shipments delivered within the last 120 days.',0,1);
				$pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1", "©").' 2021 United Parcel Service of America, Inc. All Rights Reserved. Confidential and Proprietary',0,1);
				break;
		}
		
		$dir = 'assets/data/' . PROJECT_CODE . '/file/pod/'.$package['store_id'].'/'.date('Y-m', strtotime($package['package_created_at_local']));

		if(!file_exists($dir)) {
			mkdir($dir);
		}
			
		$file_path = $dir . '/' . $package['carrier_code'].'_'.$package['track_number'].'.pdf';
				
		$pdf->Output($file_path, 'F');
		
		$file_url = base_url($file_path);
		
		if(ENVIRONMENT == 'production') {
			$this->db
				->set('pod_url', $file_url)
				->set('pod_generated_at', date('Y-m-d H:i:s'))
				->where('track_number', $package['track_number'])
				->update('packages');
		}
		
		$result['success'] = true;
		return $result;
	}
	
	public function print_info_in_pdf($pdf, $header, $content) {
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(80,10,$header,0,1,'L');
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(0,5,$content,0,1,'L');
		
		$pdf->Cell(0,5,'',0,1,'L');
	}
}