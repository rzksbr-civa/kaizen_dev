<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_inventory');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Inventory Report');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;

		$footer_data = array();
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_inventory_report', $data, true);
		
		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_inventory_report', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
