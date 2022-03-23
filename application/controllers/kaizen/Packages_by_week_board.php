<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Packages_by_week_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_package');
    }
	
	public function index() {		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Packages By Week Board');

		$data = array();
		
		$data['display_type_list'] = array('all', 'summary');
		$data['first_day_of_week_list'] = array('sunday', 'monday');
		$data['week_grouping_list'] = array('week_00_to_53' => 'Week 00 to 53', 'week_01_to_53' => 'Week 01 to 53');

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['period_from'] = isset($_GET['period_from']) ? $_GET['period_from'] : '2013-02-01';
		$data['period_to'] = isset($_GET['period_to']) ? $_GET['period_to'] : date('Y-m-d');
		$data['first_day_of_week'] = isset($_GET['first_day_of_week']) ? $_GET['first_day_of_week'] : 'sunday';
		
		// excel_weeknum (may have incomplete week at the end or start of the year) / sql (make sure complete 7 days per week)
		$data['week_grouping'] = isset($_GET['week_grouping']) ? $_GET['week_grouping'] : 'week_00_to_53';
		
		if($data['generate']) {
			$data = $this->model_package->get_packages_by_week_board_data($data);
		}
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_packages_by_week_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_packages_by_week_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
