<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget_employee_evolution_points_breakdown {
	protected $CI;
	
	protected $widget_data = array();
	protected $widget_js_data = array();
	
	public function __construct(){
		$this->CI =& get_instance();
    }
	
	public function set_widget_specs($widget_specs) {
		$data = array();
		
		$employee_id = $widget_specs['data_id'];
		
		$this->CI->load->model(PROJECT_CODE.'/model_assignment');
		
		$data['employee_evolution_points_breakdown_data'] = $this->CI->model_assignment->get_employee_evolution_points_breakdown_widget_data($employee_id);
		
		$this->widget_data = $data;
	}

	public function get_rendered_widget() {
		return $this->CI->load->view(PROJECT_CODE.'/widgets/view_widget_employee_evolution_points_breakdown', $this->widget_data, true);
	}
	
	public function get_rendered_widget_js() {
		return null;
	}
}
