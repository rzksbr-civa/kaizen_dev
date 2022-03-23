<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_db_dependencies extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	// A function to update fields in datatabse that is required to be updated by system
	//  $action_mode : add / edit
	public function update_dependent_data($table_name, $id, $action_mode, $changed_fields = array()) {
		$result = true;
		
		$entity_name = get_entity_name_by_table_name($table_name);
		if(!empty($entity_name)) {
			if($action_mode === 'delete') {
				$data = $this->model_db_crud->get_specific_deleted_data($entity_name, $id);
			}
			else {
				$data = $this->model_db_crud->get_specific_data($entity_name, $id);
			}
		}
		
		return $result;
	}
}