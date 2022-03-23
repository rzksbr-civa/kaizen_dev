<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_countdown extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_countdown_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$break_times = $this->model_db_crud->get_several_data('break_time');
		
		$data['date'] = date('Y-m-d', strtotime($timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$date_in_utc = gmdate('Y-m-d H:i:s', strtotime($data['date']));
		$current_local_time = date('Y-m-d H:i:s', strtotime($timezone.' hours '.gmdate('Y-m-d H:i:s')));
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		$start_time_in_utc = date('Y-m-d H:i:s', strtotime('+'.($timezone*-1).' hours '.$data['date'].' '.$data['start_time']));
		// $end_time_in_utc = date('Y-m-d H:i:s', strtotime('+'.($timezone*-1).' hours '.$data['date'].' '.$data['end_time']));
		
		$data['completed_shipments_count'] = 0;
		$data['hourly_completed_shipments_count'] = array();
		$data['past_hourly_completed_shipments_count'] = array();
		
		for($i=0; $i<24; $i++) {
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value'] = 0;
			$data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value'] = 0;
			
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value_per_minute'] = 0;
			$data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]['value_per_minute'] = 0;
		}
		
		$packages_data = $redstag_db
			->select('HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
			->from('sales_flat_shipment_package')
			->where('created_at >=', $start_time_in_utc)
			// ->where('created_at <', $end_time_in_utc)
			->where('stock_id', $stock_id)
			->group_by('the_hour')
			->order_by('the_hour')
			->get()->result_array();
			
		foreach($packages_data as $current_data) {
			$data['completed_shipments_count'] += $current_data['qty'];
			$data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])]['value'] += $current_data['qty'];
		}
		
		foreach($data['hourly_completed_shipments_count'] as $the_hour => $current_data) {
			$elapsed_mins = 60;
			if($the_hour == date('Y-m-d H:00:00', strtotime($current_local_time))) {
				$elapsed_mins = (strtotime($current_local_time) - strtotime(date('Y-m-d H:00:00', strtotime($current_local_time)))) / 60;
				if($elapsed_mins == 0) $elapsed_mins = 1;
			}
			$data['hourly_completed_shipments_count'][$the_hour]['value_per_minute'] = round($current_data['value'] / $elapsed_mins, 2);
			
			if($the_hour == date('Y-m-d H:00:00', strtotime($current_local_time))) {
				$data['current_num_shipment_per_minute'] = $data['hourly_completed_shipments_count'][$the_hour]['value_per_minute'];
			}
		}
		
		// Past 4 weeks hourly completed shipments
		$current_hh_mm_ss = gmdate('H:i:s');
		$past_completed_shipments_count_to_time = array();
		
		for($i=1; $i<=4; $i++) {
			$past_completed_shipments_count_to_time[$i] = 0;
			
			$past_date_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $date_in_utc));
			
			$redstag_db
				->select('HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
				->from('sales_flat_shipment_package')
				->where('created_at >=', $past_date_in_utc)
				->where('created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $past_date_in_utc)))
				->group_by('the_hour');
			
			if(!empty($stock_id)) {
				$redstag_db->where('stock_id', $stock_id);
			}
			
			$packages_data = $redstag_db->get()->result_array();
		
			foreach($packages_data as $current_data) {
				$data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $current_data['the_hour'])]['value'] += $current_data['qty'];
			}
			
			$redstag_db
				->select('HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
				->from('sales_flat_shipment_package')
				->where('created_at >=', date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $start_time_in_utc)))
				// ->where('created_at <', date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $end_time_in_utc)))
				->group_by('the_hour');
			
			if(!empty($stock_id)) {
				$redstag_db->where('stock_id', $stock_id);
			}
			
			$packages_data = $redstag_db->get()->result_array();
			
			$past_completed_shipments_count_to_time[$i] = $packages_data[0]['qty'];
		}

		foreach($data['past_hourly_completed_shipments_count'] as $key => $value) {
			$data['past_hourly_completed_shipments_count'][$key]['value'] = round($data['past_hourly_completed_shipments_count'][$key]['value'] / 4);
			$data['past_hourly_completed_shipments_count'][$key]['value_per_minute'] = round($data['past_hourly_completed_shipments_count'][$key]['value'] / 60, 2);
		}
		
		// GET SHIPMENT COUNT FIGURE
		$redstag_db
			->select('sales_flat_shipment.status, COUNT(sales_flat_shipment.entity_id) AS shipments_count, COUNT(DISTINCT(order_id)) AS orders_count')
			->from('sales_flat_shipment')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where('sales_flat_shipment.target_ship_date', $data['date'])
			->where_in('sales_flat_shipment.status', array('new','picking','picked','packing','packed'))
			->where('sales_flat_order.can_fulfill', 1)
			->where('sales_flat_shipment.defunct', 0)
			->group_by('sales_flat_shipment.status')
			->order_by('sales_flat_shipment.status');
		
		if(!empty($stock_id)) {
			$redstag_db->where('sales_flat_shipment.stock_id', $stock_id);
		}
		
		$total_shipments_data = $redstag_db->get()->result_array();
		
		$data['new_shipments_count'] = 0;
		
		$data['in_processing_shipments_count'] = 0;
		
		foreach($total_shipments_data as $current_data) {
			if($current_data['status'] == 'new') {
				$data['new_shipments_count'] = $current_data['shipments_count'];
			}
			else {
				$data['in_processing_shipments_count'] += $current_data['shipments_count'];
			}
		}

		$data['total_shipments_count'] = $data['new_shipments_count'] + $data['in_processing_shipments_count'];

		// Filter start time & end time
		$start_hour = intval(date('H', strtotime($data['start_time'])));
		// $end_hour = intval(date('H', strtotime($data['end_time'])));
		for($i=0; $i < 24; $i++) {
			if(($i < $start_hour) /*|| ($i > $end_hour)*/) {
				unset($data['hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
				unset($data['past_hourly_completed_shipments_count'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)]);
			}
		}
		
		$data['projected_demand'] = 0;
		
		$this->load->model(PROJECT_CODE.'/model_assignment');
		$takt_data = $this->model_assignment->get_takt_data(
			array(
				'date' => $data['date'],
				'facility' => $data['facility']
			)
		);
		
		// Past 4 weeks hourly orders
		for($i=1; $i<=4; $i++) {
			$past_date_in_utc = date('Y-m-d H:i:s', strtotime('-'.($i*7). ' days ' . $date_in_utc));
			
			$redstag_db
				->select('HOUR(DATE_ADD(created_at, INTERVAL '.$timezone.' HOUR)) AS the_hour, COUNT(*) AS qty')
				->from('sales_flat_order_stock')
				->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_order_stock.order_id')
				->where('created_at >=', $past_date_in_utc)
				->where('created_at <', date('Y-m-d H:i:s', strtotime('+1 day ' . $past_date_in_utc)))
				->group_by('the_hour');

			if(!empty($stock_id)) {
				$redstag_db->where('sales_flat_order_stock.stock_id', $stock_id);
			}
			
			$past_hourly_orders_data = $redstag_db->get()->result_array();
			
			foreach($past_hourly_orders_data as $current_data) {
				$data['projected_demand'] += $current_data['qty'];
			}
		}
		
		// Take the average of the past 4 weeks projected demand
		$data['projected_demand'] = round($data['projected_demand'] / 4);
		
		$default_projected_demand = $data['projected_demand'];
		$default_hours_shift = $facility_data['hours_shift'];
		$default_break_time_per_shift_in_min = $facility_data['break_time_per_shift_in_min'];
		$default_lunch_time_per_shift_in_min = $facility_data['lunch_time_per_shift_in_min'];
		
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
		$data['net_available_time_per_day_in_min'] = (isset($data['available_time_per_shift_in_min']) && isset($data['break_time_per_shift_in_min']) && isset($data['lunch_time_per_shift_in_min'])) ? $data['available_time_per_shift_in_min'] - $data['break_time_per_shift_in_min'] - $data['lunch_time_per_shift_in_min'] : null;
		
		$data['takt_time_in_min'] = !empty($data['net_available_time_per_day_in_min']) ? $data['projected_demand'] / $data['net_available_time_per_day_in_min'] : null;
		
		$data['takt_value'] = isset($data['takt_time_in_min']) ? $data['takt_time_in_min'] * 60 : null;
		$data['takt_value_per_minute'] = isset($data['takt_value']) ? $data['takt_value'] / 60 : null;
		
		$data['hourly_takt_value_per_minute'] = array();
		for($i=0; $i < 24; $i++) {
			if($i >= $start_hour) {
				$working_minutes_this_hour = 60;
				$this_hour_start_time = sprintf('%02d:00:00', $i);
				$this_hour_end_time = date('H:00:00', strtotime('+1 hour '.$this_hour_start_time));
				foreach($break_times as $the_break) {
					if(strtotime($this_hour_end_time) > strtotime($the_break['start_time']) && strtotime($this_hour_start_time) < strtotime($the_break['end_time'])) {
						$working_minutes_this_hour -= ((
							min(strtotime($this_hour_end_time), strtotime($the_break['end_time'])) -
							max(strtotime($this_hour_start_time), strtotime($the_break['start_time']))
						) / 60);
					}
				}
				
				$data['hourly_takt_value_per_minute'][$data['date'] . ' ' . sprintf('%02d:00:00', $i)] = isset($data['takt_value']) ? round($data['takt_value'] / $working_minutes_this_hour,2) : null;
			}
		}
		
		$data['hourly_completed_shipments_per_minute_chart_max_scale'] = max(
			array(
				ceil((max(array_column($data['hourly_completed_shipments_count'],'value_per_minute')) * 1.1) / 10) * 10,
				ceil((max(array_column($data['past_hourly_completed_shipments_count'],'value_per_minute')) * 1.1) / 10) * 10
			)
		);
		
		$data['adjusted_projected_demand'] = round(((min(strtotime($data['end_time']),strtotime($data['cut_off_time'])) - strtotime($data['start_time']))/3600) * ($data['projected_demand'] / $data['hours_shift']));
		
		$data['remaining_shipments_count'] = max(
			$data['adjusted_projected_demand'] - $data['completed_shipments_count'],
			$data['total_shipments_count']
		);
		
		//$data['current_num_shipment_per_minute'] = number_format($data['completed_shipments_count'] / ((strtotime($current_local_time) - strtotime($data['start_time'])) / 60),2);
		$data['required_num_shipment_per_minute'] = $data['remaining_shipments_count'] / ((strtotime($data['end_time']) - strtotime($current_local_time)) / 60);
		
		$data['required_num_shipment_per_minute_text'] = (($data['required_num_shipment_per_minute'] > 0) && ($data['required_num_shipment_per_minute'] < 10*$data['current_num_shipment_per_minute'])) ? number_format($data['required_num_shipment_per_minute'],2) : 'LATE';
		
		$data['estimated_finish_time'] = null;
		$data['estimated_finish_time_js'] = null;
		$data['estimated_finish_time_text'] = 'N/A';
		$data['estimated_remaining_secs'] = null;
		$data['estimated_remaining_secs_text'] = 'N/A';
		
		$data['color_code'] = 'green';
		
		if($data['completed_shipments_count'] > 0) {
			// $data['estimated_finish_time'] = date('Y-m-d H:i:s', strtotime($current_local_time) + ((strtotime($current_local_time) - strtotime($data['start_time'])) / $data['completed_shipments_count'] * $data['remaining_shipments_count']));

			$data['estimated_finish_time'] = $data['current_num_shipment_per_minute'] > 0 ? date('Y-m-d H:i:s', strtotime($current_local_time) + $data['remaining_shipments_count'] / $data['current_num_shipment_per_minute'] * 60) : null;
			
			$data['estimated_remaining_secs'] = isset($data['estimated_finish_time']) ? strtotime($data['estimated_finish_time']) - strtotime($current_local_time) : null;
			
			if($data['estimated_remaining_secs'] > 0) {
				foreach($break_times as $the_break) {
					if((strtotime($current_local_time) < strtotime($the_break['end_time'])) && (strtotime($data['estimated_finish_time']) > strtotime($the_break['start_time']))) {
						$used_break_secs = min(strtotime($data['estimated_finish_time']), strtotime($the_break['end_time'])) - max(strtotime($current_local_time), strtotime($the_break['start_time']));
						
						$data['estimated_remaining_secs'] += $used_break_secs;
						$data['estimated_finish_time'] = date('Y-m-d H:i:s', strtotime($current_local_time) + $data['estimated_remaining_secs']);
					}
				}
			}
			
			$data['estimated_finish_time_js'] = isset($data['estimated_finish_time']) ? strtotime(date('H:i:s', strtotime($data['estimated_finish_time']))) * 1000 : null;
			$data['estimated_finish_time_text'] = isset($data['estimated_finish_time']) ? date('g:i A', strtotime($data['estimated_finish_time'])) : null;
			
			/*$data['estimated_remaining_secs_text'] = sprintf('%02d:%02d:%02d', 
				floor($data['estimated_remaining_secs']/3600),
				floor(($data['estimated_remaining_secs']%3600)/60),
				$data['estimated_remaining_secs']%60
			);*/
			
			$data['estimated_remaining_secs_text'] = '';
			
			if(isset($data['estimated_remaining_secs'])) {
				if($data['estimated_remaining_secs'] / 3600 >= 1) {
					$data['estimated_remaining_secs_text'] .= floor($data['estimated_remaining_secs'] / 3600) . ' hr ';
				}
				$data['estimated_remaining_secs_text'] .= floor(($data['estimated_remaining_secs'] % 3600) / 60) . ' min ';
				if($data['estimated_remaining_secs'] < 60) {
					$data['estimated_remaining_secs_text'] = $data['estimated_remaining_secs'] . ' sec ';
				}
			}
			else {
				$data['estimated_remaining_secs_text'] = 'N/A';
			}
			
			if(!isset($data['estimated_finish_time'])) {
				$data['color_code'] = 'red';
			}
			else if(abs(strtotime($data['estimated_finish_time']) - strtotime($data['end_time'])) <= 15*60) {
				$data['color_code'] = 'orange';
			}
			else if(strtotime($data['estimated_finish_time']) > strtotime($data['end_time'])) {
				$data['color_code'] = 'red';
			}
			else {
				$data['color_code'] = 'green';
			}
			
			if($data['estimated_remaining_secs'] < 0) {
				$data['estimated_remaining_secs_text'] = 'N/A';
				$data['color_code'] = 'red';
			}
		}
		
		$data['current_num_shipment_per_minute'] = number_format($data['current_num_shipment_per_minute'],2);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['countdown_board_shipments_graph_html'] = $this->load->view(PROJECT_CODE.'/view_countdown_board_shipments_graph', $data, true);
		
		$data['js_countdown_board_shipments_graph_html'] = $this->load->view(PROJECT_CODE.'/js_view_countdown_board_shipments_graph', $data, true);

		return $data;
	}
}