<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_report_config extends CI_Model {
	public $report_types;
	
	public $options_template = array(
		'period' => array(
			'label' => 'Period',
			'input_type' => 'select',
			'select_type' => 'basic',
			'options' => array(
				'today' => array('label' => 'Today'),
				'yesterday' => array('label' => 'Yesterday'),
				'this_week' => array('label' => 'This Week'),
				'last_week' => array('label' => 'Last Week'),
				'last_seven_days' => array('label' => 'Last 7 Days'),
				'this_month' => array('label' => 'This Month'),
				'last_month' => array('label' => 'Last Month'),
				'last_thirty_days' => array('label' => 'Last 30 Days'),
				'last_three_months' => array('label' => 'Last 3 Months'),
				'this_year' => array('label' => 'This Year'),
				'last_year' => array('label' => 'Last Year'),
				'all_time' => array('label' => 'All Time')
			),
			'option_default' => 'this_month'
		),
		'comparison_period' => array(
			'label' => 'Compare To',
			'input_type' => 'select',
			'select_type' => 'basic',
			'options' => array(
				'previous_period' => 'Previous Period',
				'previous_year' => 'Previous Year'
			)
		)
	);
	
	public $card_config_template = array(

	);
	
	public $query_specs_variables = array();
	
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
		
		$this->report_types = array();
	}
}