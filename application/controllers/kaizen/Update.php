<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Update extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Update');

		$data = array();

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_update', $data);
		$this->load->view('view_footer');
	}
	
	public function employees() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Update Employees');
		
		$data = array();
		
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$data['updated_employees'] = $this->model_outbound->update_employees_data();

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_update_employees', $data);
		$this->load->view('view_footer');
	}
	
	public function tickets($date) {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Update Tickets');
		
		$data = array();
		
		$this->load->model(PROJECT_CODE.'/model_ticket');
		$data['updated_tickets'] = $this->model_ticket->import_ticket_data($date);

		$this->load->view('view_header', $header_data);
		echo '<div class="container">'.$date.' DONE: Added '.$data['updated_tickets']['count'].' tickets</div>';
		//echo '<script>window.location = "'.base_url('kaizen/update/tickets/'.date('Y-m-d', strtotime('+1 day '.$date))).'";</script>';
		$this->load->view('view_footer');
	}
	
	public function pay_rate() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Update Employees\' Pay Rate');
		
		$data = array();
		
		$this->load->model(PROJECT_CODE.'/model_package_board');
		$data = $this->model_package_board->update_employees_pay_rate_data();

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_update_employees_pay_rate', $data);
		$this->load->view('view_footer');
	}
	
	public function packages_shipping_method($date) {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Update Packages Shipping Method');
		
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$updated_package_data = $redstag_db
			->select('package_id,shipping_method')
			->from('sales_flat_shipment_package')
			->join('sales_flat_shipment','sales_flat_shipment.entity_id=sales_flat_shipment_package.shipment_id')
			->where('sales_flat_shipment_package.created_at >=', $date)
			->where('sales_flat_shipment_package.created_at <', date('Y-m-d', strtotime('+1 day '.$date)))
			->get()->result_array();
		
		if(!empty($updated_package_data)) {
			$prod_db->update_batch('packages', $updated_package_data, 'package_id');
		}
		
		echo $date . ' SUCCESS ('.count($updated_package_data).')';
		echo '<script>window.location.replace("http://kaizen.ikanbanonline.com/dev01/kaizen/update/packages_shipping_method/'.date('Y-m-d',strtotime('-1 day '.$date)).'");</script>';
	}
	
	public function ontrac_expected_delivery_date() {
		$this->load->model(PROJECT_CODE.'/model_package');
		$result = $this->model_package->update_ontrac_expected_delivery_date();
		echo ($result['success']) ? 'Success' : 'Failed';
	}
	
	public function ups_expected_delivery_date() {
		$this->load->model(PROJECT_CODE.'/model_package');
		$result = $this->model_package->update_ups_expected_delivery_date();
		echo ($result['success']) ? 'Success' : 'Failed';
		echo '<script>window.location.reload();</script>';
	}
	
	public function target_ship_date($limit) {
		if(empty($limit)) {
			$limit = 1000;
		}
		
		$this->load->model(PROJECT_CODE.'/model_package');
		$result = $this->model_package->update_target_ship_date($limit);
		
		echo 'Min:'.$result['min'].'; Max:'.$result['max'];
		echo '<script>window.location.reload();</script>';
	}
	
	public function update_package_data($date) {
		$this->load->model(PROJECT_CODE.'/model_package');
		$result = $this->model_package->update_package_data($date);
		
		print_r($result);
	}
}
