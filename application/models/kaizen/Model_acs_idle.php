<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_acs_idle extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function get_acs_idle_board_data($data) {
		$data['idle_shipments'] = array();
		
		$redstag_db = $this->load->database('redstag', TRUE);

		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		$timezone += date('I'); // Daylight saving time
		
		$current_utc_time = gmdate('Y-m-d H:i:s');
		
		$date = date('Y-m-d');
		
		$removed_orders = $this->model_db_crud->get_several_data('removed_idle_order', array('date' => $date));
		$removed_order_nos = array_column($removed_orders, 'order_no');
		
		$redstag_db
			->select('
				sales_flat_order.increment_id AS order_no,
				sales_flat_shipment.status,
				TIME_TO_SEC(TIMEDIFF("'.$current_utc_time.'",action_log.started_at)) AS idle_duration, 
				admin_user.name AS last_action_by')
			->from('action_log')
			->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = action_log.entity_id')
			->join('sales_flat_order', 'sales_flat_order.entity_id = sales_flat_shipment.order_id')
			->join('admin_user', 'admin_user.user_id = action_log.user_id')
			->where_in('action_log.action', array('pick','pack'))
			->where('action_log.entity_type', 'shipment')
			->where('action_log.finished_at IS NULL', null, false)
			->where('action_log.started_at >=', date('Y-m-d'))
			->where('action_log.started_at <', date('Y-m-d H:i:s', strtotime('-1 hour '.$current_utc_time)))
			->order_by('idle_duration', 'desc');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('action_log.stock_id', $stock_id);
		}
		
		$data['idle_shipments'] = $redstag_db->get()->result_array();
		
		if(!empty($data['idle_shipments'])) {
			foreach($data['idle_shipments'] as $key => $current_data) {
				if($current_data['idle_duration'] >= 4 * 3600) {
					$data['idle_shipments'][$key]['color'] = 'red';
				}
				else {
					$data['idle_shipments'][$key]['color'] = 'yellow';
				}
				
				$data['idle_shipments'][$key]['is_removed'] = in_array($current_data['order_no'], $removed_order_nos);
				
				if(($data['data_visibility'] == 'removed' && !$data['idle_shipments'][$key]['is_removed']) || ($data['data_visibility'] == 'not_removed' && $data['idle_shipments'][$key]['is_removed'])) {
					unset($data['idle_shipments'][$key]);
				}
 
			}
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.$current_utc_time));
		
		$data['acs_idle_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_acs_idle_board_visualization', $data, true);
		
		return $data;
	}
	
	public function change_idle_order_state($action, $order_no) {
		$result = array('success' => false);
		
		$date = date('Y-m-d');
		
		$removed_idle_orders = $this->model_db_crud->get_several_data('removed_idle_order', array('order_no' => $order_no));
		
		if($action == 'remove') {
			if(empty($removed_idle_orders)) {
				$remove_idle_order = $this->model_db_crud->add_item('removed_idle_orders', array('order_no'=>$order_no, 'date'=>$date));
			}
			$result['success'] = true;
			$result['td_action_html'] = '<a class="btn btn-default btn-change-idle-order-state" action="unremove" order_no="'.$order_no.'">Unremove</a>';
		}
		else if($action == 'unremove') {
			foreach($removed_idle_orders as $removed_idle_order) {
				$unremove_idle_order = $this->model_db_crud->delete_item('removed_idle_orders', $removed_idle_order['id']);
				$result['success'] = true;
			}
			$result['td_action_html'] = '<a class="btn btn-default btn-change-idle-order-state" action="remove" order_no="'.$order_no.'">Remove</a>';
		}

		return $result;
	}
}