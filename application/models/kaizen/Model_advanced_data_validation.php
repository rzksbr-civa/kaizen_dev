<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_advanced_data_validation extends CI_Model {
	public function __construct()	{
		$this->load->database(); 
		$this->load->model('model_db_crud');
	}
	
	// A function to validate data before added/edited
	// $action_mode : add / edit
	public function validate_data($entity_name, $action_mode, $data, $data_id) {
		$result = array();
		
		switch($entity_name) {
			case 'takt_data':
				$result = $this->validate_takt_data($action_mode, $data, $data_id);
				break;
			default:
				
		}
		
		return $result;
	}
	
	public function validate_data_deletion($entity_name, $data_id) {
		$result = array();
		$result['result'] = true;
		$result['error_message'] = '';
		
		return $result;
	}
	
	public function validate_takt_data($action_mode, $data, $data_id = 0) {
		$result['success'] = true;
		$result['data_valid'] = true;
		$result['error'] = array();
		
		if($action_mode == 'add') {
			$existing_takt_data = $this->model_db_crud->get_several_data('takt_data', array('facility' => $data['facility'], 'date' => $data['date']));
			
			if(!empty($existing_takt_data)) {
				$result['data_valid'] = false;
				$error_detail = array(
					'error_field'   => 'date',
					'error_message' => 'Data for this date already exist.'
				);
				$result['error'][] = $error_detail;
			}
		}
		
		return $result;
	}
}