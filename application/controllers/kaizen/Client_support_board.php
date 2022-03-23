<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_support_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_ticket');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Client Support Board');

		$data = array();
		
		$data['periodicity_list'] = array('hourly', 'daily', 'weekly', 'monthly', 'yearly');
		
		$data['customer_list'] = $this->model_ticket->get_customer_list();

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['customer'] = isset($_GET['customer']) ? $_GET['customer'] : null;
		$data['periodicity'] = isset($_GET['periodicity']) ? $_GET['periodicity'] : 'daily';
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : date('Y-m-d', strtotime('-8 days'));
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d', strtotime('-1 day'));
		
		if($data['generate']) {
			$data = $this->model_ticket->get_client_support_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_client_support_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_client_support_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
