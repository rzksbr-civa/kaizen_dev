<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Packages_by_month_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_package');
    }
	
	public function index() {		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Packages By Month Board');

		$data = array();
		
		
		$data['display_type_list'] = array('all', 'summary');

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : '2013-02-01';
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		
		if($data['generate']) {
			$data = $this->model_package->get_packages_by_month_board_data($data);	
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_packages_by_month_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_packages_by_month_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
