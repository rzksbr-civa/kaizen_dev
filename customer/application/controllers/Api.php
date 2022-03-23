<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API extends CI_Controller {
	public function __construct(){
        parent::__construct();
		
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_logged_in') !== TRUE) {
			$result = array('success'=>false, 'error_message'=>'User not logged in', 'logged_in'=>false);
			echo json_encode($result);
			exit;
		}
		
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$this->_show_404_page();
	}
	
	public function view_list($entity_name = '') {
		if(!entity_exists($entity_name)) {
			$this->_show_404_page();
			return;
		}
		
		if(!$this->model_db_crud->user_can('view', $entity_name)) {
			return;
		}
		
		$result = $this->model_db_crud->get_data_list($entity_name, array());
		
		//echo ini_get('memory_limit') . '/' . number_format(memory_get_peak_usage(true)/1048576,2);
		echo '{"data":' . json_encode($result) . '}';
	}
	
	// Filter list: show only data which meet $filter criteria
	//  $filter_args : e.g. field_a=active&field_b=test
	public function view_filtered_list($entity_name = '') {
		$table_name = get_entity_info($entity_name, 'table_name');
		
		if(!$this->model_db_crud->user_can('view', $entity_name)) {
			return;
		}
		
		$data_filters = $this->input->get(NULL, TRUE);
		unset($data_filters['_']);

		$result = $this->model_db_crud->get_data_list($entity_name, $data_filters);
		
		echo '{"data":' . json_encode($result) . '}';
	}
	
	public function add_edit_item() {
		$result = array();
		$result['error'] = array();
		
		$action_mode = $this->input->post('action_mode');
		$entity_name = $this->input->post('entity_name');
		$table_name = get_entity_info($entity_name, 'table_name');
		
		if($table_name === null) {
			$result['success'] = false;
			echo json_encode($result);
			return;
		}
		
		$entity_data = get_entity($entity_name);
		
		$parent_entity_name = $this->input->post('parent_entity');
		$data_id = $this->input->post('data_id');
		
		if($action_mode == 'edit') {
			$existing_data = $this->model_db_crud->get_specific_data($entity_name, $data_id);
		}
		
		// Check if user can edit this data
		if(!$this->model_db_crud->user_can($action_mode, $entity_name, $data_id)) {
			$result['success'] = false;
			$result['error'][] = array(
				'error_field'   => 'general',
				'error_message' => ucfirst(lang('message__you_are_not_allowed_to_perform_this_action'))
			);
			echo json_encode($result);
			return;
		}
		
		// Data format received from serializeArray:
		// [ { name: "...", value: "..." }, { name: "...", value: "..." } ]
		$data = $this->input->post('data');
		
		// Trim the input & set null for empty input
		for($i=0; $i<count($data); $i++) {
			$data[$i]['value'] = trim($data[$i]['value']);
			
			// Strip script/style tag
			$data[$i]['value'] = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $data[$i]['value'] );
			
			if($data[$i]['value'] === '') {
				$data[$i]['value'] = null;
			}
		}
		
		// Data format for CI Query Builder:
		// [ { "name" => "value" }, { "name" => "value" } ]
		$data_for_query_builder = array();
		foreach($data as $current_data) {
			$data_for_query_builder[$current_data['name']] = $current_data['value'];
		}
		
		$data = $data_for_query_builder;
		
		// Server side validation
		$fields = get_entity_info($entity_name, 'fields');
		foreach($fields as $field_name => $field_info) {
			// Ignore field that is not part of the form (e.g. id)
			if(!array_key_exists($field_name, $data)) {
				continue;
			}
			
			$current_data = $data[$field_name];
			
			// Check for required field
			if(isset($field_info['required']) && $field_info['required'] === true) {
				if($current_data == null) {
					$error_detail = array(
						'error_field'   => $field_name,
						'error_message' => ucfirst(lang('message__this_field_is_required'))
					);
					$result['error'][] = $error_detail;
					continue;
				}
			}
			
			// Check data type
			if($field_info['field_data_type'] == 'double' || $field_info['field_data_type'] == 'int') {
				if($current_data !== null) {
					$current_data = trim(str_replace(NUMBER_THOUSAND_SEPARATOR, '', $current_data));
					$current_data = str_replace(NUMBER_DECIMAL_POINT, '.', $current_data);
					$data[$field_name] = $current_data;
					
					if($current_data <> '' && !is_numeric($current_data)) {
						$error_detail = array(
							'error_field'   => $field_name,
							'error_message' => ucfirst(sprintf(lang('message__x_must_be_a_number'), $field_info['field_label']))
						);
						$result['error'][] = $error_detail;
						continue;
					}
					else {
						if(isset($field_info['min_number']) && $current_data < $field_info['min_number']) {
							$error_detail = array(
								'error_field'   => $field_name,
								'error_message' => ucfirst(sprintf(lang('message__minimum_x_is_y'), lcfirst($field_info['field_label']), $field_info['min_number']))
							);
							$result['error'][] = $error_detail;
							continue;
						}
						else if(isset($field_info['max_number']) && $current_data > $field_info['max_number']) {
							$error_detail = array(
								'error_field'   => $field_name,
								'error_message' => ucfirst(sprintf(lang('message__maximum_x_is_y'), lcfirst($field_info['field_label']), $field_info['max_number']))
							);
							$result['error'][] = $error_detail;
							continue;
						}
					}
				}
			}
			else if(isset($field_info['validation'])) {
				if($field_info['validation'] == 'datetime') {
					if($current_data <> '' && !(DateTime::createFromFormat('Y-m-d H:i:s', $current_data) || DateTime::createFromFormat('Y-m-d H:i', $current_data) || DateTime::createFromFormat('Y-m-d', $current_data))) {
						$error_detail = array(
							'error_field'   => $field_name,
							'error_message' => 'Date format should be in YYYY-MM-DD HH:MM (e.g. 2019-01-25 13:45)'
						);
						$result['error'][] = $error_detail;
						continue;
					}
				}
				else if($field_info['validation'] == 'time') {
					if($current_data <> '' && !(DateTime::createFromFormat('H:i:s', $current_data) || DateTime::createFromFormat('H:i', $current_data))) {
						$error_detail = array(
							'error_field'   => $field_name,
							'error_message' => 'Date format should be in HH:MM (e.g. 13:45)'
						);
						$result['error'][] = $error_detail;
						continue;
					}
				}
			}
			
			// Check regex format
			if(isset($field_info['format_regex'])) {
				if($current_data <> '' && !preg_match($field_info['format_regex'], $current_data)) {
					$error_message = isset($field_info['format_error_message']) ? $field_info['format_error_message'] : lang('message__input_format_is_incorrect');
					$error_detail = array(
						'error_field'   => $field_name,
						'error_message' => $error_message
					);
					$result['error'][] = $error_detail;
					continue;
				}
			}
			
			// Check for uniqueness
			if(isset($field_info['unique']) && $field_info['unique'] === true && $current_data <> null) {
				
				// Check if data exists...
				if($this->model_db_crud->data_exist($table_name, $field_name, $current_data, $data_id)) {
					$field_label = strtolower($field_info['field_label']);
					$error_detail = array(
						'error_field'   => $field_name,
						'error_message' => ucfirst(sprintf(lang('message__this_x_already_exists__please_use_another_x'), $field_label, $field_label))
					);
					$result['error'][] = $error_detail;
					continue;
				}
			}
			
			// Check if this field is editable
			$disallowed_field_got_edited = false;
			if(!$this->model_db_crud->user_can($action_mode,$entity_name,$data_id,array('field_name'=>$field_name))) {
				
				if($action_mode == 'edit') {
					if($existing_data[$field_name] <> $current_data) {
						$disallowed_field_got_edited = true;
					}
				}
				else if($action_mode == 'add') {
					if(isset($field_info['default_value'])) {
						if($field_info['default_value'] <> '{CUSTOM}' && $field_info['default_value'] <> $current_data) {
							$disallowed_field_got_edited = true;
						}
					}
					else if(!empty($current_data)) {
						$disallowed_field_got_edited = true;
					}
				}
			}
			
			if($action_mode == 'add' && empty($parent_entity_name)) {
				if(!$this->model_db_crud->user_can('direct_add',$entity_name,$data_id,array('field_name'=>$field_name))) {
					if(isset($field_info['default_value'])) {
						if($field_info['default_value'] <> '{CUSTOM}' && $field_info['default_value'] <> $current_data) {
							$disallowed_field_got_edited = true;
						}
					}
					else if(!empty($current_data)) {
						$disallowed_field_got_edited = true;
					}
				}
			}
			
			if($disallowed_field_got_edited) {
				$error_detail = array(
					'error_field'   => $field_name,
					'error_message' => ucfirst(lang('message__you_are_not_allowed_to_edit_this_field'))
				);
				$result['error'][] = $error_detail;
				continue;
			}
		}
		
		// Advanced Data Validation
		if(file_exists(APPPATH.'models/'.PROJECT_CODE.'/Model_advanced_data_validation.php')) {
			$this->load->model(PROJECT_CODE.'/model_advanced_data_validation');
			$advanced_data_validation = $this->model_advanced_data_validation->validate_data($entity_name, $action_mode, $data, $data_id);
			
			if(!empty($advanced_data_validation['error'])) {
				if(empty($result['error'])) {
					$result['error'] = $advanced_data_validation['error'];
				}
				else {
					$result['error'] = array_merge($advanced_data_validation['error'], $result['error']);
				}
			}
		}
		
		if(empty($result['error'])) {
			if($action_mode === 'add') {
				$result = $this->model_db_crud->add_item($table_name, $data);
				
				if($result['success']) {
					$added_data_list = $this->model_db_crud->get_data_list($entity_name, array($table_name.'.id' => $result['insert_id']));
					$result['added_data'] = $added_data_list[0];
				}
			}
			else if($action_mode === 'edit') {
				$result = $this->model_db_crud->edit_item($table_name, $data_id, $data);
				
				if($result['success']) {
					$updated_data_list = $this->model_db_crud->get_data_list($entity_name, array($table_name.'.id' => $data_id));

					$result['updated_data'] = $updated_data_list[0];
				}
			}
		}
		else {
			$result['success'] = false;
		}
		
		if(!$result['success'] && empty($result['error'])) {
			$result['error'][] = array(
				'error_field'   => 'general',
				'error_message' => 'Unknown error'
			);
		}

		echo json_encode($result);
	}
	
	public function delete_item() {
		$result = array();
		$result['error_message'] = null;

		$entity_name = $this->input->post('entity_name');
		$table_name = get_entity_info($entity_name, 'table_name');
		
		if($table_name === null) {
			$result['success'] = false;
			echo json_encode($result);
			return;
		}
		
		$data_id = $this->input->post('data_id');
		
		// Check if user can delete this data
		if(!$this->model_db_crud->user_can('delete', $entity_name, $data_id)) {
			$result['success'] = false;
			$result['error_message'] = ucfirst(lang('message__you_are_not_allowed_to_perform_this_action'));
			echo json_encode($result);
			return;
		}
		
		$data_deletion_validation = true;
		if(file_exists(APPPATH.'models/'.PROJECT_CODE.'/Model_advanced_data_validation.php')) {
			$this->load->model(PROJECT_CODE.'/model_advanced_data_validation');
			$data_deletion_validation = $this->model_advanced_data_validation->validate_data_deletion($entity_name, $data_id);
		}
		
		if($data_deletion_validation['result'] == true) {
			// Make sure there is no related data in the related tables...
			$db_structure = config_item('db_structure');
			$excluded_related_entities = get_entity_info($entity_name, 'excluded_related_entities_in_data_deletion_validation');
			if($excluded_related_entities === null) $excluded_related_entities = array();
			$related_entities_label_with_found_data = array();
			foreach($db_structure as $this_entity_name => $this_entity_data) {
				foreach($this_entity_data['fields'] as $field_name => $field_info) {
					if(isset($field_info['foreign_key']) && $field_info['foreign_key']['entity_name'] == $entity_name && !in_array($this_entity_name, $excluded_related_entities)) {
						if(isset($field_info['calculation_type'])) {
							// Homework to be done later...
						}
						else {
							if(!empty($this->model_db_crud->get_specific_data($this_entity_name, null, array('where' => array(array($field_name, $data_id)), 'limit' => 1)))) {
								if(!in_array($this_entity_data['label_singular'], $related_entities_label_with_found_data)) {
									$related_entities_label_with_found_data[] = $this_entity_data['label_singular'];
								}
							}
						}
					}
				}
			}
		
			if(empty($related_entities_label_with_found_data)) {
				$result = $this->model_db_crud->delete_item($table_name, $data_id);
			}
			else {
				$result['success'] = false;
				$entity_label = get_entity_info($entity_name, 'label_singular');
				$result['error_message'] = sprintf(lang('message__you_cannot_delete_this_x_because_there_are_related_x_to_this_x'), $entity_label, implode(', ', $related_entities_label_with_found_data), $entity_label);
			}
			
			
		}
		else {
			$result['success'] = false;
			$result['error_message'] = $data_deletion_validation['error_message'];
		}
		
		echo json_encode($result);
	}
	
	// Retrieve information to be shown on the add/edit modal (e.g. modal title, modal body, modal footer)
	public function prepare_modal_add_edit() {
		$result = array();
		$result['success'] = true;
		
		$entity_name = $this->input->post('entity_name');
		$parent_entity = $this->input->post('parent_entity');
		$data_id = $this->input->post('data_id');
		$parent_data_id = $this->input->post('parent_data_id');
		$action_mode = $this->input->post('action_mode');
		$form_source = $this->input->post('form_source');
		$widget_id = $this->input->post('widget_id');
		
		// Action mode must be add / edit / delete
		if(!in_array($action_mode, array('add', 'edit', 'delete'))) {
			$result['success'] = false;
			$result['error_message'] = lang('message__you_are_not_allowed_to_perform_this_action');
			echo json_encode($result);
			return;
		}
		
		// Check if the user can add/edit data
		if(($action_mode == 'add' && !$this->model_db_crud->user_can('add', $entity_name)) ||
		($action_mode == 'edit' && !$this->model_db_crud->user_can('edit', $entity_name, $data_id))) {
			$result['success'] = false;
			$result['error_message'] = lang('message__you_are_not_allowed_to_perform_this_action');
			echo json_encode($result);
			return;
		}
		
		$entity_data = get_entity($entity_name);
		
		// Check if this entity is set to 'view_only'. If yes, deny request.
		if(!empty($entity_data['view_only'])) {
			$result['success'] = false;
			$result['error_message'] = lang('message__you_are_not_allowed_to_perform_this_action');
			echo json_encode($result);
			return;
		}
		
		// Set edit modal header label
		if(!empty($widget_id)) {
			// If widget_id is set, it means that this request made by widget_partial_info
			// We then find the widget specs first...
			$widget_specs = array();
			
			$parent_entity_data = get_entity($parent_entity);

			foreach($parent_entity_data['tabs_widgets'] as $tabs_widgets) {
				foreach($tabs_widgets['widgets'] as $widgets) {
					if(isset($widgets['widget_specs']['widget_id']) && $widgets['widget_specs']['widget_id'] == $widget_id) {
						$widget_specs = $widgets['widget_specs'];
						break;
					}
				}
			}
		}
		
		if(!empty($widget_specs['info_type']) && $widget_specs['info_type'] == 'info') {
			$result['modal_header_label'] = ucwords(sprintf(lang('word__set_x'), lcfirst($widget_specs['widget_title'])));;
		}
		else {
			if($action_mode === 'add') {
				$result['modal_header_label'] = ucwords(sprintf(lang('word__add_new_x'), $entity_data['label_singular']));
			}
			else if($action_mode === 'edit') {
				$result['modal_header_label'] = ucwords(sprintf(lang('word__edit_x'), $entity_data['label_singular']));
			}
			else if($action_mode === 'delete') {
				$result['modal_header_label'] = ucwords(sprintf(lang('word__delete_x'), $entity_data['label_singular']));
			}
		}
		
		$result['modal_body'] = '';
		
		$this->load->library('chchdb/chchdb_form');
		$this->chchdb_form->set('entity_name', $entity_name);
		
		// Modal body for add & edit
		if($action_mode === 'add' || $action_mode === 'edit') {
			$this->chchdb_form->set('show_action_button', false);
			$this->chchdb_form->set('parent_entity_name', $parent_entity);
			$this->chchdb_form->set('data_id', $data_id);
			$this->chchdb_form->set('parent_data_id', $parent_data_id);
			$this->chchdb_form->set('action_mode', $action_mode);
			$this->chchdb_form->set('form_source', $form_source);
			
			if(isset($widget_specs)) {
				$this->chchdb_form->set('filtered_fields', $widget_specs['fields']);
			}
			
			if($action_mode === 'edit') {
				$data = $this->model_db_crud->get_specific_data($entity_name, $data_id);
				
				if($data == null) {
					// Data not found
					$result['modal_body'] = lang('message__error_data_may_have_been_deleted');
					echo json_encode($result);
					return;
				}
				else {
					$this->chchdb_form->set('data', $data);
				}
			}
			
			$result['modal_body'] = $this->chchdb_form->get_rendered_form();
		}
		else if($action_mode === 'delete') {
			$result['modal_body'] = ucfirst(lang('message__are_you_sure_you_want_to_delete_this_item'));
		}

		echo json_encode($result);
	}
	
	public function user($action = '') {
		$this->load->model('model_user');
		
		$result = array();
		$result['success'] = false;
		$result['error_details'] = array();
		
		if($action === 'change_password') {
			$current_user_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
			$current_password = $this->input->post('current_password');
			$new_password = $this->input->post('new_password');
			$confirm_new_password = $this->input->post('confirm_new_password');
			
			// Server side validation
			
			// Current password is required
			if(strlen($current_password) === 0) {
				$result['error_details'][] = array(
					'error_field'   => 'current_password',
					'error_message' => lang('message__this_field_is_required')
				);
			}
			else {
				// Verify the current password
				$verify_current_password = $this->model_user->verify_user_password($current_user_id, $current_password);
				if(!$verify_current_password['result']) {
					$result['error_details'][] = array(
						'error_field'   => 'current_password',
						'error_message' => lang('message__current_password_is_wrong')
					);
				}
			}
			
			// New password is required
			if(strlen($new_password) === 0) {
				$result['error_details'][] = array(
					'error_field'   => 'new_password',
					'error_message' => lang('message__this_field_is_required')
				);
			}
			
			// New password must be at least 8 characters
			else if(strlen($new_password) < 8) {
				$result['error_details'][] = array(
					'error_field'   => 'new_password',
					'error_message' => lang('message__password_must_be_at_least_8_characters')
				);
			}
			
			// Confirm new password is required
			if(strlen($confirm_new_password) === 0) {
				$result['error_details'][] = array(
					'error_field'   => 'confirm_new_password',
					'error_message' => lang('message__this_field_is_required')
				);
			}
			
			// Confirm new password must match the new password
			else if($confirm_new_password <> $new_password) {
				$result['error_details'][] = array(
					'error_field'   => 'confirm_new_password',
					'error_message' => lang('message__password_does_not_match')
				);
			}
			
			if(empty($result['error_details'])) {
				// Proceed to change the password
				$do_change_password = $this->model_user->change_user_password($current_user_id, $new_password);
				
				if($do_change_password['result'] === 'success') {
					$result['success'] = true;
				}
				else {
					$result['error_details'][] = array(
						'error_field'   => '{general}',
						'error_message' => $do_change_password['error_message']
					);
				}
			}
			else {
				$result['success'] = false;
			}	
		}

		echo json_encode($result);
	}
	
	public function get_event_listener_action() {
		$result = array();
		
		$command = $this->input->post('command');
		$field_name = $this->input->post('field_name');
		$field_value = $this->input->post('field_value');
		$action_mode = $this->input->post('action_mode');
		$tmp_fields = $this->input->post('fields');
		
		$fields = array();
		foreach($tmp_fields as $tmp_field) {
			$fields[$tmp_field['name']] = $tmp_field['value'];
		}
		
		$this->load->model(PROJECT_CODE.'/model_event_listener');
		$event_listener_action = $this->model_event_listener->get_event_listener_action($command, $field_name, $field_value, $action_mode, $fields);
		
		if($event_listener_action['success'] === true) {
			$result['success'] = true;
			$result['actions'] = $event_listener_action['actions'];
		}
		else {
			$result['success'] = false;
			$result['error_message'] = $event_listener_action['error_message'];
		}

		echo json_encode($result);
	}
	
	// To do action from action_options
	public function do_action() {
		$result = array();
		$result['success'] = false;
		$result['error_message'] = null;
		
		$command = $this->input->post('command');
		$action_name = $this->input->post('action_name');
		$entity_name = $this->input->post('entity_name');
		$data_id = $this->input->post('data_id');
		
		if($command === 'call_project_api') {
			$this->load->model(PROJECT_CODE.'/model_custom_action_options');
			$result = $this->model_custom_action_options->do_action($action_name, $entity_name, $data_id);
		}
		
		echo json_encode($result);
	}
	
	public function view_report() {
		$report_args = $this->input->get(NULL, TRUE);
		
		$this->load->library('chchdb/widgets/widget_report');
		if(!isset($report_args['report_type']) || !$this->widget_report->user_can_view_report($report_args['report_type'])) {
			return null;
		}

		$this->load->model(PROJECT_CODE.'/model_report');
		$result = $this->model_report->get_table_data($report_args);
		
		echo '{"data":' . json_encode($result) . '}';
	}
	
	public function get_report_options_parameter() {
		if(!empty($this->input->post('report_type'))) {
			$report_args = array('report_type' => $this->input->post('report_type'), 'refresh_option_parameter' => true);
		}
		else {
			$report_args = $this->input->get(NULL, TRUE);
		}
		
		$this->load->library('chchdb/widgets/widget_report');
		if(!isset($report_args['report_type']) || !$this->widget_report->user_can_view_report($report_args['report_type'])) {
			return null;
		}

		$this->load->model(PROJECT_CODE.'/model_report');		
		$result = $this->model_report->get_report_options_parameter($report_args);
		
		echo json_encode($result);
	}
	
	public function get_after_table_widgets() {
		$result = array('after_table_widgets' => '');
		
		$parent_entity_name = $this->input->post('parent_entity_name');
		$entity_name = $this->input->post('entity_name');
		$tab_name = $this->input->post('tab_name');
		$data_id = $this->input->post('data_id');
		
		$rendered_widgets = '';
		$parent_entity_data = get_entity($parent_entity_name);
		if(isset($parent_entity_data['tabs_widgets'])) {
			foreach($parent_entity_data['tabs_widgets'] as $tab_widgets) {
				if($tab_widgets['tab_name'] == $tab_name) {
					foreach($tab_widgets['widgets'] as $widget) {
						if(isset($widget['widget_position']) && $widget['widget_position'] == 'after_table') {
							$widget_type = isset($widget['widget_type']) ? $widget['widget_type'] : 'core';
							$widget_name = $widget['widget_name'];
							
							$project_code = ($widget_type === 'core') ? 'chchdb' : PROJECT_CODE;
							$this->load->library($project_code.'/widgets/'.$widget_name);
							
							$widget_specs = array(
								'entity_name' => $entity_name,
								'data_id' => $data_id
							);
							
							if(isset($widget['widget_specs'])) {
								$widget_specs = array_merge($widget_specs, $widget['widget_specs']);
							}
							
							$this->$widget_name->set_widget_specs($widget_specs);
							
							$rendered_widgets .= $this->$widget_name->get_rendered_widget();
						}
					}
				}
			}
		}
		
		$result['after_table_widgets'] = $rendered_widgets;
		
		echo json_encode($result);
	}
	
	public function get_table_footer_total_info_data() {
		$result = array();
		
		$arr = array(
			'table_name' => $this->input->post('table_name'),
			'data_id' => $this->input->post('data_id')
		);

		$this->load->model(PROJECT_CODE.'/widgets/model_widget_table_footer_total_info');
		$result = $this->model_widget_table_footer_total_info->get_data($arr);
		
		echo json_encode($result);
	}
	
	public function get_rendered_custom_modal() {
		$result = array();
		$result['success'] = false;
		$result['error_message'] = null;

		$modal_name = $this->input->post('modal_name');
		$entity_name = $this->input->post('entity_name');
		$data_id = $this->input->post('data_id');
		
		$this->load->model(PROJECT_CODE.'/model_custom_modal');
		$args = array(
			'entity_name' => $entity_name,
			'data_id' => $data_id
		);
		$result = $this->model_custom_modal->get_rendered_custom_modal($modal_name, $args);

		echo json_encode($result);
	}
	
	public function add_image() {
		$result = array('success'=>false, 'error_message'=>'Something went wrong');
		
		$entity_name = $this->input->post('entity_name');
		$data_id = $this->input->post('data_id');
		
		if(!$this->model_db_crud->user_can('edit', $entity_name, $data_id)) {
			$result['success'] = false;
			$result['error_message'] = ucfirst(lang('message__you_are_not_allowed_to_perform_this_action'));
			echo json_encode($result);
			return;
		}
		
		if($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
			$result['success'] = false;
			$result['error_message'] = lang('message__image_upload_failed_please_try_again');
			echo json_encode($result);
			return;
		}
		
		$image_prefix = strtotime(date('Y-m-d H:i:s'));
		$image_name = strtolower($_FILES['image']['name']);
		$image_tmp_name = $_FILES['image']['tmp_name'];
		
		$image_file_type = exif_imagetype($image_tmp_name);
		$file_extension = pathinfo(strtolower($image_name), PATHINFO_EXTENSION);
		
		if(!in_array($image_file_type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF)) ||
		!in_array($file_extension, array('jpg', 'jpeg', 'png', 'gif'))) {
			$result['success'] = false;
			$result['error_message'] = lang('message__error_only_jpeg_png_gif_image_type_allowed');
			echo json_encode($result);
			return;
		}
		
		$new_image_name = $image_prefix . '-' . rand(100000,999999) . '.' . $file_extension;
		
		$relative_path = 'img/' . $new_image_name;
		$path = 'assets/data/' . PROJECT_CODE . '/' . $relative_path;
		move_uploaded_file($image_tmp_name, $path);
		
		$add_image_info = $this->model_db_crud->add_item(
			'chchdb_files',
			array(
				'file_name' => $new_image_name,
				'original_file_name' => $_FILES['image']['name'],
				'entity_name' => $entity_name,
				'ref_id' => $data_id,
				'ref_field' => '[IMAGES]',
				'file_order' => 1,
				'file_type' => 'image',
				'file_path' => $relative_path,
				'file_caption' => null,
				'thumbnail_path' => $relative_path,
				'is_primary' => false
			),
			array('skip_check_data_dependency' => true)
		);
		
		$result['success'] = $add_image_info['success'];
		if($add_image_info['success']) {
			$result['image_id'] = $add_image_info['insert_id'];
			$result['image_path'] = base_url($path);
		}
		
		echo json_encode($result);
	}
	
	public function delete_image() {
		$result = array('success'=>false, 'error_message'=>'Something went wrong');
		
		$image_id = $this->input->post('image_id');
		
		$images = $this->db
			->select('*')
			->from('chchdb_files')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('id', $image_id)
			->get()->result_array();
		
		if(empty($images)) {
			$result['success'] = false;
			$result['error_message'] = 'Error. Image not found.';
			echo json_encode($result);
			return;
		}
		
		$image = $images[0];
		$entity_name = $image['entity_name'];
		$data_id = $image['ref_id'];
		
		if(!$this->model_db_crud->user_can('edit', $entity_name, $data_id)) {
			$result['success'] = false;
			$result['error_message'] = ucfirst(lang('message__you_are_not_allowed_to_perform_this_action'));
			echo json_encode($result);
			return;
		}
		
		unlink('assets/data/'.PROJECT_CODE.'/'.$image['file_path']) or die("Couldn't delete file");
		
		$delete_image_info = $this->model_db_crud->delete_item(
			'chchdb_files',
			$image_id,
			array('skip_check_data_dependency' => true)
		);
		
		$result['success'] = $delete_image_info['success'];
		
		echo json_encode($result);
	}
}
