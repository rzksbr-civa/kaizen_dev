<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_carrier_diversification_board extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_carrier_diversification_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
			
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		$date_field_name = "IF(sales_flat_shipment_package.stock_id IN(3,6), CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'), CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern'))";
		
		$data['excluded_customer'] = array(
			261, // Bidetking
			317, // WS Distribution
			309, // My Pet Chicken
			277, 300, // ToiletTree
			264, // Rocker
			153, // FireResQ
			2, 3 // LGDC
		);
		
		$redstag_db
			->select('carrier_code')
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->where($date_field_name ." >= '".$period_from."'", null, false)
			->where($date_field_name ." < '".date('Y-m-d', strtotime('+1 day '.$period_to))."'", null, false)
			->where('carrier_code IS NOT NULL', null, false)
			->group_by('carrier_code')
			->order_by('carrier_code');
		
		if(!empty($data['customer'])) {
			$redstag_db->where_in('sales_flat_shipment.store_id', $data['customer']);
		}
		if(!empty($data['account']) && $data['account'] == 'rsf') {
			$redstag_db->where_not_in('sales_flat_shipment.store_id', $data['excluded_customer']);
		}
		if(!empty($data['stock_ids'])) {
			$redstag_db->where_in('sales_flat_shipment_package.stock_id', $data['stock_ids']);
		}
		
		$carriers_tmp = $redstag_db->get()->result_array();
		
		if(empty($data['periodicity'])) {
			$redstag_db
				->select('carrier_code, COUNT(*) AS total_packages')
				->group_by('carrier_code');
		}
		else {
			switch($data['periodicity']) {
				case 'daily':
					$redstag_db
						->select('DATE('.$date_field_name.') AS period, carrier_code, COUNT(*) AS total_packages')
						->group_by('period, carrier_code');
					break;
				case 'weekly':
					$redstag_db
						->select('CONCAT("WEEK ", DATE_ADD(DATE('.$date_field_name.'), INTERVAL - WEEKDAY('.$date_field_name.') DAY)) AS period, carrier_code, COUNT(*) AS total_packages')
						->group_by('period, carrier_code');
					break;
				case 'monthly':
					$redstag_db
						->select('DATE_FORMAT('.$date_field_name.', "%Y-%m (%M %Y)") AS period, carrier_code, COUNT(*) AS total_packages')
						->group_by('period, carrier_code');
					break;
				case 'yearly':
					$redstag_db
						->select('YEAR('.$date_field_name.') AS period, carrier_code, COUNT(*) AS total_packages')
						->group_by('period, carrier_code');
					break;
			}
		}
		
		$redstag_db
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->where('carrier_code IS NOT NULL', null, false)
			->where($date_field_name ." >= '".$period_from."'", null, false)
			->where($date_field_name ." < '".date('Y-m-d', strtotime('+1 day '.$period_to))."'", null, false);
		
		if(!empty($data['periodicity'])) {
			$redstag_db->group_by('period, carrier_code');
		}
		else {
			$redstag_db->group_by('carrier_code');
		}
		
		if(!empty($data['customer'])) {
			$redstag_db->where_in('sales_flat_shipment.store_id', $data['customer']);
		}
		if(!empty($data['account']) && $data['account'] == 'rsf') {
			$redstag_db->where_not_in('sales_flat_shipment.store_id', $data['excluded_customer']);
		}
		if(!empty($data['stock_ids'])) {
			$redstag_db->where_in('sales_flat_shipment_package.stock_id', $data['stock_ids']);
		}
		
		$table_data_tmp = $redstag_db->get()->result_array();
		
		$data['carriers'] =  array_column($carriers_tmp, 'carrier_code');
		
		$table_template = array();
		foreach($data['carriers'] as $current_carrier) {
			$table_template[$current_carrier] = 0;
		}
		$table_template['total'] = 0;
		
		$data['table_data'] = array();
		if(!empty($data['periodicity'])) {
			foreach($table_data_tmp as $current_data) {
				if(!isset($data['table_data'][$current_data['period']])) {
					$data['table_data'][$current_data['period']] = $table_template;
				}
				
				$data['table_data'][$current_data['period']][$current_data['carrier_code']] = $current_data['total_packages'];
				$data['table_data'][$current_data['period']]['total'] += $current_data['total_packages'];
			}
		}
		else {
			$data['table_data'] = $table_template;
			foreach($table_data_tmp as $current_data) {
				$data['table_data'][$current_data['carrier_code']] = $current_data['total_packages'];
				$data['table_data']['total'] += $current_data['total_packages'];
			}
		}

		
		$data['carrier_diversification_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_carrier_diversification_board_visualization', $data, true);
		
		return $data;
	}
}