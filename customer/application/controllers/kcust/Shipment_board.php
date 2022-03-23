<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_shipment_board');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Shipment Board');

		$data = array();

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_shipment_board->get_shipment_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_shipment_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_shipment_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
