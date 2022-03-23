<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_carrier_optimization extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_carrier_optimization_board_data($data) {
		if(!isset($data['period_from'])) {
			$data['period_from'] = date('Y-m-d');
		}
		
		if(!isset($data['period_to'])) {
			$data['period_to'] = date('Y-m-d');
		}
		
		$data = $this->get_package_details_data($data);

		if($data['report_type'] == 'package_details') {
			$data['carrier_optimization_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_carrier_optimization_board_package_details_table', $data, true);
		}
		else if($data['report_type'] == 'results_by_carrier_base_rate') {
			$data['carrier_optimization_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_carrier_optimization_board_results_by_carrier_table', $data, true);
		}
		else if($data['report_type'] == 'results_by_carrier_with_surcharge') {
			$data['carrier_optimization_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_carrier_optimization_board_results_by_carrier_with_surcharge_table', $data, true);
		}
		else if($data['report_type'] == 'summary') {
			$data['carrier_optimization_board_table_html'] = $this->load->view(PROJECT_CODE.'/view_carrier_optimization_board_summary_table', $data, true);
		}
		
		return $data;
	}
	
	public function get_package_details_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$redstag_db
			->select("
			    cataloginventory_stock.name AS facility_name,
				sales_flat_shipment_package.stock_id,
				sales_flat_shipment.increment_id AS shipment_no,
				core_store.name AS merchant_name,
				sales_flat_shipment_package.carrier_code AS carrier,
				sales_flat_shipment.shipping_method,
				sales_flat_shipment_package.weight,
				sales_flat_shipment_package.height,
				sales_flat_shipment_package.width,
				sales_flat_shipment_package.length,
				IF(cataloginventory_stock.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) AS package_created_at,
				GREATEST(CEILING(sales_flat_shipment_package.weight), CEILING(sales_flat_shipment_package.height * sales_flat_shipment_package.width * sales_flat_shipment_package.length / ".$data['fedex_client_dim_factor'].")) AS client_billable_fedex_weight,
				GREATEST(CEILING(sales_flat_shipment_package.weight), CEILING(sales_flat_shipment_package.height * sales_flat_shipment_package.width * sales_flat_shipment_package.length / ".$data['ups_client_dim_factor'].")) AS client_billable_ups_weight,
				GREATEST(CEILING(sales_flat_shipment_package.weight), CEILING(sales_flat_shipment_package.height * sales_flat_shipment_package.width * sales_flat_shipment_package.length / ".$data['rsf_fedex_dim_factor'].")) AS rsf_billable_fedex_weight,
				GREATEST(CEILING(sales_flat_shipment_package.weight), CEILING(sales_flat_shipment_package.height * sales_flat_shipment_package.width * sales_flat_shipment_package.length / ".$data['rsf_ups_dim_factor'].")) AS rsf_billable_ups_weight,
				LEFT(sales_flat_order_address.postcode,5) AS fedex_zip_code,
				LEFT(sales_flat_order_address.postcode,3) AS ups_zip_code", false)
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment_package.shipment_id = sales_flat_shipment.entity_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('core_store', 'sales_flat_order.store_id = core_store.store_id')
			->join('sales_flat_order_address', 'sales_flat_shipment.shipping_address_id = sales_flat_order_address.entity_id')
			->join('cataloginventory_stock', 'cataloginventory_stock.stock_id = sales_flat_shipment_package.stock_id')
			->group_start()
				->group_start()
					->where_in('sales_flat_shipment_package.stock_id',  array(3,6)) // Salt Lake City
					->where('sales_flat_shipment_package.created_at >=', $this->convert_timezone($data['period_from'],'US/Mountain','UTC'))
					->where('sales_flat_shipment_package.created_at <', $this->convert_timezone(date('Y-m-d H:i:s', strtotime('+1 day ' . $data['period_to'])),'US/Mountain','UTC'))
				->group_end()
				->or_group_start()
					->where_not_in('sales_flat_shipment_package.stock_id', array(3,6))
					->where('sales_flat_shipment_package.created_at >=', $this->convert_timezone($data['period_from'],'US/Eastern','UTC'))
					->where('sales_flat_shipment_package.created_at <', $this->convert_timezone(date('Y-m-d H:i:s', strtotime('+1 day ' . $data['period_to'])),'US/Eastern','UTC'))
				->group_end()
			->group_end();
		
		if(!empty($data['merchant'])) {
			$redstag_db->where('sales_flat_order.store_id', $data['merchant']);
		}
		
		$data['package_details_data'] = $redstag_db->get()->result_array();
		
		$data['results_by_carrier'] = array(
			'fedex' => array(),
			'ups' => array()
		);
		
		$data['fedex_tier'] = isset($data['fedex_client_discount_tier']) ? explode('_', $data['fedex_client_discount_tier'])[1] : '1';
		$data['ups_tier'] = isset($data['ups_client_discount_tier']) ? explode('_', $data['ups_client_discount_tier'])[1] : '1';
		
		foreach($data['results_by_carrier'] as $carrier_code => $value) {
			$data['results_by_carrier'][$carrier_code] = array(
				'total_packages' => 0,
				'current_packages' => 0,
				'optimized_packages' => 0,
				'tier_1' => array(
					'cost' => 0,
					'profit' => 0,
					'cost_per_package' => 0,
					'profit_per_package' => 0
				),
				'tier_2' => array(
					'cost' => 0,
					'profit' => 0,
					'cost_per_package' => 0,
					'profit_per_package' => 0
				),
				'tier_3' => array(
					'cost' => 0,
					'profit' => 0,
					'cost_per_package' => 0,
					'profit_per_package' => 0
				),
				'rsf' => array(
					'cost' => 0,
					'cost_per_package' => 0,
				),
				'current' => array(
					'cost' => 0,
					'profit' => 0,
					'cost_per_package' => 0,
					'profit_per_package' => 0
				),
				'optimized' => array(
					'cost' => 0,
					'profit' => 0,
					'cost_per_package' => 0,
					'profit_per_package' => 0
				)
			);
		}
		
		$data['results_by_carrier_with_published_surcharge'] = $data['results_by_carrier'];
		$data['results_by_carrier_with_client_surcharge'] = $data['results_by_carrier'];
		
		$data['preferred_carrier'] = null;
		
		$lookup_tmp = $this->db
			->select('carrier_code,facility,destination_zip_code,zone')
			->from('carrier_zip_zones')
			->where('data_status', DATA_ACTIVE)
			->get()->result_array();
		
		$carrier_zone_lookup = array(
			'fedex' => array(),
			'ups' => array()
		);
		
		$this->load->model('model_db_crud');
		$facility_data = $this->model_db_crud->get_several_data('facility');
		$facility_data = array_combine( array_column($facility_data, 'id'), $facility_data );
		
		foreach($lookup_tmp as $current_data) {
			if(!isset($carrier_zone_lookup[$current_data['carrier_code']][$current_data['destination_zip_code']])) {
				$carrier_zone_lookup[$current_data['carrier_code']][$current_data['destination_zip_code']] = array(
					2 => 0, // TYS1A
					3 => 0, // SLC
					4 => 0 // TYS1B
				);
			}
			
			$stock_id = !empty($facility_data[$current_data['facility']]['stock_id']) ? $facility_data[$current_data['facility']]['stock_id'] : 2;
			$carrier_zone_lookup[$current_data['carrier_code']][$current_data['destination_zip_code']][$stock_id] = $current_data['zone'];
		}
		
		unset($lookup_tmp);
		
		$rates_tmp = $this->db
			->select('carrier_code,weight,zone,rate')
			->from('carrier_base_daily_rates')
			->where('data_status', DATA_ACTIVE)
			->get()->result_array();
		
		$fedex_rate_type = isset($data['fedex_client_discount_tier_list']) ? explode('_',$data['fedex_client_discount_tier'])[0] : 'standard';
		$ups_rate_type = isset($data['fedex_client_discount_tier_list']) ? explode('_',$data['ups_client_discount_tier'])[0] : 'standard';
		
		$disc_tmp = $this->db
			->select('carrier_code,weight,rsf_base_discount_percentage,tier_1_base_discount_percentage,tier_2_base_discount_percentage,tier_3_base_discount_percentage')
			->from('carrier_base_rate_discount')
			->where('data_status', DATA_ACTIVE)
			->group_start()
				->like('carrier_code', 'fedex')
				->where('rate_type', $fedex_rate_type)
			->group_end()
			->or_group_start()
				->like('carrier_code', 'ups')
				->where('rate_type', $ups_rate_type)
			->group_end()
			->get()->result_array();
		
		$carrier_discount = array();
		foreach($disc_tmp as $current_data) {
			$carrier_discount[$current_data['carrier_code'].'/rsf/'.$current_data['weight']] = $current_data['rsf_base_discount_percentage'];
			$carrier_discount[$current_data['carrier_code'].'/tier_1/'.$current_data['weight']] = $current_data['tier_1_base_discount_percentage'];
			$carrier_discount[$current_data['carrier_code'].'/tier_2/'.$current_data['weight']] = $current_data['tier_2_base_discount_percentage'];
			$carrier_discount[$current_data['carrier_code'].'/tier_3/'.$current_data['weight']] = $current_data['tier_3_base_discount_percentage'];
		}
		
		$carrier_rates = array();
	
		foreach($rates_tmp as $current_data) {
			$carrier_rates[$current_data['carrier_code'].'/base/'.$current_data['zone'].'/'.$current_data['weight']] = $current_data['rate'];
		}
		
		foreach($rates_tmp as $current_data) {
			if($current_data['carrier_code'] == 'fedex_home_delivery' || $current_data['carrier_code'] == 'fedex_ground') {
				$carrier_rates[$current_data['carrier_code'].'/rsf/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $data['fedex_earned_discount'] - $carrier_discount[$current_data['carrier_code'].'/rsf/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-$data['fedex_reduction_to_minimum'])
				);
				
				$carrier_rates[$current_data['carrier_code'].'/tier_1/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $carrier_discount[$current_data['carrier_code'].'/tier_1/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-0.1)
				);
				
				$carrier_rates[$current_data['carrier_code'].'/tier_2/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $carrier_discount[$current_data['carrier_code'].'/tier_2/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-0.1)
				);
				
				$carrier_rates[$current_data['carrier_code'].'/tier_3/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $carrier_discount[$current_data['carrier_code'].'/tier_3/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-0.1)
				);
			}
			else if($current_data['carrier_code'] == 'ups_ground') {
				$carrier_rates[$current_data['carrier_code'].'/rsf/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $data['ups_earned_discount'] - $carrier_discount[$current_data['carrier_code'].'/rsf/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-$data['ups_reduction_to_minimum'])
				);
				
				$carrier_rates[$current_data['carrier_code'].'/tier_1/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $carrier_discount[$current_data['carrier_code'].'/tier_1/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-0.1)
				);
				
				$carrier_rates[$current_data['carrier_code'].'/tier_2/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $carrier_discount[$current_data['carrier_code'].'/tier_2/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-0.1)
				);
				
				$carrier_rates[$current_data['carrier_code'].'/tier_3/'.$current_data['zone'].'/'.$current_data['weight']] = max(
					(100 - $carrier_discount[$current_data['carrier_code'].'/tier_3/'.$current_data['weight']])/100*$current_data['rate'],
					($carrier_rates[$current_data['carrier_code'].'/base/2/1']-0.1)
				);
			}
		}
		
		foreach($data['package_details_data'] as $key => $current_data) {
			$data['package_details_data'][$key]['fedex_zone'] = isset($carrier_zone_lookup['fedex'][intval($current_data['fedex_zip_code'])][$current_data['stock_id']]) ? $carrier_zone_lookup['fedex'][intval($current_data['fedex_zip_code'])][$current_data['stock_id']] : null;
			$data['package_details_data'][$key]['ups_zone'] = isset($carrier_zone_lookup['ups'][intval($current_data['ups_zip_code'])][$current_data['stock_id']]) ? $carrier_zone_lookup['ups'][intval($current_data['ups_zip_code'])][$current_data['stock_id']] : null;
			
			$data['package_details_data'][$key]['fedex_base_shipping_cost'] = null;
			$data['package_details_data'][$key]['fedex_client_tier_1_cost'] = null;
			$data['package_details_data'][$key]['fedex_client_tier_2_cost'] = null;
			$data['package_details_data'][$key]['fedex_client_tier_3_cost'] = null;
			$data['package_details_data'][$key]['fedex_rsf_cost'] = null;
			
			$data['package_details_data'][$key]['ups_base_shipping_cost'] = null;
			$data['package_details_data'][$key]['ups_client_tier_1_cost'] = null;
			$data['package_details_data'][$key]['ups_client_tier_2_cost'] = null;
			$data['package_details_data'][$key]['ups_client_tier_3_cost'] = null;
			$data['package_details_data'][$key]['ups_rsf_cost'] = null;
			
			$data['package_details_data'][$key]['profit_fedex'] = null;
			$data['package_details_data'][$key]['profit_ups'] = null;
			$data['package_details_data'][$key]['profit_diff'] = null;
			$data['package_details_data'][$key]['preferred_carrier'] = null;
			
			$data['package_details_data'][$key]['fedex_published_residential_delivery_fee'] = null;
			$data['package_details_data'][$key]['fedex_published_ahs_weight_surcharge'] = null;
			$data['package_details_data'][$key]['fedex_published_ahs_dimension_surcharge'] = null;
			$data['package_details_data'][$key]['fedex_published_total_surcharge'] = 0;
			
			$data['package_details_data'][$key]['fedex_rsf_residential_delivery_fee'] = null;
			$data['package_details_data'][$key]['fedex_rsf_weight_surcharge'] = null;
			$data['package_details_data'][$key]['fedex_rsf_dimension_surcharge'] = null;
			$data['package_details_data'][$key]['fedex_rsf_total_surcharge'] = 0;
			
			$data['package_details_data'][$key]['fedex_client_residential_delivery_fee'] = null;
			$data['package_details_data'][$key]['fedex_client_ahs_weight_surcharge'] = null;
			$data['package_details_data'][$key]['fedex_client_ahs_dimension_surcharge'] = null;
			$data['package_details_data'][$key]['fedex_client_total_surcharge'] = 0;
			
			$data['package_details_data'][$key]['ups_published_residential_delivery_fee'] = null;
			$data['package_details_data'][$key]['ups_published_ahs_weight_surcharge'] = null;
			$data['package_details_data'][$key]['ups_published_ahs_dimension_surcharge'] = null;
			$data['package_details_data'][$key]['ups_published_total_surcharge'] = 0;
			
			$data['package_details_data'][$key]['ups_rsf_residential_delivery_fee'] = null;
			$data['package_details_data'][$key]['ups_rsf_weight_surcharge'] = null;
			$data['package_details_data'][$key]['ups_rsf_dimension_surcharge'] = null;
			$data['package_details_data'][$key]['ups_rsf_total_surcharge'] = 0;
			
			$data['package_details_data'][$key]['ups_client_residential_delivery_fee'] = null;
			$data['package_details_data'][$key]['ups_client_ahs_weight_surcharge'] = null;
			$data['package_details_data'][$key]['ups_client_ahs_dimension_surcharge'] = null;
			$data['package_details_data'][$key]['ups_client_total_surcharge'] = 0;
			
			if(isset($data['package_details_data'][$key]['fedex_zone']) && isset($data['package_details_data'][$key]['ups_zone']) && $current_data['client_billable_fedex_weight'] > 0 && $current_data['client_billable_ups_weight'] > 0) {
				$carrier_code = null;
				
				switch($current_data['shipping_method']) {
					case 'fedex_FEDEX_GROUND':
						$carrier_code = 'fedex_ground';
						break;
					case 'fedex_GROUND_HOME_DELIVERY':
						$carrier_code = 'fedex_home_delivery';
						break;
					case 'ups_03':
						$carrier_code = 'ups_ground';
						break;
				}
				
				if(isset($carrier_code)) {
					$tmp_fedex_zone = $data['package_details_data'][$key]['fedex_zone'];
					$tmp_ups_zone = $data['package_details_data'][$key]['ups_zone'];
					$tmp_client_billable_fedex_weight = $current_data['client_billable_fedex_weight'];
					$tmp_client_billable_ups_weight = $current_data['client_billable_ups_weight'];
					$tmp_rsf_billable_fedex_weight = $current_data['rsf_billable_fedex_weight'];
					$tmp_rsf_billable_ups_weight = $current_data['rsf_billable_ups_weight'];
					
					if($carrier_code == 'fedex_home_delivery' || $carrier_code == 'fedex_ground') {
						$data['package_details_data'][$key]['fedex_base_shipping_cost'] = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates[$carrier_code.'/base/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates[$carrier_code.'/base/'.$tmp_fedex_zone.'/150'];
							
						$data['package_details_data'][$key]['fedex_client_tier_1_cost'] = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates[$carrier_code.'/tier_1/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates[$carrier_code.'/tier_1/'.$tmp_fedex_zone.'/150'];
							
						$data['package_details_data'][$key]['fedex_client_tier_2_cost'] = $tmp_client_billable_fedex_weight <= 150 ? 
							$carrier_rates[$carrier_code.'/tier_2/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates[$carrier_code.'/tier_2/'.$tmp_fedex_zone.'/150'];
							
						$data['package_details_data'][$key]['fedex_client_tier_3_cost'] = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates[$carrier_code.'/tier_3/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates[$carrier_code.'/tier_3/'.$tmp_fedex_zone.'/150'];
							
						$data['package_details_data'][$key]['fedex_rsf_cost'] = $tmp_rsf_billable_fedex_weight <= 150 ?
							$carrier_rates[$carrier_code.'/rsf/'.$tmp_fedex_zone.'/'.$tmp_rsf_billable_fedex_weight]
							: $tmp_rsf_billable_fedex_weight / 150 * $carrier_rates[$carrier_code.'/rsf/'.$tmp_fedex_zone.'/150'];
					}
					else if($carrier_code == 'ups_ground') {
						$tmp_fedex_home_delivery_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_home_delivery/base/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_home_delivery/base/'.$tmp_fedex_zone.'/150'];
						$tmp_fedex_ground_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_ground/base/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_ground/base/'.$tmp_fedex_zone.'/150'];
						$data['package_details_data'][$key]['fedex_base_shipping_cost'] = min($tmp_fedex_home_delivery_rate, $tmp_fedex_ground_rate);

						$tmp_fedex_home_delivery_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_home_delivery/tier_1/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_home_delivery/tier_1/'.$tmp_fedex_zone.'/150'];
						$tmp_fedex_ground_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_ground/tier_1/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_ground/tier_1/'.$tmp_fedex_zone.'/150'];
						$data['package_details_data'][$key]['fedex_client_tier_1_cost'] = min($tmp_fedex_home_delivery_rate, $tmp_fedex_ground_rate);
						
						$tmp_fedex_home_delivery_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_home_delivery/tier_2/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_home_delivery/tier_2/'.$tmp_fedex_zone.'/150'];
						$tmp_fedex_ground_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_ground/tier_2/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_ground/tier_2/'.$tmp_fedex_zone.'/150'];
						$data['package_details_data'][$key]['fedex_client_tier_2_cost'] = min($tmp_fedex_home_delivery_rate, $tmp_fedex_ground_rate);
						
						$tmp_fedex_home_delivery_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_home_delivery/tier_3/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_home_delivery/tier_3/'.$tmp_fedex_zone.'/150'];
						$tmp_fedex_ground_rate = $tmp_client_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_ground/tier_3/'.$tmp_fedex_zone.'/'.$tmp_client_billable_fedex_weight]
							: $tmp_client_billable_fedex_weight / 150 * $carrier_rates['fedex_ground/tier_3/'.$tmp_fedex_zone.'/150'];
						$data['package_details_data'][$key]['fedex_client_tier_3_cost'] = min($tmp_fedex_home_delivery_rate, $tmp_fedex_ground_rate);
						
						$tmp_fedex_home_delivery_rate = $tmp_rsf_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_home_delivery/rsf/'.$tmp_fedex_zone.'/'.$tmp_rsf_billable_fedex_weight]
							: $tmp_rsf_billable_fedex_weight / 150 * $carrier_rates['fedex_home_delivery/rsf/'.$tmp_fedex_zone.'/150'];
						$tmp_fedex_ground_rate = $tmp_rsf_billable_fedex_weight <= 150 ?
							$carrier_rates['fedex_ground/rsf/'.$tmp_fedex_zone.'/'.$tmp_rsf_billable_fedex_weight]
							: $tmp_rsf_billable_fedex_weight / 150 * $carrier_rates['fedex_ground/rsf/'.$tmp_fedex_zone.'/150'];
						$data['package_details_data'][$key]['fedex_rsf_cost'] = min($tmp_fedex_home_delivery_rate, $tmp_fedex_ground_rate);
					}
					
					$data['package_details_data'][$key]['ups_base_shipping_cost'] = $tmp_client_billable_ups_weight <= 150 ?
						$carrier_rates['ups_ground/base/'.$tmp_ups_zone.'/'.$tmp_client_billable_ups_weight]
						: $tmp_client_billable_ups_weight / 150 * $carrier_rates['ups_ground/base/'.$tmp_ups_zone.'/150'];
					
					$data['package_details_data'][$key]['ups_client_tier_1_cost'] = $tmp_client_billable_ups_weight <= 150 ?
						$carrier_rates['ups_ground/tier_1/'.$tmp_ups_zone.'/'.$tmp_client_billable_ups_weight]
						: $tmp_client_billable_ups_weight / 150 * $carrier_rates['ups_ground/tier_1/'.$tmp_ups_zone.'/150'];
					
					$data['package_details_data'][$key]['ups_client_tier_2_cost'] = $tmp_client_billable_ups_weight <= 150 ?
						$carrier_rates['ups_ground/tier_2/'.$tmp_ups_zone.'/'.$tmp_client_billable_ups_weight]
						: $tmp_client_billable_ups_weight / 150 * $carrier_rates['ups_ground/tier_2/'.$tmp_ups_zone.'/150'];
					
					$data['package_details_data'][$key]['ups_client_tier_3_cost'] = $tmp_client_billable_ups_weight <= 150 ?
						$carrier_rates['ups_ground/tier_3/'.$tmp_ups_zone.'/'.$tmp_client_billable_ups_weight]
						: $tmp_client_billable_ups_weight / 150 * $carrier_rates['ups_ground/tier_3/'.$tmp_ups_zone.'/150'];
					
					$data['package_details_data'][$key]['ups_rsf_cost'] = $tmp_rsf_billable_ups_weight <= 150 ?
						$carrier_rates['ups_ground/rsf/'.$tmp_ups_zone.'/'.$tmp_rsf_billable_ups_weight]
						: $tmp_rsf_billable_ups_weight / 150 * $carrier_rates['ups_ground/rsf/'.$tmp_ups_zone.'/150'];
					
					// Surcharge calculation
					
					// 1. Residential Delivery Fee
					if(in_array($carrier_code, array('fedex_home_delivery', 'ups_ground'))) {
						$data['package_details_data'][$key]['fedex_published_residential_delivery_fee'] = $data['fedex_residential_delivery_published_fee'];
						$data['package_details_data'][$key]['fedex_client_residential_delivery_fee'] = $data['fedex_residential_delivery_client_fee'];
						$data['package_details_data'][$key]['fedex_rsf_residential_delivery_fee'] = $data['fedex_residential_delivery_rsf_fee'];
						$data['package_details_data'][$key]['fedex_published_total_surcharge'] += $data['package_details_data'][$key]['fedex_published_residential_delivery_fee'];
						$data['package_details_data'][$key]['fedex_client_total_surcharge'] += $data['package_details_data'][$key]['fedex_client_residential_delivery_fee'];
						$data['package_details_data'][$key]['fedex_rsf_total_surcharge'] += $data['package_details_data'][$key]['fedex_rsf_residential_delivery_fee'];
						
						$data['package_details_data'][$key]['ups_published_residential_delivery_fee'] = $data['ups_residential_delivery_published_fee'];
						$data['package_details_data'][$key]['ups_client_residential_delivery_fee'] = $data['ups_residential_delivery_client_fee'];
						$data['package_details_data'][$key]['ups_rsf_residential_delivery_fee'] = $data['ups_residential_delivery_rsf_fee'];
						$data['package_details_data'][$key]['ups_published_total_surcharge'] += $data['package_details_data'][$key]['ups_published_residential_delivery_fee'];
						$data['package_details_data'][$key]['ups_client_total_surcharge'] += $data['package_details_data'][$key]['ups_client_residential_delivery_fee'];
						$data['package_details_data'][$key]['ups_rsf_total_surcharge'] += $data['package_details_data'][$key]['ups_rsf_residential_delivery_fee'];
					}
					
					// 2. AHS Dimension Surcharge
					$dims = array($current_data['length'], $current_data['width'], $current_data['height']);
					rsort($dims);
					if($dims[0] > 48 || $dims[1] > 30) {
						$data['package_details_data'][$key]['fedex_published_ahs_dimension_surcharge_fee'] = $data['fedex_ahs_dimension_surcharge_published_fee'];
						$data['package_details_data'][$key]['fedex_client_ahs_dimension_surcharge_fee'] = $data['fedex_ahs_dimension_surcharge_client_fee'];
						$data['package_details_data'][$key]['fedex_rsf_ahs_dimension_surcharge_fee'] = $data['fedex_ahs_dimension_surcharge_rsf_fee'];
						$data['package_details_data'][$key]['fedex_published_total_surcharge'] += $data['package_details_data'][$key]['fedex_published_ahs_dimension_surcharge_fee'];
						$data['package_details_data'][$key]['fedex_client_total_surcharge'] += $data['package_details_data'][$key]['fedex_client_ahs_dimension_surcharge_fee'];
						$data['package_details_data'][$key]['fedex_rsf_total_surcharge'] += $data['package_details_data'][$key]['fedex_rsf_ahs_dimension_surcharge_fee'];
						
						$data['package_details_data'][$key]['ups_published_ahs_dimension_surcharge_fee'] = $data['ups_ahs_dimension_surcharge_published_fee'];
						$data['package_details_data'][$key]['ups_client_ahs_dimension_surcharge_fee'] = $data['ups_ahs_dimension_surcharge_client_fee'];
						$data['package_details_data'][$key]['ups_rsf_ahs_dimension_surcharge_fee'] = $data['ups_ahs_dimension_surcharge_rsf_fee'];
						$data['package_details_data'][$key]['ups_published_total_surcharge'] += $data['package_details_data'][$key]['ups_published_ahs_dimension_surcharge_fee'];
						$data['package_details_data'][$key]['ups_client_total_surcharge'] += $data['package_details_data'][$key]['ups_client_ahs_dimension_surcharge_fee'];
						$data['package_details_data'][$key]['ups_rsf_total_surcharge'] += $data['package_details_data'][$key]['ups_rsf_ahs_dimension_surcharge_fee'];
					}
					
					// 3. AHS Weight Surcharge
					else if($current_data['weight'] > 50) {
						$data['package_details_data'][$key]['fedex_published_ahs_weight_surcharge_fee'] = $data['fedex_ahs_weight_surcharge_published_fee'];
						$data['package_details_data'][$key]['fedex_client_ahs_weight_surcharge_fee'] = $data['fedex_ahs_weight_surcharge_client_fee'];
						$data['package_details_data'][$key]['fedex_rsf_ahs_weight_surcharge_fee'] = $data['fedex_ahs_weight_surcharge_rsf_fee'];
						$data['package_details_data'][$key]['fedex_published_total_surcharge'] += $data['package_details_data'][$key]['fedex_published_ahs_weight_surcharge_fee'];
						$data['package_details_data'][$key]['fedex_client_total_surcharge'] += $data['package_details_data'][$key]['fedex_client_ahs_weight_surcharge_fee'];
						$data['package_details_data'][$key]['fedex_rsf_total_surcharge'] += $data['package_details_data'][$key]['fedex_rsf_ahs_weight_surcharge_fee'];
						
						$data['package_details_data'][$key]['ups_published_ahs_weight_surcharge_fee'] = $data['ups_ahs_weight_surcharge_published_fee'];
						$data['package_details_data'][$key]['ups_client_ahs_weight_surcharge_fee'] = $data['ups_ahs_weight_surcharge_client_fee'];
						$data['package_details_data'][$key]['ups_rsf_ahs_weight_surcharge_fee'] = $data['ups_ahs_weight_surcharge_rsf_fee'];
						$data['package_details_data'][$key]['ups_published_total_surcharge'] += $data['package_details_data'][$key]['ups_published_ahs_weight_surcharge_fee'];
						$data['package_details_data'][$key]['ups_client_total_surcharge'] += $data['package_details_data'][$key]['ups_client_ahs_weight_surcharge_fee'];
						$data['package_details_data'][$key]['ups_rsf_total_surcharge'] += $data['package_details_data'][$key]['ups_rsf_ahs_weight_surcharge_fee'];
					}
					
					// Cost & Profit Calculation
					switch($data['fedex_tier']) {
						case '1':
							$the_fedex_cost = $data['package_details_data'][$key]['fedex_client_tier_1_cost'];
							$data['package_details_data'][$key]['profit_fedex'] = $data['package_details_data'][$key]['fedex_client_tier_1_cost'] - $data['package_details_data'][$key]['fedex_rsf_cost'];
							break;
						case '2':
							$the_fedex_cost = $data['package_details_data'][$key]['fedex_client_tier_2_cost'];
							$data['package_details_data'][$key]['profit_fedex'] = $data['package_details_data'][$key]['fedex_client_tier_2_cost'] - $data['package_details_data'][$key]['fedex_rsf_cost'];
							break;
						case '3':
							$the_fedex_cost = $data['package_details_data'][$key]['fedex_client_tier_3_cost'];
							$data['package_details_data'][$key]['profit_fedex'] = $data['package_details_data'][$key]['fedex_client_tier_3_cost'] - $data['package_details_data'][$key]['fedex_rsf_cost'];
							break;
					}
					
					switch($data['ups_tier']) {
						case '1':
							$the_ups_cost = $data['package_details_data'][$key]['ups_client_tier_1_cost'];
							$data['package_details_data'][$key]['profit_ups'] = $data['package_details_data'][$key]['ups_client_tier_1_cost'] - $data['package_details_data'][$key]['ups_rsf_cost'];
							break;
						case '2':
							$the_ups_cost = $data['package_details_data'][$key]['ups_client_tier_2_cost'];
							$data['package_details_data'][$key]['profit_ups'] = $data['package_details_data'][$key]['ups_client_tier_2_cost'] - $data['package_details_data'][$key]['ups_rsf_cost'];
							break;
						case '3':
							$the_ups_cost = $data['package_details_data'][$key]['ups_client_tier_3_cost'];
							$data['package_details_data'][$key]['profit_ups'] = $data['package_details_data'][$key]['ups_client_tier_3_cost'] - $data['package_details_data'][$key]['ups_rsf_cost'];
							break;
					}
					
					// Results by Carrier
					if($carrier_code == 'fedex_home_delivery' || $carrier_code == 'fedex_ground') {
						$data['results_by_carrier']['fedex']['current_packages']++;
						$data['results_by_carrier']['fedex']['current']['cost'] += $the_fedex_cost;
						$data['results_by_carrier']['fedex']['current']['profit'] += $data['package_details_data'][$key]['profit_fedex'];			
					}
					else if($carrier_code == 'ups_ground') {
						$data['results_by_carrier']['ups']['current_packages']++;
						$data['results_by_carrier']['ups']['current']['cost'] += $the_ups_cost;
						$data['results_by_carrier']['ups']['current']['profit'] += $data['package_details_data'][$key]['profit_ups'];
					}
					
					$data['package_details_data'][$key]['profit_diff'] = $data['package_details_data'][$key]['profit_ups'] - $data['package_details_data'][$key]['profit_fedex'];
					if($data['package_details_data'][$key]['profit_fedex'] >= $data['package_details_data'][$key]['profit_ups']) {
						$data['package_details_data'][$key]['preferred_carrier'] = 'fedex';
						$data['results_by_carrier']['fedex']['optimized_packages']++;
						$data['results_by_carrier']['fedex']['optimized']['cost'] += $the_fedex_cost;
						$data['results_by_carrier']['fedex']['optimized']['profit'] += $data['package_details_data'][$key]['profit_fedex'];
					}
					else if($data['package_details_data'][$key]['profit_ups'] > $data['package_details_data'][$key]['profit_fedex']) {
						$data['package_details_data'][$key]['preferred_carrier'] = 'ups';
						$data['results_by_carrier']['ups']['optimized_packages']++;
						$data['results_by_carrier']['ups']['optimized']['cost'] += $the_ups_cost;
						$data['results_by_carrier']['ups']['optimized']['profit'] += $data['package_details_data'][$key]['profit_ups'];
					}
					
					$data['results_by_carrier']['fedex']['total_packages']++;
					$data['results_by_carrier']['fedex']['tier_1']['cost'] += $data['package_details_data'][$key]['fedex_client_tier_1_cost'];
					$data['results_by_carrier']['fedex']['tier_2']['cost'] += $data['package_details_data'][$key]['fedex_client_tier_2_cost'];
					$data['results_by_carrier']['fedex']['tier_3']['cost'] += $data['package_details_data'][$key]['fedex_client_tier_3_cost'];
					$data['results_by_carrier']['fedex']['rsf']['cost'] += $data['package_details_data'][$key]['fedex_rsf_cost'];
					
					$data['results_by_carrier']['ups']['total_packages']++;
					$data['results_by_carrier']['ups']['tier_1']['cost'] += $data['package_details_data'][$key]['ups_client_tier_1_cost'];
					$data['results_by_carrier']['ups']['tier_2']['cost'] += $data['package_details_data'][$key]['ups_client_tier_2_cost'];
					$data['results_by_carrier']['ups']['tier_3']['cost'] += $data['package_details_data'][$key]['ups_client_tier_3_cost'];
					$data['results_by_carrier']['ups']['rsf']['cost'] += $data['package_details_data'][$key]['ups_rsf_cost'];
					
					$profit_with_published_fedex_surcharge = ($data['package_details_data'][$key]['profit_fedex'] + $data['package_details_data'][$key]['fedex_published_total_surcharge'] - $data['package_details_data'][$key]['fedex_rsf_total_surcharge']);
					$profit_with_client_fedex_surcharge = ($data['package_details_data'][$key]['profit_fedex'] + $data['package_details_data'][$key]['fedex_client_total_surcharge'] - $data['package_details_data'][$key]['fedex_rsf_total_surcharge']);
					$profit_with_published_ups_surcharge = ($data['package_details_data'][$key]['profit_ups'] + $data['package_details_data'][$key]['ups_published_total_surcharge'] - $data['package_details_data'][$key]['ups_rsf_total_surcharge']);
					$profit_with_client_ups_surcharge = ($data['package_details_data'][$key]['profit_ups'] + $data['package_details_data'][$key]['ups_client_total_surcharge'] - $data['package_details_data'][$key]['ups_rsf_total_surcharge']);
					
					// Results by Carrier with surcharge
					if($carrier_code == 'fedex_home_delivery' || $carrier_code == 'fedex_ground') {
						$data['results_by_carrier_with_published_surcharge']['fedex']['current_packages']++;
						$data['results_by_carrier_with_published_surcharge']['fedex']['current']['cost'] += ($the_fedex_cost + $data['package_details_data'][$key]['fedex_published_total_surcharge']);
						$data['results_by_carrier_with_published_surcharge']['fedex']['current']['profit'] += $profit_with_published_fedex_surcharge;			
						
						$data['results_by_carrier_with_client_surcharge']['fedex']['current_packages']++;
						$data['results_by_carrier_with_client_surcharge']['fedex']['current']['cost'] += ($the_fedex_cost + $data['package_details_data'][$key]['fedex_client_total_surcharge']);
						$data['results_by_carrier_with_client_surcharge']['fedex']['current']['profit'] += $profit_with_client_fedex_surcharge;
					}
					else if($carrier_code == 'ups_ground') {
						$data['results_by_carrier_with_published_surcharge']['ups']['current_packages']++;
						$data['results_by_carrier_with_published_surcharge']['ups']['current']['cost'] += ($the_ups_cost + $data['package_details_data'][$key]['ups_published_total_surcharge']);
						$data['results_by_carrier_with_published_surcharge']['ups']['current']['profit'] += $profit_with_published_ups_surcharge;
					
						$data['results_by_carrier_with_client_surcharge']['ups']['current_packages']++;
						$data['results_by_carrier_with_client_surcharge']['ups']['current']['cost'] += ($the_ups_cost + $data['package_details_data'][$key]['ups_client_total_surcharge']);
						$data['results_by_carrier_with_client_surcharge']['ups']['current']['profit'] += $profit_with_client_ups_surcharge;
					}
					
					if($profit_with_published_fedex_surcharge >= $profit_with_published_ups_surcharge) {
						$data['results_by_carrier_with_published_surcharge']['fedex']['optimized_packages']++;
						$data['results_by_carrier_with_published_surcharge']['fedex']['optimized']['cost'] += ($the_fedex_cost + $data['package_details_data'][$key]['fedex_published_total_surcharge']);
						$data['results_by_carrier_with_published_surcharge']['fedex']['optimized']['profit'] += $profit_with_published_fedex_surcharge;
					}
					else if($profit_with_published_ups_surcharge > $profit_with_published_fedex_surcharge) {
						$data['results_by_carrier_with_published_surcharge']['ups']['optimized_packages']++;
						$data['results_by_carrier_with_published_surcharge']['ups']['optimized']['cost'] += ($the_ups_cost + $data['package_details_data'][$key]['ups_published_total_surcharge']);
						$data['results_by_carrier_with_published_surcharge']['ups']['optimized']['profit'] += $profit_with_published_ups_surcharge;
					}
					
					if($profit_with_client_fedex_surcharge >= $profit_with_client_ups_surcharge) {
						$data['results_by_carrier_with_client_surcharge']['fedex']['optimized_packages']++;
						$data['results_by_carrier_with_client_surcharge']['fedex']['optimized']['cost'] += ($the_fedex_cost + $data['package_details_data'][$key]['fedex_client_total_surcharge']);
						$data['results_by_carrier_with_client_surcharge']['fedex']['optimized']['profit'] += $profit_with_client_fedex_surcharge;
					}
					else if($profit_with_client_ups_surcharge > $profit_with_client_fedex_surcharge) {
						$data['results_by_carrier_with_client_surcharge']['ups']['optimized_packages']++;
						$data['results_by_carrier_with_client_surcharge']['ups']['optimized']['cost'] += ($the_ups_cost + $data['package_details_data'][$key]['ups_client_total_surcharge']);
						$data['results_by_carrier_with_client_surcharge']['ups']['optimized']['profit'] += $profit_with_client_ups_surcharge;
					}
					
					$data['results_by_carrier_with_published_surcharge']['fedex']['total_packages']++;
					$data['results_by_carrier_with_published_surcharge']['fedex']['tier_1']['cost'] += ($data['package_details_data'][$key]['fedex_client_tier_1_cost'] + $data['package_details_data'][$key]['fedex_published_total_surcharge']);
					$data['results_by_carrier_with_published_surcharge']['fedex']['tier_2']['cost'] += ($data['package_details_data'][$key]['fedex_client_tier_2_cost'] + $data['package_details_data'][$key]['fedex_published_total_surcharge']);
					$data['results_by_carrier_with_published_surcharge']['fedex']['tier_3']['cost'] += ($data['package_details_data'][$key]['fedex_client_tier_3_cost'] + $data['package_details_data'][$key]['fedex_published_total_surcharge']);
					$data['results_by_carrier_with_published_surcharge']['fedex']['rsf']['cost'] += ($data['package_details_data'][$key]['fedex_rsf_cost'] + $data['package_details_data'][$key]['fedex_rsf_total_surcharge']);
					
					$data['results_by_carrier_with_published_surcharge']['ups']['total_packages']++;
					$data['results_by_carrier_with_published_surcharge']['ups']['tier_1']['cost'] += ($data['package_details_data'][$key]['ups_client_tier_1_cost'] + $data['package_details_data'][$key]['ups_published_total_surcharge']);
					$data['results_by_carrier_with_published_surcharge']['ups']['tier_2']['cost'] += ($data['package_details_data'][$key]['ups_client_tier_2_cost'] + $data['package_details_data'][$key]['ups_published_total_surcharge']);
					$data['results_by_carrier_with_published_surcharge']['ups']['tier_3']['cost'] += ($data['package_details_data'][$key]['ups_client_tier_3_cost'] + $data['package_details_data'][$key]['ups_published_total_surcharge']);
					$data['results_by_carrier_with_published_surcharge']['ups']['rsf']['cost'] += ($data['package_details_data'][$key]['ups_rsf_cost'] + $data['package_details_data'][$key]['ups_rsf_total_surcharge']);
				
					$data['results_by_carrier_with_client_surcharge']['fedex']['total_packages']++;
					$data['results_by_carrier_with_client_surcharge']['fedex']['tier_1']['cost'] += ($data['package_details_data'][$key]['fedex_client_tier_1_cost'] + $data['package_details_data'][$key]['fedex_client_total_surcharge']);
					$data['results_by_carrier_with_client_surcharge']['fedex']['tier_2']['cost'] += ($data['package_details_data'][$key]['fedex_client_tier_2_cost'] + $data['package_details_data'][$key]['fedex_client_total_surcharge']);
					$data['results_by_carrier_with_client_surcharge']['fedex']['tier_3']['cost'] += ($data['package_details_data'][$key]['fedex_client_tier_3_cost'] + $data['package_details_data'][$key]['fedex_client_total_surcharge']);
					$data['results_by_carrier_with_client_surcharge']['fedex']['rsf']['cost'] += ($data['package_details_data'][$key]['fedex_rsf_cost'] + $data['package_details_data'][$key]['fedex_rsf_total_surcharge']);
					
					$data['results_by_carrier_with_client_surcharge']['ups']['total_packages']++;
					$data['results_by_carrier_with_client_surcharge']['ups']['tier_1']['cost'] += ($data['package_details_data'][$key]['ups_client_tier_1_cost'] + $data['package_details_data'][$key]['ups_client_total_surcharge']);
					$data['results_by_carrier_with_client_surcharge']['ups']['tier_2']['cost'] += ($data['package_details_data'][$key]['ups_client_tier_2_cost'] + $data['package_details_data'][$key]['ups_client_total_surcharge']);
					$data['results_by_carrier_with_client_surcharge']['ups']['tier_3']['cost'] += ($data['package_details_data'][$key]['ups_client_tier_3_cost'] + $data['package_details_data'][$key]['ups_client_total_surcharge']);
					$data['results_by_carrier_with_client_surcharge']['ups']['rsf']['cost'] += ($data['package_details_data'][$key]['ups_rsf_cost'] + $data['package_details_data'][$key]['ups_rsf_total_surcharge']);
				}
			}
		}
		
		foreach(array('results_by_carrier', 'results_by_carrier_with_published_surcharge', 'results_by_carrier_with_client_surcharge') as $report_type) {
			foreach($data[$report_type] as $carrier_code => $value) {
				$data[$report_type][$carrier_code]['tier_1']['profit'] = $data[$report_type][$carrier_code]['tier_1']['cost'] - $data[$report_type][$carrier_code]['rsf']['cost'];
				$data[$report_type][$carrier_code]['tier_2']['profit'] = $data[$report_type][$carrier_code]['tier_2']['cost'] - $data[$report_type][$carrier_code]['rsf']['cost'];
				$data[$report_type][$carrier_code]['tier_3']['profit'] = $data[$report_type][$carrier_code]['tier_3']['cost'] - $data[$report_type][$carrier_code]['rsf']['cost'];
			
				$data[$report_type][$carrier_code]['tier_1']['cost_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['tier_1']['cost'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['tier_2']['cost_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['tier_2']['cost'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['tier_3']['cost_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['tier_3']['cost'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['rsf']['cost_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['rsf']['cost'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['current']['cost_per_package'] = $data[$report_type][$carrier_code]['current_packages'] > 0 ? $data[$report_type][$carrier_code]['current']['cost'] / $data[$report_type][$carrier_code]['current_packages'] : 0;
				$data[$report_type][$carrier_code]['optimized']['cost_per_package'] = $data[$report_type][$carrier_code]['optimized_packages'] > 0 ? $data[$report_type][$carrier_code]['optimized']['cost'] / $data[$report_type][$carrier_code]['optimized_packages'] : 0;
				
				$data[$report_type][$carrier_code]['tier_1']['profit_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['tier_1']['profit'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['tier_2']['profit_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['tier_2']['profit'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['tier_3']['profit_per_package'] = $data[$report_type][$carrier_code]['total_packages'] > 0 ? $data[$report_type][$carrier_code]['tier_3']['profit'] / $data[$report_type][$carrier_code]['total_packages'] : 0;
				$data[$report_type][$carrier_code]['current']['profit_per_package'] = $data[$report_type][$carrier_code]['current_packages'] > 0 ? $data[$report_type][$carrier_code]['current']['profit'] / $data[$report_type][$carrier_code]['current_packages'] : 0;
				$data[$report_type][$carrier_code]['optimized']['profit_per_package'] = $data[$report_type][$carrier_code]['optimized_packages'] > 0 ? $data[$report_type][$carrier_code]['optimized']['profit'] / $data[$report_type][$carrier_code]['optimized_packages'] : 0;
			}
		}

		return $data;
	}
	
	public function get_carrier_optimization_summary_data($data) {
		
		return $data;
	}
	
	public function get_merchant_list() {
		$redstag_db = $this->load->database('redstag', TRUE);
		$merchant_list = $redstag_db
			->select('store_id AS id, name AS merchant_name', false)
			->from('core_store')
			->order_by('name', 'asc')
			->not_like('name', 'Inactive')
			->get()->result_array();
		return $merchant_list;
	}
	
	public function convert_timezone($time, $from_timezone, $to_timezone) {
		$old_timezone = new DateTimeZone($from_timezone);
		$datetime = new DateTime($time, $old_timezone);
		$new_timezone = new DateTimeZone($to_timezone);
		$datetime->setTimezone($new_timezone);
		return $datetime->format('Y-m-d H:i:s');
	}
}