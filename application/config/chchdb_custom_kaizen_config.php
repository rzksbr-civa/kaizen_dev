<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* OVERWRITTEN CONFIG */

date_default_timezone_set('US/Eastern');

/* END OF OVERWRITTEN CONFIG */

// CONSTANTS

// Number format
define('NUMBER_THOUSAND_SEPARATOR', ',');
define('NUMBER_DECIMAL_POINT', '.');

define('BOOTSTRAP_THEME', 'cyborg');

// Define user roles
define('USER_ROLE_ALL', 0);
define('USER_ROLE_ADMIN', 1);
define('USER_ROLE_ADMIN_WITH_FINANCIAL', 2);

$config['header_menu'] = array(
	array('type' => 'link', 'label' => 'AC', 'link' => 'db/view/arc'),
	array(
		'type'    => 'dropdown',
		'label'   => 'Settings',
		'submenu' => array (
			array('type' => 'link', 'label' => 'Abnormal Types', 'link' => 'db/view/abnormal_type'),
			array('type' => 'link', 'label' => 'Assignment Types', 'link' => 'db/view/assignment_type'),
			array('type' => 'link', 'label' => 'Attendance Types', 'link' => 'db/view/attendance_type'),
			array('type' => 'link', 'label' => 'Block Times', 'link' => 'db/view/block_time'),
			array('type' => 'link', 'label' => 'Break Times', 'link' => 'db/view/break_time'),
			array('type' => 'link', 'label' => 'Carriers', 'link' => 'db/view/carrier'),
			array('type' => 'link', 'label' => 'Customers', 'link' => 'db/view/customer'),
			array('type' => 'link', 'label' => 'Departments', 'link' => 'db/view/department'),
			array('type' => 'link', 'label' => 'Employees', 'link' => 'db/view/employee'),
			array('type' => 'link', 'label' => 'Employee Shift Types', 'link' => 'db/view/employee_shift_type'),
			array('type' => 'link', 'label' => 'Team Assignments', 'link' => 'kaizen/assignment'),
			array('type' => 'link', 'label' => 'Facilities', 'link' => 'db/view/facility'),
			array('type' => 'link', 'label' => 'Attendance', 'link' => 'db/view/attendance'),
			array('type' => 'link', 'label' => 'Reward', 'link' => 'db/view/reward'),
			array('type' => 'link', 'label' => 'Metrics Board Note', 'link' => 'db/view/metrics_board_note')
		)
	),
	array(
		'type'    => 'dropdown',
		'label'   => 'Report',
		'submenu' => array (
			array('type' => 'link', 'label' => 'AC KPI\'s', 'link' => 'kaizen/report/arc_kpi_graph'),
			array('type' => 'link', 'label' => 'AC Trend', 'link' => 'kaizen/report/arc_trend_graph'),
			array('type' => 'link', 'label' => 'Monthly AC Data Table', 'link' => 'kaizen/report/monthly_ac_data_table'),
			//array('type' => 'link', 'label' => 'Outbound Performance KPI by Status', 'link' => 'kaizen/report/outbound_performance_kpi/by/status'),
			array('type' => 'link', 'label' => 'Outbound Performance KPI by User', 'link' => 'kaizen/report/outbound_performance_kpi/by/user'),
			array('type' => 'link', 'label' => 'Shipment', 'link' => 'kaizen/report/shipment'),
			array('type' => 'link', 'label' => 'Work Summary', 'link' => 'kaizen/report/work_summary'),
			array('type' => 'link', 'label' => 'Action Log Data Error', 'link' => 'kaizen/report/action_log_data_error'),
			array('type' => 'link', 'label' => 'Inventory', 'link' => 'kaizen/report/inventory'),
			array('type' => 'link', 'label' => 'Client Complexity And Profitability', 'link' => 'kaizen/report/client_complexity_and_profitability', 'hide_from_user_role' => array(USER_ROLE_ADMIN)),
			array('type' => 'link', 'label' => 'Utilization Metrics', 'link' => 'kaizen/report/utilization_metrics'),
		)
	),
	array('type' => 'link', 'label' => 'Scoreboard', 'link' => 'kaizen/scoreboard'),
	array('type' => 'link', 'label' => 'Takt Board', 'link' => 'kaizen/takt_board'),
	array('type' => 'link', 'label' => 'Package Board', 'link' => 'kaizen/package_board', 'hide_from_user_role' => array(USER_ROLE_ADMIN)),
	array(
		'type'    => 'dropdown',
		'label'   => 'Team',
		'submenu' => array (
			array('type' => 'link', 'label' => 'ACs Idle Board', 'link' => 'kaizen/acs_idle_board'),
			array('type' => 'link', 'label' => 'Client Support Board', 'link' => 'kaizen/client_support_board'),
			array('type' => 'link', 'label' => 'Countdown Board', 'link' => 'kaizen/countdown_board'),
			array('type' => 'link', 'label' => 'Metrics Board', 'link' => 'kaizen/metrics_board'),
			array('type' => 'link', 'label' => 'Status Board', 'link' => 'kaizen/idle_status_board'),
			array('type' => 'link', 'label' => 'Team Helper Board', 'link' => 'kaizen/team_helper_board'),
			array('type' => 'link', 'label' => 'Idle Status Board', 'link' => 'kaizen/idle_status_board'),
			array('type' => 'link', 'label' => 'Idle Break Board', 'link' => 'kaizen/idle_break_board'),
		)
	),
	array(
		'type'    => 'dropdown',
		'label'   => 'Inventory',
		'submenu' => array (
			array('type' => 'link', 'label' => 'Client Inventory Optimization Board', 'link' => 'kaizen/client_inventory_replenishment_board'),
			array('type' => 'link', 'label' => 'Inventory Counts Board', 'link' => 'kaizen/inventory_counts_board'),
			array('type' => 'link', 'label' => 'Inventory Board', 'link' => 'kaizen/inventory_board'),
			array('type' => 'link', 'label' => 'Live Drops Board', 'link' => 'kaizen/live_drops_board'),
			array('type' => 'link', 'label' => 'Long Term Inventory Counts Board', 'link' => 'kaizen/long_term_inventory_counts_board'),
			array('type' => 'link', 'label' => 'Replenishment Release Board', 'link' => 'kaizen/replenishment_release_board'),
		)
	),
	array(
		'type'    => 'dropdown',
		'label'   => 'More Board',
		'submenu' => array (
			array('type' => 'link', 'label' => 'Batching Helper Board', 'link' => 'kaizen/batching_helper_board'),
			array('type' => 'link', 'label' => 'Carrier Diversification Board', 'link' => 'kaizen/carrier_diversification_board'),
			array('type' => 'link', 'label' => 'Carrier Optimization Board', 'link' => 'kaizen/carrier_optimization_board', 'hide_from_user_role' => array(USER_ROLE_ADMIN)),
			array('type' => 'link', 'label' => 'Carton Utilization Board', 'link' => 'kaizen/carton_utilization_board'),
			array('type' => 'link', 'label' => 'Empty Spots Board', 'link' => 'kaizen/empty_spots_board'),
			array('type' => 'link', 'label' => 'Idle Manifest Board', 'link' => 'kaizen/idle_manifest_board'),
			array('type' => 'link', 'label' => 'Idle Picking Batch Board', 'link' => 'kaizen/idle_picking_batch_board'),
			array('type' => 'link', 'label' => 'Inbound Board', 'link' => 'kaizen/inbound_board'),
			array('type' => 'link', 'label' => 'Inbound Idle Time Board', 'link' => 'kaizen/inbound_idle_time_board'),
			array('type' => 'link', 'label' => 'Kaizen Manager', 'link' => 'kaizen/kaizen_manager'),
			array('type' => 'link', 'label' => 'Loading Andon Board', 'link' => 'kaizen/loading_andon_board'),
			array('type' => 'link', 'label' => 'Loading Utilization Board', 'link' => 'kaizen/loading_utilization_board'),
			array('type' => 'link', 'label' => 'Package Status Board', 'link' => 'kaizen/package_status_board'),
			array('type' => 'link', 'label' => 'Packages By Date X Location X Carrier Board', 'link' => 'kaizen/packages_by_date_location_carrier_board'),
			array('type' => 'link', 'label' => 'Packages By Month Board', 'link' => 'kaizen/packages_by_month_board'),
			array('type' => 'link', 'label' => 'Packages By Week Board', 'link' => 'kaizen/packages_by_week_board'),
			array('type' => 'link', 'label' => 'Revenue Board', 'link' => 'kaizen/revenue_board', 'hide_from_user_role' => array(USER_ROLE_ADMIN)),
			array('type' => 'link', 'label' => 'SLA Board', 'link' => 'kaizen/sla_board'),
			array('type' => 'link', 'label' => 'Trailer Utilization Forecast Board', 'link' => 'kaizen/trailer_utilization_forecast_board'),	
		)
	),
	array(
		'type'    => 'dropdown',
		'label'   => 'Tools',
		'submenu' => array (
			array('type' => 'link', 'label' => 'Update Data', 'link' => 'kaizen/update'),
			// array('type' => 'link', 'label' => 'AC Record Screen', 'link' => 'kaizen/ac_record_screen')
		)
	),
);

$config['db_structure'] = array(
	// Entity abnormal_type
	'abnormal_type' =>
	array(
		'table_name'  			=> 'abnormal_types',
		'label_singular' 		=> 'abnormal type',
		'label_plural'			=> 'abnormal types',
		'data_order'		    => array('abnormal_type_name' => 'asc'),
		'id_prefix_code'		=> 'ABT',
		'name_field'			=> 'abnormal_type_name',
		'related_entities'			=> 
			array(
				// Related entity: ARC
				array(
					'entity_name'	 => 'arc',
					'related_field'  => 'abnormal_type'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'abnormal_type_name' =>
				array('field_label' 	=> 'Abnormal Type Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'is_controllable' =>
				array('field_label' 	=> 'Is Controllable?', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'options_type'	=> 'true_false',
					  'column_width'	=> 50,
					  'format_type'		=> 'status'
				),
				
				'does_affect_rmp' =>
				array('field_label' 	=> 'Does Affect RMP?', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'options_type'	=> 'true_false',
					  'column_width'	=> 50,
					  'format_type'		=> 'status'
				),
				
				'default_caused_by_points' =>
				array('field_label' 	=> 'Default Caused By Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'min_number'		=> 0,
					  'column_width'	=> 80,
					  'tooltip'			=> 'How much devolution points should be deducted from those who caused this AC type by default'
				),
				
				'default_discovered_by_points' =>
				array('field_label' 	=> 'Default Discovered By Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'min_number'		=> 0,
					  'column_width'	=> 80,
					  'tooltip'			=> 'How much evolution points should be added to those who discovered this AC type by default'
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),

	// Entity ARC
	'arc' =>
	array(
		'table_name'  			=> 'arc',
		'label_singular' 		=> 'AC',
		'label_plural'			=> 'AC',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'A',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'tabs_widgets' => array(
			array(
				'tab_name'  => 'info',
				'widgets'   => array(
					array(
						'widget_type' => 'core', 
						'widget_name' => 'widget_images',
						'widget_specs' => array(
							
						)
					)
				)
			)
		),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'date' =>
				array('field_label' 	=> 'Date', 
					  'field_data_type' => 'date',
					  'input_type'		=> 'date',
					  'default_value'	=> '{TODAY}',
					  'required'		=> true,
					  'column_width'	=> 70,
					  'text_align'		=> 'center'
				),
				
				'time' =>
				array('field_label' 	=> 'Time', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'column_width'	=> 60,
					  'text_align'		=> 'center',
					  'validation'		=> 'time',
					  'placeholder'		=> 'HH:MM'
				),
				
				'datetime' =>
				array('field_label' 	=> 'Datetime', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'column_width'	=> 70,
					  'visible_in_edit'	=> false,
					  'column_hidden_in_list' => true
				),
				
				'customer' =>
				array('field_label' 	=> 'Customer Name', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'customer',
							  'data_order'     => 'customer_name asc'
						),
					  'required'		=> true
				),
				
				'carrier' =>
				array('field_label' 	=> 'Carrier Name', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'carrier',
							  'data_order'     => 'carrier_name asc'
						)
				),
				
				'order_no' =>
				array('field_label' 	=> 'Order No.', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'column_width'	=> 100
				),
				
				'facility' =>
				array('field_label' 	=> 'Facility', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'facility',
							  'data_order'     => 'facility_name asc'
						)
				),
				
				'abnormal_type' =>
				array('field_label' 	=> 'Abnormal Type', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'abnormal_type',
							  'data_order'     => 'abnormal_type_name asc'
						)
				),
				
				'department' =>
				array('field_label' 	=> 'Department', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'department',
							  'data_order'     => 'department_name asc'
						)
				),
				
				'is_rc_sent' =>
				array('field_label' 	=> 'AC Sent', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'required'		=> true,
					  'selectized'		=> true,
					  'options_type'	=> 'yes_no',
					  'column_width'	=> 50,
					  'format_type'		=> 'option_label',
					  'default_value'	=> 'yes',
					  'text_align'		=> 'center'
				),
				
				'is_rc_closed' =>
				array('field_label' 	=> 'AC Closed', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'required'		=> true,
					  'selectized'		=> true,
					  'options_type'	=> 'yes_no',
					  'column_width'	=> 50,
					  'format_type'		=> 'option_label',
					  'default_value'	=> 'yes',
					  'text_align'		=> 'center'
				),
				
				'is_order_cancelled' =>
				array('field_label' 	=> 'Order Cancelled', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'required'		=> true,
					  'selectized'		=> true,
					  'options_type'	=> 'yes_no',
					  'column_width'	=> 70,
					  'format_type'		=> 'option_label',
					  'default_value'	=> 'yes',
					  'text_align'		=> 'center'
				),
				
				'is_customer_affected' =>
				array('field_label' 	=> 'Customer Affected', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'required'		=> true,
					  'selectized'		=> true,
					  'options_type'	=> 'yes_no',
					  'column_width'	=> 70,
					  'format_type'		=> 'option_label',
					  'text_align'		=> 'center'
				),
				
				'is_customer_charged' =>
				array('field_label' 	=> 'Customer Charged', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'required'		=> true,
					  'selectized'		=> true,
					  'options_type'	=> 'yes_no',
					  'column_width'	=> 70,
					  'format_type'		=> 'option_label',
					  'text_align'		=> 'center'
				),
				
				'issue' =>
				array('field_label' 	=> 'Issue', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'textarea_num_rows' => 6,
					  'column_width'	=> 300
				),
				
				'discovered_by' =>
				array('field_label' 	=> 'Discovered By', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee',
							  'data_order'     => 'employee_name asc'
						)
				),
				
				'caused_by' =>
				array('field_label' 	=> 'Caused By', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee',
							  'data_order'     => 'employee_name asc'
						)
				),
				
				'discovered_by_points' =>
				array('field_label' 	=> 'Discovered By Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'caused_by_points' =>
				array('field_label' 	=> 'Caused By Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'root_cause' =>
				array('field_label' 	=> 'Root Cause', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'textarea_num_rows' => 6,
					  'column_width'	=> 300
				),
				
				'counter_measure' =>
				array('field_label' 	=> 'Counter Measure', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'textarea_num_rows' => 6,
					  'column_width'	=> 300
				),
				
				'resolved_by' =>
				array('field_label' 	=> 'Resolved By', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'column_width'	=> 100
				),
				
				'ac_hours' =>
				array('field_label' 	=> 'AC Hours', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80,
					  'text_align'		=> 'center'
				),
				
				'ac_cost' =>
				array('field_label' 	=> 'AC Cost', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'format_type'		=> 'currency',
					  'format_args'		=> array('currency' => 'USD'),
					  'column_width'	=> 100
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity assignment_types
	'assignment_type' =>
	array(
		'table_name'  			=> 'assignment_types',
		'label_singular' 		=> 'assignment type',
		'label_plural'			=> 'assignment types',
		'data_order'		    => array('assignment_type_name' => 'asc'),
		'id_prefix_code'		=> 'AST',
		'name_field'			=> 'assignment_type_name',
		'related_entities'			=> 
			array(
				// Related entity: assignment
				array(
					'entity_name'	 => 'assignment',
					'related_field'  => 'assignment_type'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'assignment_type_name' =>
				array('field_label' 	=> 'Assignment Type Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'evolution_point_factor' =>
				array('field_label' 	=> 'Evolution Point Factor', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'default_value' 	=> 1,
					  'column_width'	=> 80
				),
				
				'label_printer_prefix' =>
				array('field_label' 	=> 'Label Printer Prefix', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text'
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity assignments
	'assignment' =>
	array(
		'table_name'  			=> 'assignments',
		'label_singular' 		=> 'assignment',
		'label_plural'			=> 'assignments',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'ASG',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'date' =>
				array('field_label' 	=> 'Date', 
					  'field_data_type' => 'date',
					  'input_type'		=> 'date',
					  'default_value'	=> '{TODAY}',
					  'required'		=> true,
					  'column_width'	=> 70,
					  'text_align'		=> 'center'
				),
				
				'shift' =>
				array('field_label' 	=> 'Block Time', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'block_time',
							  'data_order'     => 'block_time_name asc'
						)
				),
				
				'employee' =>
				array('field_label' 	=> 'Employee', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee',
							  'data_order'     => 'employee_name asc'
						)
				),
				
				'assignment_type' =>
				array('field_label' 	=> 'Assignment Type', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'assignment_type',
							  'data_order'     => 'assignment_type_name asc'
						)
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity attendance_types
	'attendance_type' =>
	array(
		'table_name'  			=> 'attendance_types',
		'label_singular' 		=> 'attendance type',
		'label_plural'			=> 'attendance types',
		'data_order'		    => array('attendance_type_name' => 'asc'),
		'id_prefix_code'		=> 'ATT',
		'name_field'			=> 'attendance_type_name',
		'related_entities'			=> 
			array(
				// Related entity: attendance
				array(
					'entity_name'	 => 'attendance',
					'related_field'  => 'attendance_type'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'attendance_type_name' =>
				array('field_label' 	=> 'Attendance Type Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'default_devolution_points' =>
				array('field_label' 	=> 'Default Devolution Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity attendance
	'attendance' =>
	array(
		'table_name'  			=> 'attendance',
		'label_singular' 		=> 'attendance',
		'label_plural'			=> 'attendance',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'ATD',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'date' =>
				array('field_label' 	=> 'Date', 
					  'field_data_type' => 'date',
					  'input_type'		=> 'date',
					  'default_value'	=> '{TODAY}',
					  'required'		=> true,
					  'column_width'	=> 70,
					  'text_align'		=> 'center'
				),
				
				'employee' =>
				array('field_label' 	=> 'Employee', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee',
							  'data_order'     => 'employee_name asc'
						)
				),
				
				'attendance_type' =>
				array('field_label' 	=> 'Attendance Type', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'attendance_type',
							  'data_order'     => 'id asc'
						)
				),
				
				'devolution_points' =>
				array('field_label' 	=> 'Devolution Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity block_times
	'block_time' =>
	array(
		'table_name'  			=> 'block_times',
		'label_singular' 		=> 'block time',
		'label_plural'			=> 'block times',
		'data_order'		    => array('block_time_name' => 'asc'),
		'id_prefix_code'		=> 'BT',
		'name_field'			=> 'block_time_name',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'block_time_no' =>
				array('field_label' 	=> 'Block Time No.', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'block_time_name' =>
				array('field_label' 	=> 'Block Time Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'start_time' =>
				array('field_label' 	=> 'Start Time', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'end_time' =>
				array('field_label' 	=> 'End Time', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity break_time
	'break_time' =>
	array(
		'table_name'  			=> 'break_times',
		'label_singular' 		=> 'break time',
		'label_plural'			=> 'break times',
		'data_order'		    => array('break_time_no' => 'asc'),
		'id_prefix_code'		=> 'BR',
		'name_field'			=> 'break_time_name',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'break_time_no' =>
				array('field_label' 	=> 'Break Time No.', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'break_time_name' =>
				array('field_label' 	=> 'Break Time Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'start_time' =>
				array('field_label' 	=> 'Start Time', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'end_time' =>
				array('field_label' 	=> 'End Time', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity carrier
	'carrier' =>
	array(
		'table_name'  			=> 'carriers',
		'label_singular' 		=> 'carrier',
		'label_plural'			=> 'carriers',
		'data_order'		    => array('carrier_name' => 'asc'),
		'id_prefix_code'		=> 'CAR',
		'name_field'			=> 'carrier_name',
		'related_entities'			=> 
			array(
				// Related entity: ARC
				array(
					'entity_name'	 => 'arc',
					'related_field'  => 'carrier'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'carrier_name' =>
				array('field_label' 	=> 'Carrier Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity customer
	'customer' =>
	array(
		'table_name'  			=> 'customers',
		'label_singular' 		=> 'customer',
		'label_plural'			=> 'customers',
		'data_order'		    => array('customer_name' => 'asc'),
		'id_prefix_code'		=> 'CUS',
		'name_field'			=> 'customer_name',
		'related_entities'			=> 
			array(
				// Related entity: ARC
				array(
					'entity_name'	 => 'arc',
					'related_field'  => 'customer'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'customer_name' =>
				array('field_label' 	=> 'Customer Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity department
	'department' =>
	array(
		'table_name'  			=> 'departments',
		'label_singular' 		=> 'department',
		'label_plural'			=> 'departments',
		'data_order'		    => array('department_name' => 'asc'),
		'id_prefix_code'		=> 'DEP',
		'name_field'			=> 'department_name',
		'related_entities'			=> 
			array(
				// Related entity: ARC
				array(
					'entity_name'	 => 'arc',
					'related_field'  => 'responsible'
				),
				
				// Related entity: Employee
				array(
					'entity_name'	 => 'employee',
					'related_field'  => 'department'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'department_name' =>
				array('field_label' 	=> 'Department Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity employee
	'employee' =>
	array(
		'table_name'  			=> 'employees',
		'label_singular' 		=> 'employee',
		'label_plural'			=> 'employees',
		'data_order'		    => array('employee_name' => 'asc'),
		'id_prefix_code'		=> 'EMP',
		'name_field'			=> 'employee_name',
		'related_entities'			=> 
			array(
				// Related entity: Assignment
				array(
					'entity_name'	 => 'assignment',
					'related_field'  => 'employee'
				),
				
				// Related entity: Attendance
				array(
					'entity_name'	 => 'attendance',
					'related_field'  => 'employee'
				),
				
				// Related entity: Reward
				array(
					'entity_name'	 => 'reward',
					'related_field'  => 'employee'
				),
			),
		'tabs_widgets'			=> array(
			array(
				'tab_name'  => 'info',
				'widgets'   => array(
					array('widget_type' => 'custom', 'widget_name' => 'widget_employee_evolution_points_breakdown')
				)
			)
		),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'employee_name' =>
				array('field_label' 	=> 'Employee Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'employee_username' =>
				array('field_label' 	=> 'Employee Username', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
				),
				
				'email' =>
				array('field_label' 	=> 'Email', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
				),
				
				'role' =>
				array('field_label' 	=> 'Role', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
				),
				
				'facility' =>
				array('field_label' 	=> 'Facility', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'facility',
							  'data_order'     => 'facility_name asc'
						)
				),
				
				'department' =>
				array('field_label' 	=> 'Department', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'department',
							  'data_order'     => 'department_name asc'
						)
				),
				
				'employee_shift' =>
				array('field_label' 	=> 'Shift', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee_shift_type',
							  'data_order'     => 'employee_shift_type_name asc'
						)
				),
				
				'is_staff' =>
				array('field_label' 	=> 'Is Staff?', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'options_type'	=> 'true_false',
					  'column_width'	=> 50,
					  'format_type'		=> 'status'
				),
				
				'is_active' =>
				array('field_label' 	=> 'Is Active?', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'options_type'	=> 'true_false',
					  'column_width'	=> 50,
					  'format_type'		=> 'status'
				),
				
				'user_id' =>
				array('field_label' 	=> 'User ID', 
					  'field_data_type' => 'int',
					  'input_type'		=> 'text',
					  'column_width'	=> 80,
					  'tooltip'			=> 'User ID from MWE database'
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity employee_shift_type
	'employee_shift_type' =>
	array(
		'table_name'  			=> 'employee_shift_types',
		'label_singular' 		=> 'employee shift type',
		'label_plural'			=> 'employee shift types',
		'data_order'		    => array('employee_shift_type_name' => 'asc'),
		'id_prefix_code'		=> 'EST',
		'name_field'			=> 'employee_shift_type_name',
		'related_entities'			=> 
			array(
				// Related entity: Employee
				array(
					'entity_name'	 => 'employee',
					'related_field'  => 'employee_shift'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id',
					  'column_hidden_in_list' => true
				),
				
				'employee_shift_type_name' =>
				array('field_label' 	=> 'Employee Shift Type Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'pay_scale' =>
				array('field_label' 	=> 'Pay Scale', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity evolution_points_logs
	'evolution_points_log' =>
	array(
		'table_name'  			=> 'evolution_points_logs',
		'label_singular' 		=> 'evolution points log',
		'label_plural'			=> 'evolution points logs',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'EVP',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id',
					  'column_hidden_in_list' => true
				),
				
				'employee' =>
				array('field_label' 	=> 'Employee', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee',
							  'data_order'     => 'employee_name asc'
						)
				),
				
				'datetime' =>
				array('field_label' 	=> 'Datetime', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'column_width'	=> 70
				),
				
				'reason_type' =>
				array('field_label' 	=> 'Reason Type', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'required'		=> true,
					  'selectized'		=> true,
					  'options_type'	=> 'evolution_point_reason_types',
					  'column_width'	=> 50,
					  'format_type'		=> 'option_label'
				),
				
				'work_action_type' =>
				array('field_label' 	=> 'Work Action Type', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'options_type'	=> 'work_action_types',
					  'column_width'	=> 50,
					  'format_type'		=> 'option_label'
				),
				
				'base_evolution_points' =>
				array('field_label' 	=> 'Base Evolution Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'evolution_point_factor' =>
				array('field_label' 	=> 'Evolution Point Factor', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'is_counted' =>
				array('field_label' 	=> 'Is Counted?', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'options_type'	=> 'true_false',
					  'column_width'	=> 50,
					  'format_type'		=> 'status'
				),
				
				'evolution_points' =>
				array('field_label' 	=> 'Evolution Points', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity facility
	'facility' =>
	array(
		'table_name'  			=> 'facilities',
		'label_singular' 		=> 'facility',
		'label_plural'			=> 'facilities',
		'data_order'		    => array('facility_name' => 'asc'),
		'id_prefix_code'		=> 'FAC',
		'name_field'			=> 'facility_name',
		'related_entities'			=> 
			array(
				// Related entity: takt_data
				array(
					'entity_name'	 => 'takt_data',
					'related_field'  => 'facility'
				),
				
				// Related entity: ARC
				array(
					'entity_name'	 => 'arc',
					'related_field'  => 'facility'
				),
				
				// Related entity: carrier_sla
				array(
					'entity_name'	 => 'carrier_sla',
					'related_field'  => 'facility'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'facility_name' =>
				array('field_label' 	=> 'Facility Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'hours_shift' =>
				array('field_label' 	=> 'Hours Shift', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'break_time_per_shift_in_min' =>
				array('field_label' 	=> 'Break Time Per Shift (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'lunch_time_per_shift_in_min' =>
				array('field_label' 	=> 'Lunch Time Per Shift (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'picking_cycle_time_in_min' =>
				array('field_label' 	=> 'Picking Cycle Time (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'packing_cycle_time_in_min' =>
				array('field_label' 	=> 'Packing Cycle Time (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'loading_cycle_time_in_min' =>
				array('field_label' 	=> 'Loading Cycle Time (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'operational_cost_per_package' =>
				array('field_label' 	=> 'Operational Cost Ratio', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'fte_cost_per_hour' =>
				array('field_label' 	=> 'FTE Cost Per Hour', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'cost_per_package_target' =>
				array('field_label' 	=> 'Cost Per Package Target', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'evolution_goals' =>
				array('field_label' 	=> 'Evolution Goals', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'stock_id' =>
				array('field_label' 	=> 'Stock ID', 
					  'field_data_type' => 'int',
					  'input_type'		=> 'text',
					  'column_width'	=> 80,
					  'tooltip'			=> 'Facility ID from MWE database'
				),
				
				'facility_code' =>
				array('field_label' 	=> 'Facility Code', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'tsheets_facility_prefix' =>
				array('field_label' 	=> 'TSheets Facility Prefix', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true,
					  'tooltip'			=> 'The prefix used in the TSheets Group name for this facility'
				),
				
				'timezone' =>
				array('field_label' 	=> 'Timezone', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'timezone_name' =>
				array('field_label' 	=> 'Time Zone Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true
				),
				
				'carrier_ontrac_cutoff_time' =>
				array('field_label' 	=> 'Ontrac Carrier Cutoff Time (Local Time)', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'placeholder'		=> 'HH:MM'
				),
				
				'carrier_ups_cutoff_time' =>
				array('field_label' 	=> 'UPS Carrier Cutoff Time (Local Time)', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'placeholder'		=> 'HH:MM'
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity metrics_board_note
	'metrics_board_note' =>
	array(
		'table_name'  			=> 'metrics_board_notes',
		'label_singular' 		=> 'metrics board note',
		'label_plural'			=> 'metrics board notes',
		'data_order'		    => array('datetime' => 'desc'),
		'id_prefix_code'		=> 'MBN',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'datetime' =>
				array('field_label' 	=> 'Datetime', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'validation'		=> 'datetime',
					  'default_value'	=> '{NOW}',
					  'required'		=> true,
					  'column_width'	=> 80
				),
				
				'note_content' =>
				array('field_label' 	=> 'Note Content', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'required'		=> true,
					  'column_width'	=> 300
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity reward
	'reward' =>
	array(
		'table_name'  			=> 'rewards',
		'label_singular' 		=> 'reward',
		'label_plural'			=> 'rewards',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'RWD',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'employee' =>
				array('field_label' 	=> 'Employee', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'employee',
							  'data_order'     => 'employee_name asc'
						)
				),
				
				'reward_given_datetime' =>
				array('field_label' 	=> 'Reward Given Date & Time', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'validation'		=> 'datetime',
					  'default_value'	=> '{NOW}',
					  'column_width'	=> 80
				),

				'reward_code' =>
				array('field_label' 	=> 'Reward Code', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'column_width'	=> 100
				),
				
				'reward_value' =>
				array('field_label' 	=> 'Reward Value (USD)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity takt_data
	'takt_data' =>
	array(
		'table_name'  			=> 'takt_data',
		'label_singular' 		=> 'takt data',
		'label_plural'			=> 'takt data',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'TKD',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id',
					  'column_hidden_in_list' => true
				),
				
				'facility' =>
				array('field_label' 	=> 'Facility', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'facility',
							  'data_order'     => 'facility_name asc'
						)
				),
				
				'date' =>
				array('field_label' 	=> 'Date', 
					  'field_data_type' => 'date',
					  'input_type'		=> 'date',
					  'default_value'	=> '{TODAY}',
					  'required'		=> true,
					  'column_width'	=> 70,
					  'text_align'		=> 'center'
				),
				
				'projected_demand' =>
				array('field_label' 	=> 'Projected Demand', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'hours_shift' =>
				array('field_label' 	=> 'Hours Shift', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'break_time_per_shift_in_min' =>
				array('field_label' 	=> 'Break Time Per Shift (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'lunch_time_per_shift_in_min' =>
				array('field_label' 	=> 'Lunch Time Per Shift (Mins)', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'number_of_employees_scheduled' =>
				array('field_label' 	=> 'Number of Employees Scheduled', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity supplier
	'supplier' =>
	array(
		'table_name'  			=> 'suppliers',
		'label_singular' 		=> 'supplier',
		'label_plural'			=> 'suppliers',
		'data_order'		    => array('supplier_name' => 'asc'),
		'id_prefix_code'		=> 'SUP',
		'name_field'			=> 'supplier_name',
		'related_entities'			=> 
			array(
				// Related entity: ARC
				array(
					'entity_name'	 => 'arc',
					'related_field'  => 'supplier'
				),
			),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'supplier_name' =>
				array('field_label' 	=> 'Supplier Name', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
					  'unique'			=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity removed_idle_order
	'removed_idle_order' =>
	array(
		'table_name'  			=> 'removed_idle_orders',
		'label_singular' 		=> 'removed idle order',
		'label_plural'			=> 'removed idle orders',
		'data_order'		    => array('date' => 'desc'),
		'id_prefix_code'		=> 'RIO',
		'name_field'			=> 'order_no',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'order_no' =>
				array('field_label' 	=> 'Order No', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
				),
				
				'date' =>
				array('field_label' 	=> 'Date', 
					  'field_data_type' => 'date',
					  'input_type'		=> 'date',
					  'required'		=> true,
					  'column_width'	=> 70,
					  'text_align'		=> 'center'
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
	
	// Entity carrier_sla
	'carrier_sla' =>
	array(
		'table_name'  			=> 'carrier_sla',
		'label_singular' 		=> 'carrier SLA',
		'label_plural'			=> 'carrier SLA',
		'data_order'		    => array('id' => 'asc'),
		'id_prefix_code'		=> 'CSLA',
		'name_field'			=> 'id',
		'related_entities'			=> 
			array(),
		'fields'				=>
			array(
				'id' =>
				array('field_label' 	=> 'ID',          
					  'format_type'		=> 'id'
				),
				
				'facility' =>
				array('field_label' 	=> 'Facility', 
					  'field_data_type' => 'number',
					  'input_type'		=> 'select',
					  'selectized'		=> true,
					  'required'		=> true,
					  'foreign_key' 	=> 
						array('entity_name'    => 'facility',
							  'data_order'     => 'facility_name asc'
						)
				),
				
				'carrier_code' =>
				array('field_label' 	=> 'Carrier Code', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'text',
					  'required'		=> true,
				),
				
				'sla_cap' =>
				array('field_label' 	=> 'SLA Cap', 
					  'field_data_type' => 'double',
					  'input_type'		=> 'text',
					  'column_width'	=> 80,
					  'required'		=> true
				),
				
				'note' =>
				array('field_label' 	=> 'Note', 
					  'field_data_type' => 'text',
					  'input_type'		=> 'textarea',
					  'column_width'	=> 300
				)
			)
	),
);

$config['styles_template'] = array(
	'id' =>
	array(
		'visible_in_edit' => false,
		'column_width' => 40,
		'text_align' => 'center'
	),
	'status' =>
	array(
		'column_width' => 70,
		'text_align' => 'center'
	),
	'currency' =>
	array(
		'column_width' => 100,
		'text_align' => 'right'
	)
);

define('USE_ID_PREFIX', true);

// Option to show the add new button in "view detail" page.
$config['show_add_new_button_in_view_detail'] = true;

$config['default_metadata_visibility'] = false;

// If 'view' is restricted, the other action is also restricted.
$config['user_capabilities'] = array(
	USER_ROLE_ALL =>
	array(
		'restricted_entities' => array(
			'view'  =>
				array(),
			'direct_add' => // Cannot add directly (need to add from related entities)
				array(),
			'add' => 
				array('evolution_points_log', 'assignment'),
			'edit'	=> 
				array('evolution_points_log'),
			'delete'	=> 
				array('evolution_points_log')
		),
		'restricted_fields' => array(
			'view' =>
				array(),
			'direct_add' => // Cannot add directly (need to add from related entities)
				array(),
			'add' =>
				array(),
			'edit' =>
				array(
					'takt_data' => array('date')
				)
		),
		'fields_shown_with_condition' => array(
			'direct_add' =>
				array(),
			'add' =>
				array(),
			'edit' =>
				array()
		),
		'advanced_permission_criteria' => array(
			'add' => array(),
			'edit' => array(),
			'delete' => array()
		)
	),
	USER_ROLE_ADMIN =>
	array(
		'restricted_entities' => array(
			'view' => array(),
			'edit'	=> array()		
		),
		'advanced_permission_criteria' => array(
			'view' => array(),
			'edit' => array() 
		),
		'user_level' => 1000
	),
	USER_ROLE_ADMIN_WITH_FINANCIAL =>
	array(
		'restricted_entities' => array(
			'view' => array(),
			'edit'	=> array()		
		),
		'advanced_permission_criteria' => array(
			'view' => array(),
			'edit' => array() 
		),
		'user_level' => 1000
	)
);

// To label select options.
$config['options_labels'] = array(
	'yes_no' => array(
		'yes' => array('label' => 'Yes', 'color' => 'success'),
		'no' => array('label' => 'No', 'color' => 'danger')
	),
	'time_hours' => array(
		'00:00:00' => array('label' => '00:00'),
		'01:00:00' => array('label' => '01:00'),
		'02:00:00' => array('label' => '02:00'),
		'03:00:00' => array('label' => '03:00'),
		'04:00:00' => array('label' => '04:00'),
		'05:00:00' => array('label' => '05:00'),
		'06:00:00' => array('label' => '06:00'),
		'07:00:00' => array('label' => '07:00'),
		'08:00:00' => array('label' => '08:00'),
		'09:00:00' => array('label' => '09:00'),
		'10:00:00' => array('label' => '10:00'),
		'11:00:00' => array('label' => '11:00'),
		'12:00:00' => array('label' => '12:00'),
		'13:00:00' => array('label' => '13:00'),
		'14:00:00' => array('label' => '14:00'),
		'15:00:00' => array('label' => '15:00'),
		'16:00:00' => array('label' => '16:00'),
		'17:00:00' => array('label' => '17:00'),
		'18:00:00' => array('label' => '18:00'),
		'19:00:00' => array('label' => '19:00'),
		'20:00:00' => array('label' => '20:00'),
		'21:00:00' => array('label' => '21:00'),
		'22:00:00' => array('label' => '22:00'),
		'23:00:00' => array('label' => '23:00')
	),
	
	'true_false' => array(
		1 => array('label' => 'Yes', 'color' => 'success'),
		0 => array('label' => 'No', 'color' => 'default')
	),
	
	'evolution_point_reason_types' => array(
		'work' => array('label' => 'Work'),
		'attendance' => array('label' => 'Attendance')
	),
	
	'work_action_types' => array(
		'picking' => array('label' => 'Picking'),
		'packing' => array('label' => 'Packing'),
		'load' => array('label' => 'Load')
	)
);