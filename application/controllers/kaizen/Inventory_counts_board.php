<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_counts_board extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		set_time_limit(300);
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_inventory');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Inventory Counts Board');

		$data = array();
		
		$data['month_list'] = array();
		
		$the_date = date('Y-m-01', strtotime('-1 month'));
		while(strtotime($the_date) >= strtotime('2020-01-01')) {
			$data['month_list'][$the_date] = date('F Y', strtotime($the_date));
			$the_date = date('Y-m-01', strtotime('-1 month '.$the_date));
		}
		
		/*$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);*/

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['month'] = isset($_GET['month']) ? $_GET['month'] : date('Y-m-01', strtotime('-1 month'));
		
		$data['page_version'] = 1;
		$data['page_generated_time'] = null;
		
		if($data['generate']) {
			$data = $this->model_inventory->get_inventory_counts_board_data($data);
			
			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename=\"inventory-".( isset($data['month_list'][$data['month']]) ? $data['month_list'][$data['month']] : $data['month'] ).".csv\"");
			header("Pragma: no-cache");
			header("Expires: 0");

			$handle = fopen('php://output', 'w');
			$data_count = count($data);
			
			if(!empty($data['inventory_data'])) {
				$header_names = array();
				foreach($data['inventory_data'][0] as $header_name => $value) {
					$header_names[] = ucwords(str_replace('_',' ',$header_name));
				}
				fputcsv($handle, $header_names);
				
				foreach ($data['inventory_data'] as $current_data) {
					fputcsv($handle, $current_data);
				}
			}
			
			fclose($handle);
			exit;
		}
		
		$footer_data = array();
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_inventory_counts_board', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_inventory_counts_board', $data);
		$this->load->view('view_footer', $footer_data);
	}
	
	public function generate_last_month_data($stock_id=2) {
		$data = $this->model_inventory->get_last_month_inventory_counts($stock_id);
		//debug_var($data);
		
		$this->benchmark->mark('before_csv');
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=\"inventory-".date('Y-m', strtotime('last month')).".csv\"");
		header("Pragma: no-cache");
		header("Expires: 0");

		$handle = fopen('php://output', 'w');
		$data_count = count($data);
		
		//$elapsed = $this->benchmark->elapsed_time('code_start', 'before_csv');
		//debug_var($elapsed, 'before_csv');
		
		foreach ($data as $current_data) {
			fputcsv($handle, $current_data);
		}
		fclose($handle);

		
		exit;
	}
	
	// e.g. $month = 2021-07-01
	public function generate_long_term_inventory_counts($month, $days_backward = 180) {
		$data = $this->model_inventory->get_long_term_inventory_report( array('month' => $month, 'days_backward' => $days_backward) );
		
		$month_text = date('M Y', strtotime($month));
		
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=\"long-term-inventory-".$month.".csv\"");
		header("Pragma: no-cache");
		header("Expires: 0");

		$handle = fopen('php://output', 'w');
		$data_count = count($data);
		
		if(!empty($data['long_term_inventory'])) {
			$header_names = array('Month/Year', 'Client Name', 'Product ID', 'SKU', 'Average Inventory Count over '.$days_backward.' days', 'Length', 'Width', 'Height');
			fputcsv($handle, $header_names);
			
			foreach ($data['long_term_inventory'] as $current_data) {
				$row_data = array(
					$month_text,
					$current_data['client_name'],
					$current_data['product_id'],
					$current_data['sku'],
					$current_data['qty'],
					$current_data['length'],
					$current_data['width'],
					$current_data['height']
				);
				
				fputcsv($handle, $row_data);
			}
		}
		
		fclose($handle);
		exit;		
	}
}
