<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chchdb_form {
	protected $CI;
	protected $entity_name = null;
	protected $show_action_button = true;
	protected $parent_entity_name = null;
	protected $data_id = null;
	protected $parent_data_id = null;
	protected $action_mode = 'add';
	protected $filtered_fields = null;
	protected $data;
	
	// Form source: modal / page (where this form is displayed: in a modal, or in a dedicated page).
	// If the form is shown in a page, the user will be redirected to the item page after the new item is created.
	protected $form_source = 'page';
	
	public function __construct(){
		$this->CI =& get_instance();
    }
	
	public function set($variable_name, $value) {
		switch($variable_name) {
			case 'entity_name':
				$this->entity_name = $value;
				break;
			case 'show_action_button':
				$this->show_action_button = $value;
				break;
			case 'parent_entity_name':
				$this->parent_entity_name = $value;
				break;
			case 'data_id':
				$this->data_id = $value;
				break;
			case 'parent_data_id':
				$this->parent_data_id = $value;
				break;
			case 'action_mode':
				$this->action_mode = $value;
				break;
			case 'form_source':
				$this->form_source = $value;
				break;
			case 'filtered_fields':
				$this->filtered_fields = $value;
				break;
			case 'data':
				$this->data = $value;
				break;	
			default:
		}
	}

	public function get_rendered_form() {
		if(!entity_exists($this->entity_name)) {
			return null;
		}
		
		$result = '';
				
		$entity_data = get_entity($this->entity_name);
		$fields = $entity_data['fields'];
		
		$related_field = null;
		
		if(!empty($this->parent_entity_name) && ($this->parent_entity_name <> $this->entity_name)) {
			// Get related field
			$parent_related_entities = get_entity_info($this->parent_entity_name, 'related_entities');
			
			// Search from the parent related entities
			$search_index = array_search($this->entity_name, array_column($parent_related_entities, 'entity_name'));
			$related_field = $parent_related_entities[$search_index]['related_field'];
		}

		$result .= '<form class="form_chchdb" id="form_'.$this->entity_name.'" entity="'.$this->entity_name.'" action_mode="'.$this->action_mode.'">';
		
		foreach($fields as $field_name => $field_data) {
			// Don't show field that doesn't have input type (e.g. id)
			if(!isset($field_data['input_type'])) {
				continue;
			}
			
			$input_type = $field_data['input_type'];
			$element_id = 'input_'.$field_name;		
			$element_classes = array('form-control');
			$element_attributes = array(
				'field_label' => strtolower($field_data['field_label']),
				'name' => $field_name
			);
			
			$form_group_element_classes = array('form-group');

			if(!$this->CI->model_db_crud->user_can($this->action_mode, $this->entity_name, $this->data_id, array('field_name' => $field_name))) {
				$element_attributes['disabled'] = null;
			}

			if($this->action_mode == 'add' && empty($this->parent_entity_name)) {
				if(!$this->CI->model_db_crud->user_can('direct_add', $this->entity_name, $this->data_id, array('field_name' => $field_name))) {
					$element_attributes['disabled'] = null;
				}
			}
			
			if($field_data['input_type'] === 'hidden') {
				$form_group_element_classes[] = 'hidden';
			}
			
			$required_sign = '';
			if(isset($field_data['required']) && $field_data['required'] === true) {
				$element_classes[] = 'input_required';
				$required_sign = get_rendered_required_field_sign();
			}
			
			if($field_data['field_data_type'] === 'double' || $field_data['field_data_type'] === 'int') {
				$element_classes[] = 'input_number';
				$element_classes[] = 'big-input';
			}
			
			if(isset($field_data['selectized']) && $field_data['selectized'] === true) {
				if($this->CI->model_db_crud->user_can($this->action_mode, $this->entity_name, $this->data_id, array('field_name' => $field_name))) {
					$element_classes[] = 'selectized';
				}
			}
			
			if(isset($field_data['event_listener'])) {
				foreach($field_data['event_listener'] as $event_listener) {
					// If action mode is not specified, add event listener to both add/edit action mode.
					// If action mode is specified, add event listener to only the specified action mode.
					if(!isset($event_listener['action_mode']) || $event_listener['action_mode'] == $this->action_mode) {
						$element_classes[] = 'event_listened_' . $event_listener['type'];
						$element_attributes[$event_listener['type'].'_event_listener_command'] = $event_listener['command'];
					}
				}
			}
			
			if(!empty($field_data['validation'])) {
				if($field_data['validation'] == 'datetime') {
					$element_attributes['placeholder'] = 'YYYY-MM-DD HH:MM (e.g. 2019-01-25 13:45)';
					
					if(file_exists(APPPATH.'models/'.PROJECT_CODE.'/Model_event_listener.php')) {
						$element_classes[] = 'event_listened_on_change';
						$element_attributes['on_change_event_listener_command'] = 'datetime_autoformat';
					}
				}
			}
			
			$tooltip_sign = '';
			if(!empty($field_data['tooltip'])) {
				$tooltip_sign = get_rendered_tooltip($field_data['tooltip'], false);
			}
			
			if(isset($field_data['unique']) && $field_data['unique'] === true) {
				$element_classes[] = 'input_unique';
			}

			if(!empty($field_data['placeholder'])) {
				$element_attributes['placeholder'] = htmlspecialchars($field_data['placeholder']);
			}
			if(array_key_exists('editable', $field_data) && $field_data['editable'] === false) {
				$element_attributes['readonly'] = null;
			}
			if(!empty($field_data['textarea_num_rows'])) {
				$element_attributes['rows'] = $field_data['textarea_num_rows'];
			}
			
			$value = '';
			
			// Data value
			if($this->action_mode === 'edit') {
				$current_data = $this->data;
				
				if(isset($current_data[$field_name])) {
					$value = htmlspecialchars($current_data[$field_name]);
				}
				
				if($field_data['field_data_type'] === 'double' || $field_data['field_data_type'] === 'int') {
					if($value <> '') {
						$decimal_precision = 0;
						if($value * 100 % 100 <> 0) $decimal_precision = 2;
						
						$value = number_format($value, $decimal_precision, NUMBER_DECIMAL_POINT, NUMBER_THOUSAND_SEPARATOR);
					}
				}

				if($field_name === $related_field) {
					// Hide the form group as the value is fixed
					if(!in_array('hidden', $form_group_element_classes)) {
						$form_group_element_classes[] = 'hidden';
					}
				}
			}
			else if($this->action_mode === 'add') {
				// Set default value if any
				if(isset($field_data['default_value'])) {
					if($field_data['default_value'] === '{NOW}') {
						$value = date('Y-m-d H:i:s');
					}
					else if($field_data['default_value'] === '{TODAY}') {
						$value = date('Y-m-d');
					}
					else if($field_data['default_value'] === '{CUSTOM}') {
						$this->CI->load->model(PROJECT_CODE.'/model_custom_default_value');
						$value = $this->CI->model_custom_default_value->generate_default_value($this->entity_name, $field_name, $this->parent_entity_name, $this->parent_data_id);
					}
					else {
						$value = $field_data['default_value'];
					}
				}
				
				// Set parent entity ID for related entity
				if($field_name === $related_field) {
					$value = $this->parent_data_id;
					
					// Hide the form group as the value is fixed
					if(!in_array('hidden', $form_group_element_classes)) {
						$form_group_element_classes[] = 'hidden';
					}
				}
			}

			$field_attributes = '';
			foreach($element_attributes as $element_attribute_name => $element_attribute_value) {
				$field_attributes .= ' ' . $element_attribute_name;
				if($element_attribute_value !== null) {
					$field_attributes .= "='".htmlspecialchars($element_attribute_value)."'";
				}
			}
			
			$input_addon = array();
			if(isset($field_data['format_args']['currency']) && $field_data['format_args']['currency'] == 'IDR') {
				$input_addon['before'] = 'Rp';
			}
			if(isset($field_data['format_type']) && $field_data['format_type'] == 'percentage') {
				$input_addon['after'] = '%';
			}
			if(isset($field_data['input_addon']['before'])) {
				$input_addon['before'] = $field_data['input_addon']['before'];
			}
			if(isset($field_data['input_addon']['after'])) {
				$input_addon['after'] = $field_data['input_addon']['after'];
			}
			
			// Start rendering form
			$element_class = implode(' ', $element_classes);
			
			if($field_name <> $related_field) {
				// Show only the filtered fields if filtered_fields is set
				if(!empty($this->filtered_fields) && !in_array($field_name, $this->filtered_fields)) {
					continue;
				}
				
				// Don't show the field that is set not visible in edit mode, unless it's requested in filtered fields
				if(isset($field_data['visible_in_edit']) && $field_data['visible_in_edit'] === false) {
					if(empty($this->filtered_fields) || !in_array($field_name, $this->filtered_fields)) {
						$result .= '<input type="hidden" name="'.$field_name.'" value="'.$value.'">';
						continue;
					}
				}
			}

			$result .= '
				<div class="'.implode(' ', $form_group_element_classes).'" id="form_group_'.$field_name.'">
					<label class="control-label" for="'.$element_id.'">'.$field_data['field_label'].$tooltip_sign.$required_sign.'</label>
			';
			
			if(!empty($input_addon)) {
				$result .= '<div class="input-group">';
				if(!empty($input_addon['before'])) {
					$result .= '<div class="input-group-addon">'.$input_addon['before'].'</div>';
				}
			}

			if($input_type === 'text') {
				$result .= '<input type="text" class="'.$element_class.'" id="'.$element_id.'" value="'.$value.'" '.$field_attributes.' autocomplete="off">';
			}
			else if($input_type === 'hidden') {
				$result .= '<input type="hidden" name="'.$field_name.'" value="'.$value.'">';
			}
			else if($input_type === 'date') {
				$result .= '<input type="date" class="'.$element_class.'" id="'.$element_id.'" value="'.$value.'" '.$field_attributes.' autocomplete="off">';
			}
			else if($input_type === 'datetime-local') {
				$result .= '<input type="datetime-local" class="'.$element_class.'" id="'.$element_id.'" value="'.$value.'" '.$field_attributes.' autocomplete="off">';
			}
			else if($input_type === 'textarea') {
				$result .= '<textarea class="'.$element_class.'" id="'.$element_id.'"'.$field_attributes.'>'.$value.'</textarea>';
			}
			else if($input_type === 'select') {
				$result .= '<select class="'.$element_class.'" id="'.$element_id.'"'.$field_attributes.'>';
				$result .= '<option value=""></option>';
				
				// Options from foreign key
				if(isset($field_data['foreign_key'])) {	
					$chchdb = get_instance();
					$chchdb->load->model('model_db_crud');
					$options = $chchdb->model_db_crud->get_option_list($field_data['foreign_key']['entity_name'], $field_data['foreign_key']['data_order']);
					
					if(!empty($options)) {
						foreach($options as $option) {
							$selected = ($value == $option['id']) ? ' selected' : '';
							$result .= '<option value="'.$option['id'].'"'.$selected.'>'.$option['label'].'</option>';
						}
					}
				}
				
				// Fixed options list
				else if(isset($field_data['options_type'])) {
					$options_type = $field_data['options_type'];
					$options_labels = config_item('options_labels');
					$options = $options_labels[$options_type];
					
					foreach($options as $option_id => $option_args) {
						if(is_array($option_args)) {
							$option_label = $option_args['label'];
						}
						else {
							$option_label = $option_args;
						}
						$selected = (isset($value) && $value !== '') ? ($value == $option_id ? ' selected' : '') : '';
						$result .= '<option value="'.$option_id.'"'.$selected.'>'.$option_label.'</option>';
					}
				}
				
				$result .= '</select>';
			}
			
			if(!empty($input_addon)) {
				if(!empty($input_addon['after'])) {
					$result .= '<div class="input-group-addon">'.$input_addon['after'].'</div>';
				}
				$result .= '</div>'; // input-group
			}
			
			$result .= '<span id="help_block_'.$field_name.'" class="help-block"></span>';
			$result .= '</div>';
			
			// Add hidden field to disabled field so the data can be passed to POST.
			if(in_array('disabled', $element_attributes)) {
				$result .= '<input type="hidden" name="'.$field_name.'" value="'.$value.'">';
			}
		}
		
		// Action button
		if($this->show_action_button) {
			$result .= '<button type="button" class="btn btn-primary add-edit-action-button" id="button-do-add-edit-'.$this->entity_name.'" entity="'.$this->entity_name.'" form_source="'.$this->form_source.'" parent_entity="'.$this->parent_entity_name.'" data_id="'.$this->data_id.'" action_mode="'.$this->action_mode.'" >'.ucwords(lang('word__save')).'</button>';
		}
		
		$result .= '</form>';
		
		return $result;
	}
}
