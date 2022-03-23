<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_inbound extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_inbound_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$timezone_name = ($timezone == -7) ? 'US/Mountain' : 'US/Eastern';
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		// Daylight saving time
		$period_from_timezone = $timezone + date('I', strtotime($period_from));
		$period_to_timezone = $timezone + date('I', strtotime($period_to));
		
		// Current timezone
		$timezone += date('I');
		
		$local_date_field = "DATE(CONVERT_TZ(delivery.created_at, 'UTC', '".$timezone_name."'))";

		switch($data['periodicity']) {
			case 'weekly':
				$select_period_query = 'DATE_ADD('.$local_date_field.', INTERVAL - WEEKDAY('.$local_date_field.') DAY)';
				$select_period_label_query = 'CONCAT("WEEK ", '.$select_period_query.')';
				break;
			case 'monthly':
				$select_period_query = 'DATE_FORMAT('.$local_date_field.', "%Y-%m-01")';
				$select_period_label_query = 'DATE_FORMAT('.$local_date_field.', "%Y-%m (%M %Y)")';
				break;
			case 'yearly':
				$select_period_query = 'DATE_FORMAT('.$local_date_field.', "%Y-01-01")';
				$select_period_label_query = 'DATE_FORMAT('.$local_date_field.', "%Y")';
				break;
			case 'daily':
			default:
				$select_period_query = $local_date_field;
				$select_period_label_query = $local_date_field;
		}
		
		$redstag_db
			->select($select_period_query.' AS period, '.$select_period_label_query.' AS period_label, AVG(TIME_TO_SEC(TIMEDIFF(delivery.putaway_at, delivery.delivered_at)) / 3600) AS average_accept_putaway_duration, SUM(num_containers) AS num_pallets_processed, SUM(total_skus) AS num_skus_processed, COUNT(*) AS asn_count', false)
			->from('delivery')
			->where('delivery.delivery_type', 'asn')
			->where('delivery.created_at >=', date('Y-m-d H:i:s', strtotime(($period_from_timezone <= 0 ? '+' : '').($period_from_timezone*-1). ' hour ' . $period_from)))
			->where('delivery.created_at <', date('Y-m-d H:i:s', strtotime(($period_to_timezone <= 0 ? '+' : '').($period_to_timezone*-1). ' hour ' . date('Y-m-d', strtotime('+1 day '. $period_to)))))
			->where('delivery.delivered_at IS NOT NULL', null, false)
			->where('delivery.putaway_at IS NOT NULL', null, false)
			->group_by($select_period_query.','.$select_period_label_query)
			->order_by('period');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('delivery.stock_id', $stock_id);
		}

		$accept_putaway_throughput_data = $redstag_db->get()->result_array();
		
		$data['accept_putaway_throughput_data'] = $accept_putaway_throughput_data;
		
		switch($data['periodicity']) {
			case 'daily':
			case 'weekly':
				$data['datetime_formatter'] = 'yyyy-MM-dd';
				break;
			case 'monthly':
				$data['datetime_formatter'] = "MMM 'yy";
				break;
			case 'yearly':
				$data['datetime_formatter'] = 'yyyy';
				break;
			default:
				$data['datetime_formatter'] = 'yyyy-MM-dd';
				break;
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['inbound_board_accept_putaway_throughput_html'] = $this->load->view(PROJECT_CODE.'/view_inbound_board_accept_putaway_throughput', $data, true);
		
		$data['js_inbound_board_accept_putaway_throughput_html'] = $this->load->view(PROJECT_CODE.'/js_view_inbound_board_accept_putaway_throughput', $data, true);
		
		return $data;
	}
}