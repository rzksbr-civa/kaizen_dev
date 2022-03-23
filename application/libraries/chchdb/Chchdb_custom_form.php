<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chchdb_custom_form {
	protected $CI;
	
	/* $fields: array(
	      field_name => array(
		     field_label
			 field_info_type
			 input_type
			 tooltip
			 validation
			 placeholder
			 textarea_num_rows
			 format_type
			 format_args
			 input_addon => array(before , after)
			 
		  )
	   )
	 */
	protected $fields = array();
	protected $action_button_label = null;
	protected $data;
	protected $custom_form_args;
	
	public function __construct(){
		$this->CI =& get_instance();
		
		$this->action_button_label = ucfirst(lang('word__save'));
		$this->action_mode = 'add';
		$this->data_id = null;
    }
	
	public function set_fields($fields) {
		$this->fields = $fields;
	}
	
	public function set_args($args) {
		$this->custom_form_args = $args;
	}

	public function get_rendered_form() {
		$fields = $this->fields;
		$data = $this->data;

		$result = '<form class="form_chchdb" id="chchdb_custom_form">';
		
		foreach($fields as $field_name => $field_info) {
			// Don't show field that doesn't have input type (e.g. id)
			if(!isset($field_info['input_type'])) {
				continue;
			}
			
			$input_type = $field_info['input_type'];
			$element_id = 'input_'.$field_name;		
			$element_classes = array('form-control');
			$element_attributes = array(
				'field_label' => strtolower($field_info['field_label']),
				'name' => $field_name
			);
			
			$form_group_element_classes = array('form-group');
			
			if($field_info['input_type'] === 'hidden') {
				$form_group_element_classes[] = 'hidden';
			}
			
			$required_sign = '';
			if(isset($field_info['required']) && $field_info['required'] === true) {
				$element_classes[] = 'input_required';
				$required_sign = get_rendered_required_field_sign();
			}
			
			if($field_info['field_data_type'] === 'double' || $field_info['field_data_type'] === 'int') {
				$element_classes[] = 'input_number';
				$element_classes[] = 'big-input';
			}
			
			if(isset($field_info['selectized']) && $field_info['selectized'] === true) {
				$element_classes[] = 'selectized';
			}
			
			if(!empty($field_info['validation'])) {
				if($field_info['validation'] == 'datetime') {
					$element_attributes['placeholder'] = 'YYYY-MM-DD HH:MM (e.g. 2019-01-25 13:45)';
				}
			}
			
			$tooltip_sign = '';
			if(!empty($field_info['tooltip'])) {
				$tooltip_sign = get_rendered_tooltip($field_info['tooltip'], false);
			}

			if(!empty($field_info['placeholder'])) {
				$element_attributes['placeholder'] = htmlspecialchars($field_info['placeholder']);
			}
			if(!empty($field_info['textarea_num_rows'])) {
				$element_attributes['rows'] = $field_info['textarea_num_rows'];
			}
			
			$value = '';
			
			// Data value
			if($this->action_mode === 'edit') {	
				if(isset($data[$field_name])) {
					$value = htmlspecialchars($data[$field_name]);
				}
				
				if($field_info['field_data_type'] === 'double' || $field_info['field_data_type'] === 'int') {
					if($value <> '') {
						$decimal_precision = 0;
						if($value * 100 % 100 <> 0) $decimal_precision = 2;
						
						$value = number_format($value, $decimal_precision, NUMBER_DECIMAL_POINT, NUMBER_THOUSAND_SEPARATOR);
					}
				}
			}
			else if($this->action_mode === 'add') {
				// Set default value if any
				if(isset($field_info['default_value'])) {
					if($field_info['default_value'] === '{NOW}') {
						$value = date('Y-m-d H:i:s');
					}
					else if($field_info['default_value'] === '{TODAY}') {
						$value = date('Y-m-d');
					}
					else {
						$value = $field_info['default_value'];
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
			if(isset($field_info['format_args']['currency']) && $field_info['format_args']['currency'] == 'IDR') {
				$input_addon['before'] = 'Rp';
			}
			if(isset($field_info['format_type']) && $field_info['format_type'] == 'percentage') {
				$input_addon['after'] = '%';
			}
			if(isset($field_info['input_addon']['before'])) {
				$input_addon['before'] = $field_info['input_addon']['before'];
			}
			if(isset($field_info['input_addon']['after'])) {
				$input_addon['after'] = $field_info['input_addon']['after'];
			}
			
			// Start rendering form
			$element_class = implode(' ', $element_classes);

			$result .= '
				<div class="'.implode(' ', $form_group_element_classes).'" id="form_group_'.$field_name.'">
					<label class="control-label" for="'.$element_id.'">'.$field_info['field_label'].$tooltip_sign.$required_sign.'</label>
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
				if(isset($field_info['foreign_key'])) {	
					$chchdb = get_instance();
					$chchdb->load->model('model_db_crud');
					$options = $chchdb->model_db_crud->get_option_list($field_info['foreign_key']['entity_name'], $field_info['foreign_key']['data_order']);
					
					if(!empty($options)) {
						foreach($options as $option) {
							$selected = ($value == $option['id']) ? ' selected' : '';
							$result .= '<option value="'.$option['id'].'"'.$selected.'>'.$option['label'].'</option>';
						}
					}
				}
				
				// Fixed options list
				else if(isset($field_info['options_type'])) {
					$options_type = $field_info['options_type'];
					$options_labels = config_item('options_labels');
					$options = $options_labels[$options_type];
					
					foreach($options as $option_id => $option_args) {
						if(is_array($option_args)) {
							$option_label = $option_args['label'];
						}
						else {
							$option_label = $option_args;
						}
						$selected = isset($value) ? ($value == $option_id ? ' selected' : '') : '';
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
	
		$result .= '</form>';
		
		return $result;
	}
	
	public function get_rendered_buttons() {
		$custom_form_args = array();
		foreach($this->custom_form_args as $arg_key => $arg_value) {
			$custom_form_args[] = 'data-'.$arg_key.'="'.addcslashes($arg_value, '"').'"';
		}
		
		$result = '<button type="button" class="btn btn-primary custom-form-action-button" id="button-do-add-edit-custom-form" '.implode(' ',$custom_form_args).'>'.$this->action_button_label.'</button>';
		
		return $result;
	}
}
