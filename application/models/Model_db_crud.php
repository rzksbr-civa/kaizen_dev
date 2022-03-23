<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_DB_CRUD extends CI_Model {
	public function __construct()	{
		$this->load->database();
	}
	
	// Data for API called in view list
	public function get_data_list($entity_name, $filter = array()) {
		if(!entity_exists($entity_name)) {
			return null;
		}
		
		return $this->get_displayed_fields_data($entity_name, null, $filter);
	}
	
	// Info data for view detail
	// $fields = to return only specific fields. Empty array means return all...
	public function get_view_detail_info_data($entity_name, $id, $specific_fields = array()) {
		if(!entity_exists($entity_name)) {
			return null;
		}

		$entity_data = get_entity($entity_name);
		$table_name = $entity_data['table_name'];
		
		$result = $this->get_displayed_fields_data($entity_name, $id, null, $specific_fields);

		// Filter the result to only specific fields we want, only if specific_fields is set.
		/*if(!empty($specific_fields)) {
			foreach($result as $field_name => $field_value) {
				if(!in_array($field_name, $specific_fields)) {
					unset($result[$field_name]);
				}
			}
		}*/

		return $result;
	}
	
	public function get_option_list($entity_name, $data_order = '') {
		if(!entity_exists($entity_name)) {
			return null;
		}
		
		$entity_data = get_entity($entity_name);
		$table_name = $entity_data['table_name'];
		
		$select_fields = array($entity_data['id_field'], $entity_data['name_field']);
		
		$this->db->select(implode(',', $select_fields));
		$this->db->from($table_name);
		
		// Show only the active/not deleted data
		$this->db->where($table_name . '.data_status', DATA_ACTIVE);
		
		// Filter data group to user's group or 0 (global group)
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$this->db->order_by($data_order);
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		$result = array();
		
		foreach($query_result as $current_data) {
			$option_id = $current_data[$entity_data['id_field']];
			$option_label = $current_data[$entity_data['name_field']];
			$result[] = array(
				'id' => $option_id,
				'label' => $option_label
			);
		}
		
		return $result;
	}
	
	// Add item (api/add_item)
	// $args : 
	//   - add_timestamp => true / false (default:true)
	//   - skip_check_data_dependency => true / false (default:false)
	public function add_item($table_name, $data, $args = array()) {
		$result = array();
		
		$add_timestamp = (array_key_exists('add_timestamp', $args) && $args['add_timestamp'] === false) ? false : true;
		$skip_check_data_dependency = (array_key_exists('skip_check_data_dependency', $args) && $args['skip_check_data_dependency'] === true) ? true : false;
		
		if($table_name === 'chchdb_edit_history') {
			$skip_check_data_dependency = true;
		}
		
		$data['data_status'] = DATA_ACTIVE;
		$data['data_group'] = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		
		if($add_timestamp) {
			$now = date('Y-m-d H:i:s');
			$data['created_time'] = $now;
			$data['created_user'] = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
			$data['last_modified_time'] = $data['created_time'];
			$data['last_modified_user'] = $data['created_user'];
		}
		
		$this->db->trans_start();
		
		$this->db->insert($table_name, $data);
		$insert_id = $this->db->insert_id();
		
		if($insert_id <> null) {
			$result['success'] = true;
			$result['insert_id'] = $insert_id;
			
			if(!$skip_check_data_dependency) {
				$changed_fields = array();
				foreach($data as $field => $value) {
					$changed_fields[$field] = array(
						'old_data' => null,
						'new_data' => $value
					);
				}
				
				// Update dependent data
				$this->load->model(PROJECT_CODE.'/model_db_dependencies');
				$this->model_db_dependencies->update_dependent_data($table_name, $insert_id, 'add', $changed_fields);
			}
		}
		else {
			$result['success'] = false;
		}
		
		$this->db->trans_complete();
		
		return $result;
	}
	
	// Edit item (api/edit_item)
	// $args : 
	//   - update_timestamp => true / false (default:true)
	//   - skip_check_data_dependency => true / false (default:false)
	//   - skip_update_edit_history (default:false)
	public function edit_item($table_name, $id, $data, $args = array()) {
		$result = array();
		
		$this->db->trans_start();
		
		$update_timestamp = (array_key_exists('update_timestamp', $args) && $args['update_timestamp'] === false) ? false : true;
		$skip_check_data_dependency = (array_key_exists('skip_check_data_dependency', $args) && $args['skip_check_data_dependency'] === true) ? true : false;
		$skip_update_edit_history = (array_key_exists('skip_update_edit_history', $args) && $args['skip_update_edit_history'] === true) ? true : false;
		$changed_by_system = (array_key_exists('changed_by_system', $args) && $args['changed_by_system'] === true) ? true : false;
		
		$entity_name = get_entity_name_by_table_name($table_name);
		if(empty($entity_name)) {
			$entity_name = 'table__'.$table_name;
		}
		
		if($update_timestamp) {
			$now = date('Y-m-d H:i:s');
			$data['last_modified_time'] = $now;
			$data['last_modified_user'] = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
		}
		
		// Get current data
		$query_result = $this->db->get_where($table_name, array('id' => $id))->result_array();
		
		// If data has been deleted, don't continue...
		if(empty($query_result)) {
			$result['success'] = false;
			return $result;
		}
		else {
			$current_data = $query_result[0];
		}

		$changed_field_data = array();
		foreach($data as $field_name => $new_data) {
			if($current_data[$field_name] !== $new_data) {
				if($new_data === null) {
					$this->db->set($field_name, 'NULL', false);
				}
				else {		
					$this->db->set($field_name, $new_data);
				}

				if($field_name <> 'last_modified_time' && $field_name <> 'last_modified_user') {
					$changed_field_data[] = array('entity_name' => $entity_name, 'ref_id' => $id, 'field_name' => $field_name, 'old_data' => $current_data[$field_name], 'new_data' => $new_data);
				}
			}
		}
		
		$this->db->where('id', $id);
		
		if(empty($changed_field_data)) {
			$this->db->reset_query();
			$result['success'] = true;
		}
		else {
			$result['success'] = $this->db->update($table_name);
		}
		
		if($changed_by_system || $skip_update_edit_history) {
			foreach($changed_field_data as $current_changed_field_data) {
				$current_changed_field_data['changed_by_system'] = true;
				$this->add_item('chchdb_edit_history', $current_changed_field_data);
			}
		}
		else {
			// Update edit history
			foreach($changed_field_data as $current_changed_field_data) {
				$this->add_item('chchdb_edit_history', $current_changed_field_data);
			}
		}
		
		if(!$skip_check_data_dependency) {
			$changed_fields = array();
			foreach($changed_field_data as $current_changed_field_data) {
				$changed_fields[$current_changed_field_data['field_name']] = array(
					'old_data' => $current_changed_field_data['old_data'],
					'new_data' => $current_changed_field_data['new_data']
				);
			}
			
			// Update dependent data
			$this->load->model(PROJECT_CODE.'/model_db_dependencies');
			$this->model_db_dependencies->update_dependent_data($table_name, $id, 'edit', $changed_fields);
		}
		
		$this->db->trans_complete();
		
		return $result;
	}
	
	// $args : 
	//   - update_timestamp => true / false (default:true)
	//   - skip_check_data_dependency => true / false (default:false)
	public function delete_item($table_name, $id, $args = array()) {
		$result = array();
		
		$this->db->trans_start();
		
		$update_timestamp = (array_key_exists('update_timestamp', $args) && $args['update_timestamp'] === false) ? false : true;
		$skip_check_data_dependency = (array_key_exists('skip_check_data_dependency', $args) && $args['skip_check_data_dependency'] === true) ? true : false;
		$changed_by_system = (array_key_exists('changed_by_system', $args) && $args['changed_by_system'] === true) ? true : false;
		
		$entity_name = get_entity_name_by_table_name($table_name);
		if(empty($entity_name)) {
			$entity_name = 'table__'.$table_name;
		}
		
		// Get current data
		$query_result = $this->db->get_where($table_name, array('id' => $id))->result_array();
		
		// If data has been deleted, don't continue...
		if(empty($query_result)) {
			$result['success'] = false;
			return $result;
		}
		else {
			$current_data = $query_result[0];
		}
		
		$this->db->set($table_name . '.data_status', DATA_DELETED);
		
		if($update_timestamp) {
			$now = date('Y-m-d H:i:s');
			$this->db->set($table_name . '.last_modified_time', $now);
			$this->db->set($table_name . '.last_modified_user', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id'));
		}
		
		$this->db->where('id', $id);
		
		$result['success'] = $this->db->update($table_name);
		
		if($result['success']) {
			$changed_field_data = array('entity_name' => $entity_name, 'ref_id' => $id, 'field_name' => 'data_status', 'old_data' => DATA_ACTIVE, 'new_data' => DATA_DELETED);
			
			if($changed_by_system) {
				$changed_field_data['changed_by_system'] = true;
			}
			
			$this->add_item('chchdb_edit_history', $changed_field_data);
			
			if(!$skip_check_data_dependency) {
				// Update dependent data
				$this->load->model(PROJECT_CODE.'/model_db_dependencies');
				$this->model_db_dependencies->update_dependent_data($table_name, $id, 'delete');
			}
		}
		
		$this->db->trans_complete();
		
		return $result;
	}
	
	// Check if data exist.
	public function data_exist($table_name, $field_name, $field_value, $excluded_id = null) {
		$this->db->select($field_name)->from($table_name);
		$this->db->where($field_name, $field_value);
		
		// Show only the active/not deleted data
		$this->db->where('data_status', DATA_ACTIVE);
		
		// Excluded ID
		if($excluded_id <> null) {
			$this->db->where('id <> '.$excluded_id);
		}
		
		// Filter data group to user's group or 0 (global group)
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		if(empty($query_result)) {
			// Data not exist
			return false;
		}
		else {
			// Data exist
			return true;
		}
	}
	
	// Get specific data from a table
	// filter_args e.g. array('order_by' => 'test')
	public function get_specific_data($entity_name, $id, $filter_args = array(), $select_fields = array()) {
		$entity_data = get_entity($entity_name);
		$table_name = $entity_data['table_name'];
		
		if(empty($select_fields)) {
			$this->db->select('*');
		}
		else {
			$this->db->select($select_fields);
		}
		$this->db->from($table_name);

		if(!empty($id)) {
			$this->db->where('id', $id);
		}
		
		if(!empty($filter_args)) {
			foreach($filter_args as $filter_operation => $filter_value) {
				switch($filter_operation) {
					case 'order_by':
						if(!is_array($filter_value)) {
							$this->db->order_by($filter_value);
						}
						else {
							$this->db->order_by($filter_value[0], $filter_value[1]);
						}
						break;
					case 'limit':
						if(!is_array($filter_value)) {
							$this->db->limit($filter_value);
						}
						else {
							$this->db->limit($filter_value[0], $filter_value[1]);
						}
						break;
					case 'where':
						foreach($filter_value as $where_filter) {
							if(!is_array($where_filter)) {
								$this->db->where($where_filter);
							}
							else {
								$this->db->where($where_filter[0], $where_filter[1]);
							}
						}
					default:
				}
			}
		}
		
		// Show only the active/not deleted data
		$this->db->where($table_name . '.data_status', 'active');
		
		// Filter data group to user's group or 0 (global group)
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		if(empty($query_result)) {
			return null;
		}
		else {
			return $query_result[0];
		}
	}
	
	// Get specific deleted data from a table
	// filter_args e.g. array('order_by' => 'test')
	public function get_specific_deleted_data($entity_name, $id, $filter_args = array()) {
		$entity_data = get_entity($entity_name);
		$table_name = $entity_data['table_name'];
		
		$this->db->select('*');
		$this->db->from($table_name);

		if(!empty($id)) {
			$this->db->where('id', $id);
		}
		
		if(!empty($filter_args)) {
			foreach($filter_args as $filter_operation => $filter_value) {
				switch($filter_operation) {
					case 'order_by':
						if(!is_array($filter_value)) {
							$this->db->order_by($filter_value);
						}
						else {
							$this->db->order_by($filter_value[0], $filter_value[1]);
						}
						break;
					case 'limit':
						if(!is_array($filter_value)) {
							$this->db->limit($filter_value);
						}
						else {
							$this->db->limit($filter_value[0], $filter_value[1]);
						}
						break;
					case 'where':
						foreach($filter_value as $where_filter) {
							if(!is_array($where_filter)) {
								$this->db->where($where_filter);
							}
							else {
								$this->db->where($where_filter[0], $where_filter[1]);
							}
						}
					default:
				}
			}
		}
		
		// Show only the deleted data
		$this->db->where($table_name . '.data_status', DATA_DELETED);
		
		// Filter data group to user's group or 0 (global group)
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		if(empty($query_result)) {
			return null;
		}
		else {
			return $query_result[0];
		}
	}
	
	public function get_specific_data_field($entity_name, $id, $field_name, $filter_args = array()) {
		$data = $this->get_specific_data($entity_name, $id, $filter_args);
		
		if(!empty($data)) {
			return $data[$field_name];
		}
		else {
			return null;
		}
	}
	
	public function get_specific_deleted_data_field($entity_name, $id, $field_name, $filter_args = array()) {
		$data = $this->get_specific_deleted_data($entity_name, $id, $filter_args);
		
		if(!empty($data)) {
			return $data[$field_name];
		}
		else {
			return null;
		}
	}
	
	// Get several data from a table
	public function get_several_data($entity_name, $where_filters = array()) {
		$entity_data = get_entity($entity_name);
		$table_name = $entity_data['table_name'];
		
		$this->db->select('*');
		$this->db->from($table_name);

		if(is_array($where_filters)) {
			foreach($where_filters as $field_name => $value) {
				$this->db->where($field_name, $value);
			}
		}
		else {
			$this->db->where($where_filters);
		}
		
		// Show only the active/not deleted data
		$this->db->where($table_name . '.data_status', 'active');
		
		// Filter data group to user's group or 0 (global group)
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		return $query_result;
	}
	
	// Get data from one or more tables
	// $args = array('select' => array(...), 'join' => array(...), 'where' => array(...), 'order_by' => array(...), 'limit' => array(...))
	public function get_data($table_name, $args = array()) {
		$select_fields = !empty($args['select']) ? $args['select'] : '*';
		if(is_array($select_fields)) {
			foreach($select_fields as $select_field) {
				$this->db->select($select_field);
			}
		}
		else {
			$this->db->select($select_fields);
		}
		
		$this->db->from($table_name);
		
		if(!empty($args['join'])) {
			foreach($args['join'] as $join_table_name => $related_fields) {
				$this->db->join($join_table_name, $related_fields, 'left');
			}
		}
		
		if(!empty($args['normal_join'])) {
			foreach($args['normal_join'] as $join_table_name => $related_fields) {
				$this->db->join($join_table_name, $related_fields);
			}
		}
		
		if(!empty($args['where'])) {
			if(is_array($args['where'])) {
				foreach($args['where'] as $field_name => $value) {
					if(is_numeric($field_name)) {
						$this->db->where($value);
					}
					else {
						$this->db->where($field_name, $value);
					}
				}
			}
			else {
				$this->db->where($args['where']);
			}
		}
		
		if(!empty($args['where_in'])) {
			foreach($args['where_in'] as $field_name => $value) {
				$this->db->where_in($field_name, $value);
			}
		}
		
		if(!empty($args['group_by'])) {
			foreach($args['group_by'] as $field) {
				$this->db->group_by($field);
			}
		}
		
		if(!empty($args['having'])) {
			foreach($args['having'] as $field) {
				$this->db->having($field);
			}
		}
		
		if(!empty($args['order_by'])) {
			foreach($args['order_by'] as $field => $order) {
				$this->db->order_by($field, $order);
			}
		}
		
		if(!empty($args['limit'])) {
			if(!is_array($args['limit'])) {
				$this->db->limit($args['limit']);
			}
			else if(is_array($args['limit']) && count($args['limit']) == 1) {
				$this->db->limit($args['limit'][0]);
			}
			else if(is_array($args['limit']) && count($args['limit']) == 2) {
				$this->db->limit($args['limit'][0], $args['limit'][1]);
			}
		}
		
		// Show only the active/not deleted data
		$this->db->where($table_name . '.data_status', 'active');
		
		// Filter data group to user's group or 0 (global group)
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		// Return single data (the first data) if 'get_first_data' is set to true
		if(isset($args['get_first_data']) && count($query_result) > 0) {
			$query_result = $query_result[0];
		}
		
		return $query_result;
	}

	public function user_can($action_type, $entity_name, $id = 0, $args = array()) {
		// If view is restricted, all other action is restricted as well...
		if($action_type <> 'view') {
			$user_can_view = $this->user_can('view', $entity_name, $id);
			if(!$user_can_view) return false;
		}
		
		$users_capabilities_config = config_item('user_capabilities');
		
		$all_user_capabilities = $users_capabilities_config[USER_ROLE_ALL];
		
		$user_capabilities = $users_capabilities_config[$this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role')];
				
		// Check if this entity is part of the restricted entities...
		if(!empty($all_user_capabilities['restricted_entities'][$action_type]) && array_search($entity_name, $all_user_capabilities['restricted_entities'][$action_type]) !== false) {
			return false;
		}
		
		if(!empty($user_capabilities['restricted_entities'][$action_type]) && array_search($entity_name, $user_capabilities['restricted_entities'][$action_type]) !== false) {
			return false;
		}
		
		// Check field restrictions
		if(isset($args['field_name'])) {
			if(isset($all_user_capabilities['restricted_fields'][$action_type][$entity_name])) {
				if(in_array($args['field_name'], $all_user_capabilities['restricted_fields'][$action_type][$entity_name])) {
					return false;
				}
			}
			if(isset($user_capabilities['restricted_fields'][$action_type][$entity_name])) {
				if(in_array($args['field_name'], $user_capabilities['restricted_fields'][$action_type][$entity_name])) {
					return false;
				}
			}
			
			$restricted_field_found = false;
			if(!empty($all_user_capabilities['fields_shown_with_condition'][$action_type][$entity_name][$args['field_name']])) {
				$all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name] = $all_user_capabilities['fields_shown_with_condition'][$action_type][$entity_name][$args['field_name']];
				$restricted_field_found = true;
			}
			if(!empty($user_capabilities['fields_shown_with_condition'][$action_type][$entity_name][$args['field_name']])) {
				$user_capabilities['advanced_permission_criteria'][$action_type][$entity_name] = $user_capabilities['fields_shown_with_condition'][$action_type][$entity_name][$args['field_name']];
				$restricted_field_found = true;
			}
			if(!$restricted_field_found) return true;
		}
		
		if($action_type == 'add' && isset($args['parent_entity_name']) && isset($args['parent_data_id'])) {
			if(!empty($all_user_capabilities['advanced_permission_criteria']['add'][$entity_name][$args['parent_entity_name']])) {
				$parent_data = $this->get_specific_data($args['parent_entity_name'], $args['parent_data_id']);
				foreach($all_user_capabilities['advanced_permission_criteria']['add'][$entity_name][$args['parent_entity_name']] as $field => $value) {
					if(substr($field,-2) == '<>') {
						if($parent_data[explode(' ', $field)[0]] === $value) {
							return false;
						}
					}
					else if($parent_data[$field] <> $value) {
						return false;
					}
				}
			}
			
			if(!empty($user_capabilities['advanced_permission_criteria']['add'][$entity_name][$args['parent_entity_name']])) {
				$parent_data = $this->get_specific_data($args['parent_entity_name'], $args['parent_data_id']);
				foreach($user_capabilities['advanced_permission_criteria']['add'][$entity_name][$args['parent_entity_name']] as $field => $value) {
					if(substr($field,-2) == '<>') {
						if($parent_data[explode(' ', $field)[0]] === $value) {
							return false;
						}
					}
					else if($parent_data[$field] <> $value) {
						return false;
					}
				}
			}
		}
		
		// If we just check whether the entity is restricted or not, we don't need to check further...
		if($id === 0) return true;
		
		// Check for advanced permission criteria...
		if($action_type <> 'add') {
			if(!empty($all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name]) || !empty($user_capabilities['advanced_permission_criteria'][$action_type][$entity_name])) {
				$this->db->select('id');
				$this->db->from(get_entity_info($entity_name, 'table_name'));
				$this->db->where('data_status', DATA_ACTIVE);
				$this->db->where('(data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR data_group = 0)');
				$this->db->where('id', $id);
				
				if(!empty($all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name])) {
					foreach($all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name] as $field => $value) {
						if(is_numeric($field)) {
							$this->db->where($value);
						}
						else if(is_array($value)) {
							$this->db->group_start();
							$this->db->where($field, $value[0]);
							for($i=1; $i<count($value); $i++) {
								$this->db->or_where($field, $value[$i]);
							}
							$this->db->group_end();
						}
						else {
							$this->db->where($field, $value);
						}
					}
				}
				
				if(!empty($user_capabilities['advanced_permission_criteria'][$action_type][$entity_name])) {
					foreach($user_capabilities['advanced_permission_criteria'][$action_type][$entity_name] as $field => $value) {
						if(is_numeric($field)) {
							$this->db->where($value);
						}
						else if(is_array($value)) {
							$this->db->group_start();
							$this->db->where($field, $value[0]);
							for($i=1; $i<count($value); $i++) {
								$this->db->or_where($field, $value[$i]);
							}
							$this->db->group_end();
						}
						else {
							$this->db->where($field, $value);
						}
					}
				}
				
				$query = $this->db->get();
				$query_result = $query->result_array();
				if(empty($query_result)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	// Get the IDs in {$entity_name} table that user can {$action_type} (view/add/edit)
	public function get_permitted_data_id_list($action_type, $entity_name) {
		// If view is restricted, all other action is restricted as well...
		$view_permitted_data_id_list = array();
		if($action_type <> 'view') {
			$view_permitted_data_id_list = $this->get_permitted_data_id_list('view', $entity_name);
			if(empty($view_permitted_data_id_list)) return array();
		}
		
		$users_capabilities_config = config_item('user_capabilities');
		
		$all_user_capabilities = $users_capabilities_config[USER_ROLE_ALL];
		$user_capabilities = $users_capabilities_config[$this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role')];
				
		// Check if this entity is part of the restricted entities...
		if(!empty($all_user_capabilities['restricted_entities'][$action_type]) && array_search($entity_name, $all_user_capabilities['restricted_entities'][$action_type]) !== false) {
			return array();
		}
		
		if(!empty($user_capabilities['restricted_entities'][$action_type]) && array_search($entity_name, $user_capabilities['restricted_entities'][$action_type]) !== false) {
			return array();
		}
		
		$this->db->select('id');
		$this->db->from(get_entity_info($entity_name, 'table_name'));
		$this->db->where('data_status', DATA_ACTIVE);
		$this->db->where('(data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR data_group = 0)');
		
		// Check for advanced permission criteria...
		if(!empty($all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name]) || !empty($user_capabilities['advanced_permission_criteria'][$action_type][$entity_name])) {
			
			if(!empty($all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name])) {
				foreach($all_user_capabilities['advanced_permission_criteria'][$action_type][$entity_name] as $field => $value) {
					if(is_numeric($field)) {
						$this->db->where($value);
					}
					else if(is_array($value)) {
						$this->db->group_start();
						$this->db->where($field, $value[0]);
						for($i=1; $i<count($value); $i++) {
							$this->db->or_where($field, $value[$i]);
						}
						$this->db->group_end();
					}
					else {
						$this->db->where($field, $value);
					}
				}
			}
			
			if(!empty($user_capabilities['advanced_permission_criteria'][$action_type][$entity_name])) {
				foreach($user_capabilities['advanced_permission_criteria'][$action_type][$entity_name] as $field => $value) {
					if(is_numeric($field)) {
						$this->db->where($value);
					}
					else if(is_array($value)) {
						$this->db->group_start();
						$this->db->where($field, $value[0]);
						for($i=1; $i<count($value); $i++) {
							$this->db->or_where($field, $value[$i]);
						}
						$this->db->group_end();
					}
					else {
						$this->db->where($field, $value);
					}
				}
			}
		}
		
		$query = $this->db->get();
		$query_result = $query->result_array();
		
		$result = array();
		foreach($query_result as $current_query_result) {
			$result[] = $current_query_result['id'];
		}
		
		if($action_type <> 'view') {
			$result = array_intersect($result, $view_permitted_data_id_list);
		}
		
		return $result;
	}
	
	public function get_action_options($entity_name, $data_id) {
		$action_options = array();
		
		$basic_action_options = array();
		$custom_action_options = array();
		
		$entity_data = get_entity($entity_name);

		// Edit & Delete button
		if($this->user_can('edit', $entity_name, $data_id)) {
			$basic_action_options['edit'] = array(
				'type' => 'other',
				'label' => ucwords(lang('word__edit')),
				'glyphicon' => 'edit',
				'attr' => array(
					'href' => base_url('db/edit/' . $entity_name . '/' . $data_id),
				)
			);
		}
		
		if($this->user_can('delete', $entity_name, $data_id)) {
			$basic_action_options['delete'] = array(
				'type' => 'other',
				'label' => ucwords(lang('word__delete')),
				'glyphicon' => 'trash',
				'attr' => array(
					'href' => '#',
					'class' => 'add-edit-action-button',
					'action_mode' => 'delete',
					'form_source' => 'page',
				)
			);
		}
		
		
		if(config_item('show_add_new_button_in_view_detail') && $this->user_can('add', $entity_name) && $this->user_can('direct_add', $entity_name)) {
			$basic_action_options['add'] = array(
				'type' => 'other',
				'label' => ucwords(sprintf(lang('word__add_new_x'), $entity_data['label_singular'])),
				'glyphicon' => 'plus',
				'attr' => array(
					'href' => base_url('db/add/' . $entity_name),
				)
			);
		}
		
		if(file_exists(APPPATH.'models/'.PROJECT_CODE.'/Model_custom_action_options.php')) {
			$this->load->model(PROJECT_CODE.'/model_custom_action_options');
			$custom_action_options = $this->model_custom_action_options->get_action_options($entity_name, $data_id);
		}
		
		if(!empty($custom_action_options)) {
			// Add separator if there's at least one "other" item in custom_action_options
			if(!empty($basic_action_options) && array_search('other', array_column($custom_action_options, 'type')) !== FALSE) {
				$basic_action_options['separator'] = array(
				'type' => 'other',
				'label' => '{SEPARATOR}');
			}
			
			$action_options = array_merge($basic_action_options, $custom_action_options);
		}
		else {
			$action_options = $basic_action_options;
		}
		
		return $action_options;
	}
	
	function get_displayed_fields_data($entity_name, $id=null, $filter=array(), $specific_fields=array()) {
		$entity_data = get_entity($entity_name);
		
		$table_name = $entity_data['table_name'];
		
		if(!empty($id)) {
			$page_type = 'detail';
			$visible_here = 'visible_in_view';
		}
		else {
			$page_type = 'list'; // view list
			$visible_here = 'visible_in_list';
		}
		
		// Get the information of the fields to display
		$fields = $entity_data['fields'];
		
		$is_view_permitted = array();
		$is_edit_permitted = array();
		$is_delete_permitted = array();
		
		if(!empty($id)) { // View detail
			$is_view_permitted[$id] = $this->user_can('view', $entity_name, $id);
		}
		else { // View list
			// Get the information of data that is allowed to be viewed & edited
			$permitted_data_id_list = $this->get_permitted_data_id_list('view', $entity_name);	
			foreach($permitted_data_id_list as $permitted_data_id) {
				$is_view_permitted[$permitted_data_id] = true;
			}
			
			$permitted_data_id_list = $this->get_permitted_data_id_list('edit', $entity_name);	
			foreach($permitted_data_id_list as $permitted_data_id) {
				$is_edit_permitted[$permitted_data_id] = true;
			}
			
			$permitted_data_id_list = $this->get_permitted_data_id_list('delete', $entity_name);
			foreach($permitted_data_id_list as $permitted_data_id) {
				$is_delete_permitted[$permitted_data_id] = true;
			}
		}
		
		$select_fields = array();
		
		$id_field = isset($entity_data['id_field']) ? $entity_data['id_field'] : 'id';
		$name_field = isset($entity_data['name_field']) ? $entity_data['name_field'] : $id_field;
		
		$this->db->from($table_name);
		
		// We need to select the ID to be used in edit & delete button
		$select_fields[] = $table_name . '.' . $id_field . ' AS ' . $table_name . '__' . $id_field;
		
		// Prepare data for each column to display
		$visible_fields = array();
		foreach($fields as $field_name => $field_info) {
			if(!$this->model_db_crud->user_can('view', $entity_name, $id, array('field_name' => $field_name))) {
				continue;
			}
			
			if(!empty($specific_fields)) {
				if(!in_array($field_name, $specific_fields)) {
					continue;
				}
			}
			else {
				if(isset($field_info['visible']) && $field_info['visible'] === false) {
					continue;
				}
				if(isset($field_info[$visible_here]) && $field_info[$visible_here] === false) {
					continue;
				}
			}
			
			$visible_fields[$field_name] = $field_info;
			$field_alias = get_field_alias($entity_name, $field_name);
			$visible_fields[$field_name]['field_alias'] = $field_alias;
			
			if(isset($field_info['calculation_type'])) {
				if($field_info['calculation_type'] <> 'rendered_field') {		
					continue;
				}
			}
			
			$select_fields[] = str_replace('__', '.', $field_alias) . ' AS ' . $field_alias;
			
			if(isset($field_info['foreign_key'])) {
				$foreign_entity_data = get_entity($field_info['foreign_key']['entity_name']);
				$foreign_table_name = $field_name; // field_name as table alias
				$foreign_table_id_field = $foreign_entity_data['id_field'];

				if(isset($field_info['foreign_key']['join'])) {
					foreach($field_info['foreign_key']['join'] as $join_table => $join_related_fields) {
						$this->db->join($join_table, $join_related_fields, 'left');
					}
				}
				else {
					$this->db->join($foreign_entity_data['table_name'] . ' AS ' .$foreign_table_name, $foreign_table_name . '.' . $foreign_table_id_field . '=' . $table_name . '.' . $field_name, 'left');
				}
				
				if(isset($field_info['foreign_key']['filter'])) {
					foreach($field_info['foreign_key']['filter'] as $filter_field => $filter_value) {
						$this->db->where($filter_field, $filter_value);
					}
				}
				
				$show_link_here = ($page_type == 'list') ? 'list' : 'view';
				
				if(!isset($field_info[$show_link_here]) || $field_info[$show_link_here] !== false) {
					$select_fields[] = $foreign_table_name . '.' . $foreign_table_id_field . ' AS ' . $foreign_table_name . '__' . $foreign_table_id_field;
				}
			}
		}
		
		// Select metadata (created time, created by, last modified time, last modified by)
		$select_fields[] = $table_name . '.created_time AS created_time';
		$select_fields[] = $table_name . '.created_user AS created_user_id';
		$select_fields[] = USER_TABLE . '_created.user_name AS created_user_name';
		$select_fields[] = $table_name . '.last_modified_time AS last_modified_time';
		$select_fields[] = $table_name . '.last_modified_user AS last_modified_user_id';
		$select_fields[] = USER_TABLE . '_last_modified.user_name last_modified_user_name';
		
		$this->db->select(implode(',', $select_fields));

		$this->db->join(USER_TABLE . ' AS ' . USER_TABLE . '_created', $table_name . '.created_user = ' . USER_TABLE . '_created.id', 'left');
		$this->db->join(USER_TABLE . ' AS ' . USER_TABLE . '_last_modified', $table_name . '.last_modified_user = ' . USER_TABLE . '_last_modified.id', 'left');

		if(isset($filter)) {
			foreach($filter as $field => $value) {
				// If there is no '.' in field, add table name in front of the field to resolve ambiguous fields.
				if(strpos($field, '.') === false) {
					$field = $table_name . '.' . $field;
				}
				
				$this->db->where($field, $value);
			}
		}

		if(!empty($id)) {
			$this->db->where($table_name.'.id', $id);
		}
		
		$this->db->where($table_name.'.data_status', DATA_ACTIVE);
		
		$this->db->where('(' . $table_name . '.data_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR ' . $table_name . '.data_group = 0)');
		
		$query = $this->db->get();
		
		$query_result = $query->result_array();
		
		$result = array();
		foreach($query_result as $current_data) {
			$result_row = array();
			
			$data_id = $current_data[$entity_data['table_name'] . '__' . $id_field];
			
			// Skip the data that is not permitted to be viewed
			if(!isset($is_view_permitted[$data_id])) continue;
			
			foreach($visible_fields as $field_name => $field_info) {
				$field_alias = $field_info['field_alias'];
				
				if(isset($field_info['calculation_type'])) {
					if($field_info['calculation_type'] <> 'rendered_field') {
						continue;
					}
				}				
				
				$current_data[$field_alias] = nl2br($current_data[$field_alias]);

				$tmp_result = $current_data[$field_alias];
				if($page_type == 'list' && isset($field_info['trim_long_text']) && $field_info['trim_long_text'] === true) {
					$tmp_result = trim_long_text($tmp_result);
				}
				
				if(!empty($field_info['format_type'])) {
					if($field_info['format_type'] == 'id' && USE_ID_PREFIX) {
						$field_info['format_type'] = 'id_prefix_code';
						$this_entity_name = isset($field_info['foreign_key']['entity_name']) ? $field_info['foreign_key']['entity_name'] : $entity_name;
						$field_info['format_args'] = array('entity_name' => $this_entity_name);
					}
					else if($field_info['format_type'] == 'status') {
						$field_info['format_type'] = 'option_label';
						
						if($page_type == 'list') {
							$field_info['format_args'] = array('options_type' => $field_info['options_type'], 'render_type' => 'label');
						}
						else {
							$field_info['format_args'] = array('options_type' => $field_info['options_type']);
						}
					}
					else if($field_info['format_type'] == 'option_label') {
						if(isset($field_info['options_type'])) {
							$field_info['format_args'] = array('options_type' => $field_info['options_type']);
						}
					}
					
					$format_args = isset($field_info['format_args']) ? $field_info['format_args'] : array();
					
					$tmp_result = format_text($tmp_result, $field_info['format_type'], $format_args);
				}
				
				// Added link
				$link_base_url = !empty($field_info['link_base_url']) ? $field_info['link_base_url'] : 'db/view';
				$base_url = ($page_type == 'list') ? '{U}' : base_url();
				$show_link_here = ($page_type == 'list') ? 'show_link_in_list' : 'show_link_in_view';
				
				if(strlen($tmp_result) > 0) {
					if(isset($field_info['foreign_key']) && (!isset($field_info[$show_link_here]) || $field_info[$show_link_here] !== false)) {
						if(!isset($field_info['show_link']) || $field_info['show_link'] !== false) {
							$foreign_entity_data = get_entity($field_info['foreign_key']['entity_name']);
							$foreign_table_name = $field_name;
							$tmp_result = '<a href="'. $base_url . $link_base_url . '/' . $field_info['foreign_key']['entity_name'] .'/'. $current_data[$foreign_table_name . '__' . $foreign_entity_data['id_field']].'">'. $tmp_result . '</a>';
						}
					}
					else if($page_type == 'list' && ($field_name == $id_field || $field_name == $name_field)) {
						$tmp_result = '<a href="'. $base_url . $link_base_url . '/' . $entity_name .'/'. $current_data[$table_name . '__' . $id_field].'">'. $tmp_result . '</a>';
					}
				}
				
				if($page_type == 'detail') {
					$result_row['original'][$field_name] = $current_data[$field_alias];
					$result_row['rendered'][$field_alias] = array(
						'label' => $field_info['field_label'],
						'value' => $tmp_result
					);
				}
				else {
					$shortened_field_alias = get_shortened_field_alias($field_alias);
					$result_row[$shortened_field_alias] = $tmp_result;
				}
			}
			
			// Calculate calculated fields
			foreach($visible_fields as $field_name => $field_info) {
				if(isset($field_info['calculation_type'])) {
					if($field_info['calculation_type'] == 'concat') {
						$tmp_result = '';
						foreach($field_info['elements'] as $element) {
							if($element['type'] == 'field') {
								$field_alias = get_field_alias($entity_name, $element['field']);
								if($page_type == 'list') {
									$shortened_field_alias = get_shortened_field_alias($field_alias);
									$tmp_result .= $result_row[$shortened_field_alias];
								}
								else {
									$tmp_result .= $result_row['rendered'][$field_alias]['value'];
								}
							}
							else if($element['type'] == 'text') {
								$tmp_result .= $element['text'];
							}
						}
					}
					
					else if($field_info['calculation_type'] == 'math') {
						$tmp_result = null;
						$math_operator = $field_info['math_operator'];
						foreach($field_info['elements'] as $element) {
							if($element['type'] == 'field') {
								$field_alias = get_field_alias($entity_name, $element['field']);
								$value = !empty($current_data[$field_alias]) ? $current_data[$field_alias] : 0;
							}
							else if($element['type'] == 'number') {
								$value = !empty($element['value']) ? $element['value'] : 1;
							}

							if($tmp_result === null) {
								$tmp_result = $value;
							}
							else {
								if($math_operator == 'multiply') {
									$tmp_result *= $value;
								}
							}
						}
						
						if(isset($field_info['format_type'])) {
							$format_args = isset($field_info['format_args']) ? $field_info['format_args'] : array();
							$tmp_result = format_text($tmp_result, $field_info['format_type'], $format_args);
						}
					}
					
					else if($field_info['calculation_type'] == 'age') {
						$birth_date_field_alias = get_field_alias($entity_name, $field_info['birth_date_field']);
						$birth_date = !empty($current_data[$birth_date_field_alias]) ? new DateTime($current_data[$birth_date_field_alias]) : null;

						$death_date = null;
						if(!empty($field_info['death_date_field'])) {
							$death_date_field_alias = get_field_alias($entity_name, $field_info['death_date_field']);
							$death_date = !empty($current_data[$death_date_field_alias]) ? new DateTime($current_data[$death_date_field_alias]) : null;
						}
						
						if(!empty($death_date)) {
							$current_date = $death_date;
						}
						else {
							$current_date = new DateTime(date('Y-m-d'));
						}
						
						$age = !empty($birth_date) ? floor($current_date->diff($birth_date)->y) : null;
						
						$tmp_result = $age;
					}
					
					if($field_info['calculation_type'] <> 'rendered_field') {
						if($page_type == 'list') {
							$shortened_field_alias = get_shortened_field_alias(get_field_alias($entity_name, $field_name));
							$result_row[$shortened_field_alias] = $tmp_result;
						}
						else {
							$result_row['rendered'][$field_alias] = array(
								'label' => $field_info['field_label'],
								'value' => $tmp_result
							);
						}
					}
				}
			}
			
			// Metadata
			if($page_type == 'detail') {
				$result_row['original']['ct'] = $result_row['rendered']['ct'] = array('label'=>ucwords(lang('label__created_time')), 'value'=>$current_data['created_time']);
				$result_row['original']['cu'] = $result_row['rendered']['cu'] = array('label'=>ucwords(lang('label__created_by')), 'value'=>$current_data['created_user_name']);
				$result_row['original']['lt'] = $result_row['rendered']['lt'] = array('label'=>ucwords(lang('label__last_modified_time')), 'value'=>$current_data['last_modified_time']);
				$result_row['original']['lu'] = $result_row['rendered']['lu'] = array('label'=>ucwords(lang('label__last_modified_by')), 'value'=>$current_data['last_modified_user_name']);
			}
			else {
				$result_row['ct'] = $current_data['created_time'];
				$result_row['cu'] = $current_data['created_user_name'];
				$result_row['lt'] = $current_data['last_modified_time'];
				$result_row['lu'] = $current_data['last_modified_user_name'];
			}
			
			if($page_type == 'list') {
				// Add edit & delete button in the last column only if it's not set to 'view_only'
				$result_row['e'] = ''; // e is abbreviation of edit_and_delete_button
				if(!empty($is_edit_permitted[$data_id])) {
					$edit_button = '<span class="trigger-manipulate-data glyphicon glyphicon-edit no-text-decoration" entity="'.$entity_name.'" parent_entity="" data_id="'.$data_id.'" action_mode="edit" form_source="modal" title="'.ucfirst(lang('word__edit')).'"></span>';
					$result_row['e'] = $edit_button;
				}
				
				if(!empty($is_delete_permitted[$data_id])) {
					$delete_button = '<span class="trigger-manipulate-data glyphicon glyphicon-trash no-text-decoration" entity="'.$entity_name.'" data_id="'.$data_id.'" action_mode="delete" form_source="modal" title="'.ucfirst(lang('word__delete')).'"></span>';
					if($result_row['e'] <> '') $result_row['e'] .= ' &middot; ';
					$result_row['e'] .= $delete_button;				
				}
				
				// Row ID
				$result_row['row_id'] = 'row-'.$entity_name.'-'.$data_id;
			}
			
			$result[] = $result_row;
		}
		
		if(!empty($id) && !empty($result)) {
			return $result[0];
		}
		else {
			return $result;
		}
	}
}