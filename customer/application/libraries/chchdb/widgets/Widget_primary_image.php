<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget_primary_image {
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
		
		$this->CI->load->model('model_db_crud');
		$product_data = $this->CI->model_db_crud->get_specific_data($entity_name, $data_id);
		
		$data = array();
		$data['barcode'] = $product_data['barcode'];
		
		$this->widget_data = $data;
	}

	public function get_rendered_widget($widget_specs = array()) {
		return $this->CI->load->view('chchdb/widgets/view_widget_primary_image', $this->widget_data, true);
	}
	
	public function get_rendered_widget_js() {
		return null;
	}
}
