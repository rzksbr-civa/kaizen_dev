<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_sla extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_sla_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(empty($data['facility']) || empty($data['carrier']) || empty($data['period_from']) || empty($data['period_to'])) {
			$data['error_message'] = 'Please fill in facility, carrier, period from, and period to.';
			$data['page_generated_time'] = null;
			$data['sla_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_sla_board_visualization', $data, true);
			return $data;
		}
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$stock_ids = array($stock_id);
		if($stock_id == 2) {
			$stock_ids[] = 4; // Combine TYS-1 + TYS-2
		}
		
		$timezone_name = ($timezone == -7) ? 'US/Mountain' : 'US/Eastern';
		
		// Get carrier SLA
		$carrier_sla_data = $this->db
			->select('sla_cap')
			->from('carrier_sla')
			->where('data_status', DATA_ACTIVE)
			->where('facility', $data['facility'])
			->where('carrier_code', $data['carrier'])
			->get()->result_array();
		
		if(empty($carrier_sla_data)) {
			$data['error_message'] = 'Carrier SLA data for this facility and carrier has not been set. Please set it first under Settings > Facility';
			$data['page_generated_time'] = null;
			$data['sla_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_sla_board_visualization', $data, true);
			return $data;
		}
		
		$sla_cap = $carrier_sla_data[0]['sla_cap'];
		
		$recent_packages_data = $redstag_db
			->select('core_store.name AS store_name, COUNT(*) AS package_qty')
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_shipment.order_id = sales_flat_order.entity_id')
			->join('core_store', 'core_store.store_id = sales_flat_order.store_id')
			->where("CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','".$timezone_name."') >= '".$this->db->escape_str($data['period_from'])."'", null, false)
			->where("CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','".$timezone_name."') < '".$this->db->escape_str($data['period_to'])."'", null, false)
			->where_in('sales_flat_shipment_package.stock_id', $stock_ids)
			->where('sales_flat_shipment_package.carrier_code', $data['carrier'])
			->where('sales_flat_shipment.defunct', 0)
			->group_by('core_store.name')
			->order_by('core_store.name')
			->get()->result_array();
				
		$total_package_count = 0;
		foreach($recent_packages_data as $key => $current_data) {
			$total_package_count += $current_data['package_qty'];
		}
		
		$sla_categories = array('< 1', '1-10', '11-50', '51-100', '> 100');
		$data['sla_board_summary_data'] = array();
		foreach($sla_categories as $sla_category) {
			$data['sla_board_summary_data'][$sla_category] = array(
				'customers_count' => 0,
				'percentage' => 0
			);
		}
		
		$data['total'] = array(
			'sla' => 0,
			'todays_total_orders_count' => 0,
			'todays_completed_orders_count' => 0,
			'todays_remaining_orders_count' => 0,
		);
		
		$data['sla_board_data'] = array();
		foreach($recent_packages_data as $key => $current_data) {
			$sla = $total_package_count > 0 ? $current_data['package_qty'] / $total_package_count * $sla_cap : 0;
			
			$data['total']['sla'] += $sla;
			
			$data['sla_board_data'][$current_data['store_name']] = array(
				'sla' => $sla,
				'todays_total_orders_count' => 0,
				'todays_completed_orders_count' => 0,
				'todays_remaining_orders_count' => 0,
			);
			
			if($sla < 1) {
				$data['sla_board_summary_data']['< 1']['customers_count']++;
			}
			else if($sla >= 1 && $sla < 10) {
				$data['sla_board_summary_data']['1-10']['customers_count']++;
			}
			else if($sla >= 11 && $sla < 50) {
				$data['sla_board_summary_data']['11-50']['customers_count']++;
			}
			else if($sla >= 51 && $sla < 100) {
				$data['sla_board_summary_data']['51-100']['customers_count']++;
			}
			else if($sla > 100) {
				$data['sla_board_summary_data']['> 100']['customers_count']++;
			}
		}
		
		// Find today's total orders
		$todays_orders_data = $redstag_db
			->select('core_store.name AS store_name, COUNT(DISTINCT sales_flat_shipment.order_id) AS total_orders')
			->from('sales_flat_shipment')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('core_store', 'core_store.store_id = sales_flat_order.store_id')
			->where('sales_flat_shipment.target_ship_date', date('Y-m-d'))
			->where('sales_flat_order.can_fulfill', 1)
			->where('sales_flat_shipment.defunct', 0)
			->where_in('sales_flat_shipment.stock_id', $stock_ids)
			->group_by('core_store.name')
			->get()->result_array();
		
		foreach($todays_orders_data as $current_data) {
			if(!isset($data['sla_board_data'][$current_data['store_name']])) {
				$data['sla_board_data'][$current_data['store_name']] = array(
					'sla' => 0,
					'todays_total_orders_count' => 0,
					'todays_completed_orders_count' => 0,
					'todays_remaining_orders_count' => 0,
				);
			}
			
			$data['sla_board_data'][$current_data['store_name']]['todays_total_orders_count'] = $current_data['total_orders'];
			$data['total']['todays_total_orders_count'] += $current_data['total_orders'];
		}
		
		// Find today's completed orders
		$todays_completed_orders_data = $redstag_db
			->select('core_store.name AS store_name, COUNT(DISTINCT sales_flat_shipment.order_id) AS total_completed_orders')
			->from('sales_flat_shipment')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('core_store', 'core_store.store_id = sales_flat_order.store_id')
			->where('sales_flat_shipment.target_ship_date', date('Y-m-d'))
			->where('sales_flat_order.can_fulfill', 1)
			->where('sales_flat_shipment.defunct', 0)
			->where('sales_flat_order.completed_at IS NOT NULL', null, false)
			->where_in('sales_flat_shipment.stock_id', $stock_ids)
			->group_by('core_store.name')
			->get()->result_array();
			
		foreach($todays_completed_orders_data as $current_data) {
			$data['sla_board_data'][$current_data['store_name']]['todays_completed_orders_count'] = $current_data['total_completed_orders'];
			$data['sla_board_data'][$current_data['store_name']]['todays_remaining_orders_count'] = $data['sla_board_data'][$current_data['store_name']]['todays_total_orders_count'] - $data['sla_board_data'][$current_data['store_name']]['todays_completed_orders_count'];
			
			$data['total']['todays_completed_orders_count'] += $data['sla_board_data'][$current_data['store_name']]['todays_completed_orders_count'];
			$data['total']['todays_remaining_orders_count'] += $data['sla_board_data'][$current_data['store_name']]['todays_remaining_orders_count'];
		}
		
		$total_customers = count($data['sla_board_data']);
		
		foreach($data['sla_board_summary_data'] as $key => $current_data) {
			$data['sla_board_summary_data'][$key]['percentage'] = $total_customers > 0 ? round($current_data['customers_count'] / $total_customers * 100) . '%' : '';
		}

		$data['sla_cap'] = $sla_cap;
		
		$data['sla_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_sla_board_visualization', $data, true);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		return $data;
	}
}