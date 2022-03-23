<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_batching_helper extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_batching_helper_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : null; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$timezone_name = ($timezone == -7) ? 'US/Mountain' : 'US/Eastern';

		$redstag_db
			->select("
				sales_flat_order.increment_id AS order_no,
				sales_flat_order.ext_order_id AS order_ref_no,
				sales_flat_shipment.increment_id AS shipment_no,
				core_store.name AS brand_name,
				sales_flat_shipment.status AS shipment_status,
				sales_flat_order.status AS order_status,
				sales_flat_shipment.target_ship_date,
				IF(sales_flat_order.can_fulfill=0,'Yes','No') AS ready_to_ship,
				sales_flat_order.batch_tag,
				sales_flat_order.carrier,
				sales_flat_order.shipping_method,
				sales_flat_shipment.solution_id AS packing_solution_id,
				sales_flat_order.created_at AS order_date,
				sales_flat_shipment.total_qty,
				sales_flat_shipment.total_weight,
				sales_flat_shipment.updated_at AS shipment_last_updated_on,
				sales_flat_shipment.created_at AS batch_created_on,
				sales_flat_order.entity_id AS order_id,
				sales_flat_shipment.entity_id AS shipment_id", false)
			->from('sales_flat_shipment')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('core_store', 'core_store.store_id = sales_flat_order.store_id')
			->where('sales_flat_shipment.target_ship_date >=', $data['period_from'])
			->where('sales_flat_shipment.target_ship_date >=', date('Y-m-d', strtotime('+1 day '.$data['period_from'])))
			->limit(10000);

		if(!empty($stock_id)) {
			$redstag_db->where('sales_flat_shipment.stock_id', $stock_id);
		}
		
		if(!empty($data['customer'])) {
			$redstag_db->where_in('sales_flat_order.store_id', $data['customer']);
		}
		
		if(!empty($data['carrier'])) {
			$redstag_db->where_in('sales_flat_order.carrier', $data['carrier']);
		}
			
		$batching_helper_board_data = $redstag_db->get()->result_array();

		$data['batching_helper_board_data'] = $batching_helper_board_data;
		
		$data['batching_helper_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_batching_helper_board_visualization', $data, true);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		return $data;
	}
}