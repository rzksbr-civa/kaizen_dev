<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carton_utilization_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_carton_utilization');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Carton Utilization Board');

		$data = array();

		$data['client_list'] = $this->model_carton_utilization->get_client_list();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'stock_id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['stock_id'] = isset($_GET['stock_id']) ? $_GET['stock_id'] : array();
		$data['client'] = isset($_GET['client']) ? $_GET['client'] : array();
		$data['package_created_from'] = isset($_GET['package_created_from']) ? $_GET['package_created_from'] : date('Y-m-d');
		$data['package_created_to'] = isset($_GET['package_created_to']) ? $_GET['package_created_to'] : $data['package_created_from'];

		$data['page_version'] = 1;
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		if($data['generate']) {
			$data = $this->model_carton_utilization->get_carton_utilization_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_carton_utilization_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_carton_utilization_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
