<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_inbound_idle_time extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_inbound_idle_time_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$timezone_name = ($timezone == -7) ? 'US/Mountain' : 'US/Eastern';
		
		if(empty($data['status'])) {
			$data['status'] = array();
		}
		
		// Current timezone
		$timezone += date('I');
		
		$data['status_summary'] = array();
		if(!empty($data['delivery_status_list'])) {
			foreach($data['delivery_status_list'] as $status_code => $status_name) {
				$data['status_summary'][$status_code] = array(
					'status_name' => $status_name,
					'count' => 0
				);
			}
		}
		
		$redstag_db
			->select("
				delivery.increment_id AS delivery_no,
				delivery.status,
				core_website.name AS merchant_name,
				delivery.carrier_name,
				delivery.total_skus,
				delivery.num_containers,
				delivery.num_exceptions,
				delivery.progress,
				CONVERT_TZ(delivery.receive_by,'UTC','".$timezone_name."') AS local_receive_by,
				TIMESTAMPDIFF(HOUR,CURTIME(),delivery.receive_by) AS duration,
				CONVERT_TZ(MAX(delivery_status_history.created_at),'UTC','".$timezone_name."') AS last_action_time,
				TIMESTAMPDIFF(SECOND,MAX(delivery_status_history.created_at),CURTIME()) AS secs_since_last_action",
				false)
			->from('delivery_status_history')
			->join('delivery', 'delivery.delivery_id = delivery_status_history.delivery_id')
			->join('core_website', 'core_website.website_id = delivery.website_id', 'left')
			->where('delivery_type', 'asn')		
			//->where('delivery.receive_by < CURTIME()', null, false)
			->where('core_website.name <>', 'LGDC')
			->group_by('delivery_status_history.delivery_id')
			->order_by('last_action_time');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('delivery.stock_id', $stock_id);
		}
		
		if(!empty($data['status'])) {
			$redstag_db->where_in('delivery.status', $data['status']);
		}
		
		if(!in_array('complete', $data['status'])) {
			$redstag_db
				->where('delivery.progress <', 100)
				->where('delivery.status <>', 'complete');
		}
		
		if(!empty($data['created_at_period_from'])) {
			if(empty($data['created_at_period_to'])) {
				$data['created_at_period_to'] = $data['created_at_period_from'];
			}
			
			$redstag_db
				->where("CONVERT_TZ(delivery.created_at,'UTC','".$timezone_name."') >=", $data['created_at_period_from'])
				->where("CONVERT_TZ(delivery.created_at,'UTC','".$timezone_name."') <", date('Y-m-d', strtotime('+1 day ' . $data['created_at_period_to'])));
		}
		
		if(!empty($data['completed_date_period_from'])) {
			if(empty($data['completed_date_period_to'])) {
				$data['completed_date_period_to'] = $data['completed_date_period_from'];
			}
			
			$redstag_db
				->where('delivery.status', 'complete')
				->having("last_action_time >=", $data['completed_date_period_from'])
				->having("last_action_time <", date('Y-m-d', strtotime('+1 day ' . $data['completed_date_period_to'])));
		}

		$data['waiting_asns'] = $redstag_db->get()->result_array();
		
		// Accepted At
		$redstag_db
			->select("
				delivery.increment_id AS delivery_no,
				CONVERT_TZ(delivery_status_history.created_at,'UTC','".$timezone_name."') AS accepted_at",
				false)
			->from('delivery_status_history')
			->join('delivery', 'delivery.delivery_id = delivery_status_history.delivery_id')
			->join('core_website', 'core_website.website_id = delivery.website_id', 'left')
			->where('delivery_type', 'asn')		
			->where('delivery_status_history.status', 'accepted')
			->where('core_website.name <>', 'LGDC');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('delivery.stock_id', $stock_id);
		}
		
		if(!empty($data['status'])) {
			$redstag_db->where_in('delivery.status', $data['status']);
		}
		
		if(!in_array('complete', $data['status'])) {
			$redstag_db
				->where('delivery.progress <', 100)
				->where('delivery.status <>', 'complete');
		}
		
		if(!empty($data['created_at_period_from'])) {
			if(empty($data['created_at_period_to'])) {
				$data['created_at_period_to'] = $data['created_at_period_from'];
			}
			
			$redstag_db
				->where("CONVERT_TZ(delivery.created_at,'UTC','".$timezone_name."') >=", $data['created_at_period_from'])
				->where("CONVERT_TZ(delivery.created_at,'UTC','".$timezone_name."') <", date('Y-m-d', strtotime('+1 day ' . $data['created_at_period_to'])));
		}
		
		if(!empty($data['accepted_at_period_from'])) {
			if(empty($data['accepted_at_period_to'])) {
				$data['accepted_at_period_to'] = $data['accepted_at_period_from'];
			}
			
			$redstag_db
				->where("CONVERT_TZ(delivery_status_history.created_at,'UTC','".$timezone_name."') >=", $data['accepted_at_period_from'])
				->where("CONVERT_TZ(delivery_status_history.created_at,'UTC','".$timezone_name."') <", date('Y-m-d', strtotime('+1 day ' . $data['accepted_at_period_to'])));
		}
		
		$accepted_waiting_asns_tmp = $redstag_db->get()->result_array();
		
		$accepted_waiting_asns = array_combine(
			array_column($accepted_waiting_asns_tmp, 'delivery_no'),
			array_column($accepted_waiting_asns_tmp, 'accepted_at')
		);
		
		$status_list = !empty($data['delivery_status_list']) ? array_keys($data['delivery_status_list']) : array();
		
		foreach($data['waiting_asns'] as $key => $waiting_asns) {
			if(!empty($data['accepted_at_period_from']) && !isset($accepted_waiting_asns[$waiting_asns['delivery_no']])) {
				unset($data['waiting_asns'][$key]);
				continue;
			}
			
			$data['waiting_asns'][$key]['accepted_at'] = isset($accepted_waiting_asns[$waiting_asns['delivery_no']]) ? $accepted_waiting_asns[$waiting_asns['delivery_no']] : null;
			
			$data['waiting_asns'][$key]['completed_date'] = ($waiting_asns['status'] == 'complete') ? $waiting_asns['last_action_time'] : null;
			
			if(!empty($status_list) && in_array($waiting_asns['status'], $status_list)) {
				$data['status_summary'][$waiting_asns['status']]['count']++;
			}
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['inbound_idle_time_waiting_asns_html'] = $this->load->view(PROJECT_CODE.'/view_inbound_idle_time_board_waiting_asns', $data, true);
		
		$data['js_outbound_takt_waiting_asns_html'] = $this->load->view(PROJECT_CODE.'/js_view_inbound_idle_time_board_waiting_asns', $data, true);
		
		return $data;
	}
}