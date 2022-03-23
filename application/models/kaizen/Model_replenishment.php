<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_replenishment extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_replenishment_release_board_data($data) {
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I');
		
		$replenishment_release_board_data = $this->get_replenishment_stock_data($data);
		
		$data['replenishment_release_board_2d_data'] = array();
		foreach($replenishment_release_board_data as $current_data) {
			$data['replenishment_release_board_2d_data'][] = array(
				$current_data['sku'],
				$current_data['count_of_orders_with_sku'],
				number_format(ceil($current_data['average_of_item_count_per_order']),0,'.',''),
				number_format($current_data['standard_deviation'],1,'.',''),
				number_format($current_data['sum_of_items'],0,'.',''),
				number_format($current_data['average_daily_demand'],1,'.',''),
				$current_data['cumulative'],
				number_format($current_data['cumulative_percentage']).'%',
				$current_data['sku_tier'],
				$current_data['replenish_freq'],
				number_format($current_data['safety_stock'],1,'.',''),
				number_format(ceil($current_data['reorder_point']),0,'.',''),
				number_format($current_data['current_pickable_stock'],0,'.',''),
				number_format($current_data['current_nonpickable_stock'],0,'.',''),
				number_format($current_data['total_stock_on_hand'],0,'.',''),
				number_format($current_data['stock_needed_for_service_level'],1,'.',''),
				$current_data['need_restock']
			);
		}
		
		$data['replenishment_release_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_replenishment_release_board_visualization', $data, true);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		return $data;
	}
	
	public function get_replenishment_stock_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data_tmp = $this->db
				->select('*')
				->from('facilities')
				->where('data_status',DATA_ACTIVE)
				->where('id',$data['facility'])
				->get()->result_array();
			$facility_data = $facility_data_tmp[0];
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : null;
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I');
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d', strtotime('-30 day'));
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		if(!isset($data['default_replenish_freq_tier'])) {
			$data['default_replenish_freq_tier'] = array(
				1 => 3,
				2 => 7,
				3 => 14,
				4 => 30,
				5 => 30
			);
			
			$data['replenish_freq_tier_1'] = $data['default_replenish_freq_tier'][1];
			$data['replenish_freq_tier_2'] = $data['default_replenish_freq_tier'][2];
			$data['replenish_freq_tier_3'] = $data['default_replenish_freq_tier'][3];
			$data['replenish_freq_tier_4'] = $data['default_replenish_freq_tier'][4];
			$data['replenish_freq_tier_5'] = $data['default_replenish_freq_tier'][5];
		}
		
		$num_days = (strtotime($period_to) - strtotime($period_from))/86400 + 1;
		$z_score = $this->normsinv($data['service_level_percentage']/100);
		
		// New and in processing SKU
		/*$new_and_in_processing_skus_data = $redstag_db->query("SELECT DISTINCT(catalog_product_entity.sku) AS sku
			FROM sales_flat_shipment_item
			JOIN sales_flat_shipment ON sales_flat_shipment.entity_id = sales_flat_shipment_item.parent_id
			JOIN sales_flat_order ON sales_flat_order.entity_id = sales_flat_shipment.order_id
			JOIN catalog_product_entity ON catalog_product_entity.entity_id = sales_flat_shipment_item.product_id
			WHERE sales_flat_shipment.target_ship_date = ".$redstag_db->escape(date('Y-m-d'))."
			AND sales_flat_order.status IN ('new', 'processing')")->result_array();
		
		$new_and_in_processing_skus = array();
		if(!empty($new_and_in_processing_skus_data)) {
			$new_and_in_processing_skus = array_column($new_and_in_processing_skus_data, 'sku');
		}*/
		
		// Current pickable stock
		
		$redstag_db
			->select(
				'catalog_product_entity.sku,
				SUM(cataloginventory_stock_location.qty_unreserved) AS stock_qty', false)
			->from('cataloginventory_stock_location')
			->join('cataloginventory_stock_item', 'cataloginventory_stock_item.item_id = cataloginventory_stock_location.stock_item_id')
			->join('cataloginventory_product', 'cataloginventory_product.product_id = cataloginventory_stock_item.product_id')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id')
			->where('cataloginventory_stock_location.qty_unreserved >=', 1)
			->where('cataloginventory_stock_location.qty_reserved <=', 1)
			->where('cataloginventory_stock_location.is_pickable', -1)
			->group_by('sku');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('cataloginventory_stock_location.stock_id', $stock_id);
		}
		
		$current_pickable_stock_data = $redstag_db->get()->result_array();
		
		$current_pickable_stock_data = array_combine(
			array_column($current_pickable_stock_data, 'sku'),
			array_column($current_pickable_stock_data, 'stock_qty')
		);
		
		// Current nonpickable stock
		
		$redstag_db
			->select(
				'catalog_product_entity.sku,
				SUM(cataloginventory_stock_location.qty_unreserved) AS stock_qty', false)
			->from('cataloginventory_stock_location')
			->join('cataloginventory_stock_item', 'cataloginventory_stock_item.item_id = cataloginventory_stock_location.stock_item_id')
			->join('cataloginventory_product', 'cataloginventory_product.product_id = cataloginventory_stock_item.product_id')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id')
			->where('cataloginventory_stock_location.qty_unreserved >=', 1)
			->where('cataloginventory_stock_location.qty_reserved <=', 1)
			->where('cataloginventory_stock_location.is_pickable', 0)
			->group_by('sku');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('cataloginventory_stock_location.stock_id', $stock_id);
		}
		
		$current_nonpickable_stock_data = $redstag_db->get()->result_array();
		
		$current_nonpickable_stock_data = array_combine(
			array_column($current_nonpickable_stock_data, 'sku'),
			array_column($current_nonpickable_stock_data, 'stock_qty')
		);
		
		$local_completed_time_field = "IF(sales_flat_order_stock.stock_id IN (3,6),CONVERT_TZ(sales_flat_order.completed_at,'UTC', 'US/Mountain'),CONVERT_TZ(sales_flat_order.completed_at,'UTC','US/Eastern'))";
		
		$redstag_db
			->select(
				'sales_flat_order_item.sku,
				COUNT(*) AS count_of_orders_with_sku,
				AVG(qty_ordered) AS average_of_item_count_per_order,
				SUM(qty_ordered) AS sum_of_items,
				STD(qty_ordered) AS standard_deviation', false)
			->from('sales_flat_order_item')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_item.order_id')
			->join('sales_flat_order_stock', 'sales_flat_order_stock.order_id = sales_flat_order.entity_id', 'right')
			->where($local_completed_time_field . ' >= \''.$period_from.'\'', null, false)
			->where($local_completed_time_field . ' < \''.$period_to.'\'', null, false)
			->group_by('sales_flat_order_item.sku')
			->order_by('sum_of_items', 'desc');
			
		if(!empty($data['facility'])) {
			$redstag_db->where('sales_flat_order_stock.stock_id', $stock_id);
		}
		
		if(!empty($data['customer'])) {
			$redstag_db->where('sales_flat_order.store_id', $data['customer']);
		}
		
		$replenishment_release_board_data = $redstag_db->get()->result_array();
		
		$cumulative = 0;
		foreach($replenishment_release_board_data as $key => $current_data) {
			$cumulative += $current_data['sum_of_items'];
			
			$replenishment_release_board_data[$key]['average_daily_demand'] = !empty($num_days) ? $current_data['sum_of_items'] / $num_days : 0;
			$replenishment_release_board_data[$key]['cumulative'] = $cumulative;
		}
		
		foreach($replenishment_release_board_data as $key => $current_data) {
			$cumulative_percentage = $current_data['cumulative'] / $cumulative * 100;
			$replenishment_release_board_data[$key]['cumulative_percentage'] = $cumulative_percentage;
			
			if($cumulative_percentage <= 20) {
				$sku_tier = 'Tier-1';
				$replenish_freq = $data['replenish_freq_tier_1'];
			}
			else if($cumulative_percentage <= 50) {
				$sku_tier = 'Tier-2';
				$replenish_freq = $data['replenish_freq_tier_2'];
			}
			else if($cumulative_percentage <= 70) {
				$sku_tier = 'Tier-3';
				$replenish_freq = $data['replenish_freq_tier_3'];
			}
			else if($cumulative_percentage <= 90) {
				$sku_tier = 'Tier-4';
				$replenish_freq = $data['replenish_freq_tier_4'];
			}
			else {
				$sku_tier = 'Tier-5';
				$replenish_freq = $data['replenish_freq_tier_5'];
			}
			
			if(!empty($data['sku_tier']) && !in_array($sku_tier, $data['sku_tier'])) {
				unset($replenishment_release_board_data[$key]);
				continue;
			}
			
			$replenishment_release_board_data[$key]['sku_tier'] = $sku_tier;
			$replenishment_release_board_data[$key]['replenish_freq'] = $replenish_freq;
			
			$replenishment_release_board_data[$key]['safety_stock'] = $current_data['average_daily_demand'] * $z_score;
			$replenishment_release_board_data[$key]['reorder_point'] = $current_data['average_daily_demand'] * $replenish_freq + $replenishment_release_board_data[$key]['safety_stock'];
			
			$replenishment_release_board_data[$key]['current_pickable_stock'] = !empty($current_pickable_stock_data[$current_data['sku']]) ? $current_pickable_stock_data[$current_data['sku']] : 0;
			$replenishment_release_board_data[$key]['current_nonpickable_stock'] = !empty($current_nonpickable_stock_data[$current_data['sku']]) ? $current_nonpickable_stock_data[$current_data['sku']] : 0;
			
			$replenishment_release_board_data[$key]['total_stock_on_hand'] = $replenishment_release_board_data[$key]['current_pickable_stock'] + $replenishment_release_board_data[$key]['current_nonpickable_stock'];
			
			$replenishment_release_board_data[$key]['stock_needed_for_service_level'] = ceil($replenishment_release_board_data[$key]['safety_stock'] + $replenishment_release_board_data[$key]['reorder_point']);
			
			$replenishment_release_board_data[$key]['need_restock'] = ($replenishment_release_board_data[$key]['current_nonpickable_stock'] == 0 || $replenishment_release_board_data[$key]['current_pickable_stock'] > $replenishment_release_board_data[$key]['stock_needed_for_service_level']) ? 'No' : 'Yes';
		}
		
		return $replenishment_release_board_data;
	}
	
	public function normsinv($probability) {
		$a1 = -39.6968302866538; 
		$a2 = 220.946098424521;
		$a3 = -275.928510446969;
		$a4 = 138.357751867269;
		$a5 = -30.6647980661472;
		$a6 = 2.50662827745924;

		$b1 = -54.4760987982241;
		$b2 = 161.585836858041;
		$b3 = -155.698979859887;
		$b4 = 66.8013118877197;
		$b5 = -13.2806815528857;

		$c1 = -7.78489400243029E-03;
		$c2 = -0.322396458041136;
		$c3 = -2.40075827716184;
		$c4 = -2.54973253934373;
		$c5 = 4.37466414146497;
		$c6 = 2.93816398269878;

		$d1 = 7.78469570904146E-03;
		$d2 = 0.32246712907004;
		$d3 = 2.445134137143;
		$d4 =  3.75440866190742;

		$p_low = 0.02425;
		$p_high = 1 - $p_low;
		$q = 0;
		$r = 0;
		$normSInv = 0;
		if ($probability < 0 ||
			$probability > 1)
		{
			throw new \Exception("normSInv: Argument out of range.");
		} else if ($probability < $p_low) {

			$q = sqrt(-2 * log($probability));
			$normSInv = ((((($c1 * $q + $c2) * $q + $c3) * $q + $c4) * $q + $c5) * $q + $c6) / (((($d1 * $q + $d2) * $q + $d3) * $q + $d4) * $q + 1);

		} else if ($probability <= $p_high) {

			$q = $probability - 0.5;
			$r = $q * $q;
			$normSInv = ((((($a1 * $r + $a2) * $r + $a3) * $r + $a4) * $r + $a5) * $r + $a6) * $q / ((((($b1 * $r + $b2) * $r + $b3) * $r + $b4) * $r + $b5) * $r + 1);

		} else {

			$q = sqrt(-2 * log(1 - $probability));
			$normSInv = -((((($c1 * $q + $c2) * $q + $c3) * $q + $c4) * $q + $c5) * $q + $c6) /(((($d1 * $q + $d2) * $q + $d3) * $q + $d4) * $q + 1);

		}

		return $normSInv;
	}
	
	public function get_client_inventory_replenishment_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I');
		
		$local_created_time_field = "IF(sales_flat_order_stock.stock_id IN (3,6),CONVERT_TZ(sales_flat_order.created_at,'UTC', 'US/Mountain'),CONVERT_TZ(sales_flat_order.created_at,'UTC','US/Eastern'))";
		
		/*$redstag_db
			->select('sku, SUM(sales_flat_order_item.qty_backordered) AS total_qty_backordered', false)
			->from('sales_flat_order_item')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_item.order_id')
			->where('sales_flat_order.status', 'backordered')
			->where('sales_flat_order.created_at >= \''.date('Y-m-d', strtotime('-90 day')).'\'', null, false)
			->where('sales_flat_order.created_at <', date('Y-m-d', strtotime('+1 day')))
			->group_by('sku');
		
		if(!empty($data['customer'])) {
			$redstag_db->where('sales_flat_order.store_id', $data['customer']);
		}*/
		
		$redstag_db
			->select('sku, qty_backordered AS total_qty_backordered')
			->from('cataloginventory_product')
			->join('catalog_product_entity', 'cataloginventory_product.product_id = catalog_product_entity.entity_id');
		
		$backorder_data = $redstag_db->get()->result_array();
		
		// Incoming Deliveries
		$redstag_db
			->select('sku, SUM(delivery_item.qty_expected) AS total_qty_expected', false)
			->from('delivery_item')
			->join('cataloginventory_product', 'cataloginventory_product.product_id = delivery_item.product_id')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id')
			->join('delivery', 'delivery.delivery_id = delivery_item.delivery_id')
			->join('core_website', 'core_website.website_id = delivery.website_id')
			->where('delivery.delivery_type', 'asn')
			->where('delivery.status', 'new')
			->group_by('sku')
			->order_by('sku');
		
		if(!empty($data['customer'])) {
			$redstag_db->where('core_website.default_store_id', $data['customer']);
		}
		
		$incoming_deliveries_data = $redstag_db->get()->result_array();
		
		// Ontime Incoming Deliveries
		$redstag_db
			->select('sku, SUM(delivery_item.qty_expected) AS total_qty_expected', false)
			->from('delivery_item')
			->join('cataloginventory_product', 'cataloginventory_product.product_id = delivery_item.product_id')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id')
			->join('delivery', 'delivery.delivery_id = delivery_item.delivery_id')
			->join('core_website', 'core_website.website_id = delivery.website_id')
			->where('expected_delivery >=', date('Y-m-d'))
			->where('delivery.delivery_type', 'asn')
			->where('delivery.status', 'new')
			->group_by('sku')
			->order_by('sku');
		
		if(!empty($data['customer'])) {
			$redstag_db->where('core_website.default_store_id', $data['customer']);
		}
		
		$ontime_incoming_deliveries_data = $redstag_db->get()->result_array();
		
		// Last 90 days
		
		$redstag_db
			->select('sales_flat_order_item.product_id, catalog_product_entity.sku, catalog_product_entity.name AS product_name, qty_available, ROUND(SUM(qty_ordered)) AS total_qty', false)
			->from('sales_flat_order_item')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_item.order_id')
			->join('cataloginventory_product', 'cataloginventory_product.product_id = sales_flat_order_item.product_id')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = sales_flat_order_item.product_id')
			->where('sales_flat_order.created_at >=', date('Y-m-d', strtotime('-90 day')))
			->group_by('sales_flat_order_item.product_id, sku, qty_available');
		
		if(!empty($data['customer'])) {
			$redstag_db->where('sales_flat_order.store_id', $data['customer']);
		}
		
		if(empty($data['sort_order']) || $data['sort_order'] == 'highest_to_lowest') {
			$redstag_db->order_by('total_qty', 'desc');
		}
		else {
			$redstag_db->order_by('total_qty', 'asc');
		}
		
		$last_ninety_days_order_data = $redstag_db->get()->result_array();
		
		$sku_data = array();
		$total_ordered_item = 0;
		foreach($last_ninety_days_order_data as $current_data) {
			$sku_data[$current_data['sku']] = array(
				'sku_status' => null,
				'product_name' => $current_data['product_name'],
				'sum_of_item_count' => $current_data['total_qty'],
				'ninety_days_average' => $current_data['total_qty'] / 90,
				'qty_available' => $current_data['qty_available'],
				'backorders' => null,
				'incoming_deliveries' => null
			);
			
			$total_ordered_item += $current_data['total_qty'];
		}
		
		$cumulative_share_of_order_volume = 0;
		if(!empty($sku_data)) {
			foreach($sku_data as $sku => $current_data) {
				$sku_data[$sku]['share_of_order_volume'] = $current_data['sum_of_item_count'] / $total_ordered_item * 100;
				
				$cumulative_share_of_order_volume += $sku_data[$sku]['share_of_order_volume'];
				$sku_data[$sku]['cumulative_share_of_order_volume'] = $cumulative_share_of_order_volume;
			}
		}
		
		$total_backordered_qty = 0;
		foreach($backorder_data as $current_data) {
			if(isset($sku_data[$current_data['sku']])) {
				$sku_data[$current_data['sku']]['backorders'] = $current_data['total_qty_backordered'];
				$total_backordered_qty += $current_data['total_qty_backordered'];
			}
		}
		
		foreach($incoming_deliveries_data as $current_data) {
			if(isset($sku_data[$current_data['sku']])) {
				$sku_data[$current_data['sku']]['incoming_deliveries'] = $current_data['total_qty_expected'];
			}
		}
		
		foreach($ontime_incoming_deliveries_data as $current_data) {
			if(isset($sku_data[$current_data['sku']])) {
				$sku_data[$current_data['sku']]['ontime_incoming_deliveries'] = $current_data['total_qty_expected'];
			}
		}
		
		$z_score = $this->normsinv($data['service_level_percentage']/100);
		
		$data['inventory_snapshot_chart_data'] = array(
			'out_of_stock' => 0,
			'running_out' => 0,
			'ok' => 0
		);
		
		$cumulative = 0;
		foreach($sku_data as $sku => $current_data) {
			$cumulative += $current_data['sum_of_item_count'];
			$cumulative_percentage = $cumulative / $total_ordered_item * 100;
			
			$sku_data[$sku]['cumulative'] = $cumulative;
			$sku_data[$sku]['cumulative_percentage'] = $cumulative_percentage;

			$sku_data[$sku]['safety_stock'] = $z_score * $current_data['ninety_days_average'];

			$sku_data[$sku]['inventory_after_deliveries'] =
				$sku_data[$sku]['qty_available'] +
				(isset($current_data['incoming_deliveries']) ? $current_data['incoming_deliveries'] : 0) -
				(isset($current_data['backorders']) ? $current_data['backorders'] : 0);
			
			$sku_data[$sku]['days_on_hand_inventory'] = isset($sku_data[$sku]['inventory_after_deliveries']) ? $sku_data[$sku]['inventory_after_deliveries'] / $current_data['ninety_days_average'] : null;
			
			if($current_data['qty_available'] == 0) {
				$data['inventory_snapshot_chart_data']['out_of_stock']++;
			}
			else if($current_data['qty_available'] < $sku_data[$sku]['safety_stock']) {
				$data['inventory_snapshot_chart_data']['running_out']++;
			}
			else {
				$data['inventory_snapshot_chart_data']['ok']++;
			}
		}
		
		$data['client_inventory_replenishment_board_2d_data'] = array();
		foreach($sku_data as $sku => $current_data) {
			$sku_status = '<span class="label label-success">OK</span>';
			if($current_data['qty_available'] == 0) {
				$sku_status = '<span class="label label-danger">OUT OF STOCK</span>';
			}
			else if($current_data['qty_available'] < ceil($current_data['safety_stock'])) {
				$sku_status = '<span class="label label-warning">RUNNING OUT</span>';
			}
			
			$projected_stock_out_date = '';
			if($current_data['qty_available'] > 0) {
				$projected_stock_out_date = date('Y-m-d', strtotime('+'.ceil($current_data['days_on_hand_inventory']).' day'));
			}
			
			
			$data['client_inventory_replenishment_board_2d_data'][] = array(
				'',
				$sku_status,
				$sku,
				$current_data['product_name'],
				$current_data['sum_of_item_count'],
				number_format(ceil($current_data['ninety_days_average']),0,'.',''),
				number_format($current_data['share_of_order_volume'],2).'%',
				round($current_data['qty_available']),
				isset($current_data['backorders']) ? round($current_data['backorders']) : '-',
				isset($current_data['incoming_deliveries']) ? round($current_data['incoming_deliveries']) : '-',
				isset($current_data['ontime_incoming_deliveries']) ? round($current_data['ontime_incoming_deliveries']) : '-',
				$current_data['cumulative'],
				number_format($current_data['cumulative_share_of_order_volume'],2).'%',
				number_format(ceil($current_data['safety_stock']),0,'.',''),
				isset($current_data['inventory_after_deliveries']) ? $current_data['inventory_after_deliveries'] : '-',
				isset($current_data['days_on_hand_inventory']) ? number_format(ceil($current_data['days_on_hand_inventory']),0,'.','') : '-',
				$projected_stock_out_date
			);
		}
		
		// Facilities
		$data['warehouse_inventory_distribution_chart_data'] = array();
		
		// Incoming Deliveries by Location
		$redstag_db
			->select('cataloginventory_stock.name, ROUND(SUM(delivery_item.qty_expected),0) AS total_incoming_deliveries')
			->from('delivery_item')
			->join('delivery', 'delivery.delivery_id = delivery_item.delivery_id')
			->join('cataloginventory_stock', 'cataloginventory_stock.stock_id = delivery.stock_id')
			->join('core_website', 'core_website.website_id = delivery.website_id')
			->where_not_in('delivery.stock_id', array(1,5))
			->where('delivery.delivery_type', 'asn')
			->where('delivery.status', 'new')
			->group_by('cataloginventory_stock.name');
			
		if(!empty($data['customer'])) {
			$redstag_db->where('core_website.default_store_id', $data['customer']);
		}
		
		$incoming_deliveries_by_location_tmp = $redstag_db->get()->result_array();
		
		if(!empty($incoming_deliveries_by_location_tmp)) {
			foreach($incoming_deliveries_by_location_tmp as $current_data) {
				$data['warehouse_inventory_distribution_chart_data'][$current_data['name']] = array(
					'On Hand' => 0,
					'Inbound' => $current_data['total_incoming_deliveries'],
					'Backorder' => 0,
				);
			}
		}
		
		// On Hand Inventory by Location
		$redstag_db
			->select('cataloginventory_stock.name AS warehouse_name, ROUND(SUM(qty_available),0) AS on_hand_qty')
			->from('cataloginventory_stock_item')
			->join('cataloginventory_stock', 'cataloginventory_stock.stock_id = cataloginventory_stock_item.stock_id')
			->where_not_in('cataloginventory_stock_item.stock_id', array(1,5))
			->group_by('cataloginventory_stock.name');
		
		if(!empty($data['customer'])) {
			if(!empty($last_ninety_days_order_data)) {
				$redstag_db->where_in('cataloginventory_stock_item.product_id', array_column($last_ninety_days_order_data,'product_id'));
			}
			else {
				$redstag_db->where_in('cataloginventory_stock_item.product_id', array(0));
			}
		}
			
		$on_hand_inventory_by_location_tmp = $redstag_db->get()->result_array();
		
		if(!empty($on_hand_inventory_by_location_tmp)) {
			foreach($on_hand_inventory_by_location_tmp as $current_data) {
				if(empty($data['warehouse_inventory_distribution_chart_data'][$current_data['warehouse_name']])) {
					$data['warehouse_inventory_distribution_chart_data'][$current_data['warehouse_name']] = array(
						'On Hand' => 0,
						'Inbound' => 0,
						'Backorder' => 0
					);
				}
				
				$data['warehouse_inventory_distribution_chart_data'][$current_data['warehouse_name']]['On Hand'] = $current_data['on_hand_qty'];
			}
		}
		
		$data['warehouse_inventory_distribution_chart_data']['Total Backorder'] = array(
			'On Hand' => 0,
			'Inbound' => 0,
			'Backorder' => $total_backordered_qty,
		);
		
		$data['client_inventory_replenishment_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_client_inventory_replenishment_board_visualization', $data, true);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		return $data;
	}
	
	public function get_sku_historical_demand_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$result = array();
		
		$redstag_db
			//->select('DATE_FORMAT(created_at,"%Y-%m-15") AS the_date, SUM(qty_ordered) AS demand_qty', false)
			->select('DATE_FORMAT(created_at,"%Y-%m-15") AS the_date, COUNT(DISTINCT order_id) AS demand_qty', false)
			->from('sales_flat_order_item')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_item.order_id')
			->group_by('DATE_FORMAT(created_at,"%Y-%m-15")')
			->order_by('the_date');
		
		if(!empty($data['sku'])) {
			$redstag_db->where('sku', $data['sku']);
		}
		else {
			$redstag_db->where('created_at >=', date('Y-01-01', strtotime('-3 year')));
		}
		
		if(!empty($data['customer'])) {
			$redstag_db->where('sales_flat_order_item.store_id', $data['customer']);
		}
		
		$historical_demand_data_tmp = $redstag_db->get()->result_array();
		
		$historical_demand = array();
		$result['historical_demand'] = array();
		$result['historical_demand_min_date'] = strtotime('now') * 1000;
		if(!empty($historical_demand_data_tmp)) {
			foreach($historical_demand_data_tmp as $current_data) {
				$timestamp = strtotime($current_data['the_date'])*1000;
				$historical_demand[$current_data['the_date']] = $current_data['demand_qty'];
				$result['historical_demand'][] = array(
					$timestamp,
					$current_data['demand_qty']
				);
				
				if($timestamp < $result['historical_demand_min_date']) {
					$result['historical_demand_min_date'] = $timestamp;
				}
			}
		}
		
		// Forecast
		$forecasted_demand = $historical_demand;
		$previous_month_date = date('Y-m-15', strtotime('-1 month'));
		
		$result['forecasted_demand'] = array();
		$result['forecasted_demand'][] = array(
			strtotime($previous_month_date)*1000,
			!empty($forecasted_demand[$previous_month_date]) ? $forecasted_demand[$previous_month_date] : 0
		);

		$current_month_date = date('Y-m-15');
		
		$forecasted_demand[$current_month_date] = date('t') / date('j') * $forecasted_demand[$current_month_date];
		$result['forecasted_demand'][] = array(
			strtotime($current_month_date)*1000,
			$forecasted_demand[$current_month_date]
		);

		$recent_growths = array();
		for($i=0; $i<6; $i++) {
			$current_month_date = date('Y-m-15', strtotime('+1 month '.$previous_month_date));
			
			if(isset($forecasted_demand[$current_month_date]) && !empty($forecasted_demand[$previous_month_date])) {
				$growth = $forecasted_demand[$current_month_date] / $forecasted_demand[$previous_month_date];
				if($growth > 1.3) {
					$recent_growths[] = 1.3;
				}
				else if($growth < 0.7) {
					$recent_growths[] = 0.7;
				}
				else {
					$recent_growths[] = $growth;
				}
			}
		}
		$average_recent_growth = !empty($recent_growths) ? array_sum($recent_growths) / count($recent_growths) : 1;
		
		$previous_month_date = date('Y-m-15');
		
		for($i=0; $i<3; $i++) {
			$current_month_date = date('Y-m-15', strtotime('+1 month '.$previous_month_date));
			$timestamp = strtotime($current_month_date)*1000;
			
			if(isset($forecasted_demand[$previous_month_date])) {
				$current_month_forecasted_demand = $forecasted_demand[$previous_month_date] * $average_recent_growth;
				$forecasted_demand[$current_month_date] = $current_month_forecasted_demand;
				
				$result['forecasted_demand'][] = array(
					$timestamp,
					$current_month_forecasted_demand
				);
			}
			else {
				$result['forecasted_demand'][] = array(
					$timestamp,
					$forecasted_demand[date('Y-m-15')]
				);
			}
			
			$previous_month_date = $current_month_date;
			array_shift($recent_growths);
			$recent_growths[] = $average_recent_growth;
			$average_recent_growth = !empty($recent_growths) ? array_sum($recent_growths) / count($recent_growths) : 1;
		}
		
		return $result;
	}
	
	public function get_historical_inventory_levels_graph_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$result = array();
		
		$historical_on_hand_qty = array();
		$historical_inbound_qty = array();
		$historical_order_volume = array();
		$forecasted_order_volume = array();
		
		// Inventory Levels = On Hand Qty + Inbound Qty - Order Volume
		$historical_inventory_levels = array();
		
		// Find Historical On Hand Qty
		
		//-- Find Current On Hand Qty
		$current_on_hand_qty = $this->get_current_on_hand_qty($data['sku']);
		$current_backordered_qty = $this->get_current_backordered_qty($data['sku']);
		
		//-- Find Historical stock movement
		$redstag_db
			->select('DATE(stock_movement.created_at) AS the_date, COALESCE(SUM(qty_putaway),0) + COALESCE(SUM(qty_available),0) + COALESCE(SUM(qty_allocated),0) + COALESCE(SUM(qty_reserved),0) + COALESCE(SUM(qty_processed),0) + COALESCE(SUM(qty_picked),0) AS total_qty_adjusted')
			->from('stock_movement')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = stock_movement.product_id')
			->group_by('DATE(stock_movement.created_at)')
			->order_by('the_date');
		
		if(!empty($data['sku'])) {
			$redstag_db->where('catalog_product_entity.sku', $data['sku']);
		}
		else {
			$redstag_db->where('stock_movement.created_at >=', date('Y-01-01', strtotime('-3 year')));
		}
		
		$historical_on_hand_qty_adjustment_tmp = $redstag_db->get()->result_array();
		
		$historical_on_hand_qty_adjustment = array();
		if(!empty($historical_on_hand_qty_adjustment_tmp)) {
			foreach($historical_on_hand_qty_adjustment_tmp as $current_data) {
				$historical_on_hand_qty_adjustment[$current_data['the_date']] = $current_data['total_qty_adjusted'];
			}
		}
		
		// $historical_on_hand_qty = On Hand Qty at the end of the month (snapshot)
		// $historical_average_on_hand_qty = Daily average of On Hand Qty in the given month (average)
		
		$current_date_timestamp = strtotime(date('Y-m-15'))*1000;
		$historical_on_hand_qty[$current_date_timestamp] = array(
			$current_date_timestamp,
			$current_on_hand_qty
		);
		
		$daily_on_hand_qty_this_month = array();
		$daily_on_hand_qty_this_month[] = $current_on_hand_qty;
		
		$the_date = date('Y-m-d', strtotime('yesterday'));
		$this_day_on_hand_qty = $current_on_hand_qty;
		
		while(strtotime($the_date) >= strtotime('2020-12-01')) {
			$the_following_date = date('Y-m-d', strtotime('+1 day '.$the_date));
			
			if(!empty($historical_on_hand_qty_adjustment[$the_following_date])) {
				$this_day_on_hand_qty -= $historical_on_hand_qty_adjustment[$the_following_date];
			}
			
			if(date('m', strtotime($the_date)) <> date('m', strtotime('+1 day '.$the_date))) {
				// End of the month
				$daily_on_hand_qty_this_month = array();
				
				$current_date_timestamp = strtotime(date('Y-m-15', strtotime($the_date)))*1000;
				$historical_on_hand_qty[$current_date_timestamp] = array(
					$current_date_timestamp,
					$this_day_on_hand_qty
				);
			}
			
			$daily_on_hand_qty_this_month[] = $this_day_on_hand_qty;
			
			if(date('j', strtotime($the_date)) == '1') {
				// 1st of the month
				$current_date_timestamp = strtotime(date('Y-m-15', strtotime($the_date)))*1000;
				$historical_average_on_hand_qty[$current_date_timestamp] = array(
					$current_date_timestamp,
					array_sum($daily_on_hand_qty_this_month)/count($daily_on_hand_qty_this_month)
				);
			}
			
			$the_date = date('Y-m-d', strtotime('-1 day '.$the_date));
		}
		
		/*$period_count = count($historical_on_hand_qty_adjustment_tmp);
		$on_hand_qty_tmp = $current_on_hand_qty;
		
		for($i=$period_count-1; $i>=0; $i--) {
			$current_data = $historical_on_hand_qty_adjustment_tmp[$i];
			if(strtotime($current_data['the_date']) < strtotime('2020-12-01')) {
				continue; // Inventory prior to 23 Nov 2020 was not correct, due to some bugs, so we exclude it.
			}
			$timestamp = strtotime('-1 month '.$current_data['the_date'])*1000;
			$on_hand_qty_tmp -= $current_data['total_qty_adjusted'];
			$historical_on_hand_qty[$timestamp] = array(
				$timestamp,
				$on_hand_qty_tmp
			);
		}*/
		
		// Find Historical Inbound Qty
		$redstag_db
			->select('DATE_FORMAT(COALESCE(delivered_at,expected_delivery),"%Y-%m-15") AS the_date, SUM(IF(qty_received > 0, qty_received, qty_expected)) AS inbound_qty')
			->from('delivery_item')
			->join('delivery', 'delivery.delivery_id = delivery_item.delivery_id')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = delivery_item.product_id')
			->group_start()
				->where('delivered_at IS NOT NULL', null, false)
				->where('delivered_at >', '2000-01-01')
			->group_end()
			->group_start()
				->where('expected_delivery IS NOT NULL', null, false)
				->where('expected_delivery >', '2000-01-01')
			->group_end()
			->group_by('DATE_FORMAT(COALESCE(delivered_at,expected_delivery),"%Y-%m-15")')
			->order_by('the_date');
		
		if(!empty($data['sku'])) {
			$redstag_db->where('catalog_product_entity.sku', $data['sku']);
		}
		
		$historical_inbound_qty_tmp = $redstag_db->get()->result_array();
		
		if(!empty($historical_inbound_qty_tmp)) {
			foreach($historical_inbound_qty_tmp as $current_data) {
				$timestamp = strtotime($current_data['the_date'])*1000;
				$historical_inbound_qty[$timestamp] = array(
					$timestamp,
					$current_data['inbound_qty']
				);
			}
		}
		
		// Find Historical Order Volume
		$redstag_db
			//->select('DATE_FORMAT(created_at,"%Y-%m-15") AS the_date, SUM(qty_ordered) AS demand_qty', false)
			->select('DATE_FORMAT(created_at,"%Y-%m-15") AS the_date, COUNT(DISTINCT order_id) AS demand_qty', false)
			->from('sales_flat_order_item')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_item.order_id')
			->group_by('DATE_FORMAT(created_at,"%Y-%m-15")')
			->order_by('the_date');
		
		if(!empty($data['sku'])) {
			$redstag_db->where('sku', $data['sku']);
		}
		
		$historical_order_volume_tmp = $redstag_db->get()->result_array();
		
		if(!empty($historical_order_volume_tmp)) {
			foreach($historical_order_volume_tmp as $current_data) {
				$timestamp = strtotime($current_data['the_date'])*1000;
				$historical_order_volume[$timestamp] = array(
					$timestamp,
					$current_data['demand_qty']
				);
			}
		}
		
		// Find Forecasted Order Volume
		
		// Process...
		$historical_inventory_levels = $historical_on_hand_qty;
		if(!empty($historical_inbound_qty)) {
			foreach($historical_inbound_qty as $timestamp => $current_data) {
				if(!isset($historical_inventory_levels[$timestamp])) {
					$historical_inventory_levels[$timestamp] = array(
						$timestamp,
						0
					);
				}
				
				$historical_inventory_levels[$timestamp][1] += $current_data[1];
			}
		}
		if(!empty($historical_order_volume)) {
			foreach($historical_order_volume as $timestamp => $current_data) {
				if(!isset($historical_inventory_levels[$timestamp])) {
					$historical_inventory_levels[$timestamp] = array(
						$timestamp,
						0
					);
				}
				
				$historical_inventory_levels[$timestamp][1] -= $current_data[1];
			}
		}
		
		// Find historical backordered qty
		$redstag_db
			->select('DATE_FORMAT(stock_movement.created_at,"%Y-%m-15") AS the_date, COALESCE(SUM(qty_backordered),0) AS total_qty_backordered')
			->from('stock_movement')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = stock_movement.product_id')
			->where('qty_backordered >', 0)
			->group_by('DATE_FORMAT(stock_movement.created_at,"%Y-%m-15")')
			->order_by('the_date');
		
		if(!empty($data['sku'])) {
			$redstag_db->where('catalog_product_entity.sku', $data['sku']);
		}
		else {
			$redstag_db->where('stock_movement.created_at >=', date('Y-01-01', strtotime('-3 year')));
		}
		
		$historical_backorder_volume_tmp = $redstag_db->get()->result_array();
		$historical_backorder_volume = array();
		
		if(!empty($historical_backorder_volume_tmp)) {
			foreach($historical_backorder_volume_tmp as $current_data) {
				$timestamp = strtotime($current_data['the_date'])*1000;
				$historical_backorder_volume[$timestamp] = array(
					$timestamp,
					$current_data['total_qty_backordered']
				);
			}
		}
		
		// Projected Stock Out Graph
		
		// Get order last 90 days
		if(!empty($data['sku'])) {
			$average_daily_order_qty_tmp = $redstag_db
				->select('SUM(qty_ordered) / 90 AS average_daily_order_qty', false)
				->from('sales_flat_order_item')
				->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_item.order_id')
				->where('sales_flat_order.created_at >=', date('Y-m-d', strtotime('-90 day')))
				->where('sales_flat_order_item.sku', $data['sku'])
				->get()->result_array();
		}
		
		$average_daily_order_qty = !empty($average_daily_order_qty_tmp) ? $average_daily_order_qty_tmp[0]['average_daily_order_qty'] : 0;
		
		// Get expected delivery
		if(!empty($data['sku'])) {
			$expected_delivery_data_tmp = $redstag_db
				->select('delivery.expected_delivery, delivery_item.qty_expected')
				->from('delivery_item')
				->join('delivery', 'delivery_item.delivery_id = delivery.delivery_id')
				->join('catalog_product_entity', 'delivery_item.product_id = catalog_product_entity.entity_id')
				->where('expected_delivery >=', date('Y-m-d'))
				->where('catalog_product_entity.sku', $data['sku'])
				->get()->result_array();
		}
		$expected_delivery_data = array();
		if(!empty($expected_delivery_data_tmp)) {
			foreach($expected_delivery_data_tmp as $current_data) {
				$expected_delivery_data[$current_data['expected_delivery']] = $current_data['qty_expected'];
			}
		}
		
		$projected_stock_out_graph_data = array();
		$timestamp = strtotime(date('Y-m-d'))*1000;
		$this_day_on_hand_qty = $current_on_hand_qty;
		
		$is_backordered = false;
		if($this_day_on_hand_qty == 0) {
			$is_backordered = true;
			$this_day_on_hand_qty = $current_backordered_qty * -1;
		}
		
		$total_expected_delivery_qty = 0;
		$expected_delivery_count = 0;
		if(!empty($expected_delivery_data[date('Y-m-d')])) {
			$total_expected_delivery_qty += $expected_delivery_data[date('Y-m-d')];
			$this_day_on_hand_qty += $total_expected_delivery_qty;
			$expected_delivery_count++;
		}
		$projected_stock_out_graph_data[$timestamp] = array(
			$timestamp,
			$this_day_on_hand_qty
		);
		
		$elapsed_days = 0;
		while($this_day_on_hand_qty <> 0) {
			$elapsed_days++;
			$this_date = date('Y-m-d', strtotime('+'.$elapsed_days.' day'));
			if(!empty($expected_delivery_data[$this_date])) {
				$total_expected_delivery_qty += $expected_delivery_data[$this_date];
				$expected_delivery_count++;
			}
			
			$timestamp = strtotime($this_date)*1000;
			$this_day_on_hand_qty = floor($current_on_hand_qty + $total_expected_delivery_qty - ($elapsed_days * $average_daily_order_qty));
			
			if($this_day_on_hand_qty < 0 && $expected_delivery_count == count($expected_delivery_data)) {
				$this_day_on_hand_qty = 0;
			}
			
			$projected_stock_out_graph_data[$timestamp] = array(
				$timestamp,
				$this_day_on_hand_qty
			);
			
			if($average_daily_order_qty == 0 && $elapsed_days >= 180) {
				break;
			}
			if($elapsed_days >= 180) {
				break;
			}
		}

		$result['historical_on_hand_qty'] = array_values($historical_on_hand_qty);
		$result['historical_average_on_hand_qty'] = array_values($historical_average_on_hand_qty);
		$result['historical_inbound_qty'] = array_values($historical_inbound_qty);
		$result['historical_inventory_levels'] = array_values($historical_inventory_levels);
		$result['historical_backorder_volume'] = array_values($historical_backorder_volume);
		$result['projected_stock_out_graph_data'] = array_values($projected_stock_out_graph_data);
		
		$result['projected_stock_out_graph_data_max'] = max(array_column($result['projected_stock_out_graph_data'],1));
		$result['projected_stock_out_graph_data_min'] = min(array_column($result['projected_stock_out_graph_data'],1));
		
		$result['historical_inventory_levels_min_date'] = !empty($historical_inventory_levels) ? min(array_keys($historical_inventory_levels)) : $current_date_timestamp;

		return $result;
	}
	
	public function get_current_on_hand_qty($sku) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$redstag_db
			->select('COALESCE(SUM(cataloginventory_product.qty_on_hand),0) AS total_qty_on_hand')
			->from('cataloginventory_product')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id');
		
		if(!empty($sku)) {
			$redstag_db->where('catalog_product_entity.sku', $sku);
		}
		
		$current_on_hand_qty_tmp = $redstag_db->get()->result_array();
		
		return !empty($current_on_hand_qty_tmp) ? $current_on_hand_qty_tmp[0]['total_qty_on_hand'] : 0;
	}
	
	public function get_current_backordered_qty($sku) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$redstag_db
			->select('COALESCE(SUM(cataloginventory_product.qty_backordered),0) AS total_qty_backordered')
			->from('cataloginventory_product')
			->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id');
		
		if(!empty($sku)) {
			$redstag_db->where('catalog_product_entity.sku', $sku);
		}
		
		$current_backordered_qty_tmp = $redstag_db->get()->result_array();
		
		return !empty($current_backordered_qty_tmp) ? $current_backordered_qty_tmp[0]['total_qty_backordered'] : 0;
	}
}