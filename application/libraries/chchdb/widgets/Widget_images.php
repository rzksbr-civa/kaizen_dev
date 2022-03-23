<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget_images {
	protected $CI;
	
	protected $widget_data = array();
	protected $widget_js_data = array();
	
	public function __construct(){
		$this->CI =& get_instance();
    }

	public function set_widget_specs($widget_specs) {
		// Default value	
		$entity_name = isset($widget_specs['entity_name']) ? $widget_specs['entity_name'] : null;
		$data_id = isset($widget_specs['data_id']) ? $widget_specs['data_id'] : null;
		
		$data = array();
		
		$data['entity_name'] = $entity_name;
		$data['data_id'] = $data_id;
		
		$data['widget_title'] = isset($widget_specs['widget_title']) ? $widget_specs['widget_title'] : ucwords(lang('title__images'));
		
		$data['images'] = $this->CI->db
			->select('*')
			->from('chchdb_files')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $this->CI->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where('entity_name', $entity_name)
			->where('ref_id', $data_id)
			->get()->result_array();
		
		$this->widget_data = $data;
	}
	
	public function get_rendered_widget() {
		return $this->CI->load->view('chchdb/widgets/view_widget_images', $this->widget_data, true);
	}
	
	public function get_rendered_widget_js() {
		return $this->CI->load->view('chchdb/widgets/js_view_widget_images', $this->widget_data, true);
	}
}
