<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_general extends CI_Model {
	public function __construct() {
		$this->load->database();
		$prod_db = $this->load->database('prod', TRUE);
	}
	
	public function get_facility_list() {
		$facility_list = $prod_db
			->select('id, facility_name')
			->from('facilities')
			->where('data_status', DATA_ACTIVE)
			->order_by('facility_name')
			->get()->result_array();
		
		return $facility_list;
	}
}