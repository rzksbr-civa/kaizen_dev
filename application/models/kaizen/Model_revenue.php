<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_revenue extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_revenue_board_data($data) {
		if(!isset($data['period_from'])) {
			$data['period_from'] = date('Y-m-d');
		}
		
		if(!isset($data['period_to'])) {
			$data['period_to'] = date('Y-m-d');
		}

		if($data['report_type'] == 'package_pivot') {
			$data = $this->get_package_pivot_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_package_pivot_table', $data, true);
		}
		else if($data['report_type'] == 'outbound_packages') {
			$data = $this->get_outbound_packages_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_outbound_packages_table', $data, true);
		}
		else if($data['report_type'] == 'inbound_pivot') {
			$data = $this->get_inbound_pivot_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_inbound_pivot_table', $data, true);
		}
		else if($data['report_type'] == 'inbound_revenue') {
			$data = $this->get_inbound_revenue_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_inbound_revenue_table', $data, true);
		}
		else if($data['report_type'] == 'wages') {
			$data = $this->get_wages_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_wages_table', $data, true);
		}
		else if($data['report_type'] == 'revenue_summary') {
			$data = $this->get_revenue_summary_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_summary_table', $data, true);
			$data['revenue_board_js'] = $this->load->view(PROJECT_CODE.'/js_view_revenue_board_summary_table', $data, true);
		}
		else if($data['report_type'] == 'trending_graph') {
			$data = $this->get_trending_graph_data($data);
			$data['revenue_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_revenue_board_trending_graph', $data, true);
			$data['revenue_board_js'] = $this->load->view(PROJECT_CODE.'/js_view_revenue_board_trending_graph', $data, true);
		}
		
		return $data;
	}
	
	public function get_wages_data($data) {
		$default_wages = array(
			'inbound' => 19.5,
			'inventory' => 20,
			'kitting' => 19.5,
			'leads' => 25,
			'ltl' => 19.5,
			'material' => 18.5,
			'outbound' => 18
		);
		
		$filter = array(
			'data' => array(
				'start_date' => $data['period_from'],
				'end_date' => $data['period_to']
			)
		);
		
				$groups_data = array();
		
		do {
			$process = curl_init();
			curl_setopt($process, CURLOPT_URL, 'https://rest.tsheets.com/api/v1/groups');
			curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$groups_data_tmp = json_decode(curl_exec($process), true);
			curl_close($process);
			
			$groups_data = array_merge($groups_data, $groups_data_tmp['results']['groups']);
		}
		while($groups_data_tmp['more'] == 1);
		
		$facilities = $this->db
			->select('id, tsheets_facility_prefix')
			->from('facilities')
			->where('data_status', DATA_ACTIVE)
			->get()->result_array();
		
		$user_groups_by_facility = array();
		foreach($facilities as $facility) {
			$user_groups_by_facility[$facility['id']] = array();
		}

		foreach($groups_data as $group_id => $group_data) {
			$tsheets_facility_prefix = explode(' ', $group_data['name'])[0];
			
			foreach($facilities as $facility) {
				if($tsheets_facility_prefix == $facility['tsheets_facility_prefix']) {
					$user_groups_by_facility[$facility['id']][] = $group_id;
					break;
				}
			}
		}
		
		$process = curl_init();
		curl_setopt( $process, CURLOPT_URL, 'https://rest.tsheets.com/api/v1/reports/payroll');
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($filter));
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$raw_wages_data = json_decode(curl_exec($process), true);
		curl_close($process);
		
		$wages = array();
		
		$data['total_wages'] = array(
			'regular_hours' => 0,
			'regular_cost' => 0,
			'pto_hours' => 0,
			'pto_cost' => 0,
			'paid_break_hours' => 0,
			'paid_break_cost' => 0,
			'one_half_overtime_hours' => 0,
			'one_half_overtime_cost' => 0,
			'double_overtime_hours' => 0,
			'double_overtime_cost' => 0,
			'unpaid_break_hours' => 0,
			'total_work_hours' => 0,
			'total_cost' => 0,
			'departments' => array(
				'inbound' => 0,
				'inventory' => 0,
				'kitting' => 0,
				'leads' => 0,
				'ltl' => 0,
				'material' => 0,
				'outbound' => 0
			)
		);
		
		if(!empty($raw_wages_data['supplemental_data']['users'])) {
			foreach($raw_wages_data['supplemental_data']['users'] as $user_id => $user_data) {
				if(!empty($data['facility']) && !in_array($user_data['group_id'], $user_groups_by_facility[$data['facility']])) {
					continue;
				}
				
				$group_name = isset($groups_data['results']['groups'][$user_data['group_id']]['name']) ? strtolower($groups_data['results']['groups'][$user_data['group_id']]['name']) : null;
				
				$current_user_department_code = null;
				foreach($data['department_list'] as $department_code => $department_name) {
					if(strpos($group_name, $department_code) !== false) {
						$current_user_department_code = $department_code;
					}
				}

				
				if(!empty($data['department']) && $data['department'] == $current_user_department_code) {
					continue;
				}
				
				if(empty($user_data['pay_rate']) && !empty($default_wages[$current_user_department_code])) {
					$user_data['pay_rate'] = $default_wages[$current_user_department_code];
				}
				
				$wages[$user_id] = array(
					'user_id' => $user_id,
					'group_id' => $user_data['group_id'],
					'group_name' => isset($groups_data['results']['groups'][$user_data['group_id']]['name']) ? $groups_data['results']['groups'][$user_data['group_id']]['name'] : null,
					'first_name' => $user_data['first_name'],
					'last_name' => $user_data['last_name'],
					'pay_rate' => $user_data['pay_rate'],
					'regular_hours' => 0,
					'regular_cost' => 0,
					'pto_hours' => 0,
					'pto_cost' => 0,
					'paid_break_hours' => 0,
					'paid_break_cost' => 0,
					'one_half_overtime_hours' => 0,
					'one_half_overtime_pay_rate' => 1.5 * $user_data['pay_rate'],
					'one_half_overtime_cost' => 0,
					'double_overtime_hours' => 0,
					'double_overtime_pay_rate' => 2 * $user_data['pay_rate'],
					'double_overtime_cost' => 0,
					'unpaid_break_hours' => 0,
					'total_work_hours' => 0,
					'total_cost' => 0,
					'department_code' => $current_user_department_code
				);
			}
		}
		
		foreach($wages as $user_id => $user_data) {
			if(!empty($raw_wages_data['results']['payroll_report'][$user_id])) {
				$wages_data = $raw_wages_data['results']['payroll_report'][$user_id];
				
				$wages[$user_id]['regular_hours'] = $wages_data['total_re_seconds'] / 3600;
				$wages[$user_id]['regular_cost'] = $wages[$user_id]['regular_hours'] * $wages[$user_id]['pay_rate'];
				
				$data['total_wages']['regular_hours'] += $wages[$user_id]['regular_hours'];
				$data['total_wages']['regular_cost'] += $wages[$user_id]['regular_cost'];
				
				$wages[$user_id]['pto_hours'] = $wages_data['total_pto_seconds'] / 3600;
				$wages[$user_id]['pto_cost'] = $wages[$user_id]['pto_hours'] * $wages[$user_id]['pay_rate'];
				
				$data['total_wages']['pto_hours'] += $wages[$user_id]['pto_hours'];
				$data['total_wages']['pto_cost'] += $wages[$user_id]['pto_cost'];
				
				$wages[$user_id]['paid_break_hours'] = $wages_data['total_paid_break_seconds'] / 3600;
				$wages[$user_id]['paid_break_cost'] = $wages[$user_id]['paid_break_hours'] * $wages[$user_id]['pay_rate'];
				
				$data['total_wages']['paid_break_hours'] += $wages[$user_id]['paid_break_hours'];
				$data['total_wages']['paid_break_cost'] += $wages[$user_id]['paid_break_cost'];
				
				$wages[$user_id]['one_half_overtime_hours'] = $wages_data['overtime_seconds']['1.5'] / 3600;
				$wages[$user_id]['one_half_overtime_cost'] = $wages[$user_id]['one_half_overtime_hours'] * $wages[$user_id]['one_half_overtime_pay_rate'];
				
				$data['total_wages']['one_half_overtime_hours'] += $wages[$user_id]['one_half_overtime_hours'];
				$data['total_wages']['one_half_overtime_cost'] += $wages[$user_id]['one_half_overtime_cost'];
				
				$wages[$user_id]['double_overtime_hours'] = isset($wages_data['overtime_seconds']['2']) ? $wages_data['overtime_seconds']['2'] / 3600 : null;
				$wages[$user_id]['double_overtime_cost'] = $wages[$user_id]['double_overtime_hours'] * $wages[$user_id]['double_overtime_pay_rate'];
				
				$data['total_wages']['double_overtime_hours'] += $wages[$user_id]['double_overtime_hours'];
				$data['total_wages']['double_overtime_cost'] += $wages[$user_id]['double_overtime_cost'];
				
				$wages[$user_id]['unpaid_break_hours'] = $wages_data['total_unpaid_break_seconds'] / 3600;
				
				$data['total_wages']['unpaid_break_hours'] += $wages[$user_id]['unpaid_break_hours'];
				
				$wages[$user_id]['total_work_hours'] = $wages_data['total_work_seconds'] / 3600;
				
				$data['total_wages']['total_work_hours'] += $wages[$user_id]['total_work_hours'];
				
				$wages[$user_id]['total_cost'] = $wages[$user_id]['regular_cost'] + $wages[$user_id]['pto_cost'] + $wages[$user_id]['paid_break_cost'] + $wages[$user_id]['one_half_overtime_cost'] + $wages[$user_id]['double_overtime_cost'];
			
				$data['total_wages']['total_cost'] += $wages[$user_id]['total_cost'];
				
				if(!empty($wages[$user_id]['department_code'])) {
					$data['total_wages']['departments'][$wages[$user_id]['department_code']] += $wages[$user_id]['total_cost'];
				}
			}
		}
		
		$data['wages'] = array_values($wages);
		
		return $data;
	}
	
	public function get_inbound_revenue_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$redstag_db
			->select(
				"delivery.increment_id AS delivery_no,
				 cataloginventory_stock.name AS facility_name,
				 delivery_container_type.name AS container,
				 IF(cataloginventory_stock.stock_id IN (3,6),CONVERT_TZ(delivery.completed_at,'UTC','US/Mountain'),CONVERT_TZ(delivery.completed_at,'UTC','US/Eastern')) AS completed_at,
				 delivery_container.total_skus,
				 IF(delivery_container_type.name = 'Wood Pallet' OR delivery_container_type.name = 'Plastic Pallet',13.25,6) AS pallet_or_parcel_fee,
				 IF(delivery_container.total_skus>1,(delivery_container.total_skus-1)*1.75,0) AS additional_sku_fee", false)
			->from('delivery_container')
			->join('delivery', 'delivery.delivery_id = delivery_container.delivery_id')
			->join('delivery_container_type', 'delivery_container_type.container_type_id = delivery_container.container_type_id')
			->join('cataloginventory_stock', 'cataloginventory_stock.stock_id = delivery.stock_id')
			->where('delivery.delivery_type', 'asn');
		
		if(!empty($data['facility'])) {
			$facility_data_tmp = $this->db
				->select('*')
				->from('facilities')
				->where('data_status', DATA_ACTIVE)
				->where('id', $data['facility'])
				->get()->result_array();
			$facility_data = !empty($facility_data_tmp) ? $facility_data_tmp[0] : null;
			$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2;
			
			$redstag_db->where('delivery.stock_id', $stock_id);
		}
		
		$redstag_db
			->group_start()
				->group_start()
					->where_in('delivery.stock_id', array(3,6)) // Salt Lake City
					->where('delivery.completed_at >=', $this->convert_timezone($data['period_from'],'US/Mountain','UTC'))
					->where('delivery.completed_at <', $this->convert_timezone(date('Y-m-d H:i:s', strtotime('+1 day ' . $data['period_to'])),'US/Mountain','UTC'))
				->group_end()
				->or_group_start()
					->where_not_in('delivery.stock_id', array(3,6))
					->where('delivery.completed_at >=', $this->convert_timezone($data['period_from'],'US/Eastern','UTC'))
					->where('delivery.completed_at <', $this->convert_timezone(date('Y-m-d H:i:s', strtotime('+1 day ' . $data['period_to'])),'US/Eastern','UTC'))
				->group_end()
			->group_end();
				
		$data['inbound_revenue_data'] = $redstag_db->get()->result_array();
		return $data;
	}
	
	public function get_outbound_packages_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);

		$redstag_db
			->select(
				"cataloginventory_stock.name AS facility_name,
				 sales_flat_order.increment_id AS order_no,
				 sales_flat_shipment.increment_id AS shipment_no,
				 IF(cataloginventory_stock.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) AS package_created_at,
				 sales_flat_order.overbox,
				 sales_flat_shipment_package.qty AS total_qty,
				 sales_flat_shipment_package.qty - 1 AS additional_item,
				 1.9 AS fulfillment_fee,
				 IF(sales_flat_shipment_package.qty > 1 OR sales_flat_order.overbox = 1,0.75,0) AS packaging_fee,
				 (sales_flat_shipment_package.qty - 1)*0.3 AS additional_item_fee", false)
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('cataloginventory_stock', 'cataloginventory_stock.stock_id = sales_flat_shipment.stock_id');
		
		if(!empty($data['facility'])) {
			$facility_data_tmp = $this->db
				->select('*')
				->from('facilities')
				->where('data_status', DATA_ACTIVE)
				->where('id', $data['facility'])
				->get()->result_array();
			$facility_data = !empty($facility_data_tmp) ? $facility_data_tmp[0] : null;
			$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2;
			
			$redstag_db->where('sales_flat_shipment.stock_id', $stock_id);
		}
		
		$redstag_db
			->group_start()
				->group_start()
					->where_in('sales_flat_shipment.stock_id', array(3,6)) // Salt Lake City
					->where('sales_flat_shipment_package.created_at >=', $this->convert_timezone($data['period_from'],'US/Mountain','UTC'))
					->where('sales_flat_shipment_package.created_at <', $this->convert_timezone(date('Y-m-d H:i:s', strtotime('+1 day ' . $data['period_to'])),'US/Mountain','UTC'))
				->group_end()
				->or_group_start()
					->where_not_in('sales_flat_shipment.stock_id', array(3,6))
					->where('sales_flat_shipment_package.created_at >=', $this->convert_timezone($data['period_from'],'US/Eastern','UTC'))
					->where('sales_flat_shipment_package.created_at <', $this->convert_timezone(date('Y-m-d H:i:s', strtotime('+1 day ' . $data['period_to'])),'US/Eastern','UTC'))
				->group_end()
			->group_end();
				
		$data['outbound_packages_data'] = $redstag_db->get()->result_array();
		return $data;
	}
	
	public function get_inbound_pivot_data($data) {
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$period_start = $this->model_outbound->get_periodicity_date($data['periodicity'], $data['period_from']);
		$period_end = $this->model_outbound->get_periodicity_date($data['periodicity'], $data['period_to'] . ' 23:59:59');

		$inbound_pivot_data = array();
		
		$date = $period_start;
		while(strtotime($date) <= strtotime($period_end)) {
			$inbound_pivot_data[$date] = array(
				'label' => null,
				'num_containers' => 0,
				'sum_of_pallet_or_parcel_fee' => 0,
				'sum_of_additional_sku_fee' => 0,
				'total_inbound_revenue' => 0
			);
			
			switch($data['periodicity']) {
				case 'hourly':
					$inbound_pivot_data[$date]['label'] = $date;
					$date = date('Y-m-d H:00:00', strtotime('+1 hour '.$date));
					break;
				case 'daily':
					$inbound_pivot_data[$date]['label'] = $date;
					$date = date('Y-m-d', strtotime('+1 day '.$date));
					break;
				case 'weekly':
					$inbound_pivot_data[$date]['label'] = 'Week '.$date;
					$date = date('Y-m-d', strtotime('+1 week '.$date));
					break;
				case 'monthly':
					$inbound_pivot_data[$date]['label'] = date('Y-m', strtotime($date));
					$date = date('Y-m-d', strtotime('+1 month '.$date));
					break;
				case 'yearly':
					$inbound_pivot_data[$date]['label'] = date('Y', strtotime($date));
					$date = date('Y-m-d', strtotime('+1 year '.$date));
					break;
			}
		}
		
		$raw_inbound_revenue_data = $this->get_inbound_revenue_data($data);
		$inbound_revenue_data = $raw_inbound_revenue_data['inbound_revenue_data'];
		
		$data['total_inbound_pivot_data'] = array(
			'num_containers' => 0,
			'sum_of_pallet_or_parcel_fee' => 0,
			'sum_of_additional_sku_fee' => 0,
			'total_inbound_revenue' => 0
		);
		
		foreach($inbound_revenue_data as $current_data) {
			$this_period = $this->model_outbound->get_periodicity_date($data['periodicity'], $current_data['completed_at']);
			
			$inbound_pivot_data[$this_period]['num_containers']++;
			$inbound_pivot_data[$this_period]['sum_of_pallet_or_parcel_fee'] += $current_data['pallet_or_parcel_fee'];
			$inbound_pivot_data[$this_period]['sum_of_additional_sku_fee'] += $current_data['additional_sku_fee'];
			$inbound_pivot_data[$this_period]['total_inbound_revenue'] += ($current_data['pallet_or_parcel_fee'] + $current_data['additional_sku_fee']);
		
			$data['total_inbound_pivot_data']['num_containers']++;
			$data['total_inbound_pivot_data']['sum_of_pallet_or_parcel_fee'] += $current_data['pallet_or_parcel_fee'];
			$data['total_inbound_pivot_data']['sum_of_additional_sku_fee'] += $current_data['additional_sku_fee'];
			$data['total_inbound_pivot_data']['total_inbound_revenue'] += ($current_data['pallet_or_parcel_fee'] + $current_data['additional_sku_fee']);
		}
		
		$data['inbound_pivot_data'] = $inbound_pivot_data;
		
		return $data;
	}
	
	public function get_package_pivot_data($data) {
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$period_start = $this->model_outbound->get_periodicity_date($data['periodicity'], $data['period_from']);
		$period_end = $this->model_outbound->get_periodicity_date($data['periodicity'], $data['period_to'] . ' 23:59:59');

		$inbound_pivot_data = array();
		
		$date = $period_start;
		while(strtotime($date) <= strtotime($period_end)) {
			$package_pivot_data[$date] = array(
				'label' => null,
				'num_packages' => 0,
				'fulfillment_revenue' => 0,
				'packaging_revenue' => 0,
				'additional_item_revenue' => 0,
				'total_package_revenue' => 0
			);
			
			switch($data['periodicity']) {
				case 'hourly':
					$package_pivot_data[$date]['label'] = $date;
					$date = date('Y-m-d H:00:00', strtotime('+1 hour '.$date));
					break;
				case 'daily':
					$package_pivot_data[$date]['label'] = $date;
					$date = date('Y-m-d', strtotime('+1 day '.$date));
					break;
				case 'weekly':
					$package_pivot_data[$date]['label'] = 'Week ' . $date;
					$date = date('Y-m-d', strtotime('+1 week '.$date));
					break;
				case 'monthly':
					$package_pivot_data[$date]['label'] = date('Y-m', strtotime($date));
					$date = date('Y-m-d', strtotime('+1 month '.$date));
					break;
				case 'yearly':
					$package_pivot_data[$date]['label'] = date('Y', strtotime($date));
					$date = date('Y-m-d', strtotime('+1 year '.$date));
					break;
			}
		}
		
		$raw_outbound_packages_data = $this->get_outbound_packages_data($data);
		$outbound_packages_data = $raw_outbound_packages_data['outbound_packages_data'];
		
		$data['total_package_pivot_data'] = array(
			'num_packages' => 0,
			'fulfillment_revenue' => 0,
			'packaging_revenue' => 0,
			'additional_item_revenue' => 0,
			'total_package_revenue' => 0
		);
		
		foreach($outbound_packages_data as $current_data) {
			$this_period = $this->model_outbound->get_periodicity_date($data['periodicity'], $current_data['package_created_at']);
			
			$package_pivot_data[$this_period]['num_packages']++;
			$package_pivot_data[$this_period]['fulfillment_revenue'] += $current_data['fulfillment_fee'];
			$package_pivot_data[$this_period]['packaging_revenue'] += $current_data['packaging_fee'];
			$package_pivot_data[$this_period]['additional_item_revenue'] += $current_data['additional_item_fee'];
			$package_pivot_data[$this_period]['total_package_revenue'] += ($current_data['fulfillment_fee'] + $current_data['packaging_fee'] + $current_data['additional_item_fee']);
		
			$data['total_package_pivot_data']['num_packages']++;
			$data['total_package_pivot_data']['fulfillment_revenue'] += $current_data['fulfillment_fee'];
			$data['total_package_pivot_data']['packaging_revenue'] += $current_data['packaging_fee'];
			$data['total_package_pivot_data']['additional_item_revenue'] += $current_data['additional_item_fee'];
			$data['total_package_pivot_data']['total_package_revenue'] += ($current_data['fulfillment_fee'] + $current_data['packaging_fee'] + $current_data['additional_item_fee']);
		}
		
		$data['package_pivot_data'] = $package_pivot_data;
		
		return $data;
	}
	
	public function get_revenue_summary_data($data) {
		$data['revenue_summary'] = array();
		
		$tmp = $this->get_package_pivot_data($data);
		$data['revenue_summary']['total_package_revenue'] = $tmp['total_package_pivot_data']['total_package_revenue'];
		
		$data['department'] = null;
		$tmp = $this->get_wages_data($data);
		$data['revenue_summary']['wages'] = $tmp['total_wages']['departments'];
		$data['revenue_summary']['total_wages'] = $tmp['total_wages']['total_cost'];
		
		$data['revenue_summary']['wages_with_overhead'] = array();
		foreach($data['revenue_summary']['wages'] as $department_code => $total_wages) {
			$data['revenue_summary']['wages_with_overhead'][$department_code] = $total_wages * 1.31;
		}
		
		$data['revenue_summary']['total_wages_with_overhead'] = $tmp['total_wages']['total_cost'] * 1.31;
		
		$data['revenue_summary']['outbound_profit'] = $data['revenue_summary']['total_package_revenue'] - $data['revenue_summary']['wages_with_overhead']['outbound'];
		
		$tmp = $this->get_inbound_pivot_data($data);
		$data['revenue_summary']['inbound_revenue'] = $tmp['total_inbound_pivot_data']['total_inbound_revenue'];
		
		return $data;
	}
	
	public function convert_timezone($time, $from_timezone, $to_timezone) {
		$old_timezone = new DateTimeZone($from_timezone);
		$datetime = new DateTime($time, $old_timezone);
		$new_timezone = new DateTimeZone($to_timezone);
		$datetime->setTimezone($new_timezone);
		return $datetime->format('Y-m-d H:i:s');
	}
	
	public function update_wages_data($date) {
		$result = array();
		
		$department_list = array(
			'inbound' => 'Inbound',
			'inventory' => 'Inventory',
			'kitting' => 'Kitting',
			'leads' => 'Leads',
			'ltl' => 'LTL',
			'material' => 'Material Handling',
			'outbound' => 'Outbound'
		);
		
		$groups_data = array();
		
		do {
			$process = curl_init();
			curl_setopt($process, CURLOPT_URL, 'https://rest.tsheets.com/api/v1/groups');
			curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$groups_data_tmp = json_decode(curl_exec($process), true);
			curl_close($process);
			
			if(!empty($groups_data_tmp['results']['groups'])) {
				$groups_data = array_merge($groups_data, $groups_data_tmp['results']['groups']);
			}
		}
		while($groups_data_tmp['more'] == 1);

		$group_info = array();
		foreach($groups_data as $group_id => $group_data) {
			$group_name = $group_data['name'];
			$group_info[$group_id] = array(
				'group_name' => $group_name,
				'department_name' => 'Unknown',
				'facility' => null
			);
			
			foreach($department_list as $department_code => $department_name) {
				if(strpos(strtolower($group_name), $department_code) !== false) {
					$group_info[$group_id]['department_name'] = $department_name;
				}
			}
			
			$tsheets_facility_prefix = explode(' ', $group_data['name'])[0];
			if($tsheets_facility_prefix == 'TYS1') {
				$group_info[$group_id]['facility'] = 1;
			}
			else if(substr($tsheets_facility_prefix,0,3) == 'SLC') {
				$group_info[$group_id]['facility'] = 2;
			}
		}

		$filter = array(
			'data' => array(
				'start_date' => $date,
				'end_date' => $date
			)
		);
		
		$process = curl_init();
		
		curl_setopt($process, CURLOPT_URL, 'https://rest.tsheets.com/api/v1/reports/payroll');
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($filter));
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$raw_wages_data = json_decode(curl_exec($process), true);
		curl_close($process);
		
		$tsheets_user_data = array();
		$new_wages_data = array();
		
		if(!empty($raw_wages_data['supplemental_data']['users'])) {
			foreach($raw_wages_data['supplemental_data']['users'] as $user_id => $user_data) {
				$tsheets_user_data[$user_id] = array(
					'user_id' => $user_id,
					'group_id' => $user_data['group_id'],
					'pay_rate' => $user_data['pay_rate'],
				);
			}
		}
		
		foreach($tsheets_user_data as $user_id => $user_data) {
			if(!empty($raw_wages_data['results']['payroll_report'][$user_id])) {
				$wages_data = $raw_wages_data['results']['payroll_report'][$user_id];
				
				$new_wages_data[] = array(
					'tsheets_user_id' => $user_id,
					'date' => $date,
					'total_re_seconds' => $wages_data['total_re_seconds'],
					'total_pto_seconds' => $wages_data['total_pto_seconds'],
					'total_work_seconds' => $wages_data['total_work_seconds'],
					'total_paid_break_seconds' => $wages_data['total_paid_break_seconds'],
					'total_unpaid_break_seconds' => $wages_data['total_unpaid_break_seconds'],
					'one_half_overtime_seconds' => $wages_data['overtime_seconds']['1.5'],
					'double_overtime_seconds' => $wages_data['overtime_seconds']['2'],
					'timesheet_count' => $wages_data['timesheet_count'],
					'pay_rate' => $user_data['pay_rate'],
					'group_id' => $user_data['group_id'],
					'group_name' => isset($group_info[$user_data['group_id']]) ? $group_info[$user_data['group_id']]['group_name'] : null,
					'facility' => isset($group_info[$user_data['group_id']]) ? $group_info[$user_data['group_id']]['facility'] : null,
					'department_name' => isset($group_info[$user_data['group_id']]) ? $group_info[$user_data['group_id']]['department_name'] : null,
				);
			}
		}

		$result['success'] = true;
		$result['updated_wages_data_count'] = count($new_wages_data);
		
		if(!empty($new_wages_data)) {
			$this->db->trans_start();
			
			$this->db
				->where('date', $date)
				->delete('wages');
			
			$result['success'] = $this->db->insert_batch('wages', $new_wages_data);
			
			$this->db->trans_complete();
		}

		return $result;
	}
	
	public function update_tsheets_employees_data() {
		$result = array();

		$tsheets_employees_to_insert = array();
		
		$process = curl_init();
		curl_setopt($process, CURLOPT_URL, 'https://rest.tsheets.com/api/v1/groups');
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$groups_data = json_decode(curl_exec($process), true);
		curl_close($process);
		
		$facility_by_user_group_id = array();
		foreach($groups_data['results']['groups'] as $group_id => $group_data) {
			$tsheets_facility_prefix = explode(' ', $group_data['name'])[0];
			if($tsheets_facility_prefix == 'TYS') {
				$facility_by_user_group_id[$group_id] = 1;
			}
			else if(substr($tsheets_facility_prefix,0,3) == 'SLC') {
				$facility_by_user_group_id[$group_id] = 2;
			}
		}
		
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
			
			$department_list = array(
				'inbound' => 'Inbound',
				'inventory' => 'Inventory',
				'kitting' => 'Kitting',
				'leads' => 'Leads',
				'ltl' => 'LTL',
				'material' => 'Material Handling',
				'outbound' => 'Outbound'
			);
			
			foreach($tsheets_employees as $current_data) {
				$group_name = isset($groups_data['results']['groups'][$current_data['group_id']]['name']) ? $groups_data['results']['groups'][$current_data['group_id']]['name'] : null;
				$department_name = null;
				
				foreach($department_list as $department_code => $this_department_name) {
					if(strpos(strtolower($group_name), $department_code) !== false) {
						$department_name = $this_department_name;
					}
				}
				
				$tsheets_employees_to_insert[] = array(
					'tsheets_user_id' => $current_data['id'],
					'first_name' => $current_data['first_name'],
					'last_name' => $current_data['last_name'],
					'group_id' => $current_data['group_id'],
					'group_name' => $group_name,
					'facility' => isset($facility_by_user_group_id[$current_data['group_id']]) ? $facility_by_user_group_id[$current_data['group_id']] : null,
					'department_name' => $department_name,
					'active' => $current_data['active'],
					'employee_number' => $current_data['employee_number'],
					'salaried' => $current_data['salaried'],
					'exempt' => $current_data['exempt'],
					'username' => $current_data['username'],
					'email' => $current_data['email'],
					'payroll_id' => $current_data['payroll_id'],
					'mobile_number' => $current_data['mobile_number'],
					'profile_image_url' => $current_data['profile_image_url'],
					'pay_rate' => $current_data['pay_rate'],
					'pay_interval' => $current_data['pay_interval']
				);
			}
			
			$page++;
		}
		while( true );

		if(!empty($tsheets_employees_to_insert)) {
			$this->db->trans_start();
			
			$this->db->where('tsheets_user_id >', 0)->delete('tsheets_employees');
			
			$result['success'] = $this->db->insert_batch('tsheets_employees', $tsheets_employees_to_insert);
			
			$this->db->trans_complete();
		}
		else {
			$result['success'] = true;
		}
		
		return $result;
	}
	
	public function get_trending_graph_data($data) {
		$data['trending_graph'] = array();
		
		$data['department_list']['unknown'] = 'Unknown';
		
		foreach($data['department_list'] as $department_code => $department_name) {
			$data['trending_graph'][$department_name] = array(
				'revenue' => array(),
				'wages_with_overhead' => array(),
				'profit' => array()
			);
		}
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$period_start = $this->model_outbound->get_periodicity_date($data['periodicity'], $data['period_from']);
		$period_end = $this->model_outbound->get_periodicity_date($data['periodicity'], $data['period_to'] . ' 23:59:59');
		
		$data['trending_graph_label_date'] = array();
		$data['trending_graph_label'] = array();
		$date = $period_start;
		while(strtotime($date) <= strtotime($period_end)) {
			$data['trending_graph_label_date'][] = $date;
			switch($data['periodicity']) {
				case 'hourly':
					$data['trending_graph_label'][] = $date;
					$date = date('Y-m-d H:00:00', strtotime('+1 hour '.$date));
					break;
				case 'daily':
					$data['trending_graph_label'][] = $date;
					$date = date('Y-m-d', strtotime('+1 day '.$date));
					break;
				case 'weekly':
					$data['trending_graph_label'][] = 'Week '.$date;
					$date = date('Y-m-d', strtotime('+1 week '.$date));
					break;
				case 'monthly':
					$data['trending_graph_label'][] = date('Y-m', strtotime($date));
					$date = date('Y-m-d', strtotime('+1 month '.$date));
					break;
				case 'yearly':
					$data['trending_graph_label'][] = date('Y', strtotime($date));
					$date = date('Y-m-d', strtotime('+1 year '.$date));
					break;
			}
		}
		
		$date = $period_start;
		while(strtotime($date) <= strtotime($period_end)) {
			foreach($data['department_list'] as $department_code => $department_name) {
				$data['trending_graph'][$department_name]['wages_with_overhead'][$date] = 0;
				
				if(in_array($department_code, array('inbound','outbound'))) {
					$data['trending_graph'][$department_name]['revenue'][$date] = 0;
					$data['trending_graph'][$department_name]['profit'][$date] = 0;
				}
			}

			switch($data['periodicity']) {
				case 'daily':
					$date = date('Y-m-d', strtotime('+1 day '.$date));
					break;
				case 'weekly':
					$date = date('Y-m-d', strtotime('+1 week '.$date));
					break;
				case 'monthly':
					$date = date('Y-m-d', strtotime('+1 month '.$date));
					break;
				case 'yearly':
					$date = date('Y-m-d', strtotime('+1 year '.$date));
					break;
			}
		}
		
		$prod_db = $this->load->database('prod', TRUE);
		
		$periodicity_query = null;
		
		switch($data['periodicity']) {
			case 'daily':
				$periodicity_query = 'DATE(date)';
				break;
			case 'weekly':
				$periodicity_query = 'DATE_ADD(date, INTERVAL - WEEKDAY(date) DAY)';
				break;
			case 'monthly':
				$periodicity_query = 'DATE_FORMAT(date, "%Y-%m-01")';
				break;
			case 'yearly':
				$periodicity_query = 'DATE_FORMAT(date, "%Y-01-01")';
				break;
		}
		
		// Inbound & Outbound Revenue
		
		$prod_db
			->select($periodicity_query.' AS period, SUM(outbound_revenue) AS total_outbound_revenue, SUM(inbound_revenue) AS total_inbound_revenue', false)
			->from('revenue')
			->where('date >=', $data['period_from'])
			->where('date <=', $data['period_to'])
			->group_by('period');
		
		if(!empty($data['facility'])) {
			$prod_db->where('facility', $data['facility']);
		}
		
		$revenue_data = $prod_db->get()->result_array();
		
		foreach($revenue_data as $current_data) {
			$data['trending_graph'][$data['department_list']['inbound']]['revenue'][$current_data['period']] = $current_data['total_inbound_revenue'];
			$data['trending_graph'][$data['department_list']['outbound']]['revenue'][$current_data['period']] = $current_data['total_outbound_revenue'];
		}
		
		// Wages
		
		$prod_db
			->select('department_name, '.$periodicity_query.' AS period, SUM((total_re_seconds+total_pto_seconds+total_paid_break_seconds+one_half_overtime_seconds*1.5+double_overtime_seconds*2)/3600*wages.pay_rate*1.31) AS total_wages_with_overhead', false)
			->from('wages')
			->where('date >=', $data['period_from'])
			->where('date <=', $data['period_to'])
			->group_by('department_name,period');
		
		if(!empty($data['facility'])) {
			$prod_db->where('facility', $data['facility']);
		}
		
		$wages_data = $prod_db->get()->result_array();
		
		foreach($wages_data as $current_data) {
			$department_name = !empty($current_data['department_name']) ? $current_data['department_name'] : 'Unknown';
			$data['trending_graph'][$department_name]['wages_with_overhead'][$current_data['period']] = $current_data['total_wages_with_overhead'];
			
			if(isset($data['trending_graph'][$department_name]['revenue'][$current_data['period']])) {
				if(!isset($data['trending_graph'][$department_name]['profit'])) {
					$data['trending_graph'][$department_name]['profit'] = array();
				}
				
				$data['trending_graph'][$department_name]['profit'][$current_data['period']] = $data['trending_graph'][$department_name]['revenue'][$current_data['period']] - $data['trending_graph'][$department_name]['wages_with_overhead'][$current_data['period']];
			}
		}
		
		return $data;
	}
	
	public function update_inbound_and_outbound_revenue_data($date) {
		$result = array();
		
		$args = array();
		
		$args['department_list'] = array(
			'inbound' => 'Inbound',
			'inventory' => 'Inventory',
			'kitting' => 'Kitting',
			'leads' => 'Leads',
			'ltl' => 'LTL',
			'material' => 'Material Handling',
			'outbound' => 'Outbound'
		);

		$args['facility'] = 1;
		$args['periodicity'] = 'daily';
		$args['period_from'] = $date;
		$args['period_to'] = $date;
		
		$island_river_inbound_revenue_data = $this->get_inbound_pivot_data($args);
		$island_river_inbound_revenue = $island_river_inbound_revenue_data['inbound_pivot_data'][$date]['total_inbound_revenue'];
		
		$island_river_outbound_revenue_data = $this->get_package_pivot_data($args);
		$island_river_outbound_revenue = $island_river_outbound_revenue_data['package_pivot_data'][$date]['total_package_revenue'];
		
		$args['facility'] = 2;
		
		$slc_inbound_revenue_data = $this->get_inbound_pivot_data($args);
		$slc_inbound_revenue = $slc_inbound_revenue_data['inbound_pivot_data'][$date]['total_inbound_revenue'];
		
		$slc_outbound_revenue_data = $this->get_package_pivot_data($args);
		$slc_outbound_revenue = $slc_outbound_revenue_data['package_pivot_data'][$date]['total_package_revenue'];
		
		$this->db->trans_start();
		
		$this->db->where('date', $date)->delete('revenue');
		
		$this->db->insert('revenue', array('date'=>$date, 'facility'=>1, 'outbound_revenue'=>$island_river_outbound_revenue, 'inbound_revenue'=>$island_river_inbound_revenue));
		
		$result['success'] = $this->db->insert('revenue', array('date'=>$date, 'facility'=>2, 'outbound_revenue'=>$slc_outbound_revenue, 'inbound_revenue'=>$slc_inbound_revenue));
		
		$this->db->trans_complete();
		
		return $result;
	}
	
	public function update_tsheets_groups($page) {
		$result = array('success' => true);
		
		$groups_data = array();
		
		$groups_data_tmp = $this->get_tsheets_api_result('https://rest.tsheets.com/api/v1/groups?per_page=50&page='.$page);
		
		$groups_data = array_merge($groups_data, $groups_data_tmp['results']['groups']);
		
		debug_var($groups_data);
		
		return $result;
	}
	
	public function get_tsheets_api_result($api_url) {
		$process = curl_init();
		curl_setopt($process, CURLOPT_URL, 'https://rest.tsheets.com/api/v1/groups');
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: '.TSHEETS_AUTHORIZATION));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$result = json_decode(curl_exec($process), true);
		curl_close($process);
		
		return $result;
	}
}