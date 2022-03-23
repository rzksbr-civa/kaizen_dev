<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_preshift_check extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function get_checked_in_employees($date = '') {
		if(empty($date)) {
			$date = date('Y-m-d');
		}
		
		$raw_checked_in_employees = $this->db
			->select('employee, is_flagged')
			->from('preshift_checks')
			->where('data_status', 'active')
			->where('date', $date)
			->get()->result_array();
		
		$checked_in_employees = array();
		foreach($raw_checked_in_employees as $current_data) {
			$checked_in_employees[$current_data['employee']] = array('is_flagged' => $current_data['is_flagged']);
		}
		
		return $checked_in_employees;
	}
	
	public function checkin($data) {
		$is_flagged = false;
		foreach($data['responses'] as $question_id => $response) {
			if($response == 'No') {
				$is_flagged = true;
			}
		}
		
		$now = date('Y-m-d H:i:s');
		
		$this_preshift_check = array(
			'employee' => $data['employee_id'],
			'facility' => $data['facility_id'],
			'date' => $data['date'],
			'checkin_time' => $data['time'],
			'is_flagged' => $is_flagged,
			'responses' => json_encode($data['responses']),
			'data_status' => 'active',
			'data_group' => 1,
			'created_time' => $now,
			'created_user' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id'),
			'last_modified_time' => $now,
			'last_modified_user' => $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id')
		);
		
		$existing_preshift_check = $this->db
			->select('id')
			->from('preshift_checks')
			->where('data_status', 'active')
			->where('employee', $data['employee_id'])
			->where('facility', $data['facility_id'])
			->where('date', $data['date'])
			->get()->result_array();
		
		if(empty($existing_preshift_check)) {
			// Create new
			$this->db->insert('preshift_checks', $this_preshift_check);
		}
		else {
			// Update
			unset($this_preshift_check['created_time']);
			unset($this_preshift_check['created_user']);
			
			$this->db
				->where('id', $existing_preshift_check[0]['id'])
				->update('preshift_checks', $this_preshift_check);
		}
		
		$result = array('success' => true, 'is_flagged' => $is_flagged);
		return $result;
	}
}