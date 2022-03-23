<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_carton_utilization extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_carton_utilization_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!isset($data['package_created_from'])) {
			$data['package_created_from'] = date('Y-m-d');
		}
		
		if(!isset($data['package_created_to'])) {
			$data['package_created_to'] = $data['package_created_from'];
		}
		
		$num_days = (strtotime($data['package_created_to']) - strtotime($data['package_created_from'])) / 86400 + 1;
		
		// Daily Usage
		$redstag_db
			->select('
				sales_flat_shipment_package_packaging.name AS carton_sku,
				SUM(sales_flat_shipment_package_packaging.qty) / '.$num_days.' AS total')
			->from('sales_flat_shipment_package_packaging')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = sales_flat_shipment_package_packaging.package_id')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where("IF(sales_flat_shipment_package.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) >= '".$data['package_created_from']."'", null, false)
			->where("IF(sales_flat_shipment_package.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) < '".date('Y-m-d', strtotime('+1 day '.$data['package_created_to']))."'", null, false)
			//->where("sales_flat_shipment_package_packaging.name LIKE 'Box%'", null, false)
			->group_by('sales_flat_shipment_package_packaging.name')
			->order_by('total', 'desc');
		
		if(!empty($data['stock_id'])) {
			$redstag_db->where_in('sales_flat_shipment_package.stock_id', $data['stock_id']);
		}
		
		if(!empty($data['client'])) {
			$redstag_db->where_in('sales_flat_order.store_id', $data['client']);
		}
		
		$usage_tmp = $redstag_db->get()->result_array();
		
		$data['carton_usage'] = array();
		
		if(!empty($usage_tmp)) {
			foreach($usage_tmp as $current_data) {
				if(!isset($data['carton_usage'][$current_data['carton_sku']])) {
					$data['carton_usage'][$current_data['carton_sku']] = array(
						'daily' => 0,
						'weekly' => 0,
						'monthly' => 0
					);
				}
				
				$data['carton_usage'][$current_data['carton_sku']]['daily'] = $current_data['total'];
			}
		}
		
		// Weekly Usage
		$redstag_db
			->select('
				sales_flat_shipment_package_packaging.name AS carton_sku,
				SUM(sales_flat_shipment_package_packaging.qty) /7 AS total')
			->from('sales_flat_shipment_package_packaging')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = sales_flat_shipment_package_packaging.package_id')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where("IF(sales_flat_shipment_package.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) >= '".date('Y-m-d', strtotime('-8 day '.$data['package_created_from']))."'", null, false)
			->where("IF(sales_flat_shipment_package.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) < '".date('Y-m-d', strtotime('-1 day '.$data['package_created_from']))."'", null, false)
			->where("sales_flat_shipment_package_packaging.name LIKE 'Box%'", null, false)
			->group_by('sales_flat_shipment_package_packaging.name')
			->order_by('total', 'desc');
		
		if(!empty($data['stock_id'])) {
			$redstag_db->where_in('sales_flat_shipment_package.stock_id', $data['stock_id']);
		}
		
		if(!empty($data['client'])) {
			$redstag_db->where_in('sales_flat_order.store_id', $data['client']);
		}
		
		$usage_tmp = $redstag_db->get()->result_array();
		
		if(!empty($usage_tmp)) {
			foreach($usage_tmp as $current_data) {
				if(!isset($data['carton_usage'][$current_data['carton_sku']])) {
					$data['carton_usage'][$current_data['carton_sku']] = array(
						'daily' => 0,
						'weekly' => 0,
						'monthly' => 0
					);
				}
				
				$data['carton_usage'][$current_data['carton_sku']]['weekly'] = $current_data['total'];
			}
		}
		
		// Monthly Usage
		$redstag_db
			->select('
				sales_flat_shipment_package_packaging.name AS carton_sku,
				SUM(sales_flat_shipment_package_packaging.qty) /30 AS total')
			->from('sales_flat_shipment_package_packaging')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = sales_flat_shipment_package_packaging.package_id')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->where("IF(sales_flat_shipment_package.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) >= '".date('Y-m-d', strtotime('-31 day '.$data['package_created_from']))."'", null, false)
			->where("IF(sales_flat_shipment_package.stock_id IN (3,6),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Mountain'),CONVERT_TZ(sales_flat_shipment_package.created_at,'UTC','US/Eastern')) < '".date('Y-m-d', strtotime('-1 day '.$data['package_created_from']))."'", null, false)
			->where("sales_flat_shipment_package_packaging.name LIKE 'Box%'", null, false)
			->group_by('sales_flat_shipment_package_packaging.name')
			->order_by('total', 'desc');
		
		if(!empty($data['stock_id'])) {
			$redstag_db->where_in('sales_flat_shipment_package.stock_id', $data['stock_id']);
		}
		
		if(!empty($data['client'])) {
			$redstag_db->where_in('sales_flat_order.store_id', $data['client']);
		}
		
		$usage_tmp = $redstag_db->get()->result_array();
		
		if(!empty($usage_tmp)) {
			foreach($usage_tmp as $current_data) {
				if(!isset($data['carton_usage'][$current_data['carton_sku']])) {
					$data['carton_usage'][$current_data['carton_sku']] = array(
						'daily' => 0,
						'weekly' => 0,
						'monthly' => 0
					);
				}
				
				$data['carton_usage'][$current_data['carton_sku']]['monthly'] = $current_data['total'];
			}
		}
		
		$data['carton_utilization_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_carton_utilization_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_client_list() {
		$redstag_db = $this->load->database('redstag', TRUE);
		$merchant_list = $redstag_db
			->select('store_id AS id, name AS client_name', false)
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