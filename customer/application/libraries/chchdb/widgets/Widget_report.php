<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget_report {
	protected $CI;
	
	protected $widget_data = array();
	protected $widget_js_data = array();
	
	protected $widget_specs = array();
	
	protected $show_report_options_control = false;
	protected $show_report_result = true;
	
	// Options Control
	protected $show_report_type_options = false;
	
	protected $report_type = null;
	protected $report_title = null;
	protected $report_args = array();
	
	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->model('model_report');
    }
	
	public function set_widget_specs($widget_specs) {
		$this->widget_specs = $widget_specs;
	}
	
	public function set_show_report_options_control($value) {
		$this->show_report_options_control = ($value === true) ? true : false;
	}
	
	public function set_show_report_result($value) {
		$this->show_report_result = ($value === true) ? true : false;
	}
	
	public function set_show_report_type_options($value) {
		$this->show_report_type_options = ($value === true) ? true : false;
	}
	
	public function set_report_type($report_type) {
		$this->report_type = $report_type;
		$this->set_report_title($this->get_report_title_from_report_types($report_type));
	}
	
	public function set_report_title($report_title) {
		$this->report_title = $report_title;
	}
	
	public function set_report_args($report_args) {
		$this->report_args = $report_args;
		
		if(isset($report_args['report_type'])) {
			$this->set_report_type($report_args['report_type']);
		}
	}
	
	public function get_report_title() {
		return $this->report_title;
	}
	
	public function get_report_subtitle() {
		return $this->CI->model_report->get_report_subtitle($this->report_args);
	}
	
	public function get_report_args() {
		return $this->report_args;
	}

	public function get_rendered_widget() {
		$data = array();
		$widget_specs = $this->widget_specs;
		
		if(isset($this->report_type) && !$this->user_can_view_report($this->report_type)) {
			return '<h1>'.lang('message__access_denied').'</h1>';
		}
		
		if(!isset($widget_specs['report_args'])) {
			$widget_specs['report_args'] = $this->get_report_args();
		}
		
		if($this->show_report_options_control) {
			$options_data = array();
			$options_data['report_type'] = $this->report_type;
		
			// Option to show "report type options" or not...
			$options_data['show_report_type_options'] = $this->show_report_type_options;
			
			if($options_data['show_report_type_options']) {
				$options_data['report_types'] = $this->get_report_types();
			}
			
			$options_data['options_parameter'] = $this->CI->model_report->get_report_options_parameter($this->get_report_args());
			
			$view_report_options_control = $this->CI->load->view('chchdb/widgets/view_widget_report_options_control', $options_data, true);
		}
		
		if($this->show_report_result) {
			$result_data = array();
		
			$result_data['report_type'] = $this->report_type;
			$result_data['report_title'] = $this->report_title;
			$result_data['report_subtitle'] = $this->CI->model_report->get_report_subtitle($widget_specs['report_args']);
			
			$result_data['report_result'] = $this->CI->model_report->get_report_result($widget_specs, 'body');
				
			$view_report_options_result = $this->CI->load->view('chchdb/widgets/view_widget_report_result', $result_data, true);
		}
		
		if($this->show_report_options_control && $this->show_report_result) {
			return '
				<div class="row">
					<div class="col-md-3">
						'.$view_report_options_control.'
					</div>

					<div class="col-md-9">
						'.$view_report_options_result.'
					</div>
				</div>
			';
		}
		else if($this->show_report_options_control) {
			return $view_report_options_control;
		}
		else {
			return $view_report_result;
		}
	}
	
	public function get_rendered_widget_js() {
		$widget_specs = $this->widget_specs;
		
		if(!isset($widget_specs['report_args'])) {
			$widget_specs['report_args'] = $this->report_args;
		}
		$js_data = array();
		
		if(isset($widget_specs['report_args']['report_type'])) {
			$js_data = $this->CI->model_report->get_report_result($widget_specs, 'js');
		}
		
		return $this->CI->load->view('chchdb/widgets/js_view_widget_report', $js_data, true);
	}
	
	public function get_report_types() {
		$report_types = $this->CI->model_report->get_all_report_types();
		
		$permitted_report_types = array();
		foreach($report_types as $report_type_name => $report_type_info) {
			if($this->user_can_view_report($report_type_name) && (!isset($report_type_info['main_report']) || $report_type_info['main_report'] === true)) {
				$permitted_report_types[$report_type_name] = $report_type_info;
			}
		}
		return $permitted_report_types;
	}
	
	public function report_type_exists($report_type) {
		$report_types = $this->CI->model_report->get_all_report_types();
		return array_key_exists($report_type, $report_types);
	}
	
	public function get_report_type_info($report_type) {
		if($this->report_type_exists($report_type)) {
			$report_types = $this->CI->model_report->get_all_report_types();
			return $report_types[$report_type];
		}
		else {
			return null;
		}
	}
	
	public function get_report_title_from_report_types($report_type) {
		$report_type_info = $this->get_report_type_info($report_type);
		
		if(!empty($report_type_info)) {
			return $report_type_info['title'];
		}
		else {
			return ucwords(lang('word__report'));
		}
	}
	
	public function user_can_view_report($report_type) {
		$report_type_info = $this->get_report_type_info($report_type);
		
		if(empty($report_type_info)) {
			return false;
		}

		if($this->CI->session->userdata('chchdb_'.PROJECT_CODE.'_user_level') >= $report_type_info['required_user_level']) {
			return true;
		}
		else {
			return false;
		}
	}
}
