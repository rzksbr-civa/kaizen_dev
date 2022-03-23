<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_shipment_board extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_shipment_board_data($data) {
		$prod_db = $this->load->database('prod', TRUE);
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$date_in_utc = gmdate('Y-m-d H:i:s', strtotime($data['date']));
		$current_local_time = date('Y-m-d H:i:s');
		$store_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		
		$data['completed_shipments_count'] = 0;
		$data['hourly_completed_shipments_count'] = array();
		$data['hourly_completed_orders_count'] = array();
		$data['past_hourly_completed_shipments_count'] = array();
		
		for($i=0; $i<24; $i++) {
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value'] = 0;
			$data['hourly_completed_orders_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value'] = 0;
			$data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value'] = 0;
			
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value_per_minute'] = 0;
			$data['hourly_completed_orders_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value_per_minute'] = 0;
			$data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value_per_minute'] = 0;
			
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['employee'] = array();
		}
		
		$packages_data = $redstag_db
			->select("
				IF(sales_flat_shipment_package.stock_id=3,HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain')),HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))) AS the_hour,
				COUNT(*) AS qty")
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('sales_flat_order.store_id', $store_id)
			->where('sales_flat_shipment_package.created_at >=', $date_in_utc)
			->where('sales_flat_shipment_package.created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->group_by('the_hour')
			->get()->result_array();
			
		foreach($packages_data as $current_data) {
			$data['completed_shipments_count'] += $current_data['qty'];
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])]['value'] += $current_data['qty'];
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
			->select('COUNT(DISTINCT(sales_flat_shipment.order_id)) AS completed_orders_count')
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('sales_flat_order.store_id', $store_id)
			->where('sales_flat_shipment_package.created_at >=', $date_in_utc)
			->where('sales_flat_shipment_package.created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->where('sales_flat_shipment.defunct', 0)
			->get()->result_array();
		
		$data['completed_orders_count'] = $completed_orders_data[0]['completed_orders_count'];
				
		$hourly_completed_orders_data = $redstag_db
			->select("
				IF(sales_flat_shipment_package.stock_id=3,HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain')),HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))) AS the_hour,
				COUNT(DISTINCT(order_id)) AS completed_orders_count")
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('sales_flat_order.store_id', $store_id)
			->where('sales_flat_shipment_package.created_at >=', $date_in_utc)
			->where('sales_flat_shipment_package.created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->where('sales_flat_shipment.defunct', 0)
			->group_by('the_hour')
			->get()->result_array();
		
		foreach($hourly_completed_orders_data as $current_data) {
			$the_hour = $data['date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour']);
			
			$elapsed_mins = 60;
			if($the_hour == date('Y-m-d H:00:00')) {
				$elapsed_mins = (strtotime(date('H:i:s')) - strtotime(date('H:00:00'))) / 60;
				if($elapsed_mins == 0) $elapsed_mins = 1;
			}
			
			$data['hourly_completed_orders_count'][$the_hour]['value'] += $current_data['completed_orders_count'];
			$data['hourly_completed_orders_count'][$the_hour]['value_per_minute'] += round($current_data['completed_orders_count'] / $elapsed_mins, 2);
		}

		$last_four_week_dates = array();
		
		$last_four_week_dates[] = $data['date'];
		for($i=1; $i<=4; $i++) {
			$last_four_week_dates[] = date('Y-m-d', strtotime('-'.($i*7). ' days ' . $data['date']));
		}

		// Past 4 weeks hourly completed shipments
		$current_hh_mm_ss = gmdate('H:i:s');
		$data['past_average_completed_shipments_count_to_time'] = 0;
		$past_completed_shipments_count_to_time = array();
		for($i=1; $i<=4; $i++) {
			$past_completed_shipments_count_to_time[$i] = 0;
			
			$past_date_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $date_in_utc));
			
			$redstag_db
				->select("
					IF(sales_flat_shipment_package.stock_id=3,HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain')),HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))) AS the_hour,
					COUNT(*) AS qty")
				->from('sales_flat_shipment_package')
				->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
				->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
				->where('sales_flat_order.store_id', $store_id)
				->where('sales_flat_shipment_package.created_at >=', $past_date_in_utc)
				->where('sales_flat_shipment_package.created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $past_date_in_utc)))
				->group_by('the_hour');
			
			$packages_data = $redstag_db->get()->result_array();
		
			foreach($packages_data as $current_data) {
				$data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])]['value'] += $current_data['qty'];
			}
			
			$redstag_db
				->select("
					IF(sales_flat_shipment_package.stock_id=3,HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain')),HOUR(CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))) AS the_hour,
					COUNT(*) AS qty")
				->from('sales_flat_shipment_package')
				->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
				->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
				->where('sales_flat_order.store_id', $store_id)
				->where('sales_flat_shipment_package.created_at >=', $past_date_in_utc)
				->where('sales_flat_shipment_package.created_at <', date('Y-m-d H:i:s', strtotime($past_date_in_utc) + strtotime(date('H:i:s'))))
				->group_by('the_hour');
			
			$packages_data = $redstag_db->get()->result_array();
			
			$past_completed_shipments_count_to_time[$i] = $packages_data[0]['qty'];
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
			->where('sales_flat_order.store_id', $store_id)
			->where('sales_flat_shipment.target_ship_date', $data['date'])
			->where_in('sales_flat_shipment.status', array('new','picking','picked','packing'))
			->where('sales_flat_order.can_fulfill', 1)
			->where('sales_flat_shipment.defunct', 0)
			->group_by('sales_flat_shipment.status')
			->order_by('sales_flat_shipment.status');
		
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
		
		// ORDERS BY HOUR
		$data['hourly_orders_count'] = array();
		$data['past_hourly_orders_count'] = array();
		
		$data['projected_demand'] = 0;
		
		for($i=0; $i<24; $i++) {
			$data['hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $i)] = 0;
			$data['past_hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $i)] = 0;
		}
		
		$redstag_db
			->select("
				IF(sales_flat_order_stock.stock_id=3,HOUR(CONVERT_TZ(created_at,'UTC','US/Mountain')),HOUR(CONVERT_TZ(created_at,'UTC','US/Eastern'))) AS the_hour,
				COUNT(*) AS qty")
			->from('sales_flat_order_stock')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_stock.order_id')
			->where('sales_flat_order.store_id', $store_id)
			->where('created_at >=', $date_in_utc)
			->where('created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $date_in_utc)))
			->group_by('the_hour');

		$hourly_orders_data = $redstag_db->get()->result_array();
		
		foreach($hourly_orders_data as $current_data) {
			$data['hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $current_data['the_hour'])] = $current_data['qty'];
		}
		
		// Past 4 weeks hourly orders
		for($i=1; $i<=4; $i++) {
			$past_date_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $date_in_utc));
			
			$redstag_db
				->select("
					IF(sales_flat_order_stock.stock_id=3,HOUR(CONVERT_TZ(created_at,'UTC','US/Mountain')),HOUR(CONVERT_TZ(created_at,'UTC','US/Eastern'))) AS the_hour,
					COUNT(*) AS qty")
				->from('sales_flat_order_stock')
				->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_stock.order_id')
				->where('sales_flat_order.store_id', $store_id)
				->where('created_at >=', $past_date_in_utc)
				->where('created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $past_date_in_utc)))
				->group_by('the_hour');
			
			$past_hourly_orders_data = $redstag_db->get()->result_array();
			
			foreach($past_hourly_orders_data as $current_data) {
				$data['past_hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $current_data['the_hour'])] += $current_data['qty'];
				$data['projected_demand'] += $current_data['qty'];
			}
		}
		
		foreach($data['past_hourly_orders_count'] as $key => $value) {
			$data['past_hourly_orders_count'][$key] = round($data['past_hourly_orders_count'][$key] / 4);
		}
		
		$data['hourly_completed_shipments_chart_max_scale'] = max(
			array(
				ceil((max(array_column($data['hourly_completed_shipments_count'],'value')) * 1.1) / 10) * 10,
				ceil((max(array_column($data['past_hourly_completed_shipments_count'],'value')) * 1.1) / 10) * 10
			)
		);

		$data['hourly_orders_chart_max_scale'] = max(
			ceil((max($data['hourly_orders_count']) * 1.1) / 10) * 10,
			ceil((max($data['past_hourly_orders_count']) * 1.1) / 10) * 10,
			$data['hourly_completed_shipments_chart_max_scale']
		);
		
		// Filter custom start time
		$start_hour = intval(date('H', strtotime('08:00')));
		for($i=0; $i < $start_hour; $i++) {
			unset($data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['hourly_completed_orders_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			unset($data['hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $i)]);
			unset($data['past_hourly_orders_count'][$data['date'] . ' ' . sprintf('%02d:00', $i)]);
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		$data['shipment_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_shipment_board_visualization', $data, true);
		$data['js_shipment_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_shipment_board_visualization', $data, true);
		
		return $data;
	}
}