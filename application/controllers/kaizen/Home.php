<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title(lang('title__home'));

		$data = array();
		
		$data['shortcuts'] = array(
			array('glyphicon' => 'list', 'label' => 'Scoreboard', 'url' => base_url('kaizen/scoreboard')),
			array('glyphicon' => 'list', 'label' => 'Takt Board', 'url' => base_url('kaizen/takt_board')),
			array('glyphicon' => 'list', 'label' => 'Metrics Board', 'url' => base_url('kaizen/metrics_board')),
			array('glyphicon' => 'list', 'label' => 'Team Helper Board', 'url' => base_url('kaizen/team_helper_board')),
			array('glyphicon' => 'list', 'label' => 'Client Support Board', 'url' => base_url('kaizen/client_support_board')),
			array('glyphicon' => 'list', 'label' => 'Loading Andon Board', 'url' => base_url('kaizen/loading_andon_board')),
			array('glyphicon' => 'list', 'label' => 'Inbound Board', 'url' => base_url('kaizen/inbound_board')),
			array('glyphicon' => 'list', 'label' => 'Idle Status Board', 'url' => base_url('kaizen/idle_status_board')),
			array('glyphicon' => 'list', 'label' => 'Idle Break Board', 'url' => base_url('kaizen/idle_break_board')),
			array('glyphicon' => 'list', 'label' => 'Countdown Board', 'url' => base_url('kaizen/countdown_board')),
			array('glyphicon' => 'list', 'label' => 'ACs Idle Board', 'url' => base_url('kaizen/acs_idle_board')),
			array('glyphicon' => 'list', 'label' => 'Inbound Idle Time Board', 'url' => base_url('kaizen/inbound_idle_time_board')),
			array('glyphicon' => 'list', 'label' => 'Package Status Board', 'url' => base_url('kaizen/package_status_board')),
			array('glyphicon' => 'list', 'label' => 'Inventory Board', 'url' => base_url('kaizen/inventory_board')),
			array('glyphicon' => 'list', 'label' => 'Loading Utilization Board', 'url' => base_url('kaizen/loading_utilization_board')),
			array('glyphicon' => 'list', 'label' => 'Empty Spots Board', 'url' => base_url('kaizen/empty_spots_board')),
			array('glyphicon' => 'list', 'label' => 'Idle Picking Batch Board', 'url' => base_url('kaizen/idle_picking_batch_board')),
			array('glyphicon' => 'list', 'label' => 'Replenishment Release Board', 'url' => base_url('kaizen/replenishment_release_board')),
			array('glyphicon' => 'list', 'label' => 'Trailer Utilization Forecast Board', 'url' => base_url('kaizen/trailer_utilization_forecast_board')),
			array('glyphicon' => 'list', 'label' => 'SLA Board', 'url' => base_url('kaizen/sla_board')),
			array('glyphicon' => 'list', 'label' => 'Batching Helper Board', 'url' => base_url('kaizen/batching_helper_board')),
			array('glyphicon' => 'list', 'label' => 'Client Inventory Optimization Board', 'url' => base_url('kaizen/client_inventory_replenishment_board')),
			array('glyphicon' => 'list', 'label' => 'Status Board', 'url' => base_url('kaizen/status_board')),
			array('glyphicon' => 'list', 'label' => 'Carton Utilization Board', 'url' => base_url('kaizen/carton_utilization_board')),
			array('glyphicon' => 'list', 'label' => 'Packages By Week Board', 'url' => base_url('kaizen/packages_by_week_board')),
			array('glyphicon' => 'list', 'label' => 'Packages By Month Board', 'url' => base_url('kaizen/packages_by_month_board')),
			array('glyphicon' => 'list', 'label' => 'Packages By Date X Location X Carrier Board', 'url' => base_url('kaizen/packages_by_date_location_carrier_board')),
			array('glyphicon' => 'list', 'label' => 'Kaizen Manager', 'url' => base_url('kaizen/kaizen_manager')),
			array('glyphicon' => 'list', 'label' => 'Idle Manifest Board', 'url' => base_url('kaizen/idle_manifest_board')),
			array('glyphicon' => 'list', 'label' => 'Carrier Diversification Board', 'url' => base_url('kaizen/carrier_diversification_board')),
			array('glyphicon' => 'list', 'label' => 'Inventory Counts Board', 'url' => base_url('kaizen/inventory_counts_board')),
			array('glyphicon' => 'list', 'label' => 'Long Term Inventory Counts Board', 'url' => base_url('kaizen/long_term_inventory_counts_board'))
		);
		
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role') == USER_ROLE_ADMIN_WITH_FINANCIAL) {
			$data['shortcuts'][] = array('glyphicon' => 'list', 'label' => 'Package Board', 'url' => base_url('kaizen/package_board'));
			$data['shortcuts'][] = array('glyphicon' => 'list', 'label' => 'Revenue Board', 'url' => base_url('kaizen/revenue_board'));
			$data['shortcuts'][] = array('glyphicon' => 'list', 'label' => 'Carrier Optimization Board', 'url' => base_url('kaizen/carrier_optimization_board'));
			$data['shortcuts'][] = array('glyphicon' => 'list', 'label' => 'Client Complexity and Profitability', 'url' => base_url('kaizen/report/client_complexity_and_profitability'));
		}

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_home', $data);
		$this->load->view('view_footer');
	}
}
