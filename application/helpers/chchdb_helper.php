<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|  Add PROJECT_NAME in the end of the page title
*/
function generate_page_title($page_title) {
	return $page_title . ' - ' . PROJECT_NAME;
}

/* 
|  generate_header_menu_item :
|  Function to generate header menu item.
|  header_menu_elements setup in chchdb_config.php
*/
function generate_header_menu_item($header_menu_elements, $user_role_id) {
	$CI = get_instance();
	$CI->load->model('model_db_crud');
	
	foreach($header_menu_elements as $header_menu_element) {
		if(isset($header_menu_element['access_right']) && !$CI->model_db_crud->user_can($header_menu_element['access_right'][0], $header_menu_element['access_right'][1])) {
			continue;
		}
		
		if(isset($header_menu_element['hide_from_user_role']) && array_search($user_role_id, $header_menu_element['hide_from_user_role']) !== false) continue;
	
		// Generate dropdown menu
		if($header_menu_element['type'] === 'dropdown') {
			echo '
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$header_menu_element['label'].' <b class="caret"></b></a>
			';
			
			// Generate dropdown submenu
			if(!empty($header_menu_element['submenu'])) {
				echo '<ul class="dropdown-menu">';
				foreach($header_menu_element['submenu'] as $header_submenu_element) {
					if(isset($header_submenu_element['access_right']) && !$CI->model_db_crud->user_can($header_submenu_element['access_right'][0], $header_submenu_element['access_right'][1])) {
						continue;
					}
					
					if($header_submenu_element['type'] === 'link') {
						if(isset($header_submenu_element['hide_from_user_role']) && array_search($user_role_id, $header_submenu_element['hide_from_user_role']) !== false) continue;
						
						echo '<li><a href="'.base_url($header_submenu_element['link']).'">'.$header_submenu_element['label'].'</a></li>';
					}
					else if($header_submenu_element['type'] === 'separator') {
						echo '<li role="separator" class="divider"></li>';
					}
				}
				echo '</ul>'; // end ul dropdown-menu
			}
			
			echo '</li>'; // end li dropdown
		}
		else if($header_menu_element['type'] === 'link') {
			echo '<li><a href="'.base_url($header_menu_element['link']).'">'.$header_menu_element['label'].'</a></li>';
		}
	}
}

function entity_exists($entity_name) {
	$db_structure = config_item('db_structure');
	
	if(!empty($db_structure[$entity_name])) return true;
	else return false;
}

/*
| Retrieve all data in an entity in config['db_structure']
*/
function get_entity($entity_name, $set_defaults = true) {
	$db_structure = config_item('db_structure');
	
	if(!empty($db_structure[$entity_name])) {
		$entity_data = $db_structure[$entity_name];
		
		if($set_defaults) {
			if(!isset($entity_data['id_field'])) {
				$entity_data['id_field'] = 'id';
			}
			if(!isset($entity_data['name_field'])) {
				$entity_data['name_field'] = $entity_data['id_field'];
			}
		}
		
		return $entity_data;
	}
	else {
		log_message('error', 'Error in function get_entity: ' . $entity_name . ' doesn\'t exist.');
		return null;
	}
}

/*
| Retrieve a specific data in an entity in config['db_structure']
*/
function get_entity_info($entity_name, $info_type) {
	$entity_data = get_entity($entity_name);

	if(!empty($entity_data)) {
		if(isset($entity_data[$info_type])) {
			return $entity_data[$info_type];
		}
		else {
			return null;
		}
	}
	
	return null;
}

function get_entity_label($entity_name, $type='singular') {
	if($type === 'singular') {
		return get_entity_info($entity_name, 'label_singular');
	}
	else if($type === 'plural') {
		return get_entity_info($entity_name, 'label_plural');
	}
}

function get_id_prefix_code($entity_name) {
	return get_entity_info($entity_name, 'id_prefix_code');
}

/*
| e.g.
| Input : customers (table customers)
| Output: customer (entity customer)
*/
function get_entity_name_by_table_name($table_name) {
	$db_structure = config_item('db_structure');
	foreach($db_structure as $entity_name => $entity_data) {
		if($entity_data['table_name'] === $table_name) {
			return $entity_name;
		}
	}
	return null;
}

/*
| e.g.
| Input : customers.customer_name
| Output: Customer Name
*/
function get_default_label_for_displayed_field($field_name) {
	$tmp = explode('.', $field_name);
	if(count($tmp) != 2) return null;
	
	$table_name = $tmp[0];
	$field_name = $tmp[1];
	
	$entity_name = get_entity_name_by_table_name($table_name);
	$db_structure = config_item('db_structure');
	
	if(!empty($db_structure[$entity_name]['fields'][$field_name])) {
		return $db_structure[$entity_name]['fields'][$field_name]['field_label'];
	}
	return null;
}

function format_text($text, $format_type = null, $format_args = null) {
	if($text === null || $text === '') return $text;
	
	if($format_type === 'text') {
		//$text = nl2br($text);
	}
	else if($format_type === 'id_prefix_code') {
		if(!empty($format_args['entity_name'])) {
			$id_prefix_code = get_id_prefix_code($format_args['entity_name']);
			return sprintf('%s%04d', $id_prefix_code, $text); 
		}
	}
	else if($format_type === 'option_label') {
		$result = $text;
		if(!empty($format_args['options_type'])) {
			$options_type = $format_args['options_type'];
			$options_labels = config_item('options_labels');
			if(isset($options_labels[$options_type][$text])) {
				$result = $options_labels[$options_type][$text]['label'];
			}
		}
		if(!empty($format_args['render_type'])) {
			$classes = '';
			if(!empty($format_args['class'])) {
				if(is_array($format_args['class'])) {
					$classes = implode(' ', $format_args['class']);
				}
				else {
					$classes = $format_args['class'];
				}
			}
			
			switch($format_args['render_type']) {
				case 'label';
					$result = '<span class="label '.$classes.' label-'.$options_labels[$options_type][$text]['color'].'">'.strtoupper($result).'</span>';
					break;
				default:
			}
		}
		
		return $result;
	}
	else if($format_type === 'full-datetime') {
		$text = date('l, j F Y - H:i', strtotime($text));
		$text = str_replace('-', '&middot;', $text);
		
		$english_day_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		$indonesian_day_names = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
		if(PROJECT_LANGUAGE == 'indonesian' || (isset($format_args['language']) && $format_args['language'] === 'indonesian')) {
			$text = str_replace($english_day_names, $indonesian_day_names, $text);
		}
		
		$english_month_names = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		$indonesian_month_names = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
		if(PROJECT_LANGUAGE == 'indonesian' || (isset($format_args['language']) && $format_args['language'] === 'indonesian')) {
			$text = str_replace($english_month_names, $indonesian_month_names, $text);
		}
		
		$text = str_replace(' &middot; 00:00', '', $text);
	}
	else if($format_type === 'currency') {
		$text = trim($text);
		
		// Currency must be numeric
		if(!is_numeric($text)) {
			return $text;
		}
		else if($text == 0) {
			return '-';
		}

		$result_text = '';
		$currency = isset($format_args['currency']) ? $format_args['currency'] : null;
		$currency_display = isset($format_args['currency_display']) ? $format_args['currency_display'] : 'symbol';

		$decimals = isset($format_args['decimals']) ? $format_args['decimals'] : ((bcmod(($text * 100), 100) <> 0) ? 2 : 0);
		
		$currency_symbols = array(
			'IDR' => 'Rp',
			'USD' => '$',
		);
		
		if($currency_display === 'symbol' && isset($currency_symbols[$currency])) {
			$result_text .= $currency_symbols[$currency] . ' ';
		}
		else if($currency_display === 'code') {
			$result_text .= strtoupper($currency) . ' ';
		}
		
		$result_text .= number_format($text, $decimals, NUMBER_DECIMAL_POINT, NUMBER_THOUSAND_SEPARATOR);
		
		$text = $result_text;
	}
	else if($format_type === 'percentage') {
		$text = trim($text);
		
		// Currency must be numeric
		if(!is_numeric($text)) {
			return $text;
		}
		else if($text == 0) {
			return '-';
		}
		else {
			return $text . '%';
		}
	}
	else if($format_type === 'datetime') {
		$datetime_format = $format_args['datetime_format'];
		
		if($datetime_format === 'date_with_full_month_name') {
			$datetime_format = 'j F Y';
		}
		
		$text = date($datetime_format, strtotime($text));
		
		$english_day_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		$indonesian_day_names = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
		
		$english_month_names = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		$indonesian_month_names = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
		
		if(PROJECT_LANGUAGE == 'indonesian') {
			$text = str_replace($english_day_names, $indonesian_day_names, $text);
			$text = str_replace($english_month_names, $indonesian_month_names, $text);
		}
		
		$text = trim(str_replace('00:00:00', '', $text));
		$text = trim(str_replace('00:00', '', $text));
	}
	else if($format_type === 'number') {
		$decimals = isset($format_args['decimals']) ? $format_args['decimals'] : 0;
		
		$text = number_format($text, $decimals, NUMBER_DECIMAL_POINT, NUMBER_THOUSAND_SEPARATOR);
	}
	
	return $text;
}

// Get configuration to show table list
// $type : body / js
// $parent_entity is used to filter data
// $parent_entity['entity_name'] : parent entity of the related entity
// $parent_entity['related_field'] : related field in the related entity
// $parent_entity['parent_id'] : data ID of the parent of the related entity
function get_datatable_config($type, $entity_name, $args = array()) {
	$CI = get_instance();
	$CI->load->model('model_db_crud');
	
	$data_filters = isset($args['data_filters']) ? $args['data_filters'] : array();
	$parent_entity = isset($args['parent_entity']) ? $args['parent_entity'] : array();
	$datatable_filters = isset($args['datatable_filters']) ? $args['datatable_filters'] : array();
			
	$entity_data = get_entity($entity_name);
	
	if($type === 'body') {
		$body_data = array();
		$body_data['entity_name'] = $entity_name;
		
		// Table headers
		$body_data['table_headers'] = array();
		$body_data['table_footers'] = array();
		
		// Add column for edit & delete button
		$body_data['table_headers'][] = '';
		$body_data['table_footers'][] = '';
		
		foreach($entity_data['fields'] as $field_name => $field_info) {
			// Exclude column that should not be shown in list
			if(!$CI->model_db_crud->user_can('view',$entity_name,0,array('field_name'=>$field_name))) {
				continue;
			}
			
			if(isset($field_info['visible']) && $field_info['visible'] === false) {
				continue;
			}
			if(isset($field_info['visible_in_list']) && $field_info['visible_in_list'] === false) {
				continue;
			}
			
			$field_label = $field_info['field_label'];	
			$body_data['table_headers'][] = $field_label;
			
			if(!(isset($field_info['searchable_in_list']) && $field_info['searchable_in_list'] === false)) {
				$body_data['table_footers'][] = array(
					'label' => $field_label,
					'format' => isset($field_info['format_type']) ? $field_info['format_type'] : null
				);
			}
			else {
				$body_data['table_footers'][] = array(
					'label' => null,
					'format' => null
				);
			}
		}
		
		// Column for metadata (created time, created user, last modified time, last modified user)
		$metadata_header_label = array(
			ucwords(lang('label__created_time')),
			ucwords(lang('label__created_by')),
			ucwords(lang('label__last_modified_time')),
			ucwords(lang('label__last_modified_by'))
		);
		$body_data['table_headers'] = array_merge($body_data['table_headers'], $metadata_header_label);
		$body_data['table_footers'] = array_merge($body_data['table_footers'], $metadata_header_label);
		
		return $body_data;
	}
	else if($type === 'js') {
		$js_data = array();
		
		$js_data['entity_name'] = $entity_name;
		$js_data['columns'] = array();
		$js_data['column_defs'] = array();
		
		// Show all data if parent entity is empty
		if(empty($parent_entity)) {
			if(empty($data_filters)) {
				$js_data['ajax_url'] = base_url('api/view_list/'.$entity_name);
			}
			else {
				$js_data['ajax_url'] = base_url('api/view_filtered_list/'.$entity_name.'/?'.http_build_query($data_filters));
			}
		}
		else {
			$parent_entity_name = $parent_entity['entity_name'];
			$related_field = $parent_entity['related_field'];
			$parent_id = $parent_entity['parent_id'];
			
			$data_filters[$related_field] = $parent_id;
			
			$js_data['ajax_url'] = base_url('api/view_filtered_list/'.$entity_name.'/?'.http_build_query($data_filters));
		}
		
		$fields = $entity_data['fields'];
		$column_no = array();
		$i = 0;
		
		// Column definition for the edit & delete button
		$column_def = array(
			'targets' => array($i++),
			'data' => 'e', // abbreviation of 'edit_and_delete_button'
			'width' => '50px',
			'orderable' => false
		);
		
		$js_data['column_defs'][] = json_encode($column_def);
		
		$parent_entity_table_name = '';
		if(!empty($parent_entity)) {
			$parent_entity_table_name = get_entity_info($parent_entity['entity_name'], 'table_name');
		}

		foreach($fields as $field_name => $field_info) {
			if(!$CI->model_db_crud->user_can('view',$entity_name,0,array('field_name'=>$field_name))) {
				continue;
			}
			
			// Field width, visibility, searchability
			$column_def = array();
			
			if(isset($field_info['format_type'])) {
				$style_template = get_style_template($field_info['format_type']);
				foreach($style_template as $style_type => $style_value) {
					if(!isset($field_info[$style_type])) {
						$field_info[$style_type] = $style_value;
					}
				}
			}
			
			// Exclude column that should not be shown in list
			if(isset($field_info['visible']) && $field_info['visible'] === false) {
				continue;
			}
			if(isset($field_info['visible_in_list']) && $field_info['visible_in_list'] === false) {
				continue;
			}

			$column_def['targets'] = array($i);
			$column_no[$field_name] = $i;
			
			if(isset($field_info['foreign_key'])) {
				$foreign_entity_data = get_entity($field_info['foreign_key']['entity_name']);
				
				$foreign_table_name = $field_name; // field_name as table alias
				$foreign_table_name_field = $foreign_entity_data['name_field'];
				
				$field_alias = $foreign_table_name . '__' . $foreign_table_name_field;
				
				// Make the parent entity column invisible in related table
				if(!empty($parent_entity) && $foreign_entity_data['table_name'] === $parent_entity_table_name) {
					$column_def['visible'] = false;
				}
			}
			else {
				$field_alias = $entity_data['table_name'] . '__' . $field_name;
			}
			
			$field_alias = get_field_alias($entity_name, $field_name);
			
			$column_def['data'] = get_shortened_field_alias($field_alias);
		
			if(isset($field_info['column_width'])) {
				$column_def['width'] = $field_info['column_width'];
				if(is_numeric($column_def['width'])) {
					$column_def['width'] .= 'px';
				}
			}
			else {
				$column_def['width'] = DEFAULT_COLUMN_WIDTH;
			}
			
			if(isset($field_info['column_hidden_in_list']) && $field_info['column_hidden_in_list'] === true) {
				$column_def['visible'] = false;
			}
			if(isset($field_info['searchable_in_list']) && $field_info['searchable_in_list'] === false) {
				$column_def['searchable'] = false;
			}
			
			// If columns_hidden is specified in related_field_info, hide those columns...
			if(isset($args['related_entity_info']['hidden_columns']) && in_array($field_name, $args['related_entity_info']['hidden_columns'])) {
				$column_def['visible'] = false;
			}
			
			if(isset($field_info['format_type'])) {
				if($field_info['format_type'] === 'currency') {
					$column_def['type'] = 'currency';
				}
			}
			
			if(isset($field_info['text_align'])) {
				if($field_info['text_align'] == 'center') {
					$column_def['class'] = 'dt-center';
				}
				else if($field_info['text_align'] == 'right') {
					$column_def['class'] = 'dt-right';
				}
			}

			$js_data['column_defs'][] = json_encode($column_def);
			
			$i++;
		}

		// Metadata visibility
		$metadata_visibility = config_item('default_metadata_visibility');
		$metadata_data = array('ct','cu','lt','lu');
		foreach($metadata_data as $data) {
			$column_def = array(
				'targets' => array($i++),
				'data' => $data,
				'width' => '120px',
				'visible' => $metadata_visibility
			);
			$js_data['column_defs'][] = json_encode($column_def);
		}
		
		// Data order
		$js_data['data_order'] = array();
		
		if(isset($args['related_entity_info']['data_order'])) {
			$entity_data['data_order'] = $args['related_entity_info']['data_order'];
		}
		
		if(isset($entity_data['data_order'])) {
			foreach($entity_data['data_order'] as $field => $order) {
				$col_index = 1;
				$field_found = false;
				foreach($fields as $field_name => $field_info) {
					if(isset($field_info['visible']) && $field_info['visible'] === false) {
						continue;
					}
					if(isset($field_info['visible_in_list']) && $field_info['visible_in_list'] === false) {
						continue;
					}
					
					if($field_name == $field) {
						$field_found = true;
						break;
					}
					
					$col_index++;
				}

				if($field_found) {
					$js_data['data_order'][] = "[".($col_index).",'".$order."'"."]";
				}
			}
		}
		
		// Datatable filter
		if(!empty($datatable_filters)) {
			$js_data['datatable_filters'] = array();
			foreach($datatable_filters as $field_name => $filter_value) {
				$js_data['datatable_filters'][] = array('column_no'=>$column_no[$field_name], 'filter_value'=>$filter_value);
			}
		}
		
		return $js_data;
	}
	
	return null;
}

// Get column list of the displayed list for DataTable
function get_displayed_list_columns($entity_name, $parent_entity_name = null) {
	$result = array();
	$column_data = array();
	
	// Edit and delete button
	$column_data[] = 'e';
	
	$fields = get_entity_info($entity_name, 'fields');
	
	foreach($fields as $field_name => $field_info) {
		$field_alias = get_field_alias($entity_name, $field_name);
		$column_data[] = get_shortened_field_alias($field_alias);
	}
	
	// Metadata (Created time, created user, last modified time, last modified user)
	$metadata = array('ct','cu','lt','lu');
	$column_data = array_merge($column_data, $metadata);

	foreach($column_data as $this_column_data) {
		$result[] = array('data' => $this_column_data);
	}
	
	return $result;
}

function get_rendered_required_field_sign() {
	return ' <span class="glyphicon glyphicon-asterisk" aria-hidden="true" style="color:orange;" data-toggle="tooltip" data-placement="top" title="' . ucfirst(lang('message__this_field_is_required')) . '"></span>';
}

function get_rendered_tooltip($lang_message, $use_lang = true) {
	$tooltip_title = $lang_message;
	if($use_lang === true) {
		$tooltip_title = lang($lang_message);
	}

	$tooltip_title = htmlspecialchars(ucfirst($tooltip_title));
	
	return ' <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="' . $tooltip_title . '"></span>';
}

function get_formatted_id_prefix_code($entity_name, $id) {
	return format_text($id, 'id_prefix_code', array('entity_name' => $entity_name));
}

function trim_long_text($text, $num_char = 100) {
	if(strlen($text) > $num_char) {
		$new_text = substr($text, 0, strpos($text, ' ', $num_char)) . ' ...';
		return $new_text;
	}
	
	return $text;
}

function get_rendered_action_option($entity_name, $data_id, $action_option_spec) {
	$result = '';

	if($action_option_spec['type'] == 'primary') {
		$anchor_tag = 'span';
		if(isset($action_option_spec['attr']['href']) && $action_option_spec['attr']['href'] <> '#') {
			$anchor_tag = 'a';
		}
		
		$label_class = isset($action_option_spec['label_class']) ? $action_option_spec['label_class'] : '';
	
		$result .= '<'.$anchor_tag.' role="button" ';
		if(empty($action_option_spec['attr']['class'])) {
			$result .= ' class="btn btn-default" ';
		}
		foreach($action_option_spec['attr'] as $attr => $value) {
			$result .= ' ' . $attr . '="'. addslashes($value) . '"';
		}
		$result .= ' entity="'.$entity_name.'" data_id="'.$data_id.'" id="action-btn-'.strtolower(str_replace(' ', '_', $action_option_spec['label'])).'">';
		$result .= '<span class="glyphicon glyphicon-'.$action_option_spec['glyphicon'].'" aria-hidden="true"></span><span class="action-btn-label-area '.$label_class.'">&nbsp;&nbsp;&nbsp;<span id="action-btn-label-'.strtolower(str_replace(' ', '_', $action_option_spec['label'])).'">'.$action_option_spec['label'].'</span></span></'.$anchor_tag.'> ';
	}
	else if($action_option_spec['type'] == 'other') {
		if($action_option_spec['label'] == '{SEPARATOR}') {
			$result .= '<li role="separator" class="divider"></li>';
		}
		else {
			$result .= '<li';
			$result .= (!empty($action_option_spec['attr']['disabled'])) ? ' class="disabled">' : '>';
			$result .= '<a ';
			foreach($action_option_spec['attr'] as $attr => $value) {
				$result .= ' ' . $attr . '="'. addslashes($value) . '"';
			}
			$result .= ' entity="'.$entity_name.'" data_id="'.$data_id.'">';
			$result .= '<span class="glyphicon glyphicon-'.$action_option_spec['glyphicon'].'" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;'.$action_option_spec['label'];
			$result .= '</a></li>';
		}
	}
	
	return $result;
}

function debug_var($var, $label = '') {
	log_message('error', $label . ': ' . print_r($var, true));
}

function in_changed_fields($changed_fields, $searched_changed_fields) {
	if(!is_array($searched_changed_fields)) {
		$searched_changed_fields = array($searched_changed_fields);
	}
	
	if(!empty(array_intersect(array_keys($changed_fields), $searched_changed_fields))) {
		return true;
	}
	else {
		return false;
	}
}

function get_style_template($format_type) {
	$styles_template = config_item('styles_template');
	if(isset($styles_template[$format_type])) {
		return $styles_template[$format_type];
	}
	else {
		return array();
	}
}

function get_field_alias($entity_name, $field_name) {
	$entity_data = get_entity($entity_name);
	$table_name = $entity_data['table_name'];
	
	$field_info = $entity_data['fields'][$field_name];
	
	if(!empty($field_info['foreign_key'])) {
		$foreign_entity_name = $field_info['foreign_key']['entity_name'];
		$foreign_entity_data = get_entity($foreign_entity_name);
		$foreign_table_name = $field_name; // field_name as table alias
		
		if(isset($field_info['foreign_key']['name_field'])) {
			$field_alias = $foreign_table_name . '__' . $field_info['foreign_key']['name_field'];
		}
		else {
			$field_alias = $foreign_table_name . '__' . $foreign_entity_data['name_field'];
		}
	}
	else {
		$field_alias = $table_name . '__' . $field_name;
	}
	
	return $field_alias;
}

function get_shortened_field_name($table_name, $field_name) {
	return substr(md5($table_name . '__' . $field_name),1,HASHED_FIELD_NAME_LENGTH);
}

function get_shortened_field_alias($field_alias) {
	return substr(md5($field_alias),1,HASHED_FIELD_NAME_LENGTH);
}

function get_status_color($options_type, $status) {
	$options_labels = config_item('options_labels');
	if(isset($options_labels[$options_type][$status]['color'])) return $options_labels[$options_type][$status]['color'];
	else return 'default';
}

function convert_timezone($time, $from_timezone, $to_timezone) {
	$old_timezone = new DateTimeZone($from_timezone);
	$datetime = new DateTime($time, $old_timezone);
	$new_timezone = new DateTimeZone($to_timezone);
	$datetime->setTimezone($new_timezone);
	return $datetime->format('Y-m-d H:i:s');
}

?>