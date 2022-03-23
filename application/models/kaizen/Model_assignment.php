<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_assignment extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function get_employee_assignment_data($args) {
		$result = array();
		
		$date = isset($args['date']) ? $args['date'] : date('Y-m-d');
		$facility = isset($args['facility']) ? $args['facility'] : null;
		$department = isset($args['department']) ? $args['department'] : null;
		$employee_shift = isset($args['employee_shift']) ? $args['employee_shift'] : null;

		// Get the list of ACTIVE employees
		$this->db
			->select('id, employee_name')
			->from('employees')
			->where('employees.data_status', 'active')
			->where('employees.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('employees.is_active', true)
			->where('employees.facility IS NOT NULL', null, false)
			->order_by('employee_name', 'asc');
		
		if(!empty($facility)) {
			$this->db->where('facility', $facility);
		}
		
		if(!empty($department)) {
			$this->db->where('department', $department);
		}
		
		if(!empty($employee_shift)) {
			$this->db->where_in('employee_shift', $employee_shift);
		}
		
		$employees = $this->db->get()->result_array();
		
		foreach($employees as $employee) {
			$result[$employee['id']] = array(
				'employee_id' => $employee['id'],
				'employee_name' => $employee['employee_name'],
				'assignments' => array(
					1 => array(),
					2 => array(),
					3 => array(),
					4 => array()
				)
			);
		}
		
		$this->db
			->select('assignments.*, assignment_types.assignment_type_name')
			->from('assignments')
			->join('assignment_types', 'assignment_types.id = assignments.assignment_type', 'left')
			->join('employees', 'employees.id = assignments.employee')
			->where('assignments.data_status', 'active')
			->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('assignments.date', $date)
			->where('employees.facility IS NOT NULL', null, false)
			->where('employees.is_active', true);
		
		if(!empty($facility)) {
			$this->db->where('assignments.facility', $facility);
		}
		
		if(!empty($department)) {
			$this->db->where('employees.department', $department);
		}
		
		if(!empty($employee_shift)) {
			$this->db->where_in('employees.employee_shift', $employee_shift);
		}
		
		$assignments = $this->db->get()->result_array();
			
		foreach($assignments as $assignment) {
			$result[$assignment['employee']]['assignments'][$assignment['shift']][] = array('id' => $assignment['assignment_type'], 'name' => $assignment['assignment_type_name']);
		}
		
		return $result;
	}
	
	public function assign_employee($args) {
		$result = array('success' => false);
		
		$employee_id = $args['employee_id'];
		$facility = $this->model_db_crud->get_specific_data_field('employee', $employee_id, 'facility');
		$date = $args['date'];
		$shift = $args['shift'];
		$assignment_type = $args['assignment_type'];
		$double_assign_means_unassign = isset($args['double_assign_means_unassign']) ? $args['double_assign_means_unassign'] : true;
		
		$block_time = $this->model_db_crud->get_specific_data('block_time', $shift);

		$this->db->trans_start();
		
		// Check if there's any record related to this date & block time in evolution_points_logs
		
		$evolution_points_records = $this->db
			->select('id, base_evolution_points, is_counted')
			->from('evolution_points_logs')
			->where('evolution_points_logs.data_status', 'active')
			->where('evolution_points_logs.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('employee', $employee_id)
			->like('datetime', $date)
			->where('block_time', $shift)
			->where('reason_type', 'work')
			->get()->result_array();
		
		$evolution_points_record = !empty($evolution_points_records) ? $evolution_points_records[0] : null;
		
		$assignment_type_for_evolution_points_log = $assignment_type;
		$assignment_type_data = isset($assignment_type) ? $this->model_db_crud->get_specific_data('assignment_type', $assignment_type) : null;
		$evolution_point_factor = isset($assignment_type_data['evolution_point_factor'])  ? $assignment_type_data['evolution_point_factor'] : 1;
		
		if(empty($assignment_type)) {
			// Empty assignment type means unassign every assignments...
			$this->db
				->set('data_status', 'deleted')
				->set('last_modified_time', date('Y-m-d H:i:s'))
				->set('last_modified_user', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id'))
				->where('assignments.data_status', 'active')
				->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('assignments.facility', $facility)
				->where('assignments.employee', $employee_id)
				->where('assignments.date', $date)
				->where('assignments.shift', $shift)
				->update('assignments');
			
			$assignment_type_for_evolution_points_log = null;
			$evolution_point_factor = 1;
			
			$result['success'] = true;
		}
		else {
			$existing_assignments = $this->db
				->select('assignments.id')
				->from('assignments')
				->where('assignments.data_status', 'active')
				->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('assignments.facility', $facility)
				->where('assignments.employee', $employee_id)
				->where('assignments.date', $date)
				->where('assignments.shift', $shift)
				->where('assignments.assignment_type', $assignment_type)
				->get()->result_array();
			
			if(empty($existing_assignments)) {
				// Delete all other assignments first...
				$this->db
					->set('data_status', 'deleted')
					->set('last_modified_time', date('Y-m-d H:i:s'))
					->set('last_modified_user', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id'))
					->where('assignments.data_status', 'active')
					->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
					->where('assignments.facility', $facility)
					->where('assignments.employee', $employee_id)
					->where('assignments.date', $date)
					->where('assignments.shift', $shift)
					->update('assignments');
				
				$add_assignment = $this->model_db_crud->add_item(
					'assignments',
					array(
						'facility' => $facility,
						'employee' => $employee_id,
						'date' => $date,
						'shift' => $shift,
						'shift_start_time' => $block_time['start_time'],
						'shift_end_time' => $block_time['end_time'],
						'assignment_type' => $assignment_type
					)
				);
				
				$result['success'] = $add_assignment['success'];
			}
			else {
				if($double_assign_means_unassign) {
					$delete_assignment = $this->model_db_crud->delete_item(
						'assignments',
						$existing_assignments[0]['id']
					);
					
					$assignment_type_for_evolution_points_log = null;
					$evolution_point_factor = 1;
					
					$result['success'] = $delete_assignment['success'];
				}
				else {
					// Do nothing
					$result['success'] = true;
				}
			}	
		}
		
		// Set the evolution points factor in evolution_points_logs
		if(isset($evolution_points_record)) {
			$this->db
				->set('ref_assignment_type', $assignment_type_for_evolution_points_log)
				->set('evolution_point_factor', $evolution_point_factor)
				->where('evolution_points_logs.data_status', DATA_ACTIVE)
				->where('evolution_points_logs.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('evolution_points_logs.employee', $employee_id)
				->where('evolution_points_logs.reason_type', 'work')
				->like('evolution_points_logs.datetime', $date)
				->where('evolution_points_logs.block_time', $shift)
				->update('evolution_points_logs');
			
			$this->db
				->set('evolution_points', 'base_evolution_points * evolution_point_factor * is_counted', false)
				->where('evolution_points_logs.data_status', DATA_ACTIVE)
				->where('evolution_points_logs.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
				->where('evolution_points_logs.employee', $employee_id)
				->where('evolution_points_logs.reason_type', 'work')
				->like('evolution_points_logs.datetime', $date)
				->where('evolution_points_logs.block_time', $shift)
				->update('evolution_points_logs');
		}
		
		$this->db->trans_complete();
		
		$current_assignments = $this->db
			->select('assignments.assignment_type, assignment_types.assignment_type_name')
			->from('assignments')
			->join('assignment_types', 'assignment_types.id = assignments.assignment_type', 'left')
			->where('assignments.data_status', 'active')
			->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('assignments.facility', $facility)
			->where('assignments.employee', $employee_id)
			->where('assignments.date', $date)
			->where('assignments.shift', $shift)
			->get()->result_array();
			
		$result['current_assignments_text'] = implode(', ', array_column($current_assignments, 'assignment_type_name'));

		return $result;
	}
	
	public function get_employee_assignment_count_by_assignment_type($args) {
		$result = array();
		
		$date = !empty($args['date']) ? $args['date'] : date('Y-m-d');
		$facility = $args['facility'];
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $facility);
		}
		
		$assignment_types = $this->model_db_crud->get_several_data('assignment_type');
		
		$assignment_type_name_by_id = array();
		
		foreach($assignment_types as $assignment_type) {
			$assignment_type_name_by_id[$assignment_type['id']] = $assignment_type['assignment_type_name'];
			$result[$assignment_type['assignment_type_name']] = array(
				1 => 0,
				2 => 0,
				3 => 0,
				4 => 0
			);
		}
		
		$production_db = $this->load->database('prod', TRUE);
		
		$block_times = $this->model_db_crud->get_several_data('block_time');
		
		foreach($block_times as $block_time) {
			$production_db
				->select('action_log.assignment_type, COUNT(DISTINCT(user_id)) AS employee_count')
				->from('action_log')
				->where('action_log.started_at >=', $date.' '.$block_time['start_time'])
				->where('action_log.started_at <', $date.' '.$block_time['end_time'])
				->where('assignment_type >', 0)
				->group_by('assignment_type')
				->order_by('assignment_type');
				
			if(!empty($facility_data['stock_id'])) {
				$production_db->where('stock_id', $facility_data['stock_id']);
			}
			
			$assignment_count = $production_db->get()->result_array();
			
			foreach($assignment_count as $item) {
				$result[$assignment_type_name_by_id[$item['assignment_type']]][$block_time['id']] = $item['employee_count'];
			}
		}

		return $result;
	}

	public function get_takt_data($args) {
		$result = array();
		
		$date = !empty($args['date']) ? $args['date'] : date('Y-m-d');
		$facility = $args['facility'];

		$takt_data = $this->model_db_crud->get_several_data('takt_data', array('facility' => $facility, 'date' => $date));
		
		return !empty($takt_data) ? $takt_data[0] : null;
	}
	
	public function get_current_employee_assignment($args) {
		$result = array();
		
		$employee_name = $args['employee_name'];
		$date = !empty($args['date']) ? $args['date'] : date('Y-m-d');
		
		// Get employee ID
		$employees = $this->model_db_crud->get_several_data('employee', array('employee_name' => $employee_name));
		
		if(empty($employees)) {
			$result['success'] = false;
			$result['error_message'] = 'Employee not found.';
			return $result;
		}
		$employee_id = $employees[0]['id'];
		
		// Get current block times
		$block_times = $this->model_db_crud->get_several_data('block_time');
		
		$result['assignments'] = array();
		foreach($block_times as $block_time) {
			$result['assignments'][$block_time['id']] = array(
				'assignment_id' => null,
				'assignment_type' => null
			);
		}
		
		// Get current employee assignment
		$assignments = $this->db
			->select('assignments.id, assignments.shift, assignments.assignment_type, assignment_types.assignment_type_name')
			->from('assignments')
			->join('assignment_types', 'assignment_types.id = assignments.assignment_type', 'left')
			->where('assignments.data_status', 'active')
			->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('assignments.date', $date)
			->where('assignments.employee', $employee_id)
			->get()->result_array();
		
		foreach($assignments as $assignment) {
			$result['assignments'][$assignment['shift']] = array(
				'assignment_id' => $assignment['id'],
				'assignment_type' => $assignment['assignment_type']
			);
		}
		
		$result['success'] = true;
		
		return $result;
	}
	
	public function edit_employee_assignment($args) {
		$result = array();
		
		$employee_name = $args['employee_name'];
		$date = !empty($args['date']) ? $args['date'] : date('Y-m-d');
		
		// Get employee ID
		$employees = $this->model_db_crud->get_several_data('employee', array('employee_name' => $employee_name));
		
		if(empty($employees)) {
			$result['success'] = false;
			$result['error_message'] = 'Employee not found.';
			return $result;
		}
		$employee_id = $employees[0]['id'];
		
		foreach($args['assignments'] as $block => $assignment_type) {
			$this->assign_employee(
				array(
					'employee_id' => $employee_id,
					'date' => $args['date'],
					'shift' => $block,
					'assignment_type' => $assignment_type,
					'double_assign_means_unassign' => false
				)
			);
		}
		
		$current_assignments = $this->db
			->select('assignments.assignment_type, assignment_types.assignment_type_name')
			->from('assignments')
			->join('assignment_types', 'assignment_types.id = assignments.assignment_type', 'left')
			->where('assignments.data_status', 'active')
			->where('assignments.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('assignments.employee', $employee_id)
			->where('assignments.date', $date)
			->order_by('assignments.shift', 'asc')
			->get()->result_array();
			
		$result['current_assignments_text'] = implode(', ', array_column($current_assignments, 'assignment_type_name'));
		
		$result['success'] = true;
		
		return $result;
	}
	
	public function edit_takt_data($args) {
		$result = array();
		
		$facility = $args['facility'];
		$date = $args['date'];
		
		// Get takt data ID
		$takt_data_tmp = $this->model_db_crud->get_several_data('takt_data', array('facility' => $facility, 'date' => $date));
		
		if(empty($takt_data_tmp)) {
			$result['success'] = false;
			$result['error_message'] = 'Takt data not found.';
			return $result;
		}
		$takt_data_id = $takt_data_tmp[0]['id'];

		$edit_takt_data = $this->model_db_crud->edit_item(
			'takt_data',
			$takt_data_id,
			$args
		);
		
		$result['success'] = $edit_takt_data['success'];
		
		return $result;
	}
	
	public function get_employee_evolution_points_breakdown_widget_data($employee_id) {
		$result = array(
			'positive_evolution_points' => array(),
			'negative_evolution_points' => 0,
			'lifetime_evolution_points' => 0
		);
		
		$result['positive_evolution_points'] = $this->db
			->select('assignment_types.assignment_type_name, ROUND(SUM(evolution_points)) AS evolution_points')
			->from('evolution_points_logs')
			->join('assignment_types', 'assignment_types.id = evolution_points_logs.ref_assignment_type', 'left')
			->where('evolution_points_logs.data_status', 'active')
			->where('evolution_points_logs.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('evolution_points_logs.employee', $employee_id)
			->where('evolution_points_logs.reason_type', 'work')
			->group_by('evolution_points_logs.ref_assignment_type')
			->order_by('assignment_types.assignment_type_name', 'asc')
			->get()->result_array();
			
		$devolution_points_data = $this->db
			->select('ROUND(SUM(evolution_points)) AS devolution_points')
			->from('evolution_points_logs')
			->where('evolution_points_logs.data_status', 'active')
			->where('evolution_points_logs.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('evolution_points_logs.employee', $employee_id)
			->where('evolution_points_logs.reason_type', 'attendance')
			->get()->result_array();
		
		$result['negative_evolution_points'] = !empty($devolution_points_data[0]['devolution_points']) ? $devolution_points_data[0]['devolution_points'] : 0;
		
		$lifetime_evolution_points_data = $this->db
			->select('ROUND(SUM(evolution_points)) AS evolution_points')
			->from('evolution_points_logs')
			->where('evolution_points_logs.data_status', 'active')
			->where('evolution_points_logs.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('evolution_points_logs.employee', $employee_id)
			->get()->result_array();
		
		$result['lifetime_evolution_points'] = !empty($lifetime_evolution_points_data[0]['evolution_points']) ? $lifetime_evolution_points_data[0]['evolution_points'] : 0;
		
		return $result;
	}
}