<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_trailer_utilization_forecast extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_trailer_utilization_forecast_board_data($data) {
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
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$timezone_name = 'US/Eastern';
		if($stock_id == 3 || $stock_id == 6) $timezone_name = 'US/Mountain';
		
		$date = !empty($data['date']) ? $data['date'] : date('Y-m-d');
		
		$trailer_cubic_ft_capacity = 8 * 8 * 53;
		$trailer_weight_capacity = 40000;
		
		$redstag_db
			->select("
				carrier_code,
				COUNT(*) AS package_qty,
				SUM(length*width*height) / 1728 AS total_cubic_ft,
				SUM(weight) AS total_weight,
				SUM(length*width*height) / 1728 / ".$trailer_cubic_ft_capacity." AS forecasted_trailer_based_on_dimension,
				SUM(weight) / ".$trailer_weight_capacity." AS forecasted_trailer_based_on_weight", false)
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->where('sales_flat_shipment.target_ship_date', $date)
			->group_by('carrier_code');
			
		if(!empty($data['facility'])) {
			$redstag_db->where('sales_flat_shipment_package.stock_id', $stock_id);
		}
		
		if(!empty($data['carrier'])) {
			$redstag_db->where_in('sales_flat_shipment_package.carrier_code', $data['carrier']);
		}
		
		$trailer_forecast_data = $redstag_db->get()->result_array();
				
		$data['trailer_forecast_data'] = $trailer_forecast_data;
		
		$data['total_cubic_ft'] = 0;
		$data['total_weight'] = 0;
		$data['total_package'] = 0;
		$data['forecasted_trailer_based_on_dimension'] = 0;
		$data['forecasted_trailer_based_on_weight'] = 0;
		
		if(!empty($trailer_forecast_data)) {
			foreach($trailer_forecast_data as $current_data) {
				$data['total_cubic_ft'] += $current_data['total_cubic_ft'];
				$data['total_weight'] += $current_data['total_weight'];
				$data['total_package'] += $current_data['package_qty'];
				$data['forecasted_trailer_based_on_dimension'] += $current_data['forecasted_trailer_based_on_dimension'];
				$data['forecasted_trailer_based_on_weight'] += $current_data['forecasted_trailer_based_on_weight'];
			}
		}
		
		$data['forecasted_trailer_based_on_dimension'] = ceil($data['forecasted_trailer_based_on_dimension']);
		$data['forecasted_trailer_based_on_weight'] = ceil($data['forecasted_trailer_based_on_weight']);

		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['trailer_utilization_forecast_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_trailer_utilization_forecast_board_visualization', $data, true);
		
		return $data;
	}
}