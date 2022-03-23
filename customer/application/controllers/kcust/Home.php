<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title(lang('title__home'));

		$data = array();
		
		$data['shortcuts'] = array(
			array('glyphicon' => 'list', 'label' => 'Package Status Board', 'url' => base_url('kcust/carrier_status_dashboard_for_packages')),
			array('glyphicon' => 'list', 'label' => 'Client Inventory Optimization Board', 'url' => base_url('kcust/client_inventory_optimization_board')),
		);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_home', $data);
		$this->load->view('view_footer');
	}
}
