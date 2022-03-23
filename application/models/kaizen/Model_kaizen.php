<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_kaizen extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function get_trend_graph_data($args) {
		$graph = array();
		
		// Weekly
		$this->db
			->select('CONCAT("WEEK ", DATE_ADD(date, INTERVAL - WEEKDAY(date) DAY)) AS the_week, COUNT(*) AS total')
			->from('arc')
			->where('arc.data_status', 'active')
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('arc.date >=', $args['period_from'])
			->where('arc.date <=', $args['period_to'])
			->group_by('the_week');
		
		if(!empty($args['abnormal_type'])) {
			$this->db->where('arc.abnormal_type', $args['abnormal_type']);
		}
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
			
		$data = $this->db->get()->result_array();
		$weekly_data = array();
		foreach($data as $current_data) {
			$weekly_data[$current_data['the_week']] = $current_data['total'];
		}
		
		$date = date('Y-m-d', strtotime('last monday', strtotime('+1 day '. $args['period_from'])));
		while($date <= $args['period_to']) {
			$week = 'WEEK ' . $date;
			$week_with_num = 'WEEK-' . date('W', strtotime($date)) . ' ' . $date;

			if(isset($weekly_data[$week])) {
				$graph['weekly'][$date] = $weekly_data[$week];
			}
			else {
				$graph['weekly'][$date] = 0;
			}
			
			$date = date('Y-m-d', strtotime('+7 day ' . $date));
		}
		
		// Monthly
		$this->db
			->select('DATE_FORMAT(date, "%b-%Y") AS the_month, COUNT(*) AS total')
			->from('arc')
			->where('arc.data_status', 'active')
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('arc.date >=', $args['period_from'])
			->where('arc.date <=', $args['period_to'])
			->group_by('the_month');
			
		if(!empty($args['abnormal_type'])) {
			$this->db->where('arc.abnormal_type', $args['abnormal_type']);
		}
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
		
		$data = $this->db->get()->result_array();
		$monthly_data = array();
		foreach($data as $current_data) {
			$monthly_data[$current_data['the_month']] = $current_data['total'];
		}
		
		$date = date('Y-m-01', strtotime($args['period_from']));
		while($date <= $args['period_to']) {
			$month = date('M-Y', strtotime($date));
			
			if(isset($monthly_data[$month])) {
				$graph['monthly'][$month] = $monthly_data[$month];
			}
			else {
				$graph['monthly'][$month] = 0;
			}
			
			$date = date('Y-m-d', strtotime('+1 month ' . $date));
		}
		
		// Yearly
		$this->db
			->select('DATE_FORMAT(date, "%Y") AS the_year, COUNT(*) AS total')
			->from('arc')
			->where('arc.data_status', 'active')
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('arc.date >=', $args['period_from'])
			->where('arc.date <=', $args['period_to'])
			->group_by('the_year');
			
		if(!empty($args['abnormal_type'])) {
			$this->db->where('arc.abnormal_type', $args['abnormal_type']);
		}
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
		
		$data = $this->db->get()->result_array();
		$yearly_data = array();
		foreach($data as $current_data) {
			$yearly_data[$current_data['the_year']] = $current_data['total'];
		}
		
		$date = date('Y-01-01', strtotime($args['period_from']));
		while($date <= $args['period_to']) {
			$year = date('Y', strtotime($date));
			
			if(isset($yearly_data[$year])) {
				$graph['yearly'][$year] = $yearly_data[$year];
			}
			else {
				$graph['yearly'][$year] = 0;
			}
			
			$date = date('Y-m-d', strtotime('+1 year ' . $date));
		}

		return $graph;
	}
	
	public function get_kpi_graph_data($args) {
		$graph_data = array();
		
		// Department
		$this->db
			->select('department_name, COUNT(*) AS total')
			->from('arc')
			->join('departments', 'departments.id = arc.department', 'left')
			->where('arc.data_status', 'active')
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('departments.data_status', 'active')
			->where('departments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('arc.date >=', $args['period_from'])
			->where('arc.date <=', $args['period_to'])
			->group_by('department_name')
			->order_by('total', 'desc');
		
		if(!empty($args['abnormal_type'])) {
			$this->db->where('arc.abnormal_type', $args['abnormal_type']);
		}
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
		
		$data = $this->db->get()->result_array();
			
		$graph_data['department'] = array();
		foreach($data as $current_data) {
			$graph_data['department'][$current_data['department_name']] = $current_data['total'];
		}
		
		// Reason
		$this->db
			->select('abnormal_type_name, COUNT(*) AS total')
			->from('arc')
			->join('abnormal_types', 'abnormal_types.id = arc.abnormal_type', 'left')
			->where('arc.data_status', 'active')
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('abnormal_types.data_status', 'active')
			->where('abnormal_types.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('arc.date >=', $args['period_from'])
			->where('arc.date <=', $args['period_to'])
			->group_by('abnormal_type_name')
			->order_by('total', 'desc');
			
		if(!empty($args['abnormal_type'])) {
			$this->db->where('arc.abnormal_type', $args['abnormal_type']);
		}
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
			
		$data = $this->db->get()->result_array();
			
		$graph_data['reason'] = array();
		foreach($data as $current_data) {
			$graph_data['reason'][$current_data['abnormal_type_name']] = $current_data['total'];
		}
		
		// Customer
		$this->db
			->select('customer_name, COUNT(*) AS total')
			->from('arc')
			->join('customers', 'customers.id = arc.customer', 'left')
			->where('arc.data_status', 'active')
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('customers.data_status', 'active')
			->where('customers.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('arc.date >=', $args['period_from'])
			->where('arc.date <=', $args['period_to'])
			->group_by('customer_name')
			->order_by('total', 'desc');
		
		if(!empty($args['abnormal_type'])) {
			$this->db->where('arc.abnormal_type', $args['abnormal_type']);
		}
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
		
		$data = $this->db->get()->result_array();
			
		$graph_data['customer'] = array();
		foreach($data as $current_data) {
			$graph_data['customer'][$current_data['customer_name']] = $current_data['total'];
		}
		
		return $graph_data;
	}
	
	public function get_monthly_ac_data($args) {
		$data = array();
		$year = $args['year'];
		
		$initial_monthly_data = array();
		for($i=1; $i<=12; $i++) {
			if($year >= date('Y') && $i > date('n')) {
				// Set data as null for future months
				$initial_monthly_data[$i] = null;
			}
			else {
				$initial_monthly_data[$i] = 0;
			}
		}
		$initial_monthly_data['total'] = 0;
		
		$abnormal_types = $this->model_db_crud->get_several_data('abnormal_type');
		
		$data = array();
		foreach($abnormal_types as $abnormal_type) {
			$data[$abnormal_type['id']] = array(
				'abnormal_type_name' => $abnormal_type['abnormal_type_name'],
				'monthly_count' => $initial_monthly_data,
			);
		}
		
		$this->db
			->select('abnormal_type, MONTH(datetime) AS month, COUNT(*) AS count')
			->from('arc')
			->where('arc.data_status', DATA_ACTIVE)
			->where('arc.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('YEAR(datetime)', $year)
			->group_by('abnormal_type, month');
			
		if(!empty($args['carrier'])) {
			$this->db->where('arc.carrier', $args['carrier']);
		}
		if(!empty($args['customer'])) {
			$this->db->where('arc.customer', $args['customer']);
		}
		if(!empty($args['department'])) {
			$this->db->where('arc.department', $args['department']);
		}
		if(!empty($args['facility'])) {
			$this->db->where('arc.facility', $args['facility']);
		}
		
		$raw_count_data = $this->db->get()->result_array();
			
		foreach($raw_count_data as $count_data) {
			$abnormal_type_id = !empty($count_data['abnormal_type']) ? $count_data['abnormal_type'] : 0;
			
			if($abnormal_type_id == 0 && !isset($data[0])) {
				$data[0] = array(
					'abnormal_type_name' => '(not set)',
					'monthly_count' => $initial_monthly_data,
				);
			}
			
			$data[$abnormal_type_id]['monthly_count'][$count_data['month']] = $count_data['count'];
			$data[$abnormal_type_id]['monthly_count']['total'] += $count_data['count'];
		}
		
		return $data;
	}
}