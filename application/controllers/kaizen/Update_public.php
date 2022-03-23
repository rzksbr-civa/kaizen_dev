<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Update_public extends CI_Controller {
	public function __construct(){
		parent::__construct();
		ignore_user_abort(true);
    }
	
	public function index() {
		
	}
	
	// Update open tickets status count
	public function open_tickets_status_count() {
		$header_data = array();
		$header_data['page_title'] = 'Update';

		$body_data = array();
		
		$this->load->model(PROJECT_CODE.'/model_ticket');
		$result_open_tickets_status_count = $this->model_ticket->update_open_tickets_status_count();

		echo $result_open_tickets_status_count['message'];	
	}
	
	public function mins_to_initial_response() {
		$header_data = array();
		$header_data['page_title'] = 'Update Mins to Initial Response';

		$body_data = array();
		
		$this->load->model(PROJECT_CODE.'/model_ticket');
		$result = $this->model_ticket->update_mins_to_initial_response();
		
		echo $result['message'];
	}
	
	public function wages($date = '') {
		if(empty($date)) {
			$date = date('Y-m-d');
			$yesterday = date('Y-m-d', strtotime('yesterday'));
		}
		
		$this->load->model(PROJECT_CODE.'/model_revenue');
		
		//$this->model_revenue->update_tsheets_employees_data();
		
		if(isset($yesterday)) {
			$this->model_revenue->update_wages_data($yesterday);
		}
		
		$result = $this->model_revenue->update_wages_data($date);
		
		//echo '<html><head><title>Update wages</title><body>';
		echo $date . ': ' . $result['updated_wages_data_count'] . ' updated data.<br>';
		//echo '<script>window.location = "'.base_url('kaizen/update_public/wages/'.date('Y-m-d', strtotime('+1 day '.$date))).'";</script>';
		//echo '</body></html>';
	}
	
	public function inbound_and_outbound_revenue($date = '') {
		if(empty($date)) {
			$date = date('Y-m-d');
			$yesterday = date('Y-m-d', strtotime('yesterday'));
		}
		
		$this->load->model(PROJECT_CODE.'/model_revenue');
		
		if(isset($yesterday)) {
			$this->model_revenue->update_inbound_and_outbound_revenue_data($yesterday);
		}
		
		$result = $this->model_revenue->update_inbound_and_outbound_revenue_data($date);
		
		echo $date . ': Inbound and outbound revenue data updated.<br>';
		//echo '<script>window.location = "'.base_url('kaizen/update_public/inbound_and_outbound_revenue/'.date('Y-m-d', strtotime('+1 day '.$date))).'";</script>';
	}
	
	public function wages_and_revenue($date = '') {
		$this->inbound_and_outbound_revenue($date);
		$this->wages($date);
	}
	
	public function packages($date = '') {
		$this->load->model(PROJECT_CODE.'/model_package');
		
		if(empty($date)) {
			$date = date('Y-m-d');
			$yesterday_date = date('Y-m-d', strtotime('yesterday'));
			$result = $this->model_package->update_package_data($yesterday_date);
			echo $yesterday_date . ': Package updated<br>';
		}

		$result = $this->model_package->update_package_data($date);
	
		echo $date . ': Package updated';
	}
	
	public function live_drops() {
		$this->load->model(PROJECT_CODE.'/model_inventory');
		
		$result = $this->model_inventory->update_live_drops_logs();
		
		echo $result['message'];
	}
	
	public function shipment_report_table() {
		$this->load->model(PROJECT_CODE.'/model_shipment');
		
		$message = $this->model_shipment->update_shipment_report_table();
		
		echo $message;
	}
	
	public function sync_action_log_table() {
		$this->load->model(PROJECT_CODE.'/model_shipment');
		$this->model_shipment->sync_action_log_table();
		echo 'Success';
	}
	
	public function run_update_script($update_function_name) {
		$data = array(
			'update_function_name' => $update_function_name
		);
		
		$this->load->view(PROJECT_CODE.'/view_run_update_script', $data);
	}
	
	public function ticket_history() {
		$header_data = array();
		$header_data['page_title'] = 'Update Ticket History';

		$body_data = array();
		
		$this->load->model(PROJECT_CODE.'/model_ticket');
		$result = $this->model_ticket->update_ticket_history();
		
		echo $result['message'];
	}
	
	public function update_current_open_tickets() {
		$header_data = array();
		$header_data['page_title'] = 'Update Open Tickets';

		$body_data = array();
		
		$this->load->model(PROJECT_CODE.'/model_ticket');
		$result = $this->model_ticket->update_current_open_tickets();
	}
	
	public function update_daily_stock_movement($date = null) {
		if(empty($date)) {
			$date = date('Y-m-d');
		}
		
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$result = $this->model_inventory->update_daily_stock_movement($date);
		
		if($result['success']) {
			echo $date . ': Success';
		}
		
		echo '<script>window.location = "'.base_url('kaizen/update_public/update_daily_stock_movement/'.date('Y-m-d', strtotime('+1 day '.$date))).'";</script>';
	}
	
	public function track_ups($track_number) {
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$result = $this->model_package_status_board->get_ups_tracking_status( array('track_number' => $track_number) );
		
		print_r($result);
	}
	
	public function update_ups_packages_tracking_by_date($date) {
		$date = str_replace('%20',' ',$date);
		$this->load->model(PROJECT_CODE.'/model_package_status_board');

		$result = $this->model_package_status_board->update_ups_packages_tracking_by_date($date);
		echo $date.': '.$result['count'] . ' updated';
		
		echo '<script>window.location = "'.base_url('kaizen/update_public/update_ups_packages_tracking_by_date/'.date('Y-m-d H:i:s', strtotime('+10 minute '.$date))).'";</script>';
	}
	
	public function update_fedex_packages_tracking_by_date($date) {
		$date = str_replace('%20',' ',$date);
		$this->load->model(PROJECT_CODE.'/model_package_status_board');

		$result = $this->model_package_status_board->update_fedex_packages_tracking_by_date($date);
		echo $date.': '.$result['count'] . ' updated';
		
		$prod_db = $this->load->database('prod', TRUE);
		$undelivered_fedex_packages = $prod_db->select('COUNT(*) AS total')->from('packages')->where('carrier_code', 'fedex')->where('actual_delivery_date IS NULL', null, false)->get()->result_array();
		
		echo '<br><br>Undelivered FedEx packages: '.$undelivered_fedex_packages[0]['total'];
		
		echo '<script>window.location = "'.base_url('kaizen/update_public/update_fedex_packages_tracking_by_date/'.date('Y-m-d H:i:s', strtotime('+5 minute '.$date))).'";</script>';
	}
	
	public function update_tsheets_groups($page) {
		$this->load->model(PROJECT_CODE.'/model_revenue');
		$this->model_revenue->update_tsheets_groups($page);
	}
	
	public function update_inventory_levels_table($date = null) {
		if(empty($date)) {
			$date = date('Y-m-d', strtotime('yesterday'));
		}
		
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$result = $this->model_inventory->update_inventory_levels_table($date);
		$result = $this->model_inventory->update_products_table();
		
		echo $date. ': '.$result['message'];
	}
	
	public function update_ups_packages_status() {
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$result = $this->model_package_status_board->update_ups_packages_status();
		
		echo 'Successful: ' . $result['success_count'] . '; Prev check: ' . $result['previously_checked_at'];
		if(!empty($result['unsuccessful_package_track_numbers'])) {
			echo '; Unsuccessful: ' . implode(', ', $result['unsuccessful_package_track_numbers']);
		}
		
		// echo '<script> location.reload(); </script>';
	}
	
	public function update_fedex_packages_status() {
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$result = $this->model_package_status_board->update_fedex_packages_status();
		
		echo 'Successful: ' . $result['success_count'] . '; Prev check: ' . $result['previously_checked_at'];
		if(!empty($result['unsuccessful_package_track_numbers'])) {
			echo '; Unsuccessful: ' . implode(', ', $result['unsuccessful_package_track_numbers']);
		}
		
		$prod_db = $this->load->database('prod', TRUE);
		$undelivered_fedex_packages = $prod_db->select('COUNT(*) AS total')->from('packages')->where('carrier_code', 'fedex')->where('actual_delivery_date IS NULL', null, false)->get()->result_array();
		
		echo '<br><br>Undelivered FedEx packages: '.$undelivered_fedex_packages[0]['total'];
		
		echo '<script> location.reload(); </script>';
	}
	
	// LaserShip, FedEx, OnTrac, USPS
	public function update_other_carriers_packages_status() {
		if(date('i') == '00') {
			$this->load->model(PROJECT_CODE.'/model_package');
			$result = $this->model_package->update_package_data($date);
		}
		
		$this->load->model(PROJECT_CODE.'/model_package_status_board');
		$result = $this->model_package_status_board->update_other_carriers_packages_status();
		
		echo 'Successful: ' . $result['success_count'] . '; Prev check: ' . $result['previously_checked_at'];
		if(!empty($result['unsuccessful_package_track_numbers'])) {
			echo '; Unsuccessful package ID: ' . implode(', ', $result['unsuccessful_package_ids']);
		}
		
		// echo '<script> location.reload(); </script>';
	}
	
	public function update_ups_expected_delivery_date() {
		$this->load->model(PROJECT_CODE.'/model_package');
		$result = $this->model_package->update_ups_expected_delivery_date();
		print_r($result);
		
		echo '<script> location.reload(); </script>';
	}
	
	public function generate_pods() {
		$this->load->model(PROJECT_CODE.'/model_package');
		$result = $this->model_package->generate_next_pods();
		print_r($result);
	}
	
	public function update_long_term_inventory_levels_table_by_date($date, $days_backward = 180) {
		ignore_user_abort(true);
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$result = $this->model_inventory->update_long_term_inventory_levels_table($date, $days_backward);
		
		if($result['success']) {
			echo $date;
			echo '<script> window.location = "'.base_url(PROJECT_CODE.'/update_public/update_long_term_inventory_levels_table_by_date/'.date('Y-m-d', strtotime('+1 day '.$date))).'/'.$days_backward.'"; </script>';
		}
	}
	
	public function update_long_term_inventory_levels_table($days_backward = 180) {
		ignore_user_abort(true);
		$prod_db = $this->load->database('prod', TRUE);
		
		$date = date('Y-m-d', strtotime('yesterday'));
		
		// Update yesterday's data or a day after max date
		$max_date_tmp = $prod_db
			->select('MAX(date) AS max_date')
			->from('long_term_inventory_levels')
			->where('days_backward', $days_backward)
			->get()->result_array();
		
		if(!empty($max_date_tmp)) {
			$max_date = $max_date_tmp[0]['max_date'];
			if(strtotime('+1 day '.$max_date) < strtotime($date)) {
				$date = date('Y-m-d', strtotime('+1 day '.$max_date));
			}
		}
		
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$result = $this->model_inventory->update_long_term_inventory_levels_table($date, $days_backward);
		
		if($result['success']) {
			echo 'Success: ' . $date;
		}
	}
	
	public function update_inventory_levels_table_2($date = null) {
		if(empty($date)) {
			$date = date('Y-m-d', strtotime('yesterday'));
		}
		
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$result = $this->model_inventory->update_inventory_levels_table($date);
		$result = $this->model_inventory->update_products_table();
		
		echo $date. ': '.$result['message'];
		
		echo '<script> window.location = "'.base_url(PROJECT_CODE.'/update_public/update_inventory_levels_table_2/'.date('Y-m-d', strtotime('-1 day '.$date))).'"; </script>';
	}
}
