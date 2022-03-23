<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_monitoring');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Monitoring');
		
		$data = array();
		$data['monitoring_data'] = $this->model_monitoring->get_monitoring_data();
		
		$data['page_generated_time'] = date('Y-m-d H:i:s');
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_monitoring', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_monitoring', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
