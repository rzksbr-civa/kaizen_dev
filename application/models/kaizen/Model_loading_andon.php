<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_loading_andon extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_loading_andon_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$data['carrier_list'] = array(
			'express' => 'Express',
			'fedex' => 'FedEx Ground',
			'smartpost' => 'FedEx SmartPost',
			'ups' => 'UPS',
			'upsnext' => 'UPS Next',
			'usps' => 'USPS',
			'ontrac' => 'OnTrac',
			'lasership' => 'LaserShip'
		);
		
		$data['container_type_list'] = array(
			'pallet' => 'Pallet',
			'rolling_bin' => 'Rolling Bin',
			'flat_cart' => 'Flat Cart',
			'truck_trailer' => 'Truck Trailer'
		);
		
		$data['manifest_status_list'] = array(
			'loaded' => 'Loaded',
			'open' => 'Open',
			'sealed' => 'Sealed'
		);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$timezone_name = 'US/Eastern';
		if($stock_id == 3 || $stock_id == 6) $timezone_name = 'US/Mountain';
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		$redstag_db
			->select("manifest.stock_id, manifest.manifest_id, manifest.increment_id, manifest.container_type, manifest.manifest_courier_code AS carrier_code, manifest.load_location, manifest.weight, manifest.status, manifest.weight / 40000 * 100 AS blow_out, manifest.packages, IF(manifest.stock_id IN(3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern')) AS local_created_time, manifest.completed_at", false)
			->from('manifest')
			->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern')) >=", "'".$period_from."'", false)
			->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(created_at,'UTC','US/Mountain'),CONVERT_TZ(created_at,'UTC','US/Eastern')) <", "'".date('Y-m-d', strtotime('+1 day '.$period_to))."'", false);
		
		if(!empty($data['facility'])) {
			$redstag_db->where('manifest.stock_id', $stock_id);
		}
		if(!empty($data['status'])) {
			$redstag_db->where('manifest.status', $data['status']);
		}
		if(!empty($data['load_location'])) {
			$redstag_db->where('manifest.load_location', $data['load_location']);
		}
		if(!empty($data['container_type'])) {
			$redstag_db->where('manifest.container_type', $data['container_type']);
		}
		if(!empty($data['carrier'])) {
			$redstag_db->where('manifest.manifest_courier_code', $data['carrier']);
		}
		
		if($data['sort'] == 'load_location') {
			$redstag_db->order_by('manifest.load_location, manifest.created_at DESC');
		}
		else if($data['sort'] == 'utilization') {
			$redstag_db->order_by('blow_out DESC, manifest.created_at DESC');
		}
		else {
			$redstag_db->order_by('manifest.created_at DESC');
		}
		
		if($data['utilization'] == 'green') {
			$redstag_db->where('manifest.weight <', 30000);
		}
		else if($data['utilization'] == 'yellow') {
			$redstag_db->where('manifest.weight >=', 30000);
			$redstag_db->where('manifest.weight <', 39200);
		}
		else if($data['utilization'] == 'red') {
			$redstag_db->where('manifest.weight >=', 39200);
		}
		
		$manifest_data = $redstag_db->get()->result_array();
		
		if(!empty($manifest_data)) {
			foreach($manifest_data as $key => $manifest) {
				if($manifest['weight'] < 30000) {
					$manifest_data[$key]['color'] = 'green';
				}
				else if($manifest['weight'] < 39200) {
					$manifest_data[$key]['color'] = 'yellow';
				}
				else {
					$manifest_data[$key]['color'] = 'red';
				}
				
				$manifest_data[$key]['container_type_name'] = isset($data['container_type_list'][$manifest['container_type']]) ? $data['container_type_list'][$manifest['container_type']] : $manifest['container_type'];
			
				$manifest_data[$key]['carrier_name'] = isset($data['carrier_list'][$manifest['carrier_code']]) ? $data['carrier_list'][$manifest['carrier_code']] : $manifest['carrier_code'];
			}
		}
		
		$data['manifest_data'] = $manifest_data;
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['loading_andon_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_loading_andon_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_loading_utilization_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$data['carrier_list'] = array(
			'express' => 'Express',
			'fedex' => 'FedEx Ground',
			'smartpost' => 'FedEx SmartPost',
			'ups' => 'UPS',
			'upsnext' => 'UPS Next',
			'usps' => 'USPS',
			'ontrac' => 'OnTrac'
		);
		
		$data['container_type_list'] = array(
			'pallet' => 'Pallet',
			'rolling_bin' => 'Rolling Bin',
			'flat_cart' => 'Flat Cart',
			'truck_trailer' => 'Truck Trailer'
		);
		
		$data['manifest_status_list'] = array(
			'loaded' => 'Loaded',
			'open' => 'Open',
			'sealed' => 'Sealed'
		);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$timezone_name = 'US/Eastern';
		if($stock_id == 3 || $stock_id == 6) $timezone_name = 'US/Mountain';
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		$redstag_db
			->select("manifest.increment_id AS manifest_no,
				IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) AS start,
				IF(manifest.stock_id IN (3,6), CONVERT_TZ(manifest.completed_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.completed_at,'UTC','US/Eastern')) AS end,
				manifest.manifest_courier_code AS carrier_code,
				manifest.packages AS total_packages,
				manifest.weight AS total_weight,
				SUM(length*width*height / 1728) AS total_cubic_ft,
				manifest.weight/40000*100 AS weight_percentage,
				SUM(length*width*height / 1728) / (8*8*53) * 100 AS cubic_ft_percentage,
				manifest.container_type, manifest.load_location, manifest.weight, manifest.status", false)
			->from('manifest_item')
			->join('manifest', 'manifest.manifest_id = manifest_item.manifest_id')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = manifest_item.package_id')
			->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) >=", "'".$period_from."'", false)
			->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) <", "'".date('Y-m-d', strtotime('+1 day '.$period_to))."'", false)
			->group_by('manifest.manifest_id');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('manifest.stock_id', $stock_id);
		}
		if(!empty($data['status'])) {
			$redstag_db->where('manifest.status', $data['status']);
		}
		if(!empty($data['load_location'])) {
			$redstag_db->where('manifest.load_location', $data['load_location']);
		}
		if(!empty($data['container_type'])) {
			$redstag_db->where('manifest.container_type', $data['container_type']);
		}
		if(!empty($data['carrier'])) {
			$redstag_db->where('manifest.manifest_courier_code', $data['carrier']);
		}
		
		if($data['sort'] == 'weight_percentage') {
			$redstag_db->order_by('weight_percentage ASC, manifest.created_at DESC');
		}
		else if($data['sort'] == 'cubic_ft_percentage') {
			$redstag_db->order_by('cubic_ft_percentage ASC, manifest.created_at DESC');
		}
		else {
			$redstag_db->order_by('manifest.created_at DESC');
		}
		
		if($data['utilization'] == 'green') {
			$redstag_db->where('manifest.weight <', 30000);
		}
		else if($data['utilization'] == 'yellow') {
			$redstag_db->where('manifest.weight >=', 30000);
			$redstag_db->where('manifest.weight <', 39200);
		}
		else if($data['utilization'] == 'red') {
			$redstag_db->where('manifest.weight >=', 39200);
		}
		
		$manifest_data = $redstag_db->get()->result_array();
		$manifest_data_by_manifest_no = array();
		
		$customer_count_in_manifest = array();
		
		$redstag_db
			->select("manifest.increment_id AS manifest_no,
				core_store.name AS customer_name,
				IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) AS start,
				IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.completed_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.completed_at,'UTC','US/Eastern')) AS end,
				manifest.manifest_courier_code AS carrier_code,
				COUNT(sales_flat_shipment_package.package_id) AS total_packages,
				SUM(sales_flat_shipment_package.weight) AS total_weight,
				SUM(length*width*height / 1728) AS total_cubic_ft,
				SUM(sales_flat_shipment_package.weight)/40000*100 AS weight_percentage,
				SUM(length*width*height / 1728) / (8*8*53) * 100 AS cubic_ft_percentage,
				manifest.container_type, manifest.load_location, manifest.weight, manifest.status", false)
			->from('manifest_item')
			->join('manifest', 'manifest.manifest_id = manifest_item.manifest_id')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = manifest_item.package_id')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('core_store', 'core_store.store_id = sales_flat_order.store_id')
			->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) >=", "'".$period_from."'", false)
			->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) <", "'".date('Y-m-d', strtotime('+1 day '.$period_to))."'", false)
			->group_by('manifest.manifest_id, core_store.name');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('manifest.stock_id', $stock_id);
		}
		if(!empty($data['status'])) {
			$redstag_db->where('manifest.status', $data['status']);
		}
		if(!empty($data['load_location'])) {
			$redstag_db->where('manifest.load_location', $data['load_location']);
		}
		if(!empty($data['container_type'])) {
			$redstag_db->where('manifest.container_type', $data['container_type']);
		}
		if(!empty($data['carrier'])) {
			$redstag_db->where('manifest.manifest_courier_code', $data['carrier']);
		}
		
		if($data['sort'] == 'weight_percentage') {
			$redstag_db->order_by('weight_percentage ASC, manifest.created_at DESC');
		}
		else if($data['sort'] == 'cubic_ft_percentage') {
			$redstag_db->order_by('cubic_ft_percentage ASC, manifest.created_at DESC');
		}
		else {
			$redstag_db->order_by('manifest.created_at DESC');
		}
		
		$manifest_by_customer_data = $redstag_db->get()->result_array();
		
		foreach($manifest_by_customer_data as $key => $manifest) {
			if($manifest['total_weight'] < 30000) {
				$manifest_by_customer_data[$key]['color'] = 'green';
			}
			else if($manifest['total_weight'] < 39200) {
				$manifest_by_customer_data[$key]['color'] = 'yellow';
			}
			else {
				$manifest_by_customer_data[$key]['color'] = 'red';
			}
			
			if($manifest['weight_percentage'] < 50) {
				$manifest_by_customer_data[$key]['weight_utilization_color'] = 'red';
			}
			else if($manifest['weight_percentage'] < 70) {
				$manifest_by_customer_data[$key]['weight_utilization_color'] = 'yellow';
			}
			else {
				$manifest_by_customer_data[$key]['weight_utilization_color'] = 'green';
			}
			
			if($manifest['cubic_ft_percentage'] < 50) {
				$manifest_by_customer_data[$key]['cubic_ft_utilization_color'] = 'red';
			}
			else if($manifest['cubic_ft_percentage'] < 70) {
				$manifest_by_customer_data[$key]['cubic_ft_utilization_color'] = 'yellow';
			}
			else {
				$manifest_by_customer_data[$key]['cubic_ft_utilization_color'] = 'green';
			}
			
			if(!isset($customer_count_in_manifest[$manifest['manifest_no']])) {
				$customer_count_in_manifest[$manifest['manifest_no']] = 1;
			}
			else {
				$customer_count_in_manifest[$manifest['manifest_no']]++;
			}
		}
		
		if(isset($data['breakdown']) && $data['breakdown'] == 'customer') {
			$data['manifest_by_customer_data'] = $manifest_by_customer_data;
		}
		
		if(!empty($manifest_data)) {
			foreach($manifest_data as $key => $manifest) {				
				if($manifest['total_weight'] < 30000) {
					$manifest_data[$key]['color'] = 'green';
				}
				else if($manifest['total_weight'] < 39200) {
					$manifest_data[$key]['color'] = 'yellow';
				}
				else {
					$manifest_data[$key]['color'] = 'red';
				}
				
				if($manifest['weight_percentage'] < 50) {
					$manifest_data[$key]['weight_utilization_color'] = 'red';
				}
				else if($manifest['weight_percentage'] < 70) {
					$manifest_data[$key]['weight_utilization_color'] = 'yellow';
				}
				else {
					$manifest_data[$key]['weight_utilization_color'] = 'green';
				}
				
				if($manifest['cubic_ft_percentage'] < 50) {
					$manifest_data[$key]['cubic_ft_utilization_color'] = 'red';
				}
				else if($manifest['cubic_ft_percentage'] < 70) {
					$manifest_data[$key]['cubic_ft_utilization_color'] = 'yellow';
				}
				else {
					$manifest_data[$key]['cubic_ft_utilization_color'] = 'green';
				}
				
				$manifest_data[$key]['container_type_name'] = isset($data['container_type_list'][$manifest['container_type']]) ? $data['container_type_list'][$manifest['container_type']] : $manifest['container_type'];
			
				$manifest_data[$key]['carrier_name'] = isset($data['carrier_list'][$manifest['carrier_code']]) ? $data['carrier_list'][$manifest['carrier_code']] : $manifest['carrier_code'];
				
				$manifest_data[$key]['customer_count'] = $customer_count_in_manifest[$manifest['manifest_no']];
				
				$manifest_data_by_manifest_no[$manifest['manifest_no']] = $manifest_data[$key];
			}
		}
		
		$data['manifest_data'] = $manifest_data;
		$data['manifest_data_by_manifest_no'] = $manifest_data_by_manifest_no;
		
		$data['js_loading_utilization_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_loading_utilization_board_visualization', $data, true);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['loading_utilization_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_loading_utilization_board_visualization', $data, true);
		
		return $data;
	}
}