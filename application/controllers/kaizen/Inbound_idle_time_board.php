<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inbound_idle_time_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_inbound_idle_time');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Inbound Idle Time Board');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['delivery_status_list'] = array(
			'accepted' => 'Accepted',
			'accepting' => 'Accepting',
			'canceled' => 'Canceled',
			'complete' => 'Complete',
			'new' => 'New',
			'processed' => 'Processed',
			'processing' => 'Processing',
			'processing_exception' => 'Processing Exception',
			'put_away' => 'Put Away',
			'putting_away' => 'Putting Away',
			'ready_to_process' => 'Ready To Process',
			'void' => 'Void'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['status'] = isset($_GET['status']) ? $_GET['status'] : array();
		$data['created_at_period_from'] = isset($_GET['created_at_period_from']) ? $_GET['created_at_period_from'] : null;
		$data['created_at_period_to'] = isset($_GET['created_at_period_to']) ? $_GET['created_at_period_to'] : $data['created_at_period_from'];
		$data['accepted_at_period_from'] = isset($_GET['accepted_at_period_from']) ? $_GET['accepted_at_period_from'] : null;
		$data['accepted_at_period_to'] = isset($_GET['accepted_at_period_to']) ? $_GET['accepted_at_period_to'] : null;
		$data['completed_date_period_from'] = isset($_GET['completed_date_period_from']) ? $_GET['completed_date_period_from'] : null;
		$data['completed_date_period_to'] = isset($_GET['completed_date_period_to']) ? $_GET['completed_date_period_to'] : null;
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_inbound_idle_time->get_inbound_idle_time_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_inbound_idle_time_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_inbound_idle_time_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
