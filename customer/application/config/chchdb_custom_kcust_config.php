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
define('USER_ROLE_CUSTOMER', 3);

$config['header_menu'] = array(
	array(
		'type'    => 'dropdown',
		'label'   => 'Boards',
		'submenu' => array (
			array('type' => 'link', 'label' => 'Package Status Board', 'link' => 'kcust/carrier_status_dashboard_for_packages'),
			array('type' => 'link', 'label' => 'Client Inventory Optimization Board', 'link' => 'db/view/client_inventory_optimization_board'),
		)
	),
);

$config['db_structure'] = array(
	
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
	),
	USER_ROLE_CUSTOMER =>
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
	)
);