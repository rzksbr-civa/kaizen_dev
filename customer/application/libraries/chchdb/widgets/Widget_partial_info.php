<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget_partial_info {
	protected $CI;
	
	protected $widget_data = array();
	protected $widget_js_data = array();
	
	public function __construct(){
		$this->CI =& get_instance();
    }
	
	public function set_widget_specs($widget_specs) {
		$data = $widget_specs;
		
		$data['fields_alias'] = array();
		foreach($widget_specs['fields'] as $field) {
			$data['fields_alias'][] = get_field_alias($widget_specs['data_entity'], $field);
		}
		
		$entity_name = $data['entity_name'];
		$data_id = $data['data_id'];
		
		$this->CI->load->model('model_db_crud');
		if(!$this->CI->model_db_crud->user_can('view', $entity_name, $data_id)) {
			return null;
		}
		
		$data['parent_data_id'] = $data['data_id'];
		if($data['info_type'] == 'list') {
			$data['data_id'] = null;
		}
		
		if($widget_specs['info_type'] == 'info') {
			$data['info'] = $this->CI->model_db_crud->get_view_detail_info_data($entity_name, $data_id, $data['fields']);
			
			$data['user_can_edit'] = $this->CI->model_db_crud->user_can('edit', $entity_name, $data_id) ? true : false;
		}
		else if($widget_specs['info_type'] == 'list') {
			$list_entity_name = $widget_specs['data_entity'];
			
			$data['user_can_add'] = $this->CI->model_db_crud->user_can('add', $list_entity_name) ? true : false;
			
			$data['list'] = array(
				'header' => null,
				'content' => null
			);
			
			// Get list header
			$list_entity_data = get_entity($list_entity_name);
			$fields_info = $list_entity_data['fields'];
			foreach($widget_specs['displayed_fields'] as $displayed_field_name) {
				$key = array_search($displayed_field_name, array_column($list_entity_data['displayed_fields'], 'field_name'));
				$field_label = $list_entity_data['displayed_fields'][$key]['field_label'];
				
				if($field_label == '{default}') {
					$field_parts = explode('.', $displayed_field_name);
					$data['list']['header'][] = $fields_info[$field_parts[1]]['field_label'];
				}
				else {
					$data['list']['header'][] = $field_label;
				}
			}
			
			// Get list content
			$data['list']['content'] = $this->CI->model_db_crud->get_data_list($list_entity_name, array($widget_specs['connected_id_field'] => $data_id));
		}
		
		$data['col_layout'] = isset($widget_specs['col_layout']) ? $widget_specs['col_layout'] : 'col-md-6';
		
		$this->widget_data = $data;
	}

	public function get_rendered_widget() {
		return $this->CI->load->view('chchdb/widgets/view_widget_partial_info', $this->widget_data, true);
	}
	
	public function get_rendered_widget_js() {
		return null;
	}
}
