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
		
		switch($table_name) {
			case 'arc':
				$result = $this->update_dependent_arc_data($data, $action_mode, $changed_fields);
				break;
			case 'attendance':
				$result = $this->update_dependent_attendance_data($data, $action_mode, $changed_fields);
				break;
		}
		
		return $result;
	}
	
	public function update_dependent_arc_data($arc, $action_mode, $changed_fields) {
		$result = true;
		
		if($action_mode == 'add' || $action_mode == 'edit') {
			$datetime = $arc['date'] . ' ' . $arc['time'];
			
			$this->model_db_crud->edit_item(
				'arc',
				$arc['id'],
				array(
					'datetime' => $datetime
				),
				array('update_timestamp'=>false, 'skip_check_data_dependency'=>true, 'changed_by_system'=>true)
			);
		}
		
		return $result;
	}
	
	public function update_dependent_attendance_data($attendance, $action_mode, $changed_fields) {
		$result = true;

		if($action_mode == 'add') {
			$this->model_db_crud->add_item(
				'evolution_points_logs',
				array(
					'employee' => $attendance['employee'],
					'datetime' => $attendance['date'],
					'reason_type' => 'attendance',
					'ref_attendance' => $attendance['id'],
					'evolution_points' => ($attendance['devolution_points'] * -1),
				),
				array('changed_by_system'=>true)
			);
		}
		else if($action_mode == 'edit') {
			$evolution_points_logs = $this->model_db_crud->get_several_data('evolution_points_log', array('ref_attendance' => $attendance['id']));
			
			if(!empty($evolution_points_logs)) {
				$this->model_db_crud->edit_item(
					'evolution_points_logs',
					$evolution_points_logs[0]['id'],
					array(
						'employee' => $attendance['employee'],
						'datetime' => $attendance['date'],
						'reason_type' => 'attendance',
						'ref_attendance' => $attendance['id'],
						'evolution_points' => ($attendance['devolution_points'] * -1),
					),
					array('changed_by_system'=>true)
				);
			}
			else {
				$this->model_db_crud->add_item(
					'evolution_points_logs',
					array(
						'employee' => $attendance['employee'],
						'datetime' => $attendance['date'],
						'reason_type' => 'attendance',
						'ref_attendance' => $attendance['id'],
						'evolution_points' => ($attendance['devolution_points'] * -1),
					),
					array('changed_by_system'=>true)
				);
			}
		}
		else if($action_mode == 'delete') {
			$evolution_points_logs = $this->model_db_crud->get_several_data('evolution_points_log', array('ref_attendance' => $attendance['id']));
			
			if(!empty($evolution_points_logs)) {
				$this->model_db_crud->delete_item(
					'evolution_points_logs',
					$evolution_points_logs[0]['id'],
					array('changed_by_system'=>true)
				);
			}
		}
		
		return $result;
	}
}