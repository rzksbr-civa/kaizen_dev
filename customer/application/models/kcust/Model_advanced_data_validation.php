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
		
		return $result;
	}
	
	public function validate_data_deletion($entity_name, $data_id) {
		$result = array();
		$result['result'] = true;
		$result['error_message'] = '';
		
		return $result;
	}
}